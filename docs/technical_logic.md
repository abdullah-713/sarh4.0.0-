# صرح — المخطط المعماري (المنطق التقني)
> **الإصدار:** 1.7.0 | **آخر تحديث:** 2026-02-08
> **النطاق:** مخطط قاعدة البيانات، علاقات الكيانات، معمارية تدفق البيانات، وقرارات التصميم

---

## 1. نظرة عامة على معمارية قاعدة البيانات

### 1.1 ترتيب تنفيذ الترحيلات

الترحيلات مُرتبة زمنياً لتلبية قيود المفاتيح الأجنبية:

| # | الطابع الزمني | الترحيل | الجداول المُنشأة | الاعتمادات |
|---|-----------|-----------|----------------|--------------|
| 1 | `0000_01_01_000001` | `create_branches_table` | `branches` | لا يوجد |
| 2 | `0000_01_01_000002` | `create_departments_table` | `departments` | `branches` |
| 3 | `0000_01_01_000003` | `create_roles_permissions_tables` | `roles`, `permissions`, `role_permission` | لا يوجد |
| 4 | `0001_01_01_000000` | `create_users_table` | `users`, `password_reset_tokens`, `sessions` | `branches`, `departments`, `roles` |
| 5 | `0001_01_01_000001` | `create_cache_table` | `cache`, `cache_locks` | لا يوجد (إعداد Laravel الافتراضي) |
| 6 | `0001_01_01_000002` | `create_jobs_table` | `jobs`, `job_batches`, `failed_jobs` | لا يوجد (إعداد Laravel الافتراضي) |
| 7 | `2024_01_02_000001` | `create_attendance_logs_table` | `attendance_logs` | `users`, `branches` |
| 8 | `2024_01_02_000002` | `create_financial_reports_table` | `financial_reports` | `users`, `branches`, `departments` |
| 9 | `2024_01_02_000003` | `create_whistleblower_reports_table` | `whistleblower_reports` | `users` |
| 10 | `2024_01_02_000004` | `create_messaging_tables` | `conversations`, `conversation_participants`, `messages`, `circulars`, `circular_acknowledgments`, `performance_alerts` | `users`, `branches`, `departments`, `roles` |
| 11 | `2024_01_02_000005` | `create_gamification_tables` | `badges`, `user_badges`, `points_transactions` | `users` |
| 12 | `2024_01_02_000006` | `create_trap_interactions_table` | `trap_interactions` | `users` |
| 13 | `2024_01_02_000007` | `create_operational_tables` | `leave_requests`, `shifts`, `user_shifts`, `audit_logs`, `holidays` | `users`, `branches` |

**إجمالي الجداول:** 26 (20 مخصص + 6 إعدادات Laravel الافتراضية)

---

### 1.2 خريطة علاقات الكيانات

```
branches ─┬── departments ──── users ─┬── attendance_logs
           │        │                   ├── leave_requests
           │        │                   ├── financial_reports
           │        │                   ├── messages
           │        │                   ├── performance_alerts
           │        │                   ├── trap_interactions
           │        │                   ├── points_transactions
           │        │                   ├── audit_logs
           │        │                   └── [self-ref: direct_manager_id]
           │        │
           │        └── financial_reports (scope=department)
           │
           ├── attendance_logs
           ├── financial_reports (scope=branch)
           └── holidays

roles ─── role_permission ─── permissions

users ─── user_badges ─── badges
users ─── conversation_participants ─── conversations ─── messages
users ─── user_shifts ─── shifts
users ─── circular_acknowledgments ─── circulars

whistleblower_reports (anonymous — no FK to reporter)
```

---

## 2. معمارية تدفق البيانات الأساسية

### 2.1 سير عملية تسجيل الحضور

```
Employee GPS → Branch.distanceTo(lat, lng) [Haversine]
    │
    ├── distance ≤ geofence_radius (17m) → check_in_within_geofence = true
    │
    ├── Compare check_in_at vs Shift.start_time + grace_period
    │   ├── Within grace → status = 'present', delay_minutes = 0
    │   └── Beyond grace → status = 'late', delay_minutes = diff
    │
    └── Snapshot Financial Data:
        ├── cost_per_minute = User.cost_per_minute (calculated accessor)
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
    │   └── net_financial_impact = delay_cost + early_leave_cost - overtime_cost
    │
    └── Calculate:
        └── loss_percentage = (total_delay_cost / total_salary_budget) × 100
```

