<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Support\Activity;

class ReactionController extends Controller
{
    public function store(Request $request, Post $post)
    {
        abort_unless($post->status === 'approved', 404);

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(Reaction::TYPES))],
        ]);

        $key = Reaction::voterKey($request, create: true);

        $reaction = Reaction::where('post_id', $post->id)->where('voter_key', $key)->first();

        if ($reaction && $reaction->type === $data['type']) {
            $reaction->delete();                        
            $mine = null;
        } elseif ($reaction) {
            $reaction->update(['type' => $data['type']]); 
            $mine = $data['type'];
        } else {
            Reaction::create(['post_id' => $post->id, 'voter_key' => $key, 'type' => $data['type']]);
            $mine = $data['type'];
        }

        $counts = Reaction::where('post_id', $post->id)
            ->selectRaw('type, count(*) as c')
            ->groupBy('type')
            ->pluck('c', 'type')
            ->all();

        return response()->json(['mine' => $mine, 'counts' => $counts]);
    }
}