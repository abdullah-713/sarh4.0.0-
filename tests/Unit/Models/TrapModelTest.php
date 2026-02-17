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

    public function test_trap_scope_for_level(): void
    {
        Trap::factory()->create(['target_levels' => [3, 4, 5]]);
        Trap::factory()->create(['target_levels' => [7, 8]]);
        $this->assertEquals(1, Trap::forLevel(3)->count());
        $this->assertEquals(1, Trap::forLevel(7)->count());
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
        // Model uses 0-100 scale and returns Arabic labels
        $low = TrapInteraction::factory()->create(['risk_score' => 10]);
        $medium = TrapInteraction::factory()->create(['risk_score' => 30]);
        $high = TrapInteraction::factory()->create(['risk_score' => 60]);
        $critical = TrapInteraction::factory()->create(['risk_score' => 80]);

        $this->assertEquals('منخفض', $low->risk_level);
        $this->assertEquals('متوسط', $medium->risk_level);
        $this->assertEquals('مرتفع', $high->risk_level);
        $this->assertEquals('حرج', $critical->risk_level);
    }

    public function test_trap_interaction_risk_color(): void
    {
        $interaction = TrapInteraction::factory()->create(['risk_score' => 80]);
        $this->assertEquals('danger', $interaction->risk_color);
    }

    public function test_trap_interaction_scope_high_risk(): void
    {
        // Default threshold is 50.0
        TrapInteraction::factory()->create(['risk_score' => 70]);
        TrapInteraction::factory()->create(['risk_score' => 30]);
        $this->assertEquals(1, TrapInteraction::highRisk()->count());
    }

    public function test_trap_unique_interactions_count(): void
    {
        $trap = Trap::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        TrapInteraction::factory()->create(['trap_id' => $trap->id, 'user_id' => $user1->id]);
        TrapInteraction::factory()->create(['trap_id' => $trap->id, 'user_id' => $user2->id]);
        $this->assertEquals(2, $trap->unique_interactions_count);
    }

    public function test_trap_average_risk_score(): void
    {
        $trap = Trap::factory()->create();
        TrapInteraction::factory()->create(['trap_id' => $trap->id, 'risk_score' => 40]);
        TrapInteraction::factory()->create(['trap_id' => $trap->id, 'risk_score' => 60]);
        $this->assertEquals(50.0, $trap->average_risk_score);
    }
}
