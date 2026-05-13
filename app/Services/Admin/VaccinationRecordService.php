<?php

namespace App\Services\Admin;

use App\Models\VaccinationRecord;

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
        return VaccinationRecord::create($data);
    }

    /**
     * Update an existing vaccination record.
     */
    public function updateRecord(VaccinationRecord $record, array $data)
    {
        return $record->update($data);
    }

    /**
     * Delete a vaccination record.
     */
    public function deleteRecord(VaccinationRecord $record)
    {
        return $record->delete();
    }
}
