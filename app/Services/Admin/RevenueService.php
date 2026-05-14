<?php

namespace App\Services\Admin;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class RevenueService
{
    /**
     * Get all statistics for the revenue dashboard based on the specified year.
     *
     * @param int $year
     * @return array
     */
    public function getDashboardStatistics(int $year): array
    {
        // Khởi tạo query cho các giao dịch thành công
        $successfulPayments = Payment::whereIn('status', ['Thành công', 'Đã thanh toán']);

        // Tổng quan doanh thu
        $totalRevenue = (clone $successfulPayments)->sum('total_amount');
        $totalTransactions = (clone $successfulPayments)->count();

        // Biểu đồ doanh thu theo tháng trong năm
        $revenueByMonth = (clone $successfulPayments)
            ->whereYear('payment_date', $year)
            ->selectRaw('MONTH(payment_date) as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Chuẩn hóa dữ liệu mảng 12 tháng
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[] = $revenueByMonth[$i] ?? 0;
        }

        // Tỷ lệ phương thức thanh toán
        $methods = (clone $successfulPayments)
            ->selectRaw('method, COUNT(*) as count')
            ->groupBy('method')
            ->pluck('count', 'method')
            ->toArray();

        // Giao dịch gần đây
        $recentPayments = Payment::with(['appointment.user'])
            ->orderBy('payment_date', 'desc')
            ->limit(10)
            ->get();

        return [
            'year' => $year,
            'totalRevenue' => $totalRevenue,
            'totalTransactions' => $totalTransactions,
            'monthlyData' => $monthlyData,
            'methods' => $methods,
            'recentPayments' => $recentPayments,
        ];
    }
}
