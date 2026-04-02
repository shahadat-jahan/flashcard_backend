<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovedPostNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Post $post;

    private ?User $approvedBy;

    private string $topics;

    /**
     * Create a new notification instance.
     */
    public function __construct(Post $post)
    {
        $this->afterCommit();
        $this->post = $post;
        $this->approvedBy = $post->approvedBy;
        $this->topics = implode(', ', array_column($post->topics->toArray(), 'name'));
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'post_id' => $this->post['id'],
            'message' => $this->getMessage(),
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = frontendUrl('posts', ['id' => $this->post['id']]);

        return (new MailMessage)
            ->subject($this->getMessage())
            ->view('emails.approved-post-template', [
                'notifiable' => $notifiable,
                'approvedBy' => $this->approvedBy,
                'post' => $this->post,
                'topics' => $this->topics,
                'url' => $url,
            ]);
    }

    private function getMessage(): string
    {
        return sprintf(
            'Your post has been approved by %s.',
            $this->approvedBy->name
        );
    }
}
