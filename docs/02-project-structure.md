# 2. هيكل المشروع

## 2.1 نظرة عامة على المجلدات

```
sarh/
├── app/                    ← الكود الرئيسي للتطبيق
│   ├── Console/            ← أوامر Artisan المخصصة
│   ├── Events/             ← الأحداث (Events)
│   ├── Exceptions/         ← الاستثناءات المخصصة
│   ├── Filament/           ← مكونات لوحة الإدارة
│   ├── Helpers/            ← الدوال المساعدة
│   ├── Http/               ← المتحكمات والوسيطات
│   ├── Jobs/               ← الوظائف المؤجلة (Queued Jobs)
│   ├── Listeners/          ← مستمعات الأحداث
│   ├── Livewire/           ← مكونات بوابة الموظف
│   ├── Models/             ← نماذج قاعدة البيانات
│   ├── Policies/           ← سياسات التفويض
│   ├── Providers/          ← مزودات الخدمة
│   └── Services/           ← خدمات منطق العمل
├── bootstrap/              ← ملفات إقلاع Laravel
├── config/                 ← ملفات الإعدادات
├── database/               ← التهجيرات والبذور والمصانع
├── docs/                   ← التوثيق (أنت هنا)
├── lang/                   ← ملفات الترجمة (ar/en)
├── public/                 ← الملفات العامة (نقطة الدخول)
├── resources/              ← العروض والأصول
├── routes/                 ← تعريف المسارات
├── storage/                ← التخزين المحلي والسجلات
├── tests/                  ← الاختبارات
└── vendor/                 ← حزم Composer (لا تُعدّل)
```

## 2.2 مجلد app/ — التفصيل الكامل

### app/Console/Commands/
أوامر Artisan المخصصة التي يمكن تشغيلها من سطر الأوامر:

| الملف | الأمر | الوصف |
|-------|-------|-------|
| `InstallCommand.php` | `sarh:install` | معالج التثبيت الكامل |
| `AutoDocumentCommand.php` | `sarh:auto-document` | توليد التوثيق تلقائيًا |
| `RunAnalyticsCommand.php` | `sarh:analytics` | تشغيل محرك التحليلات |
| `GeneratePayrollCommand.php` | `sarh:payroll` | توليد كشوف الرواتب |
| `DailyTelemetryStatsCommand.php` | `telemetry:daily-stats` | إحصائيات أجهزة الاستشعار |

### app/Events/
الأحداث التي يُطلقها النظام عند حدوث عمليات معينة:

| الملف | متى يُطلق؟ | البيانات المرفقة |
|-------|-----------|-----------------|
| `AttendanceRecorded.php` | عند تسجيل حضور/انصراف | سجل الحضور `AttendanceLog` |
| `BadgeAwarded.php` | عند منح شارة لموظف | شارة المستخدم `UserBadge` |
| `AnomalyDetected.php` | عند اكتشاف شذوذ في البيانات | المستخدم + القراءة + سجل الشذوذ |
| `TrapTriggered.php` | عند تفعيل فخ نفسي | الفخ + المستخدم + التفاعل |

### app/Exceptions/
استثناءات مخصصة للتعامل مع حالات استثنائية:

| الملف | الاستخدام |
|-------|-----------|
| `BusinessException.php` | أخطاء منطق العمل (مثل: الموظف غير نشط) |
| `OutOfGeofenceException.php` | محاولة تسجيل حضور خارج النطاق الجغرافي |

### app/Filament/
مكونات لوحة الإدارة المبنية على Filament v3:

```
Filament/
├── App/                    ← بوابة الموظف (/app)
│   ├── Pages/              ← صفحات الموظف
│   ├── Resources/          ← موارد الموظف (حضوره، إجازاته)
│   └── Widgets/            ← ويدجات لوحة الموظف
├── Pages/                  ← صفحات الإدارة (14 صفحة)
├── Resources/              ← موارد الإدارة (21 مورد)
└── Widgets/                ← ويدجات لوحة الإدارة (11 ويدجت)
```

→ تفاصيل كاملة في [مكونات Filament](05-filament-components.md)

### app/Helpers/
دوال مساعدة عامة:

| الملف | الدالة | الوصف |
|-------|--------|-------|
| `ArabicHelper.php` | `toArabicDigits()` | تحويل الأرقام الغربية (0-9) إلى أرقام عربية (٠-٩) |

### app/Http/
المتحكمات والوسيطات:

#### Middleware/
| الملف | الوظيفة |
|-------|---------|
| `EnsureAdminPanelAccess.php` | يمنع الموظفين (المستوى < 4) من الوصول للوحة الإدارة |
| `SetPermissionsPolicy.php` | يضيف رؤوس أمان HTTP: يسمح بالموقع الجغرافي ويمنع الكاميرا والميكروفون |

