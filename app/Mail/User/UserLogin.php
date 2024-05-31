<?php

namespace App\Mail\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserLogin extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $mailData;

    public function __construct(array $mailData)
    {
        $this->mailData = $mailData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You request OTP on the Jost Pay App for Login!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.user.user-login',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
