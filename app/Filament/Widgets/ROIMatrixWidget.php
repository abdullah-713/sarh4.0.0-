<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasDashboardFilter;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class ROIMatrixWidget extends Widget
{
    use HasDashboardFilter;

    protected static string $view = 'filament.widgets.roi-matrix';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 5;

    protected static ?string $pollingInterval = null;

    protected function getViewData(): array
    {
        try {
            [$startDate, $endDate] = $this->getFilterDates();

            // تحديد النطاق حسب فرع المستخدم — level < 10 يرى فرعه فقط
            $user     = auth()->user();
            $branchId = ($user && !$user->is_super_admin && $user->security_level < 10)
                ? $user->branch_id
                : null;

            $service = app(AnalyticsService::class);
            $matrixData = $service->calculateROIMatrix($startDate, $endDate, $branchId);
        } catch (\Throwable $e) {
            $matrixData = [];
        }

        return [
            'matrix'      => $matrixData,
            'periodLabel' => $this->getPeriodLabel(),
        ];
    }
}
