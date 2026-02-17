<?php

namespace Tests\Unit\Models;

use App\Models\AnomalyLog;
use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\SensorReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnomalyLogTest extends TestCase
{
    use RefreshDatabase;

    private function createAnomaly(array $attrs = []): AnomalyLog
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

        return AnomalyLog::create(array_merge([
            'user_id'           => $user->id,
            'sensor_reading_id' => $reading->id,
            'anomaly_type'      => 'location_mismatch',
            'confidence'        => 0.95,
            'context_data'      => ['test' => true],
            'is_reviewed'       => false,
        ], $attrs));
    }

    public function test_belongs_to_user(): void
    {
        $anomaly = $this->createAnomaly();
        $this->assertInstanceOf(User::class, $anomaly->user);
    }

    public function test_belongs_to_reviewer(): void
    {
        $reviewer = User::factory()->create();
        $anomaly = $this->createAnomaly(['reviewed_by' => $reviewer->id]);
        $this->assertEquals($reviewer->id, $anomaly->reviewer->id);
    }

    public function test_scope_pending(): void
    {
        $this->createAnomaly(['is_reviewed' => false]);
        $this->createAnomaly(['is_reviewed' => true]);
        $this->assertEquals(1, AnomalyLog::pending()->count());
    }

    public function test_scope_reviewed(): void
    {
        $this->createAnomaly(['is_reviewed' => true]);
        $this->createAnomaly(['is_reviewed' => false]);
        $this->assertEquals(1, AnomalyLog::reviewed()->count());
    }

    public function test_scope_high_confidence(): void
    {
        $this->createAnomaly(['confidence' => 0.95]);
        $this->createAnomaly(['confidence' => 0.5]);
        $this->assertEquals(1, AnomalyLog::highConfidence(0.9)->count());
    }

    public function test_mark_reviewed(): void
    {
        $anomaly = $this->createAnomaly(['is_reviewed' => false]);
        $reviewer = User::factory()->create();
        $anomaly->markReviewed($reviewer->id);
        $anomaly->refresh();
        $this->assertTrue($anomaly->is_reviewed);
        $this->assertEquals($reviewer->id, $anomaly->reviewed_by);
        $this->assertNotNull($anomaly->reviewed_at);
    }

    public function test_readable_type_attribute(): void
    {
        $types = [
            'location_mismatch'    => 'تناقض الموقع والنشاط',
            'perfect_signal'       => 'إشارة مثالية (آلة)',
            'no_motion_timeout'    => 'ثبات طويل',
            'frequency_mismatch'   => 'تردد غير متوافق',
            'replay_attack'        => 'قراءات مُعادة',
            'impossible_frequency' => 'تردد مستحيل بشرياً',
            'unknown_type'         => 'شذوذ غير مصنف',
        ];

        foreach ($types as $type => $expected) {
            $anomaly = $this->createAnomaly(['anomaly_type' => $type]);
            $this->assertEquals($expected, $anomaly->readable_type);
        }
    }

    public function test_casts_context_data_as_array(): void
    {
        $anomaly = $this->createAnomaly(['context_data' => ['key' => 'value']]);
        $anomaly->refresh();
        $this->assertIsArray($anomaly->context_data);
        $this->assertEquals('value', $anomaly->context_data['key']);
    }
}
