<?php

namespace App\Notifications;

use App\Interfaces\services\ileaverequestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaverequestWithdrawn extends Notification implements ShouldQueue
{
    use Queueable;

    private $leaverequest, $leaverequestuuid, $approvalrecordid, $approverid;
    protected $leaverequestService, $leaverequestapprovalrepo;
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
        //approval url
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
            ->subject('RE: LEAVE REQUEST WITHDRAWAL')
            ->line('')
            ->line('A '.$leavetype->name.' leave request has been withdrawn by '.$this->leaverequest->user->name.' '.$this->leaverequest->user->surname)
            ->line('')
            ->action('View Details', $finalizationurl)
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