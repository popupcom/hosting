<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OneTimeLoginMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $actionUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Einmal-Anmeldung: '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.one-time-login',
            with: [
                'actionUrl' => $this->actionUrl,
            ],
        );
    }
}
