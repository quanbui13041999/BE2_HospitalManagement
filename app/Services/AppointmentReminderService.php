<?php

namespace App\Services;

use App\Models\Appointment;
use App\Services\Doctor\BrevoMailService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AppointmentReminderService
{
    public function __construct(private BrevoMailService $brevoMailService)
    {
    }

    public function sendPendingReminders(): array
    {
        return [
            'sent_1day' => $this->send1DayReminders(),
            'sent_1hour' => $this->send1HourReminders(),
        ];
    }

    public function send1DayReminders(): int
    {
        $appointments = Appointment::with(['user', 'schedule.doctor.department', 'service'])
            ->whereIn('status', ['Chờ xác nhận', 'Đã xác nhận'])
            ->whereDate('appointment_time', Carbon::tomorrow()->toDateString())
            ->where('mail_reminded_1day', false)
            ->get();

        $sent = 0;
        /** @var Appointment $appointment */
        foreach ($appointments as $appointment) {
            if ($this->sendReminder($appointment, '1day')) {
                $sent++;
            }
        }

        return $sent;
    }

    public function send1HourReminders(): int
    {
        $now = Carbon::now();
        $from = $now->copy()->startOfMinute()->addMinutes(45);
        $to = $now->copy()->endOfMinute()->addMinutes(75);

        $appointments = Appointment::with(['user', 'schedule.doctor.department', 'service'])
            ->whereIn('status', ['Chờ xác nhận', 'Đã xác nhận'])
            ->where('mail_reminded_1hour', false)
            ->whereBetween('appointment_time', [$from, $to])
            ->get();

        $sent = 0;
        /** @var Appointment $appointment */
        foreach ($appointments as $appointment) {
            if ($this->sendReminder($appointment, '1hour')) {
                $sent++;
            }
        }

        return $sent;
    }

    protected function sendReminder(Appointment $appointment, string $type): bool
    {
        $user = $appointment->user;
        if (! $user || ! $user->email || ! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('[AppointmentReminder] Bỏ qua appointment_id ' . $appointment->appointment_id . ' vì email không hợp lệ.');
            return false;
        }

        $params = $this->buildEmailParams($appointment);
        $toName = $user->full_name ?? $user->name ?? 'Bệnh nhân';
        $result = false;

        if ($type === '1day') {
            $result = $this->brevoMailService->sendReminder1Day($user->email, $toName, $params);
        } elseif ($type === '1hour') {
            $result = $this->brevoMailService->sendReminder1Hour($user->email, $toName, $params);
        }

        if ($result) {
            $appointment->{$type === '1day' ? 'mail_reminded_1day' : 'mail_reminded_1hour'} = true;
            $appointment->save();
            Log::info(sprintf('[AppointmentReminder] Gửi email %s thành công cho appointment_id %s', $type, $appointment->appointment_id));
            return true;
        }

        Log::warning(sprintf('[AppointmentReminder] Gửi email %s thất bại cho appointment_id %s', $type, $appointment->appointment_id));
        return false;
    }

    protected function buildEmailParams(Appointment $appointment): array
    {
        $schedule = $appointment->schedule;
        $doctor = $schedule?->doctor;
        $department = $doctor?->department;
        $service = $appointment->service;

        return [
            'patient_name' => $appointment->user->full_name ?? $appointment->user->name ?? 'Quý khách',
            'doctor_name' => $doctor->full_name ?? 'Bác sĩ',
            'department_name' => $department->department_name ?? 'Chuyên khoa',
            'service_name' => $service->service_name ?? 'Khám tổng quát',
            'clinic_address' => config('app.clinic_address', config('app.name', 'Phòng khám')), 
            'appointment_date' => optional($appointment->appointment_time)->format('d/m/Y'),
            'appointment_time' => optional($appointment->appointment_time)->format('H:i'),
            'queue_number' => $appointment->queue_number,
        ];
    }
}
