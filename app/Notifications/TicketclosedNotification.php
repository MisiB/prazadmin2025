<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketclosedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $name;
    public $surname;
    public $ticketnumber;
    public $comment;
    public function __construct($name,$surname,$ticketnumber,$comment)
    {
        $this->name=$name;
        $this->surname=$surname;
        $this->ticketnumber=$ticketnumber;
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
        ->subject('PRAZ Issue ticket Resolved and Closed')
        ->greeting('Good day'.$this->name." ".$this->surname)
        ->line("Your Issue with  Ticket number :  ".$this->ticketnumber." has been resolved and closed with the following comment")
       ->line('Comment: '.$this->comment)
       ->line('If the issue is not resolved please contact us on the follow email egpsupport@praz.org.zw');
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
