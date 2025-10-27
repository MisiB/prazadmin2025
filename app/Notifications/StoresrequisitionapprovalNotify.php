<?php

namespace App\Notifications;

use App\Interfaces\repositories\istoresrequisitionInterface;
use App\Interfaces\services\istoresrequisitionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoresrequisitionapprovalNotify extends Notification implements ShouldQueue
{
    use Queueable;

    protected $storesrequisitionuuid, $storesrequisitionService;
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
        $status=$storesrequisition->status==='A'?'APPROVED':($storesrequisition->status==='D'?'DELIVERED':'REJECTED');
        return (new MailMessage)
            ->success()
            ->greeting('Dear '.$storesrequisition->initiator->name.' '.$storesrequisition->initiator->surname)
            ->subject('RE: '.$storesrequisition->purposeofrequisition.' stores requisition approved')
            ->line('')
            ->line('The '.$storesrequisition->purposeofrequisition.' stores requisition has been ')
            ->line(''.$status.' by the  HOD('.$storesrequisition->hod->user->name.' '.$storesrequisition->hod->user->surname.')')
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
