<?php

namespace App\Console\Commands;

use App\Services\AnalyticsService;
use Illuminate\Console\Command;

class GenerateDailyAnalyticsCommand extends Command
{
    protected $signature = 'sarh:analytics {--date= : Run for a specific date (Y-m-d)}';

    protected $description = 'Generate daily analytics snapshots, detect patterns, and trigger alerts for all active branches';

    public function handle(AnalyticsService $service): int
    {
        $date = $this->option('date')
            ? \Carbon\Carbon::parse($this->option('date'))
            : now();

        $this->info("🔬 Running SarhIndex Analytics for {$date->toDateString()}...");
        $this->newLine();

        $results = $service->runFullAnalysis($date);

        $tableData = [];
        foreach ($results as $branchId => $result) {
            $tableData[] = [
                $branchId,
                $result['branch'] ?? '—',
                $result['status'],
                $result['snapshot'] ?? '—',
                $result['alerts'] ?? 0,
                $result['patterns'] ?? 0,
                $result['error'] ?? '—',
            ];
        }

        $this->table(
            ['Branch ID', 'Name', 'Status', 'Snapshot', 'Alerts', 'Patterns', 'Error'],
            $tableData
        );

        $successCount = collect($results)->where('status', 'success')->count();
        $errorCount   = collect($results)->where('status', 'error')->count();

        $this->newLine();
        $this->info("✅ Done: {$successCount} success, {$errorCount} errors");

        return $errorCount > 0 ? self::FAILURE : self::SUCCESS;
    }
}
