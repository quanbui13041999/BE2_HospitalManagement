<?php

namespace App\Console\Commands;

use App\Models\MedicalRecord;
use Illuminate\Console\Command;

class SyncMedicalRecordStatus extends Command
{
    protected $signature = 'medical-records:sync-status';
    protected $description = 'Sync old medical record statuses to completed when the linked appointment is already completed.';

    public function handle(): int
    {
        $query = MedicalRecord::query()
            ->whereIn('status', ['pending', 'examining'])
            ->whereNotNull('appointment_id')
            ->whereHas('appointment', function ($query) {
                $query->whereIn('status', ['Đã Khám', 'Hoàn thành']);
            });

        $updated = $query->update(['status' => MedicalRecord::STATUS_COMPLETED]);

        $this->info("Đã cập nhật {$updated} hồ sơ bệnh án cũ sang trạng thái completed.");

        return 0;
    }
}
