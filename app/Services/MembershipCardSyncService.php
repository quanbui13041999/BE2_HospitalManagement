<?php

namespace App\Services;

use App\Models\MembershipCard;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class MembershipCardSyncService
{
    public function getOrCreateForUser(int $userId): MembershipCard
    {
        return MembershipCard::firstOrCreate(
            ['user_id' => $userId],
            $this->defaultCardData($userId)
        );
    }

    public function syncForPayment(Payment $payment): ?MembershipCard
    {
        $userId = $payment->appointment?->user_id;

        return $userId ? $this->syncForUser((int) $userId) : null;
    }

    public function syncForUser(int $userId): MembershipCard
    {
        return DB::transaction(function () use ($userId): MembershipCard {
            $membership = MembershipCard::where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (! $membership) {
                $membership = MembershipCard::create($this->defaultCardData($userId));
            }

            $totalSpent = (float) Payment::query()
                ->whereHas('appointment', fn ($query) => $query->where('user_id', $userId))
                ->whereIn('status', MembershipCard::PAID_PAYMENT_STATUSES)
                ->where('total_amount', '>', 0)
                ->sum('total_amount');

            $totalSpent = max(0, $totalSpent);
            $computedPoints = (int) floor($totalSpent / 1000);
            $computedTier = MembershipCard::tierForSpent($totalSpent);

            if (
                round((float) $membership->getRawOriginal('total_spent'), 2) !== round($totalSpent, 2)
                || (int) $membership->getRawOriginal('points') !== $computedPoints
                || (string) $membership->getRawOriginal('tier') !== $computedTier
                || (float) $membership->getRawOriginal('discount_pct') !== MembershipCard::discountForTier($computedTier)
            ) {
                $membership->total_spent = $totalSpent;
                $membership->save();
            }

            return $membership->refresh();
        });
    }

    public function getVisitCountForUser(int $userId): int
    {
        return Payment::query()
            ->whereHas('appointment', fn ($query) => $query->where('user_id', $userId))
            ->whereIn('status', MembershipCard::PAID_PAYMENT_STATUSES)
            ->where('total_amount', '>', 0)
            ->distinct('appointment_id')
            ->count('appointment_id');
    }

    public function getPointHistoryForUser(int $userId, int $limit = 5)
    {
        return Payment::query()
            ->whereHas('appointment', fn ($query) => $query->where('user_id', $userId))
            ->whereIn('status', MembershipCard::PAID_PAYMENT_STATUSES)
            ->where('total_amount', '>', 0)
            ->orderByDesc('payment_date')
            ->orderByDesc('payment_id')
            ->limit($limit)
            ->get()
            ->map(function (Payment $payment): array {
                $amount = max(0, (float) $payment->total_amount);

                return [
                    'payment_id' => $payment->payment_id,
                    'appointment_id' => $payment->appointment_id,
                    'points' => (int) floor($amount / 1000),
                    'amount' => $amount,
                    'payment_date' => $payment->payment_date,
                    'transaction_ref' => $payment->transaction_ref,
                ];
            });
    }

    private function defaultCardData(int $userId): array
    {
        return [
            'user_id' => $userId,
            'card_number' => MembershipCard::cardNumberForUser($userId),
            'points' => 0,
            'total_spent' => 0,
            'tier' => MembershipCard::TIER_BRONZE,
            'discount_pct' => MembershipCard::discountForTier(MembershipCard::TIER_BRONZE),
            'issue_date' => now()->toDateString(),
            'expiry_date' => now()->addYear()->toDateString(),
            'status' => true,
        ];
    }
}
