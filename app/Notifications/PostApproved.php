<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Notifications\Notification;

class PostApproved extends Notification
{
    public function __construct(public Post $post)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];   
    }

    public function toArray(object $notifiable): array
    {
        return [
            'post_id' => $this->post->id,
            'slug'    => $this->post->slug,
            'message' => 'Your post "' . $this->post->title . '" was approved and is now live!',
        ];
    }
}