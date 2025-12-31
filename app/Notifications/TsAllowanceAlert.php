<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TsAllowanceAlert extends Notification implements ShouldQueue
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
            ->subject('T&S Allowance Awaiting Your Approval')
            ->greeting('Good day '.$notifiable->name)
            ->line('A new T&S Allowance application has been submitted and requires your approval.')
            ->line('Application Details:')
            ->line('Application Number: '.$this->record['application_number'])
            ->line('Applicant: '.$this->record['full_name'])
            ->line('Department: '.$this->record['department'])
            ->line('Trip Period: '.$this->record['trip_start_date'].' to '.$this->record['trip_end_date'])
            ->line('Reason: '.$this->record['reason_for_allowances'])
            ->line('Estimated Amount: $'.number_format($this->record['calculated_subtotal'] ?? 0, 2))
            ->line('Status: '.$this->record['status'])
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
            'full_name' => $this->record['full_name'],
            'department' => $this->record['department'],
            'trip_start_date' => $this->record['trip_start_date'],
            'trip_end_date' => $this->record['trip_end_date'],
            'reason_for_allowances' => $this->record['reason_for_allowances'],
            'calculated_subtotal' => $this->record['calculated_subtotal'] ?? 0,
            'status' => $this->record['status'],
            'uuid' => $this->record['uuid'],
        ];
    }
}
