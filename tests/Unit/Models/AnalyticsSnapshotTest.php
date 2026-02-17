<?php

namespace Tests\Unit\Models;

use App\Models\AnalyticsSnapshot;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private function createSnapshot(array $attrs = []): AnalyticsSnapshot
    {
        $branch = Branch::factory()->create();
        return AnalyticsSnapshot::create(array_merge([
            'branch_id'          => $branch->id,
            'snapshot_date'      => now()->toDateString(),
            'period_type'        => 'daily',
            'total_employees'    => 10,
            'present_count'      => 8,
            'absent_count'       => 2,
            'late_count'         => 3,
            'attendance_rate'    => 80.00,
            'total_salary_cost'  => 50000,
            'total_losses'       => 2500,
            'delay_losses'       => 1500,
            'absence_losses'     => 1000,
            'early_leave_losses' => 0,
            'efficiency_score'   => 85.00,
        ], $attrs));
    }

    public function test_belongs_to_branch(): void
    {
        $snap = $this->createSnapshot();
        $this->assertInstanceOf(Branch::class, $snap->branch);
    }

    public function test_scope_daily(): void
    {
        $this->createSnapshot(['period_type' => 'daily']);
        $this->createSnapshot(['period_type' => 'weekly']);
        $this->assertEquals(1, AnalyticsSnapshot::daily()->count());
    }

    public function test_scope_weekly(): void
    {
        $this->createSnapshot(['period_type' => 'weekly']);
        $this->createSnapshot(['period_type' => 'daily']);
        $this->assertEquals(1, AnalyticsSnapshot::weekly()->count());
    }

    public function test_scope_monthly(): void
    {
        $this->createSnapshot(['period_type' => 'monthly']);
        $this->assertEquals(1, AnalyticsSnapshot::monthly()->count());
    }

    public function test_scope_for_branch(): void
    {
        $branch1 = Branch::factory()->create();
        $branch2 = Branch::factory()->create();
        $this->createSnapshot(['branch_id' => $branch1->id]);
        $this->createSnapshot(['branch_id' => $branch2->id]);
        $this->assertEquals(1, AnalyticsSnapshot::forBranch($branch1->id)->count());
    }

    public function test_scope_for_date_range(): void
    {
        $this->createSnapshot(['snapshot_date' => '2026-01-15']);
        $this->createSnapshot(['snapshot_date' => '2026-02-15']);
        $results = AnalyticsSnapshot::forDateRange('2026-01-01', '2026-01-31')->count();
        $this->assertEquals(1, $results);
    }

    public function test_get_loss_percentage(): void
    {
        $snap = $this->createSnapshot(['total_losses' => 2500, 'total_salary_cost' => 50000]);
        $this->assertEquals(5.0, $snap->getLossPercentage());
    }

    public function test_get_loss_percentage_zero_salary(): void
    {
        $snap = $this->createSnapshot(['total_losses' => 100, 'total_salary_cost' => 0]);
        $this->assertEquals(0, $snap->getLossPercentage());
    }

    public function test_is_above_threshold_true(): void
    {
        $snap = $this->createSnapshot(['total_losses' => 5001, 'total_salary_cost' => 50000]);
        $this->assertTrue($snap->isAboveThreshold(5.0));
    }

    public function test_is_above_threshold_false(): void
    {
        $snap = $this->createSnapshot(['total_losses' => 2000, 'total_salary_cost' => 50000]);
        $this->assertFalse($snap->isAboveThreshold(5.0));
    }

    public function test_casts_array_fields(): void
    {
        $snap = $this->createSnapshot([
            'hourly_checkin_distribution' => ['08' => 5, '09' => 3],
            'daily_pattern_data' => ['mon' => 10],
        ]);
        $snap->refresh();
        $this->assertIsArray($snap->hourly_checkin_distribution);
        $this->assertIsArray($snap->daily_pattern_data);
    }
}
