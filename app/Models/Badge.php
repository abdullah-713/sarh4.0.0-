<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'description_ar',
        'description_en',
        'icon',
        'color',
        'category',
        'points_reward',
        'criteria',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'points_reward' => 'integer',
            'criteria'      => 'array',
            'is_active'     => 'boolean',
        ];
    }

    /**
     * جميع منح هذه الشارة.
     */
    public function awards(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
