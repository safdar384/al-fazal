<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(function () {
                if (file_exists(base_path('routes/auth.php'))) {
                    require base_path('routes/auth.php');
                }
                if (file_exists(base_path('routes/erp_modules.php'))) {
                    require base_path('routes/erp_modules.php');
                }
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            return route('login');
        });
        $middleware->redirectUsersTo(fn () => route('dashboard'));

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'permission' => \App\Http\Middleware\EnsurePermission::class,
            'role' => \App\Http\Middleware\EnsureRole::class,
            'module.access' => \App\Http\Middleware\EnforceModuleAccess::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\PreventBrowserCache::class,
            \App\Http\Middleware\EnforceSessionIdle::class,
            \App\Http\Middleware\EnforceModuleAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
