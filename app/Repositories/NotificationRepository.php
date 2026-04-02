<?php

namespace App\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;

class NotificationRepository extends Repository
{
    /**
     * Get all topic with optional filters.
     */
    public function getNotifications(): LengthAwarePaginator
    {
        $user = Auth::user();
        // Check if the user has any notifications
        if ($user->notifications->isEmpty()) {
            return new LengthAwarePaginator([], 0, $this->_limit, Paginator::resolveCurrentPage(), [
                'path' => Paginator::resolveCurrentPath(),
            ]);
        }

        return $user->notifications->toQuery()->orderByDesc('id')->paginate($this->_limit);
    }
}
