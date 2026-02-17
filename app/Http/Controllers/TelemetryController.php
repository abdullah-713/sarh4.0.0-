<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\SensorReading;
use App\Services\AnomalyDetector;
use App\Services\TelemetryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * SarhIndex v4.1 — Telemetry Controller (استقبال بيانات الحساسات)
 *
 * POST /telemetry/push — يستقبل قراءة واحدة أو مجموعة من الموبايل
 * GET  /telemetry/config — يُرسل إعدادات العينة للتطبيق
 */
class TelemetryController extends Controller
{
    public function __construct(
        private TelemetryService $telemetryService,
        private AnomalyDetector $anomalyDetector,
    ) {}

    /**
     * POST /telemetry/push
     *
     * يستقبل بيانات الحساسات المعالجة من الموبايل (Edge Processing result)
     */
    public function push(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'readings'                   => 'required|array|min:1|max:20',
            'readings.*.x'               => 'nullable|numeric',
            'readings.*.y'               => 'nullable|numeric',
            'readings.*.z'               => 'nullable|numeric',
            'readings.*.variance'        => 'nullable|numeric|min:0',
            'readings.*.peak_freq'       => 'nullable|numeric|min:0',
            'readings.*.db'              => 'nullable|numeric|min:0|max:200',
            'readings.*.freq_stability'  => 'nullable|numeric|min:0|max:1',
            'readings.*.window'          => 'nullable|integer|min:5|max:120',
            'readings.*.timestamp'       => 'nullable|date',
        ]);

        // نجيب آخر attendance_log مفتوح لليوم
        $log = AttendanceLog::where('user_id', $user->id)
            ->whereDate('attendance_date', today())
            ->whereNotNull('check_in_at')
            ->latest()
            ->first();

        if (! $log) {
            return response()->json([
                'status'  => 'skipped',
                'message' => 'لا يوجد سجل حضور مفتوح اليوم',
            ], 200);
        }

        $processed = 0;
        $anomalies = 0;

        foreach ($validated['readings'] as $readingData) {
            try {
                $reading = $this->telemetryService->processReading($readingData, $user, $log);
                $processed++;

                if ($reading->is_anomaly) {
                    $anomalies++;
                }

                // تحليل متقدم (المستوى الثاني)
                $advancedAnomalies = $this->anomalyDetector->analyze($user, $reading);
                if (! empty($advancedAnomalies)) {
                    foreach ($advancedAnomalies as $adv) {
                        \App\Models\AnomalyLog::create([
                            'user_id'           => $user->id,
                            'sensor_reading_id' => $reading->id,
                            'anomaly_type'      => $adv['type'],
                            'confidence'        => $adv['confidence'],
                            'context_data'      => ['reason' => $adv['reason']],
                        ]);
                    }
                    $anomalies += count($advancedAnomalies);
                }
            } catch (\Throwable $e) {
                Log::warning('Telemetry processing failed', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                    'data'    => $readingData,
                ]);
            }
        }

        return response()->json([
            'status'    => 'ok',
            'processed' => $processed,
            'anomalies' => $anomalies,
        ]);
    }

    /**
     * GET /telemetry/config
     *
     * يُرسل إعدادات العينة للتطبيق (الفاصل الزمني، مدة النافذة، إلخ)
     */
    public function config(Request $request): JsonResponse
    {
        return response()->json([
            'sampling_window'       => config('telemetry.sampling_window', 30),
            'push_interval_minutes' => config('telemetry.push_interval_minutes', 10),
            'enabled'               => true,
        ]);
    }
}
