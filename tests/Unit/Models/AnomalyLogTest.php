<?php

namespace Tests\Unit\Models;

use App\Models\AnomalyLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnomalyLogTest extends TestCase
{
    use RefreshDatabase;

    private function createAnomaly(array $attrs = []): AnomalyLog
    {
        return AnomalyLog::create(array_merge([
            'user_id'       => User::factory()->create()->id,
            'anomaly_type'  => 'location_mismatch',
            'confidence'    => 0.95,
            'context_data'  => ['test' => true],
            'is_reviewed'   => false,
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
