<?php

namespace Tests\Unit\Models;

use App\Models\Branch;
use App\Models\Trap;
use App\Models\TrapInteraction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrapTest extends TestCase
{
    use RefreshDatabase;

    public function test_trap_has_many_interactions(): void
    {
        $trap = Trap::factory()->create();
        TrapInteraction::factory()->create(['trap_id' => $trap->id]);
        $this->assertEquals(1, $trap->interactions()->count());
    }

    public function test_trap_scope_active(): void
    {
        Trap::factory()->create(['is_active' => true]);
        Trap::factory()->create(['is_active' => false]);
        $this->assertEquals(1, Trap::active()->count());
    }

    public function test_trap_scope_by_type(): void
    {
        Trap::factory()->create(['trap_type' => 'phantom_page']);
        Trap::factory()->create(['trap_type' => 'fake_api']);
        $this->assertEquals(1, Trap::byType('phantom_page')->count());
    }

    public function test_trap_interaction_belongs_to_trap(): void
    {
        $interaction = TrapInteraction::factory()->create();
        $this->assertInstanceOf(Trap::class, $interaction->trap);
    }

    public function test_trap_interaction_belongs_to_user(): void
    {
        $interaction = TrapInteraction::factory()->create();
        $this->assertInstanceOf(User::class, $interaction->user);
    }

    public function test_trap_interaction_risk_level_attribute(): void
    {
        $low = TrapInteraction::factory()->create(['risk_score' => 0.2]);
        $medium = TrapInteraction::factory()->create(['risk_score' => 0.5]);
        $high = TrapInteraction::factory()->create(['risk_score' => 0.8]);
        $critical = TrapInteraction::factory()->create(['risk_score' => 0.95]);

        $this->assertEquals('low', $low->risk_level);
        $this->assertEquals('medium', $medium->risk_level);
        $this->assertEquals('high', $high->risk_level);
        $this->assertEquals('critical', $critical->risk_level);
    }

    public function test_trap_interaction_risk_color(): void
    {
        $interaction = TrapInteraction::factory()->create(['risk_score' => 0.95]);
        $this->assertEquals('danger', $interaction->risk_color);
    }

    public function test_trap_interaction_scope_high_risk(): void
    {
        TrapInteraction::factory()->create(['risk_score' => 0.9]);
        TrapInteraction::factory()->create(['risk_score' => 0.3]);
        $this->assertEquals(1, TrapInteraction::highRisk()->count());
    }
}
