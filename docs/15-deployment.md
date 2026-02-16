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
APP_NAME="سهر الإتقان"
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

QUEUE_CONNECTION=sync    # الاستضافة المشتركة لا تدعم workers
```

### ملاحظات الإنتاج

| الإعداد | القيمة | السبب |
|---------|--------|-------|
| `APP_DEBUG` | `false` | منع تسريب معلومات حساسة |
| `QUEUE_CONNECTION` | `sync` | لا يوجد supervisor على الاستضافة المشتركة |
| `SESSION_DRIVER` | `file` | أبسط وأموثق على الاستضافة المشتركة |
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
# → RoleSeeder: أدوار (مدير عام، مشرف، موظف...)
# → BadgeSeeder: شارات أساسية
# → TrapSeeder: فخاخ افتراضية
```

### 3. جدولة الأوامر

```bash
# أضف في Hostinger Cron Jobs:
* * * * * cd /home/u850419603/sarh && php artisan schedule:run >> /dev/null 2>&1
```

### 4. التحقق

| الفحص | الأمر/الرابط |
|-------|-------------|
| الموقع | `https://sarh.online` |
| لوحة الإدارة | `https://sarh.online/admin` |
| بوابة الموظف | `https://sarh.online/app` |
| البلاغات السرية | `https://sarh.online/whistleblower` |
| توثيق API | `https://sarh.online/docs/api` |

---

## 15.8 استكشاف الأخطاء

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
