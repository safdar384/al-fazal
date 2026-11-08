@extends('layout.app')

@section('title', 'User Access')

@section('content')
@include('partials.sale.erp-styles')

<style>
  .ua-card { border: 1px solid #d5dde8; border-radius: 6px; box-shadow: 0 2px 12px rgba(15,39,71,.07); overflow: hidden; }
  .ua-head { background: linear-gradient(135deg,#0f2747,#1a4a8a); color:#fff; padding:.85rem 1.1rem; }
  .ua-table thead th { background:#0f2747; color:#fff; font-size:.68rem; text-transform:uppercase; letter-spacing:.3px; }
  .ua-table tbody td { font-size:.85rem; vertical-align:middle; }
  .ua-table tbody tr:nth-child(even) td { background:#f4f7fb; }
</style>

<main class="app-main erp-sale-page" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center gy-2">
        <div class="col-12 col-md-7">
          <h3 class="mb-0 fw-bold" style="color:#0f2747;">
            <i class="bi bi-person-lock me-2"></i>User Access
          </h3>
          <p class="text-muted small mb-0">Setting · Give each staff / manager / client different modules</p>
        </div>
        <div class="col-12 col-md-5">
          <ol class="breadcrumb float-md-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Setting</span></li>
            <li class="breadcrumb-item active">User Access</li>
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
      @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">{{ session('warning') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <div class="alert border-0 mb-3" style="background:#f0f5fb;border-left:3px solid #0f2747 !important;color:#3d5168;font-size:.88rem;">
        <strong><i class="bi bi-info-circle me-1"></i>Tip:</strong>
        Pick a user → toggle modules (Sales, Bank, Reports…) → Save.
        Each person can have a different set. Admins always keep full access.
      </div>

      <div class="ua-card">
        <div class="ua-head d-flex flex-wrap justify-content-between align-items-center gap-2">
          <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-people me-2"></i>Assignable users</h6>
          <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-light fw-semibold">
            <i class="bi bi-person-plus me-1"></i>Add User
          </a>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0 ua-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Access mode</th>
                <th class="text-end">Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($users as $u)
                <tr>
                  <td class="fw-semibold">{{ $u->name }}</td>
                  <td>{{ $u->email }}</td>
                  <td>
                    @if($u->isManager())
                      <span class="badge text-bg-primary">Manager</span>
                    @elseif($u->isStaff())
                      <span class="badge text-bg-info text-dark">Staff</span>
                    @else
                      <span class="badge text-bg-secondary">Client</span>
                    @endif
                  </td>
                  <td>
                    @if($u->access_customized)
                      <span class="badge text-bg-success">Custom</span>
                      <span class="text-muted small ms-1">{{ $u->allowed_modules_count }} modules</span>
                    @else
                      <span class="badge text-bg-secondary">Role default</span>
                      <span class="text-muted small ms-1">({{ $u->role }})</span>
                    @endif
                  </td>
                  <td class="text-end">
                    <a href="{{ route('admin.user-access.edit', $u) }}" class="btn btn-sm text-white" style="background:#0f2747;">
                      <i class="bi bi-sliders me-1"></i>Set Access
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">
                    No staff / manager / client users yet.
                    <a href="{{ route('admin.users.create') }}">Add a user</a> with role <strong>Staff</strong>, then set access here.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection
