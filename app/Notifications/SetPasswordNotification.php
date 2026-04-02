<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = frontendUrl('set-password/'.$this->token, ['email' => $notifiable->email]);

        return (new MailMessage)
            ->subject('Welcome! Please Join the FlashCard System')
            ->view('emails.set-password-template', [
                'notifiable' => $notifiable,
                'setPasswordUrl' => $url,
            ]);
    }
}
