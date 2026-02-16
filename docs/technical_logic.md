# 🏗️ المخطط المعماري التقني — SARH v4.1.0

> **الإصدار:** 4.1.0 | **التاريخ:** 2026-02-16 | **المؤلف:** عبدالحكيم المذهول  
> **Stack:** Laravel 11.x • Filament 3.x • Livewire 3 • Vite 6 • TailwindCSS 3  
> **Production:** PHP 8.2 • MySQL 8.0 • Hostinger Shared

---

## 1. الهيكل المعماري العام

```
┌─────────────────────────────────────────────┐
│              🌐 SARH v4.1.0                │
│         https://sarh.online                 │
├─────────────────────────────────────────────┤
│                                             │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  │
│  │  /admin  │  │   /app   │  │  Public   │  │
│  │ Filament │  │ Filament │  │ Livewire  │  │
│  │  Panel   │  │  Panel   │  │  Routes   │  │
│  │ (Admin)  │  │ (Employee│  │           │  │
│  │ L4-L10   │  │  L1-L10) │  │ Whistle-  │  │
│  │          │  │          │  │ blower    │  │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  │
│       │              │              │        │
│  ┌────┴──────────────┴──────────────┴────┐  │
│  │        Laravel 11 Application         │  │
│  │  ┌──────────────────────────────────┐ │  │
│  │  │  Middleware Layer                 │ │  │
│  │  │  • EnsureAdminPanelAccess (L≥4)  │ │  │
│  │  │  • SetPermissionsPolicy          │ │  │
│  │  │  • Auth (session-based)          │ │  │
│  │  └──────────────────────────────────┘ │  │
│  │  ┌──────────────────────────────────┐ │  │
│  │  │  Service Layer                   │ │  │
│  │  │  • AttendanceService             │ │  │
│  │  │  • GeofencingService             │ │  │
│  │  │  • FinancialReportingService     │ │  │
│  │  │  • AnalyticsService              │ │  │
│  │  │  • FormulaEngineService          │ │  │
│  │  │  • TelemetryService              │ │  │
│  │  │  • AnomalyDetector               │ │  │
│  │  └──────────────────────────────────┘ │  │
│  │  ┌──────────────────────────────────┐ │  │
│  │  │  Event System                    │ │  │
│  │  │  AttendanceRecorded → Handler    │ │  │
│  │  │  BadgeAwarded → Handler          │ │  │
│  │  │  AnomalyDetected → Handler       │ │  │
│  │  └──────────────────────────────────┘ │  │
│  │  ┌──────────────────────────────────┐ │  │
│  │  │  Queue (database driver)         │ │  │
│  │  │  ProcessAttendanceJob            │ │  │
│  │  │  RecalculateMonthlyJob           │ │  │
│  │  │  SendCircularJob                 │ │  │
│  │  └──────────────────────────────────┘ │  │
│  └───────────────────────────────────────┘  │
│                     │                        │
│  ┌──────────────────┴────────────────────┐  │
│  │         MySQL 8.0 Database            │  │
│  │         33 Migrations                 │  │
│  │         35 Models                     │  │
│  └───────────────────────────────────────┘  │
└─────────────────────────────────────────────┘
```

---

## 2. طبقة النماذج (Models Layer)

### 2.1 خريطة العلاقات

```
User ──┬── Branch (belongsTo)
       ├── Department (belongsTo)
       ├── Role (belongsTo)
       ├── DirectManager → User (self-ref)
       ├── AttendanceLogs (hasMany)
       ├── SensorReadings (hasMany)
       ├── WorkRestStats (hasMany)
       ├── AnomalyLogs (hasMany)
       ├── FinancialReports (hasMany)
       ├── LeaveRequests (hasMany)
       ├── PerformanceAlerts (hasMany)
       ├── PointsTransactions (hasMany)
       ├── Badges → UserBadge (hasMany)
       ├── Shifts → UserShift (hasMany)
       ├── UserPermissions (hasMany)
       ├── AttendanceExceptions (hasMany)
       ├── ScoreAdjustments (hasMany)
       ├── EmployeeDocuments (hasMany)
       ├── EmployeeReminders (hasMany)
       ├── Conversations (belongsToMany)
       └── SentMessages (hasMany)

Branch ──┬── Users (hasMany)
         ├── Departments (hasMany)
         ├── AttendanceLogs (hasMany)
         ├── FinancialReports (hasMany)
         ├── Holidays (hasMany)
         ├── Payrolls (hasMany)
         ├── AnalyticsSnapshots (hasMany)
         ├── LossAlerts (hasMany)
         └── EmployeePatterns (hasMany)

Department ──┬── Branch (belongsTo)
             ├── Parent → Department (self-ref)
             ├── Children → Department (hasMany)
             ├── Head → User (belongsTo)
             ├── Users (hasMany)
             └── FinancialReports (hasMany)

AttendanceLog ──┬── User (belongsTo)
                ├── Branch (belongsTo)
                ├── ApprovedBy → User (belongsTo)
                └── SensorReadings (hasMany)

SensorReading ──┬── User (belongsTo)
                ├── AttendanceLog (belongsTo)
                └── AnomalyLog (hasOne)
```

