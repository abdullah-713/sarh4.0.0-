<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use ReflectionMethod;

class DocumentationUpdater
{
    /**
     * الوثائق المتأثرة التي تم تحديثها هذه الجلسة (لمنع التكرار).
     */
    private array $updatedDocs = [];

    /**
     * نقطة الدخول الرئيسية — تحلل المسار وتحدّث الوثيقة المناسبة.
     */
    public function handleChange(string $filePath, string $changeType): array
    {
        $affected = $this->resolveAffectedDocs($filePath);

        if (empty($affected)) {
            return [];
        }

        $updated = [];

        foreach ($affected as $docFile) {
            // تجنّب تحديث نفس الوثيقة أكثر من مرة كل 30 ثانية
            $cacheKey = $docFile;
            if (isset($this->updatedDocs[$cacheKey]) && (time() - $this->updatedDocs[$cacheKey]) < 30) {
                continue;
            }

            try {
                $this->dispatchUpdate($filePath, $docFile, $changeType);
                $this->updatedDocs[$cacheKey] = time();
                $updated[] = $docFile;
            } catch (\Throwable $e) {
                Log::warning("[الجاسوس] فشل تحديث الوثيقة {$docFile}: {$e->getMessage()}");
            }
        }

        return $updated;
    }

    /**
     * تحديد الوثائق المتأثرة بتغيير ملف معين.
     */
    public function resolveAffectedDocs(string $filePath): array
    {
        $mappings = config('file-watcher.doc_mappings', []);
        $affected = [];

        // التطابق من الأكثر تحديدًا إلى الأقل
        // أولاً: ملفات محددة (مسار كامل)
        foreach ($mappings as $pattern => $docFile) {
            if ($filePath === $pattern) {
                $affected[] = $docFile;
            }
        }

        // ثانيًا: مجلدات (بادئة)
        if (empty($affected)) {
            // ترتيب حسب الطول (الأطول = الأكثر تحديدًا) أولاً
            $sortedMappings = $mappings;
            uksort($sortedMappings, fn($a, $b) => strlen($b) - strlen($a));

            foreach ($sortedMappings as $pattern => $docFile) {
                if (str_starts_with($filePath, $pattern . '/') || str_starts_with($filePath, $pattern)) {
                    $affected[] = $docFile;
                    break; // أعلى تطابق فقط
                }
            }
        }

        return array_unique($affected);
    }

    /**
     * تنفيذ التحديث الفعلي.
     */
    private function dispatchUpdate(string $filePath, string $docFile, string $changeType): void
    {
        // تحديد المكون بناءً على المسار
        $component = $this->identifyComponent($filePath);

        if (!$component) {
            return;
        }

        match ($component['type']) {
            'model'     => $this->updateModelDocs($component['name']),
            'migration' => $this->updateMigrationDocs($component['name']),
            'resource'  => $this->updateFilamentResourceDocs($component['name']),
            'page'      => $this->updateFilamentPageDocs($component['name']),
            'widget'    => $this->updateFilamentWidgetDocs($component['name']),
            'service'   => $this->updateServiceDocs($component['name']),
            'command'   => $this->updateCommandDocs($component['name']),
            'event'     => $this->updateEventDocs($component['name']),
            'listener'  => $this->updateListenerDocs($component['name']),
            'job'       => $this->updateJobDocs($component['name']),
            'policy'    => $this->updatePolicyDocs($component['name']),
            'livewire'  => $this->updateLivewireDocs($component['name']),
            default     => $this->updateGeneralDocs($filePath, $docFile),
        };
    }

