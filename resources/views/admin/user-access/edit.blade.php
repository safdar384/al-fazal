@extends('layout.app')

@section('title', 'Access — '.$user->name)

@section('content')
@include('partials.sale.erp-styles')

<style>
  .ua-card { border:1px solid #d5dde8; border-radius:6px; box-shadow:0 2px 12px rgba(15,39,71,.07); overflow:hidden; background:#fff; }
  .ua-head { background:linear-gradient(135deg,#0f2747,#1a4a8a); color:#fff; padding:.85rem 1.1rem; }
  .ua-group { font-size:.72rem; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:#0f2747; margin:1.1rem 0 .55rem; padding-bottom:.3rem; border-bottom:2px solid #0f2747; }
  .ua-mod { display:flex; align-items:center; gap:.85rem; padding:.8rem 1rem; border-bottom:1px solid #e8eef5; }
  .ua-mod:last-child { border-bottom:none; }
  .ua-mod:hover { background:#f7fafc; }
  .ua-icon { width:2.4rem; height:2.4rem; border-radius:.45rem; background:linear-gradient(145deg,#e8eef8,#d5e0f0); color:#0f2747; display:inline-flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
  .ua-mod h6 { margin:0; font-size:.92rem; font-weight:700; color:#0f2747; }
  .ua-mod p { margin:.12rem 0 0; font-size:.78rem; color:#6b7c90; }
  .form-check-input.ua-toggle { width:2.55rem; height:1.3rem; cursor:pointer; background-color:#c0392b; border-color:#c0392b; }
  .form-check-input.ua-toggle:checked { background-color:#198754; border-color:#198754; }
  .ua-status-on { color:#198754; font-size:.68rem; font-weight:700; text-transform:uppercase; }
  .ua-status-off { color:#c0392b; font-size:.68rem; font-weight:700; text-transform:uppercase; }
</style>

<main class="app-main erp-sale-page" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center gy-2">
        <div class="col-12 col-lg-8">
          <h3 class="mb-0 fw-bold" style="color:#0f2747;">
            <i class="bi bi-sliders me-2"></i>Access for {{ $user->name }}
          </h3>
          <p class="text-muted small mb-0">
            Role: <strong>{{ ucfirst($user->role) }}</strong>
            ·
            @if($user->access_customized)
              <span class="text-success">Custom access active</span>
            @else
              Showing role defaults — save to lock a custom set for this user
            @endif
          </p>
        </div>
        <div class="col-12 col-lg-4">
          <ol class="breadcrumb float-lg-end mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.user-access.index') }}">User Access</a></li>
            <li class="breadcrumb-item active">{{ $user->name }}</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <form method="POST" action="{{ route('admin.user-access.update', $user) }}" id="accessForm">
        @csrf
        @method('PUT')

        <div class="ua-card mb-3">
          <div class="ua-head d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-toggles2 me-2"></i>Modules</h6>
              <div class="small opacity-75 mt-1">Green = allow · Red = deny</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-sm btn-light fw-semibold" id="uaAllowAll">Allow all</button>
              <button type="button" class="btn btn-sm btn-outline-light fw-semibold" id="uaDenyAll">Deny all</button>
            </div>
          </div>

          <div class="px-3 pb-3">
            @foreach($modules as $group => $items)
              <div class="ua-group"><i class="bi bi-folder2-open me-1"></i>{{ $group }}</div>
              <div class="ua-card border-0 shadow-none" style="border:1px solid #e8eef5 !important;">
                @foreach($items as $m)
                  @php
                    $isHome = $m['key'] === 'dashboard';
                    $on = $isHome ? true : (bool) ($accessMap[$m['key']] ?? false);
                  @endphp
                  <label class="ua-mod mb-0" style="cursor:{{ $isHome ? 'default' : 'pointer' }};">
                    <div class="ua-icon"><i class="bi {{ $m['icon'] }}"></i></div>
                    <div class="flex-grow-1">
                      <h6>{{ $isHome ? 'Home' : $m['label'] }}</h6>
                      <p>{{ $isHome ? 'Always available — entry page for this user' : $m['description'] }}</p>
                    </div>
                    <div class="text-end">
                      <div class="ua-status-label {{ $on ? 'ua-status-on' : 'ua-status-off' }}">
                        {{ $on ? 'Allowed' : 'Denied' }}
                      </div>
                      <div class="form-check form-switch mb-0 d-inline-block">
                        <input
                          class="form-check-input ua-toggle"
                          type="checkbox"
                          role="switch"
                          name="modules[]"
                          value="{{ $m['key'] }}"
                          @checked($on)
                          @if($isHome) checked disabled @endif
                        >
                        @if($isHome)
                          <input type="hidden" name="modules[]" value="dashboard">
                        @endif
                      </div>
                    </div>
                  </label>
                @endforeach
              </div>
            @endforeach
          </div>
        </div>

        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
          <a href="{{ route('admin.user-access.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
          </a>
          <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-sm text-white px-3" style="background:#0f2747;">
              <i class="bi bi-check2 me-1"></i>Save access
            </button>
          </div>
        </div>
      </form>

      <form method="POST" action="{{ route('admin.user-access.reset', $user) }}"
            onsubmit="return confirm('Clear custom access and use {{ $user->role }} role defaults again?');">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-danger">
          <i class="bi bi-arrow-counterclockwise me-1"></i>Reset to role defaults
        </button>
      </form>
    </div>
  </div>
</main>

<script>
(function () {
  function refreshLabels() {
    document.querySelectorAll('.ua-mod').forEach(function (row) {
      var input = row.querySelector('.ua-toggle');
      var label = row.querySelector('.ua-status-label');
      if (!input || !label) return;
      if (input.checked) {
        label.textContent = 'Allowed';
        label.className = 'ua-status-label ua-status-on';
      } else {
        label.textContent = 'Denied';
        label.className = 'ua-status-label ua-status-off';
      }
    });
  }
  document.querySelectorAll('.ua-toggle').forEach(function (el) {
    el.addEventListener('change', refreshLabels);
  });
  document.getElementById('uaAllowAll')?.addEventListener('click', function () {
    document.querySelectorAll('.ua-toggle').forEach(function (el) { el.checked = true; });
    refreshLabels();
  });
  document.getElementById('uaDenyAll')?.addEventListener('click', function () {
    document.querySelectorAll('.ua-toggle').forEach(function (el) { el.checked = false; });
    refreshLabels();
  });
})();
</script>
@endsection
