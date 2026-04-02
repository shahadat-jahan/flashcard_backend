<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserFollowedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected User $follower)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = frontendUrl('users', ['id' => $this->follower->id]);

        return (new MailMessage)
            ->subject($this->getMessage())
            ->greeting('Hello '.$notifiable->name)
            ->line($this->getMessage())
            ->action('View User', $url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'follower_id' => $this->follower->id,
            'message' => $this->getMessage(),
        ];
    }

    private function getMessage(): string
    {
        return sprintf('%s following you.', $this->follower->name);
    }
}
