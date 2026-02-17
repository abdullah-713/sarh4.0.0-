<?php

namespace Database\Factories;

use App\Models\AttendanceLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceLogFactory extends Factory
{
    protected $model = AttendanceLog::class;

    public function definition(): array
    {
        return [
            'user_id'          => \App\Models\User::factory(),
            'branch_id'        => \App\Models\Branch::factory(),
            'attendance_date'  => now()->toDateString(),
            'check_in_at'      => now()->setTime(8, 0),
            'check_in_latitude'  => 24.7136,
            'check_in_longitude' => 46.6753,
            'check_in_distance_meters' => 5.0,
            'check_in_within_geofence' => true,
            'status'           => 'present',
            'delay_minutes'    => 0,
            'cost_per_minute'  => 0.76,
            'delay_cost'       => 0,
        ];
    }

    public function late(int $minutes = 20): static
    {
        return $this->state([
            'check_in_at' => now()->setTime(8, $minutes),
            'status'      => 'late',
            'delay_minutes' => $minutes,
            'delay_cost'  => round($minutes * 0.76, 2),
        ]);
    }

    public function absent(): static
    {
        return $this->state([
            'check_in_at' => null,
            'status'       => 'absent',
        ]);
    }

    public function withCheckOut(): static
    {
        return $this->state([
            'check_out_at' => now()->setTime(16, 0),
            'worked_minutes' => 480,
        ]);
    }
}
