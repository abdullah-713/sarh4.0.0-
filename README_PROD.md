# مؤشر صرح — دليل النشر الإنتاجي
> **الإصدار:** 1.9.0 | **التاريخ:** 2026-02-08
> **البيئة المستهدفة:** استضافة مشتركة (Hostinger)، خادم VPS، أو خادم مخصص
> **المتطلبات:** PHP 8.2+، MySQL 8.0+، Composer، Node.js 18+

---

## 1. متطلبات الخادم

| المتطلب | الحد الأدنى | الموصى به |
|---------|------------|----------|
| PHP | 8.2 | 8.3+ |
| MySQL/MariaDB | 8.0 / 10.6 | 8.0+ / 10.11+ |
| Composer | 2.x | أحدث إصدار |
| Node.js | 18.x | 20.x |
| مساحة القرص | 500 ميجابايت | 1 جيجابايت+ |
| الذاكرة | 512 ميجابايت | 1 جيجابايت+ |

### إضافات PHP المطلوبة
```
openssl, pdo, pdo_mysql, mbstring, tokenizer, xml, ctype, json,
bcmath, fileinfo, curl, gd
```

---

## 2. خطوات النشر

### 2.1 رفع ملفات المشروع

**الخيار أ: استنساخ Git (خادم VPS)**
```bash
cd /var/www
git clone <repository_url> sarh
cd sarh
```

**الخيار ب: رفع الملفات (استضافة مشتركة / Hostinger)**
1. ارفع المشروع كملف مضغوط إلى المجلد الرئيسي
2. فك الضغط إلى `/home/user/sarh/`
3. اضبط جذر الويب (Document Root) على: `/home/user/sarh/public`

### 2.2 تثبيت المكتبات

```bash
cd /path/to/sarh

# مكتبات PHP
composer install --no-dev --optimize-autoloader

# مكتبات الواجهة
npm ci
npm run build
```

### 2.3 إعداد ملف البيئة

```bash
cp .env.example .env
php artisan key:generate
```

عدّل ملف `.env`:
```ini
APP_NAME="مؤشر صرح"
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Asia/Riyadh
APP_URL=https://yourdomain.com
APP_LOCALE=ar
APP_FALLBACK_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sarh
DB_USERNAME=sarh_user
DB_PASSWORD=<كلمة_مرور_قوية>

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

FILAMENT_PATH=admin
```

### 2.4 تشغيل مُثبِّت مؤشر صرح

```bash
php artisan sarh:install
```

سيقوم هذا الأمر بـ:
1. التحقق من البيئة (إصدار PHP، الإضافات، اتصال قاعدة البيانات)
2. تشغيل جميع الترحيلات (26+ جدول)
3. بذر الأدوار (10 مستويات)، الصلاحيات (42)، الشارات (8)، المصائد (4)
4. طلب إنشاء حساب المدير العام الأولي (المستوى 10)
5. إنشاء رابط التخزين وتخزين الإعدادات المؤقتة

### 2.5 صلاحيات المجلدات

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 3. تعليمات خاصة بـ Hostinger

### 3.1 جذر الويب (الرابط الرمزي)

استضافة Hostinger المشتركة تخدم الملفات من `public_html/`. أنشئ رابطاً رمزياً:

```bash
# من طرفية SSH (خطة Hostinger Business فما فوق)
cd ~/
rm -rf public_html
ln -s /home/user/sarh/public public_html
```

**إذا لم يتوفر SSH:** استخدم مدير الملفات لإعادة تسمية `public_html` إلى `public_html_bak`، ثم أنشئ إعادة توجيه PHP:

```php
// ~/public_html/index.php
<?php
require __DIR__ . '/../sarh/public/index.php';
```

### 3.2 إصدار PHP

في لوحة تحكم Hostinger (hPanel) → **متقدم** → **إعدادات PHP**:
- اضبط إصدار PHP على **8.2** أو **8.3**
- فعّل الإضافات: `pdo_mysql`, `bcmath`, `fileinfo`, `gd`

### 3.3 المهام المجدولة (اختياري — لمعالجة الطوابير)

في hPanel → **متقدم** → **المهام المجدولة**:
```
* * * * * cd /home/user/sarh && php artisan schedule:run >> /dev/null 2>&1
```

### 3.4 شهادة SSL

فعّل SSL في hPanel → **الأمان** → **SSL** → Let's Encrypt. ثم حدّث `.env`:
```ini
APP_URL=https://yourdomain.com
```

---

## 4. خادم VPS / خادم مخصص (Nginx)

