<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * Auth/session security checks without running the full ERP migration set
 * (several historical migrations are intentionally duplicated in production history).
 */
beforeEach(function () {
    Schema::dropIfExists('login_activities');
    Schema::dropIfExists('sessions');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->string('password_plain')->nullable();
        $table->string('role')->default('client');
        $table->boolean('access_customized')->default(false);
        $table->rememberToken();
        $table->timestamp('last_login_at')->nullable();
        $table->timestamps();
    });

    Schema::create('sessions', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->foreignId('user_id')->nullable()->index();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    });

    Schema::create('login_activities', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->timestamp('logged_in_at')->nullable();
    });
});

afterEach(function () {
    Schema::dropIfExists('login_activities');
    Schema::dropIfExists('sessions');
    Schema::dropIfExists('users');
});

function makeAuthUser(array $overrides = []): User
{
    return User::query()->create(array_merge([
        'name' => 'Auth Tester',
        'email' => 'auth.tester@example.com',
        'password' => Hash::make('password'),
        'role' => User::ROLE_ADMIN,
        'email_verified_at' => now(),
        'remember_token' => null,
    ], $overrides));
}

test('login screen can be rendered', function () {
    $this->get('/login')->assertOk();
});

test('guests are redirected to login from protected pages', function () {
    $this->get('/dashboard')->assertRedirect(route('login'));
    $this->get('/')->assertRedirect(route('login'));
});

test('users can authenticate using the login form', function () {
    $user = makeAuthUser();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'remember' => '1', // must be ignored — no persistent login
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);

    $user->refresh();
    expect(blank($user->getRememberToken()))->toBeTrue();

    // Must not leave a long-lived remember cookie (forget/expiry cookies are OK)
    $cookieName = Auth::getRecallerName();
    $persistentRemember = collect($response->headers->getCookies())->contains(
        fn ($c) => $c->getName() === $cookieName
            && filled($c->getValue())
            && $c->getExpiresTime() > time() + 60
    );
    expect($persistentRemember)->toBeFalse();

    // Login success response (authenticated) must not be browser-cached
    expect(strtolower((string) $response->headers->get('Cache-Control')))
        ->toContain('no-store');
});

test('users cannot authenticate with invalid password', function () {
    $user = makeAuthUser(['email' => 'bad.pass@example.com']);

    $this->from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertRedirect('/login');

    $this->assertGuest();
});

test('users can logout and session is invalidated', function () {
    $user = makeAuthUser(['email' => 'logout@example.com']);

    $this->actingAs($user);

    $response = $this->post('/logout');

    $response->assertRedirect(route('login'));
    $this->assertGuest();

    $this->get('/dashboard')->assertRedirect(route('login'));
});

test('session expire_on_close is enabled', function () {
    expect(config('session.expire_on_close'))->toBeTrue();
});
