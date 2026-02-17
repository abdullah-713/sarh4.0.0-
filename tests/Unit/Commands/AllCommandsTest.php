<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\GenerateDailyAnalyticsCommand;
use App\Console\Commands\GeneratePayrollCommand;
use App\Console\Commands\SarhInstallCommand;
use App\Console\Commands\AutoDocumentCommand;
use App\Console\Commands\CalculateDailyTelemetryStats;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sarh_install_command_registered(): void
    {
        // sarh:install is interactive; just verify it's registered
        $this->assertArrayHasKey('sarh:install', \Illuminate\Support\Facades\Artisan::all());
    }

    public function test_auto_document_command_registered(): void
    {
        $this->artisan('sarh:auto-document')
            ->assertSuccessful();
    }

    public function test_analytics_command_runs_without_branches(): void
    {
        // No branches → should still succeed
        $this->artisan('sarh:analytics')
            ->assertSuccessful();
    }

    public function test_analytics_command_with_date_option(): void
    {
        Branch::factory()->create();
        $this->artisan('sarh:analytics', ['--date' => now()->toDateString()])
            ->assertSuccessful();
    }

    public function test_payroll_command_runs(): void
    {
        $this->artisan('sarh:payroll')
            ->assertSuccessful();
    }

    public function test_payroll_command_with_branch(): void
    {
        $branch = Branch::factory()->create();
        $this->artisan('sarh:payroll', ['--branch' => $branch->id])
            ->assertSuccessful();
    }

    public function test_telemetry_stats_command_registered(): void
    {
        $this->artisan('telemetry:daily-stats')
            ->assertSuccessful();
    }
}
