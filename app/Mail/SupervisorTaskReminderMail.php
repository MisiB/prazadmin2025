<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SupervisorTaskReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public $supervisor,
        public Collection $completedTasks,
        public Collection $submittedWeeks
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $totalItems = $this->completedTasks->count() + $this->submittedWeeks->count();

        return new Envelope(
            subject: "Task Review Reminder - {$totalItems} item(s) awaiting your review",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tasks.supervisor-reminder',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