### 4.1 إعدادات Nginx

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com;
    root /var/www/sarh/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 4.2 المشرف (معالج الطوابير — Supervisor)

```ini
[program:sarh-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sarh/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/sarh/storage/logs/worker.log
stopwaitsecs=3600
```

---

## 5. قائمة تحسين الأداء الإنتاجي

```bash
# تخزين الإعدادات والمسارات لتحسين الأداء
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# تحسين محمّل Composer التلقائي
composer install --no-dev --optimize-autoloader

# بناء أصول الواجهة
npm run build

# التحقق من رابط التخزين
php artisan storage:link
```

---

## 6. تصلّب أمني

### 6.1 ملف البيئة
- [ ] `APP_DEBUG=false` — **لا تضبطه أبداً** على `true` في بيئة الإنتاج
- [ ] `APP_ENV=production`
- [ ] مفتاح `APP_KEY` قوي (يُنشأ بأمر `key:generate`)
- [ ] كلمة مرور قوية لقاعدة البيانات

### 6.2 صلاحيات الملفات
- [ ] ملف `.env`: `chmod 600 .env` (قراءة/كتابة للمالك فقط)
- [ ] `storage/` و `bootstrap/cache/`: `chmod 775`
- [ ] باقي الملفات: `chmod 644`، المجلدات: `chmod 755`

### 6.3 خادم الويب
- [ ] حظر الوصول إلى ملف `.env` عبر إعدادات الخادم
- [ ] جذر الويب يشير فقط إلى `public/` — وليس إلى جذر المشروع
- [ ] HTTPS (SSL) مفعّل

### 6.4 خاص بنظام مؤشر صرح
- [ ] صفحات قبو المستوى 10 (`/admin/whistleblower-vault`، `/admin/trap-audit`) محمية بـ `security_level >= 10`
- [ ] محتوى البلاغات مشفر بـ AES-256-CBC
- [ ] عزل بيانات الفروع مفعّل (غير المدير العام يرى فرعه فقط)
- [ ] سجل المراجعة يوثق كل وصول للقبو

---

## 7. استراتيجية النسخ الاحتياطي

### 7.1 نسخ قاعدة البيانات
```bash
# نسخ احتياطي يومي تلقائي
mysqldump -u sarh_user -p sarh > /backups/sarh_$(date +%Y%m%d_%H%M).sql

# أو عبر Laravel
php artisan backup:run  # (يتطلب حزمة spatie/laravel-backup)
```

### 7.2 نسخ الملفات
```bash
# نسخ احتياطي للملفات المرفوعة وملف البيئة
tar -czf /backups/sarh_files_$(date +%Y%m%d).tar.gz \
    /var/www/sarh/storage/app \
    /var/www/sarh/.env
```

---

## 8. أوامر الصيانة

| الأمر | الغرض |
|-------|-------|
| `php artisan sarh:install` | التثبيت الأولي |
| `php artisan migrate` | تشغيل الترحيلات المعلقة |
| `php artisan cache:clear` | مسح كاش التقارير المالية |
| `php artisan config:cache` | إعادة بناء كاش الإعدادات |
| `php artisan route:cache` | إعادة بناء كاش المسارات |
| `php artisan view:clear` | مسح العروض المُجمّعة |
| `php artisan queue:restart` | إعادة تشغيل معالجات الطوابير |
| `php artisan down` | وضع الصيانة |
| `php artisan up` | الخروج من وضع الصيانة |

---

## 9. استكشاف الأخطاء وإصلاحها

| المشكلة | الحل |
|---------|------|
| خطأ 500 بعد النشر | افحص `storage/logs/laravel.log`، تأكد من صلاحيات `storage/` |
| صفحة بيضاء | فعّل `APP_DEBUG=true` مؤقتاً، سجّل الخطأ، ثم أعده إلى `false` |
| CSS/JS لا يُحمّل | شغّل `npm run build`، تحقق من وجود `public/build/manifest.json` |
| تسجيل دخول Filament لا يعمل | تحقق من وجود مستخدم بأمر `php artisan tinker` → `User::first()` |
| "Vite manifest not found" | شغّل `npm run build` لبناء أصول الإنتاج |
| رفض اتصال قاعدة البيانات | تحقق من بيانات الاتصال في `.env`، تأكد أن MySQL يعمل |
| خطأ رابط التخزين | احذف الرابط الموجود `public/storage`، أعد تشغيل `php artisan storage:link` |

---

**مؤشر صرح** — SarhIndex
*نظام الموارد البشرية والذكاء المالي المؤسسي*
