<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * SARH v2.0 — الإعدادات العامة (Level 10 فقط)
 *
 * لوحة تحكم مركزية لإدارة الهوية البصرية والنصوص والبيانات الوصفية.
 * تُحفظ في جدول settings وتُقرأ ديناميكياً عبر Setting::instance().
 */
class GeneralSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?int $navigationSort = 99;
    protected static string $view = 'filament.pages.general-settings';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return 'إعدادات النظام';
    }

    public static function getNavigationLabel(): string
    {
        return 'الإعدادات العامة';
    }

    public function getTitle(): string
    {
        return 'الإعدادات العامة';
    }

    /**
     * Level 10 / super_admin only.
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->is_super_admin || $user->security_level >= 10);
    }

    public function mount(): void
    {
        $settings = Setting::freshInstance();
        $this->form->fill($settings->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // ── Brand Identity ────────────────────────────────
                Forms\Components\Section::make('الهوية البصرية')
                    ->description('اسم التطبيق والشعار والأيقونة')
                    ->icon('heroicon-o-paint-brush')
                    ->schema([
                        Forms\Components\TextInput::make('app_name')
                            ->label('اسم التطبيق (عربي)')
                            ->required()
                            ->maxLength(100)
                            ->hintIcon('heroicon-m-information-circle', tooltip: __('install.app_name_hint')),

                        Forms\Components\TextInput::make('app_name_en')
                            ->label('اسم التطبيق (إنجليزي)')
                            ->required()
                            ->maxLength(100)
                            ->hintIcon('heroicon-m-information-circle', tooltip: __('install.app_name_en_hint')),

                        Forms\Components\FileUpload::make('logo_path')
                            ->label('الشعار (Logo)')
                            ->image()
                            ->imageEditor()
                            ->directory('branding')
                            ->disk('public')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'])
                            ->hintIcon('heroicon-m-information-circle', tooltip: __('install.logo_path_hint'))
                            ->helperText('يُفضل PNG أو SVG بخلفية شفافة — أقصى حجم 2MB'),

                        Forms\Components\FileUpload::make('favicon_path')
                            ->label('الأيقونة (Favicon)')
                            ->image()
                            ->directory('branding')
                            ->disk('public')
                            ->maxSize(512)
                            ->acceptedFileTypes(['image/png', 'image/x-icon', 'image/svg+xml'])
                            ->hintIcon('heroicon-m-information-circle', tooltip: __('install.favicon_path_hint'))
                            ->helperText('يُفضل PNG مربع 192×192 بكسل'),
                    ])->columns(['default' => 1, 'lg' => 2]),

                // ── Welcome Screen ────────────────────────────────
                Forms\Components\Section::make('شاشة الترحيب')
                    ->description('النصوص التي تظهر في لوحة التحكم')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('welcome_title')
                            ->label('عنوان الترحيب')
                            ->maxLength(200)
                            ->hintIcon('heroicon-m-information-circle', tooltip: __('install.welcome_title_hint')),

                        Forms\Components\Textarea::make('welcome_body')
                            ->label('نص الترحيب')
                            ->rows(3)
                            ->maxLength(500)
                            ->hintIcon('heroicon-m-information-circle', tooltip: __('install.welcome_body_hint')),
                    ])->columns(1),

                // ── PWA Metadata ──────────────────────────────────
                Forms\Components\Section::make('إعدادات التطبيق (PWA)')
                    ->description('بيانات التثبيت على الأجهزة المحمولة')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\TextInput::make('pwa_name')
                            ->label('اسم التطبيق الكامل')
                            ->maxLength(100)
                            ->hintIcon('heroicon-m-information-circle', tooltip: __('install.pwa_name_hint')),

                        Forms\Components\TextInput::make('pwa_short_name')
                            ->label('الاسم المختصر')
                            ->maxLength(20)
                            ->hintIcon('heroicon-m-information-circle', tooltip: __('install.pwa_short_name_hint')),

                        Forms\Components\ColorPicker::make('pwa_theme_color')
                            ->label('لون الثيم')
                            ->hintIcon('heroicon-m-information-circle', tooltip: __('install.pwa_theme_color_hint')),

                        Forms\Components\ColorPicker::make('pwa_background_color')
                            ->label('لون الخلفية')
                            ->hintIcon('heroicon-m-information-circle', tooltip: __('install.pwa_background_color_hint')),
                    ])->columns(['default' => 1, 'lg' => 2]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = Setting::freshInstance();
        $settings->fill($data);
        $settings->save();

        Notification::make()
            ->title('تم حفظ الإعدادات بنجاح')
            ->icon('heroicon-o-check-circle')
            ->success()
            ->send();
    }
}
