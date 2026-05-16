<?php
namespace App\Mail;

use App\Models\TreatmentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TreatmentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public TreatmentReminder $reminder) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⏰ Nhắc nhở điều trị — ' . $this->reminder->remind_at->format('H:i d/m/Y'),
        );
    }

    public function content(): Content
    {
        $patient = $this->reminder->medicalRecord?->patient ?? $this->reminder->user;

        return new Content(
            markdown: 'emails.treatment_reminder',
            with: [
                'reminder' => $this->reminder,
                'patient' => $patient,
            ],
        );
    }
}
