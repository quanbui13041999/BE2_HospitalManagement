<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorSchedule;
use App\Models\QueueTicket;
use App\Models\Doctor;
use App\Services\QueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueController extends Controller
{
    public function __construct(private QueueService $queueService)
    {
    }

    /**
     * Unified Dashboard - Admin sees all, Doctor/Receptionist sees their own
     */
    public function index()
    {
        $user = Auth::user();
        $userRole = $user->role_id; // 1=admin, 2=doctor, 3=patient, 4=receptionist, 5=pharmacist

        // Lấy tất cả ca khám hoạt động hôm nay
        $baseQuery = DoctorSchedule::with(['doctor', 'doctor.department', 'room'])
            ->whereDate('work_date', today())
            ->where('status', 'Hoạt động');

        // Filter theo role
        if ($userRole === 2) { // Doctor
            $doctor = Doctor::where('user_id', $user->user_id)->first();
            if (!$doctor) {
                return back()->with('error', 'Bác sĩ không tìm thấy.');
            }
            $baseQuery->where('doctor_id', $doctor->doctor_id);
        }
        // Admin (role 1) và Receptionist (role 4) thấy tất cả
        // Pharmacist (role 5) - cũng thấy tất cả (cho thống kê)

        $schedules = $baseQuery->get()
            ->map(function ($s) {
                $snapshot = $this->queueService->getQueueSnapshot($s->schedule_id);
                $s->queue_stats = $snapshot['stats'];
                $s->current_ticket = $snapshot['current'];
                $s->waiting_list = $snapshot['waiting'];
                return $s;
            });

        // Thống kê tổng hợp
        $totalStats = [
            'total_waiting' => QueueTicket::whereDate('queue_date', today())
                ->whereIn('status', ['waiting', 'calling'])
                ->count(),
            'total_in_progress' => QueueTicket::whereDate('queue_date', today())
                ->where('status', 'in_progress')
                ->count(),
            'total_completed' => QueueTicket::whereDate('queue_date', today())
                ->where('status', 'completed')
                ->count(),
            'total_today' => QueueTicket::whereDate('queue_date', today())
                ->count(),
        ];

        // Lấy các ticket gần hoàn thành
        $recentCompleted = QueueTicket::whereDate('queue_date', today())
            ->where('status', 'completed')
            ->orderByDesc('completed_at')
            ->limit(10)
            ->with(['schedule.doctor', 'user'])
            ->get();

        // Xác định user role name
        $roleNames = [
            1 => 'admin',
            2 => 'doctor',
            3 => 'patient',
            4 => 'receptionist',
            5 => 'pharmacist',
        ];
        $userRoleName = $roleNames[$userRole] ?? 'guest';

        return view('admin.queue.dashboard', compact('schedules', 'totalStats', 'recentCompleted', 'userRole', 'userRoleName'));
    }

    /**
     * Chi tiết hàng đợi của 1 ca khám
     */
    public function show(int $scheduleId)
    {
        $schedule = DoctorSchedule::with(['doctor', 'doctor.department', 'room'])
            ->findOrFail($scheduleId);

        $snapshot = $this->queueService->getQueueSnapshot($scheduleId);

        // Lịch sử ngày hôm nay
        $history = QueueTicket::forSchedule($scheduleId)
            ->whereIn('status', ['completed', 'skipped', 'cancelled'])
            ->orderByDesc('completed_at')
            ->get();

        // Lấy user role
        $user = Auth::user();
        $userRole = $user->role_id;
        $roleNames = [
            1 => 'admin',
            2 => 'doctor',
            3 => 'patient',
            4 => 'receptionist',
            5 => 'pharmacist',
        ];
        $userRoleName = $roleNames[$userRole] ?? 'guest';

        return view('admin.queue.show', compact('schedule', 'snapshot', 'history', 'userRole', 'userRoleName'));
    }
    /**
     * API: Snapshot realtime cho 1 ca khám
     */
    public function apiSnapshot(int $scheduleId)
    {
        $snapshot = $this->queueService->getQueueSnapshot($scheduleId);

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'current' => $snapshot['current'],
            'waiting' => $snapshot['waiting'],
            'stats' => $snapshot['stats'],
            'data' => $snapshot,
        ]); /* fixed: JSON API co cau truc nhat quan */
    }

    /**
     * API: Snapshot tất cả các ca khám hôm nay
     */
    public function apiAllSnapshots()
    {
        $schedules = DoctorSchedule::whereDate('work_date', today())
            ->where('status', 'Hoạt động')
            ->pluck('schedule_id');

        $snapshots = [];
        foreach ($schedules as $scheduleId) {
            $snapshots[$scheduleId] = $this->queueService->getQueueSnapshot($scheduleId);
        }

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'snapshots' => $snapshots,
            'data' => $snapshots,
        ]); /* fixed: JSON API co cau truc nhat quan */
    }

    /**
     * Báo cáo hàng đợi của ngày (thống kê chi tiết)
     */
    public function report(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
        ]);

        $date = $validated['date'] ?? today(); /* fixed: validate filter date truoc khi query */

        $schedules = DoctorSchedule::with(['doctor', 'doctor.department'])
            ->whereDate('work_date', $date)
            ->where('status', 'Hoạt động')
            ->get();

        $reportData = [];
        foreach ($schedules as $schedule) {
            $tickets = QueueTicket::forSchedule($schedule->schedule_id)
                ->whereDate('queue_date', $date)
                ->get();

            $reportData[] = [
                'schedule' => $schedule,
                'total_tickets' => $tickets->count(),
                'completed' => $tickets->where('status', 'completed')->count(),
                'skipped' => $tickets->where('status', 'skipped')->count(),
                'cancelled' => $tickets->where('status', 'cancelled')->count(),
                'avg_wait_time' => $tickets->where('status', 'completed')->avg('est_wait_minutes'),
                'max_queue_number' => $tickets->max('queue_number') ?? 0,
            ];
        }

        return view('admin.queue.report', compact('reportData', 'date', 'schedules'));
    }
}