### 2.3 سير عملية التفويض (RBAC)

```
User Action Request
    │
    ├── is_super_admin == true → ALLOW (bypass all checks)
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

**المشكلة:** إذا تغير راتب الموظف في منتصف الشهر، فإن سجلات الحضور التاريخية ستعرض بيانات مالية غير صحيحة إذا استعلمت عن الراتب الحالي للمستخدم.

**الحل:** يُخزن كل صف في `attendance_logs` **لقطة** من `cost_per_minute` وقت تسجيل الحضور. وهذا يُنشئ سجلاً مالياً غير قابل للتغيير:

```
attendance_logs.cost_per_minute = User.basic_salary / (working_days × hours × 60)
attendance_logs.delay_cost      = delay_minutes × cost_per_minute  [Pre-calculated]
```

يوفر نموذج `User` هذا كـ **محسوب تلقائي** (`getCostPerMinuteAttribute()`)، ويقوم تابع `AttendanceLog.calculateFinancials()` بنسخه عند تسجيل الحضور.

### 3.2 التسلسل الهرمي الذاتي المرجعي للمدراء

`users.direct_manager_id → users.id` يُتيح:
- `User.directManager()` — من يدير هذا المستخدم
- `User.subordinates()` — جميع المستخدمين الذين يديرهم هذا الشخص
- `User.canManage(target)` — مقارنة مستوى الأمان

### 3.3 تصميم نظام الإبلاغ المجهول

لا يوجد مفتاح أجنبي `user_id` في `whistleblower_reports`. يتم فرض إخفاء الهوية على مستوى المخطط:
- `ticket_number` — تتبع عام (مثال: `WB-A3F1B2C4-260207`)
- `anonymous_token` — مُشفر بخوارزمية SHA-256، يُعطى للمبلغ للمتابعة
- `encrypted_content` — AES-256 عبر `encrypt()` في Laravel

### 3.4 معاملات النقاط متعددة الأشكال

`points_transactions` يستخدم نمط `morphs('sourceable')` في Laravel:
- `sourceable_type` = `App\Models\AttendanceLog` → مُكتسبة لتسجيل الحضور في الوقت المحدد
- `sourceable_type` = `App\Models\Badge` → مُكتسبة من منح الشارة
- يسمح هذا **لأي نموذج مستقبلي** بمنح/خصم نقاط بدون تغييرات في المخطط

### 3.5 الأقسام الهرمية

`departments.parent_id → departments.id` يسمح بالتداخل (مثال: تقنية المعلومات → التطوير → الواجهة الأمامية). كل قسم ينتمي لفرع واحد.

### 3.6 استراتيجية الحذف الناعم

يُطبق على: `users`، `branches`، `departments`، `messages`، `circulars`، `leave_requests`

**لا** يُطبق على: `attendance_logs`، `audit_logs`، `trap_interactions`، `financial_reports` — هذه سجلات غير قابلة للتغيير.

---

## 4. استراتيجية الفهارس

| الجدول | الفهرس | الغرض |
|-------|-------|---------|
| `users` | `(branch_id, status)` | تصفية المستخدمين النشطين حسب الفرع |
| `users` | `(department_id, status)` | تصفية المستخدمين النشطين حسب القسم |
| `users` | `security_level` | تصفية مستوى RBAC |
| `attendance_logs` | `UNIQUE(user_id, attendance_date)` | سجل واحد لكل موظف في اليوم |
| `attendance_logs` | `(branch_id, attendance_date)` | التقارير اليومية للفرع |
| `attendance_logs` | `(status, attendance_date)` | الاستعلامات المعتمدة على الحالة |
| `financial_reports` | `(scope, period_start, period_end)` | تصفية التقارير |
| `trap_interactions` | `(user_id, trap_type)` | تحليل الفخاخ لكل مستخدم |
| `trap_interactions` | `(risk_level, is_reviewed)` | قائمة انتظار المراجعة |
| `performance_alerts` | `(user_id, is_read)` | التنبيهات غير المقروءة لكل مستخدم |
| `audit_logs` | `(auditable_type, auditable_id)` | سجل التدقيق الخاص بالنموذج |
| `audit_logs` | `created_at` | التصفح الزمني |

---

## 5. خريطة نماذج Eloquent

| النموذج | الجدول | السمات الرئيسية | الحذف الناعم |
|-------|-------|------------|-------------|
| `User` | `users` | `HasFactory`, `Notifiable`, `SoftDeletes` | ✅ |
| `Branch` | `branches` | `HasFactory`, `SoftDeletes` | ✅ |
| `Department` | `departments` | `HasFactory`, `SoftDeletes` | ✅ |
| `Role` | `roles` | `HasFactory` | ❌ |
| `Permission` | `permissions` | `HasFactory` | ❌ |
| `AttendanceLog` | `attendance_logs` | `HasFactory` | ❌ |
| `FinancialReport` | `financial_reports` | `HasFactory` | ❌ |
| `WhistleblowerReport` | `whistleblower_reports` | `HasFactory` | ❌ |
| `Conversation` | `conversations` | `HasFactory` | ❌ |
| `Message` | `messages` | `HasFactory`, `SoftDeletes` | ✅ |
| `Circular` | `circulars` | `HasFactory`, `SoftDeletes` | ✅ |
| `CircularAcknowledgment` | `circular_acknowledgments` | `HasFactory` | ❌ |
| `PerformanceAlert` | `performance_alerts` | `HasFactory` | ❌ |
| `Badge` | `badges` | `HasFactory` | ❌ |
| `PointsTransaction` | `points_transactions` | `HasFactory` | ❌ |
| `TrapInteraction` | `trap_interactions` | `HasFactory` | ❌ |
| `LeaveRequest` | `leave_requests` | `HasFactory`, `SoftDeletes` | ✅ |
| `Shift` | `shifts` | `HasFactory` | ❌ |
| `AuditLog` | `audit_logs` | `HasFactory` | ❌ |
| `Holiday` | `holidays` | `HasFactory` | ❌ |

---

## 6. الامتثال لاتفاقيات التسمية

| العنصر | الاتفاقية | الحالة |
|---------|-----------|--------|
| أعمدة قاعدة البيانات | `snake_case` | ✅ مُطبق |
| أسماء النماذج | `PascalCase` | ✅ |
| أسماء التوابع | `camelCase` | ✅ |
| العلاقات | `camelCase` | ✅ |
| جداول الربط | ترتيب أبجدي `role_permission`، `user_badges` | ✅ |
| ملفات الترحيل | `snake_case` مع بادئة زمنية | ✅ |
| أسماء المسارات | `snake_case` (قيد التنفيذ) | ⏳ |
| مفاتيح الإعدادات | `snake_case` | ✅ |

---

## 7. المرحلة 1 — طبقة خدمات الحضور والسياج الجغرافي

### 7.1 معمارية الخدمات

```
PWA (Browser Geolocation API)
    │
    └── POST /attendance/check-in  {latitude, longitude}
            │
            ├── AttendanceController@checkIn
            │       │
            │       ├── GeofencingService::validatePosition(Branch, lat, lng)
            │       │       ├── Haversine distance calculation
            │       │       └── Returns: {distance_meters, within_geofence}
            │       │
            │       └── AttendanceService::checkIn(User, lat, lng, ip, device)
            │               ├── 1. Load user's branch
            │               ├── 2. GeofencingService → distance + geofence status
            │               ├── 3. Resolve current shift (User.currentShift())
            │               ├── 4. Create AttendanceLog record
            │               ├── 5. AttendanceLog.evaluateAttendance(shift_start, grace)
            │               ├── 6. AttendanceLog.calculateFinancials() — SNAPSHOT
            │               ├── 7. Save to DB
            │               └── 8. Return AttendanceLog
            │
            └── POST /attendance/check-out  {latitude, longitude}
                    │
                    └── AttendanceService::checkOut(User, lat, lng)
                            ├── 1. Find today's log
                            ├── 2. GeofencingService → checkout geofence
                            ├── 3. Calculate worked_minutes from check_in/check_out diff
                            ├── 4. Calculate overtime / early_leave
                            ├── 5. Recalculate financials
                            └── 6. Save + return
