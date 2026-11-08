@extends('layout.app')

@section('title', 'Users')

@section('content')
@include('partials.sale.erp-styles')

<style>
  .sr-card {
    box-shadow: 0 2px 12px rgba(15, 39, 71, 0.08);
    border-radius: 4px;
    border: 1px solid #d5dde8;
  }
  .sr-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    border: none;
    border-radius: 4px 4px 0 0;
    padding: 0.75rem 1rem;
    color: #fff;
  }
  .sr-table thead th {
    background: #0f2747;
    color: #fff;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border-color: #0a1c33;
    white-space: nowrap;
  }
  .sr-table tbody td {
    font-size: 0.82rem;
    border-color: #e8eef5;
    vertical-align: middle;
  }
  .sr-table tbody tr:nth-child(even) td { background: #f4f7fb; }
  .btn-navy { background: #0f2747; border-color: #0f2747; color: #fff; }
  .btn-navy:hover { background: #1a4a8a; border-color: #1a4a8a; color: #fff; }
  .sr-label { font-size: 0.78rem; color: #0f2747; font-weight: 600; margin-bottom: 0.25rem; }
  .user-pass-plain {
    font-size: 0.85rem;
    color: #0f2747;
    background: #eef3f9;
    padding: 0.2rem 0.45rem;
    border-radius: 3px;
    letter-spacing: 0.3px;
  }
</style>

<main class="app-main erp-sale-page" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center gy-2">
        <div class="col-12 col-md-6">
          <h3 class="mb-0 fw-bold" style="color:#0f2747;">
            <i class="bi bi-people me-2"></i>Users
          </h3>
          <p class="text-muted small mb-0">Setting · Manage accounts &amp; passwords</p>
        </div>
        <div class="col-12 col-md-6">
          <ol class="breadcrumb float-md-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Setting</span></li>
            <li class="breadcrumb-item active">Users</li>
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

      <div class="card sr-card border-0">
        <div class="card-header sr-card-head">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-people me-2"></i>All Accounts</h6>
            <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-light fw-semibold">
              <i class="bi bi-person-plus me-1"></i>Add User
            </a>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 sr-table">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Password</th>
                  <th>Last Login</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse($users as $u)
                  <tr>
                    <td class="fw-semibold">{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>
                      @if($u->isAdmin())
                        <span class="badge bg-danger">Admin</span>
                      @elseif($u->isManager())
                        <span class="badge text-bg-primary">Manager</span>
                      @elseif($u->isStaff())
                        <span class="badge text-bg-info text-dark">Staff</span>
                      @else
                        <span class="badge text-bg-secondary">Client</span>
                      @endif
                    </td>
                    <td>
                      @if($u->password_plain)
                        <code class="user-pass-plain">{{ $u->password_plain }}</code>
                      @else
                        <span class="text-muted">—</span>
                      @endif
                    </td>
                    <td class="text-muted small">{{ $u->last_login_at?->diffForHumans() ?? '—' }}</td>
                    <td class="text-center text-nowrap">
                      @unless($u->isAdmin())
                        <a href="{{ route('admin.user-access.edit', $u) }}" class="btn btn-sm btn-outline-primary me-1">
                          <i class="bi bi-sliders me-1"></i>Access
                        </a>
                      @endunless
                      <button type="button"
                              class="btn btn-sm btn-outline-secondary btn-set-password"
                              data-bs-toggle="modal"
                              data-bs-target="#setPasswordModal"
                              data-user-id="{{ $u->id }}"
                              data-user-name="{{ $u->name }}"
                              data-action="{{ route('admin.users.password.update', $u) }}">
                        <i class="bi bi-key me-1"></i>Set Password
                      </button>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<div class="modal fade" id="setPasswordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <form method="POST" id="setPasswordForm">
        @csrf
        @method('PUT')
        <div class="modal-header sr-card-head">
          <h5 class="modal-title text-white"><i class="bi bi-key me-2"></i>Set User Password</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-3">Set a new password for <strong id="setPasswordUserName"></strong>.</p>
          <div class="mb-3">
            <label class="form-label sr-label">New Password</label>
            <input type="password" name="password" class="form-control form-control-sm" required autocomplete="new-password" minlength="8">
          </div>
          <div class="mb-0">
            <label class="form-label sr-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control form-control-sm" required autocomplete="new-password" minlength="8">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-navy">Update Password</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('setPasswordModal')?.addEventListener('show.bs.modal', function (event) {
  const btn = event.relatedTarget;
  if (!btn) return;
  document.getElementById('setPasswordUserName').textContent = btn.getAttribute('data-user-name') || '';
  document.getElementById('setPasswordForm').action = btn.getAttribute('data-action') || '';
  document.getElementById('setPasswordForm').reset();
});
</script>
@endsection
