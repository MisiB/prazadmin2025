<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentRequisitionNotification extends Notification implements ShouldQueue
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
            ->subject('Payment Requisition awaiting your '.$this->record['status'])
            ->greeting('Good day '.$notifiable->name)
            ->line('A new payment requisition has been requested by '.$this->record['created_by'])
            ->line('Request Details:')
            ->line('Budget Item: '.$this->record['budgetitem'])
            ->line('Department: '.$this->record['department'])
            ->line('Requested By: '.$this->record['created_by'])
            ->line('Purpose: '.$this->record['purpose'])
            ->line('Total Amount: '.$this->record['total'])
            ->action('View Payment Requisition', url('/paymentrequisition/'.$this->record['uuid']))
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
            'budgetitem' => $this->record['budgetitem'],
            'department' => $this->record['department'],
            'created_by' => $this->record['created_by'],
            'purpose' => $this->record['purpose'],
            'total' => $this->record['total'],
        ];
    }
}
