# صرح — المخطط المعماري v3.0 (المنطق التقني)
> **الإصدار:** 3.0.0 | **آخر تحديث:** 2026-02-16  
> **النطاق:** مخطط قاعدة البيانات، علاقات الكيانات، معمارية تدفق البيانات، الأحداث، السياسات  
> **التحديثات:** إزالة نظام الفخاخ، تنظيف شامل، واجهة Navy+Gold

---

## 1. نظرة عامة على معمارية قاعدة البيانات

### 1.1 الجداول النشطة (v3.0)

**إجمالي الجداول:** 24 جدول نشط (تم إزالة 2 من الفخاخ)

#### الجداول الأساسية
1. `branches` — الفروع مع إحداثيات GPS
2. `departments` — الأقسام (hierarchical)
3. `users` — الموظفون والمدراء
4. `roles` — الأدوار
5. `permissions` — الصلاحيات
6. `role_permission` — جدول ربط

#### جداول الحضور والمالية
7. `attendance_logs` — سجل الحضور مع GPS ومعلومات مالية
8. `user_shifts` — تعيينات المناوبات (كيان مستقل v3.4)
9. `shifts` — جداول المناوبات
10. `holidays` — العطل الرسمية
11. `leave_requests` — طلبات الإجازات
12. `payrolls` — الرواتب الشهرية
13. `financial_reports` — التقارير المالية

#### جداول المراسلات
14. `conversations` — المحادثات
15. `conversation_participants` — المشاركون
16. `messages` — الرسائل
17. `circulars` — التعاميم
18. `circular_acknowledgments` — إقرارات الاطلاع
19. `performance_alerts` — تنبيهات الأداء

#### جداول التلعيب والأمان
20. `badges` — الشارات
21. `user_badges` — منح الشارات (كيان مستقل v3.4)
22. `points_transactions` — حركات النقاط
23. `whistleblower_reports` — البلاغات المشفرة
24. `audit_logs` — سجل المراجعة

#### جداول Laravel الافتراضية
25. `password_reset_tokens`
26. `sessions`
27. `cache`, `cache_locks`
28. `jobs`, `job_batches`, `failed_jobs`

### 1.2 الجداول المحذوفة في v3.0

❌ `traps` — نظام الفخاخ النفسية  
❌ `trap_interactions` — سجل تفاعلات الفخاخ  

---

## 2. معمارية تدفق البيانات الأساسية

### 2.1 سير عملية تسجيل الحضور

```
Employee GPS → Branch.distanceTo(lat, lng) [Haversine]
    │
    ├── distance ≤ 17m → within_geofence = true
    │
    ├── Compare check_in_at vs Shift.start_time + grace_period
    │   ├── Within grace → status = 'on_time', delay_minutes = 0
    │   └── Beyond grace → status = 'late', delay_minutes = diff
    │
    └── Snapshot Financial Data:
        ├── cost_per_minute = User.cost_per_minute
        ├── delay_cost = delay_minutes × cost_per_minute
        ├── early_leave_cost = early_leave_minutes × cost_per_minute
        └── overtime_value = overtime_minutes × cost_per_minute × 1.5
```

### 2.2 سير عملية إنشاء التقارير المالية

```
Input: scope (employee|branch|department|company), period (start, end)
    │
    ├── Query AttendanceLogs for scope+period
    │
    ├── Aggregate:
    │   ├── total_delay_minutes = SUM(delay_minutes)
    │   ├── total_delay_cost = SUM(delay_cost)
    │   ├── total_early_leave_cost = SUM(early_leave_cost)
    │   ├── total_overtime_cost = SUM(overtime_value)
    │   └── net_financial_impact = delay_cost + early_leave - overtime
    │
    └── Calculate:
        └── loss_percentage = (total_delay_cost / total_salary_budget) × 100
```

### 2.3 سير عملية التفويض (RBAC)

```
User Action Request
    │
    ├── is_super_admin == true → ALLOW (bypass all)
    │
    ├── Check User.role.permissions for required slug
    │   ├── Permission exists → ALLOW
    │   └── Permission missing → DENY
    │
    └── Security Level Check:
        └── User.security_level >= required_level → ALLOW
```

---

## 3. قرارات تصميم المخطط

### 3.1 نمط لقطة التكلفة بالدقيقة

**المشكلة:** إذا تغير راتب الموظف، فإن سجلات الحضور التاريخية ستعرض بيانات مالية غير صحيحة.

**الحل:** كل صف في `attendance_logs` يُخزن **لقطة** من `cost_per_minute` وقت التسجيل:

```
attendance_logs.cost_per_minute = User.basic_salary / (working_days × hours × 60)
attendance_logs.delay_cost      = delay_minutes × cost_per_minute
```

هذا يُنشئ سجلاً مالياً غير قابل للتغيير.

