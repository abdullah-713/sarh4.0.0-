<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'name_ar'                => fake('ar_SA')->city(),
            'name_en'                => fake()->city(),
            'code'                   => 'BR-' . fake()->unique()->numerify('###'),
            'address_ar'             => fake('ar_SA')->address(),
            'address_en'             => fake()->address(),
            'city_ar'                => fake('ar_SA')->city(),
            'city_en'                => fake()->city(),
            'phone'                  => fake()->phoneNumber(),
            'email'                  => fake()->companyEmail(),
            'latitude'               => fake()->latitude(21.0, 26.0),
            'longitude'              => fake()->longitude(39.0, 50.0),
            'geofence_radius'        => 17,
            'default_shift_start'    => '08:00',
            'default_shift_end'      => '16:00',
            'grace_period_minutes'   => 5,
            'is_active'              => true,
            'monthly_salary_budget'  => 100000,
            'monthly_delay_losses'   => 0,
            'target_attendance_rate' => 95.00,
            'max_acceptable_loss_percent' => 5.00,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function withCoordinates(float $lat, float $lng): static
    {
        return $this->state(['latitude' => $lat, 'longitude' => $lng]);
    }
}
