<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    // ================================================================
    //  TRANG TỔNG QUAN: Stat Cards + Lưới Phòng + Phân bổ ca hôm nay
    // ================================================================
    public function index(Request $request)
    {
        // ── Bộ lọc ────────────────────────────────────────────────
        $query = Room::with('department');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('room_type')) {
            $query->where('room_type', $request->room_type);
        }

        $rooms = $query->orderBy('room_code')->get();

        // ── Stat cards ────────────────────────────────────────────
        $stats = [
            'total'    => $rooms->count(),
            'in_use'   => $rooms->where('status', 'Đang sử dụng')->count(),
            'empty'    => $rooms->where('status', 'Trống')->count(),
            'maintain' => $rooms->where('status', 'Bảo trì')->count(),
            'clean'    => $rooms->where('status', 'Vệ sinh')->count(),
        ];

        // ── Lịch phân bổ ca hôm nay (sidebar) ────────────────────
        $todaySchedules = DoctorSchedule::with(['doctor', 'room'])
            ->whereDate('work_date', today())
            ->whereNotNull('room_id')
            ->orderBy('start_time')
            ->get();

        // ── Lịch theo tuần (7 ngày từ đầu tuần) ──────────────────
        $weekStart  = now()->startOfWeek();
        $weekDates  = collect(range(0, 6))->map(fn($d) => $weekStart->copy()->addDays($d));
        $timeSlots  = ['07:00','08:00','09:00','10:00','11:00','12:00',
                       '13:00','14:00','15:00','16:00','17:00','18:00'];

        $weekSchedules = DoctorSchedule::with(['doctor', 'room'])
            ->whereBetween('work_date', [$weekStart->toDateString(), $weekStart->copy()->endOfWeek()->toDateString()])
            ->get()
            ->groupBy(fn($s) => $s->work_date->format('Y-m-d'));

        // ── Helpers cho view ──────────────────────────────────────
        $departments  = Department::where('status', 1)->orderBy('department_name')->get();
        $roomTypes    = Room::ROOM_TYPES;
        $roomStatuses = Room::ROOM_STATUSES;
        $doctors      = Doctor::where('status', 1)->with('department')->orderBy('full_name')->get();
        $allRooms     = Room::where('status', '!=', 'Bảo trì')->orderBy('room_code')->get();

        return view('admin.rooms.index', compact(
            'rooms', 'stats', 'todaySchedules', 'weekDates',
            'weekSchedules', 'timeSlots', 'departments',
            'roomTypes', 'roomStatuses', 'doctors', 'allRooms'
        ));
    }

    // ----------------------------------------------------------------
    // Cập nhật nhanh trạng thái phòng (AJAX-friendly)
    // ----------------------------------------------------------------
    public function updateStatus(Request $request, Room $room)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Room::ROOM_STATUSES),
        ]);

        $room->update(['status' => $request->status]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'status' => $room->status]);
        }

        return back()->with('success', 'Đã cập nhật trạng thái phòng.');
    }

    // ----------------------------------------------------------------
    // Form tạo phòng mới
    // ----------------------------------------------------------------
    public function create()
    {
        $departments  = Department::where('status', 1)->orderBy('department_name')->get();
        $roomTypes    = Room::ROOM_TYPES;
        $roomStatuses = Room::ROOM_STATUSES;
        return view('admin.rooms.create', compact('departments', 'roomTypes', 'roomStatuses'));
    }

    // ----------------------------------------------------------------
    // Lưu phòng mới
    // ----------------------------------------------------------------
    public function store(Request $request)
    {
        $request->validate([
            'room_code'     => 'required|string|max:20|unique:Rooms,room_code',
            'room_name'     => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:Departments,department_id',
            'room_type'     => 'required|in:' . implode(',', Room::ROOM_TYPES),
            'status'        => 'required|in:' . implode(',', Room::ROOM_STATUSES),
            'notes'         => 'nullable|string|max:255',
        ], [
            'room_code.unique' => 'Mã phòng đã tồn tại.',
        ]);

        Room::create($request->only(['room_code', 'room_name', 'department_id', 'room_type', 'status', 'notes']));

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Tạo phòng khám thành công!');
    }

    // ----------------------------------------------------------------
    // Chi tiết phòng + lịch ca trong tuần
    // ----------------------------------------------------------------
    public function show(Room $room, Request $request)
    {
        $room->load('department');

        $scheduleQuery = DoctorSchedule::with(['doctor.department'])
            ->where('room_id', $room->room_id);

        if ($request->filled('date')) {
            $scheduleQuery->whereDate('work_date', $request->date);
        } else {
            $scheduleQuery->whereBetween('work_date', [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString(),
            ]);
        }

        $schedules = $scheduleQuery->orderBy('work_date')->orderBy('start_time')->get();

        return view('admin.rooms.show', compact('room', 'schedules'));
    }

    // ----------------------------------------------------------------
    // Form sửa phòng
    // ----------------------------------------------------------------
    public function edit(Room $room)
    {
        $departments  = Department::where('status', 1)->orderBy('department_name')->get();
        $roomTypes    = Room::ROOM_TYPES;
        $roomStatuses = Room::ROOM_STATUSES;
        return view('admin.rooms.edit', compact('room', 'departments', 'roomTypes', 'roomStatuses'));
    }

    // ----------------------------------------------------------------
    // Cập nhật phòng
    // ----------------------------------------------------------------
    public function update(Request $request, Room $room)
    {
        $request->validate([
            'room_code'     => 'required|string|max:20|unique:Rooms,room_code,' . $room->room_id . ',room_id',
            'room_name'     => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:Departments,department_id',
            'room_type'     => 'required|in:' . implode(',', Room::ROOM_TYPES),
            'status'        => 'required|in:' . implode(',', Room::ROOM_STATUSES),
            'notes'         => 'nullable|string|max:255',
        ]);

        $room->update($request->only(['room_code', 'room_name', 'department_id', 'room_type', 'status', 'notes']));

        return redirect()->route('admin.rooms.show', $room)
            ->with('success', 'Cập nhật phòng thành công!');
    }

    // ================================================================
    //  QUẢN LÝ CA TRỰC (DoctorSchedules)
    // ================================================================

    // Trang phân bổ ca theo ngày (lưới + sidebar)
    public function scheduleIndex(Request $request)
    {
        $date  = $request->get('date', today()->toDateString());

        $rooms = Room::with([
            'department',
            'schedules' => function ($q) use ($date) {
                $q->whereDate('work_date', $date)->with('doctor');
            },
        ])->orderBy('room_code')->get();

        $doctors     = Doctor::where('status', 1)->with('department')->orderBy('full_name')->get();
        $departments = Department::where('status', 1)->orderBy('department_name')->get();
        $statuses    = DoctorSchedule::STATUSES;

        return view('admin.rooms.schedule-index', compact('rooms', 'doctors', 'departments', 'date', 'statuses'));
    }

    // Form tạo ca mới
    public function createSchedule(Request $request)
    {
        $rooms    = Room::where('status', '!=', 'Bảo trì')->orderBy('room_code')->get();
        $doctors  = Doctor::where('status', 1)->with('department')->orderBy('full_name')->get();
        $statuses = DoctorSchedule::STATUSES;

        $selectedRoom = $request->filled('room_id')
            ? Room::find($request->room_id)
            : null;

        return view('admin.rooms.schedule-create', compact('rooms', 'doctors', 'statuses', 'selectedRoom'));
    }

    // Lưu ca mới — kiểm tra trùng bác sĩ + trùng phòng
    public function storeSchedule(Request $request)
    {
        $request->validate([
            'doctor_id'     => 'required|exists:Doctors,doctor_id',
            'room_id'       => 'nullable|exists:Rooms,room_id',
            'work_date'     => 'required|date|after_or_equal:today',
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|integer|min:5|max:120',
            'max_slot'      => 'required|integer|min:1|max:100',
            'status'        => 'required|in:' . implode(',', DoctorSchedule::STATUSES),
            'note'          => 'nullable|string|max:255',
        ], [
            'work_date.after_or_equal' => 'Ngày làm việc phải từ hôm nay trở đi.',
            'end_time.after'           => 'Giờ kết thúc phải sau giờ bắt đầu.',
        ]);

        // Kiểm tra trùng bác sĩ
        if ($this->hasScheduleConflict($request->doctor_id, $request->work_date, $request->start_time, $request->end_time)) {
            return back()
                ->withErrors(['doctor_id' => 'Bác sĩ đã có ca làm việc trùng giờ trong ngày này.'])
                ->withInput();
        }

        // Kiểm tra trùng phòng
        if ($request->filled('room_id') && $this->hasRoomConflict($request->room_id, $request->work_date, $request->start_time, $request->end_time)) {
            return back()
                ->withErrors(['room_id' => 'Phòng đã có ca khác trùng giờ trong ngày này.'])
                ->withInput();
        }

        DoctorSchedule::create($request->only([
            'doctor_id', 'room_id', 'work_date',
            'start_time', 'end_time', 'slot_duration',
            'max_slot', 'status', 'note',
        ]));

        return redirect()->route('admin.rooms.schedule.index', ['date' => $request->work_date])
            ->with('success', 'Tạo ca làm việc thành công!');
    }

    // Form sửa ca
    public function editSchedule(DoctorSchedule $schedule)
    {
        $rooms    = Room::where('status', '!=', 'Bảo trì')->orderBy('room_code')->get();
        $doctors  = Doctor::where('status', 1)->with('department')->orderBy('full_name')->get();
        $statuses = DoctorSchedule::STATUSES;
        $schedule->load(['doctor', 'room']);

        return view('admin.rooms.schedule-edit', compact('schedule', 'rooms', 'doctors', 'statuses'));
    }

    // Cập nhật ca
    public function updateSchedule(Request $request, DoctorSchedule $schedule)
    {
        $request->validate([
            'doctor_id'     => 'required|exists:Doctors,doctor_id',
            'room_id'       => 'nullable|exists:Rooms,room_id',
            'work_date'     => 'required|date',
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i|after:start_time',
            'slot_duration' => 'required|integer|min:5|max:120',
            'max_slot'      => 'required|integer|min:1|max:100',
            'status'        => 'required|in:' . implode(',', DoctorSchedule::STATUSES),
            'note'          => 'nullable|string|max:255',
        ]);

        // Kiểm tra trùng bác sĩ (bỏ qua bản ghi hiện tại)
        if ($this->hasScheduleConflict(
            $request->doctor_id, $request->work_date,
            $request->start_time, $request->end_time,
            $schedule->schedule_id
        )) {
            return back()->withErrors(['doctor_id' => 'Bác sĩ đã có ca làm việc trùng giờ.'])->withInput();
        }

        // Kiểm tra max_slot >= booked_slots
        if ($request->max_slot < $schedule->booked_slots) {
            return back()->withErrors([
                'max_slot' => "Số slot tối đa không thể nhỏ hơn số lượt đã đặt ({$schedule->booked_slots}).",
            ])->withInput();
        }

        $schedule->update($request->only([
            'doctor_id', 'room_id', 'work_date',
            'start_time', 'end_time', 'slot_duration',
            'max_slot', 'status', 'note',
        ]));

        return redirect()->route('admin.rooms.schedule.index', ['date' => $schedule->work_date])
            ->with('success', 'Cập nhật ca làm việc thành công!');
    }

    // Xoá ca (chỉ khi chưa có lịch hẹn)
    public function destroySchedule(DoctorSchedule $schedule)
    {
        if ($schedule->booked_slots > 0) {
            return back()->with('error', 'Không thể xoá ca đã có bệnh nhân đặt lịch.');
        }

        $date = $schedule->work_date->toDateString();
        $schedule->delete();

        return redirect()->route('admin.rooms.schedule.index', ['date' => $date])
            ->with('success', 'Đã xoá ca làm việc.');
    }

    // ── AJAX: Kiểm tra xung đột lịch bác sĩ trước khi submit ──────
    public function checkConflict(Request $request)
    {
        $conflict = $this->hasScheduleConflict(
            $request->doctor_id,
            $request->work_date,
            $request->start_time,
            $request->end_time,
            $request->exclude_id ?? null
        );

        return response()->json(['conflict' => $conflict]);
    }

    // ================================================================
    //  HELPERS PRIVATE
    // ================================================================

    private function hasScheduleConflict(
        int    $doctorId,
        string $workDate,
        string $startTime,
        string $endTime,
        ?int   $excludeId = null
    ): bool {
        $q = DoctorSchedule::where('doctor_id', $doctorId)
            ->where('work_date', $workDate)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)
                         ->where('end_time', '>=', $endTime);
                  });
            });

        if ($excludeId) {
            $q->where('schedule_id', '!=', $excludeId);
        }

        return $q->exists();
    }

    private function hasRoomConflict(
        int    $roomId,
        string $workDate,
        string $startTime,
        string $endTime,
        ?int   $excludeId = null
    ): bool {
        $q = DoctorSchedule::where('room_id', $roomId)
            ->where('work_date', $workDate)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)
                         ->where('end_time', '>=', $endTime);
                  });
            });

        if ($excludeId) {
            $q->where('schedule_id', '!=', $excludeId);
        }

        return $q->exists();
    }
}