### 2.2 النماذج المهمة — التفاصيل

#### User (المستخدم)
```php
fillable: [
    'name', 'email', 'password', 'employee_id',
    'branch_id', 'department_id', 'role_id',
    'direct_manager_id', 'security_level', 'is_super_admin',
    'job_title', 'monthly_salary', 'phone',
    'national_id', 'join_date', 'is_active',
    'ban_end_at'
]

casts: [
    'is_active' => 'boolean',
    'is_super_admin' => 'boolean',
    'security_level' => 'integer',
    'monthly_salary' => 'decimal:2',
    'ban_end_at' => 'datetime'
]
```

#### AttendanceLog (سجل الحضور)
```php
fillable: [
    'user_id', 'branch_id', 'date', 'status',
    'check_in', 'check_out', 'delay_minutes',
    'delay_cost', 'check_in_latitude', 'check_in_longitude',
    'check_out_latitude', 'check_out_longitude',
    'check_in_distance_meters', 'check_out_distance_meters',
    'is_within_geofence', 'approved_by',
    'total_work_minutes', 'notes'
]
```

#### Branch (الفرع)
```php
fillable: [
    'name_ar', 'name_en', 'code', 'address',
    'latitude', 'longitude', 'geofence_radius_meters',
    'max_allowed_distance_meters', 'monthly_budget',
    'working_hours_per_day', 'working_days_per_month',
    'cost_center_code', 'cost_center_name',
    'is_active'
]
```

---

## 3. طبقة الخدمات (Service Layer)

### 3.1 AttendanceService

```php
class AttendanceService
{
    checkIn(User $user, float $lat, float $lng, array $sensorData = []): AttendanceLog
    // 1. التحقق من السياج الجغرافي
    // 2. حساب دقائق التأخير
    // 3. حساب تكلفة التأخير
    // 4. إنشاء سجل الحضور
    // 5. إطلاق حدث AttendanceRecorded

    checkOut(User $user, float $lat, float $lng): AttendanceLog
    // 1. تحديث سجل الحضور
    // 2. حساب إجمالي ساعات العمل
    // 3. إطلاق حدث AttendanceRecorded

    queueCheckIn(User $user, array $data): void
    // إرسال ProcessAttendanceJob للطابور

    calculateDelayCost(User $user, int $delayMinutes): float
    // (monthly_salary / working_days / working_hours / 60) * delayMinutes
}
```

### 3.2 GeofencingService

```php
class GeofencingService
{
    validatePosition(float $lat, float $lng, Branch $branch): array
    // 1. حساب المسافة Haversine
    // 2. مقارنة مع geofence_radius_meters
    // return ['is_valid' => bool, 'distance' => float]
}
```

### 3.3 AnalyticsService

```php
class AnalyticsService
{
    // ── المؤشرات المالية ──
    calculateVPM(User $user, string $period): float
    calculateTotalLoss(Branch $branch, string $period): float
    calculateProductivityGap(User $user): float
    calculateEfficiencyScore(Branch $branch): float
    calculateROIMatrix(Branch $branch): array

    // ── كشف الأنماط ──
    detectFrequentLatePattern(User $user): ?EmployeePattern
    detectPreHolidayPattern(User $user): ?EmployeePattern
    detectMonthlyCyclePattern(User $user): ?EmployeePattern

    // ── التقارير البصرية ──
    generateHeatmapData(Branch $branch, string $period): array
    getPersonalMirror(User $user): array
    getLostOpportunityClock(Branch $branch): array

    // ── التوليد التلقائي ──
    generateDailySnapshot(?string $date = null): void
    checkAndTriggerAlerts(Branch $branch): void
    runFullAnalysis(): void
}
```

### 3.4 FinancialReportingService

```php
class FinancialReportingService
{
    getDailyLoss(Branch $branch, Carbon $date): float
    getBranchPerformance(Branch $branch, string $period): array
    getDelayImpactAnalysis(Branch $branch): array
    getPredictiveMonthlyLoss(Branch $branch): float
}
```

### 3.5 FormulaEngineService

```php
class FormulaEngineService
{
    evaluateForUser(ReportFormula $formula, User $user, string $period): float
    evaluateForBranch(ReportFormula $formula, Branch $branch, string $period): float
    resolveVariablesForUser(User $user, string $period): array
}
```

### 3.6 TelemetryService

