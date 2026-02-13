<?php

namespace App\Services;

use App\Events\AnomalyDetected;
use App\Models\AnomalyLog;
use App\Models\AttendanceLog;
use App\Models\SensorReading;
use App\Models\User;
use App\Models\WorkRestStat;
use Carbon\Carbon;

/**
 * SARH v4.1 — Telemetry Service (خدمة تحليل بيانات الحساسات)
 *
 * المنطق الرئيسي لاستقبال قراءات الموبايل ومعالجتها:
 * 1. حساب احتمال العمل (work_probability)
 * 2. تصنيف بصمة الحركة (motion_signature)
 * 3. كشف الشذوذ / التلاعب
 * 4. تحديث الإحصائيات اليومية
 */
class TelemetryService
{
    public function __construct(
        private AnomalyDetector $anomalyDetector,
    ) {}

    /**
     * معالجة قراءة جديدة من الموبايل
     */
    public function processReading(array $data, User $user, AttendanceLog $log): SensorReading
    {
        // 1. حساب احتمال العمل
        $workProbability = $this->calculateWorkProbability($data);

        // 2. تصنيف بصمة الحركة
        $motionSignature = $this->classifyMotionSignature($data);

        // 3. كشف الشذوذ
        $anomalyResult = $this->detectAnomaly($data, $user, $log);

        // 4. حفظ القراءة
        $reading = SensorReading::create([
            'user_id'            => $user->id,
            'attendance_log_id'  => $log->id,
            'avg_accel_x'        => $data['x'] ?? null,
            'avg_accel_y'        => $data['y'] ?? null,
            'avg_accel_z'        => $data['z'] ?? null,
            'variance_motion'    => $data['variance'] ?? null,
            'peak_frequency'     => $data['peak_freq'] ?? null,
            'db_level'           => $data['db'] ?? null,
            'work_probability'   => $workProbability,
            'motion_signature'   => $motionSignature,
            'is_anomaly'         => $anomalyResult['is_anomaly'],
            'anomaly_reason'     => $anomalyResult['reason'],
            'sampling_window'    => $data['window'] ?? 30,
            'processed_at'       => now(),
        ]);

        // 5. إذا كان في شذوذ → سجل + حدث أمني
        if ($anomalyResult['is_anomaly']) {
            $anomalyLogEntry = AnomalyLog::create([
                'user_id'           => $user->id,
                'sensor_reading_id' => $reading->id,
                'anomaly_type'      => $anomalyResult['type'],
                'confidence'        => $anomalyResult['confidence'],
                'context_data'      => [
                    'work_probability' => $workProbability,
                    'motion_signature' => $motionSignature,
                    'raw_data'         => $data,
                ],
            ]);

            event(new AnomalyDetected($user, $reading, $anomalyLogEntry));
        }

        return $reading;
    }

    /**
     * حساب احتمالية العمل بناءً على بيانات الحساسات
     *
     * المعادلة:
     * P(work) = w1 * norm(peak_freq) + w2 * norm(variance) + w3 * norm(db) + w4 * signature_weight
     */
    public function calculateWorkProbability(array $data): float
    {
        $peakFreq = $data['peak_freq'] ?? 0;
        $variance = $data['variance'] ?? 0;
        $dbLevel  = $data['db'] ?? 0;

        $weights = config('telemetry.weights', [
            'frequency' => 0.4,
            'variance'  => 0.3,
            'db_level'  => 0.2,
            'signature' => 0.1,
        ]);

        // تطبيع القيم بين 0 و 1
        $normFreq     = min($peakFreq / 100, 1);
        $normVariance = min($variance / 5, 1);
        $normDb       = min($dbLevel / 100, 1);
        $sigWeight    = $this->getSignatureWeight($data);

        $probability = ($weights['frequency'] * $normFreq)
                     + ($weights['variance'] * $normVariance)
                     + ($weights['db_level'] * $normDb)
                     + ($weights['signature'] * $sigWeight);

        return round(min(max($probability, 0), 1), 2);
    }

    /**
     * تصنيف بصمة الحركة
     */
    public function classifyMotionSignature(array $data): string
    {
        $peakFreq = $data['peak_freq'] ?? 0;
        $variance = $data['variance'] ?? 0;
        $dbLevel  = $data['db'] ?? 0;

        // mechanical: تردد عالي + ضجيج عالي + تباين عالي
        if ($peakFreq > 40 && $dbLevel > 70 && $variance > 2) {
            return 'mechanical';
        }

        // walking: تردد منخفض + تباين متوسط + ضجيج منخفض
        if ($peakFreq < 5 && $variance > 1 && $variance < 3 && $dbLevel < 60) {
            return 'walking';
        }

        // stationary: كل شي منخفض
        if ($peakFreq < 1 && $variance < 0.5 && $dbLevel < 40) {
            return 'stationary';
        }

        // suspicious: أنماط متضاربة
        if (($peakFreq > 50 && $dbLevel < 50) || ($peakFreq < 1 && $dbLevel > 80)) {
            return 'suspicious';
        }

        return 'unknown';
    }

