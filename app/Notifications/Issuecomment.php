<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Issuecomment extends Notification implements ShouldQueue
{
    use Queueable;
    public $name;
    public $surname;
    public $ticketnumber;
    public $comment;
    public function __construct($comment,$ticketnumber,$name=null,$surname=null)
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
        return ['database','mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                     ->greeting('Good day')
                     ->line('User: '.$this->name." ".$this->surname)
                     ->line("Issue Ticket:  ".$this->ticketnumber)
                    ->line('Comment: '.$this->comment)
                    ->line('Please login to PRAZ internal portal to access your issue ticket');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'ticketnumber' => $this->ticketnumber,
            'name' => $this->name,
            'surname' => $this->surname,
            'comment'=>$this->comment
        ];
    }
    public function databaseType(object $notifiable): string
    {
        return 'issue comment';
    }


}
