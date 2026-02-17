# 3. قاعدة البيانات والنماذج

## 3.1 نظرة عامة

يحتوي النظام على **35+ جدول** في قاعدة البيانات و **37 نموذج Eloquent**. الجداول مقسمة إلى مجموعات وظيفية:

```
┌───────────────────────────────────────────────────────────────┐
│                    جداول قاعدة البيانات                        │
├──────────────┬──────────────┬──────────────┬─────────────────┤
│  الهيكل      │   الموظفين   │   التشغيل   │   التحليلات    │
│  التنظيمي    │   والأمان    │   اليومي    │   والرقابة     │
├──────────────┼──────────────┼──────────────┼─────────────────┤
│ branches     │ users        │ attendance   │ analytics       │
│ departments  │ roles        │ leave_req    │ _snapshots      │
│ shifts       │ permissions  │ payrolls     │ anomaly_logs    │
│ holidays     │ user_perms   │ financial    │ employee        │
│ user_shifts  │ role_perm    │ _reports     │ _patterns       │
│              │              │ sensor       │ loss_alerts     │
│              │              │ _readings    │ work_rest_stats │
│              │              │              │ audit_logs      │
├──────────────┴──────────────┴──────────────┴─────────────────┤
│  التواصل والتحفيز          │  الأمان والفخاخ                 │
├────────────────────────────┼─────────────────────────────────┤
│ conversations              │ traps                           │
│ messages                   │ trap_interactions               │
│ circulars                  │ whistleblower_reports           │
│ badges / user_badges       │ score_adjustments               │
│ points_transactions        │ report_formulas                 │
│ employee_documents         │ attendance_exceptions           │
│ employee_reminders         │                                 │
└────────────────────────────┴─────────────────────────────────┘
```

## 3.2 الجداول الرئيسية — التفصيل الكامل

---

### 3.2.1 جدول `users` — الموظفون

**النموذج**: `App\Models\User`  
**السمات الخاصة**: `SoftDeletes`, `FilamentUser`

#### الحقول

| الحقل | النوع | الوصف |
|-------|-------|-------|
| `id` | bigint auto | المعرف الأساسي |
| `employee_id` | string unique | الرقم الوظيفي (`SI-YY-NNNN`) — يُولّد تلقائيًا |
| `name_ar` | string | الاسم بالعربية |
| `name_en` | string nullable | الاسم بالإنجليزية |
| `email` | string unique | البريد الإلكتروني |
| `password` | string | كلمة المرور (مشفّرة bcrypt) |
| `phone` | string nullable | رقم الهاتف |
| `national_id` | string nullable | رقم الهوية |
| `avatar` | string nullable | مسار صورة الملف الشخصي |
| `gender` | enum(male,female) | الجنس |
| `date_of_birth` | date nullable | تاريخ الميلاد |
| `branch_id` | FK → branches | الفرع |
| `department_id` | FK → departments | القسم |
| `role_id` | FK → roles | الدور (شرفي فقط) |
| `direct_manager_id` | FK → users (self) | المدير المباشر |
| `job_title_ar` | string nullable | المسمى الوظيفي بالعربية |
| `job_title_en` | string nullable | المسمى الوظيفي بالإنجليزية |
| `hire_date` | date nullable | تاريخ التعيين |
| `employment_type` | enum(full_time,part_time,contract) | نوع التوظيف |
| `status` | enum(active,inactive,suspended,terminated) | الحالة |
| `is_super_admin` | boolean default false | هل هو مدير أعلى؟ |
| `security_level` | integer default 2 | مستوى الأمان (1-10) |
| **الحقول المالية** | | |
| `basic_salary` | decimal(10,2) default 0 | الراتب الأساسي |
| `housing_allowance` | decimal(10,2) default 0 | بدل السكن |
| `transport_allowance` | decimal(10,2) default 0 | بدل النقل |
| `other_allowances` | decimal(10,2) default 0 | بدلات أخرى |
| `working_days_per_month` | integer default 22 | أيام العمل الشهرية |
| `working_hours_per_day` | decimal(4,2) default 8 | ساعات العمل اليومية |
| **حقول التحفيز** | | |
| `total_points` | integer default 0 | مجموع النقاط |
| `current_streak` | integer default 0 | سلسلة الحضور الحالية |
| `longest_streak` | integer default 0 | أطول سلسلة حضور |
| **التفضيلات** | | |
| `locale` | string default 'ar' | لغة الواجهة |
| `timezone` | string default 'Asia/Riyadh' | المنطقة الزمنية |

