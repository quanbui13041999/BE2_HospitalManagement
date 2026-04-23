<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    // 1. Hiển thị Form đặt lịch
    public function create()
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors(['email' => 'Bạn cần đăng nhập để đặt lịch!']);
        }

        $schedules = DB::table('DoctorSchedules')
            ->join('Doctors', 'DoctorSchedules.doctor_id', '=', 'Doctors.doctor_id')
            ->select('DoctorSchedules.*', 'Doctors.full_name')
            ->where('DoctorSchedules.work_date', '>=', date('Y-m-d'))
            ->get();

        $services = DB::table('Services')->get();

        return view('booking.create', compact('schedules', 'services'));
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $request->validate([
            'schedule_id' => 'required|integer',
            'service_id' => 'required|integer',
            'note' => 'nullable|string|max:255'
        ]);

        try {
            // Tạo cuộc hẹn mới
            Appointment::create([
                'user_id' => Auth::id(), 
                'schedule_id' => $request->schedule_id,
                'service_id' => $request->service_id,
                'note' => $request->note,
                'status' => 'Chờ xác nhận' 
            ]);

            return redirect('/my-bookings')->with('success', '🎉 Đặt lịch khám thành công! Vui lòng chờ xác nhận.');

        } catch (\Exception $e) {
            return back()->withErrors(['Lỗi: ' . $e->getMessage()]);
        }
         return redirect('/my-bookings')->with('success', 'Đăng ký thành công!');
    }
 // 3. Xem danh sách lịch đã đặt của người dùng
public function index()
{
    $appointments = DB::table('Appointments')
        ->join('DoctorSchedules', 'Appointments.schedule_id', '=', 'DoctorSchedules.schedule_id')
        ->join('Doctors', 'DoctorSchedules.doctor_id', '=', 'Doctors.doctor_id')
        ->join('Services', 'Appointments.service_id', '=', 'Services.service_id')
        ->select('Appointments.*', 'DoctorSchedules.work_date', 'DoctorSchedules.start_time', 'Doctors.full_name as doctor_name', 'Services.service_name')
        ->where('Appointments.user_id', Auth::id())
        ->orderBy('Appointments.appointment_id', 'desc')
        ->get();

    return view('booking.index', compact('appointments'));
}

// 4. Hiển thị form dời lịch
public function edit($id)
{
    $appointment = Appointment::where('appointment_id', $id)->where('user_id', Auth::id())->firstOrFail();
    
    $schedules = DB::table('DoctorSchedules')
        ->join('Doctors', 'DoctorSchedules.doctor_id', '=', 'Doctors.doctor_id')
        ->select('DoctorSchedules.*', 'Doctors.full_name')
        ->where('DoctorSchedules.work_date', '>=', date('Y-m-d'))
        ->get();

    return view('booking.edit', compact('appointment', 'schedules'));
}

public function update(Request $request, $id)
{
    $request->validate(['schedule_id' => 'required|integer']);

    $appointment = Appointment::where('appointment_id', $id)->where('user_id', Auth::id())->first();
    
    if ($appointment) {
        $appointment->update([
            'schedule_id' => $request->schedule_id,
            'status' => 'Chờ xác nhận'
        ]);
        return redirect()->route('booking.index')->with('success', 'Dời lịch thành công!');
    }

    return back()->withErrors(['error' => 'Không tìm thấy lịch hẹn.']);
}

// 6. Xử lý Hủy lịch
public function cancel($id)
{
    $appointment = Appointment::where('appointment_id', $id)->where('user_id', Auth::id())->first();

    if ($appointment) {
        $appointment->update(['status' => 'Đã hủy']);
        return redirect()->route('booking.index')->with('success', 'Đã hủy lịch khám.');
    }

    return back()->withErrors(['error' => 'Không thể hủy lịch này.']);
}
}