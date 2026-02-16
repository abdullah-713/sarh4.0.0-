<?php

use App\Jobs\RecalculateMonthlyAttendanceJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Jobs (v4.0)
|--------------------------------------------------------------------------
| إعادة حساب التقارير المالية — أول يوم من كل شهر الساعة 2 فجراً
| تنظيف طابور المهام — أسبوعياً
|--------------------------------------------------------------------------
*/
Schedule::job(RecalculateMonthlyAttendanceJob::forMonth(now()->year, now()->month))
    ->monthlyOn(1, '02:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->description('إعادة حساب التقارير المالية الشهرية');

Schedule::command('queue:flush')
    ->weekly()
    ->description('تنظيف طابور المهام الفاشلة');

/*
|--------------------------------------------------------------------------
| Analytics & Telemetry Schedule (v4.1)
|--------------------------------------------------------------------------
| ─ sarh:analytics → يومياً 23:50 — يولّد لقطات التحليلات
| ─ telemetry:daily-stats → يومياً 23:55 — إحصائيات العمل/الراحة
|--------------------------------------------------------------------------
*/
Schedule::command('sarh:analytics')
    ->dailyAt('23:50')
    ->withoutOverlapping()
    ->onOneServer()
    ->description('توليد لقطات التحليلات اليومية');

Schedule::command('telemetry:daily-stats')
    ->dailyAt('23:55')
    ->withoutOverlapping()
    ->onOneServer()
    ->description('حساب إحصائيات العمل والراحة اليومية');

