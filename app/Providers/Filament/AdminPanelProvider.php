<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\CustomLogin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
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
 * SarhIndex v1.9.0 — لوحة الإدارة /admin
 *
 * Module 5: Corporate Brand Identity — Orange (#FF8C00), Black, White/Grey
 * Module 3: Stealth visibility via dynamic navigation
 * Module 4: Mobile-First responsive configuration
 *
 * متاحة فقط لـ security_level >= 4 أو is_super_admin.
 */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(CustomLogin::class)
            ->brandName(fn () => Setting::instance()->app_name)
            ->brandLogo(fn () => Setting::instance()->logo_url)
            ->brandLogoHeight('2.5rem')
            ->favicon(fn () => Setting::instance()->favicon_url)
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
                'danger'  => Color::Red,
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
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                // Dashboard is auto-discovered from App\Filament\Pages\Dashboard
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
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
                \App\Http\Middleware\EnsureAdminPanelAccess::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('120s')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => new HtmlString('<link rel="manifest" href="/manifest.json"><meta name="theme-color" content="#2AABEE">'),
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
