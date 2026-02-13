<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * SARH v4.1 — Sensor Reading (حساس الإنتاجية)
 *
 * يمثل قراءة واحدة من حساسات الموبايل (accelerometer + gyroscope + dB)
 * يُرسل من التطبيق كل 10 دقائق كنتيجة Edge Processing.
 */
class SensorReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_log_id',
        'avg_accel_x',
        'avg_accel_y',
        'avg_accel_z',
        'variance_motion',
        'peak_frequency',
        'db_level',
        'work_probability',
        'motion_signature',
        'is_anomaly',
        'anomaly_reason',
        'sampling_window',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'avg_accel_x'      => 'decimal:4',
            'avg_accel_y'      => 'decimal:4',
            'avg_accel_z'      => 'decimal:4',
            'variance_motion'  => 'decimal:4',
            'peak_frequency'   => 'decimal:2',
            'db_level'         => 'decimal:2',
            'work_probability' => 'decimal:2',
            'is_anomaly'       => 'boolean',
            'sampling_window'  => 'integer',
            'processed_at'     => 'datetime',
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

    public function attendanceLog(): BelongsTo
    {
        return $this->belongsTo(AttendanceLog::class);
    }

    public function anomalyLog(): HasOne
    {
        return $this->hasOne(AnomalyLog::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeWork($query)
    {
        return $query->where('work_probability', '>', 0.7);
    }

    public function scopeRest($query)
    {
        return $query->where('work_probability', '<', 0.3);
    }

    public function scopeAnomalies($query)
    {
        return $query->where('is_anomaly', true);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('created_at', $date);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isWork(): bool
    {
        return $this->work_probability > 0.7;
    }

    public function isRest(): bool
    {
        return $this->work_probability < 0.3;
    }

    public function getReadableSignatureAttribute(): string
    {
        return match ($this->motion_signature) {
            'mechanical'  => 'عمل ميكانيكي',
            'walking'     => 'مشي',
            'stationary'  => 'ثبات',
            'suspicious'  => 'مريب',
            default       => 'غير معروف',
        };
    }
}
