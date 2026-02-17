<?php

namespace App\Console\Commands;

use App\Models\ChangeLog;
use App\Services\DocumentationUpdater;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Finder\Finder;

class WatchChangesCommand extends Command
{
    protected $signature = 'sarh:watch
        {--poll=0 : فترة الاستطلاع بالثواني (0 = من الإعدادات)}
        {--no-db : عدم التسجيل في قاعدة البيانات}
        {--no-docs : عدم تحديث الوثائق تلقائياً}
        {--no-changelog : عدم الكتابة في CHANGELOG.md}
        {--silent : عدم طباعة أي رسائل}';

    protected $description = '🔍 الجاسوس — مراقب الملفات الدائم (يسجل كل تغيير ويحدّث الوثائق)';

    /**
     * حالة الملفات الأخيرة: path → [mtime, size, hash]
     */
    private array $fileState = [];

    /**
     * مسار ملف الحالة المحفوظة.
     */
    private string $stateFile;

    /**
     * عداد الأحداث.
     */
    private int $eventCount = 0;

    /**
     * خدمة تحديث الوثائق.
     */
    private DocumentationUpdater $docUpdater;

    public function handle(): int
    {
        $this->docUpdater = new DocumentationUpdater();
        $this->stateFile = config('file-watcher.state_file', storage_path('file-watcher-state.json'));

        $pollInterval = (int) $this->option('poll') ?: config('file-watcher.poll_interval', 3);
        $dbEnabled    = !$this->option('no-db') && config('file-watcher.database_logging', true);
        $docsEnabled  = !$this->option('no-docs') && config('file-watcher.auto_update_docs', true);
        $clEnabled    = !$this->option('no-changelog') && config('file-watcher.changelog_enabled', true);
        $silent       = (bool) $this->option('silent');

        if (!$silent) {
            $this->printBanner();
            $this->info("⚙️  فترة الاستطلاع: {$pollInterval} ثانية");
            $this->info("📁  المجلدات المراقبة: " . implode(', ', config('file-watcher.paths_to_watch', [])));
            $this->info("💾  قاعدة البيانات: " . ($dbEnabled ? '✅' : '❌'));
            $this->info("📝  CHANGELOG: " . ($clEnabled ? '✅' : '❌'));
            $this->info("📖  تحديث الوثائق: " . ($docsEnabled ? '✅' : '❌'));
            $this->newLine();
            $this->info("👁️  الجاسوس يراقب... (Ctrl+C للإيقاف)");
            $this->newLine();
        }

        // تحميل الحالة المحفوظة أو بناء الحالة الأولية
        $this->loadState();

        // إنشاء CHANGELOG.md إذا لم يوجد
        $this->ensureChangelogExists();

        // ── حلقة المراقبة الرئيسية ──
        while (true) {
            try {
                $changes = $this->detectChanges();

                foreach ($changes as $change) {
                    $this->eventCount++;

                    if (!$silent) {
                        $icon = match ($change['type']) {
                            'add'    => '🟢',
                            'modify' => '🟡',
                            'delete' => '🔴',
                            default  => '⚪',
                        };
                        $this->line("  {$icon} [{$this->eventCount}] {$change['type']} → {$change['path']}");
                    }

                    // 1. تسجيل في CHANGELOG.md
                    if ($clEnabled) {
                        $this->writeChangelog($change);
                    }

                    // 2. تسجيل في قاعدة البيانات
                    if ($dbEnabled) {
                        $this->writeDatabase($change);
                    }

                    // 3. تحديث الوثائق المتأثرة
                    if ($docsEnabled) {
                        $updatedDocs = $this->docUpdater->handleChange($change['path'], $change['type']);
                        if (!empty($updatedDocs) && !$silent) {
                            foreach ($updatedDocs as $doc) {
                                $this->line("    📖 ← تحديث: {$doc}");
                            }
                        }
                    }
                }

                // حفظ الحالة بعد كل دورة
                if (!empty($changes)) {
                    $this->saveState();
                }

            } catch (\Throwable $e) {
                Log::error("[الجاسوس] خطأ في دورة المراقبة: {$e->getMessage()}");
                if (!$silent) {
                    $this->error("❌ خطأ: {$e->getMessage()}");
                }
            }

            sleep($pollInterval);
        }

        return Command::SUCCESS; // @phpstan-ignore-line
    }

    // ─────────────────────────────────────────────────────────
    //  اكتشاف التغييرات
    // ─────────────────────────────────────────────────────────

