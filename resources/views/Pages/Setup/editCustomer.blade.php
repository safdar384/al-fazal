@extends('layout.app')

@section('title', 'Edit Customer')

@section('content')

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold">
                        <i class="bi bi-pencil-square text-warning me-2"></i>
                        Edit Customer
                    </h3>
                    <p class="text-muted small mb-0">Update customer profile information</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('Customer.index') }}">Customers</a></li>
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

            <!-- Customer Summary Header -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body bg-gradient-warning text-white py-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="bg-white bg-opacity-25 p-3 rounded-circle me-3">
                                    <i class="bi bi-shop-window fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">{{ $customer->bussnessName }}</h5>
                                    <small class="opacity-75">
                                        <i class="bi bi-upc-scan me-1"></i>{{ $customer->custCode }} | 
                                        <i class="bi bi-geo-alt me-1"></i>{{ $customer->custArea }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <div class="d-flex justify-content-md-end gap-3">
                                <div class="text-center">
                                    <small class="d-block opacity-75">Balance</small>
                                    <h5 class="mb-0 fw-bold">Rs. {{ number_format($customer->custBalance, 0) }}</h5>
                                </div>
                                <div class="text-center">
                                    <small class="d-block opacity-75">Credit</small>
                                    <h5 class="mb-0 fw-bold">Rs. {{ number_format($customer->custCreditLimit, 0) }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-12">
                    <form action="{{ route('Customer.update', $customer->id) }}" method="POST" id="editCustomerForm">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- Left Column -->
                            <div class="col-lg-6">
                                <!-- Basic Information -->
                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-body p-4">
                                        <h6 class="text-warning mb-3 pb-2 border-bottom">
                                            <i class="bi bi-info-circle me-2"></i>
                                            Basic Information
                                        </h6>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-geo-alt text-warning me-1"></i>
                                                Customer Area <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select @error('custArea') is-invalid @enderror" 
                                                    name="custArea">
                                                <option value="">-- Select Area --</option>
                                                @foreach($custAreas as $custArea)
                                                    <option value="{{ $custArea->areaCode }}" {{ old('custArea', $customer->custArea) == $custArea->areaCode ? 'selected' : '' }}>
                                                        {{ $custArea->areaCode }} - {{ $custArea->areaName }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('custArea')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-upc-scan text-warning me-1"></i>
                                                Customer Code <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('custCode') is-invalid @enderror" 
                                                   name="custCode" 
                                                   value="{{ old('custCode', $customer->custCode) }}" 
                                                   autocomplete="off"
                                                   required>
                                            @error('custCode')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-building text-warning me-1"></i>
                                                Business Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('bussnessName') is-invalid @enderror" 
                                                   name="bussnessName" 
                                                   value="{{ old('bussnessName', $customer->bussnessName) }}"
                                                   autocomplete="off" 
                                                   required>
                                            @error('bussnessName')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-person text-warning me-1"></i>
                                                Customer Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('custName') is-invalid @enderror" 
                                                   name="custName" 
                                                   value="{{ old('custName', $customer->custName) }}"
                                                   autocomplete="off" 
                                                   required>
                                            @error('custName')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-map text-warning me-1"></i>
                                                Customer Address <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control @error('custAddress') is-invalid @enderror" 
                                                      name="custAddress" 
                                                      rows="2"
                                                      autocomplete="off" 
                                                      required>{{ old('custAddress', $customer->custAddress) }}</textarea>
                                            @error('custAddress')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-building-check text-warning me-1"></i>
                                                    City <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" 
                                                       class="form-control @error('custCity') is-invalid @enderror" 
                                                       name="custCity" 
                                                       value="{{ old('custCity', $customer->custCity) }}"
                                                       autocomplete="off" 
                                                       required>
                                                @error('custCity')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-telephone text-warning me-1"></i>
                                                    Contact <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light">
                                                        <i class="bi bi-telephone-fill text-warning"></i>
                                                    </span>
                                                    <input type="text" 
                                                           class="form-control @error('custContect') is-invalid @enderror" 
                                                           name="custContect" 
                                                           value="{{ old('custContect', $customer->custContect) }}"
                                                           autocomplete="off" 
                                                           required>
                                                    @error('custContect')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-0">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-book text-warning me-1"></i>
                                                Customer Page <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('custPage') is-invalid @enderror" 
                                                   name="custPage" 
                                                   value="{{ old('custPage', $customer->custPage) }}"
                                                   autocomplete="off" 
                                                   required>
                                            @error('custPage')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-lg-6">
                                <!-- Financial Information -->
                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-body p-4">
                                        <h6 class="text-success mb-3 pb-2 border-bottom">
                                            <i class="bi bi-cash-stack me-2"></i>
                                            Financial Information
                                        </h6>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-percent text-info me-1"></i>
                                                Discount (%) <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       step="0.01" 
                                                       class="form-control @error('custdiscount') is-invalid @enderror" 
                                                       name="custdiscount" 
                                                       value="{{ old('custdiscount', $customer->custdiscount) }}"
                                                       autocomplete="off" 
                                                       required>
                                                <span class="input-group-text bg-info bg-opacity-10 text-info">%</span>
                                                @error('custdiscount')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-calendar-week text-warning me-1"></i>
                                                Payment Term (Days) <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">
                                                    <i class="bi bi-clock text-warning"></i>
                                                </span>
                                                <input type="number" 
                                                       class="form-control @error('custPayTermDay') is-invalid @enderror" 
                                                       name="custPayTermDay" 
                                                       value="{{ old('custPayTermDay', $customer->custPayTermDay) }}"
                                                       autocomplete="off" 
                                                       required>
                                                <span class="input-group-text bg-light">Days</span>
                                                @error('custPayTermDay')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-credit-card text-success me-1"></i>
                                                Credit Limit <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-success bg-opacity-10 text-success fw-bold">Rs.</span>
                                                <input type="number" 
                                                       step="0.01" 
                                                       class="form-control @error('custCreditLimit') is-invalid @enderror" 
                                                       name="custCreditLimit" 
                                                       value="{{ old('custCreditLimit', $customer->custCreditLimit) }}"
                                                       autocomplete="off" 
                                                       required>
                                                @error('custCreditLimit')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-calendar-date text-primary me-1"></i>
                                                Opening Date <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" 
                                                   class="form-control @error('custOppDate') is-invalid @enderror" 
                                                   name="custOppDate" 
                                                   value="{{ old('custOppDate', $customer->custOppDate) }}"
                                                   autocomplete="off" 
                                                   required>
                                            @error('custOppDate')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-0">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-tag text-info me-1"></i>
                                                Customer Category <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select @error('custCatagory') is-invalid @enderror" 
                                                    name="custCatagory" 
                                                    required>
                                                <option value="">-- Select Category --</option>
                                                <option value="Retail" {{ old('custCatagory', $customer->custCatagory) == 'Retail' ? 'selected' : '' }}>Retail Customer</option>
                                                <option value="Wholesale" {{ old('custCatagory', $customer->custCatagory) == 'Wholesale' ? 'selected' : '' }}>Wholesale Customer</option>
                                                <option value="VIP" {{ old('custCatagory', $customer->custCatagory) == 'VIP' ? 'selected' : '' }}>VIP Customer</option>
                                                <option value="Supplier" {{ old('custCatagory', $customer->custCatagory) == 'Supplier' ? 'selected' : '' }}>Supplier</option>
                                            </select>
                                            @error('custCatagory')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-lg-6">
                                <!-- Financial Summary Cards -->
                                <div class="row g-2 mb-3">
                                    <div class="col-md-4">
                                        <div class="card border-0 bg-primary bg-opacity-10">
                                            <div class="card-body p-3 text-center">
                                                <i class="bi bi-percent text-primary fs-4 d-block mb-2"></i>
                                                <small class="text-muted d-block">Discount</small>
                                                <h5 class="mb-0 text-primary fw-bold">{{ $customer->custdiscount }}%</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-0 bg-warning bg-opacity-10">
                                            <div class="card-body p-3 text-center">
                                                <i class="bi bi-calendar-week text-warning fs-4 d-block mb-2"></i>
                                                <small class="text-muted d-block">Payment Terms</small>
                                                <h5 class="mb-0 text-warning fw-bold">{{ $customer->custPayTermDay }} Days</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-0 bg-info bg-opacity-10">
                                            <div class="card-body p-3 text-center">
                                                <i class="bi bi-tag text-info fs-4 d-block mb-2"></i>
                                                <small class="text-muted d-block">Category</small>
                                                <h6 class="mb-0 text-info fw-bold">{{ $customer->custCatagory }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Update Notice -->
                                <div class="alert alert-warning border-0 shadow-sm">
                                    <div class="d-flex">
                                        <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="alert-heading mb-1">Update Information</h6>
                                            <small class="mb-0">
                                                Updating this customer will affect all related transactions and records.
                                                Please review the changes carefully before saving.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="card border-0 shadow-sm mt-3">
                            <div class="card-footer bg-light border-0 py-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="bi bi-clock-history me-1"></i>
                                            Last modified: <span class="fw-semibold">{{ now()->format('d M Y') }}</span>
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                                        <a href="{{ route('Customer.index') }}" class="btn btn-light me-2">
                                            <i class="bi bi-arrow-left me-1"></i>
                                            Back to List
                                        </a>
                                        <button type="reset" class="btn btn-outline-secondary me-2">
                                            <i class="bi bi-arrow-clockwise me-1"></i>
                                            Reset
                                        </button>
                                        <button type="submit" class="btn btn-warning text-white px-4">
                                            <i class="bi bi-save me-1"></i>
                                            Update Customer
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
</main>

<style>
/* Gradient Header */
.bg-gradient-warning {
    background: linear-gradient(135deg, #f6d365 0%, #fda085 100%) !important;
}

.card {
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
}

.form-label {
    margin-bottom: 0.5rem;
    color: #495057;
    font-size: 0.95rem;
}

.form-control:focus, .form-select:focus {
    border-color: #f6d365;
    box-shadow: 0 0 0 0.25rem rgba(246, 211, 101, 0.15);
}

.input-group-text {
    border-radius: 0.375rem 0 0 0.375rem;
}

.btn {
    border-radius: 0.5rem;
    padding: 0.5rem 1.25rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-warning {
    background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
    border: none;
}

.btn-warning:hover {
    background: linear-gradient(135deg, #e5c254 0%, #ec8f74 100%);
}

.alert {
    border-radius: 10px;
    border-left: 4px solid;
}

.alert-danger {
    border-left-color: #dc3545;
    background-color: #ffe8e8;
}

.alert-warning {
    border-left-color: #ffc107;
    background-color: #fff8e1;
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

/* Change highlighting */
.border-warning-highlight {
    border-color: #ffc107 !important;
    border-width: 2px !important;
}
</style>

<script>
$(document).ready(function() {
    const form = $('#editCustomerForm');
    
    // Store original values
    const originalValues = {};
    form.find('input, select, textarea').each(function() {
        originalValues[this.name] = $(this).val();
    });
    
    // Highlight changed fields
    form.find('input, select, textarea').on('input change', function() {
        if ($(this).val() !== originalValues[this.name]) {
            $(this).addClass('border-warning-highlight');
        } else {
            $(this).removeClass('border-warning-highlight');
        }
    });

    // Auto-uppercase customer code
    $('input[name="custCode"]').on('input', function() {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    });

    // Auto-capitalize names
    $('input[name="custName"], input[name="bussnessName"], input[name="custCity"]').on('blur', function() {
        const words = this.value.toLowerCase().split(' ');
        this.value = words.map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    });

    // Format phone number
    $('input[name="custContect"]').on('blur', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length === 11 && value.startsWith('03')) {
            this.value = value.substring(0, 4) + '-' + value.substring(4);
        }
    });

    // Auto-dismiss alerts
    setTimeout(() => $('.alert').fadeOut('slow'), 5000);
});
</script>

@endsection
