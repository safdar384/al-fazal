@extends('layout.app')

@section('title', 'Add New Agent')

@section('content')

<main class="app-main" id="main" tabindex="-1">
    <!--begin::App Content Header-->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold">
                        <i class="bi bi-person-plus-fill text-primary me-2"></i>
                        Add New Agent
                    </h3>
                    <p class="text-muted small mb-0">Create a new agent profile</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('NewAgent.index') }}">Agents</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add New</li>
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
                    <div class="flex-shrink-0">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading mb-2">Please fix the following errors:</h5>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-9">
                    <div class="card border-0 shadow-sm">
                        <form action="{{ route('NewAgent.store') }}" method="POST" id="agentForm">
                            @csrf
                            
                            <div class="card-body p-4">
                                <!-- Basic Information Section -->
                                <div class="mb-4">
                                    <h6 class="text-primary mb-3 pb-2 border-bottom">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Basic Information
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-upc-scan text-primary me-1"></i>
                                                Agent Code
                                                <span class="badge bg-success bg-opacity-10 text-success ms-2">
                                                    <i class="bi bi-magic"></i> Auto
                                                </span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control form-control-lg @error('ajentCode') is-invalid @enderror" 
                                                   name="ajentCode" 
                                                   value="{{ old('ajentCode') }}" 
                                                   placeholder="Leave blank for auto-generation"
                                                   autocomplete="off">
                                            @error('ajentCode')
                                                <div class="invalid-feedback d-flex align-items-center">
                                                    <i class="bi bi-exclamation-circle me-1"></i>
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                            <small class="form-text text-success">
                                                <i class="bi bi-stars"></i>
                                                Will auto-generate if left blank
                                            </small>
                                        </div>

                                        <div class="col-md-8">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-person text-primary me-1"></i>
                                                Agent Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control form-control-lg @error('ajentName') is-invalid @enderror" 
                                                   name="ajentName" 
                                                   value="{{ old('ajentName') }}"
                                                   placeholder="Enter full name"
                                                   autocomplete="off" 
                                                   required>
                                            @error('ajentName')
                                                <div class="invalid-feedback d-flex align-items-center">
                                                    <i class="bi bi-exclamation-circle me-1"></i>
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Information Section -->
                                <div class="mb-4">
                                    <h6 class="text-primary mb-3 pb-2 border-bottom">
                                        <i class="bi bi-telephone me-2"></i>
                                        Contact Information
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-phone text-primary me-1"></i>
                                                Agent Contact
                                            </label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-light">
                                                    <i class="bi bi-telephone-fill text-primary"></i>
                                                </span>
                                                <input type="text" 
                                                       class="form-control @error('ajentContact') is-invalid @enderror" 
                                                       name="ajentContact" 
                                                       value="{{ old('ajentContact') }}"
                                                       placeholder="03XX-XXXXXXX"
                                                       autocomplete="off">
                                                @error('ajentContact')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-geo-alt text-primary me-1"></i>
                                                Agent Address
                                            </label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-light">
                                                    <i class="bi bi-geo-alt-fill text-primary"></i>
                                                </span>
                                                <input type="text" 
                                                       class="form-control @error('ajentAdderss') is-invalid @enderror" 
                                                       name="ajentAdderss" 
                                                       value="{{ old('ajentAdderss') }}"
                                                       placeholder="Complete address"
                                                       autocomplete="off">
                                                @error('ajentAdderss')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Financial Information Section -->
                                <div class="mb-4">
                                    <h6 class="text-primary mb-3 pb-2 border-bottom">
                                        <i class="bi bi-cash-stack me-2"></i>
                                        Financial Information
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-wallet2 text-success me-1"></i>
                                                Agent Advance
                                            </label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-success bg-opacity-10 text-success fw-bold">
                                                    Rs.
                                                </span>
                                                <input type="number" 
                                                       step="0.01" 
                                                       class="form-control @error('AjentAdvance') is-invalid @enderror" 
                                                       name="AjentAdvance" 
                                                       value="{{ old('AjentAdvance', 0) }}"
                                                       placeholder="0.00"
                                                       autocomplete="off">
                                                @error('AjentAdvance')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-percent text-info me-1"></i>
                                                Commission (%)
                                            </label>
                                            <div class="input-group input-group-lg">
                                                <input type="number" 
                                                       step="0.01" 
                                                       class="form-control @error('ajentCommission') is-invalid @enderror" 
                                                       name="ajentCommission" 
                                                       value="{{ old('ajentCommission', 0) }}"
                                                       placeholder="0.00"
                                                       autocomplete="off">
                                                <span class="input-group-text bg-info bg-opacity-10 text-info fw-bold">
                                                    %
                                                </span>
                                                @error('ajentCommission')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="bi bi-info-circle"></i>
                                                Maximum 100%
                                            </small>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-cash-coin text-warning me-1"></i>
                                                Agent Balance
                                            </label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-warning bg-opacity-10 text-warning fw-bold">
                                                    Rs.
                                                </span>
                                                <input type="number" 
                                                       step="0.01" 
                                                       class="form-control @error('ajentBalance') is-invalid @enderror" 
                                                       name="ajentBalance" 
                                                       value="{{ old('ajentBalance', 0) }}"
                                                       placeholder="0.00"
                                                       autocomplete="off">
                                                @error('ajentBalance')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Information Notice -->
                                <div class="alert alert-info border-0 shadow-sm">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="alert-heading mb-1">Quick Tips</h6>
                                            <ul class="mb-0 small ps-3">
                                                <li><strong>Agent Code:</strong> Leave blank for automatic generation (e.g., AGT011, AGT012, etc.)</li>
                                                <li><strong>Commission:</strong> Percentage cannot exceed 100%</li>
                                                <li><strong>Currency:</strong> All financial values are in Pakistani Rupees (PKR)</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-light border-0 py-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="bi bi-asterisk text-danger me-1"></i>
                                            Fields marked with <span class="text-danger fw-bold">*</span> are required
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                                        <a href="{{ route('NewAgent.index') }}" class="btn btn-light me-2">
                                            <i class="bi bi-arrow-left me-1"></i>
                                            Back to List
                                        </a>
                                        <button type="reset" class="btn btn-outline-secondary me-2">
                                            <i class="bi bi-arrow-clockwise me-1"></i>
                                            Reset
                                        </button>
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="bi bi-save me-1"></i>
                                            Save Agent
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
/* Form Enhancements */
.form-label {
    margin-bottom: 0.5rem;
    color: #495057;
    font-size: 0.95rem;
}