#### العلاقات

```php
// علاقات "ينتمي إلى" (BelongsTo)
$user->branch          // الفرع
$user->department      // القسم
$user->role            // الدور
$user->directManager   // المدير المباشر

// علاقات "لديه كثير" (HasMany)
$user->attendanceLogs      // سجلات الحضور
$user->leaveRequests       // طلبات الإجازة
$user->payrolls            // كشوف الرواتب
$user->badges              // الشارات (عبر user_badges)
$user->pointsTransactions  // معاملات النقاط
$user->documents           // الوثائق
$user->reminders           // التذكيرات
$user->subordinates        // المرؤوسون المباشرون
$user->userPermissions     // الصلاحيات الفردية
```

#### الخصائص المحسوبة (Accessors)

```php
$user->total_salary        // الراتب الإجمالي = أساسي + سكن + نقل + أخرى
$user->monthly_working_minutes  // دقائق العمل الشهرية
$user->cost_per_minute     // تكلفة الدقيقة من الراتب الأساسي
$user->total_cost_per_minute   // تكلفة الدقيقة من الراتب الإجمالي
```

#### المنطق الخاص

- **توليد الرقم الوظيفي تلقائيًا**: عند الإنشاء يُولّد بصيغة `SI-{سنة}-{رقم تسلسلي 4 أرقام}`
- **نظام الصلاحيات**: `hasPermission($slug)` يفحص: super_admin → سحب فردي → منح فردي → صلاحية ضمنية → false
- **الوصول للوحات**: `canAccessPanel('admin')` يتطلب security_level ≥ 4

---

### 3.2.2 جدول `branches` — الفروع

**النموذج**: `App\Models\Branch` | **السمات**: `SoftDeletes`

| الحقل | النوع | الوصف |
|-------|-------|-------|
| `id` | bigint auto | المعرف |
| `name_ar` / `name_en` | string | اسم الفرع |
| `code` | string unique | رمز الفرع |
| `address` | text nullable | العنوان |
| `city_ar` / `city_en` | string nullable | المدينة |
| `latitude` / `longitude` | decimal(10,7) | الإحداثيات الجغرافية |
| `geofence_radius` | integer default 100 | نصف قطر السياج بالأمتار |
| `default_shift_start` / `end` | time | أوقات الدوام الافتراضية |
| `default_grace_period` | integer default 15 | فترة السماح بالدقائق |
| `monthly_salary_budget` | decimal(12,2) | ميزانية الرواتب الشهرية |
| `target_attendance_rate` | decimal(5,2) | معدل الحضور المستهدف |
| `vpm_target` | decimal(8,2) | هدف القيمة لكل دقيقة |
| `is_active` | boolean | الحالة |

#### المنطق الخاص
- `distanceTo($lat, $lng)` — حساب المسافة بالأمتار باستخدام Haversine
- `isWithinGeofence($lat, $lng)` — هل النقطة داخل السياج؟
- `recalculateSalaryBudget()` — إعادة حساب الميزانية من رواتب الموظفين

---

### 3.2.3 جدول `attendance_logs` — سجلات الحضور

**النموذج**: `App\Models\AttendanceLog`