### app/Jobs/
الوظائف المؤجلة التي تعمل في الخلفية:

| الملف | الوظيفة | المهلة | المحاولات |
|-------|---------|--------|-----------|
| `ProcessAttendanceJob.php` | معالجة تسجيل الحضور عبر الطابور | 30 ثانية | 3 |
| `RecalculateMonthlyAttendanceJob.php` | إعادة حساب الحضور والمالية الشهرية | 300 ثانية | 1 |
| `SendCircularJob.php` | إرسال التعاميم كإشعارات (دفعات من 100) | — | — |

### app/Listeners/
مستمعات الأحداث — تنفذ منطقًا عند إطلاق حدث:

| الملف | يستمع لـ | ماذا يفعل |
|-------|----------|----------|
| `HandleAttendanceRecorded.php` | `AttendanceRecorded` | ينشئ سجل تدقيق `AuditLog` |
| `HandleBadgePoints.php` | `BadgeAwarded` | ينشئ تنبيه أداء تهنئة |
| `HandleAnomalyDetected.php` | `AnomalyDetected` | ينشئ تنبيه أداء بتفاصيل الشذوذ |
| `HandleTrapTriggered.php` | `TrapTriggered` | يسجل في قناة سجل يومية، حرج عند التصعيد |

### app/Livewire/
مكونات Livewire لبوابة الموظف (12 مكون):

| الملف | المسار | الوظيفة |
|-------|--------|---------|
| `EmployeeDashboard.php` | `/dashboard` | لوحة الموظف الرئيسية |
| `AttendanceWidget.php` | ويدجت | تسجيل الحضور بالـ GPS |
| `AttendanceStatsWidget.php` | ويدجت | إحصائيات الحضور الشهرية |
| `FinancialWidget.php` | ويدجت | تأثير التأخير المالي |
| `GamificationWidget.php` | ويدجت | النقاط والشارات والسلاسل |
| `CompetitionWidget.php` | ويدجت | لوحة منافسة الفروع |
| `BranchProgressWidget.php` | ويدجت | تقدم الفرع الحالي |
| `CircularsWidget.php` | ويدجت | التعاميم النشطة |
| `MessagingInbox.php` | `/messaging` | صندوق المحادثات |
| `MessagingChat.php` | `/messaging/{id}` | المحادثة المباشرة |
| `WhistleblowerForm.php` | `/whistleblower` | نموذج البلاغ السري (مجهول) |
| `WhistleblowerTrack.php` | `/whistleblower/track` | تتبع البلاغ بالرمز السري |

### app/Models/
نماذج Eloquent — 37 نموذج يمثّل جداول قاعدة البيانات:

→ تفاصيل كاملة في [قاعدة البيانات والنماذج](03-database-models.md)

### app/Policies/
سياسات التفويض التي تحدد من يستطيع فعل ماذا على مستوى السجل:

→ تفاصيل كاملة في [الأدوار والصلاحيات](04-roles-permissions.md)

### app/Providers/
مزودات الخدمة — تُنفَّذ عند إقلاع التطبيق:

| الملف | المسؤوليات |
|-------|-----------|
| `AppServiceProvider.php` | تسجيل الأحداث والمستمعات، تعريف البوابات (Gates)، تسجيل السياسات (Policies)، إعداد Blade و Scramble |

### app/Services/
خدمات منطق العمل — الطبقة الوسيطة بين المتحكمات والنماذج:

| الملف | المسؤولية |
|-------|-----------|
| `AttendanceService.php` | تسجيل الحضور والانصراف مع السياج الجغرافي |
| `GeofencingService.php` | حساب المسافات والتحقق من النطاق الجغرافي |
| `FinancialReportingService.php` | حساب الخسائر المالية وتحليل التأخير |
| `FormulaEngineService.php` | تقييم المعادلات المالية المخصصة |
| `AnalyticsService.php` | محرك التحليلات الشامل (889 سطر) |
| `TelemetryService.php` | معالجة بيانات أجهزة الاستشعار |
| `TrapResponseService.php` | معالجة تفاعلات الفخاخ النفسية |
| `AnomalyDetector.php` | كشف الشذوذ المتقدم في البيانات |

→ تفاصيل كاملة في [الأوامر والخدمات](13-commands-services.md)

## 2.3 مجلد config/

