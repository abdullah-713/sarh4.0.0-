<?php

namespace App\Filament\Pages;

use App\Models\LossAlert;
use App\Models\EmployeePattern;
use App\Services\AnalyticsService;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class AnalyticsDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'الذكاء المؤسسي';

    protected static ?string $title = 'لوحة الذكاء المؤسسي';

    protected static ?string $navigationGroup = 'التحليلات';

    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.analytics-dashboard';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->is_super_admin || $user->security_level >= 10);
    }

    public array $clockData = [];
    public array $recentAlerts = [];
    public array $highRiskPatterns = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $service = app(AnalyticsService::class);
        $this->clockData = $service->getLostOpportunityClock();

        $this->recentAlerts = LossAlert::with('branch')
            ->unacknowledged()
            ->recent(7)
            ->orderByDesc('alert_date')
            ->limit(10)
            ->get()
            ->toArray();

        $this->highRiskPatterns = EmployeePattern::with(['user', 'branch'])
            ->active()
            ->highRisk()
            ->orderByDesc('frequency_score')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function runAnalyticsNow(): void
    {
        $service = app(AnalyticsService::class);
        $results = $service->runFullAnalysis();

        $successCount = collect($results)->where('status', 'success')->count();

        Notification::make()
            ->title('تم تشغيل التحليلات')
            ->body("تم تحليل {$successCount} فرع بنجاح")
            ->success()
            ->send();

        $this->loadData();
    }

    public function acknowledgeAlert(int $alertId): void
    {
        $alert = LossAlert::find($alertId);
        if ($alert) {
            $alert->acknowledge(auth()->id());
            Notification::make()
                ->title('تم الاطلاع على التنبيه')
                ->success()
                ->send();
            $this->loadData();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('runAnalytics')
                ->label('تشغيل التحليلات الآن')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->action('runAnalyticsNow'),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'clockData'        => $this->clockData,
            'recentAlerts'     => $this->recentAlerts,
            'highRiskPatterns' => $this->highRiskPatterns,
        ];
    }
}
