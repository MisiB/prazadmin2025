<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DailyTaskReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public $user,
        public Collection $pendingTasks,
        public Collection $ongoingTasks,
        public $totalHours
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $taskCount = $this->pendingTasks->count() + $this->ongoingTasks->count();

        return new Envelope(
            subject: "Daily Task Reminder - You have {$taskCount} outstanding task(s)",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tasks.daily-reminder',
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