```

### 7.2 خدمة السياج الجغرافي — تطبيق Haversine

**الملف:** `app/Services/GeofencingService.php`

```
Haversine Formula (Earth as sphere, R = 6,371,000 m):

  Δlat = lat₂ - lat₁  (in radians)
  Δlng = lng₂ - lng₁  (in radians)

  a = sin²(Δlat/2) + cos(lat₁) × cos(lat₂) × sin²(Δlng/2)
  c = 2 × atan2(√a, √(1-a))
  distance = R × c

  Accuracy: ±0.5m for distances < 100m (sufficient for 17m geofence)
```

تُفوض الخدمة إلى `Branch::distanceTo()` للحساب الفعلي، مما يُبقي النموذج كمصدر وحيد للحقيقة لحسابات Haversine.

### 7.3 آلية اللقطة المالية

```
On CHECK-IN:
  attendance_logs.cost_per_minute = User.getCostPerMinuteAttribute()
    → basic_salary / (working_days × working_hours × 60)

On EVALUATE:
  attendance_logs.delay_cost = delay_minutes × cost_per_minute
  attendance_logs.early_leave_cost = early_leave_minutes × cost_per_minute
  attendance_logs.overtime_value = overtime_minutes × cost_per_minute × 1.5

IMMUTABILITY GUARANTEE:
  Once check-in occurs, cost_per_minute is FROZEN in the attendance_log row.
  Even if User.basic_salary changes the next day, historical records remain accurate.