| الحقل | النوع | الوصف |
|-------|-------|-------|
| `id` | bigint auto | المعرف |
| `user_id` | FK → users | الموظف |
| `branch_id` | FK → branches | الفرع |
| `attendance_date` | date | تاريخ الحضور |
| `check_in_time` | timestamp nullable | وقت الحضور |
| `check_in_latitude` / `longitude` | decimal | إحداثيات الحضور |
| `check_in_distance` | decimal | المسافة من الفرع (متر) |
| `check_in_within_geofence` | boolean | داخل السياج؟ |
| `check_in_ip` | string nullable | عنوان IP |
| `check_in_device` | string nullable | معلومات الجهاز |
| `check_out_time` | timestamp nullable | وقت الانصراف |
| `check_out_latitude` / `longitude` | decimal | إحداثيات الانصراف |
| `status` | enum(present,late,absent,on_leave,holiday,exception) | الحالة |
| `delay_minutes` | integer default 0 | دقائق التأخير |
| `early_leave_minutes` | integer default 0 | دقائق المغادرة المبكرة |
| `overtime_minutes` | integer default 0 | دقائق العمل الإضافي |
| `cost_per_minute` | decimal(8,4) | تكلفة الدقيقة (لقطة مالية) |
| `delay_cost` | decimal(10,2) | تكلفة التأخير |
| `overtime_value` | decimal(10,2) | قيمة العمل الإضافي |
| `notes` | text nullable | ملاحظات |
| `approved_by` | FK → users nullable | معتمد من |

#### الفهارس المُرَكّبة
- `(branch_id, attendance_date)` — للاستعلامات حسب الفرع والتاريخ
- `(user_id, attendance_date)` — للاستعلامات حسب الموظف والتاريخ

#### المنطق الخاص
- `calculateFinancials()` — يأخذ لقطة من `cost_per_minute` للمستخدم وقت التسجيل
- `evaluateAttendance()` — يحدد الحالة (حاضر/متأخر/غائب) بناءً على الوردية وفترة السماح

---

### 3.2.4 جدول `payrolls` — كشوف الرواتب

**النموذج**: `App\Models\Payroll` | **السمات**: `SoftDeletes`

| الحقل | النوع | الوصف |
|-------|-------|-------|
| `user_id` | FK → users | الموظف |
| `branch_id` | FK → branches | الفرع |
| `period` | string | الفترة (مثل 2026-02) |
| `basic_salary` | decimal | الراتب الأساسي |
| `housing_allowance` | decimal | بدل السكن |
| `transport_allowance` | decimal | بدل النقل |
| `other_allowances` | decimal | بدلات أخرى |
| `total_salary` | decimal | إجمالي الراتب قبل الخصومات |
| `total_deductions` | decimal | إجمالي الخصومات |
| `absence_deduction` | decimal | خصم الغياب |
| `delay_deduction` | decimal | خصم التأخير |
| `other_deductions` | decimal | خصومات أخرى |
| `total_additions` | decimal | إجمالي الإضافات |
| `overtime_addition` | decimal | أجر العمل الإضافي |
| `bonus` | decimal | المكافآت |
| `net_salary` | decimal | صافي الراتب |
| `working_days` | integer | أيام العمل |
| `present_days` | integer | أيام الحضور |
| `absent_days` | integer | أيام الغياب |
| `late_days` | integer | أيام التأخر |
| `total_delay_minutes` | integer | إجمالي دقائق التأخير |
| `total_overtime_minutes` | integer | إجمالي دقائق العمل الإضافي |
| `status` | enum(draft,approved,paid) | الحالة |
| `approved_by` | FK → users | معتمد من |
| `notes` | text nullable | ملاحظات |

---

### 3.2.5 جدول `leave_requests` — طلبات الإجازة

**النموذج**: `App\Models\LeaveRequest` | **السمات**: `SoftDeletes`

| الحقل | النوع | الوصف |
|-------|-------|-------|
| `user_id` | FK → users | الموظف |
| `leave_type` | enum(annual,sick,emergency,unpaid,...) | نوع الإجازة |
| `start_date` / `end_date` | date | فترة الإجازة |
| `days_count` | integer | عدد الأيام |
| `reason` | text | السبب |
| `status` | enum(pending,approved,rejected,cancelled) | الحالة |
| `approved_by` | FK → users nullable | معتمد من |
| `rejection_reason` | text nullable | سبب الرفض |

