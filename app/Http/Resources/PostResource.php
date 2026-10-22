<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
   public function toArray(Request $request): array
{
    $list = $request->routeIs('api.posts.index', 'api.posts.mine');

    return [
        'id'           => $this->id,
        'title'        => $this->title,
        'slug'         => $this->slug,
        'excerpt'      => $this->excerpt,
        'content'      => $this->when(! $list, fn () => $this->body_html),
        'featured'     => $this->featured ? asset('storage/' . $this->featured) : null,
        'status'       => $this->status,
        'views'        => $this->views,
        'reading_time' => $this->reading_time,
        'category'     => $this->whenLoaded('category', fn () => $this->category->only('id', 'name', 'slug')),
        'author'       => $this->whenLoaded('user', fn () => $this->user->only('id', 'name', 'slug')),
        'tags'         => $this->whenLoaded('tags', fn () => $this->tags->map->only('id', 'name', 'slug')),
        'published_at' => ($this->approved_at ?? $this->created_at)?->toIso8601String(),
    ];
}
}