```php
class TelemetryService
{
    processReading(User $user, array $sensorData): SensorReading
    // 1. حفظ القراءة
    // 2. تشغيل AnomalyDetector
    // 3. إذا شذوذ → AnomalyDetected event

    calculateWorkProbability(array $sensorData): float
    classifyMotionSignature(array $accelerometer): string
    calculateDailyStats(User $user, Carbon $date): WorkRestStat
}
```

### 3.7 AnomalyDetector

```php
class AnomalyDetector
{
    analyze(SensorReading $reading): ?AnomalyLog
    // تحليل بيانات الحساس للكشف عن:
    // - تلاعب بالموقع (GPS Spoofing)
    // - أنماط حركة غير طبيعية
    // - عدم تطابق البيانات
}
```

---

## 4. طبقة الأحداث (Event System)

### 4.1 تدفق الأحداث

```
┌─────────────────────────────────────────────┐
│              Event Flow                     │
├─────────────────────────────────────────────┤
│                                             │
│  AttendanceRecorded                         │
│  └─→ HandleAttendanceRecorded               │
│      ├─ تحديث إحصائيات الموظف              │
│      ├─ فحص استحقاق الشارات                │
│      └─ إنشاء تنبيهات الأداء               │
│                                             │
│  BadgeAwarded                               │
│  └─→ HandleBadgePoints                      │
│      ├─ إنشاء PerformanceAlert              │
│      └─ منح نقاط المكافأة                  │
│                                             │
│  AnomalyDetected                            │
│  └─→ HandleAnomalyDetected                  │
│      └─ إنشاء PerformanceAlert (تحذير)      │
│                                             │
└─────────────────────────────────────────────┘
```

---

## 5. طبقة المصادقة والتخويل

### 5.1 Middleware Pipeline

```
Request
  │
  ├─ /admin/* ────→ auth → EnsureAdminPanelAccess (security_level ≥ 4)
  │                  └─→ SetPermissionsPolicy
  │
  ├─ /app/* ──────→ auth → Filament App Panel
  │
  ├─ /attendance/* → auth → AttendanceController
  │
  ├─ /telemetry/* → auth → TelemetryController
  │
  ├─ /dashboard ──→ auth → EmployeeDashboard (Livewire)
  │
  ├─ /messaging/* → auth → MessagingInbox/Chat (Livewire)
  │
  └─ /whistleblower → NO AUTH (Anonymous)
```

### 5.2 God Mode (Level 10)

```php
// AppServiceProvider
Gate::before(function ($user, $ability) {
    if ($user->security_level === 10 || $user->is_super_admin) {
        return true;  // تجاوز جميع فحوصات الصلاحيات
    }
});
```

### 5.3 Policies

```php
UserPolicy:
  - viewAny: security_level ≥ 4
  - view: same branch or security_level ≥ 7
  - create: security_level ≥ 7
  - update: security_level ≥ 7
  - delete: security_level ≥ 10

AttendanceLogPolicy:
  - viewAny: security_level ≥ 4
  - view: own record or security_level ≥ 6
  - create: any authenticated user
  - update: security_level ≥ 7
  - delete: security_level ≥ 10
```

---

## 6. قاعدة البيانات

### 6.1 الترحيلات (33 ملف)

| الترتيب | الجدول(ات) | الغرض |
|---------|------------|-------|
| 000001 | branches | الفروع |
| 000002 | departments | الأقسام |
| 000003 | roles, permissions, role_permission | الأدوار والصلاحيات |
| 000000 | users, password_reset_tokens, sessions | المستخدمين |
| 000001 | cache, cache_locks | الكاش |
| 000002 | jobs, job_batches, failed_jobs | الطابور |
| 000001 | attendance_logs | سجلات الحضور |
| 000002 | financial_reports | التقارير المالية |
| 000003 | whistleblower_reports | البلاغات |
| 000004 | conversations, messages, circulars, etc. | التواصل |
| 000005 | badges, user_badges, points_transactions | التحفيز |
| 000007 | leave_requests, shifts, user_shifts, audit_logs, holidays | العمليات |
| 000011 | user_permissions | صلاحيات فردية |
| 000012 | attendance_exceptions | استثناءات الحضور |
| 000013 | score_adjustments, report_formulas | التعديلات والمعادلات |
| 000014 | settings | الإعدادات |
| 000020 | payrolls | الرواتب |
| 000022 | analytics_snapshots | لقطات التحليلات |
| 000023 | loss_alerts | تنبيهات الخسائر |
| 000024 | employee_patterns | أنماط السلوك |
| 02_13 | sensor_readings, anomaly_logs, work_rest_stats | IoT/Telemetry |
| 02_16 | employee_documents, employee_reminders | الوثائق والتذكيرات |

### 6.2 البذور (Seeders)