.form-control-lg {
    border-radius: 0.5rem;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
}

.input-group-text {
    border-radius: 0.5rem 0 0 0.5rem;
}

.input-group-lg > .input-group-text {
    padding: 0.5rem 1rem;
}

/* Card Styling */
.card {
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
}

.card-header {
    border-bottom: none;
}

.card-footer {
    border-top: 1px solid rgba(0,0,0,0.05);
}

/* Button Enhancements */
.btn {
    border-radius: 0.5rem;
    padding: 0.5rem 1.25rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5568d3 0%, #653a8a 100%);
}

/* Alert Styling */
.alert {
    border-radius: 10px;
    border-left: 4px solid;
}

.alert-danger {
    border-left-color: #dc3545;
    background-color: #ffe8e8;
}

.alert-info {
    border-left-color: #0dcaf0;
    background-color: #e7f7ff;
}

/* Form Validation Feedback */
.invalid-feedback {
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.is-invalid {
    border-color: #dc3545 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.3rem) center;
    background-size: calc(0.75em + 0.6rem) calc(0.75em + 0.6rem);
}

/* Help Section */
.bi.fs-3 {
    opacity: 0.8;
}

/* Form Animation */
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

/* Responsive */
@media (max-width: 768px) {
    .card-footer .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('agentForm');
    
    // Auto-uppercase agent code if user enters one
    const agentCodeInput = document.querySelector('input[name="ajentCode"]');
    if (agentCodeInput) {
        agentCodeInput.addEventListener('input', function(e) {
            if (this.value.trim() !== '') {
                this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            }
        });
        
        // Show helpful message
        agentCodeInput.addEventListener('focus', function(e) {
            if (this.value.trim() === '') {
                this.placeholder = 'Will auto-generate (AGT011, AGT012, etc.)';
            }
        });
        
        agentCodeInput.addEventListener('blur', function(e) {
            this.placeholder = 'Leave blank for auto-generation';
        });
    }
    
    // Auto-capitalize agent name
    const agentNameInput = document.querySelector('input[name="ajentName"]');
    if (agentNameInput) {
        agentNameInput.addEventListener('blur', function(e) {
            const words = this.value.toLowerCase().split(' ');
            const capitalized = words.map(word => 
                word.charAt(0).toUpperCase() + word.slice(1)
            ).join(' ');
            this.value = capitalized;
        });
    }
    
    // Format phone number
    const contactInput = document.querySelector('input[name="ajentContact"]');
    if (contactInput) {
        contactInput.addEventListener('blur', function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value.length === 11 && value.startsWith('03')) {
                this.value = value.substring(0, 4) + '-' + value.substring(4);
            }
        });
    }
    
    // Auto-dismiss alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (bootstrap.Alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 8000);
});
</script>

@endsection
