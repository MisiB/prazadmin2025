<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TsAllowanceCompleted extends Notification implements ShouldQueue
{
    use Queueable;

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
            ->subject('T&S Allowance Payment Processed')
            ->greeting('Good day '.$notifiable->name)
            ->line('Your T&S Allowance payment has been processed.')
            ->line('Application Details:')
            ->line('Application Number: '.$this->record['application_number'])
            ->line('Trip Period: '.$this->record['trip_start_date'].' to '.$this->record['trip_end_date'])
            ->line('Reason: '.$this->record['reason_for_allowances'])
            ->line('Payment Details:')
            ->line('Amount Paid: $'.number_format($this->record['amount_paid'] ?? 0, 2))
            ->line('Payment Method: '.$this->record['payment_method'])
            ->line('Payment Reference: '.$this->record['payment_reference'])
            ->line('Payment Date: '.$this->record['payment_date'])
            ->action('View Application', url('/workflows/ts-allowance/'.$this->record['uuid']))
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
            'application_number' => $this->record['application_number'],
            'trip_start_date' => $this->record['trip_start_date'],
            'trip_end_date' => $this->record['trip_end_date'],
            'reason_for_allowances' => $this->record['reason_for_allowances'],
            'amount_paid' => $this->record['amount_paid'] ?? 0,
            'payment_method' => $this->record['payment_method'],
            'payment_reference' => $this->record['payment_reference'],
            'payment_date' => $this->record['payment_date'],
            'uuid' => $this->record['uuid'],
        ];
    }
}
