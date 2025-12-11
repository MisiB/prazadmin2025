<?php

namespace App\Notifications;

use App\Interfaces\repositories\itaskInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskSubmittedForApproval extends Notification implements ShouldQueue
{
    use Queueable;

    protected $calendarweekId;
    protected $userId;
    protected $taskRepository;

    public function __construct(itaskInterface $taskRepository, $calendarweekId, $userId)
    {
        $this->taskRepository = $taskRepository;
        $this->calendarweekId = $calendarweekId;
        $this->userId = $userId;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $user = \App\Models\User::find($this->userId);
        $approvalUrl = route('admin.workflows.approvals.weekytasks');

        return (new MailMessage)
            ->success()
            ->greeting('Good day from PRAZ')
            ->subject('RE: TASKS SUBMITTED FOR APPROVAL')
            ->line('')
            ->line('Tasks have been submitted for approval by ' . $user->name . ' ' . $user->surname)
            ->line('')
            ->action('Review Tasks', $approvalUrl)
            ->line('')
            ->line('Please review and approve the submitted tasks.')
            ->line('Thank you for using our application, we are here to serve!')
            ->line('');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
