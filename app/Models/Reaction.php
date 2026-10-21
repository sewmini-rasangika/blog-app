<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use App\Support\Activity;

class Reaction extends Model
{
    public const TYPES = [
        'like' => '👍',
        'love' => '❤️',
        'haha' => '😂',
        'wow'  => '😮',
        'sad'  => '😢',
    ];

    protected $fillable = ['post_id', 'voter_key', 'type'];

    public static function voterKey(Request $request, bool $create = false): ?string
    {
        if ($request->user()) {
            return 'u:' . $request->user()->id;
        }

        $token = $request->cookie('visitor');

        if (! $token && $create) {
            $token = Str::random(40);
            Cookie::queue(Cookie::forever('visitor', $token));
        }

        return $token ? 'g:' . $token : null;
    }
}