```

### 7.4 شجرة قرار حالة الحضور

```
check_in_at == null?
  ├── YES → status = 'absent'
  └── NO
        │
        check_in_within_geofence == false?
        ├── YES → status = 'late' (flagged: out-of-geofence)
        │         check_in REJECTED by controller (HTTP 422)
        └── NO
              │
              check_in_at ≤ shift_start + grace_period?
              ├── YES → status = 'present', delay_minutes = 0
              └── NO  → status = 'late', delay_minutes = diff in minutes
```

### 7.5 الحسابات المالية عند تسجيل الانصراف

```
worked_minutes = diff(check_out_at, check_in_at) in minutes
expected_minutes = Shift.duration_minutes

IF worked_minutes < expected_minutes:
  early_leave_minutes = expected_minutes - worked_minutes
  early_leave_cost = early_leave_minutes × cost_per_minute

IF worked_minutes > expected_minutes:
  overtime_minutes = worked_minutes - expected_minutes
  overtime_value = overtime_minutes × cost_per_minute × 1.5
```

---

## 8. Phase 2 — Psychological Trap System & Logarithmic Risk Engine

### 8.1 Trap Registry Schema

```
traps
  ├── id
  ├── name_ar        — Arabic display name
  ├── name_en        — English display name
  ├── trap_code      — UNIQUE slug (e.g., SALARY_PEEK)
  ├── description_ar — Arabic explanation
  ├── description_en — English explanation
  ├── risk_weight    — 1-10, multiplier for severity
  ├── fake_response_type — enum: success | error | warning
  ├── is_active      — boolean
  └── timestamps

trap_interactions (UPDATED)
  ├── trap_id → FK to traps  (NEW — replaces free-text trap_type)
  └── metadata → json  (replaces interaction_data naming)
```

### 8.2 Logarithmic Risk Scoring Algorithm

```
Formula: NewPoints = 10 × (2^n − 1)

Where:
  n = COUNT of trap_interactions for this specific user
  (all-time, across ALL trap types)

Progression table:
  n=1  →  10 × (2¹ − 1) =   10 points
  n=2  →  10 × (2² − 1) =   30 points
  n=3  →  10 × (2³ − 1) =   70 points
  n=4  →  10 × (2⁴ − 1) =  150 points
  n=5  →  10 × (2⁵ − 1) =  310 points
  n=6  →  10 × (2⁶ − 1) =  630 points
  n=10 → 10 × (2¹⁰ − 1) = 10230 points

Math guarantee:
  Each subsequent trigger is MORE costly than the sum of all previous triggers.
  This creates a powerful deterrent: a 2nd mistake costs 3× the first.
