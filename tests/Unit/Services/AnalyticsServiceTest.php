<?php

namespace Tests\Unit\Services;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\User;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private AnalyticsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AnalyticsService();
    }

    private function createBranchWithEmployees(int $count = 3): Branch
    {
        $branch = Branch::factory()->create([
            'latitude' => 24.7136,
            'longitude' => 46.6753,
            'geofence_radius' => 500,
            'default_shift_start' => '08:00',
            'default_shift_end' => '16:00',
            'monthly_salary_budget' => 50000,
            'grace_period_minutes' => 15,
            'target_attendance_rate' => 95,
        ]);

        for ($i = 0; $i < $count; $i++) {
            User::factory()->create([
                'branch_id' => $branch->id,
                'status' => 'active',
                'basic_salary' => 5000,
                'working_days_per_month' => 22,
                'working_hours_per_day' => 8,
            ]);
        }

        return $branch;
    }

    // ── VPM ──

    public function test_calculate_vpm_returns_zero_for_empty_branch(): void
    {
        $branch = Branch::factory()->create(['monthly_salary_budget' => 50000]);
        $vpm = $this->service->calculateVPM($branch);
        $this->assertEquals(0, $vpm);
    }

    public function test_calculate_vpm_returns_positive_value(): void
    {
        $branch = $this->createBranchWithEmployees(3);
        $vpm = $this->service->calculateVPM($branch);
        $this->assertGreaterThan(0, $vpm);
        $this->assertIsFloat($vpm);
    }

    // ── Total Loss ──

    public function test_calculate_total_loss_returns_zero_on_friday(): void
    {
        $branch = $this->createBranchWithEmployees();
        // Find next Friday
        $friday = Carbon::now()->next(Carbon::FRIDAY);
        $result = $this->service->calculateTotalLoss($branch, $friday);
        $this->assertEquals(0, $result['total_losses']);
    }

    public function test_calculate_total_loss_structure(): void
    {
        $branch = $this->createBranchWithEmployees();
        $result = $this->service->calculateTotalLoss($branch, Carbon::now()->next(Carbon::MONDAY));
        $this->assertArrayHasKey('delay_losses', $result);
        $this->assertArrayHasKey('absence_losses', $result);
        $this->assertArrayHasKey('early_leave_losses', $result);
        $this->assertArrayHasKey('total_losses', $result);
        $this->assertArrayHasKey('absent_count', $result);
        $this->assertArrayHasKey('present_count', $result);
        $this->assertArrayHasKey('late_count', $result);
        $this->assertArrayHasKey('total_delay_minutes', $result);
    }

    // ── Productivity Gap ──

    public function test_productivity_gap_zero_for_empty_branch(): void
    {
        $branch = Branch::factory()->create();
        $gap = $this->service->calculateProductivityGap($branch, Carbon::now());
        $this->assertEquals(0, $gap);
    }

    public function test_productivity_gap_returns_float(): void
    {
        $branch = $this->createBranchWithEmployees();
        $gap = $this->service->calculateProductivityGap($branch, Carbon::now()->next(Carbon::MONDAY));
        $this->assertIsFloat($gap);
    }

    // ── Efficiency Score ──

    public function test_efficiency_score_zero_for_empty_branch(): void
    {
        $branch = Branch::factory()->create();
        $score = $this->service->calculateEfficiencyScore($branch, now()->startOfMonth(), now());
        $this->assertEquals(0, $score);
    }

    public function test_efficiency_score_returns_float(): void
    {
        $branch = $this->createBranchWithEmployees();
        $score = $this->service->calculateEfficiencyScore($branch, now()->startOfMonth(), now());
        $this->assertIsFloat($score);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    // ── Heatmap ──

    public function test_heatmap_returns_hourly_and_daily(): void
    {
        $branch = $this->createBranchWithEmployees();
        $result = $this->service->generateHeatmapData($branch, now()->startOfMonth(), now());
        $this->assertArrayHasKey('hourly_distribution', $result);
        $this->assertArrayHasKey('daily_distribution', $result);
    }

    // ── ROI Matrix ──

    public function test_roi_matrix_returns_array(): void
    {
        $this->createBranchWithEmployees();
        $matrix = $this->service->calculateROIMatrix();
        $this->assertIsArray($matrix);
    }

    public function test_roi_matrix_contains_branch_data(): void
    {
        $branch = $this->createBranchWithEmployees();
        $matrix = $this->service->calculateROIMatrix(null, null, $branch->id);
        $this->assertNotEmpty($matrix);
        $this->assertArrayHasKey('branch_id', $matrix[0]);
        $this->assertArrayHasKey('roi', $matrix[0]);
        $this->assertArrayHasKey('quadrant', $matrix[0]);
        $this->assertContains($matrix[0]['quadrant'], ['star', 'cash_cow', 'potential', 'at_risk']);
    }

    // ── Daily Snapshot ──

    public function test_generate_daily_snapshot(): void
    {
        $branch = $this->createBranchWithEmployees();
        $snapshot = $this->service->generateDailySnapshot($branch);
        $this->assertDatabaseHas('analytics_snapshots', [
            'branch_id' => $branch->id,
            'period_type' => 'daily',
        ]);
    }

    // ── Personal Mirror ──

    public function test_personal_mirror_returns_structure(): void
    {
        $branch = $this->createBranchWithEmployees();
        $user = $branch->users()->first();
        $mirror = $this->service->getPersonalMirror($user);

        $this->assertArrayHasKey('performance_score', $mirror);
        $this->assertArrayHasKey('present_days', $mirror);
        $this->assertArrayHasKey('late_days', $mirror);
        $this->assertArrayHasKey('streak', $mirror);
        $this->assertArrayHasKey('message', $mirror);
    }

    // ── Lost Opportunity Clock ──

    public function test_lost_opportunity_clock_structure(): void
    {
        $this->createBranchWithEmployees();
        $clock = $this->service->getLostOpportunityClock();

        $this->assertArrayHasKey('total_loss_today', $clock);
        $this->assertArrayHasKey('total_delay_minutes', $clock);
        $this->assertArrayHasKey('total_absent', $clock);
        $this->assertArrayHasKey('branch_breakdown', $clock);
        $this->assertArrayHasKey('timestamp', $clock);
    }

    // ── Run Full Analysis ──

    public function test_run_full_analysis(): void
    {
        $branch = $this->createBranchWithEmployees();
        $results = $this->service->runFullAnalysis();

        $this->assertArrayHasKey($branch->id, $results);
        $this->assertEquals('success', $results[$branch->id]['status']);
    }

    // ── Pattern Detection ──

    public function test_detect_frequent_late_returns_collection(): void
    {
        $branch = $this->createBranchWithEmployees();
        $patterns = $this->service->detectFrequentLatePattern($branch);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $patterns);
    }

    public function test_detect_pre_holiday_returns_collection(): void
    {
        $branch = $this->createBranchWithEmployees();
        $patterns = $this->service->detectPreHolidayPattern($branch);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $patterns);
    }
}
