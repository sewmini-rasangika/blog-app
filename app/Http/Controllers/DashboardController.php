<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Models\Comment;
use App\Support\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $cols = ['id', 'user_id', 'category_id', 'title', 'slug', 'status', 'created_at'];

        if ($user->isAdmin()) {
            return view('admin.dashboard', [
                'counts' => Post::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
                'users' => User::count(),
                'categories' => Category::count(),
                'tags' => Tag::count(),
                'trashed' => Post::onlyTrashed()->count(),
                'pendingPosts' => Post::select($cols)->with(['user:id,name', 'category:id,name'])
                    ->where('status', 'pending')->latest()->take(10)->get(),
                'commentsPending' => Comment::where('status', 'pending')->count(),
            ]);
        }

        return view('admin.dashboard-author', [
            'counts' => Post::where('user_id', $user->id)->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
            'recent' => Post::select($cols)->with('category:id,name')
                ->where('user_id', $user->id)->latest()->take(5)->get(),
        ]);
    }
}


