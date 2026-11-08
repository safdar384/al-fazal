@extends('layout.app')

@section('title', 'Home')

@section('content')
<style>
  .ws-page {
    --ws-navy: #0f2747;
    --ws-mid: #1a4a8a;
    --ws-ice: #eef3f8;
    --ws-line: #d5dde8;
  }
  .ws-page .app-content { background: linear-gradient(180deg, #f7f9fc 0%, #eef3f8 100%); min-height: 70vh; }

  .ws-hero {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    background:
      radial-gradient(ellipse 70% 80% at 90% 20%, rgba(201,162,39,0.18), transparent 50%),
      linear-gradient(135deg, #0a1c33 0%, #0f2747 45%, #1a4a8a 100%);
    color: #fff;
    padding: clamp(1.5rem, 3vw, 2.25rem);
    margin-bottom: 1.25rem;
    box-shadow: 0 8px 28px rgba(15, 39, 71, 0.2);
  }
  .ws-hero::before {
    content: "";
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
    background-size: 40px 40px;
    opacity: 0.5;
    pointer-events: none;
  }
  .ws-hero-inner { position: relative; z-index: 1; }
  .ws-kicker {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.65);
    margin-bottom: 0.4rem;
  }
  .ws-title {
    font-size: clamp(1.45rem, 3vw, 1.9rem);
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0 0 0.35rem;
  }
  .ws-lead {
    margin: 0;
    max-width: 42rem;
    color: rgba(255,255,255,0.78);
    font-size: 0.95rem;
    line-height: 1.5;
  }
  .ws-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 1rem;
  }
  .ws-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.7rem;
    border-radius: 999px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.18);
    font-size: 0.78rem;
    font-weight: 600;
  }

  .ws-section-title {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--ws-navy);
    margin: 0 0 0.85rem;
  }

  .ws-link {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    height: 100%;
    padding: 1rem 1.05rem;
    background: #fff;
    border: 1px solid var(--ws-line);
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 2px 10px rgba(15, 39, 71, 0.05);
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
  }
  .ws-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(15, 39, 71, 0.12);
    border-color: #b8c7db;
    color: inherit;
  }
  .ws-link-icon {
    width: 2.6rem;
    height: 2.6rem;
    border-radius: 0.55rem;
    background: linear-gradient(145deg, #e8eef8, #d5e0f0);
    color: var(--ws-navy);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
  }
  .ws-link h6 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--ws-navy);
  }
  .ws-link p {
    margin: 0.2rem 0 0;
    font-size: 0.8rem;
    color: #6b7c90;
    line-height: 1.35;
  }
  .ws-empty {
    background: #fff;
    border: 1px dashed var(--ws-line);
    border-radius: 8px;
    padding: 2rem 1.25rem;
    text-align: center;
    color: #6b7c90;
  }
  .ws-note {
    font-size: 0.82rem;
    color: #5a6b7d;
    background: #fff;
    border: 1px solid var(--ws-line);
    border-left: 3px solid var(--ws-navy);
    border-radius: 4px;
    padding: 0.75rem 1rem;
  }
</style>

@php
    $roleLabel = match(true) {
        $user->isStaff() => 'Staff',
        $user->isClient() => 'Client',
        $user->isManager() => 'Manager',
        default => ucfirst((string) $user->role),
    };
@endphp

<main class="app-main ws-page" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center gy-2">
        <div class="col-12 col-md-7">
          <h3 class="mb-0 fw-bold" style="color:#0f2747;">
            <i class="bi bi-house-door me-2"></i>Home
          </h3>
          <p class="text-muted small mb-0">Your workspace — no business totals shown here</p>
        </div>
        <div class="col-12 col-md-5">
          <ol class="breadcrumb float-md-end mb-0">
            <li class="breadcrumb-item active">Home</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid pb-4">
      <div class="ws-hero">
        <div class="ws-hero-inner">
          <div class="ws-kicker">{{ config('app.name', 'TradeGate') }}</div>
          <h1 class="ws-title">Welcome, {{ $user->name }}</h1>
          <p class="ws-lead">
            Use the shortcuts below to open the sections available on your account.
            Business figures and financial dashboards are reserved for administrators.
          </p>
          <div class="ws-meta">
            <span class="ws-chip"><i class="bi bi-person-badge"></i> {{ $roleLabel }}</span>
            <span class="ws-chip"><i class="bi bi-clock-history"></i>
              Last login: {{ $user->last_login_at?->diffForHumans() ?? 'Just now' }}
            </span>
            <span class="ws-chip"><i class="bi bi-calendar3"></i> {{ now()->format('D, d M Y') }}</span>
          </div>
        </div>
      </div>

      <div class="ws-note mb-3">
        <i class="bi bi-shield-check me-1"></i>
        Your menu and shortcuts follow the access set by the admin. Contact admin if you need another module.
      </div>

      <h2 class="ws-section-title">Quick access</h2>

      @if(count($shortcuts) > 0)
        <div class="row g-3">
          @foreach($shortcuts as $item)
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
              <a href="{{ $item['url'] }}" class="ws-link">
                <span class="ws-link-icon" aria-hidden="true"><i class="bi {{ $item['icon'] }}"></i></span>
                <span>
                  <h6>{{ $item['label'] }}</h6>
                  <p>{{ $item['description'] }}</p>
                </span>
              </a>
            </div>
          @endforeach
        </div>
      @else
        <div class="ws-empty">
          <i class="bi bi-lock fs-3 d-block mb-2"></i>
          No modules are enabled for your account yet.
          Please ask an administrator to grant access under <strong>Setting → User Access</strong>.
        </div>
      @endif
    </div>
  </div>
</main>
@endsection
