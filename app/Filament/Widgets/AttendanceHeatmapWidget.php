<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasDashboardFilter;
use App\Models\AnalyticsSnapshot;
use App\Models\Branch;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class AttendanceHeatmapWidget extends Widget
{
    use HasDashboardFilter;

    protected static string $view = 'filament.widgets.attendance-heatmap';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    protected static ?string $pollingInterval = null;

    public ?int $selectedBranch = null;
    public array $heatmapData = [];
    public array $branches = [];

    public function mount(): void
    {
        // تحديد النطاق حسب فرع المستخدم — level < 10 يرى فرعه فقط
        $user   = auth()->user();
        $scoped = $user && !$user->is_super_admin && $user->security_level < 10;

        $branchQuery = Branch::where('is_active', true);
        if ($scoped) $branchQuery->where('id', $user->branch_id);

        $this->branches = $branchQuery->pluck('name_ar', 'id')->toArray();
        $this->selectedBranch = array_key_first($this->branches);
    }

    public function updatedSelectedBranch(): void
    {
        // Re-render will trigger getViewData() with new branch
    }

    protected function getViewData(): array
    {
        if (!$this->selectedBranch) {
            return [
                'heatmap'     => [],
                'branches'    => $this->branches,
                'periodLabel' => $this->getPeriodLabel(),
            ];
        }

        $branch = Branch::find($this->selectedBranch);
        if (!$branch) {
            return [
                'heatmap'     => [],
                'branches'    => $this->branches,
                'periodLabel' => $this->getPeriodLabel(),
            ];
        }

        try {
            [$startDate, $endDate] = $this->getFilterDates();

            $service = app(AnalyticsService::class);
            $heatmapData = $service->generateHeatmapData($branch, $startDate, $endDate);
        } catch (\Throwable $e) {
            $heatmapData = [];
        }

        return [
            'heatmap'     => $heatmapData,
            'branches'    => $this->branches,
            'periodLabel' => $this->getPeriodLabel(),
        ];
    }
}
