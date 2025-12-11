<?php

namespace App\Notifications;

use App\Interfaces\repositories\itaskInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskApproved extends Notification implements ShouldQueue
{
    use Queueable;

    protected $taskId;
    protected $taskRepository;
    protected $approvalStatus;

    public function __construct(itaskInterface $taskRepository, $taskId, $approvalStatus = 'Approved')
    {
        $this->taskRepository = $taskRepository;
        $this->taskId = $taskId;
        $this->approvalStatus = $approvalStatus;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $task = $this->taskRepository->gettask($this->taskId);
        
        $subject = $this->approvalStatus === 'Approved' 
            ? 'RE: TASK APPROVED' 
            : 'RE: TASK SENT BACK FOR REVISION';
        
        $message = $this->approvalStatus === 'Approved'
            ? 'Your task "' . $task->title . '" has been approved by your supervisor.'
            : 'Your task "' . $task->title . '" has been sent back for revision. Please review and update accordingly.';

        return (new MailMessage)
            ->success()
            ->greeting('Good day from PRAZ')
            ->subject($subject)
            ->line('')
            ->line('Dear ' . $notifiable->name . ' ' . $notifiable->surname . ',')
            ->line('')
            ->line($message)
            ->line('')
            ->line('Task: ' . $task->title)
            ->line('Thank you for using our application, we are here to serve!')
            ->line('');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
