<?php

namespace App\Mail;

use App\Models\Doctor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DoctorDayOffNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Doctor $doctor,
        public array $data,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Đăng ký nghỉ: {$this->data['type']} - {$this->data['blocked_schedules']} ca khám bị ảnh hưởng",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.doctor-day-off',
            with: [
                'doctor' => $this->doctor,
                'type' => $this->data['type'],
                'date' => $this->data['date'],
                'end_date' => $this->data['end_date'] ?? $this->data['date'],
                'session' => $this->data['session'],
                'reason' => $this->data['reason'] ?? 'Không ghi chú',
                'blocked_schedules' => $this->data['blocked_schedules'],
                'affected_appointments' => $this->data['affected_appointments'],
            ],
        );
    }
}
