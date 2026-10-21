<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Activity;

class Role extends Model
{
    public function getRouteKeyName(): string
{
    return 'slug';
}
    protected $fillable = ['name', 'slug', 'description', 'permissions', 'is_system'];
    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean',
    ];
}