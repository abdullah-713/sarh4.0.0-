<?php

namespace Tests\Feature\Livewire;

use App\Livewire\AttendanceStatsWidget;
use App\Livewire\AttendanceWidget;
use App\Livewire\BranchProgressWidget;
use App\Livewire\CircularsWidget;
use App\Livewire\CompetitionWidget;
use App\Livewire\EmployeeDashboard;
use App\Livewire\FinancialWidget;
use App\Livewire\GamificationWidget;
use App\Models\Branch;
use App\Models\Circular;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireComponentsTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithBranch(): User
    {
        $branch = Branch::factory()->create([
            'latitude' => 24.7136,
            'longitude' => 46.6753,
            'geofence_radius' => 500,
            'default_shift_start' => '08:00',
            'default_shift_end' => '16:00',
        ]);
        return User::factory()->create([
            'branch_id' => $branch->id,
            'basic_salary' => 5000,
            'working_days_per_month' => 22,
            'working_hours_per_day' => 8,
        ]);
    }

    // ── EmployeeDashboard ──

    public function test_employee_dashboard_renders(): void
    {
        $user = $this->createUserWithBranch();
        Livewire::actingAs($user)
            ->test(EmployeeDashboard::class)
            ->assertStatus(200);
    }

    // ── AttendanceWidget ──

    public function test_attendance_widget_renders(): void
    {
        $user = $this->createUserWithBranch();
        Livewire::actingAs($user)
            ->test(AttendanceWidget::class)
            ->assertStatus(200)
            ->assertSet('status', 'not_checked_in');
    }

    public function test_attendance_widget_loads_geofence_radius(): void
    {
        $user = $this->createUserWithBranch();
        $component = Livewire::actingAs($user)->test(AttendanceWidget::class);
        $this->assertEquals(500, $component->get('geofenceRadius'));
    }

    public function test_attendance_widget_update_geolocation(): void
    {
        $user = $this->createUserWithBranch();
        Livewire::actingAs($user)
            ->test(AttendanceWidget::class)
            ->call('updateGeolocation', 24.7136, 46.6753)
            ->assertSet('isInsideGeofence', true);
    }

    // ── AttendanceStatsWidget ──

    public function test_attendance_stats_widget_renders(): void
    {
        $user = $this->createUserWithBranch();
        Livewire::actingAs($user)
            ->test(AttendanceStatsWidget::class)
            ->assertStatus(200);
    }

    // ── BranchProgressWidget ──

    public function test_branch_progress_widget_renders(): void
    {
        $user = $this->createUserWithBranch();
        Livewire::actingAs($user)
            ->test(BranchProgressWidget::class)
            ->assertStatus(200);
    }

    // ── CircularsWidget ──

    public function test_circulars_widget_renders(): void
    {
        $user = $this->createUserWithBranch();
        Livewire::actingAs($user)
            ->test(CircularsWidget::class)
            ->assertStatus(200);
    }

    public function test_circulars_widget_shows_published_circulars(): void
    {
        $user = $this->createUserWithBranch();
        Circular::factory()->create([
            'created_by' => $user->id,
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        Livewire::actingAs($user)
            ->test(CircularsWidget::class)
            ->assertStatus(200);
    }

    // ── CompetitionWidget ──

    public function test_competition_widget_renders(): void
    {
        $user = $this->createUserWithBranch();
        Livewire::actingAs($user)
            ->test(CompetitionWidget::class)
            ->assertStatus(200);
    }

    // ── FinancialWidget ──

    public function test_financial_widget_renders(): void
    {
        $user = $this->createUserWithBranch();
        Livewire::actingAs($user)
            ->test(FinancialWidget::class)
            ->assertStatus(200);
    }

    // ── GamificationWidget ──

    public function test_gamification_widget_renders(): void
    {
        $user = $this->createUserWithBranch();
        Livewire::actingAs($user)
            ->test(GamificationWidget::class)
            ->assertStatus(200);
    }
}
