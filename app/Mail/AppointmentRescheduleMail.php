<?php
// ═══════════════════════════════════════════════════════════════════════════════
// app/Mail/AppointmentRescheduleMail.php  (Brevo API version)
// ═══════════════════════════════════════════════════════════════════════════════
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class AppointmentRescheduleMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly object $patient,
        public readonly object $appointment,
        public readonly ?object $doctor = null,
        public readonly string $reason = '',
        public readonly string $type = 'leave',
        public readonly array  $alternatives = [],
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[MediBook] Lịch hẹn ' .
                     $this->appointment->appointment_time->format('d/m/Y') .
                     ' đã thay đổi — vui lòng chọn lịch mới',
        );
    }

    // Custom headers → Brevo forward vào API payload để lọc trên dashboard
    public function headers(): Headers
    {
        return new Headers(
            messageId: null,
            references: [],
            text: [
                'X-MediBook-Type'     => 'appointment-reschedule',
                'X-MediBook-DoctorId' => (string) $this->doctor->doctor_id,
                'X-MediBook-ApptId'   => (string) $this->appointment->appointment_id,
                'X-MediBook-Reason'   => $this->type,
            ],
        );
    }

    public function content(): Content
    {
        $doctor = $this->doctor ?? (object) [
            'full_name' => $this->appointment->doctor_name ?? 'Bác sĩ',
            'department' => (object) [
                'department_name' => $this->appointment->department_name ?? '',
            ],
        ];

        // Nếu alternatives đã có score, dùng email template mới (smart)
        // Nếu không, dùng template cũ
        $hasScoring = !empty($this->alternatives) && isset($this->alternatives[0]['score']);
        $viewName = $hasScoring ? 'emails.appointment-reschedule-smart' : 'emails.appointment-reschedule';

        return new Content(
            view: $viewName,
            with: [
                'patient'      => $this->patient,
                'appointment'  => $this->appointment,
                'doctor'       => $doctor,
                'reason'       => $this->reason,
                'typeLabel'    => $this->typeLabel(),
                'alternatives' => $this->alternatives,
                'bookingUrl'   => route('appointments.create'),
                'rescheduleUrl' => route('appointments.edit', ['id' => $this->appointment->appointment_id]),
                'doctorOffUrl' => route('appointments.doctor-off', ['id' => $this->appointment->appointment_id]),
            ],
        );
    }

    public function attachments(): array { return []; }

    private function typeLabel(): string
    {
        return match ($this->type) {
            'sick'       => 'Bệnh / đột xuất',
            'leave'      => 'Nghỉ phép',
            'conference' => 'Hội nghị / đào tạo',
            default      => 'Nghỉ',
        };
    }

    // Ghi log khi job thất bại hoàn toàn (sau hết retry)
    public function failed(\Throwable $e): void
    {
        \Illuminate\Support\Facades\Log::error('[BrevoMail] Gửi email thất bại', [
            'patient_email'  => $this->patient->email ?? null,
            'appointment_id' => $this->appointment->appointment_id ?? null,
            'error'          => $e->getMessage(),
        ]);
    }
}