# 13. الأوامر والخدمات والأحداث والمهام

## 13.1 أوامر Artisan

### `sarh:install` — التثبيت الكامل

```bash
php artisan sarh:install
```

**الخطوات**:
1. التحقق من المتطلبات (PHP، قاعدة البيانات، التخزين)
2. تنفيذ الهجرات (`migrate`)
3. بذر البيانات الأساسية (أدوار، شارات، فخاخ، أقسام، عطلات)
4. إنشاء مدير أعلى (المستوى 10) — تفاعلي
5. ربط التخزين (`storage:link`)
6. تخزين الإعدادات مؤقتًا في الإنتاج

---

### `sarh:analytics` — التحليلات اليومية

```bash
php artisan sarh:analytics --date=2026-02-01
```

**ما يفعله**: يُنفّذ `AnalyticsService::runFullAnalysis()`:
- يُولّد لقطة يومية لكل فرع نشط
- يفحص ويُطلق التنبيهات
- يكتشف الأنماط (أيام الأحد فقط)
- يُعرض جدول نتائج في الطرفية

---

### `sarh:payroll` — توليد كشوف الرواتب

```bash
php artisan sarh:payroll --period=2026-02 --branch=1
```

**ما يفعله**:
- يُنشئ كشف راتب لكل موظف نشط
- يُجمّع بيانات الحضور من `attendance_logs`
- يحسب الخصومات والإضافات
- الافتراضي: الشهر السابق

---

### `telemetry:daily-stats` — إحصائيات القياس عن بعد

```bash
php artisan telemetry:daily-stats --date=2026-02-01 --user=5
```

**ما يفعله**:
- يُشغّل `TelemetryService::calculateDailyStats()` لجميع الموظفين النشطين
- أو لموظف محدد (`--user`)
- يُبلّغ عن الموظفين بتقييم "حرج" أو "تسرّب"

---

### `sarh:auto-document` — التوثيق التلقائي

```bash
php artisan sarh:auto-document --watch
```

**ما يفعله**:
- يمسح النماذج، المتحكمات، الخدمات، موارد Filament، الهجرات، المسارات
- يُولّد ملفات Markdown في مجلد `docs/`
- `--watch`: يراقب التغييرات ويعيد التوليد تلقائيًا

---

## 13.2 الخدمات (Services)

| الخدمة | الملف | الوصف |
|--------|-------|-------|
| **AttendanceService** | `Services/AttendanceService.php` | تسجيل الحضور/الانصراف، الحضور غير المتزامن |
| **GeofencingService** | `Services/GeofencingService.php` | التحقق من الموقع الجغرافي (Haversine) |
| **AnalyticsService** | `Services/AnalyticsService.php` | التحليلات الشاملة (889 سطر) |
| **FinancialReportingService** | `Services/FinancialReportingService.php` | التقارير المالية والتوقعات |
| **FormulaEngineService** | `Services/FormulaEngineService.php` | محرك المعادلات المخصصة |
| **TrapResponseService** | `Services/TrapResponseService.php` | معالجة تفاعلات الفخاخ |
| **TelemetryService** | `Services/TelemetryService.php` | معالجة بيانات أجهزة الاستشعار |
| **AnomalyDetector** | `Services/AnomalyDetector.php` | كشف الشذوذ في بيانات القياس |

### ملخص لكل خدمة

#### AttendanceService
- **التبعية**: `GeofencingService` (عبر Constructor Injection)
- `checkIn()` — تسجيل حضور كامل مع سياج وتقييم ومالية
- `checkOut()` — تسجيل انصراف مع حساب الإضافي والمغادرة المبكرة
- `queueCheckIn()` — حضور غير متزامن عبر `ProcessAttendanceJob`
- `calculateDelayCost()` — غلاف لحساب تكلفة التأخير

#### GeofencingService
- `validatePosition()` — فحص الموقع ضد سياج الفرع
- `haversineDistance()` — حساب المسافة بين نقطتين (static)

#### AnalyticsService
- 14 method عامة — تفصيلها في [وثيقة التحليلات](08-analytics-system.md)

#### FinancialReportingService
- 4 methods مع تخزين مؤقت 5 دقائق — تفصيلها في [وثيقة المالية](06-financial-system.md)

