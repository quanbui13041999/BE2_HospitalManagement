<?php

namespace App\Services\Admin;

use App\Models\BhytCard;
use App\Models\Payment;
use App\Repositories\BhytRepository;

class BhytService
{
    // Ngưỡng cảnh báo hết hạn (ngày)
    const EXPIRY_WARNING_DAYS = 60;
    const EXPIRY_DANGER_DAYS  = 30;

    public function __construct(protected BhytRepository $repo) {}

    // ----------------------------------------------------------------
    // Data builders cho view
    // ----------------------------------------------------------------

    public function buildIndexData(): array
    {
        return [
            'expiringSoon' => $this->repo->expiringSoon(self::EXPIRY_WARNING_DAYS),
        ];
    }

    // ----------------------------------------------------------------
    // Tra cứu thẻ BHYT
    // ----------------------------------------------------------------

    /**
     * Tra cứu thẻ BHYT và lấy hóa đơn đang chờ của bệnh nhân.
     * Trả về null nếu không tìm thấy.
     */
    public function lookup(string $cardNumber): ?array
    {
        $card = $this->repo->findByCardNumber($cardNumber);

        if (!$card) {
            return null;
        }

        $daysLeft      = $this->repo->daysRemaining($card);
        $pendingInvoice = $this->repo->pendingInvoice($card->patient_id);

        return [
            'card'           => $card,
            'days_remaining' => $daysLeft,
            'expiry_status'  => $this->classifyExpiry($daysLeft),
            'pending_invoice'=> $pendingInvoice,
        ];
    }

    // ----------------------------------------------------------------
    // Áp dụng BHYT vào hóa đơn
    // ----------------------------------------------------------------

    /**
     * Tính và áp dụng BHYT vào hóa đơn cụ thể.
     * Trả về mảng chi tiết giảm giá, hoặc null nếu không hợp lệ.
     */
    public function applyToInvoice(int $invoiceId, string $cardNumber): ?array
    {
        $card = $this->repo->findByCardNumber($cardNumber);

        if (!$card || $card->status !== 'Còn hạn') {
            return null;
        }

        $payment = Payment::with('appointment')->find($invoiceId);

        if (!$payment || $payment->appointment->user_id !== $card->patient_id) {
            return null;
        }

        if ($payment->insurance_id !== null) {
            // Đã áp dụng rồi, trả về thông tin hiện tại
            return [
                'already_applied' => true,
                'coverage_rate'   => $card->coverage_rate,
                'bhyt_amount'     => $payment->discount_amount,
                'patient_pays'    => $payment->total_amount,
            ];
        }

        return $this->repo->applyBhytToInvoice($payment, $card);
    }

    // ----------------------------------------------------------------
    // Helper
    // ----------------------------------------------------------------

    /**
     * Phân loại trạng thái hết hạn thẻ.
     */
    private function classifyExpiry(int $daysLeft): string
    {
        if ($daysLeft <= 0)                            return 'expired';
        if ($daysLeft <= self::EXPIRY_DANGER_DAYS)     return 'danger';
        if ($daysLeft <= self::EXPIRY_WARNING_DAYS)    return 'warning';
        return 'ok';
    }
}
