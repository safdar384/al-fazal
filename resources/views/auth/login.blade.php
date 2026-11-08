@extends('layout.auth')

@section('title', 'Sign in')

@section('auth-styles')
.login-shell {
    display: grid;
    grid-template-columns: 1.05fr 0.95fr;
    min-height: 100vh;
    min-height: 100dvh;
}

/* ——— Brand panel ——— */
.login-brand {
    position: relative;
    overflow: hidden;
    background:
        radial-gradient(ellipse 80% 60% at 20% 90%, rgba(201, 162, 39, 0.18), transparent 55%),
        radial-gradient(ellipse 70% 50% at 90% 10%, rgba(26, 74, 138, 0.55), transparent 50%),
        linear-gradient(155deg, var(--tg-navy-deep) 0%, var(--tg-navy) 42%, var(--tg-navy-mid) 100%);
    color: #fff;
    padding: clamp(2rem, 5vw, 4rem);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.login-brand::before {
    content: "";
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
    background-size: 48px 48px;
    mask-image: radial-gradient(ellipse 90% 80% at 50% 50%, #000 20%, transparent 75%);
    pointer-events: none;
    animation: gridDrift 28s linear infinite;
}

.login-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(2px);
    pointer-events: none;
    opacity: 0.55;
}
.login-orb--1 {
    width: 280px; height: 280px;
    top: -80px; right: -60px;
    background: radial-gradient(circle, rgba(201,162,39,0.35), transparent 70%);
    animation: orbFloat 9s ease-in-out infinite;
}
.login-orb--2 {
    width: 200px; height: 200px;
    bottom: 12%; left: -40px;
    background: radial-gradient(circle, rgba(120,180,255,0.25), transparent 70%);
    animation: orbFloat 11s ease-in-out infinite reverse;
}

.login-brand-top {
    position: relative;
    z-index: 1;
}

.login-monogram {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 3.25rem;
    height: 3.25rem;
    border-radius: 0.65rem;
    background: linear-gradient(145deg, var(--tg-gold-soft), var(--tg-gold));
    color: var(--tg-navy-deep);
    font-family: var(--tg-display);
    font-weight: 800;
    font-size: 1.15rem;
    letter-spacing: -0.02em;
    box-shadow: 0 8px 24px rgba(0,0,0,0.25);
}

.login-brand-name {
    margin: 2.5rem 0 0.75rem;
    font-family: var(--tg-display);
    font-weight: 800;
    font-size: clamp(2.6rem, 5.5vw, 3.75rem);
    line-height: 1.05;
    letter-spacing: -0.035em;
    max-width: 12ch;
}

.login-brand-name span {
    display: block;
    background: linear-gradient(90deg, #fff 0%, var(--tg-gold-soft) 100%);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.login-brand-tag {
    margin: 0;
    max-width: 28ch;
    font-size: 1.05rem;
    line-height: 1.55;
    color: rgba(255,255,255,0.72);
    font-weight: 400;
}

.login-brand-foot {
    position: relative;
    z-index: 1;
    display: flex;
    flex-wrap: wrap;
    gap: 1.25rem 2rem;
    padding-top: 2rem;
    border-top: 1px solid rgba(255,255,255,0.12);
    margin-top: 2rem;
}

.login-stat {
    min-width: 0;
}
.login-stat strong {
    display: block;
    font-family: var(--tg-display);
    font-weight: 700;
    font-size: 1.15rem;
    letter-spacing: -0.02em;
}
.login-stat span {
    font-size: 0.78rem;
    color: rgba(255,255,255,0.55);
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

/* ——— Form panel ——— */
.login-panel {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: clamp(1.5rem, 4vw, 3rem);
    background:
        linear-gradient(180deg, #ffffff 0%, var(--tg-ice) 100%);
    position: relative;
}

.login-panel::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 60% 40% at 100% 0%, rgba(26,74,138,0.06), transparent 50%);
    pointer-events: none;
}

.login-form-wrap {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 400px;
    animation: formRise 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.login-form-kicker {
    font-family: var(--tg-display);
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--tg-navy-mid);
    margin: 0 0 0.5rem;
}

.login-form-title {
    font-family: var(--tg-display);
    font-weight: 700;
    font-size: clamp(1.65rem, 3vw, 1.95rem);
    letter-spacing: -0.03em;
    color: var(--tg-navy);
    margin: 0 0 0.4rem;
}

.login-form-lead {
    margin: 0 0 1.75rem;
    color: var(--tg-muted);
    font-size: 0.95rem;
    line-height: 1.45;
}

.login-alert {
    border: none;
    border-radius: 0.5rem;
    font-size: 0.88rem;
    background: #fff6e5;
    color: #7a5200;
    border-left: 3px solid var(--tg-gold);
}

.login-label {
    display: block;
    font-family: var(--tg-display);
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--tg-navy);
    margin-bottom: 0.4rem;
}

.login-field {
    position: relative;
    margin-bottom: 1.15rem;
}

.login-field .form-control {
    height: 3rem;
    padding: 0.65rem 1rem 0.65rem 2.75rem;
    border: 1.5px solid var(--tg-line);
    border-radius: 0.55rem;
    background: #fff;
    font-size: 0.95rem;
    color: var(--tg-navy);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.login-field .form-control:focus {
    border-color: var(--tg-navy-mid);
    box-shadow: 0 0 0 3px rgba(26, 74, 138, 0.15);
    outline: none;
}

.login-field .form-control.is-invalid {
    border-color: #c0392b;
}

.login-field-icon {
    position: absolute;
    left: 0.95rem;
    top: 2.35rem;
    color: var(--tg-muted);
    font-size: 1.05rem;
    pointer-events: none;
    transition: color 0.2s ease;
}

.login-field:focus-within .login-field-icon {
    color: var(--tg-navy-mid);
}

.login-toggle-pass {
    position: absolute;
    right: 0.65rem;
    top: 2.2rem;
    border: none;
    background: transparent;
    color: var(--tg-muted);
    padding: 0.35rem 0.45rem;
    border-radius: 0.35rem;
    cursor: pointer;
    line-height: 1;
}
.login-toggle-pass:hover { color: var(--tg-navy); }

.login-field .form-control.has-toggle {
    padding-right: 2.75rem;
}

.login-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.login-meta .form-check-input {
    width: 1.05rem;
    height: 1.05rem;
    border-color: var(--tg-line);
    margin-top: 0.15rem;
}
.login-meta .form-check-input:checked {
    background-color: var(--tg-navy);
    border-color: var(--tg-navy);
}
.login-meta .form-check-label {
    font-size: 0.88rem;
    color: var(--tg-muted);
    user-select: none;
}

.login-forgot {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--tg-navy-mid);
    text-decoration: none;
}
.login-forgot:hover {
    color: var(--tg-navy);
    text-decoration: underline;
}

.login-submit {
    width: 100%;
    height: 3.05rem;
    border: none;
    border-radius: 0.55rem;
    background: linear-gradient(135deg, var(--tg-navy) 0%, var(--tg-navy-mid) 100%);
    color: #fff;
    font-family: var(--tg-display);
    font-weight: 600;
    font-size: 0.98rem;
    letter-spacing: 0.02em;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    box-shadow: 0 10px 28px rgba(15, 39, 71, 0.28);
    transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
}
.login-submit:hover {
    color: #fff;
    filter: brightness(1.06);
    transform: translateY(-1px);
    box-shadow: 0 14px 32px rgba(15, 39, 71, 0.34);
}
.login-submit:active {
    transform: translateY(0);
}

.login-foot {
    margin-top: 1.75rem;
    text-align: center;
    font-size: 0.82rem;
    color: var(--tg-muted);
}
.login-foot a {
    color: var(--tg-navy-mid);
    font-weight: 600;
    text-decoration: none;
}
.login-foot a:hover { text-decoration: underline; }

.login-mobile-brand {
    display: none;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}
.login-mobile-brand .login-monogram {
    width: 2.5rem;
    height: 2.5rem;
    font-size: 0.95rem;
}
.login-mobile-brand strong {
    font-family: var(--tg-display);
    font-weight: 700;
    font-size: 1.15rem;
    color: var(--tg-navy);
}

@@keyframes formRise {
    from { opacity: 0; transform: translateY(18px); }
    to { opacity: 1; transform: translateY(0); }
}
@@keyframes orbFloat {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(-18px, 14px); }
}
@@keyframes gridDrift {
    from { background-position: 0 0; }
    to { background-position: 48px 48px; }
}

