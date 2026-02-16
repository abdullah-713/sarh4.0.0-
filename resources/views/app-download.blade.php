<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تحميل تطبيق صرح الإتقان</title>
    <meta name="description" content="تحميل تطبيق صرح الإتقان لأجهزة أندرويد — نظام إدارة الموارد البشرية">
    <meta name="theme-color" content="#0F1B2D">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            background: linear-gradient(135deg, #0F1B2D 0%, #1E3A5F 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #E2E8F0;
            padding: 24px;
        }
        .card {
            background: #FFFFFF;
            border-radius: 24px;
            padding: 48px 36px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            color: #1E293B;
        }
        .icon {
            width: 96px;
            height: 96px;
            background: linear-gradient(135deg, #F97316, #EA580C);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 48px;
            color: white;
            font-weight: bold;
        }
        h1 {
            font-size: 26px;
            color: #0F172A;
            margin-bottom: 8px;
        }
        .subtitle {
            color: #64748B;
            font-size: 15px;
            margin-bottom: 32px;
        }
        .version {
            display: inline-block;
            background: #F1F5F9;
            color: #475569;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 24px;
        }
        .features {
            text-align: right;
            margin-bottom: 32px;
        }
        .features li {
            list-style: none;
            padding: 8px 0;
            font-size: 14px;
            color: #475569;
            border-bottom: 1px solid #F1F5F9;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .features li:last-child { border-bottom: none; }
        .features .emoji { font-size: 18px; min-width: 24px; text-align: center; }
        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #F97316, #EA580C);
            color: white;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 14px;
            font-size: 17px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 14px rgba(249, 115, 22, 0.4);
        }
        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(249, 115, 22, 0.5);
        }
        .btn-download:active {
            transform: translateY(0);
        }
        .note {
            margin-top: 24px;
            font-size: 12px;
            color: #94A3B8;
            line-height: 1.6;
        }
        .instructions {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #E2E8F0;
            text-align: right;
        }
        .instructions h3 {
            font-size: 15px;
            color: #1E293B;
            margin-bottom: 12px;
        }
        .instructions ol {
            padding-right: 18px;
            font-size: 13px;
            color: #64748B;
            line-height: 2;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">ص</div>
        <h1>صرح الإتقان</h1>
        <p class="subtitle">نظام إدارة الموارد البشرية</p>
        <span class="version">الإصدار 1.0.0</span>

        <ul class="features">
            <li><span class="emoji">📱</span> تسجيل الحضور بالموقع الجغرافي</li>
            <li><span class="emoji">📊</span> لوحة بيانات تفاعلية</li>
            <li><span class="emoji">📝</span> طلبات الإجازات</li>
            <li><span class="emoji">💬</span> المراسلات والتعاميم</li>
            <li><span class="emoji">🔔</span> إشعارات فورية</li>
            <li><span class="emoji">📂</span> رفع وتحميل الملفات</li>
        </ul>

        <a href="/app/sarh.apk" class="btn-download">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            تحميل التطبيق (APK)
        </a>

        <div class="instructions">
            <h3>📋 خطوات التثبيت:</h3>
            <ol>
                <li>اضغط على "تحميل التطبيق" أعلاه</li>
                <li>عند ظهور تحذير الأمان، اضغط <strong>"السماح"</strong></li>
                <li>افتح الملف المُحمّل واضغط <strong>"تثبيت"</strong></li>
                <li>إذا طُلب منك السماح بالتثبيت من مصادر غير معروفة، فعّل الخيار</li>
                <li>سجّل الدخول ببيانات حسابك في صرح</li>
            </ol>
        </div>

        <p class="note">
            ⚠️ هذا التطبيق للموظفين المسجلين فقط.<br>
            يتطلب أندرويد 7.0 أو أحدث.
        </p>
    </div>
</body>
</html>
