<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SarhIndex v1.9.0 — محرك استثناءات الحضور
 *
 * يسمح بتعريف استثناءات حضور لموظفين محددين:
 * - ساعات مرنة
 * - عمل عن بعد (تجاوز السياج الجغرافي)
 * - تجاوز تسجيل التأخير
 */
class AttendanceException extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exception_type',
        'custom_shift_start',
        'custom_shift_end',
        'custom_grace_minutes',
        'bypass_geofence',
        'bypass_late_flag',
        'start_date',
        'end_date',
        'reason',
        'approved_by',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date'           => 'date',
            'end_date'             => 'date',
            'bypass_geofence'      => 'boolean',
            'bypass_late_flag'     => 'boolean',
            'is_active'            => 'boolean',
            'custom_grace_minutes' => 'integer',
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
     * استثناءات نشطة وسارية المفعول اليوم.
     */
    public function scopeActiveToday($query)
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)
                     ->where('start_date', '<=', $today)
                     ->where(function ($q) use ($today) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', $today);
                     });
    }

    /**
     * استثناءات لمستخدم محدد.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the active exception for a user today.
     */
    public static function getActiveForUser(int $userId): ?self
    {
        return static::forUser($userId)->activeToday()->first();
    }

    /**
     * Check if this exception covers today.
     */
    public function isValidToday(): bool
    {
        $today = now()->startOfDay();

        return $this->is_active
            && $this->start_date->lte($today)
            && (is_null($this->end_date) || $this->end_date->gte($today));
    }

    /**
     * Get effective shift start time (custom or null for default).
     */
    public function getEffectiveShiftStart(): ?string
    {
        return $this->custom_shift_start;
    }

    /**
     * Get effective grace period (custom or null for default).
     */
    public function getEffectiveGracePeriod(): ?int
    {
        return $this->custom_grace_minutes;
    }
}
