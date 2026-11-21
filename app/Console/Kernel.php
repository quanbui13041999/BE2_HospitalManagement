<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run all scheduled tasks.
        // Note: schedule:run is invoked by cron/worker; these are the tasks.

        $schedule->command('appointments:remind')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('reminders:send')
            ->everyMinute()
            ->withoutOverlapping();

        $schedule->command('slots:release-expired')
            ->everyMinute()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}

