<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\ReportFormula;
use App\Models\ScoreAdjustment;
use App\Models\User;
use Carbon\Carbon;

/**
 * SarhIndex v1.9.0 — محرك الصيغ الديناميكية للتقارير
 *
 * يحسب نتائج الصيغ المخصصة بناءً على بيانات الموظف الفعلية.
 * يدعم المتغيرات: attendance, delay_rate, on_time_rate, total_points,
 * overtime_rate, early_leave_rate, financial_loss, task_completion
 */
class FormulaEngineService
{
    /**
     * Evaluate a formula for a specific user over a date range.
     */
    public function evaluateForUser(ReportFormula $formula, User $user, string $startDate, string $endDate): ?float
    {
        $values = $this->resolveVariablesForUser($user, $startDate, $endDate, array_keys($formula->variables ?? []));

        return $formula->evaluate($values);
    }

    /**
     * Evaluate a formula for all users in a branch.
     *
     * @return array<int, array{user: User, score: float|null}>
     */
    public function evaluateForBranch(ReportFormula $formula, int $branchId, string $startDate, string $endDate): array
    {
        $users = User::where('branch_id', $branchId)->active()->get();
        $results = [];

        foreach ($users as $user) {
            $results[] = [
                'user'  => $user,
                'score' => $this->evaluateForUser($formula, $user, $startDate, $endDate),
            ];
        }

        usort($results, fn ($a, $b) => ($b['score'] ?? 0) <=> ($a['score'] ?? 0));

        return $results;
    }

    /**
     * Resolve variable values for a specific user from real data.
     */
    public function resolveVariablesForUser(User $user, string $startDate, string $endDate, array $requiredVars): array
    {
        $logs = AttendanceLog::where('user_id', $user->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get();

        $totalDays = max($logs->count(), 1);
        $values = [];

        foreach ($requiredVars as $var) {
            $values[$var] = match ($var) {
                // نسبة الحضور (عدد أيام الحضور / إجمالي الأيام × 100)
                'attendance' => round(
                    ($logs->whereIn('status', ['present', 'late'])->count() / $totalDays) * 100,
                    2
                ),

                // نسبة التأخير (أيام التأخير / إجمالي الأيام × 100)
                'delay_rate' => round(
                    ($logs->where('status', 'late')->count() / $totalDays) * 100,
                    2
                ),

                // نسبة الحضور في الوقت (أيام present / إجمالي الأيام × 100)
                'on_time_rate' => round(
                    ($logs->where('status', 'present')->count() / $totalDays) * 100,
                    2
                ),

                // إجمالي النقاط
                'total_points' => (float) $user->total_points,

                // نسبة العمل الإضافي (دقائق إضافية / إجمالي الدقائق المتوقعة × 100)
                'overtime_rate' => round(
                    ($logs->sum('overtime_minutes') / max($totalDays * ($user->working_hours_per_day ?? 8) * 60, 1)) * 100,
                    2
                ),

                // نسبة المغادرة المبكرة
                'early_leave_rate' => round(
                    ($logs->sum('early_leave_minutes') / max($totalDays * ($user->working_hours_per_day ?? 8) * 60, 1)) * 100,
                    2
                ),

                // الخسائر المالية بالريال
                'financial_loss' => round((float) $logs->sum('delay_cost'), 2),

                // إنجاز المهام (placeholder — يمكن ربطه بنظام مهام مستقبلي)
                'task_completion' => 100.0,

                // سلسلة الانضباط الحالية
                'current_streak' => (float) $user->current_streak,

                // أطول سلسلة انضباط
                'longest_streak' => (float) $user->longest_streak,

                // إجمالي دقائق التأخير
                'total_delay_minutes' => (float) $logs->sum('delay_minutes'),

                // إجمالي دقائق العمل الإضافي
                'total_overtime_minutes' => (float) $logs->sum('overtime_minutes'),

                // التعديلات اليدوية (مجموع النقاط المضافة)
                'manual_adjustments' => (float) ScoreAdjustment::where('scope', 'user')
                    ->where('user_id', $user->id)
                    ->sum('points_delta'),

                default => 0.0,
            };
        }

        return $values;
    }

    /**
     * Get all available system variables with descriptions.
     */
    public static function getAvailableVariables(): array
    {
        return [
            'attendance'             => ['ar' => 'نسبة الحضور (%)', 'en' => 'Attendance Rate (%)'],
            'delay_rate'             => ['ar' => 'نسبة التأخير (%)', 'en' => 'Delay Rate (%)'],
            'on_time_rate'           => ['ar' => 'نسبة الحضور في الوقت (%)', 'en' => 'On-Time Rate (%)'],
            'total_points'           => ['ar' => 'إجمالي النقاط', 'en' => 'Total Points'],
            'overtime_rate'          => ['ar' => 'نسبة العمل الإضافي (%)', 'en' => 'Overtime Rate (%)'],
            'early_leave_rate'       => ['ar' => 'نسبة المغادرة المبكرة (%)', 'en' => 'Early Leave Rate (%)'],
            'financial_loss'         => ['ar' => 'الخسائر المالية (ريال)', 'en' => 'Financial Loss (SAR)'],
            'task_completion'        => ['ar' => 'نسبة إنجاز المهام (%)', 'en' => 'Task Completion (%)'],
            'current_streak'         => ['ar' => 'سلسلة الانضباط الحالية', 'en' => 'Current Streak'],
            'longest_streak'         => ['ar' => 'أطول سلسلة انضباط', 'en' => 'Longest Streak'],
            'total_delay_minutes'    => ['ar' => 'إجمالي دقائق التأخير', 'en' => 'Total Delay Minutes'],
            'total_overtime_minutes' => ['ar' => 'إجمالي دقائق العمل الإضافي', 'en' => 'Total Overtime Minutes'],
            'manual_adjustments'     => ['ar' => 'التعديلات اليدوية', 'en' => 'Manual Adjustments'],
        ];
    }
}
