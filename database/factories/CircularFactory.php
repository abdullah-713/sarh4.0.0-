<?php

namespace Database\Factories;

use App\Models\Circular;
use Illuminate\Database\Eloquent\Factories\Factory;

class CircularFactory extends Factory
{
    protected $model = Circular::class;

    public function definition(): array
    {
        return [
            'title_ar'     => fake('ar_SA')->sentence(3),
            'title_en'     => fake()->sentence(3),
            'body_ar'      => fake('ar_SA')->paragraph(),
            'body_en'      => fake()->paragraph(),
            'priority'     => fake()->randomElement(['normal', 'important', 'urgent']),
            'target_scope' => 'all',
            'created_by'   => \App\Models\User::factory(),
            'requires_acknowledgment' => true,
            'published_at' => now(),
        ];
    }

    public function unpublished(): static
    {
        return $this->state(['published_at' => null]);
    }

    public function expired(): static
    {
        return $this->state(['expires_at' => now()->subDay()]);
    }
}
