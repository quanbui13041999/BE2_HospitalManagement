<?php

namespace App\Mail;

use App\Models\HospitalNews;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsPublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public HospitalNews $article) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bản tin bệnh viện: ' . $this->article->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.news-published',
            with: [
                'article' => $this->article,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
