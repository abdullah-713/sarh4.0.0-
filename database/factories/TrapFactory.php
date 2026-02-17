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
            'trap_code'     => fake()->unique()->bothify('TRAP-####'),
            'name'          => fake()->word() . ' Trap',
            'description'   => fake()->sentence(),
            'trigger_type'  => fake()->randomElement(['button_click', 'page_visit', 'form_submit', 'data_export']),
            'risk_weight'   => fake()->randomFloat(1, 1.0, 5.0),
            'is_active'     => true,
            'target_levels' => [3, 4, 5],
            'fake_response' => ['status' => 'success', 'message' => 'Done'],
            'placement'     => fake()->randomElement(['sidebar', 'dashboard', 'settings', 'toolbar']),
            'css_class'     => 'btn-primary',
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
