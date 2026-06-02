<?php

namespace App\Mail;

use App\Models\TreatmentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class TreatmentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public TreatmentReminder $reminder) {}

    public function envelope(): Envelope
    {
        // Ép kiểu Carbon phòng trường hợp Model quên cast datetime, tránh crash format()
        $remindAt = $this->reminder->remind_at instanceof Carbon 
            ? $this->reminder->remind_at 
            : Carbon::parse($this->reminder->remind_at);

        return new Envelope(
            subject: '⏰ Nhắc nhở điều trị — ' . $remindAt->format('H:i d/m/Y'),
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
                'patientName' => $patient?->name ?? 'Quý khách', // Tên an toàn cho file Blade
            ],
        );
    }
}