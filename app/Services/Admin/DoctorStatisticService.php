<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;

class DoctorStatisticService
{
    /**
     * Get statistics for all doctors including their ratings and total appointments.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getDoctorStatistics()
    {
        return DB::table('v_doctorratings as v')
            ->leftJoin('departments as dep', 'v.department_id', '=', 'dep.department_id')
            ->leftJoin('doctorschedules as ds', 'v.doctor_id', '=', 'ds.doctor_id')
            ->leftJoin('appointments as a', 'ds.schedule_id', '=', 'a.schedule_id')
            ->select(
                'v.doctor_id',
                'v.full_name',
                'v.experience',
                'v.price',
                'v.status',
                'v.avg_rating',
                'v.total_reviews',
                'dep.department_name',
                DB::raw('COUNT(a.appointment_id) as total_appointments')
            )
            ->groupBy(
                'v.doctor_id',
                'v.full_name',
                'v.experience',
                'v.price',
                'v.status',
                'v.avg_rating',
                'v.total_reviews',
                'dep.department_name'
            )
            ->orderBy('v.avg_rating', 'desc')
            ->get();
    }
}
