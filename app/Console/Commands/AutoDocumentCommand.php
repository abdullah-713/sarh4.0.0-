<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionMethod;

class AutoDocumentCommand extends Command
{
    protected $signature = 'sarh:auto-document {--watch : Watch for changes and auto-update}';
    protected $description = 'تحديث التوثيق تلقائياً بناءً على الكود الفعلي';

    private array $stats = [
        'models' => 0,
        'controllers' => 0,
        'services' => 0,
        'resources' => 0,
        'widgets' => 0,
        'pages' => 0,
    ];

    public function handle(): int
    {
        $this->info('🔍 بدء مسح الكود وتحديث التوثيق...');

        // إنشاء/تحديث مجلد الوثائق الموحد
        $docsPath = base_path('docs');
        if (!File::exists($docsPath)) {
            File::makeDirectory($docsPath, 0755, true);
        }

        // مسح وتوثيق المكونات
        $this->scanModels();
        $this->scanControllers();
        $this->scanServices();
        $this->scanFilamentResources();
        $this->scanFilamentWidgets();
        $this->scanFilamentPages();
        $this->scanMigrations();
        $this->scanRoutes();
        $this->scanConfig();

        // إنشاء ملف الفهرس الرئيسي
        $this->generateIndexFile();

        // إنشاء ملف الإحصائيات
        $this->generateStatsFile();

        $this->newLine();
        $this->info('✅ تم تحديث جميع الوثائق بنجاح!');
        $this->table(
            ['المكون', 'العدد'],
            [
                ['Models', $this->stats['models']],
                ['Controllers', $this->stats['controllers']],
                ['Services', $this->stats['services']],
                ['Filament Resources', $this->stats['resources']],
                ['Filament Widgets', $this->stats['widgets']],
                ['Filament Pages', $this->stats['pages']],
            ]
        );

        return self::SUCCESS;
    }

    private function scanModels(): void
    {
        $this->info('📦 مسح Models...');
        $modelsPath = app_path('Models');
        $models = [];

        foreach (File::allFiles($modelsPath) as $file) {
            $className = 'App\\Models\\' . $file->getFilenameWithoutExtension();
            
            if (!class_exists($className)) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($className);
                $modelInfo = $this->extractModelInfo($reflection);
                $models[$className] = $modelInfo;
                $this->stats['models']++;
            } catch (\Exception $e) {
                $this->warn("تعذر معالجة: {$className}");
            }
        }

