<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TsAllowanceSendBack extends Notification implements ShouldQueue
{
    use Queueable;

    public $record;

    public $comment;

    public function __construct($record, $comment)
    {
        $this->record = $record;
        $this->comment = $comment;
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
            ->subject('T&S Allowance Sent Back for Corrections')
            ->greeting('Good day '.$notifiable->name)
            ->line('Your T&S Allowance application has been sent back for corrections.')
            ->line('Application Number: '.$this->record['application_number'])
            ->line('Reason for Return:')
            ->line($this->comment)
            ->line('Please review the comments, make the necessary corrections, and resubmit your application.')
            ->action('View Application', url('/workflows/ts-allowance/'.$this->record['uuid']))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'application_number' => $this->record['application_number'],
            'uuid' => $this->record['uuid'],
            'comment' => $this->comment,
        ];
    }
}
