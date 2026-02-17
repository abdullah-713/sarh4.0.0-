<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

/**
 * SarhIndex v1.9.0 — الصفحة الرئيسية لبوابة الموظفين
 *
 * تعرض لوحة معلومات مخصصة للموظف مع تحية حسب الوقت.
 */
class EmployeeDashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = 3;

    protected static string $routePath = '/';

    protected static string $view = 'filament.app.pages.employee-dashboard';

    public static function getNavigationLabel(): string
    {
        return __('employee.home');
    }

    public function getTitle(): string
    {
        return __('employee.home');
    }

    public function getHeading(): string
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $name = app()->getLocale() === 'ar'
            ? ($user?->name_ar ?? $user?->name ?? __('employee.employee'))
            : ($user?->name ?? $user?->name_ar ?? __('employee.employee'));
        $hour = (int) now()->format('H');

        $greeting = match (true) {
            $hour >= 5 && $hour < 12   => __('employee.greeting_morning'),
            $hour >= 12 && $hour < 17  => __('employee.greeting_afternoon'),
            $hour >= 17 && $hour < 21  => __('employee.greeting_evening'),
            default                     => __('employee.greeting_default'),
        };

        return app()->getLocale() === 'ar'
            ? "{$greeting}، {$name}"
            : "{$greeting}, {$name}";
    }

    public function getSubheading(): ?string
    {
        return __('employee.welcome_subtitle');
    }

    public function getWidgets(): array
    {
        return [];
    }
}
