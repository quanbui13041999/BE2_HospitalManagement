<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\HospitalNews;
use App\Models\Doctor;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_patients' => User::where('role_id', 3)->count(),
            'total_doctors' => Doctor::count(),
            'appointments_today' => Appointment::whereDate('appointment_time', today())->count(),
            'pending_appointments' => Appointment::where('status', 'Chờ xác nhận')->count(),
            'total_revenue' => Payment::whereIn('status', ['Thành công', 'Đã thanh toán'])->sum('total_amount'),
            'revenue_today' => Payment::whereIn('status', ['Thành công', 'Đã thanh toán'])
                ->whereDate('payment_date', today())
                ->sum('total_amount'),
        ];

        $recentAppointments = Appointment::with(['user', 'schedule.doctor'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentPayments = Payment::with(['appointment.user'])
            ->orderByDesc('payment_date')
            ->limit(5)
            ->get();

        $recentNews = HospitalNews::with('author')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentAppointments', 'recentPayments', 'recentNews'));
    }
}
