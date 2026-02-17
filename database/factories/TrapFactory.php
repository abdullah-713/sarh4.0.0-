<?php

namespace Database\Factories;

use App\Models\Trap;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrapFactory extends Factory
{
    protected $model = Trap::class;

    public function definition(): array
    {
        return [
            'name'          => fake()->word() . ' Trap',
            'slug'          => fake()->unique()->slug(2),
            'trap_type'     => fake()->randomElement(['phantom_page', 'fake_api', 'hidden_field', 'canary_token']),
            'description'   => fake()->sentence(),
            'payload'       => ['response' => 'ok', 'data' => []],
            'is_active'     => true,
            'severity_level'=> fake()->randomElement(['low', 'medium', 'high', 'critical']),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
