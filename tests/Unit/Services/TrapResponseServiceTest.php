<?php

namespace Tests\Unit\Services;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\User;
use App\Services\TrapResponseService;
use App\Models\Trap;
use App\Models\TrapInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrapResponseServiceTest extends TestCase
{
    use RefreshDatabase;

    private TrapResponseService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TrapResponseService();
    }

    public function test_process_interaction_creates_record(): void
    {
        $trap = Trap::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        $result = $this->service->processInteraction($trap, [
            'path' => '/test', 'method' => 'GET',
        ]);

        $this->assertArrayHasKey('action', $result);
        $this->assertDatabaseHas('trap_interactions', [
            'trap_id' => $trap->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_calculate_risk_score_returns_float(): void
    {
        $trap = Trap::factory()->create();
        $user = User::factory()->create();

        $score = $this->service->calculateRiskScore($trap, $user, ['test' => 1]);

        $this->assertIsFloat($score);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(1, $score);
    }

    public function test_determine_action_returns_valid_action(): void
    {
        $validActions = ['log', 'alert', 'block', 'quarantine'];
        $action = $this->service->determineAction(0.5);
        $this->assertContains($action, $validActions);
    }

    public function test_get_fake_response_returns_array(): void
    {
        $trap = Trap::factory()->create(['trap_type' => 'fake_api']);
        $response = $this->service->getFakeResponse($trap);
        $this->assertIsArray($response);
    }

    public function test_get_statistics_returns_stats(): void
    {
        Trap::factory()->create();
        $stats = $this->service->getStatistics();
        $this->assertArrayHasKey('total_traps', $stats);
        $this->assertArrayHasKey('total_interactions', $stats);
    }
}
