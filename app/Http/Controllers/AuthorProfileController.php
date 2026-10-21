<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Support\Activity;

class AuthorProfileController extends Controller
{
    public function edit()
    {
        $profile = auth()->user()->profile()->firstOrCreate([]);
        return view('admin.profile.edit', compact('profile'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'avatar' => 'nullable|image|max:2048',
            'about' => 'nullable|max:1000',
            'facebook' => 'nullable|url|max:255',
        ]);

        $profile = auth()->user()->profile()->firstOrCreate([]);

        if ($request->hasFile('avatar')) {
            if ($profile->avatar) {
                Storage::disk('public')->delete($profile->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $profile->update($data);

        return redirect()->route('myprofile.edit')->with('success', 'Profile updated successfully ✨');
    }
}
