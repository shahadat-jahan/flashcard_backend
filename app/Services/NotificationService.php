<?php

namespace App\Services;

use App\Enums\ServiceResultType as ResultType;
use App\Repositories\NotificationRepository;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;

class NotificationService extends Service
{
    public function __construct(private readonly NotificationRepository $repository)
    {
        parent::__construct();
    }

    /**
     * Get topic list
     *
     * @param  Request  $request
     */
    public function getNotifications(): ServiceResult
    {
        try {
            $notifications = $this->repository->getNotifications();

            $this->result->setData($notifications);
        } catch (Exception $exception) {
            $message = 'Failed to fetch notifications';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    public function readNotification(DatabaseNotification $notification): ServiceResult
    {
        try {
            if ($notification->unread()) {
                $notification->markAsRead();
            }

            $this->result->setData($notification);
        } catch (Exception $exception) {
            $message = 'Failed to mark notification as read';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    public function readAllNotifications(): ServiceResult
    {
        try {
            $user = Auth::user();
            $unreadNotifications = $user->unreadNotifications;

            if ($unreadNotifications) {
                $unreadNotifications->markAsRead();
            }

            $this->result->setData([
                'messages' => 'All unread notifications are marked as read',
                'count' => $unreadNotifications->count(),
            ], ResultType::META);
        } catch (Exception $exception) {
            $message = 'Failed to mark all notifications as read';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }
}
