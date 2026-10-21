<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Support\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Notifications\PostApproved;
use App\Support\Activity;


class PostController extends Controller implements HasMiddleware
{
  
    public static function middleware(): array
    {
        return [
            new Middleware('can:posts.create', only: ['create', 'store']),
        ];
    }


    public function index(Request $request)
    {
        $user = auth()->user();

        $seeAll = $user->can('posts.view_all');    
        $canApprove = $user->can('posts.approve');   


        $status = $request->input('status', $canApprove ? 'pending' : 'all');
        $status = in_array($status, ['all', 'pending', 'approved', 'rejected']) ? $status : 'all';


        $base = Post::query()
            ->when(! $seeAll, fn ($q) => $q->where('user_id', $user->id))
            ->when($seeAll && $request->filled('author'), fn ($q) => $q->where('user_id', $request->input('author')));

        $counts = (clone $base)->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        $posts = (clone $base)
            ->select(['id', 'category_id', 'user_id', 'title', 'slug', 'featured', 'status', 'created_at'])
            ->with(['category:id,name', 'user:id,name'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->input('q') . '%'))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->input('category')))
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'rejected' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.posts.index', [
            'posts' => $posts,
            'counts' => $counts,
            'status' => $status,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'authorFilter' => $seeAll && $request->filled('author') ? User::find($request->input('author')) : null,
        ]);
    }


    public function create()
    {
        return view('admin.posts.create', [
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }


    public function store(Request $request)
    {
        $data = $this->validated($request);
        $tags = $data['tags'] ?? [];
        unset($data['tags']);
        $this->applyMeta($request, $data);

        if ($request->hasFile('featured')) {
            $data['featured'] = $request->file('featured')->store('posts', 'public');
        }


        $canPublish = auth()->user()->can('posts.approve');

        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['user_id'] = auth()->id();
        $data['status'] = $canPublish ? 'approved' : 'pending';
        $data['approved_at'] = $canPublish ? now() : null;

        $post = Post::create($data);
        $post->tags()->sync($tags);
        $post->refreshKeywords();

        Activity::log('post.created', "Created post \"{$post->title}\"", $post, ['status' => $post->status]);

        return redirect()->route('posts.index', ['status' => 'all'])->with(
            'success',
            $canPublish ? 'Post published.' : 'submitted post. It will not be shown on the site until the admin approves it.'
        );
    }


    public function edit(Post $post)
    {
        Gate::authorize('update', $post);  

        return view('admin.posts.edit', [
            'post' => $post->load('tags'),
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }


    public function update(Request $request, Post $post)
    {
        Gate::authorize('update', $post);

        $data = $this->validated($request);
        $tags = $data['tags'] ?? [];
        unset($data['tags']);
        $this->applyMeta($request, $data, $post);

        if ($request->hasFile('featured')) {
            if ($post->featured) {
                Storage::disk('public')->delete($post->featured);
            }
            $data['featured'] = $request->file('featured')->store('posts', 'public');
        }

        if ($data['title'] !== $post->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $post->id);
        }


        $canApprove = auth()->user()->can('posts.approve');
        if (! $canApprove) {
            $data['status'] = 'pending';
            $data['approved_at'] = null;
        }

        $post->update($data);
        $post->tags()->sync($tags);
        $post->refreshKeywords();

        Activity::log('post.updated', "Updated post \"{$post->title}\"", $post);

        return redirect()->route('posts.index', ['status' => 'all'])->with(
            'success',
            $canApprove ? 'Post updated.' : 'Post updated. It will not be shown on the site until the admin approves it.'
        );
    }


    public function destroy(Post $post)
    {
        Gate::authorize('delete', $post);

        $post->delete();
        Activity::log('post.trashed', "Moved post \"{$post->title}\" to trash", $post);

        return redirect()->route('posts.index', ['status' => 'all'])->with('success', 'Post moved to trash.');
    }



    public function approve(Post $post)
    {
         $wasApproved = $post->status === 'approved';

    $post->update(['status' => 'approved', 'approved_at' => now()]);

    if (! $wasApproved && $post->user_id !== auth()->id()) {
        $post->user?->notify(new PostApproved($post));
    }


    Activity::log('post.approved', "Approved post \"{$post->title}\"", $post);

    return back()->with('success', '"' . $post->title . '" approved.');
    }

    public function reject(Post $post)
    {
        $post->update(['status' => 'rejected', 'approved_at' => null]);
        Activity::log('post.rejected', "Rejected post \"{$post->title}\"", $post);

        return back()->with('success', '"' . $post->title . '" rejected.');
    }

    public function trash()
    {
        $posts = Post::onlyTrashed()
            ->select(['id', 'category_id', 'user_id', 'title', 'deleted_at'])
            ->with(['category:id,name', 'user:id,name'])
            ->latest('deleted_at')->paginate(20);

        return view('admin.posts.trash', compact('posts'));
    }

    public function restore($id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);
        $post->restore();
        Activity::log('post.restored', "Restored post \"{$post->title}\"", $post);

        return redirect()->route('posts.trash')->with('success', 'Post restored.');
    }

    public function forceDelete($id)
    {
        $post = Post::onlyTrashed()->findOrFail($id);


        foreach ([$post->featured, $post->meta_image] as $file) {
            if ($file) {
                Storage::disk('public')->delete($file);
            }
        }
        Activity::log('post.deleted', "Permanently deleted post \"{$post->title}\"", $post);
        $post->forceDelete();

        return redirect()->route('posts.trash')->with('success', 'Post permanently deleted.');
    }

    
    
    private function applyMeta(Request $request, array &$data, ?Post $post = null): void
    {
        $manual = $post?->meta_manual ?? [];

        foreach (['meta_title', 'meta_description', 'meta_keywords'] as $field) {
            $value = trim((string) ($data[$field] ?? ''));

            if ($value !== '') {
                $data[$field] = $value;
                $manual[] = $field;
            } else {
                $data[$field] = null;                     
                $manual = array_diff($manual, [$field]);
            }
        }

        $old = $post?->meta_image;
        $oldIsCustom = $old && in_array('meta_image', $manual, true) && str_starts_with($old, 'meta/');

        if ($request->hasFile('meta_image')) {
            if ($oldIsCustom) {
                Storage::disk('public')->delete($old);
            }
            $data['meta_image'] = $request->file('meta_image')->store('meta', 'public');
            $manual[] = 'meta_image';
        } elseif ($request->boolean('remove_meta_image') && in_array('meta_image', $manual, true)) {
            if ($oldIsCustom) {
                Storage::disk('public')->delete($old);
            }
            $data['meta_image'] = null;                     
            $manual = array_diff($manual, ['meta_image']);
        } else {
            unset($data['meta_image']);                     
        }

        $data['meta_manual'] = array_values(array_unique($manual));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|max:255',
            'category_id' => 'required|exists:categories,id',
            'content' => 'required|max:20000',
            'featured' => 'nullable|image|max:2048',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_image' => 'nullable|image|max:2048',
        ]);

        $data['content'] = HtmlSanitizer::clean($data['content']);

        $plain = html_entity_decode(strip_tags($data['content']), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (preg_replace('/[\s\x{00A0}]+/u', '', $plain) === '') {
            throw ValidationException::withMessages(['content' => 'empty Content. write somethings.']);
        }

        return $data;
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $i = 1;

        while (Post::withTrashed()->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()) {
            $slug = $original . '-' . $i++;
        }

        return $slug;
    }
}