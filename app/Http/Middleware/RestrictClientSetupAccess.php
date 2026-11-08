<?php

namespace App\Http\Middleware;

use App\Models\ClientUrlRestriction;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictClientSetupAccess
{
    /**
     * URL patterns only administrators may access (clients get 403).
     * Managed in database; see Management → Client access. Falls back to config if table empty.
     */
    protected function adminOnlyPatterns(): array
    {
        return ClientUrlRestriction::activePatterns();
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->isClient()) {
            return $next($request);
        }

        foreach ($this->adminOnlyPatterns() as $pattern) {
            if ($request->is($pattern)) {
                abort(403, 'This section is only available to administrators.');
            }
        }

        return $next($request);
    }
}
