<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // SarhIndex Hardened: Trust all proxies (Hostinger shared hosting reverse proxy).
        // Without this, $request->isSecure() returns false behind HTTPS proxy,
        // causing CSRF token / session cookie mismatches → 419 errors.
        $middleware->trustProxies(at: '*');

        // v1.9.0: إصلاح خطأ Permissions-Policy الذي ظهر في v1.8.x
        // بدون هذا، المتصفح يرفض طلب navigator.geolocation.getCurrentPosition()
        // مما يُعطّل تسجيل الحضور الجغرافي في بوابة الموظفين /app
        $middleware->append(\App\Http\Middleware\SetPermissionsPolicy::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // تسجيل الأخطاء في audit_logs (الإنتاج فقط)
        $exceptions->reportable(function (\Throwable $e) {
            if (! app()->environment('local')) {
                try {
                    \App\Models\AuditLog::record(
                        'system.error',
                        null,
                        null,
                        [
                            'message' => $e->getMessage(),
                            'file'    => $e->getFile(),
                            'line'    => $e->getLine(),
                        ],
                        'خطأ في النظام'
                    );
                } catch (\Exception $logError) {
                    // نتجاهل أخطاء التسجيل لتجنب الدورات اللانهائية
                }
            }
        });

        // معالجة موحدة للأخطاء في JSON responses
        $exceptions->renderable(function (\Throwable $e, $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            // BusinessException → رسالة واضحة للمستخدم
            if ($e instanceof \App\Exceptions\BusinessException) {
                return response()->json([
                    'message' => $e->getUserMessage(),
                ], $e->getHttpCode());
            }

            // Model Not Found → 404
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'message' => 'المورد المطلوب غير موجود',
                ], 404);
            }

            // Division by Zero → 422
            if ($e instanceof \DivisionByZeroError) {
                return response()->json([
                    'message' => 'خطأ في الحسابات المالية. الرجاء التواصل مع الدعم الفني',
                ], 422);
            }

            // في الإنتاج: نخفي تفاصيل الأخطاء الفنية
            if (! app()->environment('local')) {
                return response()->json([
                    'message' => 'حدث خطأ داخلي في الخادم',
                ], 500);
            }

            return null;
        });
    })->create();
