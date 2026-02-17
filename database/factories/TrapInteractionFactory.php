<?php

namespace Database\Factories;

use App\Models\TrapInteraction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrapInteractionFactory extends Factory
{
    protected $model = TrapInteraction::class;

    public function definition(): array
    {
        return [
            'trap_id'          => \App\Models\Trap::factory(),
            'user_id'          => \App\Models\User::factory(),
            'ip_address'       => fake()->ipv4(),
            'user_agent'       => fake()->userAgent(),
            'metadata'         => ['path' => '/test', 'method' => 'GET'],
            'risk_score'       => fake()->randomFloat(2, 0, 100),
            'action_taken'     => fake()->randomElement(['logged', 'warned', 'escalated']),
            'interaction_count'=> 1,
        ];
    }
}
