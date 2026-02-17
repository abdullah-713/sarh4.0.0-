<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Detect browser language and set app locale accordingly.
 * Supports: ar (Arabic), en (English). Default: ar.
 * Only active on the employee portal (/app).
 */
class DetectLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check session (user previously selected a language)
        if ($locale = session('locale')) {
            app()->setLocale($locale);
            return $next($request);
        }

        // 2. Check query param ?lang=en|ar
        if ($request->has('lang')) {
            $lang = in_array($request->query('lang'), ['ar', 'en']) ? $request->query('lang') : 'ar';
            session(['locale' => $lang]);
            app()->setLocale($lang);
            return $next($request);
        }

        // 3. Detect from Accept-Language header
        $preferred = $request->getPreferredLanguage(['ar', 'en']);
        $locale = $preferred ?: 'ar';

        app()->setLocale($locale);

        return $next($request);
    }
}
