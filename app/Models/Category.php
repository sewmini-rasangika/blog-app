<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public function getRouteKeyName(): string
{
    return 'slug';
}
    protected $guarded = [];

public function posts()
{
    return $this->hasMany(Post::class);
}
}
