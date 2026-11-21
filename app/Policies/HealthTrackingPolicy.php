<?php

namespace App\Policies;

use App\Models\HealthTracking;
use App\Models\MedicalRecord;
use App\Models\User;

class HealthTrackingPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isPatientUser($user) || $this->isDoctorUser($user) || $this->isAdminUser($user);
    }

    public function view(User $user, HealthTracking $ht): bool
    {
        if ($this->isAdminUser($user)) {
            return true;
        }

        if ($this->isPatientUser($user)) {
            return (int) $user->user_id === (int) $ht->patient_id;
        }

        if ($this->isDoctorUser($user)) {
            return MedicalRecord::where('doctor_id', $user->user_id)
                ->where('patient_id', $ht->patient_id)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->isPatientUser($user);
    }

    public function update(User $user, HealthTracking $ht): bool
    {
        return $this->isPatientUser($user) && (int) $user->user_id === (int) $ht->patient_id;
    }

    public function delete(User $user, HealthTracking $ht): bool
    {
        return $this->isPatientUser($user) && (int) $user->user_id === (int) $ht->patient_id;
    }

    private function isAdminUser(User $user): bool
    {
        return method_exists($user, 'isAdmin')
            ? $user->isAdmin()
            : (int) ($user->role_id ?? 0) === 1;
    }

    private function isDoctorUser(User $user): bool
    {
        return method_exists($user, 'isDoctor')
            ? $user->isDoctor()
            : (int) ($user->role_id ?? 0) === 2;
    }

    private function isPatientUser(User $user): bool
    {
        return method_exists($user, 'isPatient')
            ? $user->isPatient()
            : (int) ($user->role_id ?? 0) === 3;
    }
}
