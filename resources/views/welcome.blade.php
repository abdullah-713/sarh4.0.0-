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
    
    <!-- Favicons بأحجام متعددة -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('logo.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('logo.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('logo.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('logo.png') }}">
    
    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0F172A">
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
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 50%, #0F172A 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }
        
        /* Background Animation */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(212, 168, 65, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(212, 168, 65, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(212, 168, 65, 0.05) 0%, transparent 50%);
            animation: bgMove 20s ease-in-out infinite alternate;
            z-index: 0;
        }
        
        @keyframes bgMove {
            0% { opacity: 0.3; }
            50% { opacity: 0.6; }
            100% { opacity: 0.3; }
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
            margin-bottom: 3rem;
        }
        
        .logo {
            max-width: 250px;
            height: auto;
            animation: float 4s ease-in-out infinite;
            filter: drop-shadow(0 0 50px rgba(212, 168, 65, 0.6)) 
                    drop-shadow(0 0 100px rgba(212, 168, 65, 0.4));
            transition: transform 0.3s ease;
        }
        
        .logo:hover {
            transform: scale(1.05) !important;
        }
        
        @keyframes float {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg);
            }
            50% { 
                transform: translateY(-30px) rotate(2deg);
            }
        }
        
        .title {
            color: #D4A841;
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 1rem;
            background: linear-gradient(120deg, #D4A841 0%, #FFD700 30%, #FFF 50%, #FFD700 70%, #D4A841 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            background-size: 300% 100%;
            animation: shimmer 4s linear infinite;
            text-shadow: 0 0 30px rgba(212, 168, 65, 0.3);
        }
        
        @keyframes shimmer {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .subtitle {
            color: #CBD5E1;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            opacity: 0.9;
        }
        
        .subtitle-en {
            color: #94A3B8;
            font-size: 1.1rem;
            font-weight: 400;
            margin-bottom: 3rem;
            opacity: 0.7;
        }
        
        .nav-buttons {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 4rem;
        }
        
        .btn {
            padding: 1rem 2.5rem;
            font-size: 1.2rem;
            font-weight: 700;
            border-radius: 1rem;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid;
            display: inline-block;
            position: relative;
            overflow: hidden;
        }
        
        .btn-admin {
            background: linear-gradient(135deg, #D4A841 0%, #B8922E 100%);
            color: #0F172A;
            border-color: #D4A841;
            box-shadow: 0 10px 30px rgba(212, 168, 65, 0.3);
        }
        
        .btn-admin:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(212, 168, 65, 0.5);
        }
        
        .btn-app {
            background: rgba(212, 168, 65, 0.1);
            color: #D4A841;
            border-color: #D4A841;
            backdrop-filter: blur(10px);
        }
        
        .btn-app:hover {
            background: rgba(212, 168, 65, 0.2);
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(212, 168, 65, 0.3);
        }
        
        .copyright {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            color: #D4A841;
            font-size: 0.95rem;
            padding: 1.5rem 3rem;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 1.5rem;
            border: 2px solid rgba(212, 168, 65, 0.3);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            max-width: 90%;
            animation: pulse 3s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { 
                box-shadow: 0 10px 40px rgba(212, 168, 65, 0.2);
            }
            50% { 
                box-shadow: 0 10px 60px rgba(212, 168, 65, 0.4);
            }
        }
        
        .copyright strong {
            color: #FFD700;
            font-weight: 900;
            font-size: 1.1em;
        }
        
        .copyright small {
            display: block;
            margin-top: 0.5rem;
            color: #CBD5E1;
            font-size: 0.85rem;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin: 3rem 0;
        }
        
        .feature {
            padding: 1.5rem;
            background: rgba(212, 168, 65, 0.05);
            border: 1px solid rgba(212, 168, 65, 0.2);
            border-radius: 1rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .feature:hover {
            background: rgba(212, 168, 65, 0.1);
            border-color: rgba(212, 168, 65, 0.4);
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .feature-title {
            color: #D4A841;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .feature-desc {
            color: #94A3B8;
            font-size: 0.9rem;
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
                max-width: 180px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-wrapper">
            <img src="{{ asset('logo.png') }}" alt="SARH Logo" class="logo">
        </div>
        
        <h1 class="title">{{ config('app.name', 'صرح الإتقان') }}</h1>
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
        <small>⚠️ يمنع استخدام أو تعديل أو نسخ أي جزء من الكود • Unauthorized use, modification, or copying of any part of this code is strictly prohibited.</small>
    </div>
    
    <script>
        // Easter egg: Console message
        console.log('%c🔒 SARH System', 'color: #D4A841; font-size: 20px; font-weight: bold;');
        console.log('%cCopyright © 2026 Mr. Abdulhakim Al-Madhoul', 'color: #CBD5E1; font-size: 14px;');
        console.log('%c⚠️ Unauthorized access is prohibited', 'color: #FF6B6B; font-weight: bold;');
    </script>
</body>
</html>
