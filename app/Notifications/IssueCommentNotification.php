<?php

namespace App\Notifications;

use App\Models\Issuecomment;
use App\Models\Issuelog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IssueCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $comment;

    public $issue;

    /**
     * Create a new notification instance.
     */
    public function __construct(Issuecomment $comment, Issuelog $issue)
    {
        $this->comment = $comment;
        $this->issue = $issue;
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
            ->subject('New Comment on Your Issue Ticket: '.$this->issue->ticketnumber)
            ->greeting('Hello '.$this->issue->name.',')
            ->line('A new comment has been added to your issue ticket.')
            ->line('**Ticket #:** '.$this->issue->ticketnumber)
            ->line('**Title:** '.$this->issue->title)
            ->line('**Comment by:** '.$this->comment->user_email)
            ->line('**Comment:**')
            ->line($this->comment->comment)
            ->line('Thank you for your patience while we work on resolving your issue.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'issuelog_id' => $this->issue->id,
            'ticketnumber' => $this->issue->ticketnumber,
            'comment_id' => $this->comment->id,
        ];
    }
}
