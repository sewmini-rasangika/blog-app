<?php

namespace App\Http\Controllers;

class NotificationController extends Controller
{

    public function read(string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $slug = $notification->data['slug'] ?? null;

        return $slug ? redirect()->route('post.show', $slug) : back();
    }

 
    public function readAll()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back();
    }
}