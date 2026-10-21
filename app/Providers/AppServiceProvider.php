<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\Comment;
use App\Support\MailSettings;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use App\Support\Activity;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        MailSettings::apply();


Gate::before(fn (User $user) => $user->isAdmin() ? true : null);


foreach (\App\Support\Permissions::keys() as $permission) {
    Gate::define($permission, fn (User $user) => $user->hasPermission($permission));
}
        View::composer('layouts.frontend', function ($view) {
            $view->with([
                'navCategories' => Category::withCount(['posts' => fn ($q) => $q->approved()])->orderBy('name')->get(),
                'sideTags' => Tag::orderBy('name')->get(),
                'popularPosts' => Post::approved()->orderByDesc('views')->take(5)->get(),
                
            ]);
        });


        View::composer('layouts.admin', function ($view) {
            
            $user = auth()->user();
            $user?->loadMissing('profile');

            $view->with([
                'pendingCount' => $user?->isAdmin() ? Post::where('status', 'pending')->count() : 0,
                'pendingComments' => $user?->isAdmin() ? Comment::where('status', 'pending')->count() : 0,
                'meAvatar' => $user?->profile?->avatar,
                'pendingCount' => $user?->isAdmin() ? Post::where('status', 'pending')->count() : 0,
                'meAvatar' => $user?->profile?->avatar,
            ]);
        });

                Event::listen(Login::class, fn (Login $e) => Activity::log('auth.login', 'Logged in', null, [], $e->user));

        Event::listen(Logout::class, function (Logout $e) {
            if ($e->user) {
                Activity::log('auth.logout', 'Logged out', null, [], $e->user);
            }
        });

        Event::listen(Failed::class, fn (Failed $e) => Activity::log(
            'auth.failed', 'Failed login attempt', null, ['email' => $e->credentials['email'] ?? null]
        ));
    }
}
    

