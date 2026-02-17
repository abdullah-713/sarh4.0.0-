<?php

namespace Tests\Unit\Models;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceLogModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_user(): void
    {
        $log = AttendanceLog::factory()->create();
        $this->assertInstanceOf(User::class, $log->user);
    }

    public function test_belongs_to_branch(): void
    {
        $log = AttendanceLog::factory()->create();
        $this->assertInstanceOf(Branch::class, $log->branch);
    }

    public function test_calculate_financials(): void
    {
        $user = User::factory()->create(['basic_salary' => 8000, 'working_days_per_month' => 22, 'working_hours_per_day' => 8]);
        $log = AttendanceLog::factory()->create([
            'user_id' => $user->id,
            'delay_minutes' => 15,
            'early_leave_minutes' => 0,
            'overtime_minutes' => 0,
        ]);
        $log->calculateFinancials();
        $this->assertGreaterThan(0, $log->cost_per_minute);
        $this->assertGreaterThan(0, $log->delay_cost);
        $this->assertEquals(round(15 * $user->cost_per_minute, 2), $log->delay_cost);
    }

    public function test_evaluate_attendance_on_time(): void
    {
        $log = AttendanceLog::factory()->create([
            'attendance_date' => now()->toDateString(),
            'check_in_at' => now()->setTime(8, 3),
        ]);
        $log->evaluateAttendance('08:00', 5);
        $this->assertEquals('present', $log->status);
        $this->assertEquals(0, $log->delay_minutes);
    }

    public function test_evaluate_attendance_late(): void
    {
        $log = AttendanceLog::factory()->create([
            'attendance_date' => now()->toDateString(),
            'check_in_at' => now()->setTime(8, 20),
        ]);
        $log->evaluateAttendance('08:00', 5);
        $this->assertEquals('late', $log->status);
        $this->assertEquals(20, $log->delay_minutes);
    }

    public function test_evaluate_attendance_absent(): void
    {
        $log = AttendanceLog::factory()->create([
            'attendance_date' => now()->toDateString(),
            'check_in_at' => null,
        ]);
        $log->evaluateAttendance('08:00', 5);
        $this->assertEquals('absent', $log->status);
    }

    public function test_scope_for_date(): void
    {
        AttendanceLog::factory()->create(['attendance_date' => '2026-02-17']);
        AttendanceLog::factory()->create(['attendance_date' => '2026-02-16']);
        $this->assertEquals(1, AttendanceLog::forDate('2026-02-17')->count());
    }

    public function test_scope_late(): void
    {
        AttendanceLog::factory()->create(['status' => 'late']);
        AttendanceLog::factory()->create(['status' => 'present']);
        $this->assertEquals(1, AttendanceLog::late()->count());
    }

    public function test_scope_absent(): void
    {
        AttendanceLog::factory()->create(['status' => 'absent', 'check_in_at' => null]);
        AttendanceLog::factory()->create(['status' => 'present']);
        $this->assertEquals(1, AttendanceLog::absent()->count());
    }

    public function test_scope_with_delay_cost(): void
    {
        AttendanceLog::factory()->create(['delay_cost' => 15.00]);
        AttendanceLog::factory()->create(['delay_cost' => 0]);
        $this->assertEquals(1, AttendanceLog::withDelayCost()->count());
    }

    public function test_scope_total_delay_cost(): void
    {
        AttendanceLog::factory()->create(['delay_cost' => 10.00]);
        AttendanceLog::factory()->create(['delay_cost' => 20.50]);
        $total = AttendanceLog::query()->totalDelayCost();
        $this->assertEquals(30.50, $total);
    }

    public function test_overtime_value_calculated_at_1_5x(): void
    {
        $user = User::factory()->create(['basic_salary' => 8000, 'working_days_per_month' => 22, 'working_hours_per_day' => 8]);
        $log = AttendanceLog::factory()->create([
            'user_id' => $user->id,
            'delay_minutes' => 0,
            'early_leave_minutes' => 0,
            'overtime_minutes' => 60,
        ]);
        $log->calculateFinancials();
        $expected = round(60 * $user->cost_per_minute * 1.5, 2);
        $this->assertEquals($expected, $log->overtime_value);
    }
}
