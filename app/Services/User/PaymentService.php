<?php

namespace App\Services\User;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Repositories\PaymentRepository;
use App\Services\ActivityLogService;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(protected PaymentRepository $repo) {}

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
        $doctorFee   = (float) ($appointment->schedule->doctor->price ?? 0);
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
        $doctorFee  = (float) ($appointment->schedule->doctor->price ?? 0);
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

        // Tạo bản ghi payment
        $payment = Payment::create([
            'appointment_id'  => $appointmentId,
            'insurance_id'    => $insurance?->insurance_id,
            'membership_id'   => $membership?->card_id,
            'subtotal'        => $subtotal,
            'discount_amount' => $discountAmount,
            'total_amount'    => $totalAmount,
            'method'          => $method,
            'status'          => 'Chờ thanh toán',
            'transaction_ref' => $ref,
            'payment_date'    => now(),
        ]);

        // Tạo payment items
        if ($doctorFee > 0) {
            PaymentItem::create([
                'payment_id'  => $payment->payment_id,
                'item_type'   => 'Khám bệnh',
                'item_name'   => 'Phí khám - BS. ' . ($appointment->schedule->doctor->full_name ?? ''),
                'quantity'    => 1,
                'unit_price'  => $doctorFee,
                'total_price' => $doctorFee,
            ]);
        }

        if ($serviceFee > 0) {
            PaymentItem::create([
                'payment_id'  => $payment->payment_id,
                'item_type'   => 'Dịch vụ',
                'item_name'   => $appointment->service->service_name ?? 'Dịch vụ',
                'quantity'    => 1,
                'unit_price'  => $serviceFee,
                'total_price' => $serviceFee,
            ]);
        }

        $result = ['payment' => $payment, 'ref' => $ref];

        if ($method === 'QR') {
            $result['qr_content'] = sprintf(
                'HOSPITAL|%s|%d|Thanh toan lich kham %s',
                $ref,
                (int) $totalAmount,
                $appointmentId
            );
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
            // Đánh dấu lịch hẹn và hóa đơn đã thanh toán
            $payment = Payment::with(['appointment', 'invoice'])->find($paymentId);
            if ($payment) {
                if ($payment->appointment) {
                    $payment->appointment->update(['status' => 'Đã thanh toán']);
                }
                if ($payment->invoice) {
                    $payment->invoice->update(['status' => 'Đã thanh toán']);
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
     * Lịch sử thanh toán của user.
     */
    public function getUserPayments(int $userId)
    {
        return Payment::whereHas('appointment', fn($q) => $q->where('user_id', $userId))
            ->with(['appointment.schedule.doctor'])
            ->orderByDesc('payment_date')
            ->paginate(10);
    }
}
