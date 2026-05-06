<?php

namespace App\Repositories;

use App\Models\BhytCard;
use App\Models\Invoice;
use Illuminate\Support\Collection;

class BhytRepository
{
    /**
     * Tra cứu thẻ BHYT theo mã thẻ.
     */
    public function findByCardNumber(string $cardNumber): ?BhytCard
    {
        return BhytCard::with('patient')
            ->where('card_number', strtoupper($cardNumber))
            ->first();
    }

    /**
     * Danh sách thẻ sắp hết hạn (trong N ngày tới).
     */
    public function expiringSoon(int $days = 60): Collection
    {
        return BhytCard::with('patient')
            ->where('status', 'Còn hạn')
            ->where('expiry_date', '<=', now()->addDays($days)->toDateString())
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Tính số ngày còn lại của thẻ.
     */
    public function daysRemaining(BhytCard $card): int
    {
        return max(0, now()->diffInDays($card->expiry_date, false));
    }

    /**
     * Lấy hóa đơn chưa áp dụng BHYT cho bệnh nhân.
     */
    public function pendingInvoice(int $patientId): ?Invoice
    {
        return Invoice::with(['items.service'])
            ->where('patient_id', $patientId)
            ->where('bhyt_applied', false)
            ->where('status', 'Chờ thanh toán')
            ->latest()
            ->first();
    }

    /**
     * Áp dụng BHYT vào hóa đơn: tính giảm trừ và cập nhật tổng.
     */
    public function applyBhytToInvoice(Invoice $invoice, float $coverageRate): array
    {
        $items        = $invoice->items;
        $totalOriginal = $items->sum('unit_price');
        $bhytPays     = round($totalOriginal * $coverageRate / 100);
        $patientPays  = $totalOriginal - $bhytPays;

        $invoice->update([
            'bhyt_applied'    => true,
            'bhyt_coverage'   => $coverageRate,
            'bhyt_amount'     => $bhytPays,
            'total_amount'    => $patientPays,
        ]);

        return [
            'original_total' => $totalOriginal,
            'bhyt_pays'      => $bhytPays,
            'patient_pays'   => $patientPays,
            'coverage_rate'  => $coverageRate,
            'items'          => $items,
        ];
    }
}
