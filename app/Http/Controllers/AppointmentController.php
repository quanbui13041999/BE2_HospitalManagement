<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentConfirmed;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentRescheduled;
use App\Services\DoctorSuggestionService;
use App\Services\DoctorTimeslotService;
use App\Services\AppointmentQueueService;
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

        // ✅ FIX: Calculate appointment_timeEnd based on slot_duration
        $appointmentDatetime = $request->work_date . ' ' . $request->appointment_time . ':00';
        $appointmentEndtime = Carbon::parse($appointmentDatetime)
            ->addMinutes($schedule->slot_duration ?? 15)
            ->format('Y-m-d H:i:s');

        // ✅ FIX: Calculate queue_number for THIS SPECIFIC appointment_time ONLY (not entire schedule)
        $queueNumber = DB::table('appointments')
            ->where('schedule_id', $request->schedule_id)
            ->whereRaw("DATE_FORMAT(appointment_time, '%H:%i') = ?", [substr($request->appointment_time, 0, 5)])
            ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot', 'Đã khám'])
            ->count() + 1;
        
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
                        'appointment_timeEnd' => $appointmentEndtime,
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
                    'appointment_timeEnd' => $appointmentEndtime,
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
        // ── Thêm join reviews ──────────────────────────────────────────
        ->leftJoin('reviews', function ($join) use ($userId) {
            $join->on('reviews.appointment_id', '=', 'appointments.appointment_id')
                 ->where('reviews.user_id', '=', $userId);
        })
        // ───────────────────────────────────────────────────────────────
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
            'rooms.room_name',
            // ── Thêm các cột review ──────────────────────────────────
            'reviews.review_id',
            'reviews.rating      as review_rating',
            'reviews.comment     as review_comment',
            'reviews.doctor_reply',
            'reviews.created_at  as review_created_at',
            // ─────────────────────────────────────────────────────────
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
     * Summary of suggest (gọi ý bác sĩ tự động)
     * @param Request $request
     * thuật toán scoring (100):
     * - 40% tỉ lệ slot còn trống (bác sĩ trong nhiều lịch → ưu tiên)
     * - 35% đánh giá trung bình (avg_rating / 5 * 35)
     * - 15% năm kinh nghiệm (capped 20 năm → 15đ)
     * - 10% số lượt đánh giá
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggest(Request $request, DoctorSuggestionService $suggestionService)
    {
        $request->validate([
            'department_id' => 'required|integer|exists:departments,department_id',
            'work_date' => 'required|date|after_or_equal:today',
        ]);

        $suggested = $suggestionService->suggestTopDoctors(
            (int) $request->department_id,
            $request->work_date
        );

        return response()->json(['suggested' => $suggested]);
    }

    public function timeslots(Request $request, DoctorTimeslotService $timeslotService)
    {
        $request->validate([
            'doctor_id' => 'required|integer|exists:doctors,doctor_id',
            'work_date' => 'required|date|after_or_equal:today',
        ]);

        $result = $timeslotService->getTimeslots(
            (int) $request->doctor_id,
            $request->work_date
        );

        return response()->json($result);
    }

    // ================================================================
    // 5. ƯỚC LƯỢNG THỜI GIAN CHỜ — GET /api/appointments/queue-info
    // ================================================================
    /**
     * Lấy thông tin hàng đợi và ước lượng thời gian chờ
     * 
     * @param Request $request
     * @param AppointmentQueueService $queueService
     * @return \Illuminate\Http\JsonResponse
     * 
     * Dữ liệu trả về:
     * {
     *   "success": true,
     *   "queue_number": 3,           // Số thứ tự của người dùng hiện tại
     *   "people_ahead": 2,           // Số người đứng trước
     *   "estimated_wait_minutes": 30, // Thời gian chờ dự kiến (phút)
     *   "schedule_info": {
     *     "start_time": "09:00",
     *     "slot_duration": 15,
     *     "max_slot": 20
     *   },
     *   "queue_details": [          // Danh sách những người đứng trước
     *     {
     *       "queue_number": 1,
     *       "status": "Đã xác nhận",
     *       "abbreviated_name": "N.V.A." 
     *     }
     *   ]
     * }
     */
    public function getQueueInfo(Request $request, AppointmentQueueService $queueService)
    {
        $request->validate([
            'schedule_id' => 'required|integer|exists:doctorschedules,schedule_id',
            'appointment_time' => 'nullable|string',
            'appointment_id' => 'nullable|integer|exists:appointments,appointment_id',
        ]);

        $queueInfo = $queueService->getQueueInfo(
            (int) $request->schedule_id,
            $request->appointment_time,
            $request->appointment_id ? (int) $request->appointment_id : null
        );

        if (!$queueInfo['success']) {
            return response()->json($queueInfo, 404);
        }

        return response()->json($queueInfo);
    }
}