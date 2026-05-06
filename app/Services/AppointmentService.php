<?php

namespace App\Services;

use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmed;
use App\Mail\AppointmentRescheduled;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * AppointmentService
 * 
 * Xử lý toàn bộ business logic liên quan tới appointment
 * - Tạo, dời, hủy lịch khám
 * - Lấy dữ liệu form, danh sách lịch
 * - Validation logic
 * - Transaction management
 */
class AppointmentService
{
    /**
     * Lấy dữ liệu cho form tạo lịch khám
     */
    public function getCreateFormData(): array
    {
        $user = Auth::user();
        
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

        $scheduleData = $this->getAvailableSchedules();

        return compact('departments', 'services', 'doctorsByDept', 'scheduleData', 'user');
    }

    /**
     * Lấy lịch khám có sẵn cho 14 ngày tới
     */
    public function getAvailableSchedules(): array
    {
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

        return $scheduleData;
    }

    /**
     * Lấy lịch khám theo doctor và ngày
     */
    public function getSchedulesForDoctor(int $doctorId, string $workDate): array
    {
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
            ->where('doctorschedules.doctor_id', $doctorId)
            ->where('doctorschedules.work_date', $workDate)
            ->where('doctorschedules.status', 'Hoạt động')
            ->select(
                'doctorschedules.schedule_id',
                'doctorschedules.work_date',
                'doctorschedules.start_time',
                'doctorschedules.end_time',
                'doctorschedules.max_slot',
                'doctorschedules.slot_duration',
                DB::raw('COALESCE(bk.booked_count, 0) as booked_count')
            )
            ->orderBy('doctorschedules.start_time')
            ->get()
            ->toArray();

        return $schedules;
    }

    /**
     * Kiểm tra appointment đã được đặt
     */
    private function checkAppointmentAlreadyBooked(int $userId, int $scheduleId): bool
    {
        return DB::table('appointments')
            ->where('user_id', $userId)
            ->where('schedule_id', $scheduleId)
            ->whereNotIn('status', ['Đã hủy', 'Dời lịch'])
            ->exists();
    }

    /**
     * Kiểm tra schedule có hợp lệ
     */
    private function validateSchedule(int $scheduleId, string $workDate): ?object
    {
        $schedule = DB::table('doctorschedules')
            ->where('schedule_id', $scheduleId)
            ->where('status', 'Hoạt động')
            ->first();

        if (!$schedule || $schedule->work_date !== $workDate) {
            return null;
        }

        return $schedule;
    }

    /**
     * Tính số thứ tự cho appointment theo giờ cụ thể
     */
    private function calculateQueueNumber(int $scheduleId, string $appointmentTime): int
    {
        return DB::table('appointments')
            ->where('schedule_id', $scheduleId)
            ->whereRaw("DATE_FORMAT(appointment_time, '%H:%i') = ?", [substr($appointmentTime, 0, 5)])
            ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot', 'Đã khám'])
            ->count() + 1;
    }

    /**
     * Tính thời gian kết thúc appointment
     */
    private function calculateAppointmentEndTime(string $workDate, string $appointmentTime, int $slotDuration = 15): string
    {
        $appointmentDatetime = $workDate . ' ' . $appointmentTime . ':00';
        return Carbon::parse($appointmentDatetime)
            ->addMinutes($slotDuration)
            ->format('Y-m-d H:i:s');
    }

