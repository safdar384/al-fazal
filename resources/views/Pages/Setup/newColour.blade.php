@extends('layout.app')

@section('title', 'Add New Colour')

@section('content')

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold">
                        <i class="bi bi-palette-fill text-primary me-2"></i>
                        Add New Colour
                    </h3>
                    <p class="text-muted small mb-0">Create a new colour for your products</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('colours.index') }}">Colours</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add New</li>
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
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <form action="{{ route('colours.store') }}" method="POST" id="colourForm">
                                @csrf

                                <!-- Colour Information -->
                                <div class="mb-4">
                                    <h5 class="fw-semibold mb-3">
                                        <i class="bi bi-palette me-2 text-primary"></i>
                                        Colour Information
                                    </h5>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-palette-fill text-primary me-1"></i>
                                                Colour Name <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">
                                                    <i class="bi bi-palette"></i>
                                                </span>
                                                <input type="text" 
                                                       class="form-control @error('colourName') is-invalid @enderror" 
                                                       name="colourName" 
                                                       value="{{ old('colourName') }}"
                                                       placeholder="e.g., Black, White, Red, Blue"
                                                       autocomplete="off" 
                                                       required
                                                       autofocus>
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

                                <!-- Quick Reference -->
                                <div class="alert alert-info border-0 shadow-sm mb-4">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-lightbulb-fill fs-4 me-3"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Colour Naming Tips</h6>
                                            <ul class="mb-0 small">
                                                <li>Use clear, descriptive names (e.g., "Navy Blue" not "NB")</li>
                                                <li>Be consistent with naming conventions</li>
                                                <li>Common colours: Black, White, Red, Blue, Green, Yellow, etc.</li>
                                            </ul>
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
                                                <button type="submit" class="btn btn-primary px-4">
                                                    <i class="bi bi-save me-1"></i>
                                                    Save Colour
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
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.input-group-text {
    background-color: #f8f9fa;
    border-right: 0;
}

.input-group .form-control {
    border-left: 0;
}

.input-group:focus-within .input-group-text {
    border-color: #667eea;
}

.input-group:focus-within .form-control {
    border-color: #667eea;
}

.alert {
    border-radius: 10px;
}

.btn {
    border-radius: 6px;
    font-weight: 500;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
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

