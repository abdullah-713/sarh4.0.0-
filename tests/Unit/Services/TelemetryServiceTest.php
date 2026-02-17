<?php

namespace Tests\Unit\Services;

use App\Services\TelemetryService;
use App\Services\AnomalyDetector;
use Tests\TestCase;

class TelemetryServiceTest extends TestCase
{
    private TelemetryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TelemetryService(new AnomalyDetector());
    }

    public function test_calculate_work_probability_returns_float_between_0_and_1(): void
    {
        $probability = $this->service->calculateWorkProbability([
            'peak_freq' => 50,
            'variance' => 2.5,
            'db' => 60,
        ]);

        $this->assertIsFloat($probability);
        $this->assertGreaterThanOrEqual(0, $probability);
        $this->assertLessThanOrEqual(1, $probability);
    }

    public function test_work_probability_zero_for_empty_data(): void
    {
        $probability = $this->service->calculateWorkProbability([]);
        $this->assertEquals(0, $probability);
    }

    public function test_classify_motion_mechanical(): void
    {
        $sig = $this->service->classifyMotionSignature([
            'peak_freq' => 50,
            'variance' => 3.0,
            'db' => 80,
        ]);
        $this->assertEquals('mechanical', $sig);
    }

    public function test_classify_motion_stationary(): void
    {
        $sig = $this->service->classifyMotionSignature([
            'peak_freq' => 0.5,
            'variance' => 0.1,
            'db' => 20,
        ]);
        $this->assertEquals('stationary', $sig);
    }

    public function test_classify_motion_walking(): void
    {
        $sig = $this->service->classifyMotionSignature([
            'peak_freq' => 2,
            'variance' => 1.5,
            'db' => 40,
        ]);
        $this->assertEquals('walking', $sig);
    }

    public function test_classify_motion_suspicious(): void
    {
        $sig = $this->service->classifyMotionSignature([
            'peak_freq' => 60,
            'variance' => 0.5,
            'db' => 30,
        ]);
        $this->assertEquals('suspicious', $sig);
    }

    public function test_classify_motion_unknown(): void
    {
        $sig = $this->service->classifyMotionSignature([
            'peak_freq' => 10,
            'variance' => 1.0,
            'db' => 50,
        ]);
        $this->assertEquals('unknown', $sig);
    }
}
