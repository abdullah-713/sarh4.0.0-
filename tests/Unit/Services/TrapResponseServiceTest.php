<?php

namespace Tests\Unit\Services;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\User;
use App\Services\TrapResponseService;
use App\Models\Trap;
use App\Models\TrapInteraction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
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
        Event::fake();

        $trap = Trap::factory()->create();
        $user = User::factory()->create();

        $request = Request::create('/traps/trigger', 'POST', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'TestAgent',
        ]);

        $interaction = $this->service->processInteraction($trap, $user, $request);

        $this->assertInstanceOf(TrapInteraction::class, $interaction);
        $this->assertDatabaseHas('trap_interactions', [
            'trap_id' => $trap->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_calculate_risk_score_via_reflection(): void
    {
        $reflection = new \ReflectionMethod($this->service, 'calculateRiskScore');
        $reflection->setAccessible(true);

        // BASE_RISK=10, count=1, weight=1.0 → 10 * 2^0 * 1.0 = 10
        $score = $reflection->invoke($this->service, 1, 1.0);
        $this->assertEquals(10.0, $score);

        // count=2, weight=2.0 → 10 * 2^1 * 2.0 = 40
        $score = $reflection->invoke($this->service, 2, 2.0);
        $this->assertEquals(40.0, $score);

        // count=4, weight=5.0 → 10 * 2^3 * 5.0 = 400 → capped at 100
        $score = $reflection->invoke($this->service, 4, 5.0);
        $this->assertEquals(100.0, $score);
    }

    public function test_determine_action_via_reflection(): void
    {
        $reflection = new \ReflectionMethod($this->service, 'determineAction');
        $reflection->setAccessible(true);

        $this->assertEquals('logged', $reflection->invoke($this->service, 10.0));
        $this->assertEquals('warned', $reflection->invoke($this->service, 50.0));
        $this->assertEquals('escalated', $reflection->invoke($this->service, 80.0));
    }

    public function test_get_fake_response_returns_array(): void
    {
        $trap = Trap::factory()->create(['fake_response' => ['status' => 'ok']]);
        $response = $this->service->getFakeResponse($trap);
        $this->assertIsArray($response);
        $this->assertEquals('ok', $response['status']);
    }

    public function test_get_statistics_returns_stats(): void
    {
        Trap::factory()->create();
        $stats = $this->service->getStatistics();
        $this->assertArrayHasKey('total_traps', $stats);
        $this->assertArrayHasKey('total_interactions', $stats);
        $this->assertArrayHasKey('active_traps', $stats);
        $this->assertGreaterThanOrEqual(1, $stats['total_traps']);
    }
}
