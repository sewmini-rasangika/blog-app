<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Support\Activity;
use App\Support\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:100',
            'category' => 'nullable|string',
            'tag' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $posts = Post::approved()->with(['category', 'user', 'tags'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $like = '%' . addcslashes($request->q, '%_\\') . '%';
                $q->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('search_text', 'like', $like));
            })
            ->when($request->filled('category'), fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $request->category)))
            ->when($request->filled('tag'), fn ($q) => $q->whereHas('tags', fn ($t) => $t->where('slug', $request->tag)))
            ->latest()
            ->paginate($request->integer('per_page', 10))
            ->withQueryString();

        return PostResource::collection($posts);
    }

    public function show(Post $post)
    {
        abort_unless($post->status === 'approved', 404);
        $post->increment('views');

        return new PostResource($post->load(['category', 'user', 'tags']));
    }

    // Login una author ge posts (pending/rejected ekka)
    public function mine(Request $request)
    {
        return PostResource::collection(
            Post::where('user_id', $request->user()->id)->with(['category', 'user', 'tags'])->latest()->paginate(10)
        );
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('posts.create'), 403);

        $data = $this->validated($request);
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        if ($request->hasFile('featured')) {
            $data['featured'] = $request->file('featured')->store('posts', 'public');
        }

        $canPublish = $request->user()->can('posts.approve');

        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['user_id'] = $request->user()->id;
        $data['status'] = $canPublish ? 'approved' : 'pending';
        $data['approved_at'] = $canPublish ? now() : null;

        $post = Post::create($data);
        $post->tags()->sync($tags);
        $post->refreshKeywords();

        Activity::log('post.created', "Created post \"{$post->title}\" (API)", $post, ['status' => $post->status], $request->user());

        return (new PostResource($post->load(['category', 'user', 'tags'])))
            ->response()->setStatusCode(201);
    }

    public function update(Request $request, Post $post)
    {
        Gate::forUser($request->user())->authorize('update', $post);

        $data = $this->validated($request);
        $tags = $data['tags'] ?? null;
        unset($data['tags']);

        if ($request->hasFile('featured')) {
            if ($post->featured) {
                Storage::disk('public')->delete($post->featured);
            }
            $data['featured'] = $request->file('featured')->store('posts', 'public');
        }

        if ($data['title'] !== $post->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $post->id);
        }

        if (! $request->user()->can('posts.approve')) {
            $data['status'] = 'pending';
            $data['approved_at'] = null;
        }

        $post->update($data);
        if ($tags !== null) {
            $post->tags()->sync($tags);
        }
        $post->refreshKeywords();

        Activity::log('post.updated', "Updated post \"{$post->title}\" (API)", $post, [], $request->user());

        return new PostResource($post->load(['category', 'user', 'tags']));
    }

    public function destroy(Request $request, Post $post)
    {
        Gate::forUser($request->user())->authorize('delete', $post);

        $post->delete();
        Activity::log('post.trashed', "Moved post \"{$post->title}\" to trash (API)", $post, [], $request->user());

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title'       => 'required|max:255',
            'category_id' => 'required|exists:categories,id',
            'content'     => 'required|max:20000',
            'featured'    => 'nullable|image|max:2048',
            'tags'        => 'nullable|array',
            'tags.*'      => 'exists:tags,id',
        ]);

        $data['content'] = HtmlSanitizer::clean($data['content']);

        return $data;
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = $original = Str::slug($title);
        $i = 1;

        while (Post::withTrashed()->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $original . '-' . $i++;
        }

        return $slug;
    }
}