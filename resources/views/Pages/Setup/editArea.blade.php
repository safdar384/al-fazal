@extends('layout.app')

@section('title', 'Edit Area')

@section('content')

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold">
                        <i class="bi bi-pencil-square text-warning me-2"></i>
                        Edit Area
                    </h3>
                    <p class="text-muted small mb-0">Update area information</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('areas.index') }}">Areas</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            
            <!-- Validation Errors -->
            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                <div class="d-flex align-items-start">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-2">Please fix the following errors:</h5>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-7">
                    <div class="card border-0 shadow-sm">
                        <form action="{{ route('areas.update', $area->id) }}" method="POST" id="editAreaForm">
                            @csrf
                            @method('PUT')
                            
                            <div class="card-body p-4">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-upc-scan text-warning me-1"></i>
                                        Area Code <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('areaCode') is-invalid @enderror" 
                                           name="areaCode" 
                                           value="{{ old('areaCode', $area->areaCode) }}" 
                                           placeholder="e.g., LHR, KHI, ISB"
                                           autocomplete="off" 
                                           autofocus
                                           required>
                                    @error('areaCode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="bi bi-lightbulb"></i>
                                        Use 3-letter city abbreviations
                                    </small>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-geo-alt text-warning me-1"></i>
                                        Area Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('areaName') is-invalid @enderror" 
                                           name="areaName" 
                                           value="{{ old('areaName', $area->areaName) }}"
                                           placeholder="Enter area/city name"
                                           autocomplete="off" 
                                           required>
                                    @error('areaName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-0">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-person text-warning me-1"></i>
                                        Agent Name
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('ajentName') is-invalid @enderror" 
                                           name="ajentName" 
                                           value="{{ old('ajentName', $area->ajentName) }}"
                                           placeholder="Assign agent to this area (optional)"
                                           autocomplete="off">
                                    @error('ajentName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="bi bi-info-circle"></i>
                                        Optional - You can assign an agent later
                                    </small>
                                </div>
                            </div>

                            <div class="card-footer bg-light border-0 py-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="bi bi-clock-history me-1"></i>
                                            Editing: <span class="fw-semibold">{{ $area->areaName }} ({{ $area->areaCode }})</span>
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                                        <a href="{{ route('areas.index') }}" class="btn btn-light me-2">
                                            <i class="bi bi-arrow-left me-1"></i>
                                            Back to List
                                        </a>
                                        <button type="reset" class="btn btn-outline-secondary me-2">
                                            <i class="bi bi-arrow-clockwise me-1"></i>
                                            Reset
                                        </button>
                                        <button type="submit" class="btn btn-warning text-white px-4">
                                            <i class="bi bi-save me-1"></i>
                                            Update Area
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.card {
    border-radius: 12px;
    overflow: hidden;
}

.form-control-lg {
    border-radius: 0.5rem;
}

.form-control:focus {
    border-color: #f6d365;
    box-shadow: 0 0 0 0.25rem rgba(246, 211, 101, 0.15);
}

.btn-warning {
    background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
    border: none;
}

.btn-warning:hover {
    background: linear-gradient(135deg, #e5c254 0%, #ec8f74 100%);
}

.card-body {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-uppercase area code
    const areaCodeInput = document.querySelector('input[name="areaCode"]');
    if (areaCodeInput) {
        areaCodeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
    }
    
    // Auto-capitalize area name and agent name
    const nameInputs = document.querySelectorAll('input[name="areaName"], input[name="ajentName"]');
    nameInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const words = this.value.toLowerCase().split(' ');
            this.value = words.map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        });
    });
});
</script>

@endsection

