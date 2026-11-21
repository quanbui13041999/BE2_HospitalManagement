<?php

namespace App\Console\Commands;

use App\Mail\TreatmentReminderMail;
use App\Models\TreatmentReminder;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTreatmentReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send treatment reminder emails to patients';

    public function __construct(private NotificationService $notifications)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        // ĐÃ SỬA: Gọi trực tiếp pending() thay vì scopePending() để đúng chuẩn Laravel Magic Method
        $reminders = TreatmentReminder::pending()
            ->where('remind_at', '<=', now()->addMinutes(5))
            ->with(['user', 'medicalRecord.patient'])
            ->get();

        if ($reminders->isEmpty()) {
            $this->info("No reminders to send at this time.");
            return;
        }

        foreach ($reminders as $reminder) {
            try {
                $patient = $reminder->medicalRecord?->patient ?? $reminder->user;
                $email = $patient?->email;

                if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->warn("Skipped reminder {$reminder->reminder_id}: patient email is missing or invalid.");
                    continue;
                }

                // CHÌA KHÓA: Đánh dấu đã gửi TRƯỚC khi bắn mail 
                // Điều này chặn đứng việc tiến trình chạy sau quét trùng nếu lệnh bị nghẽn mạng quá 1 phút
                $reminder->update(['is_sent' => 1]);

                if ($patient?->user_id) {
                    $this->notifications->createForUser(
                        $patient->user_id,
                        'Nhắc lịch điều trị',
                        $reminder->message,
                        'treatment_reminder',
                        'treatment_reminder',
                        $reminder->reminder_id
                    );
                }

                Mail::to($email)->send(new TreatmentReminderMail($reminder));

                $this->info("Sent to patient: {$email} - {$reminder->message}");
            } catch (\Exception $e) {
                // Nếu gửi lỗi thực sự (sai SMTP, sập mạng), trả lại trạng thái 0 để lượt sau gửi lại
                $reminder->update(['is_sent' => 0]);
                
                $this->error("Failed to send reminder {$reminder->reminder_id}: {$e->getMessage()}");
            }
        }
    }
}
