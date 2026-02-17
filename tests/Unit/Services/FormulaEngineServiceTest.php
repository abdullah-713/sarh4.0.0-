<?php

namespace Tests\Unit\Services;

use App\Services\FormulaEngineService;
use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormulaEngineServiceTest extends TestCase
{
    use RefreshDatabase;

    private FormulaEngineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FormulaEngineService();
    }

    public function test_get_available_variables_returns_array(): void
    {
        $vars = FormulaEngineService::getAvailableVariables();
        $this->assertIsArray($vars);
        $this->assertArrayHasKey('attendance', $vars);
        $this->assertArrayHasKey('delay_rate', $vars);
        $this->assertArrayHasKey('financial_loss', $vars);
    }

    public function test_resolve_variables_attendance_rate(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'basic_salary' => 5000,
            'working_days_per_month' => 22,
            'working_hours_per_day' => 8,
        ]);

        // 2 present days
        AttendanceLog::factory()->count(2)->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'status' => 'present',
            'attendance_date' => now()->subDay(),
        ]);

        $values = $this->service->resolveVariablesForUser(
            $user,
            now()->startOfMonth()->toDateString(),
            now()->toDateString(),
            ['attendance', 'delay_rate', 'on_time_rate']
        );

        $this->assertArrayHasKey('attendance', $values);
        $this->assertArrayHasKey('delay_rate', $values);
        $this->assertArrayHasKey('on_time_rate', $values);
        $this->assertIsFloat($values['attendance']);
    }

    public function test_resolve_variables_financial_loss(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'basic_salary' => 5000,
            'working_days_per_month' => 22,
            'working_hours_per_day' => 8,
        ]);

        AttendanceLog::factory()->late(30)->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'delay_cost' => 15.50,
        ]);

        $values = $this->service->resolveVariablesForUser(
            $user,
            now()->startOfMonth()->toDateString(),
            now()->toDateString(),
            ['financial_loss']
        );

        $this->assertEquals(15.50, $values['financial_loss']);
    }

    public function test_resolve_unknown_variable_returns_zero(): void
    {
        $user = User::factory()->create();

        $values = $this->service->resolveVariablesForUser(
            $user,
            now()->startOfMonth()->toDateString(),
            now()->toDateString(),
            ['nonexistent_variable']
        );

        $this->assertEquals(0.0, $values['nonexistent_variable']);
    }
}
