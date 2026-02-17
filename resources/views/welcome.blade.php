<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'مؤشر صرح') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2AABEE">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Cairo', sans-serif;
            background: #E7EBF0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            padding: 2rem;
            max-width: 460px;
            width: 100%;
        }
        .logo-wrap {
            margin-bottom: 2rem;
        }
        .logo {
            max-width: 120px;
            height: auto;
            border-radius: 50%;
            box-shadow: 0 4px 20px rgba(42, 171, 238, 0.2);
        }
        .title {
            font-size: 2rem;
            font-weight: 800;
            color: #000000;
            margin-bottom: 0.5rem;
        }
        .subtitle {
            font-size: 1rem;
            color: #707579;
            margin-bottom: 2rem;
        }
        .nav-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 700;
            border-radius: 0.75rem;
            text-decoration: none;
            transition: all 0.15s ease;
            border: none;
            cursor: pointer;
        }
        .btn-admin {
            background: #2AABEE;
            color: #FFFFFF;
        }
        .btn-admin:hover {
            background: #229ED9;
        }
        .btn-app {
            background: #FFFFFF;
            color: #2AABEE;
            border: 1.5px solid #2AABEE;
        }
        .btn-app:hover {
            background: #EBF7FE;
        }
        .features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }
        .feature {
            padding: 1.25rem 0.75rem;
            background: #FFFFFF;
            border: 1px solid #E6E9ED;
            border-radius: 0.75rem;
            text-align: center;
        }
        .feature-icon {
            font-size: 1.75rem;
            margin-bottom: 0.25rem;
        }
        .feature-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #000000;
            margin-bottom: 0.2rem;
        }
        .feature-desc {
            font-size: 0.75rem;
            color: #707579;
        }
        .copyright {
            text-align: center;
            padding: 1rem 1.5rem;
            background: #FFFFFF;
            border-radius: 0.75rem;
            border: 1px solid #E6E9ED;
            font-size: 0.8rem;
            color: #707579;
            max-width: 90%;
            margin: 0 auto;
        }
        .copyright strong {
            color: #2AABEE;
            font-weight: 700;
        }
        @media (max-width: 480px) {
            .title { font-size: 1.5rem; }
            .features { grid-template-columns: 1fr 1fr; gap: 0.5rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-wrap">
            <img src="{{ asset('logo.png') }}" alt="Logo" class="logo">
        </div>

        <h1 class="title">{{ config('app.name', 'مؤشر صرح') }}</h1>
        <p class="subtitle">نظام الحضور والموارد البشرية الذكي</p>

        <div class="nav-buttons">
            <a href="/admin" class="btn btn-admin">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                لوحة الإدارة
            </a>
            <a href="/app" class="btn btn-app">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                بوابة الموظفين
            </a>
        </div>

        <div class="features">
            <div class="feature">
                <div class="feature-icon">📍</div>
                <div class="feature-title">تتبع GPS</div>
                <div class="feature-desc">حضور دقيق بالموقع</div>
            </div>
            <div class="feature">
                <div class="feature-icon">💰</div>
                <div class="feature-title">الذكاء المالي</div>
                <div class="feature-desc">حساب الرواتب تلقائياً</div>
            </div>
            <div class="feature">
                <div class="feature-icon">🏆</div>
                <div class="feature-title">التلعيب</div>
                <div class="feature-desc">شارات وإنجازات</div>
            </div>
            <div class="feature">
                <div class="feature-icon">📊</div>
                <div class="feature-title">تقارير ذكية</div>
                <div class="feature-desc">تحليلات شاملة</div>
            </div>
        </div>
    </div>

    <div class="copyright">
        حقوق الملكية الفكرية محفوظة لصالح <strong>عبدالحكيم المذهول</strong><br>
        <strong>Copyright &copy; {{ date('Y') }} Mr. Abdulhakim Al-Madhoul</strong>
    </div>
</body>
</html>
