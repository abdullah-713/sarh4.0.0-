<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reminder_key',
        'reminder_date',
        'notes',
        'is_completed',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'reminder_date' => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    // العلاقات
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // المحسوبات
    public function getDaysUntilDueAttribute(): int
    {
        return now()->diffInDays($this->reminder_date, false);
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->is_completed) {
            return 'success';
        }

        $days = $this->days_until_due;

        if ($days < 0) {
            return 'danger'; // متأخر
        }

        if ($days <= 10) {
            return 'danger'; // أحمر وامض (10 أيام أو أقل)
        }

        if ($days <= 30) {
            return 'danger'; // أحمر (شهر أو أقل)
        }

        if ($days <= 90) {
            return 'warning'; // أصفر (3 أشهر إلى شهر)
        }

        return 'success'; // أخضر (فوق 3 أشهر)
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_completed) {
            return 'مكتمل';
        }

        $days = $this->days_until_due;

        if ($days < 0) {
            return 'متأخر ' . abs($days) . ' يوم';
        }

        if ($days === 0) {
            return 'اليوم';
        }

        if ($days === 1) {
            return 'غداً';
        }

        if ($days <= 10) {
            return 'عاجل: ' . $days . ' يوم';
        }

        if ($days <= 30) {
            return $days . ' يوم';
        }

        if ($days <= 90) {
            return round($days / 7) . ' أسبوع';
        }

        return round($days / 30) . ' شهر';
    }

    public function getIsOverdueAttribute(): bool
    {
        return !$this->is_completed && $this->days_until_due < 0;
    }

    public function getIsUrgentAttribute(): bool
    {
        return !$this->is_completed && $this->days_until_due <= 10 && $this->days_until_due >= 0;
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return !$this->is_completed && $this->days_until_due <= 30 && $this->days_until_due >= 0;
    }

    // دوال
    public function markAsCompleted(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    // نطاقات الاستعلام
    public function scopeUpcoming($query)
    {
        return $query->where('is_completed', false)
            ->whereDate('reminder_date', '>=', now())
            ->orderBy('reminder_date', 'asc');
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_completed', false)
            ->whereDate('reminder_date', '<', now())
            ->orderBy('reminder_date', 'desc');
    }

    public function scopeUrgent($query)
    {
        return $query->where('is_completed', false)
            ->whereDate('reminder_date', '<=', now()->addDays(10))
            ->whereDate('reminder_date', '>=', now())
            ->orderBy('reminder_date', 'asc');
    }

    public function scopeExpiringSoon($query, int $days = 90)
    {
        return $query->where('is_completed', false)
            ->whereDate('reminder_date', '<=', now()->addDays($days))
            ->whereDate('reminder_date', '>=', now())
            ->orderBy('reminder_date', 'asc');
    }
}