| الملف | الوصف |
|-------|-------|
| `app.php` | إعدادات التطبيق: اللغة العربية، منطقة زمنية UTC، Faker بالعربية |
| `auth.php` | إعدادات المصادقة |
| `cache.php` | إعدادات التخزين المؤقت (file driver) |
| `database.php` | إعدادات قاعدة البيانات (MySQL) |
| `filesystems.php` | إعدادات نظام الملفات (local + public disks) |
| `logging.php` | إعدادات التسجيل (daily channels) |
| `mail.php` | إعدادات البريد الإلكتروني |
| `queue.php` | إعدادات الطوابير (database driver) |
| `scramble.php` | إعدادات توثيق API (محمي بـ auth middleware) |
| `session.php` | إعدادات الجلسات (file driver, SameSite=lax) |
| `services.php` | إعدادات الخدمات الخارجية |
| `telemetry.php` | إعدادات القياس عن بعد (معدلات العينات، الأوزان، العتبات) |

## 2.4 مجلد database/

```
database/
├── factories/          ← مصانع البيانات الوهمية للاختبارات
├── migrations/         ← 35+ ملف تهجير لإنشاء الجداول
└── seeders/            ← بذور البيانات الأولية
    ├── DatabaseSeeder.php                    ← المنسّق الرئيسي
    ├── RolesAndPermissionsSeeder.php         ← الأدوار والصلاحيات
    ├── BadgesSeeder.php                      ← الشارات
    ├── TrapsSeeder.php                       ← الفخاخ النفسية
    ├── ProjectDataSeeder.php                 ← بيانات المشروع النموذجية
    └── FixUserShiftsDataSeeder.php           ← إصلاح ربط الورديات
```

## 2.5 مجلد lang/

```
lang/
├── ar/                 ← الترجمة العربية (13 ملف)
│   ├── analytics.php   ← مصطلحات التحليلات
│   ├── app.php         ← مصطلحات عامة
│   ├── attendance.php  ← مصطلحات الحضور
│   ├── branches.php    ← مصطلحات الفروع
│   ├── circulars.php   ← مصطلحات التعاميم
│   ├── command.php     ← مصطلحات الأوامر
│   ├── competition.php ← مصطلحات المنافسة
│   ├── dashboard.php   ← مصطلحات لوحة القيادة
│   ├── holidays.php    ← مصطلحات العطلات
│   ├── install.php     ← مصطلحات التثبيت
│   ├── leaves.php      ← مصطلحات الإجازات
│   ├── pwa.php         ← مصطلحات التطبيق التقدمي
│   └── users.php       ← مصطلحات المستخدمين
└── en/                 ← الترجمة الإنجليزية
    └── analytics.php   ← مصطلحات التحليلات بالإنجليزية
```

## 2.6 مجلد resources/

```
resources/
├── css/
│   └── app.css                     ← أنماط Tailwind الرئيسية
├── js/
│   └── app.js                      ← JavaScript الرئيسي (Axios)
└── views/
    ├── welcome.blade.php           ← صفحة الهبوط
    ├── layouts/
    │   └── pwa.blade.php           ← تخطيط PWA لبوابة الموظف
    ├── livewire/                   ← عروض مكونات Livewire (12 ملف)
    ├── filament/
    │   ├── pages/                  ← عروض صفحات Filament (14 ملف)
    │   ├── widgets/                ← عروض ويدجات الإدارة (6 ملفات)
    │   └── app/                    ← عروض بوابة الموظف
    └── components/
        ├── map-picker.blade.php         ← مكون اختيار الموقع (Leaflet)
        ├── arabic-numerals.blade.php    ← مكون الأرقام العربية
        └── pwa-install-button.blade.php ← زر تثبيت PWA
```

## 2.7 مجلد routes/

| الملف | المحتوى |
|-------|---------|
| `web.php` | جميع مسارات الويب: API الحضور، القياس عن بعد، الفخاخ، البلاغات، PWA manifest |
| `console.php` | المهام المجدولة (analytics يومي، telemetry يومي، recalculate شهري) |

## 2.8 مجلد public/

| الملف | الوظيفة |
|-------|---------|
| `index.php` | نقطة الدخول الرئيسية (مسارات نسبية) |
| `robots.txt` | تعليمات محركات البحث |
| `generate_icon.php` | توليد أيقونات PWA |
| `build/` | ملفات Vite المبنية (CSS + JS) |

## 2.9 ملفات الجذر المهمة

| الملف | الوظيفة |
|-------|---------|
| `artisan` | نقطة دخول أوامر Artisan |
| `composer.json` | تبعيات PHP وإعدادات Composer |
| `package.json` | تبعيات JavaScript وسكريبتات البناء |
| `vite.config.js` | إعدادات Vite لبناء الأصول |
| `tailwind.config.js` | إعدادات Tailwind CSS |
| `phpunit.xml` | إعدادات PHPUnit للاختبارات |
| `deploy.sh` | سكريبت النشر الكامل |
| `deploy-quick.sh` | سكريبت النشر السريع (git pull فقط) |

---

> **السابق**: [نظرة عامة](01-overview.md) | **التالي**: [قاعدة البيانات والنماذج](03-database-models.md)
