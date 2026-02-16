<?php

namespace App\Filament\Pages;

use App\Models\TrapInteraction;
use App\Services\TrapResponseService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;

class TrapAuditPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'الأمان';

    protected static ?string $navigationLabel = 'تدقيق الفخاخ';

    protected static ?string $title = 'لوحة تدقيق الفخاخ النفسية';

    protected static ?int $navigationSort = 92;

    protected static string $view = 'filament.pages.trap-audit-page';

    public array $stats = [];
    public $highRiskUsers;
    public $recentInteractions;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->is_super_admin || $user->security_level >= 10);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        $service = app(TrapResponseService::class);

        $this->stats = $service->getStatistics();
        $this->highRiskUsers = $service->getHighRiskUsers(10);
        $this->recentInteractions = TrapInteraction::with(['trap', 'user'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
    }
}
