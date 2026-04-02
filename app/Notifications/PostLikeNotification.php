<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostLikeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected User $user, protected Post $post)
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
        $url = frontendUrl('posts', ['id' => $this->post->id]);

        return (new MailMessage)
            ->subject($this->getMessage())
            ->greeting('Hello '.$notifiable->name)
            ->line($this->getMessage())
            ->action('View Post', $url);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'post_id' => $this->post->id,
            'message' => $this->getMessage(),
        ];
    }

    private function getMessage(): string
    {
        return sprintf('%s liked your post.', $this->user->name);
    }
}