```

### 8.3 TrapResponseService Architecture

```
User clicks trap element
    │
    └── TrapController@trigger (POST /traps/trigger)
            │
            ├── 1. Validate: trap_code, request metadata
            ├── 2. Resolve Trap model by trap_code
            ├── 3. Create TrapInteraction record
            │       ├── user_id, trap_id, ip_address, user_agent
            │       ├── metadata (JSON: page_url, click_coords, timing)
            │       └── risk_level = derived from trap.risk_weight
            │
            ├── 4. User::incrementRiskScore()
            │       ├── Count existing interactions (n)
            │       ├── new_score = 10 × (2^(n+1) − 1)
            │       └── forceFill(['risk_score' => new_score])->save()
            │
            └── 5. TrapResponseService::generateFakeResponse(Trap)
                    ├── SALARY_PEEK    → fake salary table JSON
                    ├── PRIVILEGE_ESCALATION → success message JSON
                    ├── SYSTEM_BYPASS  → warning confirmation JSON
                    └── DATA_EXPORT    → {progress_url, download_url (encoded/empty)}
```

### 8.4 Risk Level Classification

```
risk_weight (trap) → risk_level (interaction):
  1-3  → 'low'
  4-6  → 'medium'
  7-8  → 'high'
  9-10 → 'critical'
```

---

## 9. Phase 3 — Employee PWA Architecture

### 9.1 Technology Stack

```
Frontend:
  ├── Livewire 3       — Server-driven reactive components
  ├── Alpine.js        — Client-side interactivity (bundled with Livewire)
  ├── Tailwind CSS     — Utility-first styling with RTL support
  ├── Tajawal Font     — Google Fonts, Arabic-first typography
  └── Blade Views      — RTL layout with dir="rtl" / dir="ltr" toggle

Component Architecture:
  layouts/
    └── pwa.blade.php           — RTL master layout with sidebar navigation
  livewire/
    ├── employee-dashboard      — Container for all widgets
    ├── attendance-widget        — Real-time GPS status with check-in/out
    ├── gamification-widget      — Points, streaks, badges display
    ├── financial-widget         — Discipline score & delay costs
    ├── circulars-widget         — Latest circulars with acknowledgment
    ├── whistleblower-form       — Anonymous encrypted report submission
    ├── whistleblower-track      — Track report by anonymous token
    ├── messaging-inbox          — Conversations list with unread counts
    └── messaging-chat           — Single conversation with real-time messages
```

### 9.2 Whistleblower Encryption Flow

```
Employee opens /whistleblower (no auth required for anonymity)
    │
    ├── 1. Fills form: category, severity, content (plaintext)
    │
    ├── 2. On submit (WhistleblowerForm Livewire component):
    │       ├── Generate ticket_number = WB-{8hex}-{yymmdd}
    │       ├── Generate anonymous_token = SHA-256(random_bytes(32))
    │       ├── Encrypt content: encrypt($plaintext)  ← AES-256-CBC
    │       └── Store WhistleblowerReport (no user_id, no FK)
    │
    ├── 3. Display to user:
    │       ├── ticket_number (for reference)
    │       └── anonymous_token (for follow-up — shown ONCE)
    │
    └── 4. Only security_level >= 10 can decrypt via Filament panel
```

### 9.3 Messaging Architecture

```
Conversation System:
  ├── Direct (1-to-1)   — Two participants
  ├── Group              — Multiple participants
  └── Broadcast          — Circulars with acknowledgment tracking

Message Flow:
  User opens /messaging → MessagingInbox component
    ├── Lists conversations with latest message preview
    ├── Shows unread count per conversation
    └── Click → opens MessagingChat component
          ├── Messages displayed in bubble format (RTL)
          ├── New message via Livewire form submission
          ├── Mark messages as read on view
          └── Real-time updates via Livewire polling (3s)

Circular Acknowledgment:
  Admin publishes circular → employees see it in CircularsWidget
    ├── Employee clicks "قرأت واطلعت" (I have read this)
    ├── Creates CircularAcknowledgment record
    └── Admin can track who read vs. who hasn't
```

### 9.4 Trap Integration in PWA

```
Traps are rendered as normal-looking UI elements in the dashboard:
  ├── SALARY_PEEK      — Button in sidebar: "عرض رواتب الزملاء"
  ├── DATA_EXPORT      — Button in footer: "تصدير كل السجلات"
  └── Clicks trigger Alpine.js → POST /traps/trigger → show fake response

