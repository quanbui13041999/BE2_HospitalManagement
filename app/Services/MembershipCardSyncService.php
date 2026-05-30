<?php

namespace App\Services;

use App\Models\MembershipCard;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class MembershipCardSyncService
{
    private const PAID_STATUSES = ['Thành công', 'Đã thanh toán'];
    private const PENDING_STATUSES = ['Chờ xử lý', 'Chờ thanh toán'];

    public function syncForUser(int $userId): ?MembershipCard
    {
        return DB::transaction(function () use ($userId) {
            $user = DB::table('users')
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (! $user) {
                return null;
            }

            $membership = MembershipCard::firstOrCreate(
                ['user_id' => $userId],
                [
                    'points' => 0,
                    'total_spent' => 0,
                    'tier' => 'Đồng',
                    'discount_pct' => 0,
                    'card_number' => $this->cardNumber($userId),
                    'issue_date' => now()->toDateString(),
                    'expiry_date' => now()->addYear()->toDateString(),
                    'status' => true,
                ]
            );

            $this->repairCardDefaults($membership, $userId);
            $this->linkPaidPaymentsToMembership($userId, (int) $membership->card_id);

            $totalSpent = $this->paidTotalForUser($userId);

            $membership->total_spent = $totalSpent;
            $membership->points = $this->pointsForSpent($totalSpent);
            $membership->tier = MembershipCard::tierForSpent($totalSpent);
            $membership->discount_pct = $this->discountForSpent($totalSpent);
            $membership->save();

            return $membership->fresh();
        }, 3);
    }

    public function paidTotalForUser(int $userId): float
    {
        return (float) $this->paidPaymentsQuery($userId)->sum('payments.total_amount');
    }

    public function paidVisitCountForUser(int $userId): int
    {
        return (int) $this->paidPaymentsQuery($userId)->count();
    }

    public function pendingPointsForUser(int $userId): int
    {
        $pendingAmount = (float) $this->paymentsQuery($userId)
            ->whereIn('payments.status', self::PENDING_STATUSES)
            ->sum('payments.total_amount');

        return $this->pointsForSpent($pendingAmount);
    }

    public function savedMoneyForUser(int $userId): float
    {
        return (float) $this->paidPaymentsQuery($userId)->sum('payments.discount_amount');
    }

    private function paidPaymentsQuery(int $userId): Builder
    {
        return $this->paymentsQuery($userId)->whereIn('payments.status', self::PAID_STATUSES);
    }

    private function paymentsQuery(int $userId): Builder
    {
        return DB::table('payments')
            ->join('appointments', 'payments.appointment_id', '=', 'appointments.appointment_id')
            ->where('appointments.user_id', $userId);
    }

    private function linkPaidPaymentsToMembership(int $userId, int $membershipId): void
    {
        $paymentIds = $this->paidPaymentsQuery($userId)->pluck('payments.payment_id');

        if ($paymentIds->isEmpty()) {
            return;
        }

        DB::table('payments')
            ->whereIn('payment_id', $paymentIds)
            ->update(['membership_id' => $membershipId]);
    }

    private function repairCardDefaults(MembershipCard $membership, int $userId): void
    {
        if (! $membership->card_number || $membership->card_number === 'Chưa có thẻ') {
            $membership->card_number = $this->cardNumber($userId);
        }

        if (! $membership->issue_date) {
            $membership->issue_date = now()->toDateString();
        }

        if (! $membership->expiry_date) {
            $membership->expiry_date = now()->addYear()->toDateString();
        }

        if ($membership->status === null) {
            $membership->status = true;
        }
    }

    private function pointsForSpent(float $spent): int
    {
        return (int) floor($spent / 1000);
    }

    private function discountForSpent(float $spent): float
    {
        return match (true) {
            $spent >= 25_000_000 => 12.00,
            $spent >= 10_000_000 => 8.00,
            $spent >= 5_000_000 => 5.00,
            default => 0.00,
        };
    }

    private function cardNumber(int $userId): string
    {
        return 'MB-' . now()->format('Ymd') . '-' . str_pad((string) $userId, 6, '0', STR_PAD_LEFT);
    }
}
