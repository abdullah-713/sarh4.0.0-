<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'document_number',
        'file_path',
        'file_type',
        'issue_date',
        'expiry_date',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    // العلاقات
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // المحسوبات
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return now()->diffInDays($this->expiry_date, false);
    }

    public function getStatusColorAttribute(): string
    {
        $days = $this->days_until_expiry;

        if ($days === null) {
            return 'gray';
        }

        if ($days < 0) {
            return 'danger'; // منتهي
        }

        if ($days <= 10) {
            return 'danger'; // أحمر وامض
        }

        if ($days <= 30) {
            return 'warning'; // أحمر
        }

        if ($days <= 90) {
            return 'warning'; // أصفر
        }

        return 'success'; // أخضر
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->days_until_expiry !== null && $this->days_until_expiry < 0;
    }

    public function getIsExpiringAttribute(): bool
    {
        return $this->days_until_expiry !== null && $this->days_until_expiry <= 30 && $this->days_until_expiry >= 0;
    }

    // الملف
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    // نطاقات الاستعلام
    public function scopeExpiringSoon($query, int $days = 90)
    {
        return $query->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays($days))
            ->whereDate('expiry_date', '>=', now())
            ->orderBy('expiry_date', 'asc');
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', now())
            ->orderBy('expiry_date', 'desc');
    }
}
