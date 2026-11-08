<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stop browsers from caching authenticated pages so Back after logout
 * cannot show a protected screen from history (forces a fresh auth check).
 */
class PreventBrowserCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Authenticated ERP pages + auth flow responses must not be stored by the browser.
        if (Auth::check() || $request->routeIs('login', 'logout', 'password.*')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            $response->headers->set('Surrogate-Control', 'no-store');
        }

        return $response;
    }
}