@@media (max-width: 900px) {
    .login-shell {
        grid-template-columns: 1fr;
    }
    .login-brand {
        min-height: auto;
        padding: 1.5rem 1.35rem 1.75rem;
    }
    .login-brand-name {
        margin-top: 1.25rem;
        font-size: 2rem;
        max-width: none;
    }
    .login-brand-name span { display: inline; }
    .login-brand-tag { font-size: 0.92rem; max-width: none; }
    .login-brand-foot { display: none; }
    .login-orb { display: none; }
    .login-mobile-brand { display: none; }
}

@@media (max-width: 520px) {
    .login-brand { display: none; }
    .login-mobile-brand { display: flex; }
    .login-panel { padding: 1.5rem 1.15rem; }
}
@endsection

@section('content')
<div class="login-shell">
    <aside class="login-brand" aria-hidden="false">
        <div class="login-orb login-orb--1"></div>
        <div class="login-orb login-orb--2"></div>

        <div class="login-brand-top">
            <div class="login-monogram" aria-hidden="true">TG</div>
            <h1 class="login-brand-name">
                <span>{{ config('app.name', 'TradeGate') }}</span>
            </h1>
            <p class="login-brand-tag">
                Your commerce command center — sales, purchases, and accounts in one secure place.
            </p>
</div>

        <div class="login-brand-foot">
            <div class="login-stat">
                <strong>Secure</strong>
                <span>Role-based access</span>
            </div>
            <div class="login-stat">
                <strong>Realtime</strong>
                <span>Live business data</span>
            </div>
            <div class="login-stat">
                <strong>Trusted</strong>
                <span>Daily operations</span>
            </div>
        </div>
    </aside>

    <main class="login-panel">
        <div class="login-form-wrap">
            <div class="login-mobile-brand">
                <div class="login-monogram" aria-hidden="true">TG</div>
                <strong>{{ config('app.name', 'TradeGate') }}</strong>
            </div>

            <p class="login-form-kicker">Welcome back</p>
            <h2 class="login-form-title">Sign in to continue</h2>
            <p class="login-form-lead">Enter your credentials to access the workspace.</p>

            @if(session('warning'))
                <div class="alert login-alert alert-dismissible fade show" role="alert">
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

            <form method="POST" action="{{ route('login') }}" novalidate autocomplete="off" data-lpignore="true" data-1p-ignore="true">
        @csrf

                <div class="login-field">
                    <label for="email" class="login-label">Email</label>
                    <i class="bi bi-envelope login-field-icon" aria-hidden="true"></i>
                    <input
                        id="email"
                        type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="off"
                        autofocus
                        placeholder="you@company.com"
                        data-lpignore="true"
                        data-1p-ignore="true"
                    >
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

                <div class="login-field">
                    <label for="password" class="login-label">Password</label>
                    <i class="bi bi-lock login-field-icon" aria-hidden="true"></i>
                    <input
                        id="password"
                        type="password"
                        class="form-control has-toggle @error('password') is-invalid @enderror"
                        name="password"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                        data-lpignore="true"
                        data-1p-ignore="true"
                    >
                    <button type="button" class="login-toggle-pass" id="togglePassword" aria-label="Show password">
                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                    </button>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        @if (Route::has('password.request'))
                    <div class="login-meta justify-content-end">
                        <a href="{{ route('password.request') }}" class="login-forgot">Forgot password?</a>
            </div>
        @endif

                <button type="submit" class="login-submit">
                    Sign in
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </button>
            </form>

        @if (Route::has('register'))
                <p class="login-foot">
                    Don’t have an account? <a href="{{ route('register') }}">Create one</a>
                </p>
            @else
                <p class="login-foot">Authorized personnel only · {{ config('app.name', 'TradeGate') }} ERP</p>
            @endif
            </div>
    </main>
</div>
@endsection

@section('auth-scripts')
<script>
(function () {
    var btn = document.getElementById('togglePassword');
    var input = document.getElementById('password');
    var icon = document.getElementById('togglePasswordIcon');
    if (!btn || !input || !icon) return;
    btn.addEventListener('click', function () {
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
})();
</script>
@endsection
