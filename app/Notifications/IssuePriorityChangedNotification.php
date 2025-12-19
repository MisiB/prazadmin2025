<?php

namespace App\Notifications;

use App\Models\Issuelog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IssuePriorityChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $issue;

    public $oldPriority;

    public $newPriority;

    /**
     * Create a new notification instance.
     */
    public function __construct(Issuelog $issue, string $oldPriority, string $newPriority)
    {
        $this->issue = $issue;
        $this->oldPriority = $oldPriority;
        $this->newPriority = $newPriority;
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
        $priorityColors = [
            'Low' => 'green',
            'Medium' => 'yellow',
            'High' => 'red',
        ];

        $newPriorityColor = $priorityColors[$this->newPriority] ?? 'gray';

        return (new MailMessage)
            ->subject('Issue Priority Updated - '.$this->issue->ticketnumber)
            ->greeting('Hello '.($notifiable->name ?? $this->issue->name).',')
            ->line('The priority of your issue ticket has been updated.')
            ->line('**Ticket Number:** '.$this->issue->ticketnumber)
            ->line('**Title:** '.$this->issue->title)
            ->line('**Priority Changed:** '.$this->oldPriority.' → **'.$this->newPriority.'**')
            ->line('**Current Status:** '.ucfirst(str_replace('_', ' ', $this->issue->status)))
            ->action('View Issue', route('admin.issues'))
            ->line('This change may affect the response time for your issue.');
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
            'old_priority' => $this->oldPriority,
            'new_priority' => $this->newPriority,
        ];
    }
}
