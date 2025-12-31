<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffWelfareLoanCompleted extends Notification implements ShouldQueue
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
            ->subject('Staff Welfare Loan Completed - Debt Acknowledged')
            ->greeting('Good day '.$notifiable->name)
            ->line('A Staff Welfare Loan has been completed. The employee has acknowledged their debt obligation.')
            ->line('Loan Details:')
            ->line('Loan Number: '.$this->record['loan_number'])
            ->line('Employee: '.$this->record['full_name'])
            ->line('Employee Number: '.$this->record['employee_number'])
            ->line('Department: '.$this->record['department'])
            ->line('Amount Paid: $'.number_format($this->record['amount_paid'], 2))
            ->line('Payment Date: '.$this->record['payment_date'])
            ->line('Acknowledgement Date: '.$this->record['acceptance_date'])
            ->action('View Completed Loan', url('/workflows/staff-welfare-loan/'.$this->record['uuid']))
            ->line('The loan deduction schedule can now be processed through payroll.')
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
            'employee_number' => $this->record['employee_number'],
            'department' => $this->record['department'],
            'amount_paid' => $this->record['amount_paid'],
            'payment_date' => $this->record['payment_date'],
            'acceptance_date' => $this->record['acceptance_date'],
            'uuid' => $this->record['uuid'],
        ];
    }
}
