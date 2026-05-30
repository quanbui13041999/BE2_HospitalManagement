<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipCard extends Model
{
    public const TIER_BRONZE = 'Đồng';
    public const TIER_SILVER = 'Bạc';
    public const TIER_GOLD = 'Vàng';
    public const TIER_DIAMOND = 'Kim Cương';

    public const PAID_PAYMENT_STATUSES = ['Thành công', 'Đã thanh toán'];

    protected $table = 'membershipcards';
    protected $primaryKey = 'card_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'card_number',
        'tier',
        'total_spent',
        'points',
        'discount_pct',
        'issue_date',
        'expiry_date',
        'status',
    ];

    protected $casts = [
        'total_spent' => 'float',
        'points' => 'integer',
        'discount_pct' => 'decimal:2',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'status' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (MembershipCard $card): void {
            $spent = max(0, (float) $card->total_spent);

            $card->total_spent = $spent;
            $card->points = (int) floor($spent / 1000);
            $card->tier = self::tierForSpent($spent);
            $card->discount_pct = self::discountForTier($card->tier);

            if (empty($card->issue_date)) {
                $card->issue_date = now()->toDateString();
            }

            if (empty($card->expiry_date)) {
                $card->expiry_date = now()->addYear()->toDateString();
            }

            if ($card->status === null) {
                $card->status = true;
            }
        });
    }

    public static function cardNumberForUser(int $userId): string
    {
        return 'MB-' . now()->format('Ymd') . '-' . str_pad((string) $userId, 6, '0', STR_PAD_LEFT);
    }

    public static function tierForSpent(float|int $spent): string
    {
        $spent = max(0, (float) $spent);

        return match (true) {
            $spent >= 25_000_000 => self::TIER_DIAMOND,
            $spent >= 10_000_000 => self::TIER_GOLD,
            $spent >= 5_000_000 => self::TIER_SILVER,
            default => self::TIER_BRONZE,
        };
    }

    public static function discountForTier(?string $tier): float
    {
        return match ($tier) {
            self::TIER_DIAMOND => 20.00,
            self::TIER_GOLD => 10.00,
            self::TIER_SILVER => 5.00,
            default => 0.00,
        };
    }

    public function getProgressPercentAttribute(): int
    {
        $spent = max(0, (float) $this->getRawOriginal('total_spent'));

        $percent = match (true) {
            $spent >= 25_000_000 => 100,
            $spent >= 10_000_000 => (int) (($spent - 10_000_000) / 15_000_000 * 34) + 66,
            $spent >= 5_000_000 => (int) (($spent - 5_000_000) / 5_000_000 * 33) + 33,
            default => (int) ($spent / 5_000_000 * 33),
        };

        return max(0, min(100, $percent));
    }

    public function getRemainingToNextTierAttribute(): int
    {
        $spent = max(0, (float) $this->getRawOriginal('total_spent'));

        return match (true) {
            $spent >= 25_000_000 => 0,
            $spent >= 10_000_000 => 25_000_000 - (int) $spent,
            $spent >= 5_000_000 => 10_000_000 - (int) $spent,
            default => 5_000_000 - (int) $spent,
        };
    }

    public function getNextTierAttribute(): string
    {
        $spent = max(0, (float) $this->getRawOriginal('total_spent'));

        return match (true) {
            $spent >= 25_000_000 => self::TIER_DIAMOND,
            $spent >= 10_000_000 => self::TIER_DIAMOND,
            $spent >= 5_000_000 => self::TIER_GOLD,
            default => self::TIER_SILVER,
        };
    }
}
