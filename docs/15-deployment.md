# 15. النشر والتشغيل

## 15.1 بيئة الإنتاج

| المعلومة | القيمة |
|----------|--------|
| **الاستضافة** | Hostinger (استضافة مشتركة) |
| **النطاق** | sarh.online |
| **SSH** | `ssh -p 65002 u850419603@145.223.119.139` |
| **مسار المشروع** | `/home/u850419603/sarh` |
| **مسار النطاق** | `/home/u850419603/domains/sarh.online/public_html` |
| **المستودع** | GitHub (خاص) |

---

## 15.2 هيكل النشر على Hostinger

```
/home/u850419603/
├── sarh/                           ← مشروع Laravel كامل
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/                     ← public محلي (للتطوير)
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   └── vendor/
│
└── domains/sarh.online/
    └── public_html/                ← ما يراه الزائر
        ├── index.php               ← جسر بمسارات مطلقة
        ├── .htaccess
        ├── build/                  ← Vite assets
        ├── css/ & js/
        └── storage → symlink
```

> **مهم**: `public_html/index.php` يستخدم مسارات **مطلقة** للربط بالمشروع، وليس `__DIR__` النسبية.

---

## 15.3 سكريبت النشر (deploy.sh)

### الأمر

```bash
bash deploy.sh
```

### الخطوات (9 مراحل)

| # | الخطوة | الوصف |
|---|--------|-------|
| 0 | Clone/Pull | أول مرة: clone، بعدها: `git reset --hard origin/main` |
| 1 | Composer | `composer install --optimize-autoloader --no-interaction` |
| 2 | .env | نسخ `.env.production` وتوليد `APP_KEY` |
| 2.5 | Session | فرض بروتوكول الجلسة المُحصّن |
| 3 | Migrate+Seed | `php artisan migrate --force --seed` |
| 4 | Frontend | `npm install && npm run build` |
| 5 | Storage | رابط رمزي `storage/app/public` |
| 6 | Deploy | نسخ public إلى `public_html` + إنشاء جسر `index.php` |
| 7 | Cache Clear | `optimize:clear` + مسح الجلسات القديمة |
| 8 | Optimize | `config:cache` + `event:cache` |

### بروتوكول الجلسة المُحصّن

```bash
SESSION_DRIVER=file
SESSION_ENCRYPT=false
SESSION_DOMAIN=null        # كشف تلقائي
SESSION_SECURE_COOKIE=true # HTTPS فقط
SESSION_SAME_SITE=lax
SESSION_LIFETIME=120
```

> ⚠️ **route:cache محظور** — يكسر مسارات Filament v3 القائمة على Closures.

---

## 15.4 إعداد .env للإنتاج

```env
APP_NAME="مؤشر صرح"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sarh.online

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sarh_db
DB_USERNAME=u850419603_sarh
DB_PASSWORD=*****

SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true

MAIL_MAILER=smtp
# ...

QUEUE_CONNECTION=database  # قاعدة بيانات لمعالجة المهام
CACHE_STORE=file           # مناسب لـ 100 مستخدم
```

### ملاحظات الإنتاج

| الإعداد | القيمة | السبب |
|---------|--------|-------|
| `APP_DEBUG` | `false` | منع تسريب معلومات حساسة |
| `QUEUE_CONNECTION` | `database` | معالجة المهام عبر قاعدة البيانات (بديل آمن عن sync) |
| `SESSION_DRIVER` | `file` | أبسط وأموثق على الاستضافة المشتركة |
| `SESSION_SAME_SITE` | `lax` | حماية الجلسات مع توافق Livewire |
| `CACHE_STORE` | `file` | لا يوجد Redis |

---

## 15.5 النشر السريع (deploy-quick.sh)

```bash
bash deploy-quick.sh
```

نسخة مختصرة: pull + optimize فقط (بدون composer أو npm).

---

## 15.6 إعداد SSH

```bash
bash setup-ssh-key.sh
```

يُنشئ مفتاح SSH ويُضيفه للمستودع. التفاصيل في [SSH_SETUP.md](../SSH_SETUP.md).

