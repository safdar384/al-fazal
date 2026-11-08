<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceModuleAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        if ($user->canAccessRequest($request)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'You do not have access to this section.'], 403);
        }

        abort(403, 'You do not have access to this section. Ask an administrator to grant permission.');
    }
}
