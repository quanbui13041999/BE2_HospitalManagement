<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $user,
        public $appointment
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Lịch hẹn khám bệnh của bạn tại HospitalBooking',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.appointment-confirmed',
            with: [
                'user'        => $this->user,
                'appointment' => $this->appointment,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