---

## 15.7 بعد النشر

### 1. إنشاء المدير الأعلى

```bash
php artisan sarh:install
# → يُنشئ حساب المستوى 10 تفاعليًا
```

### 2. بذر البيانات الأساسية

```bash
php artisan db:seed
# → RolesAndPermissionsSeeder: 10 أدوار + 46 صلاحية
# → BadgesSeeder: 7 شارات أساسية
# → TrapsSeeder: 4 فخاخ نفسية
# → ProjectDataSeeder: 5 فروع + مدير أعلى + 35 موظف
# → DepartmentSeeder: 7 أقسام أساسية
# → HolidaySeeder: عطلات 2026 الرسمية
```

### 3. جدولة الأوامر

> ⚠️ في Hostinger لا يتوفر `crontab` عبر SSH. استخدم **لوحة hPanel** لإضافة Cron Jobs.

```bash
# أضف في Hostinger hPanel → Cron Jobs:
# الأمر: cd /home/u850419603/sarh && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
# التكرار: كل دقيقة (* * * * *)
```

### 3.1 النسخ الاحتياطي اليومي

```bash
# أضف في Hostinger hPanel → Cron Jobs:
# الأمر: cd /home/u850419603/sarh && bash backup-db.sh >> /home/u850419603/backups/backup.log 2>&1
# التكرار: يوميًا الساعة 2:00 صباحًا (0 2 * * *)
```

سكريبت `backup-db.sh` يقوم بـ:
- قراءة بيانات الاتصال من `.env` تلقائيًا
- إنشاء نسخة مضغوطة `sarh_YYYYMMDD_HHMM.sql.gz`
- الاحتفاظ بآخر 30 نسخة وحذف الأقدم

### 4. التحقق

| الفحص | الأمر/الرابط |
|-------|-------------|
| الموقع | `https://sarh.online` |
| لوحة الإدارة | `https://sarh.online/admin` |
| بوابة الموظف | `https://sarh.online/app` |
| البلاغات السرية | `https://sarh.online/whistleblower` |
| تحميل التطبيق | `https://sarh.online/app/download` |
| فحص إصدار التطبيق | `https://sarh.online/api/app-version` |
| توثيق API | `https://sarh.online/docs/api` |

---

## 15.8 تطبيق الموبايل (Android)

### بنية المشروع

المشروع في مجلد `mobile/` — يُفتح في Android Studio.

### بناء APK

```bash
# في Android Studio:
# Build → Generate Signed Bundle / APK → APK
# اختر release → وقّع بمفتاح sarh-release-key.jks
```

### نشر APK على الخادم

```bash
# 1. انسخ APK الموقّع إلى الخادم
scp -P 65002 app-release.apk u850419603@145.223.119.139:~/domains/sarh.online/public_html/apk/sarh.apk

# 2. رابط التحميل: https://sarh.online/apk/sarh.apk
# 3. صفحة التحميل: https://sarh.online/app/download
```

### تحديث الإصدار

عند إصدار نسخة جديدة:
1. عدّل `versionCode` و `versionName` في `mobile/app/build.gradle.kts`
2. عدّل قيم `/api/app-version` في `routes/web.php`
3. أعد بناء APK ووقّعه **بنفس المفتاح**
4. استبدل الملف على الخادم

---

## 15.9 استكشاف الأخطاء

### خطأ 500

```bash
# 1. فحص السجلات
tail -50 /home/u850419603/sarh/storage/logs/laravel.log

# 2. مسح الكاش
php artisan optimize:clear
php artisan config:cache
```

### خطأ في الجلسات (Session)

```bash
rm -f storage/framework/sessions/*
php artisan config:cache
```

### صفحة بيضاء

```bash
# التحقق من صلاحيات storage
chmod -R 775 storage bootstrap/cache
```

### أخطاء Livewire/Filament

```bash
# مسح views المخزنة مؤقتًا
php artisan view:clear
php artisan filament:cache-components
```

---

> **السابق**: [التصميم والواجهة](14-ui-design.md) | **التالي**: [دليل التطوير](16-development-guide.md)
