<?php

namespace App\Notifications;

use App\Interfaces\ileaverequestapprovalInterface;
use App\Interfaces\ileavetypeInterface;
use App\Models\Leaverequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaverequestWithdrawn extends Notification implements ShouldQueue
{
    use Queueable;

    private $leaverequest, $leaverequestuuid, $approvalrecordid, $approverid;
    protected $leavetyperepo, $leaverequestapprovalrepo;
    /**
     * Create a new notification instance.
     */
    public function __construct(Leaverequest $leaverequest, ileavetypeInterface $leavetyperepo, ileaverequestapprovalInterface $leaverequestapprovalrepo)
    {
        $this->leaverequest = $leaverequest;
        $this->leavetyperepo=$leavetyperepo;
        $this->leaverequestapprovalrepo=$leaverequestapprovalrepo;
        $this->leaverequestuuid=$this->leaverequest->leaverequestuuid;
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
        $leavetype=$this->leavetyperepo->getleavetype($this->leaverequest->leavetype_id);
        $leaveapprovalitemuuid=$this->leaverequestuuid;
        $leaveapproverid=$this->leaverequestapprovalrepo->getleaverequestapproval($this->leaverequestuuid)->user_id;
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
