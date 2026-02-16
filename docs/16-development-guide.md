# 16. دليل التطوير

## 16.1 إعداد بيئة التطوير المحلية

### المتطلبات

| المتطلب | الحد الأدنى |
|---------|-------------|
| PHP | 8.2+ مع إضافات: mbstring, xml, curl, mysql, gd, zip |
| Composer | 2.x |
| Node.js | 18+ (مع npm) |
| MySQL/MariaDB | 8.0+ / 10.6+ |
| Git | 2.x |

### الخطوات

```bash
# 1. استنساخ المستودع
git clone <repo-url> sarh
cd sarh

# 2. تثبيت التبعيات
composer install
npm install

# 3. إعداد البيئة
cp .env.example .env
php artisan key:generate

# 4. إعداد قاعدة البيانات (عدّل .env أولاً)
php artisan migrate --seed

# 5. التثبيت (إنشاء مدير أعلى)
php artisan sarh:install

# 6. ربط التخزين
php artisan storage:link

# 7. تشغيل خادم التطوير
php artisan serve &
npm run dev &
```

---

## 16.2 إضافة مورد Filament جديد

### 1. إنشاء النموذج

```bash
php artisan make:model NewFeature -m
# → app/Models/NewFeature.php
# → database/migrations/xxxx_create_new_features_table.php
```

### 2. تعريف الهجرة

```php
Schema::create('new_features', function (Blueprint $table) {
    $table->id();
    $table->string('name_ar');
    $table->string('name_en')->nullable();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
    $table->enum('status', ['active', 'inactive'])->default('active');
    $table->timestamps();
    $table->softDeletes();
});
```

### 3. تعريف النموذج

```php
class NewFeature extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name_ar', 'name_en', 'user_id', 'branch_id', 'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // العلاقات
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
```

### 4. إنشاء مورد Filament

```bash
php artisan make:filament-resource NewFeature --generate
```

### 5. إضافة التحكم في الوصول

```php
// في NewFeatureResource.php
public static function canAccess(): bool
{
    return auth()->user()?->security_level >= 5;
}

// نطاق البيانات
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    $user = auth()->user();

    if (!$user->is_super_admin && $user->security_level < 10) {
        $query->where('branch_id', $user->branch_id);
    }

    return $query;
}
```

### 6. إضافة Eager Loading

```php
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->with(['user', 'branch']);
}
```

### 7. إضافة ملف اللغة

```php
// lang/ar/new_features.php
return [
    'navigation_group' => 'المجموعة',
    'model_label' => 'الميزة الجديدة',
    'plural_model_label' => 'الميزات الجديدة',
    // ...
];
```

---

## 16.3 إنشاء سياسة (Policy)

```bash
php artisan make:policy NewFeaturePolicy --model=NewFeature
```

```php
class NewFeaturePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->security_level >= 4;
    }

    public function view(User $user, NewFeature $record): bool
    {
        return $user->security_level >= 4
            || $user->id === $record->user_id;
    }

    public function create(User $user): bool
    {
        return $user->security_level >= 5;
    }

    public function update(User $user, NewFeature $record): bool
    {
        return $user->security_level >= 5;
    }

    public function delete(User $user, NewFeature $record): bool
    {
        return $user->security_level >= 10;
    }
}
```

**تسجيل** في `AppServiceProvider.php`:

```php
Gate::policy(NewFeature::class, NewFeaturePolicy::class);
```

---

## 16.4 إضافة حدث (Event) ومستمع (Listener)

### 1. إنشاء الحدث

```php
// app/Events/NewFeatureCreated.php
class NewFeatureCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public NewFeature $feature
    ) {}
}
```

### 2. إنشاء المستمع

```php
// app/Listeners/HandleNewFeatureCreated.php
class HandleNewFeatureCreated
{
    public function handle(NewFeatureCreated $event): void
    {
        // إنشاء سجل تدقيق
        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => 'feature_created',
            'auditable_type' => NewFeature::class,
            'auditable_id'   => $event->feature->id,
        ]);
    }
}
```

