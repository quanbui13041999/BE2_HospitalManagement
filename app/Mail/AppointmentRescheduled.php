<?php
// ═══════════════════════════════════════════════════════════════════════════════
// app/Mail/AppointmentRescheduleMail.php  (Brevo API version)
// ═══════════════════════════════════════════════════════════════════════════════
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class AppointmentRescheduleMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    // Queue riêng cho email — tránh tranh resource với job khác
    public string $queue   = 'mail';
    // Retry 3 lần nếu Brevo API lỗi tạm thời
    public int    $tries   = 3;
    public array  $backoff = [60, 120, 300]; // giây giữa các lần retry

    public function __construct(
        public readonly object $patient,
        public readonly object $appointment,
        public readonly object $doctor,
        public readonly string $reason,
        public readonly string $type,
        public readonly array  $alternatives,
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
        return new Content(
            view: 'emails.appointment-reschedule',
            with: [
                'patient'      => $this->patient,
                'appointment'  => $this->appointment,
                'doctor'       => $this->doctor,
                'reason'       => $this->reason,
                'typeLabel'    => $this->typeLabel(),
                'alternatives' => $this->alternatives,
                'bookingUrl'   => config('app.url') . '/appointments/create',
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