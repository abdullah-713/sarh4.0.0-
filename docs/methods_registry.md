# 📖 سجل الدوال والخدمات — SARH Methods Registry v4.1.0

> **الإصدار:** 4.1.0 | **التاريخ:** 2026-02-16 | **المؤلف:** عبدالحكيم المذهول  
> يوثق كل دالة ونقطة وصول في النظام بشكل دقيق

---

## جدول المحتويات

1. [الخدمات (Services)](#1-الخدمات)
2. [المتحكمات (Controllers)](#2-المتحكمات)
3. [الوظائف (Jobs)](#3-الوظائف)
4. [الأوامر (Commands)](#4-الأوامر)
5. [المستمعات (Listeners)](#5-المستمعات)
6. [النماذج — دوال مهمة (Models)](#6-النماذج)
7. [صفحات Filament](#7-صفحات-filament)
8. [مكونات Livewire](#8-مكونات-livewire)

---

## 1. الخدمات

### 1.1 AttendanceService

**الملف:** `app/Services/AttendanceService.php`

| الدالة | المدخلات | المخرجات | الوصف |
|--------|----------|----------|-------|
| `checkIn` | `User $user, float $lat, float $lng, array $sensorData` | `AttendanceLog` | تسجيل دخول مع GPS + IoT |
| `checkOut` | `User $user, float $lat, float $lng` | `AttendanceLog` | تسجيل خروج |
| `queueCheckIn` | `User $user, array $data` | `void` | إرسال للطابور (ضعف الاتصال) |
| `calculateDelayCost` | `User $user, int $delayMinutes` | `float` | حساب تكلفة التأخير بالريال |

**منطق `checkIn`:**
```
1. GeofencingService::validatePosition($lat, $lng, $user->branch)
2. if !valid && !Gate::allows('bypass-geofence') → throw OutOfGeofenceException
3. $delayMinutes = max(0, now()->diffInMinutes($shift->start_time) - $shift->grace_period)
4. $delayCost = calculateDelayCost($user, $delayMinutes)
5. AttendanceLog::create([...])
6. event(new AttendanceRecorded($log))
```

### 1.2 GeofencingService

**الملف:** `app/Services/GeofencingService.php`

| الدالة | المدخلات | المخرجات | الوصف |
|--------|----------|----------|-------|
| `validatePosition` | `float $lat, float $lng, Branch $branch` | `array` | التحقق من الموقع الجغرافي |

**المعادلة (Haversine):**
```
a = sin²(Δlat/2) + cos(lat1) × cos(lat2) × sin²(Δlng/2)
c = 2 × atan2(√a, √(1−a))
distance = R × c  (R = 6371000 م)
is_valid = distance ≤ geofence_radius_meters
```

### 1.3 FinancialReportingService

**الملف:** `app/Services/FinancialReportingService.php`

| الدالة | المدخلات | المخرجات | الوصف |
|--------|----------|----------|-------|
| `getDailyLoss` | `Branch $branch, Carbon $date` | `float` | خسارة فرع ليوم محدد |
| `getBranchPerformance` | `Branch $branch, string $period` | `array` | أداء الفرع الشامل |
| `getDelayImpactAnalysis` | `Branch $branch` | `array` | تحليل تأثير التأخير |
| `getPredictiveMonthlyLoss` | `Branch $branch` | `float` | توقع الخسارة الشهرية |

### 1.4 AnalyticsService

**الملف:** `app/Services/AnalyticsService.php`

| الدالة | المدخلات | المخرجات | الوصف |
|--------|----------|----------|-------|
| `calculateVPM` | `User $user, string $period` | `float` | قيمة الدقيقة الواحدة |
| `calculateTotalLoss` | `Branch $branch, string $period` | `float` | إجمالي الخسائر |
| `calculateProductivityGap` | `User $user` | `float` | فجوة الإنتاجية |
| `calculateEfficiencyScore` | `Branch $branch` | `float` | درجة الكفاءة (0-100) |
| `calculateROIMatrix` | `Branch $branch` | `array` | مصفوفة العائد على الاستثمار |
| `generateHeatmapData` | `Branch $branch, string $period` | `array` | بيانات الخريطة الحرارية |
| `detectFrequentLatePattern` | `User $user` | `?EmployeePattern` | كشف نمط التأخير المتكرر |
| `detectPreHolidayPattern` | `User $user` | `?EmployeePattern` | كشف نمط ما قبل الإجازة |
| `detectMonthlyCyclePattern` | `User $user` | `?EmployeePattern` | كشف النمط الشهري |
| `getPersonalMirror` | `User $user` | `array` | المرآة الشخصية |
| `getLostOpportunityClock` | `Branch $branch` | `array` | ساعة الفرص الضائعة |
| `checkAndTriggerAlerts` | `Branch $branch` | `void` | فحص وتفعيل التنبيهات |
| `generateDailySnapshot` | `?string $date` | `void` | لقطة يومية تلقائية |
| `runFullAnalysis` | — | `void` | تحليل شامل لجميع الفروع |

### 1.5 FormulaEngineService

**الملف:** `app/Services/FormulaEngineService.php`

| الدالة | المدخلات | المخرجات | الوصف |
|--------|----------|----------|-------|
| `evaluateForUser` | `ReportFormula $formula, User $user, string $period` | `float` | تقييم معادلة لموظف |
| `evaluateForBranch` | `ReportFormula $formula, Branch $branch, string $period` | `float` | تقييم معادلة لفرع |
| `resolveVariablesForUser` | `User $user, string $period` | `array` | حل متغيرات المعادلة |

**المتغيرات المتاحة:**
```
{salary}           → الراتب الشهري
{delay_minutes}    → دقائق التأخير
{delay_cost}       → تكلفة التأخير
{attendance_days}  → أيام الحضور
{absence_days}     → أيام الغياب
{total_hours}      → ساعات العمل
{efficiency}       → نسبة الكفاءة
```

### 1.6 TelemetryService

**الملف:** `app/Services/TelemetryService.php`

| الدالة | المدخلات | المخرجات | الوصف |
|--------|----------|----------|-------|
| `processReading` | `User $user, array $sensorData` | `SensorReading` | معالجة قراءة حساس |
| `calculateWorkProbability` | `array $sensorData` | `float` | احتمالية العمل (0-1) |
| `classifyMotionSignature` | `array $accelerometer` | `string` | تصنيف نمط الحركة |
| `calculateDailyStats` | `User $user, Carbon $date` | `WorkRestStat` | إحصائيات يومية |

**أنماط الحركة:**
```
'stationary'  → ثابت (بدون حركة)
'walking'     → مشي
'working'     → عمل (حركة منتظمة)
'running'     → ركض
'driving'     → قيادة
'irregular'   → غير منتظم (مشبوه)
```

### 1.7 AnomalyDetector

**الملف:** `app/ML/AnomalyDetector.php`

| الدالة | المدخلات | المخرجات | الوصف |
|--------|----------|----------|-------|
| `analyze` | `SensorReading $reading` | `?AnomalyLog` | تحليل قراءة للكشف عن شذوذ |

**أنواع الشذوذ المكتشفة:**
```
'gps_spoofing'         → تلاعب بالموقع GPS
'motion_inconsistency' → عدم تطابق الحركة مع الموقع
'sensor_manipulation'  → تلاعب ببيانات الحساسات
'time_anomaly'         → شذوذ في التوقيت
```

---

## 2. المتحكمات

### 2.1 AttendanceController

**الملف:** `app/Http/Controllers/AttendanceController.php`

| Method | URI | الدالة | الوصف |
|--------|-----|--------|-------|
| POST | `/attendance/check-in` | `checkIn` | تسجيل دخول |
| POST | `/attendance/check-out` | `checkOut` | تسجيل خروج |
| POST | `/attendance/queue-check-in` | `queueCheckIn` | تسجيل مؤجل |
| GET | `/attendance/today` | `todayStatus` | حالة حضور اليوم |

### 2.2 TelemetryController

**الملف:** `app/Http/Controllers/TelemetryController.php`

| Method | URI | الدالة | الوصف |
|--------|-----|--------|-------|
| POST | `/telemetry/push` | `push` | إرسال بيانات الحساسات |
| GET | `/telemetry/config` | `config` | إعدادات جمع البيانات |

---

## 3. الوظائف (Jobs)

### 3.1 ProcessAttendanceJob

**الملف:** `app/Jobs/ProcessAttendanceJob.php`

| الخاصية | القيمة |
|---------|--------|
| الطابور | `attendance` |
| الغرض | معالجة تسجيل حضور مؤجل |
| إعادة المحاولة | 3 مرات |
| التأخير | 5 ثواني بين المحاولات |

### 3.2 RecalculateMonthlyAttendanceJob

**الملف:** `app/Jobs/RecalculateMonthlyAttendanceJob.php`

| الخاصية | القيمة |
|---------|--------|
| الطابور | `reports` |
| الجدولة | أول يوم كل شهر 02:00 |
| الغرض | إعادة حساب التقارير المالية الشهرية |
| الإنشاء | `::forMonth(int $year, int $month)` |

### 3.3 SendCircularJob

**الملف:** `app/Jobs/SendCircularJob.php`

| الخاصية | القيمة |
|---------|--------|
| الطابور | `circulars` |
| الغرض | إرسال تعميم للمستهدفين |

---

## 4. الأوامر (Commands)

### 4.1 sarh:install

**التوقيع:** `sarh:install`  
**الغرض:** إعداد النظام الأولي

```
الخطوات:
1. تشغيل الترحيلات
2. بذر الأدوار والصلاحيات
3. بذر الشارات
4. بذر بيانات المشروع
5. إنشاء مستخدم مدير أولي
6. تحسين الكاش
```

### 4.2 sarh:auto-document

**التوقيع:** `sarh:auto-document {--watch}`  
**الغرض:** توليد التوثيق التلقائي للكود

### 4.3 sarh:payroll

**التوقيع:** `sarh:payroll {--period=} {--branch=}`  
**الغرض:** توليد كشوف رواتب لفترة محددة

### 4.4 sarh:analytics

**التوقيع:** `sarh:analytics {--date=}`  
**الغرض:** توليد لقطات التحليلات اليومية  
**الجدولة:** يومياً 23:50

### 4.5 telemetry:daily-stats

**التوقيع:** `telemetry:daily-stats`  
**الغرض:** حساب إحصائيات العمل/الراحة اليومية  
**الجدولة:** يومياً 23:55

---

## 5. المستمعات (Listeners)

### 5.1 HandleAttendanceRecorded

**الملف:** `app/Listeners/HandleAttendanceRecorded.php`  
**الحدث:** `AttendanceRecorded`

```
الإجراءات:
1. تحديث إحصائيات الحضور الشهرية
2. فحص استحقاق الشارات
3. إنشاء تنبيهات الأداء إذا لزم
```

### 5.2 HandleBadgePoints

**الملف:** `app/Listeners/HandleBadgePoints.php`  
**الحدث:** `BadgeAwarded`

```
الإجراءات:
1. إنشاء PerformanceAlert (نوع: badge_earned)
2. تسجيل نقاط المكافأة
```

### 5.3 HandleAnomalyDetected

**الملف:** `app/Listeners/HandleAnomalyDetected.php`  
**الحدث:** `AnomalyDetected`

```
الإجراءات:
1. إنشاء PerformanceAlert (نوع: anomaly_detected)
2. تسجيل تفاصيل الشذوذ (النوع، مستوى الثقة)
3. تسجيل في سجل النظام (Log)
```

---

## 6. النماذج — دوال مهمة

### 6.1 User

| الدالة | النوع | المخرجات | الوصف |
|--------|-------|----------|-------|
| `hasPermission($permission)` | method | `bool` | فحص صلاحية (مباشرة أو عبر الدور) |
| `isManager()` | method | `bool` | هل هو مدير؟ |
| `getMinuteRate()` | accessor | `float` | سعر الدقيقة (الراتب/الأيام/الساعات/60) |
| `getTotalDelayMinutes($period)` | method | `int` | مجموع التأخير لفترة محددة |
| `getTotalDelayCost($period)` | method | `float` | مجموع تكلفة التأخير |
| `getCurrentShift()` | method | `?Shift` | الوردية الحالية المعيّنة |

### 6.2 Branch

| الدالة | النوع | المخرجات | الوصف |
|--------|-------|----------|-------|
| `getActiveEmployeeCount()` | method | `int` | عدد الموظفين الفعّالين |
| `getMonthlyLoss($period)` | method | `float` | خسارة الفرع الشهرية |
| `isWithinGeofence($lat, $lng)` | method | `bool` | هل الموقع ضمن السياج؟ |

### 6.3 Shift

| الدالة | النوع | المخرجات | الوصف |
|--------|-------|----------|-------|
| `getDurationMinutesAttribute()` | accessor | `int` | مدة الوردية بالدقائق |
| `getName()` | accessor | `string` | اسم الوردية (حسب اللغة) |
| `scopeActive($query)` | scope | `Builder` | الورديات الفعّالة فقط |

### 6.4 Setting

| الدالة | النوع | المخرجات | الوصف |
|--------|-------|----------|-------|
| `instance()` | static | `Setting` | إرجاع النسخة الوحيدة (Singleton مع كاش) |

### 6.5 Department

| الدالة | النوع | المخرجات | الوصف |
|--------|-------|----------|-------|
| `scopeActive($query)` | scope | `Builder` | الأقسام الفعّالة |
| `getName()` | accessor | `string` | اسم القسم (حسب اللغة) |

---

## 7. صفحات Filament

### 7.1 WhistleblowerVaultPage

**الملف:** `app/Filament/Pages/WhistleblowerVaultPage.php`  
**البوابة:** `access-whistleblower-vault` (مستوى 10)

| الدالة | الوصف |
|--------|-------|
| `table()` | جدول البلاغات المشفرة مع فك التشفير عند العرض |
| `mount()` | التحقق من الصلاحية |

### 7.2 BranchLeaderboardPage

**الملف:** `app/Filament/Pages/BranchLeaderboardPage.php`

| الدالة | الوصف |
|--------|-------|
| `getViewData()` | بيانات ترتيب الفروع والموظفين |

### 7.3 FinancialReportsPage

**الملف:** `app/Filament/Pages/FinancialReportsPage.php`

| الدالة | الوصف |
|--------|-------|
| `form()` | فلاتر التقرير (الفرع، الفترة، النوع) |
| `generateReport()` | توليد التقرير المالي |

### 7.4 AnalyticsDashboard

**الملف:** `app/Filament/Pages/AnalyticsDashboard.php`

| الدالة | الوصف |
|--------|-------|
| `getHeaderWidgets()` | ودجات التحليلات (الخريطة الحرارية، الكفاءة، الخسائر) |

### 7.5 GeneralSettingsPage

**الملف:** `app/Filament/Pages/GeneralSettingsPage.php`

| الدالة | الوصف |
|--------|-------|
| `form()` | نموذج الإعدادات (PWA، النظام، المنطق) |
| `save()` | حفظ الإعدادات وتحديث الكاش |

### 7.6 DemoDataGenerator

**الملف:** `app/Filament/Pages/DemoDataGenerator.php`

| الدالة | الوصف |
|--------|-------|
| `form()` | إعدادات توليد البيانات |
| `generate()` | توليد بيانات تجريبية |

---

## 8. مكونات Livewire

### 8.1 WhistleblowerForm

**الملف:** `app/Livewire/WhistleblowerForm.php`  
**المسار:** `/whistleblower` (عام)

| الدالة | الوصف |
|--------|-------|
| `submit()` | تشفير + حفظ البلاغ + إرجاع رقم التتبع |
| `render()` | عرض نموذج البلاغ |

### 8.2 WhistleblowerTrack

**الملف:** `app/Livewire/WhistleblowerTrack.php`  
**المسار:** `/whistleblower/track` (عام)

| الدالة | الوصف |
|--------|-------|
| `track()` | البحث برقم التتبع المشفر |

### 8.3 MessagingInbox

**الملف:** `app/Livewire/MessagingInbox.php`  
**المسار:** `/messaging`

| الدالة | الوصف |
|--------|-------|
| `getConversations()` | قائمة المحادثات مرتبة بآخر رسالة |
| `createConversation()` | إنشاء محادثة جديدة |

### 8.4 MessagingChat

**الملف:** `app/Livewire/MessagingChat.php`  
**المسار:** `/messaging/{conversation}`

| الدالة | الوصف |
|--------|-------|
| `sendMessage()` | إرسال رسالة |
| `loadMessages()` | تحميل رسائل المحادثة |

### 8.5 AttendanceWidget

**الملف:** `app/Livewire/AttendanceWidget.php`

| الدالة | الوصف |
|--------|-------|
| `checkIn()` | تسجيل دخول من PWA |
| `checkOut()` | تسجيل خروج من PWA |
| `getCurrentLocation()` | الحصول على إحداثيات GPS |

### 8.6 EmployeeDashboard

**الملف:** `app/Livewire/EmployeeDashboard.php`  
**المسار:** `/dashboard`

| الدالة | الوصف |
|--------|-------|
| `render()` | لوحة تحكم الموظف الشاملة |

---

> **حقوق الملكية الفكرية:** © 2026 السيد عبدالحكيم المذهول — جميع الحقوق محفوظة
