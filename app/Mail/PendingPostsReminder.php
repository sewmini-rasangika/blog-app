<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PendingPostsReminder extends Mailable
{
    public function __construct(
        public string $recipientName,
        public Collection $posts,
        public int $days,
        public string $reviewUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        $count = $this->posts->count();

        return new Envelope(
            subject: $count . ' ' . Str::plural('post', $count) . ' waiting for approval - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.pending-posts');
    }
}
