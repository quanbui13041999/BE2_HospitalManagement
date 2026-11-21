<?php

namespace App\Console\Commands;

use App\Services\User\SlotHoldService;
use Illuminate\Console\Command;

/**
 * ReleaseExpiredSlotHolds
 *
 * Artisan command chạy theo schedule để tự động giải phóng
 * các slot hold đã hết hạn (slot_hold_expire < NOW).
 *
 * Đăng ký trong app/Console/Kernel.php:
 *
 *   protected function schedule(Schedule $schedule): void
 *   {
 *       $schedule->command('slots:release-expired')
 *                ->everyMinute()
 *                ->withoutOverlapping();
 *   }
 *
 * Chạy thủ công:
 *   php artisan slots:release-expired
 */
class ReleaseExpiredSlotHolds extends Command
{
    protected $signature   = 'slots:release-expired';
    protected $description = 'Giải phóng tất cả slot hold đã hết hạn thời gian giữ';

    public function handle(SlotHoldService $slotHoldService): int
    {
        $count = $slotHoldService->releaseExpired();

        if ($count > 0) {
            $this->info("[SlotHold] Đã giải phóng {$count} slot hold hết hạn.");
        } else {
            $this->line('[SlotHold] Không có slot hold nào hết hạn.');
        }

        return Command::SUCCESS;
    }
}