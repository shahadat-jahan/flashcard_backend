<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignPostNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private ?User $assignBy;

    private Post $post;

    private string $topics;

    private string $dueDate;

    /**
     * Create a new notification instance.
     */
    public function __construct(Post $post)
    {
        $this->afterCommit();
        $this->assignBy = $post->createdBy;
        $this->post = $post;
        $this->topics = implode(', ', array_column($post->topics->toArray(), 'name'));
        $this->dueDate = isset($post->task->due_date) ? Carbon::parse($post->task->due_date)->format('d M Y') : null;
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
            'post_id' => $this->post->id,
            'message' => $this->getMessage(),
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = frontendUrl('posts', ['id' => $this->post->id]);

        return (new MailMessage)
            ->subject($this->getMessage())
            ->view('emails.assign-post-template', [
                'notifiable' => $notifiable,
                'assignBy' => $this->assignBy,
                'post' => $this->post,
                'topics' => $this->topics,
                'dueDate' => $this->dueDate,
                'url' => $url,
            ]);
    }

    private function getMessage(): string
    {
        return sprintf(
            'You have been assigned a new post by %s.',
            $this->assignBy->name
        );
    }
}
