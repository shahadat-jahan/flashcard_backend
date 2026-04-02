<?php

namespace App\Policies;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool
    {
        return $user->isAdmin() || $user->id === $post->author_id || $post->status === PostStatus::APPROVED;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        return $post->status !== PostStatus::PENDING && (($user->isAdmin()) || ($user->id === $post->author_id));
    }

    /**
     * Determine whether the user can update status the model.
     */
    public function updateStatus(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update status (bulk) the model.
     */
    public function bulkUpdateStatus(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->isAdmin() || ($user->id === $post->author_id && $post->status !== PostStatus::APPROVED);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Post $post): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $user->isAdmin();
    }
}
