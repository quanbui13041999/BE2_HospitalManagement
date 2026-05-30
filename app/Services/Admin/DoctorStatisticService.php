<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DoctorStatisticService
{
    /**
     * Get comprehensive dashboard data for Doctor Statistics.
     *
     * @param Request $request
     * @return array
     */
    public function getDashboardData(Request $request)
    {
        $selectedMonthStr = $request->input('month', Carbon::now()->format('Y-m'));
        $selectedDate = Carbon::createFromFormat('Y-m', $selectedMonthStr)->startOfMonth();
        $previousDate = $selectedDate->copy()->subMonth();

        $selectedDoctorId = $request->input('doctor_id', 'all');

        // Doctors list for filter
        $doctors = DB::table('doctors as d')
            ->join('users as u', 'd.user_id', '=', 'u.user_id')
            ->select('d.doctor_id', 'd.full_name')
            ->get();

        // 1. KPI: Total Appointments This Month
        $currentAppointmentsQuery = DB::table('appointments as a')
            ->join('doctorschedules as ds', 'a.schedule_id', '=', 'ds.schedule_id')
            ->whereBetween('ds.work_date', [$selectedDate->copy()->startOfMonth()->toDateString(), $selectedDate->copy()->endOfMonth()->toDateString()]);

        if ($selectedDoctorId !== 'all') {
            $currentAppointmentsQuery->where('ds.doctor_id', $selectedDoctorId);
        }
        
        $totalAppointments = $currentAppointmentsQuery->count();

        // 1.b KPI: Previous Month Appointments
        $previousAppointmentsQuery = DB::table('appointments as a')
            ->join('doctorschedules as ds', 'a.schedule_id', '=', 'ds.schedule_id')
            ->whereBetween('ds.work_date', [$previousDate->copy()->startOfMonth()->toDateString(), $previousDate->copy()->endOfMonth()->toDateString()]);
        
        if ($selectedDoctorId !== 'all') {
            $previousAppointmentsQuery->where('ds.doctor_id', $selectedDoctorId);
        }
        $previousTotalAppointments = $previousAppointmentsQuery->count();

        // 2. KPI: Revenue generated (From payments)
        $revenueQuery = DB::table('payments as p')
            ->join('appointments as a', 'p.appointment_id', '=', 'a.appointment_id')
            ->join('doctorschedules as ds', 'a.schedule_id', '=', 'ds.schedule_id')
            ->where('p.status', 'Đã thanh toán')
            ->whereBetween('p.payment_date', [
                $selectedDate->copy()->startOfMonth()->toDateString() . ' 00:00:00',
                $selectedDate->copy()->endOfMonth()->toDateString() . ' 23:59:59'
            ]);

        if ($selectedDoctorId !== 'all') {
            $revenueQuery->where('ds.doctor_id', $selectedDoctorId);
        }
        $totalRevenue = $revenueQuery->sum('p.total_amount');

        // 3. KPI: Cancellation Rate
        $cancelledCount = (clone $currentAppointmentsQuery)->where('a.status', 'Đã hủy')->count();
        $cancelRate = $totalAppointments > 0 ? round(($cancelledCount / $totalAppointments) * 100, 1) : 0;

        // 4. Daily Appointments for Chart
        // Group by day of month
        $dailyAppointmentsRaw = (clone $currentAppointmentsQuery)
            ->select(DB::raw('DAY(ds.work_date) as day'), DB::raw('COUNT(a.appointment_id) as count'))
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $daysInMonth = $selectedDate->daysInMonth;
        $dailyLabels = [];
        $dailyData = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dailyLabels[] = "Ngày $i";
            $dailyData[] = isset($dailyAppointmentsRaw[$i]) ? $dailyAppointmentsRaw[$i]->count : 0;
        }

        // 5. Doctor Comparison Table
        $doctorStats = DB::table('doctors as d')
            ->join('users as u', 'd.user_id', '=', 'u.user_id')
            ->leftJoin('departments as dep', 'd.department_id', '=', 'dep.department_id')
            ->select('d.doctor_id', 'd.full_name', 'dep.department_name')
            ->get();

        foreach ($doctorStats as $doc) {
            // Appointments for this doc
            $docTotalAppts = DB::table('appointments as a')
                ->join('doctorschedules as ds', 'a.schedule_id', '=', 'ds.schedule_id')
                ->where('ds.doctor_id', $doc->doctor_id)
                ->whereBetween('ds.work_date', [$selectedDate->copy()->startOfMonth()->toDateString(), $selectedDate->copy()->endOfMonth()->toDateString()])
                ->count();
            
            $docCancelledAppts = DB::table('appointments as a')
                ->join('doctorschedules as ds', 'a.schedule_id', '=', 'ds.schedule_id')
                ->where('ds.doctor_id', $doc->doctor_id)
                ->where('a.status', 'Đã hủy')
                ->whereBetween('ds.work_date', [$selectedDate->copy()->startOfMonth()->toDateString(), $selectedDate->copy()->endOfMonth()->toDateString()])
                ->count();
                
            $doc->total_appointments = $docTotalAppts;
            $doc->cancel_rate = $docTotalAppts > 0 ? round(($docCancelledAppts / $docTotalAppts) * 100, 1) : 0;
            
            // Revenue
            $doc->revenue = DB::table('payments as p')
                ->join('appointments as a', 'p.appointment_id', '=', 'a.appointment_id')
                ->join('doctorschedules as ds', 'a.schedule_id', '=', 'ds.schedule_id')
                ->where('ds.doctor_id', $doc->doctor_id)
                ->where('p.status', 'Đã thanh toán')
                ->whereBetween('p.payment_date', [
                    $selectedDate->copy()->startOfMonth()->toDateString() . ' 00:00:00',
                    $selectedDate->copy()->endOfMonth()->toDateString() . ' 23:59:59'
                ])
                ->sum('p.total_amount');
        }
        
        // Sort doctorStats by total_appointments descending
        $doctorStats = collect($doctorStats)->sortByDesc('total_appointments')->values();

        // Previous month vs Current month % increase
        $momGrowth = 0;
        if ($previousTotalAppointments > 0) {
            $momGrowth = round((($totalAppointments - $previousTotalAppointments) / $previousTotalAppointments) * 100, 1);
        } elseif ($totalAppointments > 0) {
            $momGrowth = 100;
        }

        return [
            'selectedMonthStr' => $selectedMonthStr,
            'selectedDoctorId' => $selectedDoctorId,
            'doctors' => $doctors,
            'totalAppointments' => $totalAppointments,
            'totalRevenue' => $totalRevenue,
            'cancelRate' => $cancelRate,
            'dailyLabels' => $dailyLabels,
            'dailyData' => $dailyData,
            'doctorStats' => $doctorStats,
            'previousTotalAppointments' => $previousTotalAppointments,
            'momGrowth' => $momGrowth,
            'selectedDate' => $selectedDate,
            'previousDate' => $previousDate,
        ];
    }
}
