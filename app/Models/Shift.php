<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'start_time',
        'end_time',
        'grace_period_minutes',
        'is_overnight',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'grace_period_minutes' => 'integer',
            'is_overnight'         => 'boolean',
            'is_active'            => 'boolean',
        ];
    }

    /**
     * جميع تعيينات هذا الشفت.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(UserShift::class);
    }

    /**
     * الموظفون المعينون حالياً على هذا الشفت.
     */
    public function currentlyAssignedUsers(): HasMany
    {
        return $this->assignments()->active()->current();
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get shift duration in minutes.
     */
    public function getDurationMinutesAttribute(): int
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end   = \Carbon\Carbon::parse($this->end_time);

        if ($this->is_overnight && $end->lt($start)) {
            $end->addDay();
        }

        return (int) $start->diffInMinutes($end);
    }
}
