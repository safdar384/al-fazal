@extends('layout.app')

@section('title', 'Add New Labour')

@section('content')

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center gy-2">
                <div class="col-12 col-md-6">
                    <h3 class="mb-0 fw-bold" style="color:#0f2747;">
                        <i class="bi bi-person-plus me-2"></i>Add New Labour
                    </h3>
                    <p class="text-muted small mb-0">Labour · Create worker / agent / fitter profile</p>
                </div>
                <div class="col-12 col-md-6">
                    <ol class="breadcrumb float-md-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('Labour.index') }}">Labour</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add New</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                        <div>
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 ps-3 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('Labour.store') }}" method="POST" id="labourForm" class="nl-form">
                        @csrf
                <input type="hidden" name="labour_type" value="salary_labour">

                        <div class="row g-3">
                            <div class="col-lg-6">
                        <div class="card nl-card border-0 h-100">
                            <div class="card-header nl-card-head">
                                <h6 class="mb-0 text-white fw-semibold">
                                    <i class="bi bi-person-lines-fill me-2"></i>Basic Information
                                        </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-semibold nl-label">
                                            Labour Code <span class="text-danger">*</span>
                                        </label>
                                        <input type="text"
                                               name="labourCode"
                                               class="form-control @error('labourCode') is-invalid @enderror"
                                               value="{{ old('labourCode') }}"
                                               placeholder="e.g., L001"
                                               autocomplete="off"
                                               autofocus
                                               required>
                                                @error('labourCode')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-semibold nl-label">
                                            Labour Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text"
                                               name="labourName"
                                               class="form-control @error('labourName') is-invalid @enderror"
                                               value="{{ old('labourName') }}"
                                               placeholder="Enter full name"
                                               autocomplete="off"
                                               required>
                                                @error('labourName')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                    </div>
                                    <div class="col-12 mb-2">
                                        <label class="form-label fw-semibold nl-label">
                                            Address <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="labourAddress"
                                                  rows="2"
                                                  class="form-control @error('labourAddress') is-invalid @enderror"
                                                  placeholder="Enter address"
                                                  autocomplete="off"
                                                  required>{{ old('labourAddress') }}</textarea>
                                                @error('labourAddress')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-semibold nl-label">
                                            Category <span class="text-danger">*</span>
                                        </label>
                                        <select name="labourCatagory"
                                                id="labourCatagory"
                                                class="form-select @error('labourCatagory') is-invalid @enderror"
                                                required>
                                            <option value="">-- Select Category --</option>
                                            <option value="ajent" @selected(old('labourCatagory') === 'ajent')>Ajent</option>
                                            <option value="worker" @selected(old('labourCatagory') === 'worker')>Worker</option>
                                            <option value="fitter" @selected(old('labourCatagory') === 'fitter')>Fitter</option>
                                                </select>
                                                @error('labourCatagory')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                    </div>
                                    <div class="col-md-6 mb-0" id="commissionWrap" style="display: none;">
                                        <label class="form-label fw-semibold nl-label">Commission %</label>
                                                <div class="input-group">
                                            <input type="number"
                                                   step="0.01"
                                                   min="0"
                                                   max="100"
                                                   name="commissionPercentage"
                                                   id="commissionInput"
                                                   class="form-control @error('commissionPercentage') is-invalid @enderror"
                                                   value="{{ old('commissionPercentage') }}"
                                                   placeholder="0.00">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                                @error('commissionPercentage')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                    </div>

                            <div class="col-lg-6">
                        <div class="card nl-card border-0 mb-3">
                            <div class="card-header nl-card-head">
                                <h6 class="mb-0 text-white fw-semibold">
                                    <i class="bi bi-cash-stack me-2"></i>Financial Information
                                        </h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold nl-label">
                                        Salary <span class="text-danger">*</span>
                                    </label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Rs.</span>
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               name="salary"
                                               class="form-control @error('salary') is-invalid @enderror"
                                               value="{{ old('salary', 0) }}"
                                               required>
                                                </div>
                                                @error('salary')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold nl-label">
                                        Advance <span class="text-danger">*</span>
                                    </label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Rs.</span>
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               name="advance"
                                               class="form-control @error('advance') is-invalid @enderror"
                                               value="{{ old('advance', 0) }}"
                                               required>
                                                </div>
                                                @error('advance')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                </div>
                                <div class="mb-0">
                                    <label class="form-label fw-semibold nl-label">
                                        Balance <span class="text-danger">*</span>
                                    </label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Rs.</span>
                                        <input type="number"
                                               step="0.01"
                                               name="balance"
                                               class="form-control @error('balance') is-invalid @enderror"
                                               value="{{ old('balance', 0) }}"
                                               required>
                                                </div>
                                                @error('balance')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                </div>
                            </div>
                        </div>

                        <div class="nl-tips">
                            <h6 class="fw-semibold mb-2" style="color:#0f2747;">
                                <i class="bi bi-lightbulb me-1"></i>Quick Tips
                            </h6>
                            <ul class="mb-0 small ps-3 text-muted">
                                <li><strong>Category:</strong> Ajent, Worker, or Fitter</li>
                                <li><strong>Commission:</strong> Only shown for Ajent category</li>
                                <li><strong>Fitter:</strong> Used in Fitting Labour production</li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card nl-card border-0">
                            <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <small class="text-muted">
                                    <span class="text-danger fw-bold">*</span> Required fields
                                        </small>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('Labour.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-arrow-left me-1"></i>Back to List
                                    </a>
                                    <button type="reset" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                        </button>
                                    <button type="submit" class="btn btn-sm text-white px-4" style="background:#0f2747;border-color:#0f2747;">
                                        <i class="bi bi-save me-1"></i>Save Labour
                                        </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

@push('styles')
<style>
    .nl-card {
        box-shadow: 0 2px 12px rgba(15, 39, 71, 0.08);
        border-radius: 4px;
        overflow: visible;
        border: 1px solid #d5dde8;
    }
    .nl-card-head {
        background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
        border: none;
        border-radius: 4px 4px 0 0;
        padding: 0.75rem 1rem;
    }
    .nl-label { font-size: 0.78rem; color: #0f2747; margin-bottom: 0.25rem; }
    .nl-form .form-control,
    .nl-form .form-select { font-size: 0.9rem; border-color: #d5dde8; }
    .nl-form .form-control:focus,
    .nl-form .form-select:focus {
        border-color: #1a4a8a;
        box-shadow: 0 0 0 0.2rem rgba(15, 39, 71, 0.12);
    }
    .nl-form .btn-outline-secondary:hover {
        background: #0f2747;
        border-color: #0f2747;
        color: #fff;
    }
    .nl-tips {
        background: #f0f5fb;
        border: 1px solid #b8c9de;
        border-left: 4px solid #0f2747;
        border-radius: 4px;
        padding: 0.85rem 1rem;
    }
</style>
@endpush

<script>
    (function() {
    const form = document.getElementById('labourForm');
        const category = document.getElementById('labourCatagory');
        const commissionWrap = document.getElementById('commissionWrap');
        const commissionInput = document.getElementById('commissionInput');
    const submitBtn = form.querySelector('button[type="submit"]');

        function toggleCommission() {
            const show = category.value === 'ajent';
            commissionWrap.style.display = show ? 'block' : 'none';
            commissionInput.required = show;
            if (!show) commissionInput.value = '';
        }

    function clearFieldErrors() {
        form.querySelectorAll('.is-invalid').forEach(function(el) {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.nl-ajax-error').forEach(function(el) {
            el.remove();
        });
        const topAlert = document.getElementById('nlAjaxAlert');
        if (topAlert) topAlert.remove();
    }

    function showFieldErrors(errors) {
        Object.keys(errors || {}).forEach(function(field) {
            const input = form.querySelector('[name="' + field + '"]');
            if (!input) return;
            input.classList.add('is-invalid');
            const msg = document.createElement('div');
            msg.className = 'invalid-feedback d-block nl-ajax-error';
            msg.textContent = errors[field][0];
            const wrap = input.closest('.input-group') || input;
            wrap.parentNode.insertBefore(msg, wrap.nextSibling);
        });
    }

    function showModal(icon, title, text) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: icon,
                title: title,
                text: text,
                confirmButtonText: 'OK',
                confirmButtonColor: '#0f2747'
            });
        } else {
            alert(text);
        }
    }

        category.addEventListener('change', toggleCommission);
        toggleCommission();

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        clearFieldErrors();

        const originalHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: new FormData(form)
        })
        .then(function(res) {
            return res.json().then(function(data) {
                return { ok: res.ok, status: res.status, data: data };
            });
        })
        .then(function(result) {
            if (result.ok && result.data.success) {
                form.reset();
                toggleCommission();
                showModal('success', 'Saved!', result.data.message || 'Labour created successfully.');
                const codeInput = form.querySelector('[name="labourCode"]');
                if (codeInput) codeInput.focus();
                return;
            }

            if (result.status === 422 && result.data.errors) {
                showFieldErrors(result.data.errors);
                const first = Object.values(result.data.errors)[0];
                showModal('error', 'Validation', (first && first[0]) || 'Please fix the errors.');
                return;
            }

            showModal('error', 'Error', (result.data && result.data.message) || 'Could not save labour.');
        })
        .catch(function() {
            showModal('error', 'Error', 'Network error. Please try again.');
        })
        .finally(function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        });
    });
    })();
</script>

@endsection
