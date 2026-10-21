<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

class Panel
{

    public const RESERVED = [
        'post', 'category', 'tag', 'author', 'search', 'notifications', 'login', 'register', 'logout',
        'forgot-password', 'reset-password', 'verify-email', 'email', 'confirm-password', 'password',
        'up', 'storage', 'build', 'css', 'js', 'images', 'vendor', 'api', 'admin', 'user', 'dashboard',
    ];

    public static function isReserved(string $slug): bool
    {
        return in_array($slug, array_merge(self::RESERVED, [config('admin.path'), config('admin.user_path')]), true);
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'author';

        if (self::isReserved($base)) {
            $base .= '-user';
        }

        $slug = $base;
        $i = 2;

        while (User::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }


    public static function isStaff(User $user): bool
    {
        return $user->isAdmin() || $user->canAny([
            'posts.approve', 'posts.view_all', 'categories.manage', 'tags.manage',
            'users.manage', 'comments.moderate', 'posts.trash', 'settings.manage',
        ]);
    }


    public static function for(?User $user): string
    {
        if ($user && ! self::isStaff($user)) {
            return $user->slug ?: config('admin.user_path');
        }

        return config('admin.path');
    }
}
