<?php

namespace App\Notifications;

use App\Interfaces\repositories\iadminstoresrequisitionapprovalInterface;
use App\Interfaces\repositories\istoresrequisitionInterface;
use App\Interfaces\services\istoresrequisitionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoresrequisitionverificationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $storesrequisitionService, $storesrequisitionuuid, $adminrequisitionapproverrepo;
    public function __construct(istoresrequisitionService $storesrequisitionService, $storesrequisitionuuid)
    {
        $this->storesrequisitionService=$storesrequisitionService;
        $this->storesrequisitionuuid=$storesrequisitionuuid;
        $this->adminrequisitionapproverrepo=$this->storesrequisitionService->getadminrequisitionapprovalrecord($this->storesrequisitionuuid);
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
        $storesapproverid=$this->adminrequisitionapproverrepo->getadminrequisitionapproval($storesapprovalitemuuid)->user_id;
        $status='V';
        
        $finalizationurl=url('approval/'.$leaveapprovalitemuuid.'/'.$leaveapproverid.'/'.$storesapprovalitemuuid.'/'.$storesapproverid.'/'.$status);
        return (new MailMessage)
            ->success()
            ->greeting('Good Day')
            ->subject('RE: STORES REQUISITION VERIFICATION SUBMISSION')
            ->line('')
            ->line('A new verification request for '.$storesrequisition->purposeofrequisition.' stores requisition has been submitted by '.$storesrequisition->adminissuer->user->name.' '.$storesrequisition->adminissuer->user->surname)
            ->line('')
            ->action('Make decision', $finalizationurl)
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
