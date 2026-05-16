<?php

namespace App\Console\Commands;

use App\Mail\TreatmentReminderMail;
use App\Models\TreatmentReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTreatmentReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send treatment reminder emails to patients';

    public function handle(): void
    {
        $reminders = TreatmentReminder::where('is_sent', 0)
            ->where('remind_at', '<=', now()->addMinutes(5))
            ->with(['user', 'medicalRecord.patient'])
            ->get();

        foreach ($reminders as $reminder) {
            try {
                $patient = $reminder->medicalRecord?->patient ?? $reminder->user;
                $email = $patient?->email;

                if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->warn("Skipped reminder {$reminder->reminder_id}: patient email is missing or invalid.");
                    continue;
                }

                Mail::to($email)->send(new TreatmentReminderMail($reminder));

                $reminder->update(['is_sent' => 1]);
                $this->info("Sent to patient: {$email} - {$reminder->message}");
            } catch (\Exception $e) {
                $this->error("Failed: {$reminder->reminder_id} - {$e->getMessage()}");
            }
        }
    }
}
