<?php

namespace App\Services;

use App\Models\SensorReading;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * SARH v4.1 — Anomaly Detector (محرك كشف التلاعب المتقدم)
 *
 * المستوى الثاني من كشف التلاعب:
 * - GPS/Sensor mismatch
 * - Battery drain patterns
 * - Replay attacks (قراءات مُعادة)
 * - Impossible frequencies
 */
class AnomalyDetector
{
    /**
     * التحليل الشامل للقراءة — يُستدعى من TelemetryController اختيارياً
     *
     * @return array<int, array{type: string, confidence: float, reason: string}>
     */
    public function analyze(User $user, SensorReading $reading): array
    {
        $anomalies = [];

        if ($this->checkDuplicateReadings($user, $reading)) {
            $anomalies[] = [
                'type'       => 'replay_attack',
                'confidence' => 0.95,
                'reason'     => 'نفس القراءة تتكرر (محاولة خداع)',
            ];
        }

        if ($this->checkImpossibleFrequency($reading)) {
            $anomalies[] = [
                'type'       => 'impossible_frequency',
                'confidence' => 0.98,
                'reason'     => 'تردد غير ممكن لإنسان (آلة)',
            ];
        }

        if ($this->checkBatteryDrainPattern($user)) {
            $anomalies[] = [
                'type'       => 'battery_drain_pattern',
                'confidence' => 0.85,
                'reason'     => 'نمط فجوات كبيرة بين القراءات (توفير متعمد)',
            ];
        }

        return $anomalies;
    }

    /**
     * كشف القراءات المكررة (Replay Attack)
     */
    private function checkDuplicateReadings(User $user, SensorReading $reading): bool
    {
        $recent = SensorReading::where('user_id', $user->id)
            ->where('created_at', '>', now()->subHour())
            ->where('id', '!=', $reading->id)
            ->latest()
            ->take(5)
            ->get();

        if ($recent->count() < 3) {
            return false;
        }

        $signature  = $this->createSignature($reading);
        $duplicates = 0;

        foreach ($recent as $old) {
            if ($this->createSignature($old) === $signature) {
                $duplicates++;
            }
        }

        return $duplicates >= 3;
    }

    /**
     * كشف الترددات المستحيلة بشرياً
     */
    private function checkImpossibleFrequency(SensorReading $reading): bool
    {
        return $reading->peak_frequency > 20
            && $reading->motion_signature !== 'mechanical';
    }

    /**
     * كشف نمط فجوات البطارية
     */
    private function checkBatteryDrainPattern(User $user): bool
    {
        $readings = SensorReading::where('user_id', $user->id)
            ->where('created_at', '>', now()->subDay())
            ->orderBy('created_at')
            ->pluck('created_at');

        if ($readings->count() < 10) {
            return false;
        }

        $gaps = [];
        $prev = null;

        foreach ($readings as $ts) {
            if ($prev) {
                $gaps[] = $prev->diffInMinutes($ts);
            }
            $prev = $ts;
        }

        if (empty($gaps)) {
            return false;
        }

        $avgGap       = array_sum($gaps) / count($gaps);
        $hasLargeGaps = max($gaps) > 30;

        return $hasLargeGaps && $avgGap > 15;
    }

    /**
     * إنشاء بصمة فريدة للقراءة (للمقارنة)
     */
    private function createSignature(SensorReading $reading): string
    {
        return md5(implode('|', [
            round((float) $reading->avg_accel_x, 2),
            round((float) $reading->avg_accel_y, 2),
            round((float) $reading->avg_accel_z, 2),
            round((float) $reading->peak_frequency, 1),
        ]));
    }
}
