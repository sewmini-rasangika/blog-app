<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Activity;

class Tag extends Model
{
    public function getRouteKeyName(): string
{
    return 'slug';
}
    protected $guarded = [];

public function posts()
{
    return $this->belongsToMany(Post::class)->withTimestamps();
}
}
