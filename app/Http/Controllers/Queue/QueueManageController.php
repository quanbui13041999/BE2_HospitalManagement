<?php
namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Services\QueueService;
use App\Models\{DoctorSchedule, QueueTicket};
use Illuminate\Http\Request;

class QueueManageController extends Controller
{
    public function __construct(private QueueService $queueService)
    {
    }

    /**
     * Dashboard lễ tân: chọn ca khám để quản lý
     */
    public function index()
    {
        // Lấy các ca khám đang hoạt động hôm nay
        $schedules = DoctorSchedule::with(['doctor', 'doctor.department'])
            ->whereDate('work_date', today())
            ->where('status', 'Hoạt động')
            ->get()
            ->map(function ($s) {
                $s->queue_count = QueueTicket::forSchedule($s->schedule_id)->active()->count();
                return $s;
            });

        return view('queue.manage.index', compact('schedules'));
    }

    /**
     * Trang quản lý hàng đợi của 1 ca cụ thể
     */
    public function show(int $scheduleId)
    {
        $schedule  = DoctorSchedule::with(['doctor', 'doctor.department', 'room'])->findOrFail($scheduleId);
        $snapshot  = $this->queueService->getQueueSnapshot($scheduleId);

        // Lịch sử trong ngày (completed/skipped/cancelled)
        $history = QueueTicket::forSchedule($scheduleId)
            ->whereIn('status', ['completed', 'skipped', 'cancelled'])
            ->orderByDesc('completed_at')
            ->limit(20)
            ->get();

        return view('queue.manage.show', compact('schedule', 'snapshot', 'history'));
    }

    /**
     * Form tìm kiếm bệnh nhân để check-in
     */
    public function searchPatient(Request $request)
    {
        $keyword  = trim($request->input('keyword', ''));
        $result   = $keyword ? $this->queueService->findPatient($keyword) : ['found' => false];
        $schedules = DoctorSchedule::with('doctor')
            ->whereDate('work_date', today())
            ->where('status', 'Hoạt động')
            ->get();

        return view('queue.manage.checkin', compact('result', 'keyword', 'schedules'));
    }

    /**
     * Xử lý check-in bệnh nhân
     */
    public function checkin(Request $request)
    {
        $request->validate([
            'schedule_id'  => 'required|exists:doctorschedules,schedule_id',
            'patient_name' => 'required|string|max:100',
            'priority'     => 'required|in:normal,elderly,disabled,emergency',
            'patient_phone'=> 'nullable|string|max:15',
            'patient_email'=> 'nullable|email|max:100',
            'notes'        => 'nullable|string|max:255',
            'appointment_id' => 'nullable|exists:appointments,appointment_id',
            'user_id'      => 'nullable|exists:users,user_id',
        ]);

        $ticket = $this->queueService->checkin($request->all());

        return redirect()
            ->route('queue.manage.show', $request->schedule_id)
            ->with('success', "Check-in thành công! Số thứ tự: #{$ticket->queue_number}");
    }

    /**
     * Bỏ qua / hủy 1 ticket
     */
    public function skip(Request $request, int $ticketId)
    {
        $this->queueService->skip($ticketId, $request->input('reason', ''));
        return back()->with('success', 'Đã bỏ qua ticket.');
    }

    /**
     * API: Lấy snapshot realtime (polling fallback)
     */
    public function apiSnapshot(int $scheduleId)
    {
        return response()->json($this->queueService->getQueueSnapshot($scheduleId));
    }
}
