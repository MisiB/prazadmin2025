<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffWelfareLoanNotification extends Notification implements ShouldQueue
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
        $subject = isset($this->record['amount_paid'])
            ? 'Staff Welfare Loan Payment Processed'
            : 'Staff Welfare Loan Awaiting Your '.($this->record['status'] ?? 'Review');

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Good day '.$notifiable->name);

        if (isset($this->record['amount_paid'])) {
            $mail->line('Your Staff Welfare Loan payment has been processed.')
                ->line('Loan Number: '.$this->record['loan_number'])
                ->line('Amount Paid: '.number_format($this->record['amount_paid'], 2))
                ->line('Please acknowledge your debt obligation.')
                ->action('View Loan Details', url('/staff-welfare-loans/'.$this->record['uuid']));
        } else {
            $mail->line('A Staff Welfare Loan application requires your attention.')
                ->line('Loan Number: '.$this->record['loan_number'])
                ->line('Applicant: '.$this->record['full_name'])
                ->line('Department: '.$this->record['department'])
                ->line('Loan Amount Requested: '.number_format($this->record['loan_amount_requested'], 2))
                ->line('Loan Purpose: '.$this->record['loan_purpose'])
                ->line('Status: '.$this->record['status'])
                ->action('View Loan Application', url('/staff-welfare-loans/'.$this->record['uuid']));
        }

        return $mail->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'loan_number' => $this->record['loan_number'] ?? null,
            'full_name' => $this->record['full_name'] ?? null,
            'department' => $this->record['department'] ?? null,
            'loan_amount_requested' => $this->record['loan_amount_requested'] ?? null,
            'loan_purpose' => $this->record['loan_purpose'] ?? null,
            'status' => $this->record['status'] ?? null,
            'amount_paid' => $this->record['amount_paid'] ?? null,
            'uuid' => $this->record['uuid'] ?? null,
        ];
    }
}
