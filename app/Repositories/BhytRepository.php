<?php

namespace App\Repositories;

use App\Models\BhytCard;
use App\Models\Payment;
use Illuminate\Support\Collection;

class BhytRepository
{
    /**
     * Tra cứu thẻ BHYT theo mã thẻ.
     */
    public function findByCardNumber(string $cardNumber): ?BhytCard
    {
        return BhytCard::with('user')->where('card_number', strtoupper($cardNumber))
            ->first();
    }

    /**
     * Danh sách thẻ sắp hết hạn (trong N ngày tới).
     */
    public function expiringSoon(int $days = 60): Collection
    {
        return BhytCard::with('user')->where('status', 'Còn hạn')
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
    public function pendingInvoice(int $patientId): ?Payment
    {
        return Payment::with(['items', 'appointment.user'])
            ->whereHas('appointment', function ($query) use ($patientId) {
                $query->where('user_id', $patientId);
            })
            ->whereNull('insurance_id')
            ->where('status', 'Chưa thanh toán')
            ->orderBy('payment_id', 'desc')
            ->first();
    }

    /**
     * Áp dụng BHYT vào hóa đơn: tính giảm trừ và cập nhật tổng.
     */
    public function applyBhytToInvoice(Payment $payment, BhytCard $card): array
    {
        $items        = $payment->items;
        $totalOriginal = $payment->subtotal ?? $items->sum('subtotal') ?? 0;
        
        $coverageRate = $card->coverage_rate;
        $bhytPays     = round($totalOriginal * $coverageRate / 100);
        $patientPays  = $totalOriginal - $bhytPays;

        $payment->update([
            'insurance_id'    => $card->card_id,
            'discount_amount' => $bhytPays,
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
