<?php

namespace App\Console\Commands;

use App\Services\AppointmentReminderService;
use Illuminate\Console\Command;

class SendAppointmentEmailReminders extends Command
{
    protected $signature = 'appointments:remind';
    protected $description = 'Send appointment reminder emails via Brevo one day and one hour before appointment time';

    public function __construct(private AppointmentReminderService $reminderService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $stats = $this->reminderService->sendPendingReminders();

        $this->info(sprintf(
            'Appointment reminder job completed. 1-day=%d, 1-hour=%d',
            $stats['sent_1day'],
            $stats['sent_1hour']
        ));

        return 0;
    }
}
