<?php

namespace App\Services\Admin;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**
     * Lấy thống kê tổng quan lịch hẹn
     *
     * FIX TIMEZONE: Dùng Carbon::now(config('app.timezone')) để đảm bảo
     * đúng múi giờ Asia/Ho_Chi_Minh thay vì UTC mặc định.
     *
     * FIX LOGIC: Stats tổng quan dùng created_at (thời điểm đặt lịch)
     * thay vì appointment_time (giờ khám trong tương lai) để phản ánh
     * hoạt động thực tế trong khoảng thời gian được chọn.
     */
    public function getAppointmentStats(?string $timeRange = 'week'): array
    {
        $now       = $this->now();
        $startDate = $this->getStartDate($timeRange, $now)->startOfDay();

        // Dùng created_at: lịch hẹn được tạo trong khoảng thời gian này
        $query = Appointment::query()
            ->whereBetween('created_at', [$startDate, $now]);

        $total     = (clone $query)->count();
        $completed = (clone $query)->where('status', 'Hoàn thành')->count();
        $cancelled = (clone $query)->where('status', 'Đã hủy')->count();
        $active    = (clone $query)->whereIn('status', ['Chờ xác nhận', 'Đã xác nhận', 'Đang khám'])->count();

        // So sánh với kỳ trước
        $prevEnd   = $startDate->copy()->subSecond();
        $prevStart = $this->getStartDate($timeRange, $prevEnd->copy())->startOfDay();
        $prevQuery = Appointment::query()->whereBetween('created_at', [$prevStart, $prevEnd]);
        $prevTotal     = (clone $prevQuery)->count();
        $prevCompleted = (clone $prevQuery)->where('status', 'Hoàn thành')->count();
        $prevCancelled = (clone $prevQuery)->where('status', 'Đã hủy')->count();

        return [
            'total'             => $total,
            'completed'         => $completed,
            'cancelled'         => $cancelled,
            'active'            => $active,
            'cancellation_rate' => $total > 0 ? round(($cancelled / $total) * 100, 1) : 0,
            'total_change'      => $prevTotal > 0     ? round((($total     - $prevTotal)     / $prevTotal)     * 100, 1) : null,
            'completed_change'  => $prevCompleted > 0 ? round((($completed - $prevCompleted) / $prevCompleted) * 100, 1) : null,
            'cancelled_change'  => $prevCancelled > 0 ? round((($cancelled - $prevCancelled) / $prevCancelled) * 100, 1) : null,
        ];
    }

    /**
     * Lấy thống kê bệnh nhân
     * FIX: role_id = 3 (bệnh nhân), không dùng != 1 (tránh đếm bác sĩ)
     */
    public function getPatientStats(): array
    {
        $patientRole = 3;
        $now         = $this->now();

        $total = User::where('role_id', $patientRole)->count();
        $new   = User::where('role_id', $patientRole)
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        $returning = DB::table('users as u')
            ->where('u.role_id', $patientRole)
            ->whereIn('u.user_id',
                DB::table('appointments')
                    ->select('user_id')
                    ->where('status', 'Hoàn thành')
                    ->groupBy('user_id')
                    ->havingRaw('COUNT(*) > 1')
            )
            ->count();

        $male   = User::where('role_id', $patientRole)->where('gender', 'Nam')->count();
        $female = User::where('role_id', $patientRole)->where('gender', 'Nữ')->count();

        return [
            'total'          => $total,
            'new'            => $new,
            'returning'      => $returning,
            'returning_rate' => $total > 0 ? round(($returning / $total) * 100, 1) : 0,
            'male'           => $male,
            'female'         => $female,
        ];
    }

    /**
     * Lấy thống kê hiệu suất hệ thống
     */
    public function getPerformanceStats(?string $timeRange = 'week'): array
    {
        $now       = $this->now();
        $startDate = $this->getStartDate($timeRange, $now)->startOfDay();

        $appointments = Appointment::query()
            ->with('schedule')
            ->whereBetween('created_at', [$startDate, $now])
            ->whereIn('status', ['Hoàn thành', 'Đang khám'])
            ->get();

        $avgWaitTime        = 0;
        $avgExaminationTime = 0;

        if ($appointments->count() > 0) {
            // Thời gian chờ ước tính = (số thứ tự - 1) × slot_duration
            $waitTimes = $appointments
                ->filter(fn($apt) => $apt->queue_number && $apt->schedule?->slot_duration)
                ->map(fn($apt) => max(0, ($apt->queue_number - 1) * $apt->schedule->slot_duration));

            if ($waitTimes->count() > 0) {
                $avgWaitTime = round($waitTimes->avg(), 0);
            }

            $durations = $appointments
                ->filter(fn($apt) => $apt->schedule?->slot_duration)
                ->map(fn($apt) => $apt->schedule->slot_duration);

            $avgExaminationTime = $durations->count() > 0 ? round($durations->avg(), 0) : 20;
        }

        $reviews     = Review::whereBetween('created_at', [$startDate, $now])->get();
        $avgRating   = 0;
        $reviewCount = 0;
        $reviewRate  = 0;

        if ($reviews->count() > 0) {
            $avgRating   = round($reviews->avg('rating'), 1);
            $reviewCount = $reviews->count();

            $completedCount = Appointment::whereBetween('created_at', [$startDate, $now])
                ->where('status', 'Hoàn thành')
                ->count();

            $reviewRate = $completedCount > 0 ? round(($reviewCount / $completedCount) * 100, 0) : 0;
        }

        return [
            'avg_wait_time'        => $avgWaitTime,
            'avg_examination_time' => $avgExaminationTime,
            'avg_rating'           => $avgRating,
            'review_count'         => $reviewCount,
            'review_rate'          => $reviewRate,
        ];
    }

    /**
     * Lấy dữ liệu biểu đồ lịch hẹn theo ngày
     *
     * FIX: Dùng created_at GROUP BY để thấy ngay appointment vừa tạo,
     * thay vì appointment_time (giờ khám có thể là tương lai)
     * FIX: 1 query duy nhất, không vòng lặp N×3
     */
    public function getDailyAppointmentsData(?string $timeRange = 'week'): array
    {
        $days      = $timeRange === 'week' ? 7 : 30;
        $now       = $this->now();
        $startDate = $now->copy()->subDays($days)->startOfDay();
        // Nhúng offset trực tiếp (không dùng binding) để tránh lỗi only_full_group_by
        $tz  = $this->tzOffsetString(); // vd: '+07:00'
        $expr = "DATE(CONVERT_TZ(created_at, '+00:00', '{$tz}'))";

        $rawResults = Appointment::query()
            ->whereBetween('created_at', [$startDate, $now->copy()->endOfDay()])
            ->selectRaw("{$expr} as date, status, COUNT(*) as count")
            ->groupByRaw("{$expr}, status")
            ->get()
            ->groupBy('date');

        $data   = [];
        $labels = [];

        for ($i = $days; $i >= 0; $i--) {
            $date     = $now->copy()->subDays($i);
            $dateStr  = $date->toDateString();
            $labels[] = $date->format('d/m');

            $dayData   = $rawResults->get($dateStr, collect());
            $total     = $dayData->sum('count');
            $completed = $dayData->where('status', 'Hoàn thành')->sum('count');
            $cancelled = $dayData->where('status', 'Đã hủy')->sum('count');

            $data[] = compact('total', 'completed', 'cancelled');
        }

        return compact('labels', 'data');
    }

    /**
     * Phân bố theo chuyên khoa
     * FIX: lọc bỏ null department
     */
    public function getSpecialtyDistribution(?string $timeRange = 'week'): array
    {
        $now       = $this->now();
        $startDate = $this->getStartDate($timeRange, $now)->startOfDay();

        $specialties = Appointment::query()
            ->with('schedule.doctor.department')
            ->whereBetween('created_at', [$startDate, $now])
            ->get()
            ->groupBy(fn($apt) => optional(optional(optional($apt->schedule)->doctor)->department)->department_name)
            ->filter(fn($group, $key) => !empty($key))
            ->map(fn($group) => $group->count())
            ->toArray();

        return [
            'labels' => array_keys($specialties),
            'data'   => array_values($specialties),
        ];
    }

    /**
     * Phân bố trạng thái lịch hẹn
     */
    public function getStatusDistribution(?string $timeRange = 'week'): array
    {
        $now       = $this->now();
        $startDate = $this->getStartDate($timeRange, $now)->startOfDay();

        $query = Appointment::query()->whereBetween('created_at', [$startDate, $now]);

        return [
            'labels' => ['Chờ xác nhận', 'Đã xác nhận', 'Đang khám', 'Hoàn thành', 'Đã hủy'],
            'data'   => [
                (clone $query)->where('status', 'Chờ xác nhận')->count(),
                (clone $query)->where('status', 'Đã xác nhận')->count(),
                (clone $query)->where('status', 'Đang khám')->count(),
                (clone $query)->where('status', 'Hoàn thành')->count(),
                (clone $query)->where('status', 'Đã hủy')->count(),
            ],
        ];
    }

    /**
     * Phân bố độ tuổi bệnh nhân
     * FIX: chỉ lấy role_id = 3
     */
    public function getAgeDistribution(): array
    {
        $users = User::where('role_id', 3)->get();

        $ranges = ['0-18' => 0, '19-35' => 0, '36-50' => 0, '51-65' => 0, '65+' => 0];

        foreach ($users as $user) {
            $age = $user->date_of_birth ? Carbon::parse($user->date_of_birth)->age : 30;

            if ($age <= 18)     $ranges['0-18']++;
            elseif ($age <= 35) $ranges['19-35']++;
            elseif ($age <= 50) $ranges['36-50']++;
            elseif ($age <= 65) $ranges['51-65']++;
            else                $ranges['65+']++;
        }

        return [
            'labels' => array_keys($ranges),
            'data'   => array_values($ranges),
        ];
    }

    /**
     * Top doctors theo rating
     */
    public function getTopDoctors(int $limit = 5): array
    {
        return Doctor::query()
            ->where('status', 1)
            ->with(['department:department_id,department_name', 'reviews:review_id,doctor_id,rating'])
            ->get()
            ->map(function ($doctor) {
                $reviews = $doctor->reviews;
                return [
                    'doctor_id'       => $doctor->doctor_id,
                    'full_name'       => $doctor->full_name,
                    'department_name' => $doctor->department?->department_name ?? 'N/A',
                    'experience'      => $doctor->experience ?? 0,
                    'rating'          => $reviews->count() > 0 ? round($reviews->avg('rating'), 1) : 0,
                    'review_count'    => $reviews->count(),
                    'avatar_url'      => $doctor->avatar_url,
                ];
            })
            ->sortByDesc('rating')
            ->take($limit)
            ->values()
            ->toArray();
    }

    /**
     * Doctor có lượt đặt nhiều nhất trong tuần
     */
    public function getTopDoctorThisWeek(): array
    {
        $now         = $this->now();
        $startOfWeek = $now->copy()->startOfWeek()->startOfDay();
        $endOfWeek   = $now->copy()->endOfWeek()->endOfDay();

        $topDoctor = Appointment::query()
            ->join('doctorschedules', 'appointments.schedule_id', '=', 'doctorschedules.schedule_id')
            ->join('doctors', 'doctorschedules.doctor_id', '=', 'doctors.doctor_id')
            ->whereBetween('appointments.created_at', [$startOfWeek, $endOfWeek])
            ->whereIn('appointments.status', ['Chờ xác nhận', 'Đã xác nhận', 'Đang khám', 'Hoàn thành'])
            ->select('doctors.doctor_id', 'doctors.full_name', 'doctors.avatar_url')
            ->selectRaw('COUNT(appointments.appointment_id) as appointment_count')
            ->groupBy('doctors.doctor_id', 'doctors.full_name', 'doctors.avatar_url')
            ->orderByDesc('appointment_count')
            ->first();

        return $topDoctor ? $topDoctor->toArray() : [];
    }

    /**
     * Xu hướng bệnh nhân mới vs quay lại
     * FIX: dùng created_at, tối ưu query
     */
    public function getPatientTypeTrend(?string $timeRange = 'week'): array
    {
        $days      = $timeRange === 'week' ? 7 : 30;
        $now       = $this->now();
        $startDate = $now->copy()->subDays($days)->startOfDay();

        // Users đã có appointment hoàn thành TRƯỚC khoảng này
        $existingPatients = DB::table('appointments')
            ->where('status', 'Hoàn thành')
            ->where('created_at', '<', $startDate)
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        // Appointment hoàn thành trong khoảng, GROUP BY date
        $tz2  = $this->tzOffsetString();
        $rawResults = DB::table('appointments')
            ->where('status', 'Hoàn thành')
            ->whereBetween('created_at', [$startDate, $now->copy()->endOfDay()])
            ->selectRaw("DATE(CONVERT_TZ(created_at, '+00:00', '{$tz2}')) as date, user_id")
            ->distinct()
            ->get()
            ->groupBy('date');

        $data   = [];
        $labels = [];

        for ($i = $days; $i >= 0; $i--) {
            $date     = $now->copy()->subDays($i);
            $dateStr  = $date->toDateString();
            $labels[] = $date->format('d/m');

            $dayUsers  = $rawResults->get($dateStr, collect())->pluck('user_id')->toArray();
            $returning = count(array_intersect($dayUsers, $existingPatients));
            $new       = count($dayUsers) - $returning;

            $data[] = ['new' => $new, 'returning' => $returning];
        }

        return compact('labels', 'data');
    }

    /**
     * Xu hướng mức độ hài lòng
     */
    public function getSatisfactionTrend(?string $timeRange = 'week'): array
    {
        $days      = $timeRange === 'week' ? 7 : 30;
        $now       = $this->now();
        $startDate = $now->copy()->subDays($days)->startOfDay();

        $tz3  = $this->tzOffsetString();
        $expr3 = "DATE(CONVERT_TZ(created_at, '+00:00', '{$tz3}'))";
        $rawResults = Review::query()
            ->whereBetween('created_at', [$startDate, $now->copy()->endOfDay()])
            ->selectRaw("{$expr3} as date, ROUND(AVG(rating), 1) as avg_rating")
            ->groupByRaw($expr3)
            ->pluck('avg_rating', 'date');

        $data   = [];
        $labels = [];

        for ($i = $days; $i >= 0; $i--) {
            $date     = $now->copy()->subDays($i);
            $labels[] = $date->format('d/m');
            $data[]   = (float) ($rawResults->get($date->toDateString()) ?? 0);
        }

        return compact('labels', 'data');
    }

    /**
     * Thời gian chờ theo chuyên khoa
     * FIX: dùng queue_number × slot_duration thay vì booking lead time
     */
    public function getWaitTimeBySpecialty(?string $timeRange = 'week'): array
    {
        $now       = $this->now();
        $startDate = $this->getStartDate($timeRange, $now)->startOfDay();

        $specialties = Appointment::query()
            ->join('doctorschedules', 'appointments.schedule_id', '=', 'doctorschedules.schedule_id')
            ->join('doctors', 'doctorschedules.doctor_id', '=', 'doctors.doctor_id')
            ->join('departments', 'doctors.department_id', '=', 'departments.department_id')
            ->whereBetween('appointments.created_at', [$startDate, $now])
            ->whereIn('appointments.status', ['Hoàn thành', 'Đang khám'])
            ->whereNotNull('appointments.queue_number')
            ->selectRaw(
                'departments.department_name,
                 ROUND(AVG(GREATEST(0, (appointments.queue_number - 1) * doctorschedules.slot_duration)), 0) as avg_wait_time'
            )
            ->groupBy('departments.department_id', 'departments.department_name')
            ->get()
            ->toArray();

        return [
            'specialties' => array_map(fn($s) => $s['department_name'], $specialties),
            'wait_times'  => array_map(fn($s) => (int) ($s['avg_wait_time'] ?? 0), $specialties),
        ];
    }

    /**
     * Mức độ hài lòng theo bác sĩ
     */
    public function getSatisfactionByDoctor(int $limit = 5): array
    {
        return Doctor::query()
            ->where('status', 1)
            ->with('reviews:review_id,doctor_id,rating')
            ->get()
            ->map(function ($doctor) {
                $reviews = $doctor->reviews;
                return [
                    'full_name' => $doctor->full_name,
                    'rating'    => $reviews->count() > 0 ? round($reviews->avg('rating'), 1) : 0,
                ];
            })
            ->sortByDesc('rating')
            ->take($limit)
            ->values()
            ->toArray();
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    /**
     * Carbon::now() theo timezone của app (Asia/Ho_Chi_Minh)
     * Đây là nguyên nhân chính khiến data không load đúng nếu server chạy UTC.
     */
    private function now(): Carbon
    {
        return Carbon::now(config('app.timezone', 'UTC'));
    }

    /**
     * Chuyển timezone string sang offset string cho CONVERT_TZ của MySQL.
     * Ví dụ: 'Asia/Ho_Chi_Minh' → '+07:00'
     */
    private function tzOffsetString(): string
    {
        $tz     = config('app.timezone', 'UTC');
        $offset = (new \DateTimeZone($tz))->getOffset(new \DateTime('now', new \DateTimeZone('UTC')));
        $hours  = intdiv(abs($offset), 3600);
        $mins   = (abs($offset) % 3600) / 60;

        return sprintf('%s%02d:%02d', $offset >= 0 ? '+' : '-', $hours, $mins);
    }

    /**
     * Helper: Xác định ngày bắt đầu dựa trên time range
     */
    private function getStartDate(string $timeRange, Carbon $now): Carbon
    {
        return match ($timeRange) {
            'month' => $now->copy()->subDays(30),
            'year'  => $now->copy()->subDays(365),
            default => $now->copy()->subDays(7),
        };
    }
}