### 3.2 التسلسل الهرمي الذاتي المرجعي

`users.direct_manager_id → users.id` يُتيح:
- `User.directManager()` — من يدير هذا المستخدم
- `User.subordinates()` — المرؤوسون
- `User.canManage(target)` — مقارنة مستوى الأمان

### 3.3 تصميم نظام الإبلاغ المجهول

لا يوجد مفتاح أجنبي `user_id` في `whistleblower_reports`:
- `ticket_number` — تتبع عام (مثال: `WB-A3F1B2C4-260216`)
- `anonymous_token` — مُشفر SHA-256
- `encrypted_content` — AES-256 عبر `encrypt()`

### 3.4 معاملات النقاط متعددة الأشكال

`points_transactions` يستخدم `morphs('sourceable')`:
- `sourceable_type` = `App\Models\AttendanceLog` → نقاط الحضور
- `sourceable_type` = `App\Models\Badge` → نقاط الشارات
- يسمح لأي نموذج بمنح/خصم نقاط

### 3.5 استراتيجية الحذف الناعم

✅ يُطبق على: `users`, `branches`, `departments`, `messages`, `circulars`, `leave_requests`

❌ لا يُطبق على: `attendance_logs`, `audit_logs`, `financial_reports` — سجلات غير قابلة للتغيير

---

## 4. استراتيجية الفهارس

| الجدول | الفهرس | الغرض |
|-------|-------|---------|
| `users` | `(branch_id, status)` | تصفية المستخدمين النشطين حسب الفرع |
| `users` | `(department_id, status)` | تصفية حسب القسم |
| `users` | `security_level` | تصفية RBAC |
| `attendance_logs` | `UNIQUE(user_id, attendance_date)` | سجل واحد/يوم |
| `attendance_logs` | `(branch_id, attendance_date)` | تقارير الفرع |
| `attendance_logs` | `(status, attendance_date)` | استعلامات الحالة |
| `financial_reports` | `(scope, period_start, period_end)` | تصفية التقارير |
| `performance_alerts` | `(user_id, is_read)` | التنبيهات غير المقروءة |
| `audit_logs` | `(auditable_type, auditable_id)` | سجل التدقيق |
| `audit_logs` | `created_at` | التصفح الزمني |

---

## 5. خريطة نماذج Eloquent (v3.0)

| النموذج | الجدول | السمات | الحذف الناعم |
|-------|-------|--------|------------|
| `User` | `users` | `HasFactory, Notifiable, SoftDeletes` | ✅ |
| `Branch` | `branches` | `HasFactory, SoftDeletes` | ✅ |
| `Department` | `departments` | `HasFactory, SoftDeletes` | ✅ |
| `Role` | `roles` | `HasFactory` | ❌ |
| `Permission` | `permissions` | `HasFactory` | ❌ |
| `AttendanceLog` | `attendance_logs` | `HasFactory` | ❌ |
| `FinancialReport` | `financial_reports` | `HasFactory` | ❌ |
| `WhistleblowerReport` | `whistleblower_reports` | `HasFactory` | ❌ |
| `Circular` | `circulars` | `HasFactory, SoftDeletes` | ✅ |
| `Message` | `messages` | `HasFactory, SoftDeletes` | ✅ |
| `Badge` | `badges` | `HasFactory` | ❌ |
| `UserBadge` | `user_badges` | `HasFactory` | ❌ |
| `PointsTransaction` | `points_transactions` | `HasFactory` | ❌ |
| `Shift` | `shifts` | `HasFactory` | ❌ |
| `UserShift` | `user_shifts` | `HasFactory` | ❌ |
| `Holiday` | `holidays` | `HasFactory` | ❌ |
| `LeaveRequest` | `leave_requests` | `HasFactory, SoftDeletes` | ✅ |
| `AuditLog` | `audit_logs` | `HasFactory` | ❌ |
| `PerformanceAlert` | `performance_alerts` | `HasFactory` | ❌ |
| `AnomalyLog` | `anomaly_logs` | `HasFactory` | ❌ |
| `LossAlert` | `loss_alerts` | `HasFactory` | ❌ |

**النماذج المحذوفة:** `Trap`, `TrapInteraction`

---

## 6. العلاقات الرئيسية

### 6.1 User Model

```php
// Organizational
belongsTo: branch, department, role, directManager
hasMany: subordinates, attendanceLogs, leaveRequests

// Financial
hasMany: financialReports, pointsTransactions

// Messaging
belongsToMany: conversations (via conversation_participants)
hasMany: sentMessages, performanceAlerts

// Gamification
hasMany: badges (UserBadge), pointsTransactions

// Shifts
hasMany: shifts (UserShift)
activeShift(): UserShift with is_current=true
currentShift(): Shift via activeShift()->shift

// Security
hasMany: auditLogs (as auditable)
```

### 6.2 Branch Model

