# 4. نظام الأدوار والصلاحيات

## 4.1 نظرة عامة

يعتمد سهر على نظام أمان **هرمي من 10 مستويات** مع نظام صلاحيات فردي (Grant/Revoke). هذا التصميم يتيح:

1. **تحكم دقيق**: منح أو سحب صلاحيات فردية لكل موظف
2. **تدرج طبيعي**: المستويات الأعلى تملك صلاحيات ضمنية أوسع
3. **وضع الإله**: المستوى 10 و `is_super_admin` يتجاوزان جميع القيود

```
┌──────────────────────────────────────────────────────────────┐
│                   هرم المستويات الأمنية                      │
├──────────────────────────────────────────────────────────────┤
│  المستوى 10  │  الإله (God Mode)                             │
│              │  تجاوز كل البوابات + الخزنة السرية + الفخاخ  │
├──────────────────────────────────────────────────────────────┤
│  المستوى 8-9 │  إدارة عليا                                   │
│              │  مدير تنفيذي / مدير عمليات                    │
├──────────────────────────────────────────────────────────────┤
│  المستوى 7   │  مدير الموارد البشرية                          │
│              │  إدارة استثناءات الحضور                       │
├──────────────────────────────────────────────────────────────┤
│  المستوى 5-6 │  مشرفون ومديرو أقسام                          │
│              │  وصول لأغلب البيانات                          │
├──────────────────────────────────────────────────────────────┤
│  المستوى 4   │  الحد الأدنى لدخول لوحة الإدارة               │
│              │  عرض محدود                                    │
├──────────────────────────────────────────────────────────────┤
│  المستوى 1-3 │  موظفون عاديون                                │
│              │  بوابة الموظف فقط (PWA)                       │
└──────────────────────────────────────────────────────────────┘
```

---

## 4.2 آلية التحقق من الصلاحيات

### 4.2.1 بوابة God Mode (`Gate::before`)

```php
// AppServiceProvider.php
Gate::before(function ($user, $ability) {
    if ($user->security_level === 10 || $user->is_super_admin) {
        return true;   // ← تجاوز كل شيء
    }
});
```

> ⚠️ **هام**: لا يمكن لأي Policy أو Gate رفض وصول المستوى 10. هذا بتصميم متعمد.

### 4.2.2 ترتيب فحص الصلاحيات في `User::hasPermission()`

```
1. is_super_admin === true  → ✅ سماح فوري
2. user_permissions (type=revoke, slug=X) موجود وفعّال  → ❌ رفض فوري
3. user_permissions (type=grant, slug=X) موجود وفعّال   → ✅ سماح
4. security_level >= implied_level(slug)                → ✅ سماح ضمني
5. ← ❌ رفض افتراضي
```

### 4.2.3 الصلاحيات الضمنية (Implied)

المستوى الأمني يمنح صلاحيات ضمنية دون الحاجة لمنح صريح:

| المستوى | الصلاحيات الضمنية |
|---------|-------------------|
| 10 | كل شيء |
| 7+ | إدارة استثناءات الحضور |
| 5+ | عرض التقارير المالية، عرض تقارير الأداء |
| 4+ | دخول لوحة الإدارة |

---

## 4.3 البوابات المسماة (Named Gates)

| البوابة | الشرط | الاستخدام |
|---------|-------|-----------|
| `access-whistleblower-vault` | level ≥ 10 | فتح الخزنة السرية |
| `bypass-geofence` | level ≥ 10 أو super_admin | تجاوز السياج الجغرافي |
| `manage-competition` | level ≥ 10 أو super_admin | إدارة المسابقات |
| `adjust-points` | level ≥ 10 أو super_admin | تعديل النقاط |
| `manage-roles` | level ≥ 10 أو super_admin | إدارة الأدوار |
| `manage-permissions` | level ≥ 10 أو super_admin | إدارة الصلاحيات |
| `manage-attendance-exceptions` | level ≥ 7 أو super_admin | إدارة استثناءات الحضور |
| `manage-score-adjustments` | level ≥ 10 أو super_admin | تعديلات التقييم |
| `manage-report-formulas` | level ≥ 10 أو super_admin | إدارة معادلات التقارير |
| `access-stealth-resources` | level ≥ 10 أو super_admin | الموارد المخفية |
| `access-trap-audit` | level ≥ 10 أو super_admin | سجل تدقيق الفخاخ |

---

## 4.4 السياسات (Policies)

تم تسجيل **11 سياسة** في `AppServiceProvider`:

