@extends('layout.app')

@section('title', 'Client Access')

@section('content')
@include('partials.sale.erp-styles')

<style>
  .ca-page { --ca-navy: #0f2747; --ca-mid: #1a4a8a; --ca-line: #d5dde8; --ca-ice: #eef3f8; }
  .ca-card {
    border: 1px solid var(--ca-line);
    border-radius: 6px;
    box-shadow: 0 2px 12px rgba(15, 39, 71, 0.07);
    background: #fff;
    overflow: hidden;
  }
  .ca-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
    padding: 0.85rem 1.1rem;
  }
  .ca-stat {
    border: 1px solid var(--ca-line);
    border-radius: 6px;
    background: #fff;
    padding: 0.9rem 1rem;
    height: 100%;
  }
  .ca-stat .label {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #6b7c90;
    margin-bottom: 0.25rem;
  }
  .ca-stat .value {
    font-size: 1.45rem;
    font-weight: 800;
    color: var(--ca-navy);
    line-height: 1.1;
  }
  .ca-stat .hint { font-size: 0.78rem; color: #7a8a9c; margin-top: 0.2rem; }

  .ca-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem 1.25rem;
    font-size: 0.82rem;
    color: #4a5d73;
  }
  .ca-dot {
    display: inline-block;
    width: 0.65rem;
    height: 0.65rem;
    border-radius: 50%;
    margin-right: 0.35rem;
    vertical-align: middle;
  }
  .ca-dot-allow { background: #198754; }
  .ca-dot-deny { background: #c0392b; }
  .ca-dot-mixed { background: #d4a017; }

  .ca-group-title {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--ca-navy);
    margin: 1.25rem 0 0.65rem;
    padding-bottom: 0.35rem;
    border-bottom: 2px solid var(--ca-navy);
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .ca-module {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid #e8eef5;
    transition: background 0.15s ease;
  }
  .ca-module:last-child { border-bottom: none; }
  .ca-module:hover { background: #f7fafc; }

  .ca-module-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.5rem;
    background: linear-gradient(145deg, #e8eef8, #d5e0f0);
    color: var(--ca-navy);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
  }

  .ca-module-body { flex: 1; min-width: 0; }
  .ca-module-body h6 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--ca-navy);
  }
  .ca-module-body p {
    margin: 0.15rem 0 0;
    font-size: 0.8rem;
    color: #6b7c90;
  }
  .ca-patterns {
    margin-top: 0.3rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
  }
  .ca-patterns code {
    font-size: 0.68rem;
    background: var(--ca-ice);
    color: #3d5168;
    padding: 0.1rem 0.35rem;
    border-radius: 3px;
  }

  .ca-switch-wrap {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.3rem;
    flex-shrink: 0;
  }
  .ca-status {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }
  .ca-status-allow { color: #198754; }
  .ca-status-deny { color: #c0392b; }
  .ca-status-mixed { color: #b8860b; }

  .form-check-input.ca-toggle {
    width: 2.6rem;
    height: 1.35rem;
    cursor: pointer;
    background-color: #c0392b;
    border-color: #c0392b;
  }
  .form-check-input.ca-toggle:checked {
    background-color: #198754;
    border-color: #198754;
  }
  .form-check-input.ca-toggle:focus {
    box-shadow: 0 0 0 0.2rem rgba(15, 39, 71, 0.18);
  }

  .ca-bulk .btn-navy {
    background: var(--ca-navy);
    border-color: var(--ca-navy);
    color: #fff;
  }
  .ca-bulk .btn-navy:hover {
    background: var(--ca-mid);
    border-color: var(--ca-mid);
    color: #fff;
  }

  .ca-info {
    background: #f0f5fb;
    border: 1px solid #c8d6e8;
    border-left: 3px solid var(--ca-navy);
    border-radius: 4px;
    padding: 0.85rem 1rem;
    font-size: 0.85rem;
    color: #3d5168;
  }

  .ca-custom thead th {
    background: var(--ca-navy);
    color: #fff;
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }
  .ca-custom tbody td { font-size: 0.85rem; vertical-align: middle; }
</style>

<main class="app-main erp-sale-page ca-page" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
      <div class="row align-items-center gy-2">
        <div class="col-12 col-md-7">
          <h3 class="mb-0 fw-bold" style="color:#0f2747;">
            <i class="bi bi-shield-lock me-2"></i>Client Access
          </h3>
          <p class="text-muted small mb-0">Setting · Decide which modules the <strong>Client</strong> role can open</p>
                </div>
        <div class="col-12 col-md-5">
          <ol class="breadcrumb float-md-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Setting</span></li>
            <li class="breadcrumb-item active">Client Access</li>
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
      @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
          {{ $errors->first() }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
          <div class="ca-stat">
            <div class="label">Modules</div>
            <div class="value">{{ collect($modules)->flatten(1)->count() }}</div>
            <div class="hint">Controlled areas</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="ca-stat">
            <div class="label">Blocked</div>
            <div class="value" style="color:#c0392b;">{{ $blockedCount }}</div>
            <div class="hint">Rules denying client</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="ca-stat">
            <div class="label">Allowed</div>
            <div class="value" style="color:#198754;">{{ $allowedCount }}</div>
            <div class="hint">Rules opened for client</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="ca-stat">
            <div class="label">Who is affected</div>
            <div class="value" style="font-size:1.1rem;">Client role</div>
            <div class="hint">Admins always have full access</div>
          </div>
        </div>
      </div>

      <div class="ca-info mb-3">
        <strong><i class="bi bi-info-circle me-1"></i>How it works</strong>
        <span class="d-block mt-1">
          Toggle <strong style="color:#198754;">Allow</strong> so a Client user can open that section.
          Toggle <strong style="color:#c0392b;">Deny</strong> to block it (403).
          Sales, orders, purchases, and reports that are not listed here stay available unless you add a custom rule.
        </span>
      </div>

      <div class="ca-card mb-3">
        <div class="ca-head d-flex flex-wrap align-items-center justify-content-between gap-2">
          <div>
            <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-toggles2 me-2"></i>Module access for Client users</h6>
            <div class="small opacity-75 mt-1">Green = allowed · Red = blocked</div>
          </div>
          <div class="ca-bulk d-flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.client-access.bulk-sync') }}"
                  onsubmit="return confirm('Allow Client access to ALL listed modules?');">
              @csrf
              <input type="hidden" name="allow" value="1">
              <button type="submit" class="btn btn-sm btn-light fw-semibold">
                <i class="bi bi-check2-all me-1"></i>Allow all
              </button>
            </form>
            <form method="POST" action="{{ route('admin.client-access.bulk-sync') }}"
                  onsubmit="return confirm('Block Client access to ALL listed modules?');">
              @csrf
              <input type="hidden" name="allow" value="0">
              <button type="submit" class="btn btn-sm btn-outline-light fw-semibold">
                <i class="bi bi-slash-circle me-1"></i>Deny all
              </button>
            </form>
          </div>
        </div>

        <div class="px-3 pb-3">
          <div class="ca-legend pt-3">
            <span><span class="ca-dot ca-dot-allow"></span>Allow — client can open</span>
            <span><span class="ca-dot ca-dot-deny"></span>Deny — client blocked</span>
            <span><span class="ca-dot ca-dot-mixed"></span>Mixed — partial rules</span>
          </div>

          @foreach($modules as $group => $items)
            <div class="ca-group-title">
              <i class="bi bi-folder2-open"></i> {{ $group }}
            </div>
            <div class="ca-card border-0 shadow-none" style="border:1px solid #e8eef5 !important;">
              @foreach($items as $m)
                <div class="ca-module">
                  <div class="ca-module-icon" aria-hidden="true">
                    <i class="bi {{ $m['icon'] }}"></i>
                  </div>
                  <div class="ca-module-body">
                    <h6>{{ $m['label'] }}</h6>
                    <p>{{ $m['description'] }}</p>
                    <div class="ca-patterns">
                      @foreach($m['patterns'] as $p)
                        <code>{{ $p }}</code>
                      @endforeach
                    </div>
                  </div>
                  <div class="ca-switch-wrap">
                    @if($m['mixed'])
                      <span class="ca-status ca-status-mixed">Mixed</span>
                    @elseif($m['allowed'])
                      <span class="ca-status ca-status-allow">Allowed</span>
                    @else
                      <span class="ca-status ca-status-deny">Denied</span>
                    @endif

                    <form method="POST" action="{{ route('admin.client-access.sync-module') }}" class="mb-0">
                        @csrf
                      <input type="hidden" name="module" value="{{ $m['key'] }}">
                      <input type="hidden" name="allow" value="{{ $m['allowed'] ? 0 : 1 }}">
                      <div class="form-check form-switch mb-0">
                        <input
                          class="form-check-input ca-toggle"
                          type="checkbox"
                          role="switch"
                          id="mod-{{ $m['key'] }}"
                          @checked($m['allowed'])
                          onchange="this.form.submit()"
                          title="{{ $m['allowed'] ? 'Click to deny' : 'Click to allow' }}"
                        >
                        <label class="visually-hidden" for="mod-{{ $m['key'] }}">
                          {{ $m['allowed'] ? 'Deny' : 'Allow' }} {{ $m['label'] }}
                        </label>
                      </div>
                    </form>
                  </div>
                </div>
              @endforeach
            </div>
          @endforeach
        </div>
      </div>

      {{-- Custom / advanced rules --}}
      <div class="ca-card mb-3">
        <div class="ca-head">
          <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-code-slash me-2"></i>Custom URL rules</h6>
          <div class="small opacity-75 mt-1">Advanced — block extra paths with Laravel <code class="text-white">Request::is()</code> patterns</div>
                </div>
                <div class="card-body">
          <form method="POST" action="{{ route('admin.client-access.store') }}" class="row g-2 align-items-end mb-3">
                        @csrf
            <div class="col-md-6">
              <label class="form-label small fw-semibold mb-1" style="color:#0f2747;">Pattern</label>
                            <input type="text" name="pattern" class="form-control form-control-sm @error('pattern') is-invalid @enderror"
                                   value="{{ old('pattern') }}" placeholder="e.g. expense*" required>
                            @error('pattern')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold mb-1" style="color:#0f2747;">For Client</label>
              <select name="is_active" class="form-select form-select-sm">
                <option value="1" selected>Deny (block)</option>
                <option value="0">Allow (inactive rule)</option>
              </select>
                        </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-sm w-100 text-white" style="background:#0f2747;">
                <i class="bi bi-plus-lg me-1"></i>Add rule
              </button>
            </div>
          </form>

          @if($customRestrictions->isEmpty())
            <p class="text-muted small mb-0">No custom rules yet. Module toggles above cover the standard areas.</p>
          @else
                    <div class="table-responsive">
              <table class="table table-hover align-middle mb-0 ca-custom">
                <thead>
                                <tr>
                                    <th>Pattern</th>
                    <th>Client access</th>
                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                  @foreach($customRestrictions as $r)
                                    <tr>
                                        <td><code>{{ $r->pattern }}</code></td>
                                        <td>
                                            @if($r->is_active)
                          <span class="badge text-bg-danger">Denied</span>
                                            @else
                          <span class="badge text-bg-success">Allowed</span>
                                            @endif
                                        </td>
                                        <td class="text-end text-nowrap">
                                            <form method="POST" action="{{ route('admin.client-access.update', $r) }}" class="d-inline">
                                                @csrf
                          @method('PATCH')
                                                <input type="hidden" name="is_active" value="{{ $r->is_active ? 0 : 1 }}">
                          <button type="submit" class="btn btn-sm btn-outline-secondary py-0">
                            {{ $r->is_active ? 'Allow' : 'Deny' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.client-access.destroy', $r) }}" class="d-inline"
                              onsubmit="return confirm('Remove this custom rule?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                  @endforeach
                            </tbody>
                        </table>
                    </div>
          @endif
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
