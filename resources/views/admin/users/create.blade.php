@extends('layout.app')

@section('title', 'Add User')

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
  .sr-label { font-size: 0.78rem; color: #0f2747; font-weight: 600; margin-bottom: 0.25rem; }
  .btn-navy { background: #0f2747; border-color: #0f2747; color: #fff; }
  .btn-navy:hover { background: #1a4a8a; border-color: #1a4a8a; color: #fff; }
</style>

<main class="app-main erp-sale-page" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center gy-2">
        <div class="col-12 col-md-6">
          <h3 class="mb-0 fw-bold" style="color:#0f2747;">
            <i class="bi bi-person-plus me-2"></i>Add User
          </h3>
          <p class="text-muted small mb-0">Setting · Create a new login account</p>
        </div>
        <div class="col-12 col-md-6">
          <ol class="breadcrumb float-md-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
            <li class="breadcrumb-item active">Add</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <div class="card sr-card border-0" style="max-width: 560px;">
        <div class="card-header sr-card-head">
          <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-person-plus me-2"></i>New Account</h6>
        </div>
        <div class="card-body p-3">
          <p class="text-muted small">Only administrators can create accounts. New users sign in with the email and password you set here.</p>
          <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="mb-3">
              <label class="form-label sr-label">Name</label>
              <input type="text" name="name" class="form-control form-control-sm @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label sr-label">Email</label>
              <input type="email" name="email" class="form-control form-control-sm @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label sr-label">Role</label>
              <select name="role" class="form-select form-select-sm @error('role') is-invalid @enderror" required>
                <option value="{{ \App\Models\User::ROLE_ADMIN }}" @selected(old('role') === \App\Models\User::ROLE_ADMIN)>Admin</option>
                <option value="{{ \App\Models\User::ROLE_MANAGER }}" @selected(old('role', \App\Models\User::ROLE_STAFF) === \App\Models\User::ROLE_MANAGER)>Manager</option>
                <option value="{{ \App\Models\User::ROLE_STAFF }}" @selected(old('role', \App\Models\User::ROLE_STAFF) === \App\Models\User::ROLE_STAFF)>Staff</option>
                <option value="{{ \App\Models\User::ROLE_CLIENT }}" @selected(old('role') === \App\Models\User::ROLE_CLIENT)>Client</option>
              </select>
              @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
              <div class="form-text">Admin: full access. Manager / Staff / Client: limited by permissions.</div>
            </div>
            <div class="mb-3">
              <label class="form-label sr-label">Password</label>
              <input type="password" name="password" class="form-control form-control-sm @error('password') is-invalid @enderror" required autocomplete="new-password">
              @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label class="form-label sr-label">Confirm Password</label>
              <input type="password" name="password_confirmation" class="form-control form-control-sm" required autocomplete="new-password">
            </div>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-sm btn-navy"><i class="bi bi-check2 me-1"></i>Create User</button>
              <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection
