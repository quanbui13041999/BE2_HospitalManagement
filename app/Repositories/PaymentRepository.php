<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentRepository
{
    /**
     * Lấy hóa đơn cùng chi tiết dịch vụ và giảm BHYT.
     */
    public function getInvoiceWithDetails(int $invoiceId): ?Invoice
    {
        return Invoice::with([
            'patient',
            'doctor',
            'items.service',
            'bhytDiscount',
        ])->find($invoiceId);
    }

    /**
     * Giao dịch gần đây (sidebar).
     */
    public function recentTransactions(int $limit = 5)
    {
        return Payment::with(['invoice.patient'])
            ->orderBy('payment_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Thống kê thu ngày hôm nay.
     */
    public function todayStats(): array
    {
        $payments = Payment::whereDate('payment_date', today())->get();

        $total     = $payments->sum('amount');
        $count     = $payments->count();
        $failed    = $payments->where('status', 'Thất bại')->count();
        $rate      = $count > 0 ? round(($count - $failed) / $count * 100) : 0;

        return [
            'total'   => $total,
            'count'   => $count,
            'failed'  => $failed,
            'rate'    => $rate,
        ];
    }

    /**
     * Tạo bản ghi thanh toán mới.
     */
    public function createPayment(array $data): Payment
    {
        return Payment::create([
            'invoice_id'     => $data['invoice_id'],
            'payment_method' => $data['payment_method'],
            'amount'         => $data['amount'],
            'status'         => 'Chờ xử lý',
            'payment_date'        => now(),
            'transaction_ref'=> $data['transaction_ref'] ?? null,
        ]);
    }

    /**
     * Cập nhật trạng thái giao dịch (callback từ cổng thanh toán).
     */
    public function updateStatus(int $paymentId, string $status, ?string $ref = null): bool
    {
        return (bool) Payment::where('payment_id', $paymentId)->update([
            'status'          => $status,
            'transaction_ref' => $ref ?? DB::raw('transaction_ref'),
        ]);
    }

    /**
     * Danh sách giao dịch phân trang (trang quản lý).
     */
    public function paginatedPayments(array $filters, int $perPage = 20)
    {
        $query = Payment::with(['invoice.patient'])
            ->orderBy('payment_date', 'desc');

        if (!empty($filters['from_date'])) {
            $query->whereDate('payment_date', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('payment_date', '<=', $filters['to_date']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['method'])) {
            $query->where('payment_method', $filters['method']);
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
