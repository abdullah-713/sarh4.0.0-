<?php

return [
    /*
    |--------------------------------------------------------------------------
    | المجلدات المراقبة
    |--------------------------------------------------------------------------
    | المسارات النسبية من جذر المشروع التي سيراقبها الجاسوس.
    */
    'paths_to_watch' => [
        'app',
        'config',
        'database',
        'resources',
        'routes',
        'public',
    ],

    /*
    |--------------------------------------------------------------------------
    | الأنماط المستثناة
    |--------------------------------------------------------------------------
    | ملفات ومجلدات يتم تجاهلها أثناء المراقبة.
    */
    'ignored_paths' => [
        'node_modules',
        '.git',
        'storage',
        'vendor',
        'public/build',
        'public/hot',
        '.idea',
        '.vscode',
    ],

    /*
    |--------------------------------------------------------------------------
    | الامتدادات المراقبة
    |--------------------------------------------------------------------------
    | فقط هذه الامتدادات سيتم تتبعها.
    */
    'watched_extensions' => [
        'php',
        'blade.php',
        'js',
        'ts',
        'css',
        'json',
        'md',
    ],

    /*
    |--------------------------------------------------------------------------
    | فترة الاستطلاع (بالثواني)
    |--------------------------------------------------------------------------
    | المدة بين كل دورة مسح. قيمة أقل = استجابة أسرع لكن حمل أعلى.
    */
    'poll_interval' => (int) env('FILE_WATCHER_POLL_INTERVAL', 3),

    /*
    |--------------------------------------------------------------------------
    | تحديث الوثائق تلقائياً
    |--------------------------------------------------------------------------
    | عند true، يستدعي sarh:auto-document عند اكتشاف تغييرات مؤثرة.
    */
    'auto_update_docs' => (bool) env('FILE_WATCHER_AUTO_DOCS', true),

    /*
    |--------------------------------------------------------------------------
    | الكتابة في CHANGELOG.md
    |--------------------------------------------------------------------------
    */
    'changelog_enabled' => true,
    'changelog_path'    => 'docs/CHANGELOG.md',

    /*
    |--------------------------------------------------------------------------
    | الكتابة في قاعدة البيانات
    |--------------------------------------------------------------------------
    */
    'database_logging' => true,

    /*
    |--------------------------------------------------------------------------
    | الحد الأقصى لسجلات CHANGELOG (لمنع تضخم الملف)
    |--------------------------------------------------------------------------
    | 0 = بلا حدود
    */
    'changelog_max_entries' => 500,

    /*
    |--------------------------------------------------------------------------
    | ملف تتبع آخر تشغيل (لمنع التكرار)
    |--------------------------------------------------------------------------
    */
    'state_file' => storage_path('file-watcher-state.json'),

    /*
    |--------------------------------------------------------------------------
    | ربط المجلدات بالوثائق (للتحديث التلقائي)
    |--------------------------------------------------------------------------
    | كل مسار → ملف الوثائق الذي يجب تحديثه عند تغييره.
    */
    'doc_mappings' => [
        'app/Models'                      => 'docs/03-database-models.md',
        'database/migrations'             => 'docs/03-database-models.md',
        'app/Policies'                    => 'docs/04-roles-permissions.md',
        'app/Filament/Resources'          => 'docs/05-filament-components.md',
        'app/Filament/Pages'              => 'docs/05-filament-components.md',
        'app/Filament/Widgets'            => 'docs/05-filament-components.md',
        'app/Filament/App'                => 'docs/05-filament-components.md',
        'app/Services/FinancialReportingService.php' => 'docs/06-financial-system.md',
        'app/Services/FormulaEngineService.php'      => 'docs/06-financial-system.md',
        'app/Services/AttendanceService.php'         => 'docs/07-attendance-system.md',
        'app/Services/GeofencingService.php'         => 'docs/07-attendance-system.md',
        'app/Services/AnalyticsService.php'          => 'docs/08-analytics-system.md',
        'app/Services/TelemetryService.php'          => 'docs/08-analytics-system.md',
        'app/Services/AnomalyDetector.php'           => 'docs/08-analytics-system.md',
        'app/Livewire'                               => 'docs/09-communication-system.md',
        'app/Services/TrapResponseService.php'       => 'docs/12-trap-system.md',
        'app/Console/Commands'                       => 'docs/13-commands-services.md',
        'app/Services'                               => 'docs/13-commands-services.md',
        'app/Events'                                 => 'docs/13-commands-services.md',
        'app/Listeners'                              => 'docs/13-commands-services.md',
        'app/Jobs'                                   => 'docs/13-commands-services.md',
        'resources/views'                            => 'docs/14-ui-design.md',
        'resources/css'                              => 'docs/14-ui-design.md',
        'config'                                     => 'docs/15-deployment.md',
        'routes'                                     => 'docs/13-commands-services.md',
    ],
];
