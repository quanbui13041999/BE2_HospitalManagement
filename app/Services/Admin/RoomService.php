<?php

namespace App\Services\Admin;

use App\Models\DoctorSchedule;
use App\Models\Room;
use App\Repositories\RoomRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomService
{
    public function __construct(protected RoomRepository $repo) {}

    // ----------------------------------------------------------------
    // Phòng khám
    // ----------------------------------------------------------------

    public function buildIndexData(Request $request): array
    {
        $rooms       = $this->repo->filteredRooms($request);
        $weekStart   = now()->startOfWeek();
        $weekDates   = collect(range(0, 6))->map(fn($d) => $weekStart->copy()->addDays($d));
        $timeSlots   = $this->defaultTimeSlots();
        $weekSchedules = $this->repo->weekSchedules($weekStart);

        return [
            'rooms'         => $rooms,
            'stats'         => $this->buildRoomStats($rooms),
            'todaySchedules'=> $this->repo->todaySchedules(),
            'weekDates'     => $weekDates,
            'weekStart'     => $weekStart,
            'weekSchedules' => $weekSchedules,
            'timeSlots'     => $timeSlots,
            'departments'   => $this->repo->activeDepartments(),
            'roomTypes'     => Room::ROOM_TYPES,
            'roomStatuses'  => Room::ROOM_STATUSES,
            'doctors'       => $this->repo->activeDoctors(),
            'allRooms'      => $this->repo->allAvailableRooms(),
        ];
    }

    public function buildCreateData(): array
    {
        return [
            'departments'  => $this->repo->activeDepartments(),
            'roomTypes'    => Room::ROOM_TYPES,
            'roomStatuses' => Room::ROOM_STATUSES,
        ];
    }

    public function buildEditData(Room $room): array
    {
        return array_merge(['room' => $room], $this->buildCreateData());
    }

    public function buildShowData(Room $room, Request $request): array
    {
        $room->load('department');

        $scheduleQuery = DoctorSchedule::with(['doctor.department'])->where('room_id', $room->room_id);

        if ($request->filled('date')) {
            $scheduleQuery->whereDate('work_date', $request->date);
        } else {
            $scheduleQuery->whereBetween('work_date', [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString(),
            ]);
        }

        return [
            'room'      => $room,
            'schedules' => $scheduleQuery->orderBy('work_date')->orderBy('start_time')->get(),
        ];
    }

    // ----------------------------------------------------------------
    // Ca trực
    // ----------------------------------------------------------------

    public function buildScheduleIndexData(string $date): array
    {
        return [
            'rooms'       => $this->repo->roomsWithDaySchedules($date),
            'doctors'     => $this->repo->activeDoctors(),
            'departments' => $this->repo->activeDepartments(),
            'date'        => $date,
            'statuses'    => DoctorSchedule::STATUSES,
        ];
    }

    public function buildScheduleAllData(Request $request): array
    {
        return [
            'schedules' => $this->repo->filteredSchedules($request),
            'stats'     => $this->repo->scheduleStats(),
            'doctors'   => $this->repo->activeDoctors(),
            'rooms'     => Room::orderBy('room_code')->get(),
            'statuses'  => DoctorSchedule::STATUSES,
        ];
    }

    public function buildScheduleCreateData(Request $request): array
    {
        return [
            'rooms'        => $this->repo->allAvailableRooms(),
            'doctors'      => $this->repo->activeDoctors(),
            'statuses'     => DoctorSchedule::STATUSES,
            'selectedRoom' => $request->filled('room_id') ? Room::find($request->room_id) : null,
        ];
    }

    public function buildScheduleEditData(DoctorSchedule $schedule): array
    {
        $schedule->load(['doctor', 'room']);
        return [
            'schedule' => $schedule,
            'rooms'    => $this->repo->allAvailableRooms(),
            'doctors'  => $this->repo->activeDoctors(),
            'statuses' => DoctorSchedule::STATUSES,
        ];
    }

    /**
     * Validate conflict và tạo ca mới. Trả về mảng lỗi nếu có, null nếu thành công.
     */
    public function storeSchedule(array $data): ?array
    {
        $room = Room::find($data['room_id']);
        if ($room?->status === 'Bảo trì') {
            return ['room_id' => 'Phòng đang bảo trì, không thể phân ca.'];
        }

        if ($this->repo->hasScheduleConflict($data['doctor_id'], $data['work_date'], $data['start_time'], $data['end_time'])) {
            return ['doctor_id' => 'Bác sĩ đã có ca làm việc trùng giờ trong ngày này.'];
        }

        if ($this->repo->hasRoomConflict($data['room_id'], $data['work_date'], $data['start_time'], $data['end_time'])) {
            return ['room_id' => 'Phòng đã có ca khác trùng giờ trong ngày này.'];
        }

        DoctorSchedule::create($data);
        return null;
    }

    /**
     * Validate conflict và cập nhật ca. Trả về mảng lỗi nếu có, null nếu thành công.
     */
    public function updateSchedule(DoctorSchedule $schedule, array $data): ?array
    {
        if ($this->repo->hasScheduleConflict(
            $data['doctor_id'], $data['work_date'], $data['start_time'], $data['end_time'],
            $schedule->schedule_id
        )) {
            return ['doctor_id' => 'Bác sĩ đã có ca làm việc trùng giờ.'];
        }

        if ($this->repo->hasRoomConflict(
            $data['room_id'], $data['work_date'], $data['start_time'], $data['end_time'],
            $schedule->schedule_id
        )) {
            return ['room_id' => 'Phòng đã có ca khác trùng giờ.'];
        }

        if ($data['max_slot'] < $schedule->booked_slots) {
            return ['max_slot' => "Số slot tối đa không thể nhỏ hơn số lượt đã đặt ({$schedule->booked_slots})."];
        }

        $schedule->update($data);
        return null;
    }

    // ----------------------------------------------------------------
    // Lịch tuần
    // ----------------------------------------------------------------

    public function buildWeeklyData(Request $request, ?int $roomId): array
    {
        $rooms           = Room::orderBy('room_code')->get();
        $selectedRoomId  = $request->get('room_id', $roomId);
        $selectedRoom    = $selectedRoomId
            ? Room::with('department')->find($selectedRoomId)
            : $rooms->first();
        $selectedRoomId  = $selectedRoom?->room_id;

        $weekStart   = $request->filled('week_start')
            ? Carbon::parse($request->week_start)->startOfWeek()
            : now()->startOfWeek();

        $weekDates     = collect(range(0, 6))->map(fn($d) => $weekStart->copy()->addDays($d));
        $weekSchedules = $selectedRoomId
            ? $this->repo->weekSchedulesForRoom($selectedRoomId, $weekStart)
            : collect();

        return [
            'rooms'          => $rooms,
            'selectedRoom'   => $selectedRoom,
            'selectedRoomId' => $selectedRoomId,
            'weekDates'      => $weekDates,
            'weekStart'      => $weekStart,
            'timeSlots'      => $this->defaultTimeSlots(),
            'weekSchedules'  => $weekSchedules,
            'prevWeek'       => $weekStart->copy()->subWeek()->toDateString(),
            'nextWeek'       => $weekStart->copy()->addWeek()->toDateString(),
        ];
    }

    public function buildWeeklyAjaxResponse(Request $request): array
    {
        $roomId    = $request->get('room_id');
        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->week_start)->startOfWeek()
            : now()->startOfWeek();

        if (!$roomId) {
            return ['success' => false, 'message' => 'Chưa chọn phòng'];
        }

        $room = Room::find($roomId);
        if (!$room) {
            return ['success' => false, 'message' => 'Phòng không tồn tại'];
        }

        $weekDates = collect(range(0, 6))->map(fn($d) => [
            'full_date' => $weekStart->copy()->addDays($d)->toDateString(),
            'date'      => $weekStart->copy()->addDays($d)->format('d/m'),
            'day'       => $weekStart->copy()->addDays($d)->isoFormat('dd'),
        ]);

        $schedules = $this->repo->weekSchedulesForRoom($roomId, $weekStart)->map(fn($items) =>
            $items->map(fn($item) => [
                'start_time'  => $item->start_time,
                'end_time'    => $item->end_time,
                'status'      => $item->status,
                'doctor_name' => $item->doctor->full_name ?? '',
                'room_code'   => $item->room->room_code ?? '',
            ])->toArray()
        );

        return [
            'success'    => true,
            'room_id'    => $roomId,
            'room_code'  => $room->room_code,
            'room_name'  => $room->room_name,
            'week_start' => $weekStart->format('d/m'),
            'week_end'   => $weekStart->copy()->endOfWeek()->format('d/m/Y'),
            'week_dates' => $weekDates,
            'schedules'  => $schedules,
            'today'      => now()->format('d/m'),
        ];
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    private function buildRoomStats($rooms): array
    {
        return [
            'total'    => $rooms->count(),
            'in_use'   => $rooms->where('status', 'Đang sử dụng')->count(),
            'empty'    => $rooms->where('status', 'Trống')->count(),
            'maintain' => $rooms->where('status', 'Bảo trì')->count(),
            'clean'    => $rooms->where('status', 'Vệ sinh')->count(),
        ];
    }

    private function defaultTimeSlots(): array
    {
        return ['07:00','08:00','09:00','10:00','11:00','12:00',
                '13:00','14:00','15:00','16:00','17:00','18:00'];
    }
}
