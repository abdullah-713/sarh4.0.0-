<?php

namespace Tests\Unit\Services;

use App\Models\SensorReading;
use App\Models\User;
use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Services\AnomalyDetector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnomalyDetectorTest extends TestCase
{
    use RefreshDatabase;

    private AnomalyDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new AnomalyDetector();
    }

    public function test_analyze_returns_array(): void
    {
        $user = User::factory()->create();
        $branch = Branch::factory()->create();
        $log = AttendanceLog::factory()->create(['user_id' => $user->id, 'branch_id' => $branch->id]);

        $reading = SensorReading::create([
            'user_id'          => $user->id,
            'attendance_log_id'=> $log->id,
            'avg_accel_x'      => 0.1,
            'avg_accel_y'      => 0.2,
            'avg_accel_z'      => 9.8,
            'variance_motion'  => 0.5,
            'peak_frequency'   => 2.0,
            'db_level'         => 40,
            'work_probability' => 0.5,
            'motion_signature' => 'walking',
            'is_anomaly'       => false,
            'sampling_window'  => 10,
        ]);

        $anomalies = $this->detector->analyze($user, $reading);
        $this->assertIsArray($anomalies);
    }

    public function test_create_signature_returns_string_via_reflection(): void
    {
        $user = User::factory()->create();
        $branch = Branch::factory()->create();
        $log = AttendanceLog::factory()->create(['user_id' => $user->id, 'branch_id' => $branch->id]);

        $reading = SensorReading::create([
            'user_id'          => $user->id,
            'attendance_log_id'=> $log->id,
            'avg_accel_x'      => 0.1,
            'avg_accel_y'      => 0.2,
            'avg_accel_z'      => 9.8,
            'variance_motion'  => 0.5,
            'peak_frequency'   => 2.0,
            'db_level'         => 40,
            'work_probability' => 0.5,
            'motion_signature' => 'walking',
            'is_anomaly'       => false,
            'sampling_window'  => 10,
        ]);

        $reflection = new \ReflectionMethod($this->detector, 'createSignature');
        $reflection->setAccessible(true);
        $sig = $reflection->invoke($this->detector, $reading);
        $this->assertIsString($sig);
        $this->assertNotEmpty($sig);
    }

    public function test_analyze_detects_impossible_frequency(): void
    {
        $user = User::factory()->create();
        $branch = Branch::factory()->create();
        $log = AttendanceLog::factory()->create(['user_id' => $user->id, 'branch_id' => $branch->id]);

        // peak_frequency > 20 and motion_signature != 'mechanical' → impossible
        $reading = SensorReading::create([
            'user_id'          => $user->id,
            'attendance_log_id'=> $log->id,
            'avg_accel_x'      => 0.1,
            'avg_accel_y'      => 0.2,
            'avg_accel_z'      => 9.8,
            'variance_motion'  => 0.5,
            'peak_frequency'   => 50.0,
            'db_level'         => 40,
            'work_probability' => 0.5,
            'motion_signature' => 'walking',
            'is_anomaly'       => false,
            'sampling_window'  => 10,
        ]);

        $anomalies = $this->detector->analyze($user, $reading);
        $types = array_column($anomalies, 'type');
        $this->assertContains('impossible_frequency', $types);
    }
}
