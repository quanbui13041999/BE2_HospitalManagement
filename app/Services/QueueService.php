<?php
namespace App\Services;

use App\Models\{QueueTicket, QueueCounter, DoctorSchedule, Appointment, User};
use App\Events\{QueueUpdated, TicketCalled};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

            DoctorSchedule::where('schedule_id', $scheduleId)
                ->lockForUpdate()
                ->firstOrFail(); /* fixed: khoa ca kham de nhieu le tan check-in cung luc khong trung so thu tu */

            if (!empty($data['appointment_id']) || !empty($data['user_id'])) {
                $duplicate = QueueTicket::where('schedule_id', $scheduleId)
                    ->whereDate('queue_date', today())
                    ->whereIn('status', ['waiting', 'calling', 'in_progress'])
                    ->when(!empty($data['appointment_id']), fn ($query) => $query->where('appointment_id', $data['appointment_id']))
                    ->when(empty($data['appointment_id']) && !empty($data['user_id']), fn ($query) => $query->where('user_id', $data['user_id']))
                    ->exists();

                if ($duplicate) {
                    throw ValidationException::withMessages([
                        'ticket' => 'Bệnh nhân này đã có trong hàng đợi. Trang sẽ được tải lại để cập nhật danh sách.',
                    ]);
                }
            } /* fixed: chan bam luu lien tuc tao nhieu ticket trung khi co appointment/user */

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
                'served_by'       => array_key_exists('served_by', $data) ? $data['served_by'] : Auth::id(),
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

            // Lịch hẹn thường chỉ được gọi khi đã thanh toán; cấp cứu khám trước, thanh toán sau.
            $next = QueueTicket::forSchedule($scheduleId)
                ->waiting()
                ->where(function ($query) {
                    $query->where('priority', 'emergency')
                        ->orWhereNull('appointment_id')
                        ->orWhereHas('appointment', function ($appointmentQuery) {
                            $appointmentQuery
                                ->where('status', 'Đã thanh toán')
                                ->orWhereHas('payment', function ($paymentQuery) {
                                    $paymentQuery->whereIn('status', ['Thành công', 'Đã thanh toán']);
                                });
                        });
                })
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
        return DB::transaction(function () use ($ticketId) {
            $ticket = QueueTicket::with(['appointment.payment'])
                ->where('ticket_id', $ticketId)
                ->lockForUpdate()
                ->firstOrFail(); /* fixed: khoa ticket khi bac si bat dau kham */

            if ($ticket->status !== 'calling') {
                throw ValidationException::withMessages([
                    'ticket' => 'Ticket này đã được người khác xử lý. Trang sẽ được tải lại để cập nhật hàng đợi.',
                ]);
            }

            if (!$this->canStartExam($ticket)) {
                throw ValidationException::withMessages([
                    'ticket' => 'Bệnh nhân chưa thanh toán nên vẫn ở hàng đợi. Chỉ ca cấp cứu được khám trước và thanh toán sau.',
                ]);
            }

            $ticket->update(['status' => 'in_progress', 'started_at' => now()]);
            broadcast(new QueueUpdated($ticket->schedule_id))->toOthers();
            return $ticket;
        });
    }

    private function canStartExam(QueueTicket $ticket): bool
    {
        if ($ticket->priority === 'emergency') {
            return true;
        }

        if (!$ticket->appointment_id) {
            return true;
        }

        $appointment = $ticket->appointment;

        if (!$appointment) {
            return false;
        }

        if ($appointment->status === 'Đã thanh toán') {
            return true;
        }

        return $appointment->payment?->isPaid() ?? false;
    }

    /**
     * Hoàn thành khám
     */
    public function complete(int $ticketId): QueueTicket
    {
        return DB::transaction(function () use ($ticketId) {
            $ticket = QueueTicket::where('ticket_id', $ticketId)
                ->lockForUpdate()
                ->firstOrFail(); /* fixed: tranh 2 nguoi cung hoan thanh mot ticket */

            if ($ticket->status !== 'in_progress') {
                throw ValidationException::withMessages([
                    'ticket' => 'Ticket này đã được người khác xử lý. Trang sẽ được tải lại để cập nhật hàng đợi.',
                ]);
            }

            $ticket->update(['status' => 'completed', 'completed_at' => now()]);

            // Cập nhật appointment nếu có
            if ($ticket->appointment_id) {
                $appointment = Appointment::with(['user', 'service', 'schedule.doctor', 'medicalRecord'])
                    ->where('appointment_id', $ticket->appointment_id)
                    ->first();

                if ($appointment) {
                    $appointment->update(['status' => 'Hoàn thành']);
                    app(\App\Services\MedicalRecordService::class)->createBlankRecordFromAppointment($appointment->fresh([
                        'user',
                        'service',
                        'schedule.doctor',
                        'medicalRecord',
                    ]));
                }
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
        return DB::transaction(function () use ($ticketId, $reason) {
            $ticket = QueueTicket::where('ticket_id', $ticketId)
                ->lockForUpdate()
                ->firstOrFail(); /* fixed: tranh skip ticket da duoc bac si/le tan khac xu ly */

            if (!in_array($ticket->status, ['waiting', 'calling'], true)) {
                throw ValidationException::withMessages([
                    'ticket' => 'Ticket này đã được người khác xử lý. Trang sẽ được tải lại để cập nhật hàng đợi.',
                ]);
            }

            $ticket->update([
                'status'       => 'skipped',
                'completed_at' => now(),
                'notes'        => $reason ?: $ticket->notes,
            ]);
            broadcast(new QueueUpdated($ticket->schedule_id))->toOthers();
            return $ticket;
        });
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
            ->with(['appointment.payment'])
            ->whereIn('status', ['waiting', 'calling', 'in_progress'])
            ->ordered()
            ->get();

        $current = $tickets->whereIn('status', ['calling', 'in_progress'])->first();
        $waiting = $tickets->where('status', 'waiting')->values();

        return [
            'current'  => $current ? $this->ticketSnapshot($current) : null,
            'waiting'  => $waiting->map(fn (QueueTicket $ticket) => $this->ticketSnapshot($ticket))->values(),
            'stats'    => [
                'total_waiting'   => $waiting->count(),
                'total_callable'  => $waiting->filter(fn (QueueTicket $ticket) => $this->canStartExam($ticket))->count(),
                'total_in_progress' => $tickets->where('status', 'in_progress')->count(),
                'total_completed' => QueueTicket::forSchedule($scheduleId)->where('status', 'completed')->count(),
                'total_today'     => QueueTicket::forSchedule($scheduleId)->count(),
            ],
        ];
    }

    private function ticketSnapshot(QueueTicket $ticket): array
    {
        $canStartExam = $this->canStartExam($ticket);
        $payment = $ticket->appointment?->payment;

        return [
            'ticket_id' => $ticket->ticket_id,
            'appointment_id' => $ticket->appointment_id,
            'queue_number' => $ticket->queue_number,
            'patient_name' => $ticket->patient_name,
            'patient_phone' => $ticket->patient_phone,
            'patient_email' => $ticket->patient_email,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'priority_icon' => $ticket->priority_icon,
            'priority_label' => $ticket->priority_label,
            'checkin_time' => $ticket->checkin_time,
            'est_wait_minutes' => $ticket->est_wait_minutes,
            'notes' => $ticket->notes,
            'payment_required' => (bool) $ticket->appointment_id && $ticket->priority !== 'emergency',
            'payment_status' => $payment?->status,
            'appointment_status' => $ticket->appointment?->status,
            'can_start_exam' => $canStartExam,
        ];
    }
}
