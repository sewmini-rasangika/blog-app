<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Support\Activity;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];


    public static function map(): array
    {
        try {
            return Cache::rememberForever('app_settings', fn () => static::query()->pluck('value', 'key')->all());
        } catch (\Throwable $e) {
            return [];   
        }
    }

    public static function read(string $key, $default = null)
    {
        return static::map()[$key] ?? $default;
    }


    public static function write(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget('app_settings');
    }
}