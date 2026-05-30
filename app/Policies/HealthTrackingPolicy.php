<?php

namespace App\Policies;

use App\Models\HealthTracking;
use App\Models\User;

class HealthTrackingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isPatient() || $user->isDoctor() || $user->isAdmin();
    }

    public function view(User $user, HealthTracking $ht): bool
    {
        return $user->isDoctor()
            || $user->isAdmin()
            || (int) $user->user_id === (int) $ht->patient_id;
    }

    public function create(User $user): bool
    {
        return $user->isPatient();
    }

    public function update(User $user, HealthTracking $ht): bool
    {
        return $user->isPatient() && (int) $user->user_id === (int) $ht->patient_id;
    }

    public function delete(User $user, HealthTracking $ht): bool
    {
        return $user->isPatient() && (int) $user->user_id === (int) $ht->patient_id;
    }
}
