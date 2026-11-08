<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Never issue a long-lived "remember me" cookie — session must die with the browser.
     */
    protected function attemptLogin(Request $request): bool
    {
        return $this->guard()->attempt(
            $this->credentials($request),
            false
        );
    }

    /**
     * Credentials only — ignore any remember field if posted.
     *
     * @return array{email?: string, password?: string}
     */
    protected function credentials(Request $request): array
    {
        return $request->only($this->username(), 'password');
    }

    protected function redirectTo(): string
    {
        return '/dashboard';
    }

    /**
     * @param  \App\Models\User  $user
     */
    protected function authenticated(Request $request, $user): void
    {
        try {
            // Clear any old remember token so reopen-after-close cannot restore login
            if (! empty($user->getRememberToken())) {
                $user->setRememberToken(null);
                $user->save();
            }

            $user->forceFill(['last_login_at' => now()])->save();

            // Best-effort activity log — must never block or fail login
            DB::table('login_activities')->insert([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                'logged_in_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Session ID regeneration after successful login (session fixation protection).
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        // Drop any leftover remember cookie from older sessions
        Cookie::queue(Cookie::forget(Auth::getRecallerName()));

        $this->authenticated($request, $this->guard()->user());

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect()->intended($this->redirectPath());
    }

    /**
     * Full logout: clear remember token, invalidate session, regenerate CSRF.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user && method_exists($user, 'setRememberToken')) {
            try {
                $user->setRememberToken(null);
                $user->save();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Cookie::queue(Cookie::forget(Auth::getRecallerName()));

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect()->route('login')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
    }
}
