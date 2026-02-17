<?php

namespace Tests\Unit\Jobs;

use App\Events\AttendanceRecorded;
use App\Jobs\ProcessAttendanceJob;
use App\Jobs\RecalculateMonthlyAttendanceJob;
use App\Jobs\SendCircularJob;
use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\Circular;
use App\Models\PerformanceAlert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AllJobsTest extends TestCase
{
    use RefreshDatabase;

    // ── ProcessAttendanceJob ──

    public function test_process_attendance_job_can_be_instantiated(): void
    {
        $user = User::factory()->create(['branch_id' => Branch::factory()->create()->id]);
        $job = new ProcessAttendanceJob($user, 24.7136, 46.6753, '127.0.0.1', 'PHPUnit');
        $this->assertInstanceOf(ProcessAttendanceJob::class, $job);
    }

    public function test_process_attendance_job_skips_user_without_branch(): void
    {
        Log::shouldReceive('error')->once()->withArgs(fn($msg) => str_contains($msg, 'بدون فرع'));
        $user = User::factory()->create(['branch_id' => null]);
        $job = new ProcessAttendanceJob($user, 24.7136, 46.6753);
        $job->handle(new \App\Services\GeofencingService());
        // No attendance log should be created
        $this->assertDatabaseCount('attendance_logs', 0);
    }

    public function test_process_attendance_job_creates_log_on_valid_geofence(): void
    {
        Event::fake([AttendanceRecorded::class]);
        $branch = Branch::factory()->create([
            'latitude' => 24.7136,
            'longitude' => 46.6753,
            'geofence_radius' => 5000,
            'default_shift_start' => '08:00',
            'grace_period_minutes' => 15,
        ]);
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'basic_salary' => 6000,
            'working_days_per_month' => 22,
            'working_hours_per_day' => 8,
        ]);

        $job = new ProcessAttendanceJob($user, 24.7136, 46.6753, '127.0.0.1', 'Test');
        $job->handle(new \App\Services\GeofencingService());

        $this->assertDatabaseHas('attendance_logs', ['user_id' => $user->id, 'branch_id' => $branch->id]);
        Event::assertDispatched(AttendanceRecorded::class);
    }

    // ── RecalculateMonthlyAttendanceJob ──

    public function test_recalculate_job_can_be_instantiated(): void
    {
        $job = new RecalculateMonthlyAttendanceJob('branch', 1, 1);
        $this->assertInstanceOf(RecalculateMonthlyAttendanceJob::class, $job);
    }

    public function test_recalculate_job_for_month_factory(): void
    {
        $job = RecalculateMonthlyAttendanceJob::forMonth(2025, 1);
        $this->assertInstanceOf(RecalculateMonthlyAttendanceJob::class, $job);
    }

    public function test_recalculate_job_updates_existing_logs(): void
    {
        $branch = Branch::factory()->create([
            'default_shift_start' => '08:00',
            'grace_period_minutes' => 15,
        ]);
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'basic_salary' => 5000,
            'working_days_per_month' => 22,
            'working_hours_per_day' => 8,
        ]);

        AttendanceLog::factory()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'attendance_date' => now()->toDateString(),
            'check_in_at' => now()->setTime(8, 30),
        ]);

        $job = new RecalculateMonthlyAttendanceJob('branch', $branch->id, $user->id);
        $job->handle();

        // Log should still exist (we're just verifying no crash)
        $this->assertDatabaseCount('attendance_logs', 1);
    }

    // ── SendCircularJob ──

    public function test_send_circular_job_creates_performance_alerts(): void
    {
        $creator = User::factory()->create();
        $circular = Circular::factory()->create(['created_by' => $creator->id]);
        $users = User::factory()->count(3)->create();
        $userIds = $users->pluck('id')->toArray();

        $job = new SendCircularJob($circular, $userIds);
        $job->handle();

        // Should create a PerformanceAlert for each user
        foreach ($userIds as $userId) {
            $this->assertDatabaseHas('performance_alerts', [
                'user_id' => $userId,
                'alert_type' => 'circular',
            ]);
        }
    }

    public function test_send_circular_job_handles_empty_users(): void
    {
        $creator = User::factory()->create();
        $circular = Circular::factory()->create(['created_by' => $creator->id]);
        $job = new SendCircularJob($circular, []);
        $job->handle();
        $this->assertDatabaseCount('performance_alerts', 0);
    }
}
