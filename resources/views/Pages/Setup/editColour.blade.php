@extends('layout.app')

@section('title', 'Edit Colour')

@section('content')

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold">
                        <i class="bi bi-pencil-square text-warning me-2"></i>
                        Edit Colour
                    </h3>
                    <p class="text-muted small mb-0">Update colour information</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('colours.index') }}">Colours</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <!-- Current Colour Info -->
                    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <div class="card-body p-4 text-white">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-white bg-opacity-25 p-3 me-3">
                                    <i class="bi bi-palette-fill fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold">{{ $colour->colourName }}</h5>
                                    <p class="mb-0 small opacity-75">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Editing this colour
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <form action="{{ route('colours.update', $colour->id) }}" method="POST" id="colourForm">
                                @csrf
                                @method('PUT')

                                <!-- Colour Information -->
                                <div class="mb-4">
                                    <h5 class="fw-semibold mb-3">
                                        <i class="bi bi-palette me-2 text-warning"></i>
                                        Colour Information
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-palette-fill text-warning me-1"></i>
                                                Colour Name <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">
                                                    <i class="bi bi-palette"></i>
                                                </span>
                                                <input type="text" 
                                                       class="form-control @error('colourName') is-invalid @enderror" 
                                                       name="colourName" 
                                                       value="{{ old('colourName', $colour->colourName) }}"
                                                       placeholder="e.g., Black, White, Red, Blue"
                                                       autocomplete="off" 
                                                       required
                                                       autofocus
                                                       data-original="{{ $colour->colourName }}">
                                                @error('colourName')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="bi bi-info-circle"></i>
                                                Colour name will be automatically converted to uppercase
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Change Notice -->
                                <div class="alert alert-warning border-0 shadow-sm mb-4">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Important Notice</h6>
                                            <p class="mb-0 small">
                                                Editing this colour will update its name everywhere it's used in the system.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="card border-0 bg-light mt-4">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <small class="text-muted">
                                                    <i class="bi bi-asterisk text-danger me-1"></i>
                                                    Fields marked with <span class="text-danger fw-bold">*</span> are required
                                                </small>
                                            </div>
                                            <div class="col-md-6 text-md-end mt-2 mt-md-0">
                                                <a href="{{ route('colours.index') }}" class="btn btn-light me-2">
                                                    <i class="bi bi-arrow-left me-1"></i>
                                                    Back to List
                                                </a>
                                                <button type="reset" class="btn btn-outline-secondary me-2">
                                                    <i class="bi bi-arrow-clockwise me-1"></i>
                                                    Reset
                                                </button>
                                                <button type="submit" class="btn btn-warning text-white px-4">
                                                    <i class="bi bi-save me-1"></i>
                                                    Update Colour
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@push('styles')
<style>
.card {
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.form-label {
    font-weight: 500;
}

.form-control:focus, .form-select:focus {
    border-color: #f59e0b;
    box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25);
}

.input-group-text {
    background-color: #f8f9fa;
    border-right: 0;
}

.input-group .form-control {
    border-left: 0;
}

.input-group:focus-within .input-group-text {
    border-color: #f59e0b;
}

.input-group:focus-within .form-control {
    border-color: #f59e0b;
}

.alert {
    border-radius: 10px;
}

.btn {
    border-radius: 6px;
    font-weight: 500;
}

.form-control.changed {
    border-color: #f59e0b !important;
    background-color: #fffbeb;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Highlight changed fields
    $('input[data-original], select[data-original], textarea[data-original]').on('input change', function() {
        const original = $(this).data('original');
        const current = $(this).val();
        
        if (original != current) {
            $(this).addClass('changed');
        } else {
            $(this).removeClass('changed');
        }
    });

    // Auto-dismiss alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>
@endpush

@endsection