```php
hasMany: departments, users, attendanceLogs, holidays
distanceTo(lat, lng): float  // Haversine calculation
```

### 6.3 AttendanceLog Model

```php
belongsTo: user, branch, shift
calculateFinancials(): void  // Snapshot cost_per_minute
```

---

## 7. الأحداث والمستمعون (v3.0)

| الحدث | المستمع | التأثير |
|-------|---------|---------|
| `BadgeAwarded` | `HandleBadgePoints` | إنشاء PerformanceAlert + إضافة نقاط |
| `AnomalyDetected` | — | تسجيل في AnomalyLog |
| `AttendanceRecorded` | — | بوابة للتوسع المستقبلي |

**ملاحظة:** تم إزالة `TrapTriggered` و `LogTrapInteraction` في v3.0.

---

## 8. Jobs (Queue)

| Job | Timeout | Tries | الاستخدام |
|-----|---------|-------|----------|
| `ProcessAttendanceJob` | 30s | 3 | تسجيل حضور غير متزامن |
| `SendCircularJob` | 120s | 2 | إرسال تعاميم جماعية |
| `RecalculateMonthlyAttendanceJob` | 300s | 1 | إعادة حساب شهرية |

---

## 9. Policies

| Policy | Methods | الغرض |
|--------|---------|-------|
| `UserPolicy` | `viewSalary, updateSalary, delete` | حماية البيانات المالية |
| `AttendanceLogPolicy` | `view, scopeBranch` | تصفية سجلات الحضور حسب الفرع |

---

## 10. Services Layer

| الخدمة | المسؤولية |
|--------|-----------|
| `AttendanceService` | تسجيل الحضور، حساب التكلفة المالية |
| `GeofencingService` | التحقق من الموقع الجغرافي (Haversine) |
| `FinancialReportingService` | إنشاء التقارير المالية |
| `AnalyticsService` | تحليل الأداء والإحصائيات |
| `AnomalyDetector` | كشف الأنماط الشاذة |
| `FormulaEngineService` | محرك الصيغ المالية المخصصة |
| `TelemetryService` | رصد أداء التطبيق |

**محذوف:** `TrapResponseService`

---

## 11. الأنظمة المحذوفة في v3.0

### ما تم إزالته بالكامل

❌ **نظام الفخاخ النفسية (Psychological Traps)**
- 2 Models: Trap, TrapInteraction
- 1 Event: TrapTriggered
- 1 Listener: LogTrapInteraction
- 1 Service: TrapResponseService
- 1 Controller: TrapController
- 3 Filament Resources/Pages: TrapResource, TrapInteractionResource, TrapAuditPage
- 2 Widgets: RiskWidget, IntegrityAlertHub
- 2 Language files: ar/traps.php, en/traps.php
- 2 Migrations: create_traps_table, create_trap_interactions_table
- Tests: TrapSystemTest, LogarithmicRiskTest

❌ **نظام التنبؤ بالمغادرة (ML/ChurnPredictor)**
- كامل مجلد app/ML

❌ **سكريبتات خطرة**
- create_test_user.php
- reset_passwords.php
- update_shifts_salaries.php
- upgrade_security_levels.php
- deploy_v190.sh
- fix_index.sh

---

## 12. التحديثات على الملفات الموجودة

### AppServiceProvider.php
```php
// محذوف:
- use App\Events\TrapTriggered;
- use App\Listeners\LogTrapInteraction;
- Event::listen(TrapTriggered::class, LogTrapInteraction::class);
- Scramble routes for 'traps'
- Gate 'access-trap-audit'
```

### User.php
```php
// محذوف:
- 'is_trap_target' from hidden/casts
- trapInteractions() relationship
- enableTrapMonitoring()
- incrementRiskScore()
- getRiskLevelAttribute()
- 'traps.manage => traps.view' from permission hierarchy
```

### routes/web.php
```php
// محذوف:
- use App\Http\Controllers\TrapController;
- Route group for trap triggers
```

### RolesAndPermissionsSeeder.php
```php
// محذوف:
- 'traps.view' permission
- 'traps.manage' permission
- All trap permission assignments
```

---

## 13. الخلاصة المعمارية v3.0

### نقاط القوة
✅ معمارية نظيفة بدون أنظمة زائدة  
✅ سجلات مالية غير قابلة للتغيير (Immutable)  
✅ Geofencing دقيق (17 متر)  
✅ RBAC محكم (10 مستويات)  
✅ Event-driven architecture  
✅ Queue jobs للعمليات الثقيلة  

### التحسينات
✅ إزالة 37+ ملف غير ضروري  
✅ تقليل complexity  
✅ تحسين الأمان (إزالة ML/test scripts)  
✅ واجهة مستخدم فاخرة (Navy+Gold)  

---

> **صرح v3.0** — معمارية نظيفة، قوية، آمنة
