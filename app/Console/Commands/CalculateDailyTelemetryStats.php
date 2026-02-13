<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\TelemetryService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * SARH v4.1 — Calculate Daily Work/Rest Stats
 *
 * يُشغّل يومياً لتجميع بيانات الحساسات في work_rest_stats.
 * يمكن تشغيله يدوياً: php artisan telemetry:daily-stats
 * أو من المجدول: Schedule::command('telemetry:daily-stats')->dailyAt('23:30');
 */
class CalculateDailyTelemetryStats extends Command
{
    protected $signature = 'telemetry:daily-stats
                            {--date= : التاريخ (YYYY-MM-DD)، افتراضياً اليوم}
                            {--user= : معرف مستخدم محدد (اختياري)}';

    protected $description = 'حساب إحصائيات الإنتاجية اليومية من بيانات الحساسات';

    public function handle(TelemetryService $telemetryService): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : today();

        $userId = $this->option('user');

        $query = User::query()
            ->where('status', 'active')
            ->whereHas('sensorReadings', function ($q) use ($date) {
                $q->whereHas('attendanceLog', fn ($aq) => $aq->whereDate('attendance_date', $date));
            });

        if ($userId) {
            $query->where('id', $userId);
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->info("لا يوجد موظفين لديهم قراءات حساسات بتاريخ {$date->toDateString()}");
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $processed = 0;
        $critical  = 0;

        foreach ($users as $user) {
            $stat = $telemetryService->calculateDailyStats($user, $date);
            $processed++;

            if (in_array($stat->rating, ['leaking', 'critical'])) {
                $critical++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ تم حساب الإحصائيات لـ {$processed} موظف");

        if ($critical > 0) {
            $this->warn("⚠️ {$critical} موظف بحاجة للمراجعة (leaking/critical)");
        }

        return self::SUCCESS;
    }
}
