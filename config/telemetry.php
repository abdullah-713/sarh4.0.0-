<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Telemetry Configuration (إعدادات حساس الإنتاجية)
    |--------------------------------------------------------------------------
    */

    // مدة نافذة العينة الواحدة بالثواني
    'sampling_window' => (int) env('TELEMETRY_SAMPLING_WINDOW', 30),

    // الفاصل الزمني بين الإرسالات بالدقائق (random ± 2 min)
    'push_interval_minutes' => (int) env('TELEMETRY_PUSH_INTERVAL', 10),

    // أوزان معادلة احتمال العمل  P(work)
    'weights' => [
        'frequency' => (float) env('TELEMETRY_W_FREQ', 0.4),
        'variance'  => (float) env('TELEMETRY_W_VARIANCE', 0.3),
        'db_level'  => (float) env('TELEMETRY_W_DB', 0.2),
        'signature' => (float) env('TELEMETRY_W_SIG', 0.1),
    ],

    // حدود التصنيف
    'thresholds' => [
        'work_probability_high' => 0.7,   // فوق هذا = عمل
        'work_probability_low'  => 0.3,   // تحت هذا = راحة
        'anomaly_confidence'    => 0.85,   // حد الثقة لتسجيل شذوذ
    ],

    // كشف الثبات — كم قراءة متتالية = timeout
    'stationary_timeout_readings' => 3,   // 3 × 10 دقائق = ~30 دقيقة

    // حد التردد الأقصى للإنسان (Hz)
    'human_max_frequency' => 20,

    // الحد الأقصى لاستقرار التردد (أقل = إشارة آلة مثالية)
    'perfect_signal_threshold' => 0.01,

    // تعريفات مواقع الاستراحة [{lat, lng, radius_m}]
    // يمكن إضافتها من لوحة التحكم
    'break_rooms' => [],

];
