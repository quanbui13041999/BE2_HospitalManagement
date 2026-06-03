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

    public function destroyRoom(Room $room): ?string
    {
        if ($room->schedules()->exists()) {
            return 'Không thể xoá phòng khám này vì đã có ca trực của bác sĩ được phân bổ hoặc đang diễn ra.';
        }

        $roomName = $room->room_name ?? $room->room_code;
        $roomId = $room->room_id;
        $roomData = $room->only(['room_id', 'room_name', 'room_code']);
        
        $room->delete();

        \App\Services\ActivityLogService::log(
            'Admin xoá phòng',
            'Admin ' . (\Illuminate\Support\Facades\Auth::user()?->full_name ?: '') . ' đã xoá phòng khám ' . $roomName . '.',
            'room',
            $roomId,
            ['room' => $roomData]
        );

        return null;
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
            'departments'    => $this->repo->activeDepartments(),
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

        $schedules = $this->repo->weekSchedulesForRoom($roomId, $weekStart)->map(function ($items) {
            return $items->map(function ($item) {
                return [
                    'schedule_id'  => $item->schedule_id,
                    'start_time'   => $item->start_time,
                    'end_time'     => $item->end_time,
                    'status'       => $item->status,
                    'doctor_name'  => $item->doctor->full_name ?? '',
                    'room_code'    => $item->room->room_code ?? '',
                    'booked_slots' => $item->booked_slots,
                    'max_slot'     => $item->max_slot,
                ];
            })->values();
        });

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
            'in_use'   => $rooms->where('status', 'Hoạt động')->count(),
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

    /**
     * Tự động phân bổ ca trực cho các bác sĩ vào phòng tương ứng với chuyên khoa.
     */
    public function autoAllocateSchedules(array $data): array
    {
        $startDate = Carbon::parse($data['start_date'] ?? today()->toDateString());
        $endDate = Carbon::parse($data['end_date'] ?? today()->addDays(6)->toDateString());
        $slotDuration = (int) ($data['slot_duration'] ?? 30);
        $maxSlot = (int) ($data['max_slot'] ?? 8);
        $overwrite = !empty($data['overwrite']);
        $deptId = !empty($data['department_id']) ? (int) $data['department_id'] : null;

        $sessions = [];
        if (!empty($data['morning_enabled'])) {
            $sessions[] = [
                'label' => 'Sáng',
                'start' => '08:00:00',
                'end'   => '12:00:00',
            ];
        }
        if (!empty($data['afternoon_enabled'])) {
            $sessions[] = [
                'label' => 'Chiều',
                'start' => '13:30:00',
                'end'   => '17:30:00',
            ];
        }

        if (empty($sessions)) {
            return ['success' => false, 'message' => 'Vui lòng chọn ít nhất một ca trực (Sáng hoặc Chiều).'];
        }

        // Bác sĩ hoạt động
        $doctorQuery = \App\Models\Doctor::where('status', 1);
        if ($deptId) {
            $doctorQuery->where('department_id', $deptId);
        }
        $doctors = $doctorQuery->get();

        if ($doctors->isEmpty()) {
            return ['success' => false, 'message' => 'Không tìm thấy bác sĩ nào phù hợp với bộ lọc.'];
        }

        // Phòng khám hoạt động (trừ Bảo trì)
        $roomQuery = \App\Models\Room::where('status', '!=', 'Bảo trì');
        if ($deptId) {
            $roomQuery->where('department_id', $deptId);
        }
        $rooms = $roomQuery->get();

        if ($rooms->isEmpty()) {
            return ['success' => false, 'message' => 'Không tìm thấy phòng khám nào phù hợp (trừ các phòng đang Bảo trì).'];
        }

        $created = 0;
        $skipped = 0;
        $deleted = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use (
            $startDate, $endDate, $doctors, $rooms, $sessions, $slotDuration, $maxSlot, $overwrite, $deptId,
            &$created, &$skipped, &$deleted
        ) {
            if ($overwrite) {
                // Xóa ca cũ không có lượt đặt trong khoảng ngày
                $toDeleteQuery = DoctorSchedule::whereBetween('work_date', [
                    $startDate->toDateString(),
                    $endDate->toDateString()
                ]);

                if ($deptId) {
                    $toDeleteQuery->whereHas('doctor', function ($q) use ($deptId) {
                        $q->where('department_id', $deptId);
                    });
                }

                $toDelete = $toDeleteQuery->get();
                foreach ($toDelete as $sched) {
                    if ($sched->booked_slots === 0) {
                        $sched->delete();
                        $deleted++;
                    }
                }
            }

            // Chạy qua từng ngày trong khoảng
            $curr = $startDate->copy();
            while ($curr->lte($endDate)) {
                $workDateStr = $curr->toDateString();

                foreach ($doctors as $doctor) {
                    // Ưu tiên các phòng thuộc khoa của bác sĩ
                    $deptRooms = $rooms->where('department_id', $doctor->department_id);
                    if ($deptRooms->isEmpty()) {
                        // Nếu không có phòng cùng khoa, dùng tất cả phòng
                        $deptRooms = $rooms;
                    }

                    foreach ($sessions as $session) {
                        // 1. Kiểm tra bác sĩ đã có lịch trùng giờ chưa
                        $hasDocConflict = $this->repo->hasScheduleConflict(
                            $doctor->doctor_id,
                            $workDateStr,
                            $session['start'],
                            $session['end']
                        );

                        if ($hasDocConflict) {
                            $skipped++;
                            continue;
                        }

                        // 2. Tìm phòng trống không có ca trực trùng giờ
                        $selectedRoom = null;
                        foreach ($deptRooms as $room) {
                            $hasRoomConf = $this->repo->hasRoomConflict(
                                $room->room_id,
                                $workDateStr,
                                $session['start'],
                                $session['end']
                            );

                            if (!$hasRoomConf) {
                                $selectedRoom = $room;
                                break;
                            }
                        }

                        // Nếu các phòng cùng khoa đều bận, thử các phòng khác khoa
                        if (!$selectedRoom) {
                            foreach ($rooms as $room) {
                                if ($room->department_id === $doctor->department_id) continue;
                                $hasRoomConf = $this->repo->hasRoomConflict(
                                    $room->room_id,
                                    $workDateStr,
                                    $session['start'],
                                    $session['end']
                                );

                                if (!$hasRoomConf) {
                                    $selectedRoom = $room;
                                    break;
                                }
                            }
                        }

                        // Tạo lịch trực
                        if ($selectedRoom) {
                            DoctorSchedule::create([
                                'doctor_id'     => $doctor->doctor_id,
                                'room_id'       => $selectedRoom->room_id,
                                'work_date'     => $workDateStr,
                                'start_time'    => $session['start'],
                                'end_time'      => $session['end'],
                                'slot_duration' => $slotDuration,
                                'max_slot'      => $maxSlot,
                                'status'        => 'Hoạt động',
                                'note'          => 'Tự động phân bổ (' . $session['label'] . ')',
                            ]);
                            $created++;
                        } else {
                            $skipped++;
                        }
                    }
                }

                $curr->addDay();
            }
        });

        $msg = "Đã tự động phân bổ ca trực thành công! Tạo mới {$created} ca trực.";
        if ($deleted > 0) {
            $msg .= " Đã xoá dọn dẹp {$deleted} ca trống cũ.";
        }
        if ($skipped > 0) {
            $msg .= " Bỏ qua {$skipped} lượt phân bổ do trùng lịch hoặc không còn phòng trống.";
        }

        return [
            'success' => true,
            'message' => $msg,
        ];
    }
}
