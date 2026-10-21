<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'display_name', 'description', 'group', 'is_system', 'is_sensitive'];

    protected $casts = [
        'is_system' => 'boolean',
        'is_sensitive' => 'boolean',
    ];
}
