<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Optional idle timeout (minutes) via config permissions.idle_timeout_minutes.
 * 0 disables. Works on top of Laravel session lifetime.
 */
class EnforceSessionIdle
{
    public function handle(Request $request, Closure $next): Response
    {
        $minutes = (int) config('permissions.idle_timeout_minutes', 0);
        if ($minutes <= 0 || ! Auth::check()) {
            return $next($request);
        }

        $key = 'auth_last_activity';
        $now = time();
        $last = (int) session($key, $now);

        if ($now - $last > $minutes * 60) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $request->expectsJson()
                ? response()->json(['message' => 'Session expired due to inactivity.'], 401)
                : redirect()->route('login')->with('warning', 'Your session expired due to inactivity. Please sign in again.');
        }

        session([$key => $now]);

        return $next($request);
    }
}
