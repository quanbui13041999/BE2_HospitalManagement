<?php
// app/Http/Controllers/AppointmentController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    // ================================================================
    // 1. ĐẶT LỊCH HẸN — GET /dat-lich
    // ================================================================
    public function create()
    {
        $departments = DB::table('Departments')
            ->where('status', 1)
            ->orderBy('department_name')
            ->get();

        $services = DB::table('Services')
            ->leftJoin('ServicePrices', function ($join) {
                $join->on('ServicePrices.service_id', '=', 'Services.service_id')
                     ->where('ServicePrices.price_type', 'Thường')
                     ->whereNull('ServicePrices.end_date');
            })
            ->where('Services.status', 1)
            ->select('Services.*', 'ServicePrices.price')
            ->orderBy('Services.service_name')
            ->get();

        // Doctors grouped by department_id (for JS)
        $doctorsByDept = DB::table('Doctors')
            ->join('Users', 'Doctors.user_id', '=', 'Users.user_id')
            ->leftJoinSub(
                DB::table('Reviews')
                    ->select('doctor_id',
                             DB::raw('ROUND(AVG(rating),1) as avg_rating'),
                             DB::raw('COUNT(*) as total_reviews'))
                    ->groupBy('doctor_id'),
                'rv', 'rv.doctor_id', '=', 'Doctors.doctor_id'
            )
            ->where('Doctors.status', 1)
            ->select(
                'Doctors.doctor_id', 'Doctors.department_id',
                'Doctors.full_name', 'Doctors.experience',
                'Doctors.price', 'Doctors.avatar_url', 'Doctors.bio',
                DB::raw('COALESCE(rv.avg_rating, 0) as avg_rating'),
                DB::raw('COALESCE(rv.total_reviews, 0) as total_reviews')
            )
            ->get()
            ->groupBy('department_id')
            ->toArray();

        // Pre-load schedules next 14 days (for JS fallback)
        $scheduleData = DB::table('DoctorSchedules')
            ->leftJoinSub(
                DB::table('Appointments')
                    ->select('schedule_id', DB::raw('COUNT(*) as booked_count'))
                    ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
                    ->groupBy('schedule_id'),
                'bk', 'bk.schedule_id', '=', 'DoctorSchedules.schedule_id'
            )
            ->whereBetween('work_date', [now()->toDateString(), now()->addDays(13)->toDateString()])
            ->where('DoctorSchedules.status', 'Hoạt động')
            ->select('DoctorSchedules.*', DB::raw('COALESCE(bk.booked_count,0) as booked_count'))
            ->get()
            ->groupBy(fn($r) => $r->doctor_id . '_' . $r->work_date)
            ->toArray();

        return view('booking.create', compact('departments', 'services', 'doctorsByDept', 'scheduleData'));
    }

    // ================================================================
    // 1b. API AJAX — GET /api/schedules?doctor_id=&work_date=
    // ================================================================
    public function getSchedules(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|integer|exists:Doctors,doctor_id',
            'work_date' => 'required|date|after_or_equal:today',
        ]);

        // Check bác sĩ có ngày nghỉ không
        $isDayOff = DB::table('DoctorDaysOff')
            ->where('doctor_id', $request->doctor_id)
            ->where('off_date', $request->work_date)
            ->exists();

        if ($isDayOff) {
            return response()->json(['schedules' => [], 'day_off' => true]);
        }

        $schedules = DB::table('DoctorSchedules')
            ->leftJoinSub(
                DB::table('Appointments')
                    ->select('schedule_id', DB::raw('COUNT(*) as booked_count'))
                    ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
                    ->groupBy('schedule_id'),
                'bk', 'bk.schedule_id', '=', 'DoctorSchedules.schedule_id'
            )
            ->where('DoctorSchedules.doctor_id', $request->doctor_id)
            ->where('DoctorSchedules.work_date', $request->work_date)
            ->where('DoctorSchedules.status', 'Hoạt động')
            ->select('DoctorSchedules.*', DB::raw('COALESCE(bk.booked_count,0) as booked_count'))
            ->get();

        return response()->json(['schedules' => $schedules, 'day_off' => false]);
    }

    // ================================================================
    // 1c. LƯU ĐẶT LỊCH — POST /dat-lich
    // ================================================================
    public function store(Request $request)
    {
        $request->validate([
            'schedule_id'      => 'required|integer|exists:DoctorSchedules,schedule_id',
            'service_id'       => 'nullable|integer|exists:Services,service_id',
            'appointment_time' => 'required',
            'note'             => 'nullable|string|max:255',
        ]);

        $userId = Auth::id();

        // Kiểm tra đã đặt lịch này chưa
        $alreadyBooked = DB::table('Appointments')
            ->where('user_id', $userId)
            ->where('schedule_id', $request->schedule_id)
            ->whereNotIn('status', ['Đã hủy', 'Dời lịch'])
            ->exists();

        if ($alreadyBooked) {
            return back()->withErrors(['msg' => 'Bạn đã đặt lịch khám cho khung giờ này rồi.'])->withInput();
        }

        // Kiểm tra còn slot không
        $schedule = DB::table('DoctorSchedules')->where('schedule_id', $request->schedule_id)->first();
        $booked   = DB::table('Appointments')
            ->where('schedule_id', $request->schedule_id)
            ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
            ->count();

        if ($booked >= $schedule->max_slot) {
            return back()->withErrors(['msg' => 'Khung giờ này đã hết chỗ. Vui lòng chọn giờ khác.'])->withInput();
        }

        $queueNumber = $booked + 1;

        DB::beginTransaction();
        try {
            $appointmentId = DB::table('Appointments')->insertGetId([
                'user_id'          => $userId,
                'schedule_id'      => $request->schedule_id,
                'service_id'       => $request->service_id ?: null,
                'appointment_time' => $request->appointment_time,
                'queue_number'     => $queueNumber,
                'status'           => 'Chờ xác nhận',
                'note'             => $request->note,
                'created_at'       => now(),
            ]);

            // Ghi thông báo
            DB::table('Notifications')->insert([
                'user_id'    => $userId,
                'notif_type' => 'Lịch hẹn',
                'title'      => 'Đặt lịch hẹn thành công',
                'content'    => 'Lịch khám vào ' . $request->appointment_time . '. Số thứ tự: #' . $queueNumber,
                'ref_id'     => $appointmentId,
                'ref_type'   => 'appointment',
                'is_read'    => false,
                'created_at' => now(),
            ]);

            // Ghi activity log
            DB::table('ActivityLogs')->insert([
                'user_id'    => $userId,
                'action'     => 'Đặt lịch hẹn #' . $appointmentId,
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Đặt lịch thất bại: ' . $e->getMessage()])->withInput();
        }

        return redirect()->route('booking.index')
            ->with('success', 'Đặt lịch hẹn thành công! Số thứ tự của bạn là #' . $queueNumber . '. Chúng tôi sẽ xác nhận sớm.');
    }

    // ================================================================
    // 2. DANH SÁCH LỊCH HẸN — GET /lich-hen
    // ================================================================
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = DB::table('Appointments')
            ->join('DoctorSchedules', 'Appointments.schedule_id', '=', 'DoctorSchedules.schedule_id')
            ->join('Doctors', 'DoctorSchedules.doctor_id', '=', 'Doctors.doctor_id')
            ->join('Departments', 'Doctors.department_id', '=', 'Departments.department_id')
            ->leftJoin('Services', 'Appointments.service_id', '=', 'Services.service_id')
            ->leftJoin('Rooms', 'DoctorSchedules.room_id', '=', 'Rooms.room_id')
            ->where('Appointments.user_id', Auth::id())
            ->select(
                'Appointments.*',
                'DoctorSchedules.work_date',
                'DoctorSchedules.start_time',
                'DoctorSchedules.end_time',
                'DoctorSchedules.slot_duration',
                'Doctors.full_name as doctor_name',
                'Doctors.avatar_url as doctor_avatar',
                'Doctors.price as doctor_price',
                'Departments.department_name',
                'Services.service_name',
                'Services.price as service_price',
                'Rooms.room_code',
                'Rooms.room_name'
            )
            ->orderBy('DoctorSchedules.work_date', 'desc')
            ->orderBy('Appointments.appointment_time', 'desc');

        // Bộ lọc trạng thái
        if ($status === 'upcoming') {
            $query->whereIn('Appointments.status', ['Chờ xác nhận', 'Đã xác nhận'])
                  ->where('DoctorSchedules.work_date', '>=', now()->toDateString());
        } elseif ($status === 'completed') {
            $query->where('Appointments.status', 'Hoàn thành');
        } elseif ($status === 'cancelled') {
            $query->whereIn('Appointments.status', ['Đã hủy', 'Dời lịch']);
        }

        $appointments = $query->paginate(8)->withQueryString();

        // Đếm theo từng tab
        $counts = DB::table('Appointments')
            ->where('user_id', Auth::id())
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN status IN ('Chờ xác nhận','Đã xác nhận') THEN 1 ELSE 0 END) as upcoming"),
                DB::raw("SUM(CASE WHEN status = 'Hoàn thành' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN status IN ('Đã hủy','Dời lịch') THEN 1 ELSE 0 END) as cancelled")
            )
            ->first();

        return view('booking.index', compact('appointments', 'counts', 'status'));
    }

    // ================================================================
    // 3. FORM DỜI LỊCH — GET /lich-hen/{id}/doi
    // ================================================================
    public function edit($id)
    {
        $appointment = DB::table('Appointments')
            ->join('DoctorSchedules', 'Appointments.schedule_id', '=', 'DoctorSchedules.schedule_id')
            ->join('Doctors', 'DoctorSchedules.doctor_id', '=', 'Doctors.doctor_id')
            ->join('Departments', 'Doctors.department_id', '=', 'Departments.department_id')
            ->leftJoin('Services', 'Appointments.service_id', '=', 'Services.service_id')
            ->where('Appointments.appointment_id', $id)
            ->where('Appointments.user_id', Auth::id())
            ->select(
                'Appointments.*',
                'DoctorSchedules.work_date', 'DoctorSchedules.start_time', 'DoctorSchedules.end_time',
                'Doctors.doctor_id', 'Doctors.full_name as doctor_name', 'Doctors.department_id',
                'Departments.department_name',
                'Services.service_name'
            )
            ->first();

        if (!$appointment) {
            return redirect()->route('booking.index')->withErrors(['msg' => 'Không tìm thấy lịch hẹn.']);
        }

        // Chỉ dời lịch khi chờ xác nhận hoặc đã xác nhận
        if (!in_array($appointment->status, ['Chờ xác nhận', 'Đã xác nhận'])) {
            return redirect()->route('booking.index')
                ->withErrors(['msg' => 'Lịch hẹn này không thể dời (trạng thái: ' . $appointment->status . ').']);
        }

        // Phải dời trước 2 tiếng
        $appointmentTime = \Carbon\Carbon::parse($appointment->work_date . ' ' . $appointment->start_time);
        if ($appointmentTime->diffInHours(now(), false) > -2) {
            return redirect()->route('booking.index')
                ->withErrors(['msg' => 'Chỉ có thể dời lịch trước giờ khám ít nhất 2 tiếng.']);
        }

        // Lịch trống của cùng bác sĩ trong 14 ngày tới
        $availableSchedules = DB::table('DoctorSchedules')
            ->leftJoinSub(
                DB::table('Appointments')
                    ->select('schedule_id', DB::raw('COUNT(*) as booked_count'))
                    ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
                    ->groupBy('schedule_id'),
                'bk', 'bk.schedule_id', '=', 'DoctorSchedules.schedule_id'
            )
            ->where('DoctorSchedules.doctor_id', $appointment->doctor_id)
            ->where('DoctorSchedules.schedule_id', '!=', $appointment->schedule_id)
            ->whereBetween('DoctorSchedules.work_date', [now()->addDay()->toDateString(), now()->addDays(14)->toDateString()])
            ->where('DoctorSchedules.status', 'Hoạt động')
            ->whereRaw('COALESCE(bk.booked_count, 0) < DoctorSchedules.max_slot')
            ->select('DoctorSchedules.*', DB::raw('COALESCE(bk.booked_count,0) as booked_count'))
            ->orderBy('DoctorSchedules.work_date')
            ->orderBy('DoctorSchedules.start_time')
            ->get();

        return view('booking.edit', compact('appointment', 'availableSchedules'));
    }

    // ================================================================
    // 3b. XỬ LÝ DỜI LỊCH — PUT /lich-hen/{id}/doi
    // ================================================================
    public function update(Request $request, $id)
    {
        $request->validate([
            'new_schedule_id'      => 'required|integer|exists:DoctorSchedules,schedule_id',
            'new_appointment_time' => 'required',
        ]);

        $appointment = Appointment::where('appointment_id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$appointment) {
            return redirect()->route('booking.index')->withErrors(['msg' => 'Không tìm thấy lịch hẹn.']);
        }

        if (!in_array($appointment->status, ['Chờ xác nhận', 'Đã xác nhận'])) {
            return redirect()->route('booking.index')
                ->withErrors(['msg' => 'Lịch hẹn này không thể dời.']);
        }

        // Kiểm tra slot mới còn chỗ
        $newSchedule  = DB::table('DoctorSchedules')->where('schedule_id', $request->new_schedule_id)->first();
        $bookedInNew  = DB::table('Appointments')
            ->where('schedule_id', $request->new_schedule_id)
            ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
            ->count();

        if ($bookedInNew >= $newSchedule->max_slot) {
            return back()->withErrors(['msg' => 'Khung giờ mới đã hết chỗ. Vui lòng chọn giờ khác.']);
        }

        DB::beginTransaction();
        try {
            $oldScheduleId = $appointment->schedule_id;

            // Cập nhật lịch hẹn
            $appointment->update([
                'schedule_id'        => $request->new_schedule_id,
                'appointment_time'   => $request->new_appointment_time,
                'status'             => 'Chờ xác nhận',
                'rescheduled_from'   => $oldScheduleId,
            ]);

            // Thông báo
            DB::table('Notifications')->insert([
                'user_id'    => Auth::id(),
                'notif_type' => 'Dời lịch',
                'title'      => 'Dời lịch hẹn thành công',
                'content'    => 'Lịch hẹn #' . $id . ' đã được dời sang ' . $request->new_appointment_time,
                'ref_id'     => $id,
                'ref_type'   => 'appointment',
                'is_read'    => false,
                'created_at' => now(),
            ]);

            DB::table('ActivityLogs')->insert([
                'user_id'    => Auth::id(),
                'action'     => 'Dời lịch hẹn #' . $id . ' sang lịch #' . $request->new_schedule_id,
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Dời lịch thất bại: ' . $e->getMessage()]);
        }

        return redirect()->route('booking.index')
            ->with('success', 'Dời lịch hẹn thành công! Lịch mới đang chờ xác nhận.');
    }

    // ================================================================
    // 4. HỦY LỊCH HẸN — POST /lich-hen/{id}/huy
    // ================================================================
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancel_reason' => 'nullable|string|max:255',
        ]);

        $appointment = Appointment::where('appointment_id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$appointment) {
            return redirect()->route('booking.index')->withErrors(['msg' => 'Không tìm thấy lịch hẹn.']);
        }

        if (!in_array($appointment->status, ['Chờ xác nhận', 'Đã xác nhận'])) {
            return redirect()->route('booking.index')
                ->withErrors(['msg' => 'Lịch hẹn này không thể hủy (trạng thái: ' . $appointment->status . ').']);
        }

        // Kiểm tra thời gian — phải hủy trước 2 tiếng
        $schedule = DB::table('DoctorSchedules')
            ->where('schedule_id', $appointment->schedule_id)->first();

        $appointmentTime = \Carbon\Carbon::parse($schedule->work_date . ' ' . $schedule->start_time);
        if ($appointmentTime->diffInHours(now(), false) > -2) {
            return redirect()->route('booking.index')
                ->withErrors(['msg' => 'Chỉ có thể hủy lịch trước giờ khám ít nhất 2 tiếng.']);
        }

        DB::beginTransaction();
        try {
            $appointment->update([
                'status'        => 'Đã hủy',
                'cancel_reason' => $request->cancel_reason ?: 'Bệnh nhân tự hủy',
            ]);

            DB::table('Notifications')->insert([
                'user_id'    => Auth::id(),
                'notif_type' => 'Hủy lịch',
                'title'      => 'Hủy lịch hẹn thành công',
                'content'    => 'Lịch hẹn #' . $id . ' đã được hủy.' . ($request->cancel_reason ? ' Lý do: ' . $request->cancel_reason : ''),
                'ref_id'     => $id,
                'ref_type'   => 'appointment',
                'is_read'    => false,
                'created_at' => now(),
            ]);

            DB::table('ActivityLogs')->insert([
                'user_id'    => Auth::id(),
                'action'     => 'Hủy lịch hẹn #' . $id,
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('booking.index')
                ->withErrors(['msg' => 'Hủy lịch thất bại: ' . $e->getMessage()]);
        }

        return redirect()->route('booking.index')
            ->with('success', 'Đã hủy lịch hẹn #' . $id . ' thành công.');
    }
}
