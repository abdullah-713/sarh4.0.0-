<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SarhIndex v4.1 — Work Rest Stat (إحصائيات الإنتاجية اليومية)
 *
 * سجل يومي مجمّع من sensor_readings لكل موظف.
 * يُحسب نهاية كل يوم عمل بواسطة TelemetryService::calculateDailyStats()
 */
class WorkRestStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stat_date',
        'total_readings',
        'work_readings',
        'rest_readings',
        'anomaly_readings',
        'work_minutes',
        'rest_minutes',
        'productivity_ratio',
        'expected_work_minutes',
        'vpm_leak',
        'wasted_salary',
        'rating',
        'needs_review',
    ];

    protected function casts(): array
    {
        return [
            'stat_date'            => 'date',
            'total_readings'       => 'integer',
            'work_readings'        => 'integer',
            'rest_readings'        => 'integer',
            'anomaly_readings'     => 'integer',
            'work_minutes'         => 'decimal:2',
            'rest_minutes'         => 'decimal:2',
            'productivity_ratio'   => 'decimal:2',
            'expected_work_minutes'=> 'decimal:2',
            'vpm_leak'             => 'decimal:2',
            'wasted_salary'        => 'decimal:2',
            'needs_review'         => 'boolean',
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

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeForDate($query, $date)
    {
        return $query->where('stat_date', $date);
    }

    public function scopeGolden($query)
    {
        return $query->where('rating', 'golden');
    }

    public function scopeLeaking($query)
    {
        return $query->whereIn('rating', ['leaking', 'critical']);
    }

    public function scopeNeedsReview($query)
    {
        return $query->where('needs_review', true);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function getReadableRatingAttribute(): string
    {
        return match ($this->rating) {
            'golden'   => '🏆 ذهبي',
            'normal'   => '✅ طبيعي',
            'leaking'  => '🟡 مُستنزف',
            'critical' => '🔴 حرج',
            default    => 'غير مصنف',
        };
    }

    public function getWorkRatioFormatted(): string
    {
        $total = $this->work_minutes + $this->rest_minutes;
        if ($total <= 0) return '—';

        return round(($this->work_minutes / $total) * 100) . '%';
    }
}
