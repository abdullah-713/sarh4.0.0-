<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SarhIndex v4.0 — حماية لوحة الإدارة /admin
 *
 * يسمح فقط لـ security_level >= 4 أو is_super_admin.
 * بدون هذا الـ Middleware، أي مستخدم مُسجّل الدخول يمكنه دخول /admin.
 */
class EnsureAdminPanelAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->is_super_admin || $user->security_level >= 4) {
            return $next($request);
        }

        abort(403, 'غير مصرح لك بالدخول إلى لوحة الإدارة.');
    }
}
