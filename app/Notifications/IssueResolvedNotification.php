<?php

namespace App\Notifications;

use App\Models\Issuelog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IssueResolvedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $issue;

    /**
     * Create a new notification instance.
     */
    public function __construct(Issuelog $issue)
    {
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
            ->subject('Issue Ticket Resolved - '.$this->issue->ticketnumber)
            ->greeting('Hello '.$this->issue->name.',')
            ->line('Great news! Your support ticket has been marked as resolved.')
            ->line('**Ticket Number:** '.$this->issue->ticketnumber)
            ->line('**Title:** '.$this->issue->title)
            ->line('**Priority:** '.$this->issue->priority)
            ->line('**Issue Group:** '.($this->issue->issuegroup->name ?? 'N/A'))
            ->line('**Issue Type:** '.($this->issue->issuetype->name ?? 'N/A'))
            ->line('If you have any further questions or if the issue persists, please don\'t hesitate to create a new ticket.')
            ->action('View Ticket Details', route('admin.issues'))
            ->line('Thank you for your patience!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'issue_id' => $this->issue->id,
            'ticketnumber' => $this->issue->ticketnumber,
            'title' => $this->issue->title,
        ];
    }
}