The traps MUST be indistinguishable from real features.
Only users with is_trap_target=true see the trap elements.
```

---

## 10. Command Center — Aggregation & Security Architecture (v1.4.0)

### 10.1 Financial Aggregation Engine

```
FinancialReportingService
├── getDailyLoss(date, ?branch_id)
│   └── SUM(attendance_logs.delay_cost) WHERE attendance_date = date
├── getBranchPerformance(month)
│   └── Per-branch: on_time_rate, geofence_compliance, total_loss
├── getDelayImpactAnalysis(start, end, scope, ?scope_id)
│   └── potential_loss = total_delay_minutes × avg_cost_per_minute
│   └── actual_loss = SUM(delay_cost)
│   └── roi = (potential - actual) / potential × 100
└── getPredictiveMonthlyLoss(month)
    └── avg_daily = SUM(delay_cost this month) / working_days_elapsed
    └── predicted = avg_daily × remaining_working_days + accumulated
```

### 10.2 Security Gates for Level 10 Data

```
Whistleblower Vault:
├── Gate: auth()->user()->security_level >= 10
├── Decryption: decrypt(encrypted_content) — only in Filament page render
├── Audit: AuditLog::record('vault_access', $report) on every view
└── No export/download — view-only in browser

Trap Interaction Audit:
├── Gate: same security_level >= 10
├── Full interaction_data JSON display
├── Risk trajectory chart: user's risk_score over time via trap interactions
└── Audit: logged every access
```

### 10.3 Widget Architecture (Filament Dashboard)

| Widget | Class | Type | Sort | Level |
|--------|-------|------|------|-------|
| RealTimeLossCounter | StatsOverviewWidget | Stats | 0 | All admin |
| BranchPerformanceHeatmap | TableWidget | Table | 1 | All admin |
| IntegrityAlertHub | TableWidget | Table | 3 | Level 10 only |

---

## 11. Changelog

| Date | Version | Changes |
|------|---------|--------|
| 2026-02-07 | 1.0.0 | Initial database schema — 13 migrations, 20 models, 2 seeders, complete RBAC with 10 levels and 42 permissions |
| 2026-02-07 | 1.1.0 | Phase 1 — Attendance & Geofencing service layer with GeofencingService, AttendanceService, AttendanceController, Filament AttendanceResource |
| 2026-02-07 | 1.2.0 | Phase 2 — Psychological Trap System: traps table, TrapResponseService, logarithmic risk scoring (10→30→70→150→310), Filament TrapResource + TrapInteractionResource + RiskWidget |
| 2026-02-07 | 1.3.0 | Phase 3 — Employee PWA: Livewire 3 components (dashboard, widgets, messaging, whistleblower), Tailwind RTL layout, Tajawal font, trap integration, circular acknowledgments |
| 2026-02-08 | 1.4.0 | Phase 4 — Command Center: FinancialReportingService, 3 dashboard widgets (RealTimeLossCounter, BranchPerformanceHeatmap, IntegrityAlertHub), WhistleblowerVault + TrapAuditLog Filament pages, predictive analytics, security gate for Level 10 |
| 2026-02-08 | 1.5.0 | Phase 5 (Final) — Production Hardening: BranchScope policy, caching layer for financial queries, performance indexes migration, sarh:install Artisan command, Vite prod optimization, bilingual audit, README_PROD.md deployment guide |

---

## 12. Phase 5 — Production Hardening & Security Architecture (Final)

### 12.1 Security Hardening: BranchScope Policy

```
Problem: Non-Super Admin users in Filament could see data from all branches.
Solution: Global BranchScope middleware applied to AttendanceLog queries in Filament.

Logic:
  IF user.is_super_admin → no scope (sees all)
  ELSE → auto-filter by user.branch_id

Applied to:
  ├── AttendanceLogResource (Filament table query)
  ├── FinancialReportingService (getDailyLoss, getBranchPerformance)
  └── No global scope on model (avoids test contamination)
```

### 12.2 Caching Strategy

```
Cache Driver: config('cache.default') — file/redis/database
Cache TTL: 300 seconds (5 minutes) for financial aggregations

Cached Methods:
  ├── getDailyLoss(date, branch)     → key: sarh.loss.{date}.{branch}
  ├── getBranchPerformance(month)    → key: sarh.perf.{month}
  └── getPredictiveMonthlyLoss(month)→ key: sarh.predict.{month}

Non-Cached (real-time):
  └── getDelayImpactAnalysis() — on-demand report, user-triggered

Cache Invalidation:
  └── TTL-based (auto-expire after 5 minutes)
  └── Manual: php artisan cache:clear
```

### 12.3 Performance Index Migration

```
Migration: add_production_indexes

