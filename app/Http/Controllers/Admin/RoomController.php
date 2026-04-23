<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

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
        $timeSlots  = [
            '07:00',
            '08:00',
            '09:00',
            '10:00',
            '11:00',
            '12:00',
            '13:00',
            '14:00',
            '15:00',
            '16:00',
            '17:00',
            '18:00'
        ];

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
            'rooms',
            'stats',
            'todaySchedules',
            'weekDates',
            'weekStart',        // ← THÊM DÒNG NÀY
            'weekSchedules',
            'timeSlots',
            'departments',
            'roomTypes',
            'roomStatuses',
            'doctors',
            'allRooms'
        ));
    }

    // ----------------------------------------------------------------
    // Cập nhật nhanh trạng thái phòng
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

    // Trang phân bổ ca theo ngày
    public function scheduleIndex(Request $request)
    {
        $date = $request->get('date', today()->toDateString());

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

    // ================================================================
    //  XEM LỊCH PHÂN BỔ ĐẦY ĐỦ (TẤT CẢ CÁC CA)
    // ================================================================
    public function scheduleAll(Request $request)
    {
        // Lấy tất cả schedules, sắp xếp theo ngày gần nhất trước
        $query = DoctorSchedule::with(['doctor', 'room', 'doctor.department']);

        // Lọc theo ngày bắt đầu
        if ($request->filled('from_date')) {
            $query->whereDate('work_date', '>=', $request->from_date);
        }

        // Lọc theo ngày kết thúc
        if ($request->filled('to_date')) {
            $query->whereDate('work_date', '<=', $request->to_date);
        }

        // Lọc theo bác sĩ
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // Lọc theo phòng
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $schedules = $query->orderBy('work_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->paginate(20);

        // ⚠️ THỐNG KÊ - BỎ QUA booked_slots VÌ KHÔNG CÓ TRONG DB
        // Sử dụng accessor để lấy số lượng đã đặt từ appointments
        $allSchedules = DoctorSchedule::with('appointments')->get();
        $totalBooked = 0;
        foreach ($allSchedules as $sch) {
            $totalBooked += $sch->booked_slots; // Dùng accessor, không phải cột trong DB
        }

        $stats = [
            'total' => DoctorSchedule::count(),
            'active' => DoctorSchedule::where('status', 'Hoạt động')->count(),
            'paused' => DoctorSchedule::where('status', 'Tạm dừng')->count(),
            'cancelled' => DoctorSchedule::where('status', 'Đã huỷ')->count(),
            'total_slots' => DoctorSchedule::sum('max_slot'),
            'total_booked' => $totalBooked, // Sử dụng biến đã tính
        ];

        $doctors = Doctor::where('status', 1)->orderBy('full_name')->get();
        $rooms = Room::orderBy('room_code')->get();
        $statuses = DoctorSchedule::STATUSES;

        return view('admin.rooms.schedule-all', compact('schedules', 'stats', 'doctors', 'rooms', 'statuses'));
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

    // Lưu ca mới
    public function storeSchedule(Request $request)
    {
        $request->validate([
            'doctor_id'     => 'required|exists:Doctors,doctor_id',
            'room_id'       => 'required|exists:Rooms,room_id',
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

        // Kiểm tra phòng có đang bảo trì không
        $room = Room::find($request->room_id);
        if ($room && $room->status === 'Bảo trì') {
            return back()->withErrors(['room_id' => 'Phòng đang bảo trì, không thể phân ca.'])->withInput();
        }

        // Kiểm tra trùng bác sĩ
        if ($this->hasScheduleConflict($request->doctor_id, $request->work_date, $request->start_time, $request->end_time)) {
            return back()
                ->withErrors(['doctor_id' => 'Bác sĩ đã có ca làm việc trùng giờ trong ngày này.'])
                ->withInput();
        }

        // Kiểm tra trùng phòng
        if ($this->hasRoomConflict($request->room_id, $request->work_date, $request->start_time, $request->end_time)) {
            return back()
                ->withErrors(['room_id' => 'Phòng đã có ca khác trùng giờ trong ngày này.'])
                ->withInput();
        }

        DoctorSchedule::create($request->only([
            'doctor_id',
            'room_id',
            'work_date',
            'start_time',
            'end_time',
            'slot_duration',
            'max_slot',
            'status',
            'note',
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
            'room_id'       => 'required|exists:Rooms,room_id',
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
            $request->doctor_id,
            $request->work_date,
            $request->start_time,
            $request->end_time,
            $schedule->schedule_id
        )) {
            return back()->withErrors(['doctor_id' => 'Bác sĩ đã có ca làm việc trùng giờ.'])->withInput();
        }

        // Kiểm tra trùng phòng (bỏ qua bản ghi hiện tại)
        if ($this->hasRoomConflict(
            $request->room_id,
            $request->work_date,
            $request->start_time,
            $request->end_time,
            $schedule->schedule_id
        )) {
            return back()->withErrors(['room_id' => 'Phòng đã có ca khác trùng giờ.'])->withInput();
        }

        // Kiểm tra max_slot >= booked_slots
        if ($request->max_slot < $schedule->booked_slots) {
            return back()->withErrors([
                'max_slot' => "Số slot tối đa không thể nhỏ hơn số lượt đã đặt ({$schedule->booked_slots}).",
            ])->withInput();
        }

        $schedule->update($request->only([
            'doctor_id',
            'room_id',
            'work_date',
            'start_time',
            'end_time',
            'slot_duration',
            'max_slot',
            'status',
            'note',
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

    // AJAX: Kiểm tra xung đột lịch bác sĩ
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
                $q->where(function ($q2) use ($startTime, $endTime) {
                    $q2->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
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
                $q->where(function ($q2) use ($startTime, $endTime) {
                    $q2->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            });

        if ($excludeId) {
            $q->where('schedule_id', '!=', $excludeId);
        }

        return $q->exists();
    }

    // Thêm method này vào RoomController.php

    // ================================================================
    //  LỊCH PHÂN BỔ THEO TUẦN CHO 1 PHÒNG
    // ================================================================
    public function weeklySchedule(Request $request, $roomId = null)
    {
        // Lấy danh sách phòng để chọn
        $rooms = Room::orderBy('room_code')->get();

        // Nếu có roomId được chọn hoặc từ request
        $selectedRoomId = $request->get('room_id', $roomId);

        if ($selectedRoomId) {
            $selectedRoom = Room::with('department')->find($selectedRoomId);
        } else {
            $selectedRoom = $rooms->first();
            $selectedRoomId = $selectedRoom ? $selectedRoom->room_id : null;
        }

        // Lấy tuần được chọn (mặc định là tuần hiện tại)
        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->week_start)->startOfWeek()
            : now()->startOfWeek();

        $weekDates = collect(range(0, 6))->map(fn($d) => $weekStart->copy()->addDays($d));
        $timeSlots = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];

        // Lấy schedules cho phòng được chọn trong tuần
        $weekSchedules = collect();
        if ($selectedRoomId) {
            $weekSchedules = DoctorSchedule::with(['doctor', 'room'])
                ->where('room_id', $selectedRoomId)
                ->whereBetween('work_date', [$weekStart->toDateString(), $weekStart->copy()->endOfWeek()->toDateString()])
                ->get()
                ->groupBy(fn($s) => $s->work_date->format('Y-m-d'));
        }

        $prevWeek = $weekStart->copy()->subWeek()->toDateString();
        $nextWeek = $weekStart->copy()->addWeek()->toDateString();

        return view('admin.rooms.weekly-schedule', compact(
            'rooms',
            'selectedRoom',
            'selectedRoomId',
            'weekDates',
            'weekStart',
            'timeSlots',
            'weekSchedules',
            'prevWeek',
            'nextWeek'
        ));
    }
    // AJAX lấy lịch tuần cho 1 phòng
    public function weeklyScheduleAjax(Request $request)
    {
        $roomId = $request->get('room_id');
        $weekStart = $request->filled('week_start')
            ? Carbon::parse($request->week_start)->startOfWeek()
            : now()->startOfWeek();

        if (!$roomId) {
            return response()->json(['success' => false, 'message' => 'Chưa chọn phòng']);
        }

        $room = Room::find($roomId);
        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Phòng không tồn tại']);
        }

        $weekDates = collect(range(0, 6))->map(function ($d) use ($weekStart) {
            $date = $weekStart->copy()->addDays($d);
            return [
                'full_date' => $date->toDateString(),
                'date' => $date->format('d/m'),
                'day' => $date->isoFormat('dd'),
            ];
        });

        $schedules = DoctorSchedule::with(['doctor', 'room'])
            ->where('room_id', $roomId)
            ->whereBetween('work_date', [$weekStart->toDateString(), $weekStart->copy()->endOfWeek()->toDateString()])
            ->get()
            ->groupBy(fn($s) => $s->work_date->format('Y-m-d'))
            ->map(function ($items) {
                return $items->map(function ($item) {
                    return [
                        'start_time' => $item->start_time,
                        'end_time' => $item->end_time,
                        'status' => $item->status,
                        'doctor_name' => $item->doctor->full_name ?? '',
                        'room_code' => $item->room->room_code ?? '',
                    ];
                })->toArray();
            });

        return response()->json([
            'success' => true,
            'room_id' => $roomId,
            'room_code' => $room->room_code,
            'room_name' => $room->room_name,
            'week_start' => $weekStart->format('d/m'),
            'week_end' => $weekStart->copy()->endOfWeek()->format('d/m/Y'),
            'week_dates' => $weekDates,
            'schedules' => $schedules,
            'today' => now()->format('d/m'),
        ]);
    }
}
