<?php

namespace App\Services\Admin;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RevenueService
{
    /**
     * Get all statistics for the revenue dashboard based on filters.
     *
     * @param int $year
     * @param int|null $month
     * @param string|null $method
     * @return array
     */
    public function getDashboardStatistics(int $year, ?int $month = null, ?string $method = null): array
    {
        // 1. Get all unique payment methods available in the database
        $availableMethods = Payment::whereNotNull('method')
            ->where('method', '!=', '')
            ->distinct()
            ->pluck('method')
            ->toArray();

        // 2. Base query for successful payments
        $baseQuery = Payment::whereIn('status', ['Thành công', 'Đã thanh toán']);

        // Apply filters to current period
        $currentQuery = (clone $baseQuery)->whereYear('payment_date', $year);
        if ($month !== null) {
            $currentQuery->whereMonth('payment_date', $month);
        }
        if ($method !== null) {
            $currentQuery->where('method', $method);
        }

        $totalRevenue = (clone $currentQuery)->sum('total_amount');
        $totalTransactions = (clone $currentQuery)->count();

        // 3. Compute Growth Rate (Comparison)
        $previousRevenue = 0;
        if ($month !== null) {
            // Compare MoM (Month-on-Month)
            $prevMonth = $month === 1 ? 12 : $month - 1;
            $prevYear = $month === 1 ? $year - 1 : $year;

            $prevQuery = (clone $baseQuery)->whereYear('payment_date', $prevYear)->whereMonth('payment_date', $prevMonth);
            if ($method !== null) {
                $prevQuery->where('method', $method);
            }
            $previousRevenue = $prevQuery->sum('total_amount');
        } else {
            // Compare YoY (Year-on-Year)
            $prevYear = $year - 1;
            $prevQuery = (clone $baseQuery)->whereYear('payment_date', $prevYear);
            if ($method !== null) {
                $prevQuery->where('method', $method);
            }
            $previousRevenue = $prevQuery->sum('total_amount');
        }

        $growthRate = 0;
        if ($previousRevenue > 0) {
            $growthRate = round((($totalRevenue - $previousRevenue) / $previousRevenue) * 100, 1);
        } elseif ($totalRevenue > 0) {
            $growthRate = 100;
        }

        // 4. Monthly Trend Data (always 12 months for the line chart, regardless of selected month filter to keep visualization rich)
        $trendQuery = (clone $baseQuery)->whereYear('payment_date', $year);
        if ($method !== null) {
            $trendQuery->where('method', $method);
        }
        $revenueByMonth = $trendQuery
            ->selectRaw('MONTH(payment_date) as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[] = $revenueByMonth[$i] ?? 0;
        }

        // 5. Payment Methods Breakdown (pie/doughnut chart)
        $methodsBreakdownQuery = (clone $baseQuery)->whereYear('payment_date', $year);
        if ($month !== null) {
            $methodsBreakdownQuery->whereMonth('payment_date', $month);
        }
        $methods = $methodsBreakdownQuery
            ->selectRaw('method, COUNT(*) as count')
            ->groupBy('method')
            ->pluck('count', 'method')
            ->toArray();

        // 6. Department-level Revenue Breakdown
        $depRevenueQuery = DB::table('payments as p')
            ->join('appointments as a', 'p.appointment_id', '=', 'a.appointment_id')
            ->join('doctorschedules as ds', 'a.schedule_id', '=', 'ds.schedule_id')
            ->join('doctors as d', 'ds.doctor_id', '=', 'd.doctor_id')
            ->join('departments as dep', 'd.department_id', '=', 'dep.department_id')
            ->whereIn('p.status', ['Thành công', 'Đã thanh toán'])
            ->whereYear('p.payment_date', $year);

        if ($month !== null) {
            $depRevenueQuery->whereMonth('p.payment_date', $month);
        }
        if ($method !== null) {
            $depRevenueQuery->where('p.method', $method);
        }

        $departmentRevenue = $depRevenueQuery
            ->select('dep.department_name', DB::raw('SUM(p.total_amount) as total'))
            ->groupBy('dep.department_name')
            ->orderByDesc('total')
            ->get();

        // 7. Recent Transactions (applying the selected filters to let them audit recent payments)
        $recentPaymentsQuery = Payment::with(['appointment.user'])
            ->whereYear('payment_date', $year);

        if ($month !== null) {
            $recentPaymentsQuery->whereMonth('payment_date', $month);
        }
        if ($method !== null) {
            $recentPaymentsQuery->where('method', $method);
        }

        $recentPayments = $recentPaymentsQuery
            ->orderBy('payment_date', 'desc')
            ->limit(25)
            ->get();

        return [
            'year' => $year,
            'month' => $month,
            'selectedMethod' => $method,
            'availableMethods' => $availableMethods,
            'totalRevenue' => $totalRevenue,
            'totalTransactions' => $totalTransactions,
            'growthRate' => $growthRate,
            'previousRevenue' => $previousRevenue,
            'monthlyData' => $monthlyData,
            'methods' => $methods,
            'departmentRevenue' => $departmentRevenue,
            'recentPayments' => $recentPayments,
        ];
    }
}
