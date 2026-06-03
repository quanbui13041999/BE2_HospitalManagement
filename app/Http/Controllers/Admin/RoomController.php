<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DoctorScheduleRequest;
use App\Http\Requests\Admin\RoomRequest;
use App\Http\Requests\Admin\RoomStatusRequest;
use App\Models\DoctorSchedule;
use App\Models\Room;
use App\Services\Admin\RoomService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function __construct(protected RoomService $roomService) {}

    // ================================================================
    //  PHÒNG KHÁM
    // ================================================================

    public function index(Request $request)
    {
        // Validate page param: phải là số nguyên dương
        $page = $request->query('page');
        if ($page !== null && (!ctype_digit((string) $page) || (int) $page < 1)) {
            return redirect()->route('admin.rooms.index', array_merge(
                $request->except('page'),
                ['page' => 1]
            ))->with('error', 'Tham số trang không hợp lệ, đã chuyển về trang 1.');
        }

        return view('admin.rooms.index', $this->roomService->buildIndexData($request));
    }

    public function create()
    {
        return view('admin.rooms.create', $this->roomService->buildCreateData());
    }

    public function store(RoomRequest $request)
    {
        $room = Room::create($request->validated());

        ActivityLogService::log(
            'Admin thêm phòng',
            'Admin ' . (Auth::user()?->full_name ?: '') . ' đã thêm phòng khám ' . ($room->room_name ?? ('#' . $room->room_id)) . '.',
            'room',
            $room->room_id,
            ['room' => $room->only(['room_id', 'room_name', 'room_type', 'status'])]
        );

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Tạo phòng khám thành công!');
    }

    public function show(Room $room, Request $request)
    {
        return view('admin.rooms.show', $this->roomService->buildShowData($room, $request));
    }

    public function edit(Room $room)
    {
        return view('admin.rooms.edit', $this->roomService->buildEditData($room));
    }

    public function update(RoomRequest $request, Room $room)
    {
        // Optimistic locking: kiểm tra dữ liệu có bị thay đổi bởi tab khác không
        $lockVersion = $request->input('_lock_version');
        if ($lockVersion !== null && $room->updated_at !== null) {
            $dbTimestamp = (string) $room->updated_at->timestamp;
            if ($lockVersion !== $dbTimestamp) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Dữ liệu đã được cập nhật bởi người khác. Vui lòng tải lại trang trước khi cập nhật.',
                    ], 409);
                }
                return redirect()->route('admin.rooms.edit', $room)
                    ->with('error', 'Dữ liệu phòng khám đã được người khác cập nhật. Vui lòng tải lại trang trước khi tiếp tục chỉnh sửa.');
            }
        }

        $before = $room->only(['room_name', 'room_type', 'status', 'notes']);
        $room->update($request->validated());

        ActivityLogService::log(
            'Admin sửa phòng',
            'Admin ' . (Auth::user()?->full_name ?: '') . ' đã cập nhật phòng khám ' . ($room->room_name ?? ('#' . $room->room_id)) . '.',
            'room',
            $room->room_id,
            [
                'changes' => ActivityLogService::summarizeChanges(
                    $before,
                    $room->fresh()->only(['room_name', 'room_type', 'status', 'notes']),
                    ['room_name', 'room_type', 'status', 'notes']
                ),
            ]
        );

        return redirect()->route('admin.rooms.show', $room)
            ->with('success', 'Cập nhật phòng thành công!');
    }

    public function updateStatus(RoomStatusRequest $request, Room $room)
    {
        $before = $room->status;
        $room->update($request->validated());

        ActivityLogService::log(
            'Admin sửa phòng',
            'Admin ' . (Auth::user()?->full_name ?: '') . ' đã cập nhật trạng thái phòng ' . ($room->room_name ?? ('#' . $room->room_id)) . '.',
            'room',
            $room->room_id,
            [
                'changes' => [
                    'status' => [
                        'before' => $before,
                        'after' => $room->status,
                    ],
                ],
            ]
        );

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'status' => $room->status]);
        }

        return back()->with('success', 'Đã cập nhật trạng thái phòng.');
    }

    public function destroy(Room $room)
    {
        $error = $this->roomService->destroyRoom($room);

        if ($error) {
            return back()->with('error', $error);
        }

        return redirect()->route('admin.rooms.index')
            ->with('success', 'Xoá phòng khám thành công!');
    }

    // ================================================================
    //  CA TRỰC (DoctorSchedules)
    // ================================================================

    public function scheduleIndex(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        return view('admin.rooms.schedule-index', $this->roomService->buildScheduleIndexData($date));
    }

    public function scheduleAll(Request $request)
    {
        return view('admin.rooms.schedule-all', $this->roomService->buildScheduleAllData($request));
    }

    public function createSchedule(Request $request)
    {
        return view('admin.rooms.schedule-create', $this->roomService->buildScheduleCreateData($request));
    }

    public function storeSchedule(DoctorScheduleRequest $request)
    {
        $errors = $this->roomService->storeSchedule($request->validated());

        if ($errors) {
            return back()->withErrors($errors)->withInput();
        }

        return redirect()
            ->route('admin.rooms.schedule.index', ['date' => $request->work_date])
            ->with('success', 'Tạo ca làm việc thành công!');
    }

    /**
     * Tự động phân ca trực dựa trên thiết lập.
     */
    public function autoAllocate(Request $request)
    {
        $request->validate([
            'start_date'        => 'required|date|after_or_equal:today',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'slot_duration'     => 'required|integer|in:10,15,20,30,45,60',
            'max_slot'          => 'required|integer|min:1|max:100',
            'morning_enabled'   => 'nullable|boolean',
            'afternoon_enabled' => 'nullable|boolean',
            'overwrite'         => 'nullable|boolean',
            'department_id'     => 'nullable|exists:departments,department_id',
        ]);

        $res = $this->roomService->autoAllocateSchedules($request->all());

        if (!$res['success']) {
            return back()->with('error', $res['message']);
        }

        return redirect()
            ->route('admin.rooms.schedule.index', ['date' => $request->start_date])
            ->with('success', $res['message']);
    }

    public function editSchedule(DoctorSchedule $schedule)
    {
        return view('admin.rooms.schedule-edit', $this->roomService->buildScheduleEditData($schedule));
    }

    public function updateSchedule(DoctorScheduleRequest $request, DoctorSchedule $schedule)
    {
        $errors = $this->roomService->updateSchedule($schedule, $request->validated());

        if ($errors) {
            return back()->withErrors($errors)->withInput();
        }

        return redirect()
            ->route('admin.rooms.schedule.index', ['date' => $schedule->work_date])
            ->with('success', 'Cập nhật ca làm việc thành công!');
    }

    public function destroySchedule(DoctorSchedule $schedule)
    {
        if ($schedule->booked_slots > 0) {
            return back()->with('error', 'Không thể xoá ca đã có bệnh nhân đặt lịch.');
        }

        $date = $schedule->work_date->toDateString();
        $schedule->delete();

        return redirect()
            ->route('admin.rooms.schedule.index', ['date' => $date])
            ->with('success', 'Đã xoá ca làm việc.');
    }

    // ================================================================
    //  LỊCH TUẦN
    // ================================================================

    public function weeklySchedule(Request $request, $roomId = null)
    {
        return view('admin.rooms.weekly-schedule', $this->roomService->buildWeeklyData($request, $roomId));
    }

    public function weeklyScheduleAjax(Request $request)
    {
        return response()->json($this->roomService->buildWeeklyAjaxResponse($request));
    }

    // ================================================================
    //  AJAX: Kiểm tra xung đột lịch
    // ================================================================

    public function checkConflict(Request $request)
    {
        // Delegate sang service layer qua repository
        $conflict = app(\App\Repositories\RoomRepository::class)->hasScheduleConflict(
            $request->doctor_id,
            $request->work_date,
            $request->start_time,
            $request->end_time,
            $request->exclude_id ?? null
        );

        return response()->json(['conflict' => $conflict]);
    }

    /**
     * JSON endpoint cho realtime polling trạng thái phòng.
     */
    public function roomsData(Request $request)
    {
        $rooms = $this->roomService->buildIndexData($request);
        $stats = $rooms['stats'];
        $roomList = $rooms['rooms']->map(function ($r) use ($rooms) {
            $todayDoc = $rooms['todaySchedules']->firstWhere('room_id', $r->room_id);
            return [
                'room_id'     => $r->room_id,
                'room_code'   => $r->room_code,
                'room_name'   => $r->room_name,
                'room_type'   => $r->room_type,
                'status'      => $r->status,
                'department'  => $r->department?->department_name,
                'doctor_today'=> $todayDoc?->doctor?->full_name,
                'edit_url'    => route('admin.rooms.edit', $r),
                'show_url'    => route('admin.rooms.show', $r),
                'destroy_url' => route('admin.rooms.destroy', $r),
            ];
        });

        return response()->json([
            'stats'       => $stats,
            'rooms'       => $roomList,
            'timestamp'   => now()->toIso8601String(),
        ]);
    }
}
