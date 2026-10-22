<?php

namespace App\Http\Controllers;

use App\Support\Activity;
use Illuminate\Http\Request;

class ApiTokenController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.tokens.index', [
            'tokens' => $request->user()->tokens()->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
     $data = $request->validate([
        'name'       => 'required|string|max:60',
        'expires_at' => 'nullable|date|after:today|before:+5 years',
    ]);

    $expires = ! empty($data['expires_at'])
        ? \Illuminate\Support\Carbon::parse($data['expires_at'])->endOfDay()   
        : null;                                                                

    $token = $request->user()->createToken($data['name'], ['*'], $expires);

    $label = $expires ? 'expires ' . $expires->toDateString() : 'no expiry';
    Activity::log('auth.token_created', "Created API token \"{$data['name']}\" ({$label})");

    return redirect()->route('tokens.index')->with('new_token', $token->plainTextToken);
    }

    public function destroy(Request $request, int $id)
    {
        $token = $request->user()->tokens()->whereKey($id)->firstOrFail(); 
        $name = $token->name;
        $token->delete();

        Activity::log('auth.token_revoked', "Revoked API token \"{$name}\"");

        return back()->with('success', 'Token revoked.');
    }
}