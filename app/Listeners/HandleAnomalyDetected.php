<?php

namespace App\Listeners;

use App\Events\AnomalyDetected;
use App\Models\PerformanceAlert;
use Illuminate\Support\Facades\Log;

/**
 * SARH v4.1 — Anomaly Detected Listener
 *
 * يُنشئ تنبيه أداء عند اكتشاف تلاعب في بيانات الحساسات.
 */
class HandleAnomalyDetected
{
    public function handle(AnomalyDetected $event): void
    {
        try {
            PerformanceAlert::create([
                'user_id'    => $event->user->id,
                'alert_type' => 'anomaly_detected',
                'severity'   => $event->anomalyLog->severity ?? 'warning',
                'title_ar'   => 'تنبيه: نشاط غير طبيعي',
                'title_en'   => 'Alert: Anomalous Activity Detected',
                'message_ar' => "تم رصد نشاط غير طبيعي في بيانات الحساسات للموظف {$event->user->name}",
                'message_en' => "Anomalous sensor activity detected for employee {$event->user->name}",
                'trigger_data' => [
                    'anomaly_log_id'   => $event->anomalyLog->id,
                    'sensor_reading_id' => $event->reading->id,
                    'anomaly_type'     => $event->anomalyLog->anomaly_type ?? 'unknown',
                    'confidence'       => $event->anomalyLog->confidence ?? 0,
                ],
            ]);

            Log::info('HandleAnomalyDetected: تم إنشاء تنبيه أداء', [
                'user_id'       => $event->user->id,
                'anomaly_log_id' => $event->anomalyLog->id,
            ]);
        } catch (\Exception $e) {
            Log::error('HandleAnomalyDetected: فشل إنشاء التنبيه', [
                'user_id' => $event->user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
