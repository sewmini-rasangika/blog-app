<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Support\Seo;

class Post extends Model
{
    public function getRouteKeyName(): string
{
    return 'slug';
}
use SoftDeletes;

    protected $guarded = [];
   
    protected $casts = ['approved_at' => 'datetime', 'meta_manual' => 'array'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

public function plainText(): string
{
    $html = preg_replace('/<\/(p|li|h2|h3|blockquote)>|<br\s*\/?>/i', ' $0', (string) $this->content);
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    return trim(preg_replace('/\s+/u', ' ', $text));
}

    protected static function booted(): void
{
    static::saving(function (Post $post) {
        if ($post->isDirty('content') || $post->search_text === null) {
            $post->search_text = $post->plainText();
        }
          $post->fillSeo();
    });

}

public function getReadingTimeAttribute(): int
{
    $words = count(preg_split('/\s+/u', $this->plainText(), -1, PREG_SPLIT_NO_EMPTY));
    return max(1, (int) ceil($words / 200));
}

public function getExcerptAttribute(): string
{
    return Str::limit($this->plainText(), 140);
}


public function getBodyHtmlAttribute(): string
{
    $c = (string) $this->content;

    if ($c === strip_tags($c)) {
        return collect(preg_split('/\R{2,}/', trim($c)))
            ->filter()
            ->map(fn ($p) => '<p>' . nl2br(e($p)) . '</p>')
            ->implode('');
    }

    return $c;
}


public function getSeoTitleAttribute(): string
{
    return $this->meta_title ?: $this->title;           
}

public function getSeoDescriptionAttribute(): string
{

    return $this->meta_description ?: Str::limit($this->plainText(), 157);
}

public function getSeoKeywordsAttribute(): ?string
{
    if ($this->meta_keywords) {
        return $this->meta_keywords;
    }

  
    return $this->relationLoaded('tags') ? $this->tags->pluck('name')->implode(', ') : null;
}

public function getSeoImageAttribute(): ?string
{
    $path = $this->meta_image ?: $this->featured;       
    return $path ? asset('storage/' . $path) : null;
}


public function seoJsonLd(): array
{
    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $this->seo_title,
        'description' => $this->seo_description,
        'datePublished' => ($this->approved_at ?? $this->created_at)->toIso8601String(),
        'dateModified' => $this->updated_at->toIso8601String(),
        'author' => ['@type' => 'Person', 'name' => $this->user->name],
        'mainEntityOfPage' => route('post.show', $this->slug),
    ];

    if ($this->seo_image) {
        $data['image'] = [$this->seo_image];
    }

    return $data;
}
    

    public function scopeApproved($query)
{
    return $query->where('status', 'approved');
}

public function comments()
{
    return $this->hasMany(Comment::class);
}

public function reactions()
{
    return $this->hasMany(Reaction::class);
}

public function fillSeo(): void
{
    $manual = $this->meta_manual ?? [];
    $contentChanged = $this->isDirty(['title', 'content']);

    if (! in_array('meta_title', $manual, true) && ($contentChanged || ! $this->meta_title)) {
        $this->meta_title = Seo::title((string) $this->title);
    }

    if (! in_array('meta_description', $manual, true) && ($contentChanged || ! $this->meta_description)) {
        $this->meta_description = Seo::description($this->plainText(), (string) $this->title);
    }

    if (! in_array('meta_image', $manual, true)
        && ($this->isDirty('featured') || ($this->featured && ! $this->meta_image))) {
        $this->meta_image = Seo::image($this->featured, $this->meta_image);
    }
}


public function refreshKeywords(): void
{
    if (in_array('meta_keywords', $this->meta_manual ?? [], true)) {
        return;   
    }

    $this->load(['tags:id,name', 'category:id,name']);  

    $this->meta_keywords = Seo::keywords(
        (string) $this->title,
        $this->plainText(),
        $this->tags->pluck('name')->all(),
        $this->category?->name
    );

    $this->saveQuietly(); 
}
}