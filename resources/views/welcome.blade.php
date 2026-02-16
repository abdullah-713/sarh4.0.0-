<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'صرح الإتقان') }} - SARH System</title>
    
    <!-- Google Fonts: Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    
    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('logo.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('logo.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('logo.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('logo.png') }}">
    
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1E3A5F">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="صرح">
    
    <!-- OG Meta -->
    <meta property="og:title" content="{{ config('app.name') }}">
    <meta property="og:image" content="{{ asset('logo.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:description" content="نظام الحضور والموارد البشرية الذكي">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #FFFFFF 0%, #F0F4F8 50%, #FFFFFF 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }
        
        /* Background Decoration */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 80%;
            height: 120%;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.06) 0%, transparent 60%);
            z-index: 0;
        }
        
        body::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: -20%;
            width: 70%;
            height: 100%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.05) 0%, transparent 60%);
            z-index: 0;
        }
        
        .container {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 2rem;
            max-width: 800px;
        }
        
        .logo-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 2.5rem;
        }
        
        .logo {
            max-width: 220px;
            height: auto;
            animation: float 4s ease-in-out infinite;
            filter: drop-shadow(0 8px 24px rgba(249, 115, 22, 0.2));
            transition: transform 0.3s ease;
        }
        
        .logo:hover {
            transform: scale(1.05) !important;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .title {
            color: #111827;
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
        }
        
        .title span {
            background: linear-gradient(135deg, #F97316, #EA580C);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .subtitle {
            color: #374151;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        
        .subtitle-en {
            color: #6B7280;
            font-size: 1.1rem;
            font-weight: 400;
            margin-bottom: 3rem;
        }
        
        .nav-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 3.5rem;
        }
        
        .btn {
            padding: 1rem 2.5rem;
            font-size: 1.15rem;
            font-weight: 700;
            border-radius: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid;
            display: inline-block;
        }
        
        .btn-admin {
            background: linear-gradient(135deg, #F97316, #EA580C);
            color: #FFFFFF;
            border-color: #F97316;
            box-shadow: 0 8px 24px rgba(249, 115, 22, 0.25);
        }
        
        .btn-admin:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 36px rgba(249, 115, 22, 0.35);
        }
        
        .btn-app {
            background: #FFFFFF;
            color: #2563EB;
            border-color: #2563EB;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
        }
        
        .btn-app:hover {
            background: #2563EB;
            color: #FFFFFF;
            transform: translateY(-4px);
            box-shadow: 0 14px 36px rgba(37, 99, 235, 0.25);
        }
        
        .copyright {
            position: fixed;
            bottom: 1.5rem;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            color: #4B5563;
            font-size: 0.9rem;
            padding: 1.25rem 2.5rem;
            background: #FFFFFF;
            border-radius: 1.25rem;
            border: 1px solid #E5E7EB;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            max-width: 90%;
        }
        
        .copyright strong {
            color: #F97316;
            font-weight: 800;
            font-size: 1em;
        }
        
        .copyright small {
            display: block;
            margin-top: 0.4rem;
            color: #9CA3AF;
            font-size: 0.8rem;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.25rem;
            margin: 2.5rem 0;
        }
        
        .feature {
            padding: 1.5rem 1rem;
            background: #FFFFFF;
            border: 1px solid #E5E7EB;
            border-radius: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        
        .feature:hover {
            border-color: rgba(249, 115, 22, 0.3);
            box-shadow: 0 8px 24px rgba(249, 115, 22, 0.08);
            transform: translateY(-4px);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .feature-title {
            color: #1E3A5F;
            font-weight: 700;
            margin-bottom: 0.4rem;
        }
        
        .feature-desc {
            color: #6B7280;
            font-size: 0.85rem;
        }
        
        @media (max-width: 768px) {
            .title {
                font-size: 2.5rem;
            }
            .subtitle {
                font-size: 1.2rem;
            }
            .btn {
                padding: 0.8rem 1.5rem;
                font-size: 1rem;
            }
            .copyright {
                font-size: 0.8rem;
                padding: 1rem 1.5rem;
            }
            .logo {
                max-width: 160px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-wrapper">
            <img src="{{ asset('logo.png') }}" alt="SARH Logo" class="logo">
        </div>
        
        <h1 class="title"><span>{{ config('app.name', 'صرح الإتقان') }}</span></h1>
        <p class="subtitle">نظام الحضور والموارد البشرية الذكي</p>
        <p class="subtitle-en">Smart HR & Attendance Management System</p>
        
        <div class="nav-buttons">
            <a href="/admin" class="btn btn-admin">
                🔐 لوحة الإدارة
            </a>
            <a href="/app" class="btn btn-app">
                👥 بوابة الموظفين
            </a>
        </div>
        
        <div class="features">
            <div class="feature">
                <div class="feature-icon">📍</div>
                <div class="feature-title">تتبع GPS</div>
                <div class="feature-desc">حضور دقيق بالموقع الجغرافي</div>
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
        🔒 حقوق الملكية الفكرية محفوظة لصالح السيد <strong>عبدالحكيم المذهول</strong><br>
        📜 <strong>Copyright © {{ date('Y') }} Mr. Abdulhakim Al-Madhoul</strong><br>
        <small>⚠️ يمنع استخدام أو تعديل أو نسخ أي جزء من الكود • Unauthorized use prohibited.</small>
    </div>
    
    <script>
        console.log('%c🔒 SARH System', 'color: #F97316; font-size: 20px; font-weight: bold;');
        console.log('%cCopyright © 2026 Mr. Abdulhakim Al-Madhoul', 'color: #4B5563; font-size: 14px;');
        console.log('%c⚠️ Unauthorized access is prohibited', 'color: #DC2626; font-weight: bold;');
    </script>
</body>
</html>