    /**
     * Tạo lịch khám mới
     * 
     * @throws Exception
     */
    public function createAppointment(int $userId, array $data): array
    {
        // Validate
        $alreadyBooked = $this->checkAppointmentAlreadyBooked($userId, $data['schedule_id']);
        if ($alreadyBooked) {
            throw new Exception('Bạn đã đặt lịch khám cho khung giờ này rồi.');
        }

        $schedule = $this->validateSchedule($data['schedule_id'], $data['work_date']);
        if (!$schedule) {
            throw new Exception('Lịch khám không tồn tại hoặc ngày khám không khớp.');
        }

        $booked = DB::table('appointments')
            ->where('schedule_id', $data['schedule_id'])
            ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
            ->count();

        if ($booked >= $schedule->max_slot) {
            throw new Exception('Khung giờ này đã hết chỗ. Vui lòng chọn giờ khác.');
        }

        // Calculate queue number and times
        $appointmentDatetime = $data['work_date'] . ' ' . $data['appointment_time'] . ':00';
        $appointmentEndtime = $this->calculateAppointmentEndTime(
            $data['work_date'],
            $data['appointment_time'],
            $schedule->slot_duration ?? 15
        );
        $queueNumber = $this->calculateQueueNumber($data['schedule_id'], $data['appointment_time']);

        // Database transaction
        $appointmentId = null;
        DB::beginTransaction();
        try {
            $existing = DB::table('appointments')
                ->where('user_id', $userId)
                ->where('schedule_id', $data['schedule_id'])
                ->first();

            if ($existing) {
                DB::table('appointments')
                    ->where('appointment_id', $existing->appointment_id)
                    ->update([
                        'service_id' => $data['service_id'] ?? null,
                        'appointment_time' => $appointmentDatetime,
                        'appointment_timeEnd' => $appointmentEndtime,
                        'queue_number' => $queueNumber,
                        'status' => 'Chờ xác nhận',
                        'note' => $data['note'] ?? null,
                        'cancel_reason' => null,
                        'slot_hold_expire' => null,
                        'rescheduled_from' => null,
                    ]);
                $appointmentId = $existing->appointment_id;
            } else {
                $appointmentId = DB::table('appointments')->insertGetId([
                    'user_id' => $userId,
                    'schedule_id' => $data['schedule_id'],
                    'service_id' => $data['service_id'] ?? null,
                    'appointment_time' => $appointmentDatetime,
                    'appointment_timeEnd' => $appointmentEndtime,
                    'queue_number' => $queueNumber,
                    'status' => 'Chờ xác nhận',
                    'note' => $data['note'] ?? null,
                    'created_at' => now(),
                ]);
            }

            // Create notification
            DB::table('notifications')->insert([
                'user_id' => $userId,
                'notif_type' => 'Lịch hẹn',
                'title' => 'Đặt lịch hẹn thành công',
                'content' => 'Lịch khám lúc ' . $data['appointment_time']
                    . ' ngày ' . Carbon::parse($data['work_date'])->format('d/m/Y')
                    . '. Số thứ tự: #' . $queueNumber,
                'ref_id' => $appointmentId,
                'ref_type' => 'appointment',
                'is_read' => false,
                'created_at' => now(),
            ]);

            // Log activity
            DB::table('activitylogs')->insert([
                'user_id' => $userId,
                'action' => 'Đặt lịch hẹn #' . $appointmentId,
                'ip_address' => $data['ip_address'] ?? null,
                'created_at' => now(),
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        // Send email after commit
        $this->sendAppointmentConfirmationEmail($appointmentId, $userId);

        return [
            'appointment_id' => $appointmentId,
            'queue_number' => $queueNumber,
            'message' => 'Đặt lịch hẹn thành công! Số thứ tự: #' . $queueNumber . '. Chúng tôi sẽ xác nhận sớm.'
        ];
    }

    /**
     * Lấy thống kê lịch khám của người dùng
     */
    public function getUserAppointmentStats(int $userId): object
    {
        return DB::table('appointments')
            ->where('user_id', $userId)
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status IN ('Chờ xác nhận','Đã xác nhận') AND appointment_time >= NOW() THEN 1 ELSE 0 END) as upcoming"),
                DB::raw("SUM(CASE WHEN status = 'Đã khám' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN status IN ('Đã hủy','Dời lịch') THEN 1 ELSE 0 END) as cancelled")
            )
            ->first();
    }

    /**
     * Lấy danh sách lịch khám của người dùng
     */
    public function getUserAppointments(int $userId, string $status = 'all', string $sort = 'desc'): object
    {
        $query = DB::table('appointments')
            ->join('doctorschedules', 'appointments.schedule_id', '=', 'doctorschedules.schedule_id')
            ->join('doctors', 'doctorschedules.doctor_id', '=', 'doctors.doctor_id')
            ->join('departments', 'doctors.department_id', '=', 'departments.department_id')
            ->join('users', 'appointments.user_id', '=', 'users.user_id')
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
                'rooms.room_name',
                'users.full_name as user_full_name',
                'users.phone as user_phone',
                'users.address as user_address',
                'users.email as user_email'
            );

        if ($status === 'upcoming') {
            $query->whereIn('appointments.status', ['Chờ xác nhận', 'Đã xác nhận'])
                ->where('doctorschedules.work_date', '>=', now()->toDateString());
        } elseif ($status === 'completed') {
            $query->where('appointments.status', 'Đã khám');
        } elseif ($status === 'cancelled') {
            $query->whereIn('appointments.status', ['Đã hủy', 'Dời lịch']);
        }

        return $query->orderBy('doctorschedules.work_date', $sort === 'asc' ? 'asc' : 'desc')
            ->orderBy('appointments.appointment_time', $sort === 'asc' ? 'asc' : 'desc')
            ->paginate(8);
    }

