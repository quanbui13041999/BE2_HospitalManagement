<?php
namespace App\Services;

use App\Models\{TreatmentReminder, TreatmentConfirmation, User};
use Illuminate\Support\Facades\DB;

class ComplianceReportService
{
    /**
     * Tính toán báo cáo tổng hợp cho Admin.
     */
    public function getOverallReport(int $month, int $year): array
    {
        $totalPatients = User::where('role_id', 3)->count();
        
        $totalReminders = TreatmentReminder::whereMonth('remind_at', $month)
            ->whereYear('remind_at', $year)
            ->count();
            
        $totalConfirmed = TreatmentConfirmation::whereMonth('confirmed_at', $month)
            ->whereYear('confirmed_at', $year)
            ->count();
            
        $sentReminders = TreatmentReminder::whereMonth('remind_at', $month)
            ->whereYear('remind_at', $year)
            ->where('is_sent', 1)
            ->count();

        // Tỷ lệ trung bình toàn hệ thống
        $overallRate = $totalReminders > 0 ? round(($totalConfirmed / $totalReminders) * 100, 1) : 0;

        // Top 5 bệnh nhân tuân thủ cao nhất
        $topCompliant = User::where('role_id', 3)
            ->withCount(['treatmentReminders as total' => function($q) use ($month, $year) {
                $q->whereMonth('remind_at', $month)->whereYear('remind_at', $year);
            }])
            ->withCount(['treatmentConfirmations as confirmed' => function($q) use ($month, $year) {
                $q->whereMonth('confirmed_at', $month)->whereYear('confirmed_at', $year);
            }])
            ->get()
            ->filter(fn($u) => $u->total > 0)
            ->map(function($u) {
                $u->rate = round(($u->confirmed / $u->total) * 100, 1);
                return $u;
            })
            ->sortByDesc('rate')
            ->take(5);

        // Top 5 bệnh nhân tuân thủ thấp nhất
        $leastCompliant = User::where('role_id', 3)
            ->withCount(['treatmentReminders as total' => function($q) use ($month, $year) {
                $q->whereMonth('remind_at', $month)->whereYear('remind_at', $year);
            }])
            ->withCount(['treatmentConfirmations as confirmed' => function($q) use ($month, $year) {
                $q->whereMonth('confirmed_at', $month)->whereYear('confirmed_at', $year);
            }])
            ->get()
            ->filter(fn($u) => $u->total > 0)
            ->map(function($u) {
                $u->rate = round(($u->confirmed / $u->total) * 100, 1);
                return $u;
            })
            ->sortBy('rate')
            ->take(5);

        return [
            'total_patients' => $totalPatients,
            'total_reminders' => $totalReminders,
            'total_confirmed' => $totalConfirmed,
            'sent_reminders' => $sentReminders,
            'overall_rate' => $overallRate,
            'top_compliant' => $topCompliant,
            'least_compliant' => $leastCompliant,
            'month' => $month,
            'year' => $year
        ];
    }
}
