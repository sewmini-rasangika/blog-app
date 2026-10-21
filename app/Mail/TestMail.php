<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;


class TestMail extends Mailable
{

    public function __construct(
        public string $recipientName,
        public string $fromAddress,
        public string $fromName,
        public array $details = [],
    ) {
    }


    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromAddress, $this->fromName),
            subject: 'SMTP test: ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.test');
    }
}
