<?php

namespace App\Notifications;

use App\Interfaces\services\ileaverequestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaverequestSubmitted extends Notification implements ShouldQueue
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
        $leaveapprovalitemuuid=$this->leaverequestuuid;
        $leaveapproverid=$this->leaverequestService->getleaverequestapproval($this->leaverequestuuid)->user_id;
        $storesapprovalitemuuid='N';
        $storesapproverid='N';
        $status='N';
        $finalizationurl=url('approval/'.$leaveapprovalitemuuid.'/'.$leaveapproverid.'/'.$storesapprovalitemuuid.'/'.$storesapproverid.'/'.$status);

        return (new MailMessage)
            ->success()
            ->greeting('Good day from PRAZ')
            ->subject('RE: EMPLOYEE LEAVE REQUEST SUBMISSION')
            ->line('')
            ->line('A new '.$leavetype->name.' leave request has been submitted by '.$this->leaverequest->user->name.' '.$this->leaverequest->user->surname)
            ->line('')
            ->action('Make decision', $finalizationurl)
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