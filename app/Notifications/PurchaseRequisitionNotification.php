<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseRequisitionNotification extends Notification implements ShouldQueue
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
            ->subject('Purchase Requisition awaiting your '. $this->record["status"])
            ->greeting('Good day '. $notifiable->name)
            ->line('A new purchase requisition has been requested by '. $this->record["requested_by"])
            ->line("Request Details:")
            ->line("Budget Item: " . $this->record["budgetitem"])
            ->line("Sub Programme Output: " . $this->record["strategysubprogrammeoutput"])
            ->line("Department: " . $this->record["department"])
            ->line("Requested By: " . $this->record["requested_by"])
            ->line("Recommended By: " . $this->record["recommended_by"])
            ->line("Purpose: " . $this->record["purpose"])
            ->line("Quantity: " . $this->record["quantity"])
            ->line("Unit Price: " . $this->record["unitprice"])
            ->line("Total: " . $this->record["total"])
            ->action('View Purchase Requisition', url('/purchaserequisition/'.$this->record["uuid"]))
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
            "budgetitem" => $this->record["budgetitem"],
            "strategysubprogrammeoutput" => $this->record["strategysubprogrammeoutput"],
            "department" => $this->record["department"],
            "requested_by" => $this->record["requested_by"],
            "recommended_by" => $this->record["recommended_by"],
            "budgetconfirmed_by" => $this->record["budgetconfirmed_by"],
            "approved_by" => $this->record["approved_by"],
            "purpose" => $this->record["purpose"],
            "quantity" => $this->record["quantity"],
            "unitprice" => $this->record["unitprice"],
            "total" => $this->record["total"],
        ];
    }
}