| النموذج | السياسة | القواعد الرئيسية |
|---------|---------|------------------|
| `User` | `UserPolicy` | عرض: level ≥ 4، إنشاء/تعديل: level ≥ 7، حذف: level ≥ 10، منع حذف النفس |
| `AttendanceLog` | `AttendanceLogPolicy` | عرض: level ≥ 4 أو مالك السجل، تعديل: level ≥ 7، حذف: level ≥ 10 |
| `Payroll` | `PayrollPolicy` | عرض: level ≥ 5 أو مالك الكشف، إنشاء/تعديل: level ≥ 7، اعتماد: level ≥ 8 |
| `LeaveRequest` | `LeaveRequestPolicy` | عرض: level ≥ 4 أو مقدم الطلب، إنشاء: أي موظف، اعتماد/رفض: level ≥ 5 |
| `EmployeeDocument` | `EmployeeDocumentPolicy` | عرض: level ≥ 5 أو مالك المستند، إنشاء/تعديل: level ≥ 5 |
| `PerformanceAlert` | `PerformanceAlertPolicy` | عرض: level ≥ 5، إنشاء/تعديل: level ≥ 7 |
| `Circular` | `CircularPolicy` | عرض: أي مسجل، إنشاء: level ≥ 5، تعديل: level ≥ 5 أو المنشئ |
| `Holiday` | `HolidayPolicy` | عرض: أي مسجل، إنشاء/تعديل/حذف: level ≥ 5 |
| `Branch` | `BranchPolicy` | عرض: level ≥ 4، إنشاء/تعديل: level ≥ 7، حذف: level ≥ 10 |
| `Department` | `DepartmentPolicy` | عرض: level ≥ 4، إنشاء/تعديل: level ≥ 6، حذف: level ≥ 9 |
| `Shift` | `ShiftPolicy` | عرض: level ≥ 4، إنشاء/تعديل: level ≥ 6، حذف: level ≥ 8 |

### بنية السياسة النموذجية

```php
class ExamplePolicy
{
    // عرض أي سجل
    public function viewAny(User $user): bool
    {
        return $user->security_level >= 4;
    }

    // عرض سجل محدد
    public function view(User $user, Model $record): bool
    {
        return $user->security_level >= 4
            || $user->id === $record->user_id;   // المالك يرى بياناته
    }

    // إنشاء سجل جديد
    public function create(User $user): bool
    {
        return $user->security_level >= 7;
    }

    // لا نحذف — نؤرشف (SoftDelete)
    public function delete(User $user, Model $record): bool
    {
        return $user->security_level >= 10;
    }
}
```

---

## 4.5 التحكم في واجهة Filament

### 4.5.1 `canAccessPanel()`

```php
// User Model
public function canAccessPanel(Panel $panel): bool
{
    return match ($panel->getId()) {
        'admin' => $this->security_level >= 4,
        'app'   => true,   // بوابة الموظف متاحة للجميع
        default => false,
    };
}
```

### 4.5.2 `canAccess()` في الموارد والصفحات

كل مورد وصفحة في Filament تُحدد حد الوصول الأدنى:

```php
// مثال: FinancialReportsPage
public static function canAccess(): bool
{
    return auth()->user()?->security_level >= 7;
}
```

| المكون | الحد الأدنى |
|--------|-------------|
| موارد عامة (Users, Attendance, etc.) | 4 |
| صفحات مالية | 7 |
| صفحات التحليلات المتقدمة | 8 |
| صفحة بيانات النشر | 10 |
| صفحة الخزنة السرية | 10 |
| أدوات الفخاخ | 10 |

---

## 4.6 نظام الصلاحيات الفردية

### جدول `user_permissions`

```sql
-- منح صلاحية
INSERT INTO user_permissions (user_id, permission_id, type) VALUES (5, 3, 'grant');

-- سحب صلاحية (أقوى من المنح)
INSERT INTO user_permissions (user_id, permission_id, type) VALUES (5, 7, 'revoke');

-- صلاحية مؤقتة (تنتهي تلقائيًا)
INSERT INTO user_permissions (user_id, permission_id, type, expires_at) 
VALUES (5, 3, 'grant', '2026-03-01 00:00:00');
```

### أولوية الحسم

```
الأقوى ──────────────────────────── الأضعف
revoke فردي > grant فردي > صلاحية ضمنية > رفض
```

### مجموعات الصلاحيات

| المجموعة | أمثلة |
|----------|-------|
| `attendance` | `manage-attendance`, `view-attendance-reports` |
| `payroll` | `view-payroll`, `generate-payroll`, `approve-payroll` |
| `users` | `manage-users`, `view-users`, `delete-users` |
| `leaves` | `manage-leave-requests`, `approve-leaves` |
| `reports` | `view-financial-reports`, `export-reports` |
| `settings` | `manage-settings`, `manage-branches` |

---

## 4.7 سيناريوهات شائعة

### سيناريو 1: موظف عادي (مستوى 2)
- ✅ بوابة الموظف PWA
- ✅ تسجيل حضور/انصراف
- ✅ عرض بياناته الشخصية فقط
- ❌ لوحة الإدارة

### سيناريو 2: مشرف (مستوى 5) مع سحب صلاحية
- ✅ لوحة الإدارة
- ✅ معظم الصلاحيات الضمنية للمستوى 5
- ❌ عرض كشوف الرواتب (محجوب بـ `revoke` فردي)

### سيناريو 3: موظف (مستوى 2) مع منح صلاحية مؤقتة
- ✅ بوابة الموظف
- ✅ عرض تقارير الأداء (ممنوح مؤقتًا حتى تاريخ محدد)
- بعد انتهاء الصلاحية يعود للوضع الافتراضي

### سيناريو 4: مدير عام (مستوى 10)
- ✅ **كل شيء** بدون استثناء
- ✅ الخزنة السرية + سجلات الفخاخ
- ✅ تجاوز السياج الجغرافي
- ❌ لا يمكن لأي `revoke` أن يؤثر عليه (Gate::before يتجاوز)

---

> **السابق**: [قاعدة البيانات والنماذج](03-database-models.md) | **التالي**: [مكونات Filament](05-filament-components.md)
