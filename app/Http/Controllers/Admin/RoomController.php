<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DoctorScheduleRequest;
use App\Http\Requests\Admin\RoomRequest;
use App\Http\Requests\Admin\RoomStatusRequest;
use App\Models\DoctorSchedule;
use App\Models\Room;
use App\Services\Admin\RoomService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function __construct(protected RoomService $roomService) {}

    // ================================================================
    //  PHÒNG KHÁM
    // ================================================================

    public function index(Request $request)
    {
        return view('admin.rooms.index', $this->roomService->buildIndexData($request));
    }

    public function create()
    {
        return view('admin.rooms.create', $this->roomService->buildCreateData());
    }

    public function store(RoomRequest $request)
    {
        Room::create($request->validated());

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
        $room->update($request->validated());

        return redirect()->route('admin.rooms.show', $room)
            ->with('success', 'Cập nhật phòng thành công!');
    }

    public function updateStatus(RoomStatusRequest $request, Room $room)
    {
        $room->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'status' => $room->status]);
        }

        return back()->with('success', 'Đã cập nhật trạng thái phòng.');
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
}
