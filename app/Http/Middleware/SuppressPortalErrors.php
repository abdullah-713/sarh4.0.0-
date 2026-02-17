<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Suppress all exceptions in the employee portal.
 * Logs errors silently and redirects back to /app without showing any error details.
 */
class SuppressPortalErrors
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (\Throwable $e) {
            Log::error('[EmployeePortal] Suppressed error', [
                'url'     => $request->fullUrl(),
                'error'   => $e->getMessage(),
                'file'    => $e->getFile() . ':' . $e->getLine(),
                'user_id' => auth()->id(),
            ]);

            // If it's an AJAX/Livewire request, return empty success
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Livewire')) {
                return response()->json(['message' => 'OK'], 200);
            }

            // Redirect back to employee portal home
            return redirect('/app')->with('notification', [
                'status' => 'warning',
                'message' => __('employee.portal_title'),
            ]);
        }
    }
}