| البذرة | الغرض | التشغيل |
|--------|-------|---------|
| `DatabaseSeeder` | المنسق الرئيسي | `php artisan db:seed` |
| `RolesAndPermissionsSeeder` | الأدوار والصلاحيات (10 مستويات) | تلقائي |
| `BadgesSeeder` | الشارات الافتراضية | تلقائي |
| `ProjectDataSeeder` | بيانات المشروع الأساسية | تلقائي |
| `FixUserShiftsDataSeeder` | إصلاح بيانات الورديات | يدوي |
| `MigrateRolePermissionsToUserPermissions` | ترحيل الصلاحيات | يدوي (مرة واحدة) |

---

## 7. نظام البناء (Build System)

### 7.1 Vite Configuration

```javascript
// vite.config.js
input: [
    'resources/css/app.css',
    'resources/js/app.js',
    'resources/css/filament/admin/theme.css',
    'resources/css/filament/app/theme.css',
]
```

### 7.2 Tailwind Configuration

```javascript
// tailwind.config.js
content: [
    './app/Filament/**/*.php',
    './resources/views/**/*.blade.php',
    './vendor/filament/**/*.blade.php',
]
font: { sans: ['Tajawal', ...] }
colors: { 'brand-orange': ... }  // Legacy, overridden by CSS themes
```

### 7.3 Theme Architecture

```
resources/css/filament/
├── app/theme.css     ← Employee Portal (Mobile-First, Bottom Nav)
└── admin/theme.css   ← Admin Panel (Desktop-Optimized)

Both themes:
  • Navy (#0F172A) + Gold (#D4A841)
  • Glassmorphism (backdrop-filter: blur)
  • CSS Custom Properties
  • Cairo + Tajawal fonts
  • Accessibility: reduced-motion, high-contrast
```

---

## 8. الترجمة والتوطين

### 8.1 ملفات الترجمة

| المجلد | الملف | الغرض |
|--------|-------|-------|
| `lang/ar/` | analytics, app, attendance, branches, circulars, command, competition, dashboard, departments, holidays, install, leaves, pwa, shifts, users | عربي (13 ملف) |
| `lang/en/` | analytics, app, attendance, branches, circulars, command, competition, dashboard, departments, holidays, install, leaves, pwa, shifts, users | إنجليزي (13 ملف) |

### 8.2 الإعدادات

```php
// config/app.php
'locale' => 'ar',
'fallback_locale' => 'ar',
'faker_locale' => 'ar_SA',
```

---

## 9. الاختبارات

### 9.1 اختبارات الميزات (Feature Tests) — 8

| الاختبار | الغرض |
|----------|-------|
| `AttendanceCheckInTest` | تسجيل الحضور GPS |
| `AttendanceServiceQueueTest` | معالجة الطابور |
| `CommandCenterSecurityTest` | أمان أوامر Artisan |
| `FinancialReportingTest` | التقارير المالية |
| `MessagingTest` | المراسلات |
| `ProductionHardeningTest` | تصلب الإنتاج |
| `WhistleblowerFormTest` | البلاغات السرية |

### 9.2 اختبارات الوحدة (Unit Tests) — 12

| الاختبار | الغرض |
|----------|-------|
| `AttendanceEvaluationTest` | تقييم الحضور |
| `AttendanceLogPolicyTest` | سياسة الحضور |
| `BranchGeofencingTest` | السياج الجغرافي |
| `ExceptionTest` | الاستثناءات المخصصة |
| `GeofencingServiceTest` | خدمة السياج |
| `MassAssignmentTest` | حماية Mass Assignment |
| `RbacTest` | نظام RBAC |
| `UserFinancialTest` | الحسابات المالية |
| `UserPolicyTest` | سياسة المستخدم |
| `WhistleblowerTest` | التشفير |
| `Models/UserBadgeTest` | شارات المستخدم |
| `Models/UserShiftTest` | ورديات المستخدم |

---

## 10. النشر (Deployment)

### 10.1 بيانات الإنتاج

```
Host:     145.223.119.139
Port:     65002
User:     u850419603
Path:     /home/u850419603/sarh
URL:      https://sarh.online
```

### 10.2 خطوات النشر

```bash
# النشر السريع
./deploy-quick.sh

# أو يدوياً
rsync -avz -e "ssh -p 65002" \
  --exclude='node_modules' --exclude='.git' \
  ./ u850419603@145.223.119.139:~/sarh/

ssh -p 65002 u850419603@145.223.119.139 \
  "cd ~/sarh && php artisan optimize && php artisan filament:cache-components"
```

### 10.3 أوامر ما بعد النشر

```bash
php artisan migrate --force
php artisan optimize
php artisan filament:cache-components
php artisan icons:cache
```

---

> **حقوق الملكية الفكرية:** © 2026 السيد عبدالحكيم المذهول — جميع الحقوق محفوظة
