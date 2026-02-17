<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * نموذج الفخاخ النفسية — Psychological Traps
 *
 * يُستخدم لاكتشاف محاولات التلاعب بالنظام عبر عناصر واجهة مموهة.
 * كل فخ يحاكي صلاحية غير موجودة (مثل "تعديل الحضور يدوياً")
 * وعند تفاعل المستخدم معه يُسجَّل كمؤشر خطر سلوكي.
 */
class Trap extends Model
{
    use HasFactory;

    protected $fillable = [
        'trap_code',
        'name',
        'description',
        'trigger_type',      // button_click, page_visit, form_submit, data_export
        'risk_weight',       // 1.0 - 5.0
        'is_active',
        'target_levels',     // JSON: مستويات الأمان المستهدفة
        'fake_response',     // JSON: الاستجابة الوهمية عند التفعيل
        'placement',         // sidebar, dashboard, settings, toolbar
        'css_class',
    ];

    protected function casts(): array
    {
        return [
            'is_active'       => 'boolean',
            'risk_weight'     => 'float',
            'target_levels'   => 'array',
            'fake_response'   => 'array',
        ];
    }

    // ── العلاقات ──

    public function interactions(): HasMany
    {
        return $this->hasMany(TrapInteraction::class);
    }

    // ── النطاقات ──

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForLevel($query, int $level)
    {
        return $query->where(function ($q) use ($level) {
            $q->whereNull('target_levels')
              ->orWhereJsonContains('target_levels', $level);
        });
    }

    // ── المساعدات ──

    /**
     * عدد التفاعلات الفريدة (مستخدمين مختلفين)
     */
    public function getUniqueInteractionsCountAttribute(): int
    {
        return $this->interactions()->distinct('user_id')->count('user_id');
    }

    /**
     * متوسط درجة الخطر لجميع التفاعلات
     */
    public function getAverageRiskScoreAttribute(): float
    {
        return round($this->interactions()->avg('risk_score') ?? 0, 2);
    }
}
