<?php

namespace App\Providers\Filament;

use App\Filament\App\Pages\EmployeeDashboard;
use App\Filament\App\Widgets\EmployeeWelcomeWidget;
use App\Filament\Pages\Auth\CustomLogin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Models\Setting;

/**
 * SarhIndex v1.9.0 — بوابة الموظفين /app
 *
 * Module 5: Employee Portal Emerald Theme
 * Module 4: Mobile-First responsive configuration
 *
 * بوابة مستقلة تماماً عن /admin، مخصصة للموظفين (security_level < 4).
 * تكتشف Resources/Pages/Widgets من مجلد Filament/App/ فقط.
 */
class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->path('app')
            ->login(CustomLogin::class)
            ->passwordReset()
            ->colors([
                // Telegram Blue Brand
                'primary' => [
                    50  => '#EBF7FE',
                    100 => '#D6EFFD',
                    200 => '#ADDFFB',
                    300 => '#84CFF9',
                    400 => '#5BBFF7',
                    500 => '#2AABEE',
                    600 => '#229ED9',
                    700 => '#1C96CC',
                    800 => '#167EB0',
                    900 => '#0D5E8A',
                    950 => '#073D5C',
                ],
                'danger'  => Color::Rose,
                'warning' => Color::Amber,
                'success' => Color::Emerald,
                'info'    => Color::Blue,
                'gray'    => [
                    50  => '#F9FAFB',
                    100 => '#F3F4F6',
                    200 => '#E5E7EB',
                    300 => '#D1D5DB',
                    400 => '#9CA3AF',
                    500 => '#6B7280',
                    600 => '#4B5563',
                    700 => '#374151',
                    800 => '#1F2937',
                    900 => '#111827',
                    950 => '#030712',
                ],
            ])
            ->font('Cairo')
            ->viteTheme('resources/css/filament/app/theme.css')
            ->brandName(fn () => Setting::instance()->app_name . ' — بوابة الموظفين')
            ->brandLogo(fn () => Setting::instance()->logo_url)
            ->brandLogoHeight('2.5rem')
            ->favicon(fn () => Setting::instance()->favicon_url)
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->discoverResources(
                in: app_path('Filament/App/Resources'),
                for: 'App\\Filament\\App\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/App/Pages'),
                for: 'App\\Filament\\App\\Pages'
            )
            ->discoverWidgets(
                in: app_path('Filament/App/Widgets'),
                for: 'App\\Filament\\App\\Widgets'
            )
            ->pages([
                EmployeeDashboard::class,
            ])
            ->widgets([
                EmployeeWelcomeWidget::class,
            ])
            ->authGuard('web')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('120s')
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => view('filament.app.partials.geolocation-script'),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => new HtmlString('<link rel="manifest" href="/manifest.json"><meta name="theme-color" content="#1E3A5F">'),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => view('filament.components.arabic-numerals'),
            )
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn () => view('filament.components.pwa-install-button'),
            );
    }
}
