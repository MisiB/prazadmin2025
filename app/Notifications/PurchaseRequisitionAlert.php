<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseRequisitionAlert extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $record;
    public function __construct($record)
    {
        $this->record = $record;
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
        return (new MailMessage)
            ->line('A new purchase requisition has been created for your approval')
            ->line('Budget Item: '.$this->record['budgetitem'])
            ->line('Purpose: '.$this->record['purpose'])
            ->line('Quantity: '.$this->record['quantity'])
            ->line('Unit Price: '.$this->record['unitprice'])
            ->line('Total: '.$this->record['total'])
            ->action('View Purchase Requisition', url('purchaserequisition/'.$this->record['uuid']))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