    /**
     * Lấy appointment chi tiết để chỉnh sửa
     */
    public function getAppointmentForEdit(int $appointmentId, int $userId): ?object
    {
        return DB::table('appointments')
            ->join('doctorschedules', 'appointments.schedule_id', '=', 'doctorschedules.schedule_id')
            ->join('doctors', 'doctorschedules.doctor_id', '=', 'doctors.doctor_id')
            ->join('departments', 'doctors.department_id', '=', 'departments.department_id')
            ->leftJoin('services', 'appointments.service_id', '=', 'services.service_id')
            ->join('users', 'appointments.user_id', '=', 'users.user_id')
            ->where('appointments.appointment_id', $appointmentId)
            ->where('appointments.user_id', $userId)
            ->select(
                'appointments.*',
                'doctorschedules.work_date',
                'doctorschedules.start_time',
                'doctorschedules.end_time',
                'doctors.doctor_id',
                'doctors.full_name as doctor_name',
                'doctors.department_id',
                'departments.department_name',
                'services.service_name',
                'users.full_name as user_full_name',
                'users.phone as user_phone',
                'users.address as user_address',
                'users.email as user_email',
                'users.date_of_birth'
            )
            ->first();
    }

    /**
     * Lấy lịch khám khác để dời
     */
    public function getAvailableSchedulesForReschedule(int $appointmentId, int $doctorId): \Illuminate\Support\Collection
    {
        $appointment = DB::table('appointments')
            ->where('appointment_id', $appointmentId)
            ->first();

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
            ->where('doctorschedules.doctor_id', $doctorId)
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
        

        
    return $availableSchedules;
    }

    /**
     * Kiểm tra thời gian còn lại trước appointment
     */
    private function checkRescheduleTimeAvailable(object $schedule): ?string
    {
        $appointmentTime = Carbon::parse($schedule->work_date . ' ' . $schedule->start_time);
        $hoursUntilAppointment = $appointmentTime->diffInHours(now(), false);

        if ($hoursUntilAppointment >= 0) {
            return 'Lịch khám này đã qua hoặc đang diễn ra. Không thể dời lịch.';
        }

        if ($hoursUntilAppointment > -2) {
            return 'Chỉ có thể dời lịch trước giờ khám ít nhất 2 tiếng.';
        }

        return null;
    }

