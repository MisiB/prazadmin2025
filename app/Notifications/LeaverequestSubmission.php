<?php

namespace App\Notifications;

use App\Interfaces\services\ileaverequestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaverequestSubmission extends Notification implements ShouldQueue
{
    use Queueable;

    private $leaverequest, $leaverequestuuid, $approvalrecordid, $approverid;
    protected $leaverequestapprovalrepo, $leaverequestService;
    /**
     * Create a new notification instance.
     */
    public function __construct(ileaverequestService $leaverequestService, $leaverequestuuid)
    {
        
        $this->leaverequestService=$leaverequestService;
        $this->leaverequest = $leaverequestService->getleaverequestbyuuid($leaverequestuuid);
        $this->leaverequestuuid=$leaverequestuuid;
    }
 
    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */ 
    public function toMail($notifiable): MailMessage
    {
        $leavetype=$this->leaverequestService->getleavetype($this->leaverequest->leavetype_id);

        return (new MailMessage)
            ->success()
            ->greeting('Good day from PRAZ')
            ->subject('RE: YOUR LEAVE REQUEST SUBMISSION')
            ->line('')
            ->line('Dear '.$this->leaverequest->user->name.' '.$this->leaverequest->user->surname.' you submitted a new '.$leavetype->name.' leave request')
            ->line('')
            ->line('REF #:'.$this->leaverequest->leaverequestuuid)
            ->line('Thank you for using our application, we are here to serve!')
            ->line('');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [];
    }
}