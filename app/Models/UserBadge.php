<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SARH v2.0 — منح الشارات للموظفين.
 *
 * هذا ليس Pivot. هذا إنجاز موثق.
 */
class UserBadge extends Model
{
    use HasFactory;

    protected $table = 'user_badges';

    protected $fillable = [
        'user_id',
        'badge_id',
        'awarded_at',
        'awarded_reason',
        'awarded_by',
    ];

    protected function casts(): array
    {
        return [
            'awarded_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function awardedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'awarded_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeAwardedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('awarded_at', [$startDate, $endDate]);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /*
    |--------------------------------------------------------------------------
    | BUSINESS LOGIC
    |--------------------------------------------------------------------------
    */

    /**
     * منح شارة لموظف مع تسجيل النقاط.
     */
    public static function award(int $userId, int $badgeId, int $awardedBy, string $reason): self
    {
        $badge = Badge::findOrFail($badgeId);

        $userBadge = self::create([
            'user_id'        => $userId,
            'badge_id'       => $badgeId,
            'awarded_at'     => now(),
            'awarded_reason' => $reason,
            'awarded_by'     => $awardedBy,
        ]);

        // إضافة نقاط للموظف
        if ($badge->points_reward > 0) {
            $user = User::find($userId);
            $user->total_points += $badge->points_reward;
            $user->save();

            PointsTransaction::create([
                'user_id'         => $userId,
                'type'            => 'earned',
                'amount'          => $badge->points_reward,
                'balance_after'   => $user->total_points,
                'source'          => 'badge',
                'sourceable_type' => Badge::class,
                'sourceable_id'   => $badgeId,
                'description'     => "منح شارة: {$badge->name}",
            ]);
        }

        return $userBadge;
    }
}
