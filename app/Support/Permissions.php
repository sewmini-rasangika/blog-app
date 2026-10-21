<?php

namespace App\Support;

use App\Models\Permission;
use Throwable;


class Permissions
{
    private static ?array $sensitive = null;

  
    public static function groups(): array
    {
        try {
            $groups = [];
            foreach (Permission::orderBy('id')->get() as $p) {
                $groups[$p->group ?: 'Other'][$p->name] = $p->display_name;
            }

            return $groups ?: config('permissions.groups');
        } catch (Throwable) {
            return config('permissions.groups');
        }
    }

    public static function keys(): array
    {
        return collect(self::groups())->collapse()->keys()->all();
    }

    public static function isSensitive(string $key): bool
    {
        if (self::$sensitive === null) {
            try {
                self::$sensitive = Permission::where('is_sensitive', true)->pluck('name')->all();
            } catch (Throwable) {
                self::$sensitive = config('permissions.sensitive', []);
            }
        }

        return in_array($key, self::$sensitive, true);
    }
}
