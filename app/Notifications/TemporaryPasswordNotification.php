<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TemporaryPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;
    private $temporaryPassword;

    /**
     * Create a new notification instance.
     */
    public function __construct($temporaryPassword)
    {
        $this->temporaryPassword = $temporaryPassword;
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
        return (new MailMessage)
            ->subject('Your Temporary Password')
            ->line('Welcome to ' . config('app.name') . '!')
            ->line('Your account has been created. Please use the following temporary password to log in:')
            ->line($this->temporaryPassword)
            ->line('For security reasons, please change your password after logging in.')
            ->action('Login Now', url('/login'))
            ->line('If you did not create this account, please contact the administrator.');

    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
