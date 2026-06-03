<?php

namespace App\Services\User;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Repositories\PaymentRepository;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        protected PaymentRepository $repo,
        protected NotificationService $notifications,
        protected \App\Services\PayOsService $payOsService
    ) {}

    /**
     * Xây dựng dữ liệu trang thanh toán cho user.
     */
    public function buildPaymentPage(int $appointmentId, int $userId): array
    {
        $appointment = Appointment::with([
            'schedule.doctor.department',
            'service',
            'user',
        ])->where('appointment_id', $appointmentId)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Kiểm tra đã có payment chưa
        $existing = Payment::where('appointment_id', $appointmentId)->first();

        // Tính tổng tiền: giá bác sĩ + dịch vụ (nếu có)
        $doctorFee   = (float) ($appointment->schedule?->doctor?->price ?? 0);
        $serviceFee  = 0;

        if ($appointment->service) {
            $serviceFee = (float) ($appointment->service->latestPrice->price ?? 0);
        }

        $subtotal = $doctorFee + $serviceFee;

        // Giảm giá bảo hiểm (nếu có - lấy từ user)
        $insuranceDiscount = 0;
        $insurance = $appointment->user->insuranceCards()
            ->where('status', 'Còn hạn')
            ->where('expiry_date', '>=', now())
            ->first();

        if ($insurance) {
            $insuranceDiscount = round($subtotal * $insurance->discount_pct / 100, 2);
        }

        // Giảm membership
        $membershipDiscount = 0;
        $membership = $appointment->user->membershipCard ?? null;
        if ($membership && $membership->status == 1) {
            $membershipDiscount = round($subtotal * $membership->discount_pct / 100, 2);
        }

        $discountAmount = $insuranceDiscount + $membershipDiscount;
        $totalAmount    = max(0, $subtotal - $discountAmount);

        return [
            'appointment'       => $appointment,
            'existing'          => $existing,
            'doctorFee'         => $doctorFee,
            'serviceFee'        => $serviceFee,
            'subtotal'          => $subtotal,
            'insurance'         => $insurance,
            'membership'        => $membership,
            'insuranceDiscount' => $insuranceDiscount,
            'membershipDiscount' => $membershipDiscount,
            'discountAmount'    => $discountAmount,
            'totalAmount'       => $totalAmount,
            'paymentMethods'    => ['QR', 'ATM', 'MoMo', 'ZaloPay', 'Counter'],
        ];
    }

    /**
     * Khởi tạo giao dịch thanh toán.
     */
    public function initiatePayment(int $appointmentId, string $method, int $userId): array
    {
        $appointment = Appointment::with([
            'schedule.doctor',
            'service',
            'user.insuranceCards',
            'user.membershipCard',
        ])->where('appointment_id', $appointmentId)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Tính lại giá
        $doctorFee  = (float) ($appointment->schedule?->doctor?->price ?? 0);
        $serviceFee = 0;
        if ($appointment->service) {
            $serviceFee = (float) ($appointment->service->latestPrice->price ?? 0);
        }
        $subtotal = $doctorFee + $serviceFee;

        $insurance = $appointment->user->insuranceCards
            ->where('status', 'Còn hạn')
            ->first();
        $insuranceDiscount = $insurance
            ? round($subtotal * $insurance->discount_pct / 100, 2)
            : 0;

        $membership = $appointment->user->membershipCard ?? null;
        $membershipDiscount = ($membership && $membership->status == 1)
            ? round($subtotal * $membership->discount_pct / 100, 2)
            : 0;

        $discountAmount = $insuranceDiscount + $membershipDiscount;
        $totalAmount    = max(0, $subtotal - $discountAmount);

        $ref = 'PAY-' . strtoupper(Str::random(10));

        // Tái sử dụng bản ghi cũ nếu đã có (tránh lỗi Unique Constraint trên appointment_id)
        $payment = Payment::where('appointment_id', $appointmentId)->first();
        
        $paymentData = [
            'insurance_id'    => $insurance?->insurance_id,
            'membership_id'   => $membership?->card_id,
            'subtotal'        => $subtotal,
            'discount_amount' => $discountAmount,
            'total_amount'    => $totalAmount,
            'method'          => $method,
            'status'          => 'Chờ thanh toán',
            'transaction_ref' => $ref,
            'payment_date'    => now(),
        ];

        if ($payment) {
            $payment->update($paymentData);
            // Xóa các items cũ để nạp lại mới
            $payment->items()->delete();
        } else {
            $payment = Payment::create(array_merge([
                'appointment_id' => $appointmentId,
            ], $paymentData));
        }

        $this->notifications->createForUser(
            $userId,
            'Đã tạo yêu cầu thanh toán',
            'Hóa đơn cho lịch khám #' . $appointmentId . ' có số tiền ' . number_format($totalAmount, 0, ',', '.') . 'đ đang chờ thanh toán.',
            'payment_created',
            'payment',
            $payment->payment_id
        );

        // Tạo payment items
        if ($doctorFee > 0) {
            PaymentItem::create([
                'payment_id'  => $payment->payment_id,
                'item_name'   => 'Phí khám - BS. ' . ($appointment->schedule?->doctor?->full_name ?? ''),
                'quantity'    => 1,
                'unit_price'  => $doctorFee,
                'subtotal'    => $doctorFee,
            ]);
        }

        if ($serviceFee > 0) {
            PaymentItem::create([
                'payment_id'  => $payment->payment_id,
                'item_name'   => $appointment->service->service_name ?? 'Dịch vụ',
                'quantity'    => 1,
                'unit_price'  => $serviceFee,
                'subtotal'    => $serviceFee,
            ]);
        }

        $result = ['payment' => $payment, 'ref' => $ref];

        if ($method === 'QR') {
            // Gọi API PayOS thực tế để sinh mã VietQR động
            $payOsResult = $this->payOsService->createPaymentLink(
                $payment->payment_id,
                (int) $totalAmount,
                "Thanh toan lich kham {$appointmentId}",
                route('user.payments.success', $payment->payment_id),
                route('user.payments.show', $appointmentId)
            );

            if ($payOsResult['success']) {
                // Lưu payment link id từ PayOS vào trường transaction_ref để đối soát webhook
                $payment->update([
                    'transaction_ref' => ($payOsResult['paymentLinkId'] ?? null) ?: $ref,
                    'checkout_url' => $payOsResult['checkoutUrl'] ?? null,
                    'qr_content' => $payOsResult['qrContent'] ?? null,
                ]);
                
                $result['qr_content'] = $payOsResult['qrContent'];
                $result['checkout_url'] = $payOsResult['checkoutUrl'] ?? null;
            } else {
                // Fallback nếu API PayOS bị gián đoạn kết nối
                $result['qr_content'] = sprintf(
                    'HOSPITAL|%s|%d|Thanh toan lich kham %s',
                    $ref,
                    (int) $totalAmount,
                    $appointmentId
                );
            }
        }

        return $result;
    }

    /**
     * Xác nhận thanh toán thành công.
     */
    public function confirmPayment(int $paymentId, ?string $ref = null): bool
    {
        // SỬ DỤNG REPOSITORY THAY VÌ UPDATE TRỰC TIẾP
        $updated = $this->repo->confirmPayment($paymentId, $ref);
        
        if ($updated) {
            // Đánh dấu lịch hẹn đã thanh toán
            $payment = Payment::with(['appointment'])->find($paymentId);
            if ($payment) {
                if ($payment->appointment) {
                    $payment->appointment->update(['status' => 'Đã thanh toán']);
                    $this->notifications->createForUser(
                        $payment->appointment->user_id,
                        'Thanh toán thành công',
                        'Giao dịch #' . $payment->payment_id . ' đã được xác nhận thanh toán.',
                        'payment_paid',
                        'payment',
                        $payment->payment_id
                    );
                }

                ActivityLogService::log(
                    'Thanh toán lịch khám',
                    'Bệnh nhân đã thanh toán lịch khám #' . $payment->appointment_id . ' với số tiền ' . number_format((float) $payment->total_amount, 0, ',', '.') . 'đ.',
                    'payment',
                    $payment->payment_id,
                    [
                        'appointment_id' => $payment->appointment_id,
                        'method' => $payment->method,
                        'amount' => $payment->total_amount,
                        'transaction_ref' => $payment->transaction_ref,
                    ]
                );
            }
        }
        
        return $updated;
    }

    /**
     * Đánh dấu thất bại.
     */
    public function failPayment(int $paymentId): bool
    {
        // SỬ DỤNG REPOSITORY
        return $this->repo->failPayment($paymentId);
    }

    /**
     * Lấy payment theo ID.
     */
    public function getPayment(int $paymentId): Payment
    {
        return Payment::with(['appointment.schedule.doctor', 'appointment.user', 'items'])
            ->findOrFail($paymentId);
    }

    /**
     * Lịch sử thanh toán của user với bộ lọc nâng cao.
     */
    public function getUserPayments(int $userId, array $filters = [])
    {
        $query = Payment::whereHas('appointment', fn($q) => $q->where('user_id', $userId))
            ->with(['appointment.schedule.doctor', 'appointment.service'])
            ->orderByDesc('payment_date');

        // Lọc theo ngày
        if (!empty($filters['from_date'])) {
            $query->whereDate('payment_date', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('payment_date', '<=', $filters['to_date']);
        }

        // Lọc theo trạng thái
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'Thành công') {
                $query->whereIn('status', ['Thành công', 'Đã thanh toán']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        // Lọc theo phương thức
        if (!empty($filters['method'])) {
            $query->where('method', $filters['method']);
        }

        // Tìm kiếm từ khóa (Tên bác sĩ, tên dịch vụ hoặc mã GD)
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('transaction_ref', 'like', $search)
                  ->orWhereHas('appointment.schedule.doctor', function ($dq) use ($search) {
                      $dq->where('full_name', 'like', $search);
                  })
                  ->orWhereHas('appointment.service', function ($sq) use ($search) {
                      $sq->where('service_name', 'like', $search);
                  });
            });
        }

        return $query->paginate(10)->withQueryString();
    }

    /**
     * Thống kê thanh toán của user.
     */
    public function getUserPaymentStats(int $userId): array
    {
        $allPayments = Payment::whereHas('appointment', fn($q) => $q->where('user_id', $userId))->get();
        
        $totalSpent = $allPayments->whereIn('status', ['Thành công', 'Đã thanh toán'])->sum('total_amount');
        $completedCount = $allPayments->whereIn('status', ['Thành công', 'Đã thanh toán'])->count();
        $pendingCount = $allPayments->whereIn('status', ['Chờ xử lý', 'Chờ thanh toán', 'Chưa thanh toán'])->count();
        
        return [
            'total_spent' => $totalSpent,
            'completed_count' => $completedCount,
            'pending_count' => $pendingCount,
        ];
    }

    /**
     * Kiểm tra xem PayOS đã được cấu hình API thực tế chưa.
     */
    public function isPayOsConfigured(): bool
    {
        return $this->payOsService->isConfigured();
    }
}