    /**
     * مقارنة الحالة الحالية بالحالة المحفوظة لاكتشاف التغييرات.
     */
    private function detectChanges(): array
    {
        $currentFiles = $this->scanAllFiles();
        $changes      = [];

        // اكتشاف الملفات الجديدة والمعدّلة
        foreach ($currentFiles as $path => $meta) {
            if (!isset($this->fileState[$path])) {
                // ملف جديد
                $changes[] = [
                    'path' => $path,
                    'type' => 'add',
                    'hash' => $meta['hash'],
                    'size' => $meta['size'],
                ];
            } elseif ($this->fileState[$path]['mtime'] !== $meta['mtime']
                   || $this->fileState[$path]['size'] !== $meta['size']) {
                // ملف معدّل (تغيّر وقت التعديل أو الحجم)
                // تحقق إضافي بالهاش لتجنب الإيجابيات الكاذبة
                if ($this->fileState[$path]['hash'] !== $meta['hash']) {
                    $changes[] = [
                        'path' => $path,
                        'type' => 'modify',
                        'hash' => $meta['hash'],
                        'size' => $meta['size'],
                    ];
                }
            }
        }

        // اكتشاف الملفات المحذوفة
        foreach ($this->fileState as $path => $meta) {
            if (!isset($currentFiles[$path])) {
                $changes[] = [
                    'path' => $path,
                    'type' => 'delete',
                    'hash' => null,
                    'size' => 0,
                ];
            }
        }

        // تحديث الحالة
        $this->fileState = $currentFiles;

        // تصفية: تجاهل التغييرات في CHANGELOG.md نفسه والوثائق المُحدّثة تلقائيًا
        $changes = array_filter($changes, function ($c) {
            return !str_starts_with($c['path'], 'docs/CHANGELOG.md')
                && $c['path'] !== config('file-watcher.state_file');
        });

        return array_values($changes);
    }

    /**
     * مسح جميع الملفات في المجلدات المراقبة.
     */
    private function scanAllFiles(): array
    {
        $paths      = config('file-watcher.paths_to_watch', []);
        $ignored    = config('file-watcher.ignored_paths', []);
        $extensions = config('file-watcher.watched_extensions', ['php']);
        $basePath   = base_path();
        $files      = [];

        foreach ($paths as $relativePath) {
            $fullPath = $basePath . '/' . $relativePath;

            if (!is_dir($fullPath)) {
                // ربما ملف مفرد
                if (is_file($fullPath)) {
                    $relPath = $relativePath;
                    $files[$relPath] = $this->getFileMeta($fullPath);
                }
                continue;
            }

            try {
                $finder = new Finder();
                $finder->files()->in($fullPath)->followLinks();

                // تطبيق الاستثناءات
                foreach ($ignored as $ignoredPath) {
                    $finder->notPath($ignoredPath);
                }

                // تصفية الامتدادات
                $extPatterns = array_map(fn($ext) => '*.'. $ext, $extensions);
                $finder->name($extPatterns);

                foreach ($finder as $file) {
                    $relPath = ltrim(str_replace($basePath, '', $file->getRealPath()), '/');
                    $files[$relPath] = $this->getFileMeta($file->getRealPath());
                }
            } catch (\Throwable $e) {
                // تجاهل — المجلد قد لا يكون موجودًا
            }
        }

        return $files;
    }

    /**
     * بيانات وصفية لملف.
     */
    private function getFileMeta(string $absolutePath): array
    {
        return [
            'mtime' => filemtime($absolutePath),
            'size'  => filesize($absolutePath),
            'hash'  => hash_file('xxh3', $absolutePath) ?: hash_file('crc32b', $absolutePath),
        ];
    }

    // ─────────────────────────────────────────────────────────
    //  التسجيل
    // ─────────────────────────────────────────────────────────

    /**
     * كتابة سجل في CHANGELOG.md.
     */
    private function writeChangelog(array $change): void
    {
        $changelogPath = base_path(config('file-watcher.changelog_path', 'docs/CHANGELOG.md'));

        if (!File::exists($changelogPath)) {
            $this->ensureChangelogExists();
        }

        $content   = File::get($changelogPath);
        $timestamp = now()->format('Y-m-d H:i:s');

        $typeLabel = match ($change['type']) {
            'add'    => 'إضافة 🟢',
            'modify' => 'تعديل 🟡',
            'delete' => 'حذف 🔴',
            default  => $change['type'],
        };

        // حساب رقم الحدث من عدد العناصر الموجودة
        preg_match_all('/^### \[(\d+)\]/m', $content, $matches);
        $lastNumber = !empty($matches[1]) ? max(array_map('intval', $matches[1])) : 0;
        $actionNumber = $lastNumber + 1;

        $entry = <<<MD

### [{$actionNumber}] {$timestamp}
- **المسار:** `{$change['path']}`
- **نوع التغيير:** {$typeLabel}
- **الهاش:** `{$change['hash']}`

MD;

        // إدراج بعد العنوان مباشرة (بين الرأس والمحتوى القديم)
        $headerEnd = strpos($content, "---\n\n");
        if ($headerEnd !== false) {
            $headerEnd += 4; // بعد "---\n\n"
            $content = substr($content, 0, $headerEnd) . $entry . substr($content, $headerEnd);
        } else {
            $content .= $entry;
        }

        // الحد الأقصى للسجلات
        $maxEntries = config('file-watcher.changelog_max_entries', 500);
        if ($maxEntries > 0) {
            $content = $this->trimChangelog($content, $maxEntries);
        }

        File::put($changelogPath, $content);
    }

