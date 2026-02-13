<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SARH v2.0 — تعيين الشفتات للموظفين مع الصلاحية الزمنية.
 *
 * هذا ليس Pivot. هذا عقد عمل مؤقت.
 * يحل مشكلة: تاريخ الشفتات، التفويض، التعارض، التقارير.
 */
class UserShift extends Model
{
    use HasFactory;

    protected $table = 'user_shifts';

    protected $fillable = [
        'user_id',
        'shift_id',
        'assigned_by',
        'effective_from',
        'effective_to',
        'is_current',
        'reason',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to'   => 'date',
            'is_current'     => 'boolean',
            'approved_at'    => 'datetime',
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

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * الشفتات السارية اليوم.
     */
    public function scopeActive($query)
    {
        $today = now()->toDateString();

        return $query->where('effective_from', '<=', $today)
                     ->where(function ($q) use ($today) {
                         $q->whereNull('effective_to')
                           ->orWhere('effective_to', '>=', $today);
                     });
    }

    /**
     * الشفت الحالي (is_current = true).
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * الشفتات لموظف معين في فترة زمنية.
     */
    public function scopeForUserInPeriod($query, int $userId, $startDate, $endDate)
    {
        return $query->where('user_id', $userId)
                     ->where('effective_from', '<=', $endDate)
                     ->where(function ($q) use ($startDate) {
                         $q->whereNull('effective_to')
                           ->orWhere('effective_to', '>=', $startDate);
                     });
    }

    /*
    |--------------------------------------------------------------------------
    | BUSINESS LOGIC
    |--------------------------------------------------------------------------
    */

    /**
     * هل هذا التعيين ساري في تاريخ معين؟
     */
    public function isValidOn($date): bool
    {
        $date = \Carbon\Carbon::parse($date)->startOfDay();

        return $this->effective_from->lte($date)
            && (is_null($this->effective_to) || $this->effective_to->gte($date));
    }

    /**
     * إنهاء هذا التعيين.
     */
    public function terminate(?string $reason = null): void
    {
        $this->update([
            'effective_to' => now()->subDay(),
            'is_current'   => false,
            'reason'       => $reason ?: 'منتهي يدوياً',
        ]);
    }

    /**
     * تفعيل هذا التعيين كشفت حالي.
     */
    public function makeCurrent(): void
    {
        // إلغاء حالة current من جميع تعيينات هذا الموظف
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_current' => false]);

        $this->update(['is_current' => true]);
    }
}