---

### 3.2.6 جدول `shifts` — الورديات

**النموذج**: `App\Models\Shift`

| الحقل | النوع | الوصف |
|-------|-------|-------|
| `name_ar` / `name_en` | string | اسم الوردية |
| `start_time` / `end_time` | time | وقت البداية والنهاية |
| `grace_period_minutes` | integer default 15 | فترة السماح |
| `is_overnight` | boolean | وردية ليلية؟ |
| `is_active` | boolean | نشطة؟ |

**جدول وسيط** `user_shifts`:

| الحقل | الوصف |
|-------|-------|
| `user_id` | الموظف |
| `shift_id` | الوردية |
| `is_current` | هل هي الوردية الحالية؟ |
| `effective_from` / `effective_to` | فترة السريان |

---

### 3.2.7 جدول `roles` — الأدوار

**النموذج**: `App\Models\Role`

| الحقل | النوع | الوصف |
|-------|-------|-------|
| `name_ar` / `name_en` | string | اسم الدور |
| `slug` | string unique | المعرف الفريد |
| `level` | integer (1-10) | المستوى الأمني |
| `description` | text nullable | الوصف |
| `is_system` | boolean | دور نظامي (لا يمكن حذفه)؟ |

> ⚠️ **مهم**: الأدوار في سهر **شرفية فقط** — لا تمنح صلاحيات. الصلاحيات تُمنح عبر `user_permissions`.

---

### 3.2.8 جدول `permissions` — الصلاحيات

**النموذج**: `App\Models\Permission`

| الحقل | النوع | الوصف |
|-------|-------|-------|
| `name_ar` / `name_en` | string | اسم الصلاحية |
| `slug` | string unique | المعرف (`manage-attendance`, `view-payroll`...) |
| `group` | string | المجموعة (attendance, payroll, users...) |
| `description_en` | text nullable | الوصف |

**جدول** `user_permissions`:

| الحقل | الوصف |
|-------|-------|
| `user_id` | الموظف |
| `permission_id` | الصلاحية |
| `type` | enum(grant, revoke) — منح أو سحب |
| `expires_at` | datetime nullable — تاريخ الانتهاء |

---

### 3.2.9 جدول `whistleblower_reports` — البلاغات السرية

**النموذج**: `App\Models\WhistleblowerReport`

| الحقل | النوع | الوصف |
|-------|-------|-------|
| `ticket_number` | string unique | رقم التذكرة (`WB-YMDHIS-RAND`) |
| `encrypted_content` | text | المحتوى المشفّر (Laravel encrypt) |
| `category` | enum(corruption,harassment,...) | التصنيف |
| `severity` | enum(low,medium,high,critical) | الخطورة |
| `status` | enum(new,investigating,resolved,dismissed) | الحالة |
| `anonymous_token` | string(64) | رمز التتبع المجهول |
| `evidence_path` | string nullable | مسار الدليل المرفق |

#### الخصوصية والأمان
- المحتوى مشفّر عبر `encrypt()`/`decrypt()` من Laravel
- لا يمكن قراءة المحتوى إلا من صفحة الخزنة السرية (المستوى 10)
- صفحة التتبع لا تعرض المحتوى أبدًا — فقط الحالة والتحديثات

---

### 3.2.10 جداول التواصل

#### `conversations` — المحادثات
| الحقل | الوصف |
|-------|-------|
| `title` | عنوان المحادثة |
| `type` | نوع (direct, group) |
| `created_by` | المنشئ |

#### `conversation_participants` — المشاركون (جدول وسيط)
| الحقل | الوصف |
|-------|-------|
| `conversation_id` / `user_id` | المحادثة والمستخدم |
| `is_muted` | كتم الإشعارات |
| `last_read_at` | آخر قراءة |

