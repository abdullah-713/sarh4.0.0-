<?php

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class HandleAttendanceRecorded
{
    /**
     * Handle the AttendanceRecorded event.
     *
     * Logs the attendance action to audit_logs for traceability,
     * and can be extended for future notification/analytics hooks.
     */
    public function handle(AttendanceRecorded $event): void
    {
        $log = $event->log;

        try {
            AuditLog::create([
                'user_id'        => $log->user_id,
                'action'         => $log->check_out_at ? 'attendance_checkout' : 'attendance_checkin',
                'auditable_type' => get_class($log),
                'auditable_id'   => $log->id,
                'old_values'     => null,
                'new_values'     => [
                    'status'          => $log->status,
                    'attendance_date' => $log->attendance_date?->toDateString(),
                    'check_in'        => $log->check_in_at,
                    'check_out'       => $log->check_out_at,
                    'branch_id'       => $log->branch_id,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::warning('HandleAttendanceRecorded: فشل تسجيل الحضور في سجل المراجعة', [
                'attendance_log_id' => $log->id ?? null,
                'error'             => $e->getMessage(),
            ]);
        }
    }
}
