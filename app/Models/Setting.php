<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * SARH v2.0 — Dynamic Settings Model
 *
 * Single-row pattern: Settings::instance() always returns the one settings record.
 * Caches for 1 hour. Clears cache on save.
 *
 * @property string $app_name
 * @property string $app_name_en
 * @property string $welcome_title
 * @property string|null $welcome_body
 * @property string|null $logo_path
 * @property string|null $favicon_path
 * @property string $pwa_name
 * @property string $pwa_short_name
 * @property string $pwa_theme_color
 * @property string $pwa_background_color
 * @property array  $logic_settings
 */
class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'app_name',
        'app_name_en',
        'welcome_title',
        'welcome_body',
        'logo_path',
        'favicon_path',
        'pwa_name',
        'pwa_short_name',
        'pwa_theme_color',
        'pwa_background_color',
        'logic_settings',
    ];

    protected function casts(): array
    {
        return [
            'logic_settings' => 'array',
        ];
    }

    /**
     * Default logic settings values.
     */
    public const DEFAULT_LOGIC_SETTINGS = [
        'loss_multiplier'          => 2.0,
        'default_geofence_radius'  => 100,
        'default_grace_period'     => 10,
        'overtime_multiplier'      => 1.5,
    ];

    /**
     * Get a specific logic setting with fallback to default.
     */
    public function getLogicSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->logic_settings ?? [];
        return $settings[$key] ?? self::DEFAULT_LOGIC_SETTINGS[$key] ?? $default;
    }

    /**
     * Get the singleton settings instance (cached for 1 hour).
     */
    public static function instance(): static
    {
        return Cache::remember('sarh_settings', 3600, function () {
            return static::firstOrCreate(['id' => 1], [
                'app_name'             => 'صرح الإتقان',
                'app_name_en'          => 'SARH Al-Itqan',
                'welcome_title'        => 'مرحباً بكم في صرح الإتقان',
                'pwa_name'             => 'صرح الإتقان',
                'pwa_short_name'       => 'صرح',
                'pwa_theme_color'      => '#FF8C00',
                'pwa_background_color' => '#ffffff',
                'logic_settings'       => self::DEFAULT_LOGIC_SETTINGS,
            ]);
        });
    }

    /**
     * Get a fresh (non-cached) instance for editing.
     */
    public static function freshInstance(): static
    {
        return static::firstOrCreate(['id' => 1], [
            'app_name'             => 'صرح الإتقان',
            'app_name_en'          => 'SARH Al-Itqan',
            'welcome_title'        => 'مرحباً بكم في صرح الإتقان',
            'pwa_name'             => 'صرح الإتقان',
            'pwa_short_name'       => 'صرح',
            'pwa_theme_color'      => '#FF8C00',
            'pwa_background_color' => '#ffffff',
            'logic_settings'       => self::DEFAULT_LOGIC_SETTINGS,
        ]);
    }

    /**
     * Clear cache on save so changes take effect immediately.
     */
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('sarh_settings');
        });
    }

    /**
     * Get the full URL for the logo.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            // Default to logo.png in public directory
            return asset('logo.png');
        }

        return Storage::disk('public')->url($this->logo_path);
    }

    /**
     * Get the full URL for the favicon.
     */
    public function getFaviconUrlAttribute(): ?string
    {
        if (!$this->favicon_path) {
            // Default to logo.png in public directory
            return asset('logo.png');
        }

        return Storage::disk('public')->url($this->favicon_path);
    }
}
