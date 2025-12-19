<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewticketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $name;

    public $surname;

    public $ticketnumber;

    public $comment;

    public function __construct($name, $ticketnumber, $comment)
    {
        $this->name = $name;
        $this->ticketnumber = $ticketnumber;
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
            ->subject('PRAZ New Issue Ticket - '.$this->ticketnumber)
            ->greeting('Good day '.$this->name.',')
            ->line('Your issue has been successfully logged in our issue management system.')
            ->line('**Ticket Number:** '.$this->ticketnumber)
            ->line('**Issue Description:**')
            ->line($this->comment)
            ->line('You can use the ticket number above to track the status of your issue.')
            ->action('View Your Tickets', route('admin.issues'))
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
            'ticketnumber' => $this->ticketnumber,
            'name' => $this->name,
        ];
    }
}
