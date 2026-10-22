<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Models\Category;
use App\Models\Tag;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1')->name('api.')->group(function () {

   
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');
    Route::get('categories', fn () => Category::withCount(['posts' => fn ($q) => $q->approved()])
        ->orderBy('name')->get(['id', 'name', 'slug']));
    Route::get('tags', fn () => Tag::orderBy('name')->get(['id', 'name', 'slug']));

    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);

        Route::get('my/posts', [PostController::class, 'mine'])->name('posts.mine');
        Route::post('posts', [PostController::class, 'store'])->name('posts.store');
        Route::put('posts/{post:slug}', [PostController::class, 'update'])->name('posts.update');
        Route::delete('posts/{post:slug}', [PostController::class, 'destroy'])->name('posts.destroy');
    });
});