### 3. التسجيل في AppServiceProvider

```php
Event::listen(NewFeatureCreated::class, HandleNewFeatureCreated::class);
```

### 4. إطلاق الحدث

```php
event(new NewFeatureCreated($feature));
```

---

## 16.5 إضافة أمر Artisan

```php
// app/Console/Commands/ProcessNewFeaturesCommand.php
class ProcessNewFeaturesCommand extends Command
{
    protected $signature = 'sarh:process-features {--date=}';
    protected $description = 'معالجة الميزات الجديدة';

    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::today();

        $this->info("معالجة الميزات ليوم {$date->toDateString()}...");

        // المنطق هنا...

        $this->info('✓ تمت المعالجة');
        return Command::SUCCESS;
    }
}
```

---

## 16.6 إضافة مكون Livewire (بوابة الموظف)

```bash
php artisan make:livewire NewFeatureWidget
```

```php
// app/Livewire/NewFeatureWidget.php
class NewFeatureWidget extends Component
{
    public function render()
    {
        $features = NewFeature::where('user_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.new-feature-widget', [
            'features' => $features,
        ]);
    }
}
```

أضفه في لوحة الموظف:

```blade
{{-- resources/views/livewire/employee-dashboard.blade.php --}}
@livewire('new-feature-widget')
```

---

## 16.7 اصطلاحات الكود

### التسمية

| المكون | الاصطلاح | مثال |
|--------|----------|------|
| نموذج | PascalCase مفرد | `AttendanceLog` |
| جدول | snake_case جمع | `attendance_logs` |
| مورد Filament | `{Model}Resource` | `AttendanceLogResource` |
| سياسة | `{Model}Policy` | `AttendanceLogPolicy` |
| حدث | PascalCase فعل | `AttendanceRecorded` |
| مستمع | `Handle{Event}` | `HandleAttendanceRecorded` |
| مهمة | PascalCase فعل + Job | `ProcessAttendanceJob` |
| أمر | `sarh:{action}` | `sarh:analytics` |
| خدمة | `{Domain}Service` | `AttendanceService` |

### بنية الملفات

```
ميزة جديدة = {
    Model     → app/Models/
    Migration → database/migrations/
    Resource  → app/Filament/Resources/
    Policy    → app/Policies/
    Service   → app/Services/ (إذا معقدة)
    Event     → app/Events/
    Listener  → app/Listeners/
    Job       → app/Jobs/ (إذا async)
    Command   → app/Console/Commands/ (إذا CLI)
    Lang      → lang/ar/ + lang/en/
    Test      → tests/Feature/ أو tests/Unit/
}
```

### قواعد عامة

1. **ثنائية اللغة**: كل نص مرئي يُترجم (`__('key')` أو حقول `_ar`/`_en`)
2. **Soft Delete**: جميع النماذج الرئيسية تستخدم `SoftDeletes`
3. **Eager Loading**: كل مورد يُحمّل العلاقات مسبقًا (`->with([...])`)
4. **نطاق البيانات**: الموارد تُقيّد البيانات حسب الفرع والمستوى الأمني
5. **التدقيق**: العمليات الحساسة تُسجَّل في `AuditLog`
6. **الاستجابة للموبايل**: استخدام `Stack`/`Split` في جداول Filament

---

## 16.8 تشغيل الاختبارات

```bash
# جميع الاختبارات
php artisan test

# اختبارات الوحدة فقط
php artisan test --testsuite=Unit

# اختبارات الميزات فقط
php artisan test --testsuite=Feature

# اختبار محدد
php artisan test --filter=AttendanceTest
```

---

> **السابق**: [النشر والتشغيل](15-deployment.md) | **التالي**: [المصطلحات](17-glossary.md)
