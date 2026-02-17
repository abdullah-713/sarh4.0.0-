<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name_ar'       => fake('ar_SA')->word() . ' قسم',
            'name_en'       => fake()->word() . ' Department',
            'code'          => 'DEPT-' . fake()->unique()->numerify('###'),
            'branch_id'     => \App\Models\Branch::factory(),
            'is_active'     => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
