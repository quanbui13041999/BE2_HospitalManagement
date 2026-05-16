<?php
namespace App\Console\Commands;

use App\Models\{TreatmentReminder, User};
use App\Mail\TreatmentReminderMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTreatmentReminders extends Command
{
    protected $signature   = 'reminders:send';
    protected $description = 'Gửi email nhắc nhở điều trị theo lịch';

    public function handle(): void
    {
        // Lấy các reminder chưa gửi, trong cửa sổ 5 phút tới hoặc đã qua mà chưa gửi
        $reminders = TreatmentReminder::where('is_sent', 0)
            ->where('remind_at', '<=', now()->addMinutes(5))
            ->with('user')
            ->get();

        foreach ($reminders as $reminder) {
            try {
                if ($reminder->user && $reminder->user->email) {
                    Mail::to($reminder->user->email)
                        ->send(new TreatmentReminderMail($reminder));

                    $reminder->update(['is_sent' => 1]);
                    $this->info("Sent to: {$reminder->user->email} — {$reminder->message}");
                }
            } catch (\Exception $e) {
                $this->error("Failed: {$reminder->reminder_id} — {$e->getMessage()}");
            }
        }
    }
}