        // حفظ التوثيق
        $content = $this->generateModelsDoc($models);
        File::put(base_path('docs/AUTO_MODELS.md'), $content);
    }

    private function extractModelInfo(ReflectionClass $reflection): array
    {
        $instance = $reflection->newInstanceWithoutConstructor();
        
        return [
            'name' => $reflection->getShortName(),
            'table' => property_exists($instance, 'table') ? $instance->table : null,
            'fillable' => property_exists($instance, 'fillable') ? $instance->fillable : [],
            'hidden' => property_exists($instance, 'hidden') ? $instance->hidden : [],
            'casts' => property_exists($instance, 'casts') ? $instance->casts : [],
            'methods' => $this->extractPublicMethods($reflection),
            'relations' => $this->extractRelations($reflection),
            'traits' => array_map(fn($t) => $t->getShortName(), $reflection->getTraits()),
        ];
    }

    private function extractPublicMethods(ReflectionClass $reflection): array
    {
        $methods = [];
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class === $reflection->getName() && !$method->isConstructor()) {
                $methods[] = [
                    'name' => $method->getName(),
                    'parameters' => count($method->getParameters()),
                    'return_type' => $method->getReturnType()?->getName() ?? 'mixed',
                ];
            }
        }
        return $methods;
    }

    private function extractRelations(ReflectionClass $reflection): array
    {
        $relations = [];
        $source = file_get_contents($reflection->getFileName());
        
        // بحث بسيط عن علاقات Eloquent
        $patterns = [
            'hasMany' => '/public function (\w+)\(\).*?hasMany\((.*?)\)/s',
            'belongsTo' => '/public function (\w+)\(\).*?belongsTo\((.*?)\)/s',
            'belongsToMany' => '/public function (\w+)\(\).*?belongsToMany\((.*?)\)/s',
            'hasOne' => '/public function (\w+)\(\).*?hasOne\((.*?)\)/s',
        ];

        foreach ($patterns as $type => $pattern) {
            if (preg_match_all($pattern, $source, $matches)) {
                foreach ($matches[1] as $index => $relationName) {
                    $relations[] = [
                        'name' => $relationName,
                        'type' => $type,
                    ];
                }
            }
        }

        return $relations;
    }

    private function scanControllers(): void
    {
        $this->info('🎮 مسح Controllers...');
        $controllersPath = app_path('Http/Controllers');
        
        if (!File::exists($controllersPath)) {
            return;
        }

        $controllers = [];
        foreach (File::allFiles($controllersPath) as $file) {
            $this->stats['controllers']++;
            $controllers[] = $file->getFilenameWithoutExtension();
        }

        $content = "# Controllers\n\n";
        $content .= "**العدد الإجمالي:** " . count($controllers) . "\n\n";
        foreach ($controllers as $controller) {
            $content .= "- {$controller}\n";
        }

        File::put(base_path('docs/AUTO_CONTROLLERS.md'), $content);
    }

    private function scanServices(): void
    {
        $this->info('⚙️ مسح Services...');
        $servicesPath = app_path('Services');
        
        if (!File::exists($servicesPath)) {
            return;
        }

        $services = [];
        foreach (File::allFiles($servicesPath) as $file) {
            $this->stats['services']++;
            $services[] = $file->getFilenameWithoutExtension();
        }

        $content = "# Services\n\n";
        $content .= "**العدد الإجمالي:** " . count($services) . "\n\n";
        foreach ($services as $service) {
            $content .= "- {$service}\n";
        }

        File::put(base_path('docs/AUTO_SERVICES.md'), $content);
    }

    private function scanFilamentResources(): void
    {
        $this->info('📋 مسح Filament Resources...');
        $resourcesPath = app_path('Filament/Resources');
        
        if (!File::exists($resourcesPath)) {
            return;
        }

        $resources = [];
        foreach (File::files($resourcesPath) as $file) {
            if (str_ends_with($file, 'Resource.php')) {
                $this->stats['resources']++;
                $resources[] = $file->getFilenameWithoutExtension();
            }
        }

        $content = "# Filament Resources\n\n";
        $content .= "**العدد الإجمالي:** " . count($resources) . "\n\n";
        foreach ($resources as $resource) {
            $content .= "- {$resource}\n";
        }

        File::put(base_path('docs/AUTO_FILAMENT_RESOURCES.md'), $content);
    }

    private function scanFilamentWidgets(): void
    {
        $this->info('🧩 مسح Filament Widgets...');
        $widgetsPath = app_path('Filament/Widgets');
        
        if (!File::exists($widgetsPath)) {
            return;
        }

        $widgets = [];
        foreach (File::allFiles($widgetsPath) as $file) {
            $this->stats['widgets']++;
            $widgets[] = $file->getFilenameWithoutExtension();
        }

        $content = "# Filament Widgets\n\n";
        $content .= "**العدد الإجمالي:** " . count($widgets) . "\n\n";
        foreach ($widgets as $widget) {
            $content .= "- {$widget}\n";
        }

        File::put(base_path('docs/AUTO_FILAMENT_WIDGETS.md'), $content);
    }

    private function scanFilamentPages(): void
    {
        $this->info('📄 مسح Filament Pages...');
        $pagesPath = app_path('Filament/Pages');
        
        if (!File::exists($pagesPath)) {
            return;
        }

        $pages = [];
        foreach (File::allFiles($pagesPath) as $file) {
            $this->stats['pages']++;
            $pages[] = $file->getFilenameWithoutExtension();
        }

        $content = "# Filament Pages\n\n";
        $content .= "**العدد الإجمالي:** " . count($pages) . "\n\n";
        foreach ($pages as $page) {
            $content .= "- {$page}\n";
        }

        File::put(base_path('docs/AUTO_FILAMENT_PAGES.md'), $content);
    }

    private function scanMigrations(): void
    {
        $this->info('🗄️ مسح Migrations...');
        $migrationsPath = database_path('migrations');
        $migrations = File::allFiles($migrationsPath);

        $content = "# Database Migrations\n\n";
        $content .= "**العدد الإجمالي:** " . count($migrations) . "\n\n";
        
        foreach ($migrations as $migration) {
            $content .= "- {$migration->getFilename()}\n";
        }

        File::put(base_path('docs/AUTO_MIGRATIONS.md'), $content);
    }

    private function scanRoutes(): void
    {
        $this->info('🛣️ مسح Routes...');
        
        $routes = \Route::getRoutes();
        $content = "# Routes\n\n";
        $content .= "**العدد الإجمالي:** " . count($routes) . "\n\n";
        
        $content .= "| Method | URI | Name | Action |\n";
        $content .= "|--------|-----|------|--------|\n";
        
        foreach ($routes as $route) {
            $methods = implode('|', $route->methods());
            $uri = $route->uri();
            $name = $route->getName() ?? '-';
            $action = $route->getActionName();
            
            $content .= "| {$methods} | {$uri} | {$name} | {$action} |\n";
        }

        File::put(base_path('docs/AUTO_ROUTES.md'), $content);
    }

    private function scanConfig(): void
    {
        $this->info('⚙️ مسح Configuration...');
        
        $content = "# System Configuration\n\n";
        $content .= "**تاريخ التحديث:** " . now()->toDateTimeString() . "\n\n";
        
        $content .= "## Environment\n\n";
        $content .= "- APP_NAME: " . config('app.name') . "\n";
        $content .= "- APP_ENV: " . config('app.env') . "\n";
        $content .= "- APP_DEBUG: " . (config('app.debug') ? 'true' : 'false') . "\n";
        $content .= "- APP_URL: " . config('app.url') . "\n\n";
        
        $content .= "## Database\n\n";
        $content .= "- Connection: " . config('database.default') . "\n";
        $content .= "- Host: " . config('database.connections.mysql.host') . "\n";
        $content .= "- Database: " . config('database.connections.mysql.database') . "\n\n";

        File::put(base_path('docs/AUTO_CONFIG.md'), $content);
    }

    private function generateModelsDoc(array $models): string
    {
        $content = "# Models Documentation\n\n";
        $content .= "**آخر تحديث:** " . now()->toDateTimeString() . "\n";
        $content .= "**العدد الإجمالي:** " . count($models) . "\n\n";

        foreach ($models as $className => $info) {
            $content .= "## {$info['name']}\n\n";
            
            if ($info['table']) {
                $content .= "**الجدول:** `{$info['table']}`\n\n";
            }

            if (!empty($info['traits'])) {
                $content .= "**Traits:** " . implode(', ', $info['traits']) . "\n\n";
            }

            if (!empty($info['fillable'])) {
                $content .= "### Fillable\n\n";
                foreach ($info['fillable'] as $field) {
                    $content .= "- `{$field}`\n";
                }
                $content .= "\n";
            }

            if (!empty($info['hidden'])) {
                $content .= "### Hidden\n\n";
                foreach ($info['hidden'] as $field) {
                    $content .= "- `{$field}`\n";
                }
                $content .= "\n";
            }

            if (!empty($info['casts'])) {
                $content .= "### Casts\n\n";
                foreach ($info['casts'] as $field => $cast) {
                    $content .= "- `{$field}` → `{$cast}`\n";
                }
                $content .= "\n";
            }

            if (!empty($info['relations'])) {
                $content .= "### Relations\n\n";
                foreach ($info['relations'] as $relation) {
                    $content .= "- **{$relation['name']}** ({$relation['type']})\n";
                }
                $content .= "\n";
            }

            if (!empty($info['methods'])) {
                $content .= "### Public Methods\n\n";
                foreach ($info['methods'] as $method) {
                    $content .= "- `{$method['name']}()` → {$method['return_type']}\n";
                }
                $content .= "\n";
            }

            $content .= "---\n\n";
        }

        return $content;
    }

    private function generateIndexFile(): void
    {
        $content = "# 📚 SarhIndex v3.0 - فهرس التوثيق التلقائي\n\n";
        $content .= "**آخر تحديث:** " . now()->toDateTimeString() . "\n\n";
        $content .= "> 🤖 هذا الملف يتم تحديثه تلقائياً عند أي تغيير في الكود\n\n";
        
        $content .= "## 📑 الوثائق المتاحة\n\n";
        $content .= "### التوثيق التلقائي\n";
        $content .= "- [Models](AUTO_MODELS.md) — {$this->stats['models']} موديل\n";
        $content .= "- [Controllers](AUTO_CONTROLLERS.md) — {$this->stats['controllers']} controller\n";
        $content .= "- [Services](AUTO_SERVICES.md) — {$this->stats['services']} service\n";
        $content .= "- [Filament Resources](AUTO_FILAMENT_RESOURCES.md) — {$this->stats['resources']} resource\n";
        $content .= "- [Filament Widgets](AUTO_FILAMENT_WIDGETS.md) — {$this->stats['widgets']} widget\n";
        $content .= "- [Filament Pages](AUTO_FILAMENT_PAGES.md) — {$this->stats['pages']} page\n";
        $content .= "- [Migrations](AUTO_MIGRATIONS.md)\n";
        $content .= "- [Routes](AUTO_ROUTES.md)\n";
        $content .= "- [Configuration](AUTO_CONFIG.md)\n\n";
        
        $content .= "### التوثيق اليدوي\n";
        $content .= "- [دليل التشغيل القياسي](SOP_SarhIndex_v3.0.0.md)\n";
        $content .= "- [المخطط المعماري](technical_logic_v3.0.md)\n";
        $content .= "- [الميثاق الوظيفي](functional_ar_v3.0.md)\n\n";
        
        File::put(base_path('docs/README.md'), $content);
    }

    private function generateStatsFile(): void
    {
        $content = "# 📊 إحصائيات المشروع\n\n";
        $content .= "**آخر تحديث:** " . now()->toDateTimeString() . "\n\n";
        
        $content .= "| المكون | العدد |\n";
        $content .= "|--------|-------|\n";
        $content .= "| Models | {$this->stats['models']} |\n";
        $content .= "| Controllers | {$this->stats['controllers']} |\n";
        $content .= "| Services | {$this->stats['services']} |\n";
        $content .= "| Filament Resources | {$this->stats['resources']} |\n";
        $content .= "| Filament Widgets | {$this->stats['widgets']} |\n";
        $content .= "| Filament Pages | {$this->stats['pages']} |\n";
        
        File::put(base_path('docs/AUTO_STATS.md'), $content);
    }
}
