<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FollowUpReminderDigestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $digest
     */
    public function __construct(
        public User $user,
        public array $digest,
        public string $digestDateLabel,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your NextClient follow-up digest for {$this->digestDateLabel}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.follow-up-digest',
        );
    }
}