    /**
     * تحديد نوع المكون واسمه من المسار.
     */
    private function identifyComponent(string $filePath): ?array
    {
        $patterns = [
            '/^app\/Models\/(\w+)\.php$/'                             => 'model',
            '/^database\/migrations\/.*\.php$/'                       => 'migration',
            '/^app\/Filament\/(?:App\/)?Resources\/(\w+)Resource/'    => 'resource',
            '/^app\/Filament\/(?:App\/)?Pages\/(\w+)\.php$/'          => 'page',
            '/^app\/Filament\/(?:App\/)?Widgets\/(\w+)\.php$/'        => 'widget',
            '/^app\/Services\/(\w+)\.php$/'                           => 'service',
            '/^app\/Console\/Commands\/(\w+)\.php$/'                  => 'command',
            '/^app\/Events\/(\w+)\.php$/'                             => 'event',
            '/^app\/Listeners\/(\w+)\.php$/'                          => 'listener',
            '/^app\/Jobs\/(\w+)\.php$/'                               => 'job',
            '/^app\/Policies\/(\w+)\.php$/'                           => 'policy',
            '/^app\/Livewire\/(\w+)\.php$/'                           => 'livewire',
        ];

        foreach ($patterns as $pattern => $type) {
            if (preg_match($pattern, $filePath, $matches)) {
                return [
                    'type' => $type,
                    'name' => $matches[1] ?? basename($filePath, '.php'),
                ];
            }
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────
    //  دوال التحديث المتخصصة
    // ─────────────────────────────────────────────────────────

    /**
     * تحديث توثيق النماذج — يضيف رأسًا بمعلومات النموذج.
     */
    public function updateModelDocs(string $modelName): void
    {
        $className = "App\\Models\\{$modelName}";

        if (!class_exists($className)) {
            return;
        }

        $docFile = base_path('docs/03-database-models.md');
        if (!File::exists($docFile)) {
            return;
        }

        try {
            $reflection = new ReflectionClass($className);
            $model      = new $className;

            $info = [
                'class'     => $className,
                'table'     => $model->getTable(),
                'fillable'  => $model->getFillable(),
                'casts'     => method_exists($model, 'getCasts') ? $model->getCasts() : [],
                'traits'    => array_map(fn($t) => class_basename($t), array_keys($reflection->getTraits())),
                'relations' => $this->extractRelations($reflection),
            ];

            $this->appendUpdateNote($docFile, $modelName, $info);
        } catch (\Throwable $e) {
            Log::warning("[الجاسوس] خطأ تحليل النموذج {$modelName}: {$e->getMessage()}");
        }
    }

    /**
     * تحديث توثيق موارد Filament.
     */
    public function updateFilamentResourceDocs(string $resourceName): void
    {
        $docFile = base_path('docs/05-filament-components.md');

        $fullClass = null;
        foreach (['App\\Filament\\Resources\\', 'App\\Filament\\App\\Resources\\'] as $ns) {
            $candidate = $ns . $resourceName . 'Resource';
            if (class_exists($candidate)) {
                $fullClass = $candidate;
                break;
            }
        }

        if (!$fullClass || !File::exists($docFile)) {
            return;
        }

        try {
            $reflection = new ReflectionClass($fullClass);
            $info = [
                'class' => $fullClass,
                'model' => $reflection->hasMethod('getModel')
                    ? $fullClass::getModel()
                    : 'غير محدد',
                'methods' => array_map(
                    fn(ReflectionMethod $m) => $m->getName(),
                    $reflection->getMethods(ReflectionMethod::IS_PUBLIC)
                ),
            ];

            $this->appendUpdateNote($docFile, $resourceName . 'Resource', $info);
        } catch (\Throwable $e) {
            Log::warning("[الجاسوس] خطأ تحليل المورد {$resourceName}: {$e->getMessage()}");
        }
    }

    /**
     * تحديث توثيق صفحات Filament.
     */
    public function updateFilamentPageDocs(string $pageName): void
    {
        $this->appendSimpleNote('docs/05-filament-components.md', "Page: {$pageName}");
    }

    /**
     * تحديث توثيق أدوات Filament.
     */
    public function updateFilamentWidgetDocs(string $widgetName): void
    {
        $this->appendSimpleNote('docs/05-filament-components.md', "Widget: {$widgetName}");
    }

    /**
     * تحديث توثيق الهجرات.
     */
    public function updateMigrationDocs(string $migrationName): void
    {
        $this->appendSimpleNote('docs/03-database-models.md', "Migration: {$migrationName}");
    }

    /**
     * تحديث توثيق الخدمات.
     */
    public function updateServiceDocs(string $serviceName): void
    {
        $className = "App\\Services\\{$serviceName}";

        if (!class_exists($className)) {
            $this->appendSimpleNote('docs/13-commands-services.md', "Service: {$serviceName}");
            return;
        }

        try {
            $reflection = new ReflectionClass($className);
            $publicMethods = array_filter(
                $reflection->getMethods(ReflectionMethod::IS_PUBLIC),
                fn(ReflectionMethod $m) => !$m->isConstructor() && $m->getDeclaringClass()->getName() === $className
            );

            $info = [
                'class'   => $className,
                'methods' => array_map(fn(ReflectionMethod $m) => $m->getName(), $publicMethods),
            ];

            $this->appendUpdateNote(base_path('docs/13-commands-services.md'), $serviceName, $info);
        } catch (\Throwable $e) {
            Log::warning("[الجاسوس] خطأ تحليل الخدمة {$serviceName}: {$e->getMessage()}");
        }
    }

    /**
     * تحديث توثيق الأوامر.
     */
    public function updateCommandDocs(string $commandName): void
    {
        $this->appendSimpleNote('docs/13-commands-services.md', "Command: {$commandName}");
    }

    /**
     * تحديث توثيق الأحداث.
     */
    public function updateEventDocs(string $eventName): void
    {
        $this->appendSimpleNote('docs/13-commands-services.md', "Event: {$eventName}");
    }

    /**
     * تحديث توثيق المستمعين.
     */
    public function updateListenerDocs(string $listenerName): void
    {
        $this->appendSimpleNote('docs/13-commands-services.md', "Listener: {$listenerName}");
    }

    /**
     * تحديث توثيق المهام.
     */
    public function updateJobDocs(string $jobName): void
    {
        $this->appendSimpleNote('docs/13-commands-services.md', "Job: {$jobName}");
    }

    /**
     * تحديث توثيق السياسات.
     */
    public function updatePolicyDocs(string $policyName): void
    {
        $this->appendSimpleNote('docs/04-roles-permissions.md', "Policy: {$policyName}");
    }

    /**
     * تحديث توثيق مكونات Livewire.
     */
    public function updateLivewireDocs(string $componentName): void
    {
        $this->appendSimpleNote('docs/09-communication-system.md', "Livewire: {$componentName}");
    }

    /**
     * تحديث عام عندما لا يمكن تحديد النوع.
     */
    public function updateGeneralDocs(string $filePath, string $docFile): void
    {
        $this->appendSimpleNote($docFile, "File: {$filePath}");
    }

    // ─────────────────────────────────────────────────────────
    //  مساعدات داخلية
    // ─────────────────────────────────────────────────────────

    /**
     * استخراج أسماء دوال العلاقات من Reflection.
     */
    private function extractRelations(ReflectionClass $reflection): array
    {
        $relationTypes = [
            'HasOne', 'HasMany', 'BelongsTo', 'BelongsToMany',
            'MorphTo', 'MorphOne', 'MorphMany', 'MorphToMany',
            'HasOneThrough', 'HasManyThrough',
        ];

        $relations = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getDeclaringClass()->getName() !== $reflection->getName()) {
                continue;
            }
            if ($method->getNumberOfParameters() > 0) {
                continue;
            }

            $returnType = $method->getReturnType();
            if ($returnType) {
                $typeName = $returnType->getName();
                foreach ($relationTypes as $relType) {
                    if (str_contains($typeName, $relType)) {
                        $relations[] = $method->getName() . " → {$relType}";
                        break;
                    }
                }
            }
        }

        return $relations;
    }