attendance_logs:
  ├── INDEX(delay_cost)           — SUM aggregations in loss counter
  ├── INDEX(user_id, status)      — Employee performance queries
  └── INDEX(attendance_date, delay_cost) — Daily loss sum optimization

trap_interactions:
  ├── INDEX(trap_id)              — JOIN with traps table
  ├── INDEX(created_at)           — Chronological audit trail
  └── INDEX(user_id, created_at)  — Risk trajectory per user

audit_logs:
  ├── INDEX(user_id)              — User audit trail
  └── INDEX(action)               — Action-type filtering
```

### 12.4 Installation Command: `php artisan sarh:install`

```
Step 1: Verify environment
  ├── Check PHP >= 8.2
  ├── Check required extensions (openssl, pdo, mbstring, tokenizer, xml, ctype, json, bcmath)
  ├── Check APP_KEY is set
  └── Check database connection

Step 2: Run migrations
  └── php artisan migrate --force

Step 3: Seed core data
  ├── RolesAndPermissionsSeeder (10 roles + 42 permissions)
  ├── BadgesSeeder (8 badges)
  └── TrapsSeeder (4 psychological traps)

Step 4: Create Super Admin (Level 10)
  ├── Prompt: Name (AR), Name (EN), Email, Password
  ├── Assign role: super_admin
  └── Call: setSecurityLevel(10) + promoteToSuperAdmin()

Step 5: Finalize
  ├── php artisan storage:link
  ├── php artisan config:cache
  ├── php artisan route:cache
  └── Display success summary
```

### 12.5 Vite Production Build

```
vite.config.js optimizations:
  ├── CSS purging via Tailwind (content paths scoped)
  ├── Build target: 'es2020' (modern browsers for PWA)
  ├── Minification: esbuild (default)
  └── Build command: npm run build → public/build/

PWA Asset Strategy:
  ├── CSS: resources/css/app.css → bundled + purged
  ├── JS:  resources/js/app.js  → bundled + tree-shaken
  └── Manifest: public/build/manifest.json (generated by Vite)
```

---

## 13. UI/UX Overhaul & Level 10 Absolute Authority (v1.6.0)

### 13.1 Theme Architecture

| Property | Old Value | New Value |
|----------|-----------|----------|
| Primary Color | `Color::Emerald` | `Color::Orange` |
| Font | Tajawal (partial) | Tajawal (universal — enforced via `->font('Tajawal')`) |
| Sidebar | Fixed | `sidebarCollapsibleOnDesktop()` + `sidebarFullyCollapsibleOnDesktop()` |
| Content Width | Default | `maxContentWidth('full')` |
| Global Search | None | `command+k` / `ctrl+k` |

### 13.2 UserResource Simplification

```
Core Four Fields (Required):
├── name_ar / name_en — Bilingual identity
├── email — Unique, used for Filament login
├── password — Hashed via Hash::make(), revealable
└── basic_salary — Essential for cost_per_minute = salary / days / hours / 60

Mandatory Addition:
└── avatar — FileUpload, circular crop, stored in /avatars/

Hidden Auto-Defaults:
├── working_days_per_month = 22
├── working_hours_per_day = 8
├── locale = 'ar'
└── timezone = 'Asia/Riyadh'
```

### 13.3 BranchResource Map Intelligence

```
Leaflet.js Map Picker:
├── Interactive clickable map (OpenStreetMap tiles)
├── Draggable marker with real-time lat/lng sync
├── Visual geofence circle (orange, 15% opacity)
├── Bidirectional binding: map ↔ form fields
└── Default center: Riyadh (24.7136, 46.6753)

Geofence Radius:
├── Minimum: 1 meter
├── Maximum: 100,000 meters (100km)
└── No artificial constraints — manager decides
```

### 13.4 Level 10 Absolute Authority ("God Mode")

```
Gate::before() in AppServiceProvider:
├── Condition: security_level === 10 || is_super_admin
├── Effect: Returns true for ALL authorization checks
├── Scope: Universal — covers all Gates, Policies, and Filament guards

Named Gates:
├── 'access-whistleblower-vault' — security_level >= 10
├── 'access-trap-audit' — security_level >= 10
└── 'bypass-geofence' — security_level >= 10 || is_super_admin

