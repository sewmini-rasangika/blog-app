<?php

use App\Http\Controllers\AuthorProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\MailSettingsController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReactionController;  
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ActivityLogController;
use App\Support\Activity;


Route::post('/post/{post:slug}/react', [ReactionController::class, 'store'])
    ->middleware('throttle:30,1')->name('post.react');
Route::get('notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');


Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::get('/post/{post:slug}', [FrontendController::class, 'show'])->name('post.show');
Route::get('/category/{category:slug}', [FrontendController::class, 'category'])->name('category.show');
Route::get('/tag/{tag:slug}', [FrontendController::class, 'tag'])->name('tag.show');
Route::get('/author/{user:slug}', [FrontendController::class, 'author'])->name('author.show');
Route::get('/search', [FrontendController::class, 'search'])->name('search');



Route::post('/post/{post:slug}/comments', [CommentController::class, 'store'])
    ->middleware('throttle:6,1')->name('comments.store');



Route::get(config('admin.path'), fn () => redirect()->route('dashboard'));


Route::prefix('{panel}')
    ->middleware('panel')
    ->group(function () {

    Route::middleware('auth')->group(function () {

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('my-profile', [AuthorProfileController::class, 'edit'])->name('myprofile.edit');
        Route::put('my-profile', [AuthorProfileController::class, 'update'])->name('myprofile.update');

        
        Route::resource('posts', PostController::class)->except('show');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('api-tokens', [\App\Http\Controllers\ApiTokenController::class, 'index'])->name('tokens.index');
        Route::post('api-tokens', [\App\Http\Controllers\ApiTokenController::class, 'store'])
            ->middleware('throttle:10,1')->name('tokens.store');
        Route::delete('api-tokens/{id}', [\App\Http\Controllers\ApiTokenController::class, 'destroy'])->name('tokens.destroy');
      
        Route::middleware('can:posts.approve')->group(function () {
            Route::put('posts/{post}/approve', [PostController::class, 'approve'])->name('posts.approve');
            Route::put('posts/{post}/reject', [PostController::class, 'reject'])->name('posts.reject');
        });

        Route::middleware('can:posts.trash')->group(function () {
            Route::get('trash', [PostController::class, 'trash'])->name('posts.trash');
            Route::put('trash/{id}/restore', [PostController::class, 'restore'])->name('posts.restore');
            Route::delete('trash/{id}/force', [PostController::class, 'forceDelete'])->name('posts.force');
        });

        Route::middleware('can:comments.moderate')->group(function () {
            Route::get('comments', [CommentController::class, 'index'])->name('comments.index');
            Route::put('comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve');
            Route::put('comments/{comment}/spam', [CommentController::class, 'spam'])->name('comments.spam');
            Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
        });
        Route::get('activity', [ActivityLogController::class, 'index'])
    ->middleware('can:activity.view')->name('activity.index');

        Route::resource('categories', CategoryController::class)->except('show')
            ->middleware('can:categories.manage');

        Route::resource('tags', TagController::class)->except('show')
            ->middleware('can:tags.manage');

        Route::middleware('can:users.manage')->group(function () {
            Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'destroy']);
            Route::put('users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
        });

        Route::middleware('can:settings.manage')->group(function () {
            Route::get('settings/mail', [MailSettingsController::class, 'edit'])->name('settings.mail');
            Route::post('settings/mail', [MailSettingsController::class, 'update'])->name('settings.mail.update');
            Route::post('settings/mail/test', [MailSettingsController::class, 'test'])
                ->middleware('throttle:5,1')->name('settings.mail.test');
        });
    });

    Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('roles', RoleController::class)->except('show');
    Route::resource('permissions', \App\Http\Controllers\PermissionController::class)->except(['index', 'show']);
    });
});


require __DIR__ . '/auth.php';