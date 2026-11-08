@extends('layout.auth')

@section('title', 'Register')

@section('auth-styles')
.auth-simple {
    min-height: 100vh;
    min-height: 100dvh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    background:
        radial-gradient(ellipse 70% 50% at 10% 0%, rgba(26,74,138,0.12), transparent 55%),
        linear-gradient(160deg, #0a1c33 0%, #0f2747 45%, #1a4a8a 100%);
}
.auth-simple-card {
    width: 100%;
    max-width: 420px;
    background: #fff;
    border-radius: 0.75rem;
    padding: 2rem 1.75rem;
    box-shadow: 0 20px 50px rgba(0,0,0,0.28);
}
.auth-simple-card h1 {
    font-family: var(--tg-display);
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--tg-navy);
    margin: 0 0 0.35rem;
}
.auth-simple-card .lead {
    color: var(--tg-muted);
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
}
.auth-simple-card .form-label {
    font-size: 0.78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--tg-navy);
}
.auth-simple-card .form-control {
    height: 2.75rem;
    border: 1.5px solid var(--tg-line);
    border-radius: 0.5rem;
}
.auth-simple-card .btn-navy {
    height: 2.85rem;
    background: linear-gradient(135deg, var(--tg-navy), var(--tg-navy-mid));
    border: none;
    color: #fff;
    font-weight: 600;
    border-radius: 0.5rem;
}
.auth-simple-card .btn-navy:hover { color: #fff; filter: brightness(1.06); }
@endsection

@section('content')
<div class="auth-simple">
    <div class="auth-simple-card">
        <h1>Create account</h1>
        <p class="lead">Register for {{ config('app.name', 'TradeGate') }}</p>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Enter your name">
                @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Enter your email">
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="Enter a strong password">
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password-confirm" class="form-label">Confirm Password</label>
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm your password">
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-navy">Register</button>
            </div>

            <div class="text-center mt-3">
                <small class="text-muted">Already have an account? <a href="{{ route('login') }}">Sign in</a></small>
            </div>
        </form>
    </div>
</div>
@endsection
