<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentConfirmed;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentRescheduled;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class AppointmentController extends Controller
{

    // ================================================================
    // 1. FORM ĐẶT LỊCH — GET /dat-lich
    // ================================================================
    public function create()
    {
        $departments = DB::table('departments')
            ->where('status', 1)
            ->orderBy('department_name')
            ->get();

        $services = DB::table('services')
            ->leftJoin('serviceprices', function ($join) {
                $join->on('serviceprices.service_id', '=', 'services.service_id')
                    ->where('serviceprices.price_type', 'Thường')
                    ->whereNull('serviceprices.end_date');
            })
            ->where('services.status', 1)
            ->select('services.*', 'serviceprices.price')
            ->orderBy('services.service_name')
            ->get();

        $doctorsByDept = DB::table('doctors')
            ->leftJoinSub(
                DB::table('reviews')
                    ->select(
                        'doctor_id',
                        DB::raw('ROUND(AVG(rating),1) as avg_rating'),
                        DB::raw('COUNT(*) as total_reviews')
                    )
                    ->groupBy('doctor_id'),
                'rv',
                'rv.doctor_id',
                '=',
                'doctors.doctor_id'
            )
            ->where('doctors.status', 1)
            ->select(
                'doctors.doctor_id',
                'doctors.department_id',
                'doctors.full_name',
                'doctors.experience',
                'doctors.price',
                'doctors.avatar_url',
                'doctors.bio',
                DB::raw('COALESCE(rv.avg_rating, 0) as avg_rating'),
                DB::raw('COALESCE(rv.total_reviews, 0) as total_reviews')
            )
            ->get()
            ->groupBy('department_id')
            ->mapWithKeys(fn($group, $key) => [(string)$key => $group])
            ->toArray();

        $scheduleData = DB::table('doctorschedules')
            ->leftJoinSub(
                DB::table('appointments')
                    ->select('schedule_id', DB::raw('COUNT(*) as booked_count'))
                    ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
                    ->groupBy('schedule_id'),
                'bk',
                'bk.schedule_id',
                '=',
                'doctorschedules.schedule_id'
            )
            ->whereBetween('doctorschedules.work_date', [
                now()->toDateString(),
                now()->addDays(13)->toDateString(),
            ])
            ->where('doctorschedules.status', 'Hoạt động')
            ->select('doctorschedules.*', DB::raw('COALESCE(bk.booked_count,0) as booked_count'))
            ->get()
            ->groupBy(fn($r) => $r->doctor_id . '_' . $r->work_date)
            ->toArray();

        return view('appointments.create', compact('departments', 'services', 'doctorsByDept', 'scheduleData'));
    }

    // ================================================================
    // 1b. AJAX SCHEDULES — GET /api/schedules
    // ================================================================
    public function getSchedules(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|integer|exists:doctors,doctor_id',
            'work_date' => 'required|date|after_or_equal:today',
        ]);

        $schedules = DB::table('doctorschedules')
            ->leftJoinSub(
                DB::table('appointments')
                    ->select('schedule_id', DB::raw('COUNT(*) as booked_count'))
                    ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
                    ->groupBy('schedule_id'),
                'bk',
                'bk.schedule_id',
                '=',
                'doctorschedules.schedule_id'
            )
            ->where('doctorschedules.doctor_id', $request->doctor_id)
            ->where('doctorschedules.work_date', $request->work_date)
            ->where('doctorschedules.status', 'Hoạt động')
            ->select(
                'doctorschedules.schedule_id',
                'doctorschedules.work_date',
                'doctorschedules.start_time',
                'doctorschedules.end_time',
                'doctorschedules.max_slot',
                DB::raw('COALESCE(bk.booked_count, 0) as booked_count')
            )
            ->orderBy('doctorschedules.start_time')
            ->get();

        return response()->json(['schedules' => $schedules]);
    }

    // ================================================================
    // 1c. LƯU ĐẶT LỊCH — POST /dat-lich
    // ================================================================
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để đặt lịch hẹn.');
        }

        $request->validate([
            'schedule_id' => 'required|integer|exists:doctorschedules,schedule_id',
            'service_id' => 'nullable|integer|exists:services,service_id',
            'work_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|string|max:10',
            'note' => 'nullable|string|max:255',
        ], [
            'schedule_id.required' => 'Vui lòng chọn khung giờ khám.',
            'schedule_id.exists' => 'Khung giờ không hợp lệ.',
            'work_date.after_or_equal' => 'Ngày khám phải từ hôm nay trở đi.',
            'appointment_time.required' => 'Vui lòng chọn giờ khám.',
        ]);

        $userId = Auth::id();

        $alreadyBooked = DB::table('appointments')
            ->where('user_id', $userId)
            ->where('schedule_id', $request->schedule_id)
            ->whereNotIn('status', ['Đã hủy', 'Dời lịch'])
            ->exists();

        if ($alreadyBooked) {
            return back()
                ->withErrors(['msg' => 'Bạn đã đặt lịch khám cho khung giờ này rồi.'])
                ->withInput();
        }

        $schedule = DB::table('doctorschedules')
            ->where('schedule_id', $request->schedule_id)
            ->where('status', 'Hoạt động')
            ->first();

        if (!$schedule) {
            return back()
                ->withErrors(['msg' => 'Lịch khám không tồn tại hoặc đã ngừng hoạt động.'])
                ->withInput();
        }

        if ($schedule->work_date !== $request->work_date) {
            return back()
                ->withErrors(['msg' => 'Ngày khám không khớp với lịch đã chọn.'])
                ->withInput();
        }

        $booked = DB::table('appointments')
            ->where('schedule_id', $request->schedule_id)
            ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
            ->count();

        if ($booked >= $schedule->max_slot) {
            return back()
                ->withErrors(['msg' => 'Khung giờ này đã hết chỗ. Vui lòng chọn giờ khác.'])
                ->withInput();
        }

        $queueNumber = $booked + 1;
        $appointmentDatetime = $request->work_date . ' ' . $request->appointment_time . ':00';
        $appointmentId = null;

        // ── Transaction: chỉ DB, KHÔNG có mail bên trong ──
        DB::beginTransaction();
        try {
            $existing = DB::table('appointments')
                ->where('user_id', $userId)
                ->where('schedule_id', $request->schedule_id)
                ->first();

            if ($existing) {
                // Cập nhật bản ghi cũ (đã hủy/dời)
                DB::table('appointments')
                    ->where('appointment_id', $existing->appointment_id)
                    ->update([
                        'service_id' => $request->service_id ?: null,
                        'appointment_time' => $appointmentDatetime,
                        'queue_number' => $queueNumber,
                        'status' => 'Chờ xác nhận',
                        'note' => $request->note,
                        'cancel_reason' => null,
                        'slot_hold_expire' => null,
                        'rescheduled_from' => null,
                    ]);
                $appointmentId = $existing->appointment_id;
            } else {
                $appointmentId = DB::table('appointments')->insertGetId([
                    'user_id' => $userId,
                    'schedule_id' => $request->schedule_id,
                    'service_id' => $request->service_id ?: null,
                    'appointment_time' => $appointmentDatetime,
                    'queue_number' => $queueNumber,
                    'status' => 'Chờ xác nhận',
                    'note' => $request->note,
                    'created_at' => now(),
                ]);
            }

            DB::table('notifications')->insert([
                'user_id' => $userId,
                'notif_type' => 'Lịch hẹn',
                'title' => 'Đặt lịch hẹn thành công',
                'content' => 'Lịch khám lúc ' . $request->appointment_time
                    . ' ngày ' . Carbon::parse($request->work_date)->format('d/m/Y')
                    . '. Số thứ tự: #' . $queueNumber,
                'ref_id' => $appointmentId,
                'ref_type' => 'appointment',
                'is_read' => false,
                'created_at' => now(),
            ]);

            DB::table('activitylogs')->insert([
                'user_id' => $userId,
                'action' => 'Đặt lịch hẹn #' . $appointmentId,
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['msg' => 'Đặt lịch thất bại: ' . $e->getMessage()])
                ->withInput();
        }

        // ── Gửi mail SAU khi commit thành công — nằm ngoài transaction ──
        $appointmentForMail = DB::table('appointments')
            ->join('doctorschedules', 'appointments.schedule_id', '=', 'doctorschedules.schedule_id')
            ->join('doctors', 'doctorschedules.doctor_id', '=', 'doctors.doctor_id')
            ->join('departments', 'doctors.department_id', '=', 'departments.department_id')
            ->where('appointments.appointment_id', $appointmentId)
            ->select(
                'appointments.*',
                'doctors.full_name as doctor_name',
                'departments.department_name'
            )
            ->first();

        $user = Auth::user();
        if ($user && $user->email) {
            try {
                Mail::to($user->email)->send(
                    new AppointmentConfirmed($user, $appointmentForMail)
                );
            } catch (\Exception $mailError) {
                Log::warning('Failed to send appointment confirmation email', [
                    'appointment_id' => $appointmentId,
                    'error' => $mailError->getMessage(),
                ]);
            }
        }

        return redirect()->route('appointments.index')
            ->with('success', 'Đặt lịch hẹn thành công! Số thứ tự: #' . $queueNumber . '. Chúng tôi sẽ xác nhận sớm.');
    }

    // ================================================================
    // 2. DANH SÁCH LỊCH HẸN — GET /lich-hen
    // ================================================================
    public function index(Request $request)
    {
        $userId = Auth::id();

        $counts = DB::table('appointments')
            ->where('user_id', $userId)
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status IN ('Chờ xác nhận','Đã xác nhận') AND appointment_time >= NOW() THEN 1 ELSE 0 END) as upcoming"),
                DB::raw("SUM(CASE WHEN status = 'Đã khám' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN status IN ('Đã hủy','Dời lịch') THEN 1 ELSE 0 END) as cancelled")
            )
            ->first();

        $query = DB::table('appointments')
            ->join('doctorschedules', 'appointments.schedule_id', '=', 'doctorschedules.schedule_id')
            ->join('doctors', 'doctorschedules.doctor_id', '=', 'doctors.doctor_id')
            ->join('departments', 'doctors.department_id', '=', 'departments.department_id')
            ->leftJoin('services', 'appointments.service_id', '=', 'services.service_id')
            ->leftJoin('rooms', 'doctorschedules.room_id', '=', 'rooms.room_id')
            ->where('appointments.user_id', $userId)
            ->select(
                'appointments.*',
                'doctorschedules.work_date',
                'doctorschedules.start_time',
                'doctorschedules.end_time',
                'doctorschedules.slot_duration',
                'doctors.doctor_id',
                'doctors.full_name as doctor_name',
                'doctors.avatar_url as doctor_avatar',
                'doctors.price as doctor_price',
                'departments.department_name',
                'services.service_name',
                'rooms.room_code',
                'rooms.room_name'
            );

        $status = $request->get('status', 'all');
        if ($status === 'upcoming') {
            $query->whereIn('appointments.status', ['Chờ xác nhận', 'Đã xác nhận'])
                ->where('doctorschedules.work_date', '>=', now()->toDateString());
        } elseif ($status === 'completed') {
            $query->where('appointments.status', 'Đã khám');
        } elseif ($status === 'cancelled') {
            $query->whereIn('appointments.status', ['Đã hủy', 'Dời lịch']);
        }

        $sort = $request->get('sort', 'desc');
        $query->orderBy('doctorschedules.work_date', $sort === 'asc' ? 'asc' : 'desc')
            ->orderBy('appointments.appointment_time', $sort === 'asc' ? 'asc' : 'desc');

        $appointments = $query->paginate(8)->withQueryString();

        return view('appointments.index', compact('appointments', 'counts', 'status'));
    }

    // ================================================================
    // 3. FORM DỜI LỊCH — GET /lich-hen/{id}/doi
    // ================================================================
    public function edit($id)
    {
        $appointment = DB::table('appointments')
            ->join('doctorschedules', 'appointments.schedule_id', '=', 'doctorschedules.schedule_id')
            ->join('doctors', 'doctorschedules.doctor_id', '=', 'doctors.doctor_id')
            ->join('departments', 'doctors.department_id', '=', 'departments.department_id')
            ->leftJoin('services', 'appointments.service_id', '=', 'services.service_id')
            ->where('appointments.appointment_id', $id)
            ->where('appointments.user_id', Auth::id())
            ->select(
                'appointments.*',
                'doctorschedules.work_date',
                'doctorschedules.start_time',
                'doctorschedules.end_time',
                'doctors.doctor_id',
                'doctors.full_name as doctor_name',
                'doctors.department_id',
                'departments.department_name',
                'services.service_name'
            )
            ->first();

        if (!$appointment) {
            return redirect()->route('appointments.index')
                ->withErrors(['msg' => 'Không tìm thấy lịch hẹn.']);
        }

        if (!in_array($appointment->status, ['Chờ xác nhận', 'Đã xác nhận'])) {
            return redirect()->route('appointments.index')
                ->withErrors(['msg' => 'Lịch hẹn này không thể dời (trạng thái: ' . $appointment->status . ').']);
        }

        $appointmentTime = Carbon::parse($appointment->work_date . ' ' . $appointment->start_time);
        $hoursUntilAppointment = $appointmentTime->diffInHours(now(), false);
        
        // Check if appointment is in the past
        if ($hoursUntilAppointment >= 0) {
            return redirect()->route('appointments.index')
                ->withErrors(['msg' => 'Lịch khám này đã qua hoặc đang diễn ra. Không thể dời lịch.']);
        }
        
        // Check if appointment is within 2 hours
        if ($hoursUntilAppointment > -2) {
            return redirect()->route('appointments.index')
                ->withErrors(['msg' => 'Chỉ có thể dời lịch trước giờ khám ít nhất 2 tiếng.']);
        }

        $availableSchedules = DB::table('doctorschedules')
            ->leftJoinSub(
                DB::table('appointments')
                    ->select('schedule_id', DB::raw('COUNT(*) as booked_count'))
                    ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
                    ->groupBy('schedule_id'),
                'bk',
                'bk.schedule_id',
                '=',
                'doctorschedules.schedule_id'
            )
            ->where('doctorschedules.doctor_id', $appointment->doctor_id)
            ->where('doctorschedules.schedule_id', '!=', $appointment->schedule_id)
            ->whereBetween('doctorschedules.work_date', [
                now()->addDay()->toDateString(),
                now()->addDays(14)->toDateString(),
            ])
            ->where('doctorschedules.status', 'Hoạt động')
            ->whereRaw('COALESCE(bk.booked_count, 0) < doctorschedules.max_slot')
            ->select('doctorschedules.*', DB::raw('COALESCE(bk.booked_count,0) as booked_count'))
            ->orderBy('doctorschedules.work_date')
            ->orderBy('doctorschedules.start_time')
            ->get();

        return view('appointments.edit', compact('appointment', 'availableSchedules'));
    }

    // ================================================================
    // 3b. XỬ LÝ DỜI LỊCH — PUT /lich-hen/{id}/doi
    // ================================================================
    public function update(Request $request, $id)
    {
        $request->validate([
            'new_schedule_id' => 'required|integer|exists:doctorschedules,schedule_id',
            'new_appointment_time' => 'required|string|max:10',
            'reschedule_reason' => 'nullable|string|max:255',
        ], [
            'new_schedule_id.required' => 'Vui lòng chọn khung giờ mới.',
            'new_schedule_id.exists' => 'Khung giờ không hợp lệ.',
        ]);

        $appointment = DB::table('appointments')
            ->where('appointment_id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$appointment) {
            return redirect()->route('appointments.index')
                ->withErrors(['msg' => 'Không tìm thấy lịch hẹn.']);
        }

        if (!in_array($appointment->status, ['Chờ xác nhận', 'Đã xác nhận'])) {
            return redirect()->route('appointments.index')
                ->withErrors(['msg' => 'Lịch hẹn này không thể dời.']);
        }

        if ((int) $request->new_schedule_id === (int) $appointment->schedule_id) {
            return back()->withErrors(['msg' => 'Vui lòng chọn lịch khác với lịch hiện tại.']);
        }

        $newSchedule = DB::table('doctorschedules')
            ->where('schedule_id', $request->new_schedule_id)
            ->where('status', 'Hoạt động')
            ->first();

        if (!$newSchedule) {
            return back()->withErrors(['msg' => 'Lịch khám mới không hợp lệ.']);
        }

        if (Carbon::parse($newSchedule->work_date)->isPast()) {
            return back()->withErrors(['msg' => 'Ngày dời phải là ngày trong tương lai.']);
        }

        $bookedInNew = DB::table('appointments')
            ->where('schedule_id', $request->new_schedule_id)
            ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
            ->count();

        if ($bookedInNew >= $newSchedule->max_slot) {
            return back()->withErrors(['msg' => 'Khung giờ mới đã hết chỗ. Vui lòng chọn giờ khác.']);
        }

        $newDatetime = $newSchedule->work_date . ' ' . $request->new_appointment_time . ':00';

        DB::beginTransaction();
        try {
            DB::table('appointments')
                ->where('appointment_id', $id)
                ->update([
                    'schedule_id' => $request->new_schedule_id,
                    'appointment_time' => $newDatetime,
                    'queue_number' => $bookedInNew + 1,
                    'status' => 'Chờ xác nhận',
                    'cancel_reason' => $request->reschedule_reason
                        ? 'Dời lịch: ' . $request->reschedule_reason
                        : 'Dời sang lịch mới',
                    'rescheduled_from' => $appointment->schedule_id,
                ]);

            DB::table('notifications')->insert([
                'user_id' => Auth::id(),
                'notif_type' => 'Lịch hẹn',
                'title' => 'Dời lịch hẹn thành công',
                'content' => 'Lịch hẹn #' . $id . ' đã được dời sang '
                    . Carbon::parse($newDatetime)->format('H:i d/m/Y'),
                'ref_id' => $id,
                'ref_type' => 'appointment',
                'is_read' => false,
                'created_at' => now(),
            ]);

            DB::table('activitylogs')->insert([
                'user_id' => Auth::id(),
                'action' => 'Dời lịch hẹn #' . $id . ' sang schedule #' . $request->new_schedule_id,
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Dời lịch thất bại: ' . $e->getMessage()]);
        }

        // ── Gửi mail SAU commit ──
        $updatedAppointment = DB::table('appointments')
            ->join('doctorschedules', 'appointments.schedule_id', '=', 'doctorschedules.schedule_id')
            ->join('doctors', 'doctorschedules.doctor_id', '=', 'doctors.doctor_id')
            ->join('departments', 'doctors.department_id', '=', 'departments.department_id')
            ->where('appointments.appointment_id', $id)
            ->select(
                'appointments.*',
                'doctors.full_name as doctor_name',
                'departments.department_name'
            )
            ->first();

        $user = Auth::user();
        if ($user && $user->email) {
            try {
                Mail::to($user->email)->send(
                    new AppointmentRescheduled($user, $updatedAppointment)
                );
            } catch (\Exception $mailError) {
                Log::warning('Failed to send appointment rescheduled email', [
                    'appointment_id' => $id,
                    'error' => $mailError->getMessage(),
                ]);
            }
        }

        return redirect()->route('appointments.index')
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

        $appointment = DB::table('appointments')
            ->where('appointment_id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$appointment) {
            return redirect()->route('appointments.index')
                ->withErrors(['msg' => 'Không tìm thấy lịch hẹn.']);
        }

        if (!in_array($appointment->status, ['Chờ xác nhận', 'Đã xác nhận'])) {
            return redirect()->route('appointments.index')
                ->withErrors(['msg' => 'Lịch hẹn này không thể hủy (trạng thái: ' . $appointment->status . ').']);
        }

        $schedule = DB::table('doctorschedules')
            ->where('schedule_id', $appointment->schedule_id)
            ->first();

        $appointmentTime = Carbon::parse($schedule->work_date . ' ' . $schedule->start_time);
        $hoursUntilAppointment = $appointmentTime->diffInHours(now(), false);
        
        // Check if appointment is in the past
        if ($hoursUntilAppointment >= 0) {
            return redirect()->route('appointments.index')
                ->withErrors(['msg' => 'Lịch khám này đã qua hoặc đang diễn ra. Không thể hủy lịch.']);
        }
        
        // Check if appointment is within 2 hours
        if ($hoursUntilAppointment > -2) {
            return redirect()->route('appointments.index')
                ->withErrors(['msg' => 'Chỉ có thể hủy lịch trước giờ khám ít nhất 2 tiếng.']);
        }

        DB::beginTransaction();
        try {
            DB::table('appointments')
                ->where('appointment_id', $id)
                ->update([
                    'status' => 'Đã hủy',
                    'cancel_reason' => $request->cancel_reason ?: 'Bệnh nhân tự hủy',
                ]);

            DB::table('notifications')->insert([
                'user_id' => Auth::id(),
                'notif_type' => 'Lịch hẹn',
                'title' => 'Hủy lịch hẹn thành công',
                'content' => 'Lịch hẹn #' . $id . ' đã được hủy.'
                    . ($request->cancel_reason ? ' Lý do: ' . $request->cancel_reason : ''),
                'ref_id' => $id,
                'ref_type' => 'appointment',
                'is_read' => false,
                'created_at' => now(),
            ]);

            DB::table('activitylogs')->insert([
                'user_id' => Auth::id(),
                'action' => 'Hủy lịch hẹn #' . $id,
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('appointments.index')
                ->withErrors(['msg' => 'Hủy lịch thất bại: ' . $e->getMessage()]);
        }

        // ── Gửi mail SAU commit ──
        $cancelledAppointment = DB::table('appointments')
            ->join('doctorschedules', 'appointments.schedule_id', '=', 'doctorschedules.schedule_id')
            ->join('doctors', 'doctorschedules.doctor_id', '=', 'doctors.doctor_id')
            ->leftJoin('departments', 'doctors.department_id', '=', 'departments.department_id')
            ->where('appointments.appointment_id', $id)
            ->select(
                'appointments.*',
                'doctors.full_name as doctor_name',
                'departments.department_name'
            )
            ->first();

        $user = Auth::user();
        if ($user && $user->email) {
            try {
                Mail::to($user->email)->send(
                    new AppointmentCancelled($user, $cancelledAppointment)
                );
            } catch (\Exception $mailError) {
                Log::warning('Failed to send appointment cancelled email', [
                    'appointment_id' => $id,
                    'error' => $mailError->getMessage(),
                ]);
            }
        }

        return redirect()->route('appointments.index')
            ->with('success', 'Đã hủy lịch hẹn #' . $id . ' thành công.');
    }

    /**
     * Summary of suggest (goi y bac si tu dong)
     * @param Request $request
     * thuat toan scoring scoring (100)
     * 40% ti le slot con trong -> (bac si trong nhieu lich -> uu tien)
     * 35% danh gia trung binh (avg_rating / 5 * 35)
     * 15% nam kinh nghiem (capped 20 nam -> 15d)
     * 10% so luot danh gia 
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggest(Request $request)
    {
        $request->validate([
            'department_id' => 'required|integer|exists:departments,department_id',
            'work_date' => 'required|date|after_or_equal:today',
        ]);

        $deptId = (int) $request->department_id;
        $workDate = $request->work_date;

        // lay tat ca danh sach bac si active thuoc khoa 
        $doctors = DB::table('doctors')->leftJoinSub(
            DB::table('reviews')->select(
                'doctor_id',
                DB::raw('ROUND(AVG(rating), 2) as avg_rating'),
                DB::raw('COUNT(*) as total_reviews')
            )->groupBy('doctor_id'),
            'rv',
            'rv.doctor_id',
            '=',
            'doctors.doctor_id'
        )->where('doctors.department_id', $deptId)->where('doctors.status', 1)->select(
                'doctors.doctor_id',
                'doctors.full_name',
                'doctors.experience',
                'doctors.price',
                'doctors.avatar_url',
                'doctors.bio',
                DB::raw('COALESCE(rv.avg_rating, 0) as avg_rating'),
                DB::raw('COALESCE(rv.total_reviews, 0) as total_reviews')
            )->get();

        if ($doctors->isEmpty()) {
            return response()->json(['suggested' => []]);
        }

        $doctorIds = $doctors->pluck('doctor_id')->toArray();

        // kiem tra ngay nghi  
        $daysOff = DB::table('doctordaysoff')
            ->whereIn('doctor_id', $doctorIds)
            ->where('off_date', $workDate)
            ->pluck('doctor_id')
            ->flip()
            ->toArray();

        // dem slot trong theo tung bac si trong ngay
        $scheduleStats = DB::table('doctorschedules')
            ->leftJoinSub(
                DB::table('appointments')
                    ->select('schedule_id', DB::raw('COUNT(*) as booked_count'))
                    ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
                    ->groupBy('schedule_id'),
                'bk',
                'bk.schedule_id',
                '=',
                'doctorschedules.schedule_id'
            )
            ->whereIn('doctorschedules.doctor_id', $doctorIds)
            ->where('doctorschedules.work_date', $workDate)
            ->where('doctorschedules.status', 'Hoạt động')
            ->select(
                'doctorschedules.doctor_id',
                DB::raw('SUM(doctorschedules.max_slot) as total_slots'),
                DB::raw('SUM(COALESCE(bk.booked_count, 0)) as booked_count')
            )
            ->groupBy('doctorschedules.doctor_id')
            ->get()
            ->keyBy('doctor_id');

        // tinh diem loc 
        $scored = [];

        foreach ($doctors as $doc) {
            // bo qua bac si da nghi
            if (isset($daysOff[$doc->doctor_id])) {
                continue;
            }

            $stats = $scheduleStats->get($doc->doctor_id);
            $totalSlots = $stats ? (int) $stats->total_slots : 0;
            $bookedCount = $stats ? (int) $stats->booked_count : 0;
            $available = max(0, $totalSlots - $bookedCount);

            // bo cac bac si khong co lich or da full
            if ($totalSlots === 0 || $available === 0) {
                continue;
            }

            $avgRating = (float) $doc->avg_rating;
            $totalReviews = (int) $doc->total_reviews;
            $experience = (int) $doc->experience;

            // Scoring
            $slotScore = ($available / $totalSlots) * 40;
            $ratingScore = ($avgRating / 5.0) * 35;
            $expScore = (min($experience, 20) / 20) * 15;
            $reviewScore = (min($totalReviews, 50) / 50) * 10;
            $score = $slotScore + $ratingScore + $expScore + $reviewScore;

            $scored[] = [
                'doctor_id' => $doc->doctor_id,
                'full_name' => $doc->full_name,
                'experience' => $experience,
                'price' => $doc->price,
                'avatar_url' => $doc->avatar_url,
                'bio' => $doc->bio,
                'avg_rating' => $avgRating,
                'total_reviews' => $totalReviews,
                'available_slots' => $available,
                'total_slots' => $totalSlots,
                'score' => round($score, 2),
            ];
        }

        // sap xep lay top 3
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        $top3 = array_slice($scored, 0, 3);

        return response()->json(['suggested' => $top3]);
    }

    public function timeslots(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|integer|exists:doctors,doctor_id',
            'work_date' => 'required|date|after_or_equal:today',
        ]);

        $doctorId = (int) $request->doctor_id;
        $workDate = $request->work_date;

        // check ngay nghi
        $isDayOff = DB::table('doctordaysoff')
            ->where('doctor_id', $doctorId)
            ->where('off_date', $workDate)
            ->exists();

        if ($isDayOff) {
            return response()->json(['day_off' => true, 'slots' => []]);
        }

        // lay lich bac si
        $schedules = DB::table('doctorschedules')
            ->where('doctor_id', $doctorId)
            ->where('work_date', $workDate)
            ->where('status', 'Hoạt động')
            ->select(
                'schedule_id',
                'start_time',
                'end_time',
                'slot_duration',
                'max_slot'
            )
            ->orderBy('start_time')
            ->get();

        if ($schedules->isEmpty()) {
            return response()->json(['day_off' => false, 'slots' => []]);
        }

        // lay booking theo tung slot
        $bookings = DB::table('appointments')
            ->select(
                'schedule_id',
                'appointment_time',
                DB::raw('COUNT(*) as booked_count')
            )
            ->whereIn('schedule_id', $schedules->pluck('schedule_id'))
            ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
            ->groupBy('schedule_id', 'appointment_time')
            ->get()
            ->groupBy('schedule_id');

        // 4. Sinh slot
        $slots = [];

        foreach ($schedules as $sch) {

            // map booking theo time cho lich schedule nay
            $bookingMap = [];

            if (isset($bookings[$sch->schedule_id])) {
                foreach ($bookings[$sch->schedule_id] as $b) {
                    $bookingMap[$b->appointment_time] = (int) $b->booked_count;
                }
            }

            // tach gio
            [$sh, $sm] = array_map('intval', explode(':', $sch->start_time));
            [$eh, $em] = array_map('intval', explode(':', $sch->end_time));

            $duration = (int) $sch->slot_duration;
            $maxSlot = (int) $sch->max_slot;
            $endMins = $eh * 60 + $em;

            $curH = $sh;
            $curM = $sm;

            while ($curH * 60 + $curM + $duration <= $endMins) {

                $timeStr = sprintf('%02d:%02d', $curH, $curM);

                $endH = $curH + intdiv($curM + $duration, 60);
                $endM = ($curM + $duration) % 60;
                $endTimeStr = sprintf('%02d:%02d', $endH, $endM);

                // lay booking theo tung slot
                $booked = $bookingMap[$timeStr] ?? 0;

                $isBooked = ($booked >= $maxSlot);

                $slots[] = [
                    'schedule_id' => $sch->schedule_id,
                    'time' => $timeStr,
                    'end_time' => $endTimeStr,
                    'is_booked' => $isBooked,
                    'max_slot' => $maxSlot,
                    'booked' => $booked,
                ];

                // tang thoi gian
                $curM += $duration;
                if ($curM >= 60) {
                    $curH += intdiv($curM, 60);
                    $curM = $curM % 60;
                }
            }
        }

        // 5. sort lai
        usort($slots, fn($a, $b) => strcmp($a['time'], $b['time']));

        return response()->json(['day_off' => false, 'slots' => $slots]);
    }
}