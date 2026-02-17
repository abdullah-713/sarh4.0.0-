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
            'reading_type'     => 'motion',
            'motion_data'      => ['x' => 0.1, 'y' => 0.2, 'z' => 9.8],
            'orientation_data' => ['alpha' => 0, 'beta' => 0, 'gamma' => 0],
            'battery_level'    => 80,
            'recorded_at'      => now(),
        ]);

        $anomalies = $this->detector->analyze($reading);
        $this->assertIsArray($anomalies);
    }

    public function test_create_signature_returns_string(): void
    {
        $data = ['x' => 0.1, 'y' => 0.2, 'z' => 9.8];
        $sig = $this->detector->createSignature($data);
        $this->assertIsString($sig);
        $this->assertNotEmpty($sig);
    }
}