    /**
     * Dời lịch khám
     * 
     * @throws Exception
     */
    public function rescheduleAppointment(int $appointmentId, int $userId, array $data): array
    {
        $appointment = DB::table('appointments')
            ->where('appointment_id', $appointmentId)
            ->where('user_id', $userId)
            ->first();

        if (!$appointment) {
            throw new Exception('Không tìm thấy lịch hẹn.');
        }

        if (!in_array($appointment->status, ['Chờ xác nhận', 'Đã xác nhận'])) {
            throw new Exception('Lịch hẹn này không thể dời.');
        }

        if ((int)$data['new_schedule_id'] === (int)$appointment->schedule_id) {
            throw new Exception('Vui lòng chọn lịch khác với lịch hiện tại.');
        }

        $newSchedule = DB::table('doctorschedules')
            ->where('schedule_id', $data['new_schedule_id'])
            ->where('status', 'Hoạt động')
            ->first();

        if (!$newSchedule) {
            throw new Exception('Lịch khám mới không hợp lệ.');
        }

        if (Carbon::parse($newSchedule->work_date)->isPast()) {
            throw new Exception('Ngày dời phải là ngày trong tương lai.');
        }

        $bookedInNew = DB::table('appointments')
            ->where('schedule_id', $data['new_schedule_id'])
            ->whereNotIn('status', ['Đã hủy', 'Dời lịch', 'Giữ slot'])
            ->count();

        if ($bookedInNew >= $newSchedule->max_slot) {
            throw new Exception('Khung giờ mới đã hết chỗ. Vui lòng chọn giờ khác.');
        }

        $newDatetime = $newSchedule->work_date . ' ' . $data['new_appointment_time'] . ':00';

        DB::beginTransaction();
        try {
            DB::table('appointments')
                ->where('appointment_id', $appointmentId)
                ->update([
                    'schedule_id' => $data['new_schedule_id'],
                    'appointment_time' => $newDatetime,
                    'queue_number' => $bookedInNew + 1,
                    'status' => 'Chờ xác nhận',
                    'cancel_reason' => $data['reschedule_reason']
                        ? 'Dời lịch: ' . $data['reschedule_reason']
                        : 'Dời sang lịch mới',
                    'rescheduled_from' => $appointment->schedule_id,
                ]);

            DB::table('notifications')->insert([
                'user_id' => $userId,
                'notif_type' => 'Lịch hẹn',
                'title' => 'Dời lịch hẹn thành công',
                'content' => 'Lịch hẹn #' . $appointmentId . ' đã được dời sang '
                    . Carbon::parse($newDatetime)->format('H:i d/m/Y'),
                'ref_id' => $appointmentId,
                'ref_type' => 'appointment',
                'is_read' => false,
                'created_at' => now(),
            ]);

            DB::table('activitylogs')->insert([
                'user_id' => $userId,
                'action' => 'Dời lịch hẹn #' . $appointmentId . ' sang schedule #' . $data['new_schedule_id'],
                'ip_address' => $data['ip_address'] ?? null,
                'created_at' => now(),
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        // Send email after commit
        $this->sendAppointmentRescheduleEmail($appointmentId, $userId);

        return [
            'message' => 'Dời lịch hẹn thành công! Lịch mới đang chờ xác nhận.'
        ];
    }

    /**
     * Kiểm tra thời gian còn lại trước appointment
     */
    private function checkCancelTimeAvailable(object $schedule): ?string
    {
        $appointmentTime = Carbon::parse($schedule->work_date . ' ' . $schedule->start_time);
        $hoursUntilAppointment = $appointmentTime->diffInHours(now(), false);

        if ($hoursUntilAppointment >= 0) {
            return 'Lịch khám này đã qua hoặc đang diễn ra. Không thể hủy lịch.';
        }

        if ($hoursUntilAppointment > -2) {
            return 'Chỉ có thể hủy lịch trước giờ khám ít nhất 2 tiếng.';
        }

        return null;
    }

    /**
     * Hủy lịch khám
     * 
     * @throws Exception
     */
    public function cancelAppointment(int $appointmentId, int $userId, array $data): array
    {
        $appointment = DB::table('appointments')
            ->where('appointment_id', $appointmentId)
            ->where('user_id', $userId)
            ->first();

        if (!$appointment) {
            throw new Exception('Không tìm thấy lịch hẹn.');
        }

        if (!in_array($appointment->status, ['Chờ xác nhận', 'Đã xác nhận'])) {
            throw new Exception('Lịch hẹn này không thể hủy (trạng thái: ' . $appointment->status . ').');
        }

        $schedule = DB::table('doctorschedules')
            ->where('schedule_id', $appointment->schedule_id)
            ->first();

        $timeError = $this->checkCancelTimeAvailable($schedule);
        if ($timeError) {
            throw new Exception($timeError);
        }

        DB::beginTransaction();
        try {
            DB::table('appointments')
                ->where('appointment_id', $appointmentId)
                ->update([
                    'status' => 'Đã hủy',
                    'cancel_reason' => $data['cancel_reason'] ?? 'Bệnh nhân tự hủy',
                ]);

            DB::table('notifications')->insert([
                'user_id' => $userId,
                'notif_type' => 'Lịch hẹn',
                'title' => 'Hủy lịch hẹn thành công',
                'content' => 'Lịch hẹn #' . $appointmentId . ' đã được hủy.'
                    . ($data['cancel_reason'] ? ' Lý do: ' . $data['cancel_reason'] : ''),
                'ref_id' => $appointmentId,
                'ref_type' => 'appointment',
                'is_read' => false,
                'created_at' => now(),
            ]);

            DB::table('activitylogs')->insert([
                'user_id' => $userId,
                'action' => 'Hủy lịch hẹn #' . $appointmentId,
                'ip_address' => $data['ip_address'] ?? null,
                'created_at' => now(),
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        // Send email after commit
        $this->sendAppointmentCancellationEmail($appointmentId, $userId);

        return [
            'message' => 'Đã hủy lịch hẹn #' . $appointmentId . ' thành công.'
        ];
    }

    /**
     * Gửi email xác nhận đặt lịch
     */
    private function sendAppointmentConfirmationEmail(int $appointmentId, int $userId): void
    {
        try {
            $appointment = DB::table('appointments')
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

            $user = User::find($userId);
            if ($user && $user->email && $appointment) {
                Mail::to($user->email)->send(
                    new AppointmentConfirmed($user, $appointment)
                );
            }
        } catch (Exception $e) {
            Log::warning('Failed to send appointment confirmation email', [
                'appointment_id' => $appointmentId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Gửi email dời lịch
     */
    private function sendAppointmentRescheduleEmail(int $appointmentId, int $userId): void
    {
        try {
            $appointment = DB::table('appointments')
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

            $user = User::find($userId);
            if ($user && $user->email && $appointment) {
                Mail::to($user->email)->send(
                    new AppointmentRescheduled($user, $appointment)
                );
            }
        } catch (Exception $e) {
            Log::warning('Failed to send appointment rescheduled email', [
                'appointment_id' => $appointmentId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Gửi email hủy lịch
     */
    private function sendAppointmentCancellationEmail(int $appointmentId, int $userId): void
    {
        try {
            $appointment = DB::table('appointments')
                ->join('doctorschedules', 'appointments.schedule_id', '=', 'doctorschedules.schedule_id')
                ->join('doctors', 'doctorschedules.doctor_id', '=', 'doctors.doctor_id')
                ->leftJoin('departments', 'doctors.department_id', '=', 'departments.department_id')
                ->where('appointments.appointment_id', $appointmentId)
                ->select(
                    'appointments.*',
                    'doctors.full_name as doctor_name',
                    'departments.department_name'
                )
                ->first();

            $user = User::find($userId);
            if ($user && $user->email && $appointment) {
                Mail::to($user->email)->send(
                    new AppointmentCancelled($user, $appointment)
                );
            }
        } catch (Exception $e) {
            Log::warning('Failed to send appointment cancelled email', [
                'appointment_id' => $appointmentId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
