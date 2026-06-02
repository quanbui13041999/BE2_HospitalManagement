<?php
// app/Http/Controllers/Doctor/DoctorAppointmentController.php
namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorAppointmentController extends Controller
{
    public function index(Request $request)
    {
        // ✅ Lấy Doctor record từ user đang đăng nhập
        // Doctor->user_id tương ứng với Auth::id()
        $doctor = Doctor::where('user_id', Auth::id())->firstOrFail();

        $status = $request->get('status', 'all');
        $date   = $request->get('date', today()->format('Y-m-d'));

        $query = Appointment::with([
            'user',          // bệnh nhân (User model)
            'service',
            'schedule',      // DoctorSchedule
            'medicalRecord',
        ])
            ->forDoctor($doctor->doctor_id) // scope JOIN doctorschedules, doctor_id trong DoctorSchedules là doctor_id
            ->select('appointments.*');     

        if ($date) {
            $query->whereDate('appointments.appointment_time', $date);
        }

        if ($status !== 'all') {
            $query->where('appointments.status', $status);
        }

        $appointments = $query
            ->orderBy('appointments.is_priority', 'desc')
            ->orderBy('appointments.appointment_time', 'asc')
            ->paginate(20);

        // ✅ Thống kê — dùng closure để tái sử dụng base query
        $base = fn() => Appointment::forDoctor($doctor->doctor_id)
            ->select('appointments.*');

        $stats = [
            'today'     => $base()->whereDate('appointments.appointment_time', today())->count(),
            'pending'   => $base()->where('appointments.status', 'Chờ xác nhận')->count(),
            'confirmed' => $base()->where('appointments.status', 'Đã xác nhận')->count(),
            'done'      => $base()->whereIn('appointments.status', ['Đã Khám', 'Hoàn thành'])->count(),
        ];

        return view('doctors.appointments.index', compact(
            'appointments',
            'stats',
            'doctor',
            'status',
            'date'
        ));
    }
}
