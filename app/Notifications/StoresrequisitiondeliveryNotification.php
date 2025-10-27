<?php

namespace App\Notifications;

use App\Interfaces\services\istoresrequisitionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoresrequisitiondeliveryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $storesrequisitionService, $storesrequisitionuuid;
    public function __construct(istoresrequisitionService $storesrequisitionService, $storesrequisitionuuid)
    {
        $this->storesrequisitionService=$storesrequisitionService;
        $this->storesrequisitionuuid=$storesrequisitionuuid;
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
        $storesrequisition=$this->storesrequisitionService->getstoresrequisition($this->storesrequisitionuuid);
        

        $leaveapprovalitemuuid='N';
        $leaveapproverid='N';
        $storesapprovalitemuuid=$this->storesrequisitionuuid;
        $storesapproverid=$storesrequisition->receiver->user->id;
        $status=$storesrequisition->status;
        $statusdetail=$storesrequisition->status==='D'?'DELIVERED':'REJECTED';
        $finalizationurl=url('approval/'.$leaveapprovalitemuuid.'/'.$leaveapproverid.'/'.$storesapprovalitemuuid.'/'.$storesapproverid.'/'.$status);
        return (new MailMessage)
            ->success()
            ->greeting('Dear '.$storesrequisition->initiator->name.' '.$storesrequisition->initiator->surname)
            ->subject('RE: '.$storesrequisition->purposeofrequisition.'  stores requisition delivery')
            ->line('')
            ->line('The '.$storesrequisition->purposeofrequisition.' stores requisition has been ')
            ->line($statusdetail.' by the from the Admin Chair ('.$storesrequisition->adminvalidator->user->name.' '.$storesrequisition->adminvalidator->user->surname.')')
            ->line('')
            ->action('Accept',$finalizationurl)
            ->line('')
            ->line('REF #:'.$storesrequisition->storesrequisition_uuid)
            ->line('Thank you for using our application, we are here to serve!')
            ->line('');
    }
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