#### `messages` — الرسائل
| الحقل | الوصف |
|-------|-------|
| `conversation_id` | المحادثة |
| `user_id` | المرسل |
| `body` | نص الرسالة |
| `type` | نوع (text, image, file) |

---

### 3.2.11 جداول التحفيز

#### `badges` — الشارات
| الحقل | الوصف |
|-------|-------|
| `name_ar` / `name_en` | اسم الشارة |
| `description_ar` / `description_en` | الوصف |
| `icon` | أيقونة |
| `color` | اللون |
| `points` | النقاط الممنوحة |
| `criteria_type` | نوع المعيار |
| `criteria_value` | قيمة المعيار |

#### `user_badges` — شارات المستخدمين
| الحقل | الوصف |
|-------|-------|
| `user_id` | الموظف |
| `badge_id` | الشارة |
| `awarded_at` | تاريخ المنح |
| `awarded_by` | من قبل |
| `context` | السياق |

**دالة مهمة**: `UserBadge::award($userId, $badgeSlug, $context)` — تمنح الشارة وتطلق حدث `BadgeAwarded`

#### `points_transactions` — معاملات النقاط
| الحقل | الوصف |
|-------|-------|
| `user_id` | الموظف |
| `points` | النقاط (+ أو -) |
| `type` | النوع (earned, deducted, bonus, manual) |
| `source_type` / `source_id` | المصدر (morphTo) |
| `description` | الوصف |

---

### 3.2.12 جداول الفخاخ النفسية

#### `traps` — تعريفات الفخاخ
| الحقل | الوصف |
|-------|-------|
| `name_ar` / `name_en` | اسم الفخ |
| `trap_code` | رمز الفخ الفريد |
| `trigger_type` | نوع التفعيل |
| `risk_weight` | وزن الخطر (0.1 - 5.0) |
| `target_levels` | JSON — المستويات المستهدفة |
| `fake_response` | JSON — الاستجابة الوهمية |
| `is_active` | نشط؟ |

#### `trap_interactions` — تفاعلات الفخاخ
| الحقل | الوصف |
|-------|-------|
| `trap_id` | الفخ |
| `user_id` | المستخدم المتفاعل |
| `risk_score` | درجة الخطر (0-100%) |
| `action_taken` | الإجراء (logged, warned, escalated) |
| `context_data` | JSON — بيانات السياق |
| `ip_address` | عنوان IP |

---

### 3.2.13 جداول التحليلات

#### `analytics_snapshots` — لقطات تحليلية
| الحقل | الوصف |
|-------|-------|
| `branch_id` | الفرع |
| `snapshot_date` | التاريخ |
| `period_type` | النوع (daily, weekly, monthly) |
| `metrics` | JSON — مقاييس شاملة |

#### `anomaly_logs` — سجلات الشذوذ
| الحقل | الوصف |
|-------|-------|
| `user_id` | الموظف |
| `anomaly_type` | نوع الشذوذ |
| `confidence` | درجة الثقة |
| `context_data` | JSON — بيانات السياق |

#### `employee_patterns` — أنماط الموظفين
| الحقل | الوصف |
|-------|-------|
| `user_id` | الموظف |
| `pattern_type` | نوع النمط |
| `frequency_score` | تكرار النمط |
| `financial_impact` | الأثر المالي |
| `risk_level` | مستوى الخطر |

#### `loss_alerts` — تنبيهات الخسارة
| الحقل | الوصف |
|-------|-------|
| `branch_id` | الفرع |
| `alert_type` | النوع |
| `severity` | الخطورة |
| `current_value` / `threshold_value` | القيمة الحالية والعتبة |

#### `work_rest_stats` — إحصائيات العمل والراحة
| الحقل | الوصف |
|-------|-------|
| `user_id` | الموظف |
| `date` | التاريخ |
| `work_minutes` / `rest_minutes` | دقائق العمل والراحة |
| `productivity_ratio` | نسبة الإنتاجية |
| `rating` | التقييم |

