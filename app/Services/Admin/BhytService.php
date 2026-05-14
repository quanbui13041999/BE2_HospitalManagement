<?php

namespace App\Services\Admin;

use App\Models\BhytCard;
use App\Models\Invoice;
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

        $invoice = Invoice::find($invoiceId);

        if (!$invoice || $invoice->patient_id !== $card->patient_id) {
            return null;
        }

        if ($invoice->bhyt_applied) {
            // Đã áp dụng rồi, trả về thông tin hiện tại
            return [
                'already_applied' => true,
                'coverage_rate'   => $invoice->bhyt_coverage,
                'bhyt_amount'     => $invoice->bhyt_amount,
                'patient_pays'    => $invoice->total_amount,
            ];
        }

        return $this->repo->applyBhytToInvoice($invoice, $card->coverage_rate);
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