AttendanceService Bypass:
├── Geofence check still runs (distance calculated)
├── If outside geofence + has 'bypass-geofence' gate → allowed
├── check_in_within_geofence set to true for Level 10
└── Full audit trail maintained regardless of bypass
```

### 13.5 Changelog

| Date | Version | Changes |
|------|---------|---------|
| 2026-02-09 | 1.6.0 | UI/UX Overhaul: Orange theme, Tajawal universal font, collapsible sidebar, UserResource Core Four simplification with mandatory avatar, BranchResource Leaflet.js map picker with infinite geofence radius (1m–100km), Level 10 God Mode via Gate::before(), geofence bypass for super admins, complete bilingual lang files for users/branches |
| 2026-02-08 | 1.7.0 | Competition Engine: ProjectDataSeeder (5 branches + 36 users, all 17m geofence), BranchLeaderboardPage ranked by lowest financial loss with 6-tier Levels, DailyNewsTicker with per-branch 🏆 first check-in / 🐢 last check-in, manual points adjustment via PointsTransaction model, Cairo font replacing Tajawal, manage-competition + adjust-points gates, bilingual competition lang files |

---

## 14. Competition Engine & Branch Leaderboard (v1.7.0)

### 14.1 Mass Data Seeding Architecture

**Seeder:** `ProjectDataSeeder` — idempotent via `updateOrCreate` on email/code.

| Entity | Count | Distribution |
|--------|-------|-------------|
| Branches | 5 | FADA-2 (11), FADA-1 (8), SARH-CORNER (7), SARH-2 (5), SARH-HQ (4) |
| Super Admin | 1 | `abdullah@sarh.app` (emp001) — Level 10, 500 initial points |
| Employees | 35 | Real employee names, distributed by branch size |
| **Total Users** | **36** | Including super admin |

**Branch GPS Coordinates:**

| Code | Name | Latitude | Longitude | Radius |
|------|------|----------|-----------|--------|
| SARH-HQ | صرح الاتقان الرئيسي | 24.572368 | 46.602829 | 17m |
| SARH-CORNER | صرح الاتقان كورنر | 24.572439 | 46.603008 | 17m |
| SARH-2 | صرح الاتقان 2 | 24.572262 | 46.602580 | 17m |
| FADA-1 | فضاء المحركات 1 | 24.56968126 | 46.61405911 | 17m |
| FADA-2 | فضاء المحركات 2 | 24.566088 | 46.621759 | 17m |

### 14.2 Leaderboard Ranking & Level System

**Ranking Method:** Branches are ranked by **lowest financial loss** from tardiness (not by score).

**Discipline Score** (used for level assignment):

```
Score = 100 (base)
      - (late_checkins × 2)
      - (missed_days × 5)
      + (perfect_employees × 10)
      + (total_points × 0.1)
```

**6-Tier Level System:**

| Score Range | Level | Icon |
|-------------|-------|------|
| ≥ 150 | Legendary (أسطوري) | 🏆 |
| ≥ 120 | Diamond (ألماسي) | 💎 |
| ≥ 100 | Gold (ذهبي) | 🥇 |
| ≥ 80 | Silver (فضي) | 🥈 |
| ≥ 60 | Bronze (برونزي) | 🥉 |
| < 60 | Starter (مبتدئ) | 🐢 |

### 14.3 Trophy & Turtle System

- **Trophy 🏆:** Per-branch first check-in today (earliest `check_in_at` per branch from `attendance_logs`)
- **Turtle 🐢:** Per-branch last check-in today (latest `check_in_at` per branch)
- **DailyNewsTicker:** Dashboard widget showing per-branch 🏆 first / 🐢 last check-in + attendance stats
- Uses `AttendanceLog` model with `check_in_at` and `attendance_date` columns

### 14.4 Manual Points Adjustment

- **Location:** UserResource table → "Adjust Points" action (⭐ icon)
- **Gate:** `adjust-points` — Level 10 only
- **Flow:** Enter points (positive=add, negative=deduct) + reason → `total_points` increment + `PointsTransaction` model record
- **Notification:** Filament toast confirms adjustment with employee name and amount

### 14.5 Font Migration

- **From:** Tajawal (v1.6.0)
- **To:** Cairo (v1.7.0)
- **Locations:** `AdminPanelProvider->font('Cairo')`, `resources/css/app.css` Google Fonts import
- **Weights:** 300, 400, 500, 600, 700, 800, 900
