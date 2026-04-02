<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserMentionedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Comment $comment;

    protected User $mentionedBy;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
        $this->mentionedBy = $comment->user;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];  // Use both database and email notifications
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = frontendUrl('posts', ['id' => $this->comment->post_id]);

        return (new MailMessage)
            ->subject($this->getMessage())
            ->greeting('Hello '.$notifiable->name)
            ->line($this->getMessage())
            ->action('View Comment', $url);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'comment_id' => $this->comment->id,
            'mentioned_by' => $this->mentionedBy->name,
            'message' => $this->getMessage(),
        ];
    }

    private function getMessage(): string
    {
        return sprintf(
            '%s mentioned you in a comment.',
            $this->mentionedBy->name
        );
    }
}
