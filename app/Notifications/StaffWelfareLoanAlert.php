<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffWelfareLoanAlert extends Notification implements ShouldQueue
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
            ->subject('Staff Welfare Loan Awaiting Your Approval')
            ->greeting('Good day '.$notifiable->name)
            ->line('A new Staff Welfare Loan application has been submitted and requires your approval.')
            ->line('Loan Details:')
            ->line('Loan Number: '.$this->record['loan_number'])
            ->line('Applicant: '.$this->record['full_name'])
            ->line('Department: '.$this->record['department'])
            ->line('Loan Amount Requested: '.number_format($this->record['loan_amount_requested'], 2))
            ->line('Loan Purpose: '.$this->record['loan_purpose'])
            ->line('Status: '.$this->record['status'])
            ->action('View Loan Application', url('/staff-welfare-loans/'.$this->record['uuid']))
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
            'loan_number' => $this->record['loan_number'],
            'full_name' => $this->record['full_name'],
            'department' => $this->record['department'],
            'loan_amount_requested' => $this->record['loan_amount_requested'],
            'loan_purpose' => $this->record['loan_purpose'],
            'status' => $this->record['status'],
            'uuid' => $this->record['uuid'],
        ];
    }
}