    /**
     * كشف الشذوذ والتلاعب (المستوى الأول — القواعد الأساسية)
     */
    private function detectAnomaly(array $data, User $user, AttendanceLog $log): array
    {
        $peakFreq = $data['peak_freq'] ?? 0;
        $variance = $data['variance'] ?? 0;
        $dbLevel  = $data['db'] ?? 0;

        // 1. إشارة مثالية بشكل غير طبيعي (آلة)
        $freqStability = $data['freq_stability'] ?? 1.0;
        if ($freqStability < 0.01 && $peakFreq > 20) {
            return [
                'is_anomaly'  => true,
                'type'        => 'perfect_signal',
                'reason'      => 'إشارة اهتزاز مثالية = آلة وليس إنسان',
                'confidence'  => 0.98,
            ];
        }

        // 2. تردد مستحيل لإنسان (> 20Hz يدوياً)
        if ($peakFreq > 20 && $this->classifyMotionSignature($data) !== 'mechanical') {
            return [
                'is_anomaly'  => true,
                'type'        => 'impossible_frequency',
                'reason'      => 'تردد غير ممكن لإنسان',
                'confidence'  => 0.95,
            ];
        }

        // 3. تردد عالي في مكان هادئ (اهتزاز بدون ضجيج)
        if ($peakFreq > 60 && $dbLevel < 50) {
            return [
                'is_anomaly'  => true,
                'type'        => 'frequency_mismatch',
                'reason'      => 'اهتزاز عالي في مكان هادئ',
                'confidence'  => 0.92,
            ];
        }

        // 4. عدم حركة لفترة طويلة
        if ($variance < 0.01 && $this->hasConsecutiveStationary($user, $log)) {
            return [
                'is_anomaly'  => true,
                'type'        => 'no_motion_timeout',
                'reason'      => 'الموبايل ثابت لأكثر من 10 دقائق',
                'confidence'  => 0.85,
            ];
        }

        return [
            'is_anomaly'  => false,
            'type'        => null,
            'reason'      => null,
            'confidence'  => 0,
        ];
    }

    /**
     * تحديث الإحصائيات اليومية — يُشغّل نهاية اليوم أو عند الطلب
     */
    public function calculateDailyStats(User $user, Carbon $date): WorkRestStat
    {
        $readings = SensorReading::where('user_id', $user->id)
            ->whereHas('attendanceLog', fn ($q) => $q->whereDate('attendance_date', $date))
            ->get();

        $totalReadings   = $readings->count();
        $workReadings    = $readings->where('work_probability', '>', 0.7)->count();
        $restReadings    = $readings->where('work_probability', '<', 0.3)->count();
        $anomalyReadings = $readings->where('is_anomaly', true)->count();

        $samplingSeconds = config('telemetry.sampling_window', 30);
        $workMinutes     = round($workReadings * $samplingSeconds / 60, 2);
        $restMinutes     = round($restReadings * $samplingSeconds / 60, 2);

        $totalMinutes    = $workMinutes + $restMinutes;
        $ratio           = $totalMinutes > 0 ? round(($workMinutes / $totalMinutes) * 100, 2) : 0;

        // حساب الخسارة المالية
        $expectedMinutes = $user->working_hours_per_day * 60;
        $lostMinutes     = max($expectedMinutes - $workMinutes, 0);
        $vpmLeak         = round($lostMinutes * $user->cost_per_minute, 2);

        $rating = $this->calculateRating($ratio, $anomalyReadings);

        return WorkRestStat::updateOrCreate(
            ['user_id' => $user->id, 'stat_date' => $date],
            [
                'total_readings'        => $totalReadings,
                'work_readings'         => $workReadings,
                'rest_readings'         => $restReadings,
                'anomaly_readings'      => $anomalyReadings,
                'work_minutes'          => $workMinutes,
                'rest_minutes'          => $restMinutes,
                'productivity_ratio'    => $ratio,
                'expected_work_minutes' => $expectedMinutes,
                'vpm_leak'              => $vpmLeak,
                'wasted_salary'         => $vpmLeak,
                'rating'                => $rating,
                'needs_review'          => in_array($rating, ['leaking', 'critical']),
            ]
        );
    }

    /**
     * حساب التقييم اليومي
     */
    private function calculateRating(float $ratio, int $anomalies): string
    {
        if ($ratio >= 80 && $anomalies === 0) return 'golden';
        if ($ratio >= 60 && $anomalies <= 1)  return 'normal';
        if ($ratio >= 40)                      return 'leaking';

        return 'critical';
    }

    private function getSignatureWeight(array $data): float
    {
        $signature = $this->classifyMotionSignature($data);

        return match ($signature) {
            'mechanical'  => 1.0,
            'walking'     => 0.6,
            'stationary'  => 0.1,
            'suspicious'  => 0.3,
            default       => 0.5,
        };
    }

    private function hasConsecutiveStationary(User $user, AttendanceLog $log): bool
    {
        return SensorReading::where('user_id', $user->id)
            ->where('attendance_log_id', $log->id)
            ->where('variance_motion', '<', 0.01)
            ->where('created_at', '>', now()->subMinutes(10))
            ->count() >= 3;
    }
}
