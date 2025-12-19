<?php

namespace App\Notifications;

use App\Models\Issuelog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IssueStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $issue;

    public $oldStatus;

    public $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Issuelog $issue, string $oldStatus, string $newStatus)
    {
        $this->issue = $issue;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
        $statusLabels = [
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
        ];

        $oldStatusLabel = $statusLabels[$this->oldStatus] ?? ucfirst(str_replace('_', ' ', $this->oldStatus));
        $newStatusLabel = $statusLabels[$this->newStatus] ?? ucfirst(str_replace('_', ' ', $this->newStatus));

        return (new MailMessage)
            ->subject('Issue Status Updated - '.$this->issue->ticketnumber)
            ->greeting('Hello '.($notifiable->name ?? $this->issue->name).',')
            ->line('The status of your issue ticket has been updated.')
            ->line('**Ticket Number:** '.$this->issue->ticketnumber)
            ->line('**Title:** '.$this->issue->title)
            ->line('**Status Changed:** '.$oldStatusLabel.' → '.$newStatusLabel)
            ->line('**Priority:** '.$this->issue->priority)
            ->action('View Issue', route('admin.issues'))
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
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
        ];
    }
}
