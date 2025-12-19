<?php

namespace App\Notifications;

use App\Models\Issuelog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IssueAssignedNotification extends Notification implements ShouldQueue
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
            ->subject('New Issue Assigned to You - '.$this->issue->ticketnumber)
            ->greeting('Hello '.$notifiable->name.',')
            ->line('A new issue has been assigned to you.')
            ->line('**Ticket Number:** '.$this->issue->ticketnumber)
            ->line('**Title:** '.$this->issue->title)
            ->line('**Priority:** '.$this->issue->priority)
            ->line('**Status:** '.ucfirst(str_replace('_', ' ', $this->issue->status)))
            ->line('**Issue Group:** '.($this->issue->issuegroup->name ?? 'N/A'))
            ->line('**Issue Type:** '.($this->issue->issuetype->name ?? 'N/A'))
            ->line('**Description:**')
            ->line($this->issue->description)
            ->action('View Issue', route('admin.issues'))
            ->line('Please review and take appropriate action.');
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
            'priority' => $this->issue->priority,
        ];
    }
}
