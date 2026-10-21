<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use App\Support\Activity;

class ProfileController extends Controller
{

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    
   public function destroy(Request $request): RedirectResponse
{
    $request->validateWithBag('userDeletion', [
        'password' => ['required', 'current_password'],
    ]);

    $user = $request->user();


    if ($user->isAdmin()) {
        return back()->with('error', 'Do not delete administrator account.');
    }

    
    $user->loadMissing('profile');
    if ($user->profile?->avatar) {
        Storage::disk('public')->delete($user->profile->avatar);
    }
   Post::withTrashed()->where('user_id', $user->id)->get(['featured', 'meta_image'])
    ->flatMap(fn ($p) => [$p->featured, $p->meta_image])
    ->filter()
    ->each(fn ($file) => Storage::disk('public')->delete($file));

    Auth::logout();

    $user->delete();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return Redirect::to('/');
}
}
