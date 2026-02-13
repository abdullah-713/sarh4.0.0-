<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SARH v4.1 — Anomaly Log (سجل التلاعب)
 *
 * يُنشئ عند كشف نمط مريب في بيانات الحساسات.
 * يتطلب مراجعة من المدير قبل اتخاذ إجراء.
 */
class AnomalyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sensor_reading_id',
        'anomaly_type',
        'confidence',
        'context_data',
        'is_reviewed',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'confidence'   => 'decimal:2',
            'context_data' => 'array',
            'is_reviewed'  => 'boolean',
            'reviewed_at'  => 'datetime',
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

    public function sensorReading(): BelongsTo
    {
        return $this->belongsTo(SensorReading::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->where('is_reviewed', false);
    }

    public function scopeReviewed($query)
    {
        return $query->where('is_reviewed', true);
    }

    public function scopeHighConfidence($query, float $threshold = 0.9)
    {
        return $query->where('confidence', '>=', $threshold);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function markReviewed(?int $reviewerId = null): self
    {
        $this->update([
            'is_reviewed' => true,
            'reviewed_by' => $reviewerId ?? auth()->id(),
            'reviewed_at' => now(),
        ]);

        return $this;
    }

    public function getReadableTypeAttribute(): string
    {
        return match ($this->anomaly_type) {
            'location_mismatch'    => 'تناقض الموقع والنشاط',
            'perfect_signal'       => 'إشارة مثالية (آلة)',
            'no_motion_timeout'    => 'ثبات طويل',
            'frequency_mismatch'   => 'تردد غير متوافق',
            'replay_attack'        => 'قراءات مُعادة',
            'impossible_frequency' => 'تردد مستحيل بشرياً',
            default                => 'شذوذ غير مصنف',
        };
    }
}
