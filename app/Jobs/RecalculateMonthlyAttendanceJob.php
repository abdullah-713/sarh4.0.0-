<?php

namespace App\Jobs;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * SarhIndex v3.2 — Historical Data Integrity Job
 *
 * Re-calculates delay_minutes, delay_cost, cost_per_minute, overtime_value
 * for all AttendanceLogs in the current month when branch shift/salary
 * settings change.
 *
 * Dispatched from BranchResource and UserResource edit pages
 * when the admin enables "تحديث السجلات التاريخية".
 */
class RecalculateMonthlyAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        public readonly string $scope,       // 'branch' or 'user'
        public readonly int    $scopeId,     // branch_id or user_id
        public readonly int    $triggeredBy, // admin user id
    ) {}

    public function handle(): void
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        Log::info("[RecalculateAttendance] Starting — scope: {$this->scope}, id: {$this->scopeId}");

        $query = AttendanceLog::whereBetween('attendance_date', [$monthStart, $monthEnd]);

        if ($this->scope === 'branch') {
            $query->where('branch_id', $this->scopeId);
        } elseif ($this->scope === 'user') {
            $query->where('user_id', $this->scopeId);
        }
        // scope === 'all' → no filter, recalculate everything

        $logs = $query->with(['user', 'branch'])->get();
        $updated = 0;

        foreach ($logs as $log) {
            /** @var AttendanceLog $log */
            $user = $log->user;
            $branch = $log->branch;

            if (!$user || !$branch) {
                continue;
            }

            // Re-evaluate attendance status based on current branch shift
            $shiftStart = $branch->default_shift_start ?? '08:00';
            $gracePeriod = $branch->grace_period_minutes ?? 15;

            $log->evaluateAttendance($shiftStart, $gracePeriod);

            // Re-calculate financials with current salary
            $log->calculateFinancials();

            $log->save();
            $updated++;
        }

        Log::info("[RecalculateAttendance] Complete — {$updated} records updated for {$this->scope}:{$this->scopeId}, triggered by user:{$this->triggeredBy}");
    }

    /**
     * إنشاء Job لإعادة حساب شهر كامل لجميع الموظفين.
     * تُستخدم من الجدولة الشهرية الآلية.
     */
    public static function forMonth(int $year, int $month): self
    {
        return new self(
            scope: 'all',
            scopeId: 0,
            triggeredBy: 0,
        );
    }
}
