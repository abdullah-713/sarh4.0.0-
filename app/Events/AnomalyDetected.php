<?php

namespace App\Events;

use App\Models\AnomalyLog;
use App\Models\SensorReading;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * SARH v4.1 — Anomaly Detected Event
 *
 * يُطلق عند كشف تلاعب في بيانات الحساسات.
 */
class AnomalyDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public SensorReading $reading,
        public AnomalyLog $anomalyLog,
    ) {}
}
