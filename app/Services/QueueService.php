<?php
namespace App\Services;

use App\Models\{QueueTicket, QueueCounter, DoctorSchedule, Appointment, User};
use App\Events\{QueueUpdated, TicketCalled};
use Illuminate\Support\Facades\DB;

class QueueService
{
    /**
     * Tìm bệnh nhân qua mã appointment, SĐT, hoặc email
     */
    public function findPatient(string $keyword): array
    {
        // 1. Tìm theo appointment_id
        $appointment = Appointment::with(['user', 'schedule.doctor'])
            ->where('appointment_id', $keyword)
            ->whereDate('appointment_time', today())
            ->where('status', 'Chờ xác nhận')
            ->first();

        if ($appointment) {
            return ['found' => true, 'type' => 'appointment', 'data' => $appointment];
        }

        // 2. Tìm theo SĐT hoặc email
        $user = User::where('phone', $keyword)
            ->orWhere('email', $keyword)
            ->first();

        if ($user) {
            $appointments = Appointment::with(['schedule.doctor'])
                ->where('user_id', $user->user_id)
                ->whereDate('appointment_time', today())
                ->whereIn('status', ['Chờ xác nhận', 'Đã xác nhận'])
                ->get();
            return ['found' => true, 'type' => 'user', 'data' => $user, 'appointments' => $appointments];
        }

        return ['found' => false];
    }

    /**
     * Xếp số thứ tự mới - có xử lý ưu tiên
     */
    public function checkin(array $data): QueueTicket
    {
        return DB::transaction(function () use ($data) {
            $scheduleId = $data['schedule_id'];
            $priority   = $data['priority'] ?? 'normal';

            // Lấy số thứ tự tiếp theo cho ngày + ca này
            $lastNumber = QueueTicket::where('schedule_id', $scheduleId)
                ->whereDate('queue_date', today())
                ->max('queue_number') ?? 0;

            $queueNumber = $lastNumber + 1;

            // Tính estimated wait tạm thời: đếm ticket đang chờ trước mình
            $waitingBefore = QueueTicket::forSchedule($scheduleId)
                ->waiting()
                ->where('priority_sort', '<=', QueueTicket::PRIORITY_SORT[$priority])
                ->count();
            $estWait = $waitingBefore * 15; // 15 phút/bệnh nhân trung bình

            $ticket = QueueTicket::create([
                'appointment_id'  => $data['appointment_id'] ?? null,
                'schedule_id'     => $scheduleId,
                'user_id'         => $data['user_id'] ?? null,
                'patient_name'    => $data['patient_name'],
                'patient_phone'   => $data['patient_phone'] ?? null,
                'patient_email'   => $data['patient_email'] ?? null,
                'queue_date'      => today(),
                'queue_number'    => $queueNumber,
                'priority'        => $priority,
                'priority_sort'   => QueueTicket::PRIORITY_SORT[$priority],
                'status'          => 'waiting',
                'checkin_time'    => now(),
                'est_wait_minutes'=> $estWait,
                'notes'           => $data['notes'] ?? null,
                'served_by'       => array_key_exists('served_by', $data) ? $data['served_by'] : auth()->id(),
            ]);

            // Nếu có appointment → cập nhật status
            if (!empty($data['appointment_id'])) {
                Appointment::where('appointment_id', $data['appointment_id'])
                    ->update(['status' => 'Đã xác nhận']);
            }

            // Tự động tính toán lại thời gian chờ chính xác cho toàn bộ người đang chờ
            $this->recalculateWaitTimes($scheduleId);

            // Broadcast realtime
            broadcast(new QueueUpdated($scheduleId))->toOthers();

            return $ticket;
        });
    }

