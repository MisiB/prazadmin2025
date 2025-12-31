<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffWelfareLoanUpdate extends Notification implements ShouldQueue
{
    use Queueable;

    public $update;

    public function __construct($update)
    {
        $this->update = $update;
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
        $status = $this->update['status'] ?? 'Updated';
        $step = $this->update['step'] ?? 'Workflow Step';
        $comment = $this->update['comment'] ?? '';

        $subject = $status === 'APPROVED'
            ? 'Staff Welfare Loan Approved at '.$step
            : 'Staff Welfare Loan Rejected at '.$step;

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Good day '.$notifiable->name)
            ->line('Your Staff Welfare Loan application has been '.strtolower($status).' at the '.$step.' step.');

        if (! empty($comment)) {
            $mail->line('Comment: '.$comment);
        }

        if ($status === 'APPROVED') {
            $mail->line('Your loan application will proceed to the next approval step.');
        } else {
            $mail->line('Your loan application has been rejected. Please review the comments and contact the approver if you have questions.');
        }

        return $mail
            ->action('View Loan Details', url('/staff-welfare-loans'))
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
            'step' => $this->update['step'] ?? null,
            'status' => $this->update['status'] ?? null,
            'comment' => $this->update['comment'] ?? null,
        ];
    }
}
