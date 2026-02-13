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

    protected static ?string $pollingInterval = '60s';

    protected function getViewData(): array
    {
        try {
            [$startDate, $endDate] = $this->getFilterDates();

            $service = app(AnalyticsService::class);
            $matrixData = $service->calculateROIMatrix($startDate, $endDate);
        } catch (\Throwable $e) {
            $matrixData = [];
        }

        return [
            'matrix'      => $matrixData,
            'periodLabel' => $this->getPeriodLabel(),
        ];
    }
}
