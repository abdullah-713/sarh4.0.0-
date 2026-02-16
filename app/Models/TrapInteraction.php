<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * نموذج تفاعلات الفخاخ — Trap Interactions
 *
 * يسجل كل تفاعل لمستخدم مع فخ نفسي، بما في ذلك:
 * - درجة الخطر المحسوبة (لوغاريتمية تصاعدية)
 * - الإجراء المتخذ (logged, warned, escalated)
 * - البيانات الفرعية (IP, User-Agent, الصفحة المصدرية)
 */
class TrapInteraction extends Model
{
    protected $fillable = [
        'trap_id',
        'user_id',
        'risk_score',         // 0.0 - 100.0
        'action_taken',       // logged | warned | escalated
        'ip_address',
        'user_agent',
        'referrer_url',
        'metadata',           // JSON: بيانات إضافية
        'interaction_count',  // عدد تفاعلات هذا المستخدم مع هذا الفخ
    ];

    protected function casts(): array
    {
        return [
            'risk_score'        => 'float',
            'metadata'          => 'array',
            'interaction_count' => 'integer',
        ];
    }

    // ── العلاقات ──

    public function trap(): BelongsTo
    {
        return $this->belongsTo(Trap::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── النطاقات ──

    public function scopeHighRisk($query, float $threshold = 50.0)
    {
        return $query->where('risk_score', '>=', $threshold);
    }

    public function scopeEscalated($query)
    {
        return $query->where('action_taken', 'escalated');
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ── المساعدات ──

    /**
     * تصنيف مستوى الخطر بالعربية
     */
    public function getRiskLevelAttribute(): string
    {
        return match (true) {
            $this->risk_score >= 75 => 'حرج',
            $this->risk_score >= 50 => 'مرتفع',
            $this->risk_score >= 25 => 'متوسط',
            default                 => 'منخفض',
        };
    }

    /**
     * لون مستوى الخطر
     */
    public function getRiskColorAttribute(): string
    {
        return match (true) {
            $this->risk_score >= 75 => 'danger',
            $this->risk_score >= 50 => 'warning',
            $this->risk_score >= 25 => 'info',
            default                 => 'success',
        };
    }
}
