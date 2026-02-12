<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SARH v1.9.0 — تعديلات النقاط/الدرجات اليدوية
 *
 * يسمح للمالك بتعديل نقاط الفروع والموظفين يدوياً.
 */
class ScoreAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'scope',
        'branch_id',
        'user_id',
        'department_id',
        'points_delta',
        'value_delta',
        'category',
        'reason',
        'adjusted_by',
    ];

    protected function casts(): array
    {
        return [
            'points_delta' => 'integer',
            'value_delta'  => 'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function adjustedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('scope', 'branch')->where('branch_id', $branchId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('scope', 'user')->where('user_id', $userId);
    }

    public function scopePositive($query)
    {
        return $query->where('points_delta', '>', 0);
    }

    public function scopeNegative($query)
    {
        return $query->where('points_delta', '<', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Get the target entity name.
     */
    public function getTargetNameAttribute(): string
    {
        return match ($this->scope) {
            'branch'     => $this->branch?->name ?? '—',
            'user'       => $this->user?->name ?? '—',
            'department' => $this->department?->name ?? '—',
            default      => '—',
        };
    }
}
