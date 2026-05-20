<?php

namespace App\Repositories;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomRepository
{
    // ----------------------------------------------------------------
    // Phòng khám
    // ----------------------------------------------------------------

    public function filteredRooms(Request $request)
    {
        $query = Room::with('department');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('room_type')) {
            $query->where('room_type', $request->room_type);
        }

        return $query->orderBy('room_code')->get();
    }

    public function todaySchedules()
    {
        return DoctorSchedule::with(['doctor', 'room'])
            ->whereDate('work_date', today())
            ->whereNotNull('room_id')
            ->orderBy('start_time')
            ->get();
    }

    public function weekSchedules(Carbon $weekStart)
    {
        return DoctorSchedule::with(['doctor', 'room'])
            ->whereBetween('work_date', [
                $weekStart->toDateString(),
                $weekStart->copy()->endOfWeek()->toDateString(),
            ])
            ->get()
            ->groupBy(fn($s) => $s->work_date->format('Y-m-d'));
    }

    public function weekSchedulesForRoom(int $roomId, Carbon $weekStart)
    {
        return DoctorSchedule::with(['doctor', 'room'])
            ->where('room_id', $roomId)
            ->whereBetween('work_date', [
                $weekStart->toDateString(),
                $weekStart->copy()->endOfWeek()->toDateString(),
            ])
            ->get()
            ->groupBy(fn($s) => $s->work_date->format('Y-m-d'));
    }

    public function allAvailableRooms()
    {
        return Room::where('status', '!=', 'Bảo trì')->orderBy('room_code')->get();
    }

    // ----------------------------------------------------------------
    // Ca trực
    // ----------------------------------------------------------------

    public function filteredSchedules(Request $request)
    {
        $query = DoctorSchedule::with(['doctor', 'room', 'doctor.department']);

        if ($request->filled('from_date')) {
            $query->whereDate('work_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('work_date', '<=', $request->to_date);
        }
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->orderBy('work_date', 'desc')->orderBy('start_time')->paginate(20);
    }

    public function scheduleStats(): array
    {
        $allSchedules = DoctorSchedule::with('appointments')->get();
        $totalBooked  = $allSchedules->sum(fn($s) => $s->booked_slots);

        return [
            'total'        => DoctorSchedule::count(),
            'active'       => DoctorSchedule::where('status', 'Hoạt động')->count(),
            'paused'       => DoctorSchedule::where('status', 'Tạm dừng')->count(),
            'cancelled'    => DoctorSchedule::where('status', 'Đã huỷ')->count(),
            'total_slots'  => DoctorSchedule::sum('max_slot'),
            'total_booked' => $totalBooked,
        ];
    }

    public function roomsWithDaySchedules(string $date)
    {
        return Room::with([
            'department',
            'schedules' => function ($q) use ($date) {
                $q->whereDate('work_date', $date)->with('doctor');
            },
        ])->orderBy('room_code')->get();
    }

    // ----------------------------------------------------------------
    // Shared helpers (dùng trong service layer)
    // ----------------------------------------------------------------

    public function hasScheduleConflict(
        int $doctorId,
        string $workDate,
        string $startTime,
        string $endTime,
        ?int $excludeId = null
    ): bool {
        $q = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('work_date', $workDate)
            ->where(fn($q) => $q->where(fn($q2) =>
                $q2->where('start_time', '<', $endTime)
                   ->where('end_time', '>', $startTime)
            ));

        if ($excludeId) {
            $q->where('schedule_id', '!=', $excludeId);
        }

        return $q->exists();
    }

    public function hasRoomConflict(
        int $roomId,
        string $workDate,
        string $startTime,
        string $endTime,
        ?int $excludeId = null
    ): bool {
        $q = DoctorSchedule::where('room_id', $roomId)
            ->where('work_date', $workDate)
            ->where(fn($q) => $q->where(fn($q2) =>
                $q2->where('start_time', '<', $endTime)
                   ->where('end_time', '>', $startTime)
            ));

        if ($excludeId) {
            $q->where('schedule_id', '!=', $excludeId);
        }

        return $q->exists();
    }

    // ----------------------------------------------------------------
    // Shared dropdown data
    // ----------------------------------------------------------------

    public function activeDepartments()
    {
        return Department::where('status', 1)->orderBy('department_name')->get();
    }

    public function activeDoctors()
    {
        return Doctor::where('status', 1)->with('department')->orderBy('full_name')->get();
    }
}
