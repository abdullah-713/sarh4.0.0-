<?php

namespace Tests\Feature\Controllers;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\Trap;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ControllersTest extends TestCase
{
    use RefreshDatabase;

    // ── AttendanceController ──

    public function test_check_in_requires_auth(): void
    {
        $response = $this->postJson('/attendance/check-in', [
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);
        // Web auth middleware returns 302 redirect or 401
        $this->assertContains($response->status(), [401, 302, 419]);
    }

    public function test_check_in_validates_coordinates(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/attendance/check-in', []);
        $response->assertStatus(422);
    }

    public function test_check_in_success_with_valid_geofence(): void
    {
        $branch = Branch::factory()->create([
            'latitude' => 24.7136,
            'longitude' => 46.6753,
            'geofence_radius' => 5000,
            'default_shift_start' => '08:00',
            'grace_period_minutes' => 15,
        ]);
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'basic_salary' => 5000,
            'working_days_per_month' => 22,
            'working_hours_per_day' => 8,
        ]);

        $response = $this->actingAs($user)->postJson('/attendance/check-in', [
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['message', 'data']);
    }

    public function test_today_status_returns_null_when_not_checked_in(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->getJson('/attendance/today');
        $response->assertOk();
        $response->assertJsonPath('data', null);
    }

    public function test_today_status_returns_log_when_checked_in(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);
        AttendanceLog::factory()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'attendance_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->getJson('/attendance/today');
        $response->assertOk();
        $response->assertJsonStructure(['message', 'data' => ['id', 'status']]);
    }

    public function test_check_out_creates_checkout(): void
    {
        $branch = Branch::factory()->create([
            'latitude' => 24.7136,
            'longitude' => 46.6753,
            'geofence_radius' => 5000,
            'default_shift_start' => '08:00',
            'default_shift_end' => '16:00',
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
            'check_in_at' => now()->subHours(8),
            'check_out_at' => null,
        ]);

        $response = $this->actingAs($user)->postJson('/attendance/check-out', [
            'latitude' => 24.7136,
            'longitude' => 46.6753,
        ]);
        $response->assertOk();
    }

    // ── TrapController ──

    public function test_trap_trigger_requires_auth(): void
    {
        $response = $this->postJson('/traps/trigger', ['trap_code' => 'TEST']);
        $this->assertContains($response->status(), [401, 302, 419]);
    }

    public function test_trap_trigger_validates_trap_code(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/traps/trigger', []);
        $response->assertStatus(422);
    }

    public function test_trap_trigger_returns_success_for_inactive_trap(): void
    {
        $user = User::factory()->create();
        $trap = Trap::factory()->inactive()->create();

        $response = $this->actingAs($user)->postJson('/traps/trigger', [
            'trap_code' => $trap->trap_code,
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
    }

    public function test_trap_trigger_skips_level_10_users(): void
    {
        $user = User::factory()->create(['security_level' => 10]);
        $trap = Trap::factory()->create();

        $response = $this->actingAs($user)->postJson('/traps/trigger', [
            'trap_code' => $trap->trap_code,
        ]);

        $response->assertOk();
    }

    // ── TelemetryController ──

    public function test_telemetry_config_returns_settings(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->getJson('/telemetry/config');
        $response->assertOk();
        $response->assertJsonStructure(['sampling_window', 'push_interval_minutes', 'enabled']);
    }

    public function test_telemetry_push_requires_auth(): void
    {
        $response = $this->postJson('/telemetry/push', ['readings' => [['x' => 1]]]);
        $this->assertContains($response->status(), [401, 302, 419]);
    }

    public function test_telemetry_push_validates_readings(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/telemetry/push', []);
        $response->assertStatus(422);
    }

    public function test_telemetry_push_skipped_without_attendance_log(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/telemetry/push', [
            'readings' => [['x' => 1.0, 'y' => 2.0, 'z' => 3.0]],
        ]);
        $response->assertOk();
        $response->assertJsonPath('status', 'skipped');
    }
}