#### FormulaEngineService
- `evaluateForUser()` — تقييم معادلة لموظف محدد
- `evaluateForBranch()` — تقييم معادلة لجميع موظفي فرع
- `resolveVariablesForUser()` — تحويل 13 متغيرًا إلى قيم حقيقية
- `getAvailableVariables()` — قائمة المتغيرات المتاحة (ثنائية اللغة)

#### TrapResponseService
- `processInteraction()` — معالجة تفاعل فخ مع حساب أُسّي للخطر
- `getFakeResponse()` — إرجاع الاستجابة الوهمية
- `getHighRiskUsers()` — أعلى المستخدمين خطرًا
- `getStatistics()` — إحصائيات شاملة للفخاخ

#### TelemetryService
- **التبعية**: `AnomalyDetector`
- `processReading()` — معالجة قراءة استشعار + كشف شذوذ
- `calculateWorkProbability()` — احتمالية العمل (0-1)
- `classifyMotionSignature()` — تصنيف الحركة
- `calculateDailyStats()` — إحصائيات يومية عمل/راحة

#### AnomalyDetector
- `analyze()` — كشف 3 أنواع شذوذ: replay attack، تردد مستحيل، استنزاف بطارية

---

## 13.3 الأحداث (Events) والمستمعون (Listeners)

```
BadgeAwarded ──────→ HandleBadgePoints
                     → يُنشئ PerformanceAlert (تهنئة)

AttendanceRecorded ─→ HandleAttendanceRecorded
                     → يُنشئ AuditLog (تسجيل حضور)

AnomalyDetected ───→ HandleAnomalyDetected
                     → يُنشئ PerformanceAlert (تحذير شذوذ)

TrapTriggered ─────→ HandleTrapTriggered
                     → تسجيل في السجل اليومي
                     → عند escalated: سجل CRITICAL
```

### تسجيل الأحداث (AppServiceProvider)

```php
Event::listen(BadgeAwarded::class, HandleBadgePoints::class);
Event::listen(AttendanceRecorded::class, HandleAttendanceRecorded::class);
Event::listen(AnomalyDetected::class, HandleAnomalyDetected::class);
Event::listen(TrapTriggered::class, HandleTrapTriggered::class);
```

---

## 13.4 المهام الخلفية (Jobs)

### ProcessAttendanceJob
- **المعاملات**: `User, $lat, $lng, $ip?, $device?`
- **الوظيفة**: تسجيل حضور كامل في الخلفية
- **الإعدادات**: timeout 30 ثانية، 3 محاولات

### RecalculateMonthlyAttendanceJob
- **المعاملات**: `$scope ('branch'/'user'/'all'), $scopeId, $triggeredBy`
- **الوظيفة**: إعادة تقييم حالات الحضور وإعادة حساب المالية لكل سجلات الشهر الحالي
- **الاستخدام**: عند تغيير إعدادات الوردية أو الراتب
- **الإعدادات**: timeout 300 ثانية، 3 محاولات
- **مصنع**: `RecalculateMonthlyAttendanceJob::forMonth($period, $scope, $scopeId, $by)`

### SendCircularJob
- **المعاملات**: `Circular, $userIds[]`
- **الوظيفة**: إرسال تعميم كإشعارات `PerformanceAlert`
- **المعالجة**: دفعات من 100 مع توقف 1 ثانية
- **الإعدادات**: timeout 120 ثانية، 2 محاولات

---

## 13.5 الجدول الزمني المقترح

```
┌──────────────────────────────────────────────────────┐
│  الأمر                    │ التكرار المقترح             │
├──────────────────────────────────────────────────────┤
│  sarh:analytics           │ يوميًا الساعة 23:00        │
│  sarh:payroll             │ أول كل شهر                 │
│  telemetry:daily-stats    │ يوميًا الساعة 23:30        │
└──────────────────────────────────────────────────────┘
```

```php
// Console/Kernel.php أو routes/console.php
Schedule::command('sarh:analytics')->dailyAt('23:00');
Schedule::command('telemetry:daily-stats')->dailyAt('23:30');
Schedule::command('sarh:payroll')->monthlyOn(1, '02:00');
```

---

> **السابق**: [نظام الفخاخ النفسية](12-trap-system.md) | **التالي**: [التصميم والواجهة](14-ui-design.md)