    /**
     * إضافة ملاحظة تحديث مُفصّلة في أسفل ملف الوثيقة.
     */
    private function appendUpdateNote(string $docFile, string $component, array $info): void
    {
        if (!File::exists($docFile)) {
            return;
        }

        $content = File::get($docFile);

        // إزالة أي ملاحظة تحديث سابقة لنفس المكون (لمنع التراكم)
        $marker = "<!-- AUTO-UPDATE: {$component} -->";
        $endMarker = "<!-- /AUTO-UPDATE: {$component} -->";
        $pattern = '/' . preg_quote($marker, '/') . '.*?' . preg_quote($endMarker, '/') . '\s*/s';
        $content = preg_replace($pattern, '', $content);

        $timestamp = now()->format('Y-m-d H:i:s');
        $note = "\n\n{$marker}\n";
        $note .= "---\n";
        $note .= "> **🔄 تحديث تلقائي** — `{$component}` — {$timestamp}\n";

        if (!empty($info['fillable'])) {
            $note .= "> **الحقول**: " . implode(', ', $info['fillable']) . "\n";
        }
        if (!empty($info['relations'])) {
            $note .= "> **العلاقات**: " . implode(', ', $info['relations']) . "\n";
        }
        if (!empty($info['methods'])) {
            $filteredMethods = array_filter($info['methods'], fn($m) => !str_starts_with($m, '__'));
            if (count($filteredMethods) > 10) {
                $note .= "> **الدوال العامة**: " . count($filteredMethods) . " دالة\n";
            } else {
                $note .= "> **الدوال**: " . implode(', ', array_slice($filteredMethods, 0, 15)) . "\n";
            }
        }
        if (!empty($info['traits'])) {
            $note .= "> **السمات**: " . implode(', ', $info['traits']) . "\n";
        }

        $note .= "{$endMarker}\n";

        File::put($docFile, rtrim($content) . $note);
    }

    /**
     * إضافة ملاحظة بسيطة (بدون تحليل Reflection).
     */
    private function appendSimpleNote(string $relativeDocFile, string $component): void
    {
        $docFile = base_path($relativeDocFile);

        if (!File::exists($docFile)) {
            return;
        }

        $content = File::get($docFile);

        // نفس منطق إزالة التحديث السابق
        $marker    = "<!-- AUTO-UPDATE: {$component} -->";
        $endMarker = "<!-- /AUTO-UPDATE: {$component} -->";
        $pattern   = '/' . preg_quote($marker, '/') . '.*?' . preg_quote($endMarker, '/') . '\s*/s';
        $content   = preg_replace($pattern, '', $content);

        $timestamp = now()->format('Y-m-d H:i:s');
        $note  = "\n\n{$marker}\n";
        $note .= "---\n";
        $note .= "> **🔄 تحديث تلقائي** — `{$component}` تم تعديله — {$timestamp}\n";
        $note .= "{$endMarker}\n";

        File::put($docFile, rtrim($content) . $note);
    }
}
