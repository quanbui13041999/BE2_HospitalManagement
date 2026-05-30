<?php
namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Services\QueueService;
use App\Models\{DoctorSchedule, Doctor, QueueTicket};
use Illuminate\Http\Request;

class QueueDoctorController extends Controller
{
    public function __construct(private QueueService $queueService)
    {
    }

    /**
     * Dashboard bác sĩ: hiển thị hàng đợi ca của mình hôm nay
     */
    public function index()
    {
        $userId = auth()->id();

        // Nếu là bác sĩ (role_id = 2) → lấy schedule của bác sĩ đó
        // Nếu là admin (role_id = 1) → lấy tất cả schedule hôm nay
        if (auth()->user()->role_id == 2) {
            $doctor = Doctor::where('user_id', $userId)->firstOrFail();
            $schedules = DoctorSchedule::with(['room'])
                ->where('doctor_id', $doctor->doctor_id)
                ->whereDate('work_date', today())
                ->get();
        } else {
            $schedules = DoctorSchedule::with(['doctor', 'room'])
                ->whereDate('work_date', today())
                ->get();
        }

        // Gắn snapshot vào từng schedule
        $schedules = $schedules->map(function ($s) {
            $s->snapshot = $this->queueService->getQueueSnapshot($s->schedule_id);
            return $s;
        });

        return view('queue.doctor.index', compact('schedules'));
    }

    /**
     * Gọi số tiếp theo
     */
    public function callNext(Request $request, int $scheduleId)
    {
        $ticket = $this->queueService->callNext($scheduleId);

        if (!$ticket) {
            return back()->with('info', 'Không còn bệnh nhân đủ điều kiện khám. Bệnh nhân chưa thanh toán vẫn ở hàng đợi; ca cấp cứu được gọi ngay.');
        }

        return back()->with('success', "Đã gọi số #{$ticket->queue_number} - {$ticket->patient_name}");
    }

    /**
     * Bắt đầu khám (calling → in_progress)
     */
    public function startExam(int $ticketId)
    {
        $ticket = $this->queueService->startExam($ticketId);
        return back()->with('success', 'Đã bắt đầu khám.');
    }

    /**
     * Hoàn thành khám
     */
    public function complete(int $ticketId)
    {
        $ticket = $this->queueService->complete($ticketId);
        return back()->with('success', "Hoàn thành khám cho #{$ticket->queue_number}.");
    }

    /**
     * API snapshot cho bác sĩ (realtime polling)
     */
    public function apiSnapshot(int $scheduleId)
    {
        return response()->json($this->queueService->getQueueSnapshot($scheduleId));
    }
}
