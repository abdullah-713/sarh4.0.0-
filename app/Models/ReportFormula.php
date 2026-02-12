<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SARH v1.9.0 — صيغ التقارير الديناميكية
 *
 * يسمح لمالك النظام بتعريف صيغ حسابية مخصصة للتقارير.
 * مثال: (attendance * 0.4) + (task_completion * 0.6)
 */
class ReportFormula extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'slug',
        'formula',
        'variables',
        'description_ar',
        'description_en',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    /*
    |--------------------------------------------------------------------------
    | FORMULA ENGINE
    |--------------------------------------------------------------------------
    */

    /**
     * Evaluate the formula with given variable values.
     *
     * @param  array<string, float>  $values  e.g. ['attendance' => 95.5, 'task_completion' => 87.0]
     * @return float|null
     */
    public function evaluate(array $values): ?float
    {
        $formula = $this->formula;

        // Validate all required variables are provided
        $requiredVars = array_keys($this->variables ?? []);
        foreach ($requiredVars as $var) {
            if (!array_key_exists($var, $values)) {
                return null;
            }
        }

        // Replace variables with their values (longest first to avoid partial replacements)
        $vars = array_keys($values);
        usort($vars, fn ($a, $b) => strlen($b) - strlen($a));

        foreach ($vars as $var) {
            $formula = str_replace($var, (string) (float) $values[$var], $formula);
        }

        // Sanitize: Only allow numbers, operators, parentheses, spaces, and decimal points
        $sanitized = preg_replace('/[^0-9+\-*\/().%\s]/', '', $formula);

        if (empty($sanitized) || $sanitized !== $formula) {
            return null; // Formula contains invalid characters
        }

        try {
            // Safe evaluation using PHP's mathematical evaluation
            $result = @eval("return ({$sanitized});");
            return is_numeric($result) ? round((float) $result, 4) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Get available variables with their descriptions.
     */
    public function getVariablesList(): array
    {
        $vars = $this->variables ?? [];
        $list = [];

        foreach ($vars as $key => $description) {
            $list[] = [
                'key'         => $key,
                'description' => is_array($description)
                    ? ($description[app()->getLocale()] ?? $description['ar'] ?? $key)
                    : $description,
            ];
        }

        return $list;
    }

    /**
     * Validate formula syntax without executing.
     */
    public function validateFormula(): bool
    {
        $testValues = [];
        foreach (($this->variables ?? []) as $key => $desc) {
            $testValues[$key] = 1.0;
        }

        return $this->evaluate($testValues) !== null;
    }
}
