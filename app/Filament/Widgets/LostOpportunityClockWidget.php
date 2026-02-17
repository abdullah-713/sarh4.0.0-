<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\Widget;

class LostOpportunityClockWidget extends Widget
{
    protected static string $view = 'filament.widgets.lost-opportunity-clock';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = -3;

    /**
     * Auto-refresh every 60 seconds.
     */
    protected static ?string $pollingInterval = null;

    public array $clockData = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $service = app(AnalyticsService::class);
        $this->clockData = $service->getLostOpportunityClock();
    }

    protected function getViewData(): array
    {
        return [
            'data' => $this->clockData,
        ];
    }
}
