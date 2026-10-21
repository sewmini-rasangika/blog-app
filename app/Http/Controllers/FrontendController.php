<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Reaction;
use App\Support\Activity;

class FrontendController extends Controller
{
    public function index()
    {
        $posts = Post::approved()->with(['category', 'user'])->latest()->paginate(7);
        return view('frontend.index', compact('posts'));
    }

    public function show(Post $post)
    {
       
        $isApproved = $post->status === 'approved';
        abort_unless($isApproved || (auth()->check() && Gate::allows('update', $post)), 404);

        if ($isApproved) {
            $post->increment('views');
        }

        $post->load(['category', 'tags', 'user.profile']);

        $next = Post::approved()->where('id', '>', $post->id)->orderBy('id')->first();
        $prev = Post::approved()->where('id', '<', $post->id)->orderByDesc('id')->first();

        $related = Post::approved()->with(['category', 'user'])
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->latest()->take(3)->get();

        $comments = $post->comments()->approved()->whereNull('parent_id')
        ->with([
            'user.profile',
            'replies' => fn ($q) => $q->approved()->oldest()->with('user.profile'),
        ])
        ->latest()
        ->paginate(10, ['*'], 'cpage')
        ->fragment('comments');

        $commentCount = $post->comments()->approved()->count();

        $reactionCounts = $post->reactions()
        ->selectRaw('type, count(*) as c')->groupBy('type')->pluck('c', 'type')->all();

        $myKey = Reaction::voterKey(request());
        $myReaction = $myKey ? $post->reactions()->where('voter_key', $myKey)->value('type') : null;

        return view('frontend.show', compact('post', 'next', 'prev', 'related', 'comments', 'commentCount', 'reactionCounts', 'myReaction'));

    }

    

    public function category(Category $category)
    {
        $posts = $category->posts()->approved()->with(['category', 'user'])->latest()->paginate(6);

        return view('frontend.list', ['title' => 'Category: ' . $category->name, 'posts' => $posts]);
    }

    public function tag(Tag $tag)
    {
        $posts = $tag->posts()->approved()->with(['category', 'user'])->latest()->paginate(6);

        return view('frontend.list', ['title' => 'Tag: #' . $tag->name, 'posts' => $posts]);
    }

    public function author(User $user)
    {
        $posts = $user->posts()->approved()->with(['category', 'user'])->latest()->paginate(6);

        return view('frontend.list', ['title' => 'Posts by ' . $user->name, 'posts' => $posts]);
    }

   public function search(Request $request)
{
    $q = trim($request->validate(['q' => 'nullable|max:100'])['q'] ?? '');

  
    $like = '%' . addcslashes($q, '%_\\') . '%';

    $posts = Post::approved()->with(['category', 'user'])
        ->when($q !== '', fn ($query) => $query->where(function ($w) use ($like) {
            $w->where('title', 'like', $like)->orWhere('search_text', 'like', $like);
        }))
        ->when($q === '', fn ($query) => $query->whereRaw('1 = 0'))
        ->latest()->paginate(6)->withQueryString();

    return view('frontend.list', [
        'title' => $q === '' ? 'Search' : "Search results for: {$q}",
        'posts' => $posts,
    ]);
}
}
