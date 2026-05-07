<?php

namespace App\Repositories;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentRepository
{
    /**
     * Giao dịch gần đây (sidebar).
     */
    public function recentTransactions(int $limit = 5)
    {
        return Payment::with(['appointment.user'])
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

        $total     = $payments->sum('total_amount');
        $count     = $payments->count();
        $failed    = $payments->where('status', 'Thất bại')->count();
        $pending   = $payments->where('status', 'Chờ xử lý')->count();
        $success   = $payments->whereIn('status', ['Thành công', 'Đã thanh toán'])->count();
        $rate      = $count > 0 ? round($success / $count * 100) : 0;

        return [
            'total'   => $total,
            'count'   => $count,
            'success' => $success,
            'failed'  => $failed,
            'pending' => $pending,
            'rate'    => $rate,
        ];
    }

    /**
     * Tạo bản ghi thanh toán mới.
     */
    public function createPayment(array $data): Payment
    {
        return Payment::create([
            'appointment_id'   => $data['appointment_id'],
            'method'           => $data['payment_method'],
            'total_amount'     => $data['amount'],
            'subtotal'         => $data['amount'] ?? 0,
            'discount_amount'  => $data['discount_amount'] ?? 0,
            'status'           => 'Chờ xử lý',
            'payment_date'     => now(),
            'transaction_ref'  => $data['transaction_ref'] ?? null,
        ]);
    }

    /**
     * Cập nhật trạng thái giao dịch thành công.
     */
    public function confirmPayment(int $paymentId, ?string $ref = null): bool
    {
        $data = [
            'status' => 'Thành công',
            'payment_date' => now(),
        ];
        
        if ($ref) {
            $data['transaction_ref'] = $ref;
        }
        
        $updated = Payment::where('payment_id', $paymentId)->update($data);
        
        Log::info('Payment confirmed', [
            'payment_id' => $paymentId,
            'ref' => $ref,
            'updated' => $updated
        ]);
        
        return (bool) $updated;
    }

    /**
     * Cập nhật trạng thái giao dịch thất bại.
     */
    public function failPayment(int $paymentId): bool
    {
        Log::info('Payment failed', ['payment_id' => $paymentId]);
        
        return (bool) Payment::where('payment_id', $paymentId)->update([
            'status' => 'Thất bại',
            'payment_date' => now(),
        ]);
    }

    /**
     * Cập nhật trạng thái giao dịch (callback từ cổng thanh toán) - Giữ lại cho backward compatibility
     */
    public function updateStatus(int $paymentId, string $status, ?string $ref = null): bool
    {
        $data = ['status' => $status];
        
        if ($ref) {
            $data['transaction_ref'] = $ref;
        }
        
        return (bool) Payment::where('payment_id', $paymentId)->update($data);
    }

    /**
     * Danh sách giao dịch phân trang (trang quản lý) - QUAN TRỌNG: ĐÃ SỬA
     */
    public function paginatedPayments(array $filters, int $perPage = 20)
    {
        $query = Payment::with(['appointment.user', 'appointment.schedule.doctor'])
            ->orderBy('payment_date', 'desc');

        // Lọc theo ngày
        if (!empty($filters['from_date'])) {
            $query->whereDate('payment_date', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('payment_date', '<=', $filters['to_date']);
        }
        
        // QUAN TRỌNG: Sửa phần lọc status để bao gồm cả 'Thành công' và 'Đã thanh toán'
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'Thành công') {
                // Lấy cả status 'Thành công' và 'Đã thanh toán'
                $query->whereIn('status', ['Thành công', 'Đã thanh toán']);
            } else {
                $query->where('status', $filters['status']);
            }
        }
        
        // Lọc theo phương thức
        if (!empty($filters['method'])) {
            $query->where('method', $filters['method']);
        }

        // Debug log
        Log::info('Paginated payments query', [
            'filters' => $filters,
            'sql' => $query->toSql(),
            'total' => $query->count()
        ]);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Lấy tất cả payments không filter (cho debug)
     */
    public function getAllPayments()
    {
        return Payment::with(['appointment.user', 'appointment.schedule.doctor'])
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    /**
     * Lấy invoice với details (cần implement nếu chưa có)
     */
    public function getInvoiceWithDetails(int $invoiceId)
    {
        return \App\Models\Invoice::with(['patient', 'doctor', 'items'])
            ->find($invoiceId);
    }
}