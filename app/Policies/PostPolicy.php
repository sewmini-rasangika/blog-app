<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{

    public function update(User $user, Post $post): bool
    {
        return $user->hasPermission('posts.edit_any')
            || ($user->id === $post->user_id && $user->hasPermission('posts.create'));
    }


    public function delete(User $user, Post $post): bool
    {
        return $this->update($user, $post);
    }
}
