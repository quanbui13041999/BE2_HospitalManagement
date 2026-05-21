<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __construct(private AdminDashboardService $service)
    {
        $this->middleware(['auth', 'is_admin']);
    }

    public function index(Request $request)
    {
        $timeRange = $request->input('time_range', 'week');

        $appointmentStats    = $this->service->getAppointmentStats($timeRange);
        $patientStats        = $this->service->getPatientStats();
        $performanceStats    = $this->service->getPerformanceStats($timeRange);

        $dailyData           = $this->service->getDailyAppointmentsData($timeRange);
        $specialtyData       = $this->service->getSpecialtyDistribution($timeRange);
        $statusData          = $this->service->getStatusDistribution($timeRange);
        $ageData             = $this->service->getAgeDistribution();
        $patientTrendData    = $this->service->getPatientTypeTrend($timeRange);
        $satisfactionTrendData = $this->service->getSatisfactionTrend($timeRange);

        $waitTimeData        = $this->service->getWaitTimeBySpecialty($timeRange);
        $satisfactionByDoctor = $this->service->getSatisfactionByDoctor(5);

        $topDoctors          = $this->service->getTopDoctors(5);
        $topDoctorWeek       = $this->service->getTopDoctorThisWeek();

        // FIX: Không cho browser/proxy cache trang dashboard
        // Đảm bảo mỗi lần vào trang đều lấy dữ liệu mới nhất từ DB
        return response()
            ->view('doctor.dashboardAdmin', compact(
                'appointmentStats',
                'patientStats',
                'performanceStats',
                'dailyData',
                'specialtyData',
                'statusData',
                'ageData',
                'patientTrendData',
                'satisfactionTrendData',
                'waitTimeData',
                'satisfactionByDoctor',
                'topDoctors',
                'topDoctorWeek',
                'timeRange'
            ))
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'        => 'no-cache',
                'Expires'       => '0',
            ]);
    }
}