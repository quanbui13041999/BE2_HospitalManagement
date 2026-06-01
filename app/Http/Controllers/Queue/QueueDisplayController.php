<?php
namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Models\{DoctorSchedule, QueueTicket, QueueCounter};
use App\Services\QueueService;

class QueueDisplayController extends Controller
{
    /**
     * List tất cả ca khám hôm nay để chọn display
     */
    public function index()
    {
        $schedules = DoctorSchedule::with(['doctor', 'doctor.department', 'room'])
            ->whereDate('work_date', today())
            ->where('status', 'Hoạt động')
            ->get();

        return view('queue.display-list', compact('schedules'));
    }

    // Màn hình TV công khai - không cần auth
    public function show(int $scheduleId, QueueService $queueService)
    {
        $schedule = DoctorSchedule::with(['doctor', 'doctor.department', 'room'])->findOrFail($scheduleId);
        $snapshot = $queueService->getQueueSnapshot($scheduleId);

        return view('queue.display', compact('schedule', 'snapshot'));
    }

    public function apiSnapshot(int $scheduleId, QueueService $queueService)
    {
        $snapshot = $queueService->getQueueSnapshot($scheduleId);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'current' => $snapshot['current'],
            'waiting' => $snapshot['waiting'],
            'stats' => $snapshot['stats'],
            'data' => $snapshot,
        ]); /* fixed: JSON API co cau truc nhat quan */
    }
}