#### `sensor_readings` — قراءات أجهزة الاستشعار
| الحقل | الوصف |
|-------|-------|
| `user_id` | الموظف |
| `accelerometer_data` | بيانات مقياس التسارع |
| `variance` | التباين |
| `frequency` | التردد |
| `decibel_level` | مستوى الصوت |
| `work_probability` | احتمالية العمل (0-1) |
| `motion_signature` | توقيع الحركة |

---

### 3.2.14 جداول أخرى

#### `financial_reports` — التقارير المالية
- تقارير مجمّعة على مستوى الشركة/الفرع/القسم/الموظف
- إحصائيات الحضور + الأثر المالي للتأخير

#### `attendance_exceptions` — استثناءات الحضور
- أنواع: ساعات مرنة، عمل عن بعد، VIP، طبي
- تتجاوز قواعد السياج الجغرافي و/أو التأخير

#### `holidays` — العطلات
- أنواع: وطنية، دينية، خاصة بالشركة
- يمكن ربطها بفرع محدد أو تكون عامة

#### `circulars` — التعاميم + `circular_acknowledgments`
- نطاق الاستهداف: الكل / فرع / قسم / دور
- تتبع من قرأ التعميم

#### `audit_logs` — سجل التدقيق
- يسجل جميع العمليات المهمة
- `auditable_type` / `auditable_id` — نظام MorphTo

#### `employee_documents` — مستندات الموظفين
- 9 أنواع (هوية وطنية، جواز سفر، رخصة قيادة، شهادة...)
- تتبع تاريخ الإصدار والانتهاء

#### `employee_reminders` — تذكيرات الموظفين
- مفتاح التذكير + التاريخ + حالة الإكمال
- تنبيهات عاجلة للوثائق المنتهية

#### `report_formulas` — معادلات التقارير
- معادلات مخصصة تُقيَّم بأمان عبر Symfony ExpressionLanguage
- متغيرات: basic_salary, total_salary, delay_minutes, cost_per_minute...

#### `score_adjustments` — تعديلات التقييم
- تعديلات يدوية على النقاط والقيم المالية
- نطاق: فرع / موظف / قسم

#### `settings` — الإعدادات
- صف واحد (singleton) مع تخزين مؤقت ساعة واحدة
- يشمل: الشعار، الأيقونة، شاشة الترحيب، بيانات PWA، معاملات منطق العمل

---

## 3.3 مخطط العلاقات الرئيسية

```
users ─────────┬── branches (branch_id)
               ├── departments (department_id)
               ├── roles (role_id)
               ├── users (direct_manager_id) [self-reference]
               │
               ├──→ attendance_logs (user_id)
               ├──→ leave_requests (user_id)
               ├──→ payrolls (user_id)
               ├──→ user_badges (user_id) ←── badges
               ├──→ points_transactions (user_id)
               ├──→ user_permissions (user_id) ←── permissions
               ├──→ user_shifts (user_id) ←── shifts
               ├──→ employee_documents (user_id)
               ├──→ employee_reminders (user_id)
               ├──→ trap_interactions (user_id)
               ├──→ performance_alerts (user_id)
               ├──→ sensor_readings (user_id)
               ├──→ anomaly_logs (user_id)
               ├──→ employee_patterns (user_id)
               └──→ work_rest_stats (user_id)

branches ──────┬──→ departments (branch_id)
               ├──→ attendance_logs (branch_id)
               ├──→ holidays (branch_id) [nullable = عامة]
               ├──→ analytics_snapshots (branch_id)
               └──→ loss_alerts (branch_id)

departments ───┬── departments (parent_id) [self-reference]
               └── users (head_id)

roles ─────────┬──→ role_permission ←── permissions
               └── users (role_id)

circulars ─────┬── users (created_by)
               ├──→ circular_acknowledgments
               ├── branches (target_branch_id)
               └── departments (target_department_id)
```

---

> **السابق**: [هيكل المشروع](02-project-structure.md) | **التالي**: [الأدوار والصلاحيات](04-roles-permissions.md)
