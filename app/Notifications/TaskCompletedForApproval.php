<?php

namespace App\Notifications;

use App\Interfaces\repositories\itaskInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCompletedForApproval extends Notification implements ShouldQueue
{
    use Queueable;

    protected $taskId;
    protected $taskRepository;

    public function __construct(itaskInterface $taskRepository, $taskId)
    {
        $this->taskRepository = $taskRepository;
        $this->taskId = $taskId;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $task = $this->taskRepository->gettask($this->taskId);
        $user = $task->user;
        $approvalUrl = route('admin.workflows.approvals.weekytasks');

        return (new MailMessage)
            ->success()
            ->greeting('Good day from PRAZ')
            ->subject('RE: TASK COMPLETED - AWAITING APPROVAL')
            ->line('')
            ->line('A task has been marked as completed by ' . $user->name . ' ' . $user->surname . ' and requires your approval.')
            ->line('')
            ->line('Task: ' . $task->title)
            ->line('')
            ->action('Review Task', $approvalUrl)
            ->line('')
            ->line('Please review and approve the completed task.')
            ->line('Thank you for using our application, we are here to serve!')
            ->line('');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
