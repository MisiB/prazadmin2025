<?php

namespace App\Notifications;

use App\Interfaces\services\istoresrequisitionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoresrequisitionapprovalSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $storesrequisitionrecord;
    public function __construct($storesrequisitionrecord)
    {
        $this->storesrequisitionrecord=$storesrequisitionrecord;
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
        $leaveapprovalitemuuid='N';
        $leaveapproverid='N';
        $storesapprovalitemuuid=$this->storesrequisitionrecord['storesrequisitionuuid'];
        $storesapproverid=$this->storesrequisitionrecord['hoduser_id'];
        $status='N';
        
        $finalizationurl=url('approval/'.$leaveapprovalitemuuid.'/'.$leaveapproverid.'/'.$storesapprovalitemuuid.'/'.$storesapproverid.'/'.$status);
        return (new MailMessage)
            ->success()
            ->greeting('Good day')
            ->subject('RE: STORES REQUISITION SUBMISSION')
            ->line('')
            ->line('A new '.$this->storesrequisitionrecord['purposeofrequisition'].' stores requisition has been submitted by '.$this->storesrequisitionrecord['initiatorname'].' '.$this->storesrequisitionrecord['initiatorsurname'])
            ->line('')
            ->action('Make decision', $finalizationurl)
            ->line('')
            ->line('REF #:'.$this->storesrequisitionrecord['storesapprovalitemuuid'])
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
