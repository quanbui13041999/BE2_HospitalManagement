<?php

namespace App\Services\Admin;

use App\Models\VaccinationRecord;
use App\Models\Vaccine;
use Illuminate\Support\Facades\DB;

class VaccinationRecordService
{
    /**
     * Get paginated vaccination records, optionally filtered by status.
     */
    public function getPaginatedRecords(?string $status = null, int $perPage = 15)
    {
        $query = VaccinationRecord::with(['user', 'vaccine', 'doctor'])->orderBy('vaccination_id', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    /**
     * Store a new vaccination record.
     */
    public function createRecord(array $data)
    {
        return DB::transaction(function () use ($data) {
            $record = VaccinationRecord::create($data);

            return $record;
        });
    }

    /**
     * Update an existing vaccination record.
     */
    public function updateRecord(VaccinationRecord $record, array $data)
    {
        return DB::transaction(function () use ($record, $data) {
            $record->update($data);

            return $record;
        });
    }

    /**
     * Delete a vaccination record.
     */
    public function deleteRecord(VaccinationRecord $record)
    {
        return DB::transaction(function () use ($record) {

            return $record->delete();
        });
    }

}
