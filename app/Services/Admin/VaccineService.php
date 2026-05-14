<?php

namespace App\Services\Admin;

use App\Models\Vaccine;

class VaccineService
{
    /**
     * Get paginated vaccines.
     */
    public function getPaginated(int $perPage = 10)
    {
        return Vaccine::orderBy('vaccine_id', 'desc')->paginate($perPage);
    }

    /**
     * Store a new vaccine.
     */
    public function createVaccine(array $data)
    {
        return Vaccine::create($data);
    }

    /**
     * Update an existing vaccine.
     */
    public function updateVaccine(Vaccine $vaccine, array $data)
    {
        return $vaccine->update($data);
    }

    /**
     * Delete a vaccine.
     */
    public function deleteVaccine(Vaccine $vaccine)
    {
        return $vaccine->delete();
    }
}