    /**
     * كتابة سجل في قاعدة البيانات.
     */
    private function writeDatabase(array $change): void
    {
        try {
            // تحقق من التكرار
            if (ChangeLog::isDuplicate($change['path'], $change['type'], $change['hash'])) {
                return;
            }

            // محاولة الحصول على git hash الحالي
            $commitHash = null;
            try {
                $commitHash = trim(shell_exec('cd ' . escapeshellarg(base_path()) . ' && git rev-parse --short HEAD 2>/dev/null') ?? '');
                if (empty($commitHash)) {
                    $commitHash = null;
                }
            } catch (\Throwable) {
                // تجاهل
            }

            ChangeLog::create([
                'action_number' => ChangeLog::nextActionNumber(),
                'timestamp'     => now(),
                'file_path'     => $change['path'],
                'change_type'   => $change['type'],
                'description'   => null,
                'commit_hash'   => $commitHash,
                'file_hash'     => $change['hash'],
                'file_size'     => $change['size'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::warning("[الجاسوس] خطأ تسجيل في قاعدة البيانات: {$e->getMessage()}");
        }
    }

    // ─────────────────────────────────────────────────────────
    //  إدارة الحالة
    // ─────────────────────────────────────────────────────────

    /**
     * تحميل حالة الملفات من ملف JSON.
     */
    private function loadState(): void
    {
        if (File::exists($this->stateFile)) {
            try {
                $data = json_decode(File::get($this->stateFile), true);
                if (is_array($data) && isset($data['files'])) {
                    $this->fileState = $data['files'];
                    $this->eventCount = $data['event_count'] ?? 0;

                    if (!$this->option('silent')) {
                        $this->info("📂 تم تحميل الحالة: " . count($this->fileState) . " ملف مُتتبّع");
                    }
                    return;
                }
            } catch (\Throwable) {
                // ملف تالف — سيتم إعادة المسح
            }
        }

        // مسح أولي
        if (!$this->option('silent')) {
            $this->info("🔍 المسح الأولي...");
        }

        $this->fileState = $this->scanAllFiles();

        if (!$this->option('silent')) {
            $this->info("📂 تم اكتشاف " . count($this->fileState) . " ملف");
        }

        $this->saveState();
    }

    /**
     * حفظ حالة الملفات في ملف JSON.
     */
    private function saveState(): void
    {
        try {
            $dir = dirname($this->stateFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            File::put($this->stateFile, json_encode([
                'files'       => $this->fileState,
                'event_count' => $this->eventCount,
                'saved_at'    => now()->toIso8601String(),
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } catch (\Throwable $e) {
            Log::warning("[الجاسوس] خطأ حفظ الحالة: {$e->getMessage()}");
        }
    }

    // ─────────────────────────────────────────────────────────
    //  مساعدات
    // ─────────────────────────────────────────────────────────

    /**
     * إنشاء ملف CHANGELOG.md إذا لم يوجد.
     */
    private function ensureChangelogExists(): void
    {
        $path = base_path(config('file-watcher.changelog_path', 'docs/CHANGELOG.md'));

        if (File::exists($path)) {
            return;
        }

        $content = <<<'MD'
# 📋 سجل التغييرات (CHANGELOG)

> يُولّد تلقائيًا بواسطة **الجاسوس** (`php artisan sarh:watch`).
> كل تغيير في الكود يُسجّل هنا فور حدوثه.

---

MD;

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);
    }

    /**
     * تقليم CHANGELOG إلى الحد الأقصى من السجلات.
     */
    private function trimChangelog(string $content, int $maxEntries): string
    {
        // عدّ الإدخالات (كل "### [N]" هو إدخال)
        preg_match_all('/^### \[\d+\]/m', $content, $matches, PREG_OFFSET_CAPTURE);

        if (count($matches[0]) <= $maxEntries) {
            return $content;
        }

        // الاحتفاظ بأحدث N إدخال فقط
        $cutOffset = $matches[0][$maxEntries][1] ?? strlen($content);

        return substr($content, 0, $cutOffset) . "\n\n> _...تم حذف سجلات أقدم (الحد: {$maxEntries})_\n";
    }

    /**
     * طباعة بانر البداية.
     */
    private function printBanner(): void
    {
        $this->newLine();
        $this->line('╔═══════════════════════════════════════════════╗');
        $this->line('║   👁️  الجاسوس — نظام مراقبة الملفات الدائم   ║');
        $this->line('║   SarhIndex Real-time File Watcher                ║');
        $this->line('╚═══════════════════════════════════════════════╝');
        $this->newLine();
    }
}