    /**
     * Gọi số tiếp theo (bác sĩ hoặc lễ tân)
     * Ưu tiên: emergency > disabled > elderly > normal, trong cùng nhóm theo queue_number ASC
     */
    public function callNext(int $scheduleId): ?QueueTicket
    {
        return DB::transaction(function () use ($scheduleId) {
            // Đóng ticket đang calling (nếu có) → skipped
            QueueTicket::forSchedule($scheduleId)
                ->where('status', 'calling')
                ->update(['status' => 'skipped', 'completed_at' => now()]);

            // Lấy ticket tiếp theo theo thứ tự ưu tiên
            $next = QueueTicket::forSchedule($scheduleId)
                ->waiting()
                ->ordered()
                ->lockForUpdate()
                ->first();

            if (!$next) return null;

            $next->update([
                'status'    => 'calling',
                'called_at' => now(),
            ]);

            // Cập nhật counter
            QueueCounter::updateOrCreate(
                ['schedule_id' => $scheduleId],
                ['current_ticket_id' => $next->ticket_id, 'last_called_number' => $next->queue_number]
            );

            broadcast(new TicketCalled($next))->toOthers();
            broadcast(new QueueUpdated($scheduleId))->toOthers();

            return $next;
        });
    }

    /**
     * Bắt đầu khám (chuyển calling → in_progress)
     */
    public function startExam(int $ticketId): QueueTicket
    {
        $ticket = QueueTicket::findOrFail($ticketId);
        $ticket->update(['status' => 'in_progress', 'started_at' => now()]);
        broadcast(new QueueUpdated($ticket->schedule_id))->toOthers();
        return $ticket;
    }

    /**
     * Hoàn thành khám
     */
    public function complete(int $ticketId): QueueTicket
    {
        return DB::transaction(function () use ($ticketId) {
            $ticket = QueueTicket::findOrFail($ticketId);
            $ticket->update(['status' => 'completed', 'completed_at' => now()]);

            // Cập nhật appointment nếu có
            if ($ticket->appointment_id) {
                Appointment::where('appointment_id', $ticket->appointment_id)
                    ->update(['status' => 'Đã Khám']);
            }

            // Recalculate est_wait cho các ticket còn lại
            $this->recalculateWaitTimes($ticket->schedule_id);

            broadcast(new QueueUpdated($ticket->schedule_id))->toOthers();
            return $ticket;
        });
    }

    /**
     * Bỏ qua / skip một ticket
     */
    public function skip(int $ticketId, string $reason = ''): QueueTicket
    {
        $ticket = QueueTicket::findOrFail($ticketId);
        $ticket->update([
            'status'       => 'skipped',
            'completed_at' => now(),
            'notes'        => $reason ?: $ticket->notes,
        ]);
        broadcast(new QueueUpdated($ticket->schedule_id))->toOthers();
        return $ticket;
    }

    /**
     * Cập nhật lại thời gian chờ ước tính
     */
    private function recalculateWaitTimes(int $scheduleId): void
    {
        $waiting = QueueTicket::forSchedule($scheduleId)->waiting()->ordered()->get();
        foreach ($waiting as $index => $ticket) {
            $ticket->update(['est_wait_minutes' => ($index + 1) * 15]);
        }
    }

    /**
     * Lấy snapshot đầy đủ hàng đợi cho 1 ca
     */
    public function getQueueSnapshot(int $scheduleId): array
    {
        $tickets = QueueTicket::forSchedule($scheduleId)
            ->whereIn('status', ['waiting', 'calling', 'in_progress'])
            ->ordered()
            ->get();

        $current = $tickets->whereIn('status', ['calling', 'in_progress'])->first();

        return [
            'current'  => $current ? [
                'ticket_id' => $current->ticket_id,
                'queue_number' => $current->queue_number,
                'patient_name' => $current->patient_name,
                'patient_phone' => $current->patient_phone,
                'status' => $current->status,
                'priority' => $current->priority,
                'priority_icon' => $current->priority_icon,
                'priority_label' => $current->priority_label,
            ] : null,
            'waiting'  => $tickets->where('status', 'waiting')->values(),
            'stats'    => [
                'total_waiting'   => $tickets->where('status', 'waiting')->count(),
                'total_in_progress' => $tickets->where('status', 'in_progress')->count(),
                'total_completed' => QueueTicket::forSchedule($scheduleId)->where('status', 'completed')->count(),
                'total_today'     => QueueTicket::forSchedule($scheduleId)->count(),
            ],
        ];
    }
}
