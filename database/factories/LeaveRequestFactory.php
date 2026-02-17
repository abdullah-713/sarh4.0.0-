<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+30 days');
        $days = rand(1, 5);
        return [
            'user_id'     => \App\Models\User::factory(),
            'leave_type'  => fake()->randomElement(['annual', 'sick', 'emergency', 'unpaid']),
            'start_date'  => $start,
            'end_date'    => (clone $start)->modify('+' . $days . ' days'),
            'total_days'  => $days,
            'reason'      => fake()->sentence(),
            'status'      => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state([
            'status' => 'approved',
            'approved_by' => \App\Models\User::factory(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(['status' => 'rejected']);
    }
}
