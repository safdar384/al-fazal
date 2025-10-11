@extends('layout.app')

@section('title', 'Add New Customer')

@section('content')

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold">
                        <i class="bi bi-shop-window text-primary me-2"></i>
                        Add New Customer
                    </h3>
                    <p class="text-muted small mb-0">Create a new customer profile</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('Customer.index') }}">Customers</a></li>
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
                <div class="col-12">
                    <form action="{{ route('Customer.store') }}" method="POST" id="customerForm">
                        @csrf

                        <div class="row g-3">
                            <!-- Left Column -->
                            <div class="col-lg-6">
                                <!-- Basic Information -->
                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-body p-4">
                                        <h6 class="text-primary mb-3 pb-2 border-bottom">
                                            <i class="bi bi-info-circle me-2"></i>
                                            Basic Information
                                        </h6>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-geo-alt text-primary me-1"></i>
                                                Customer Area <span class="text-danger">*</span>
                                            </label>
                                            <div class="d-flex gap-2">
                                                <select class="form-select select2 @error('custArea') is-invalid @enderror" 
                                                        name="custArea" 
                                                        id="customerAreaSelect">
                                                    <option value="">-- Select Area --</option>
                                                    @foreach($custAreas as $custArea)
                                                        <option value="{{ $custArea->areaCode }}" {{ old('custArea') == $custArea->areaCode ? 'selected' : '' }}>
                                                            {{ $custArea->areaCode }} - {{ $custArea->areaName }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-outline-success" onclick="openAreaModal()" title="Add New Area">
                                                    <i class="bi bi-plus-circle"></i>
                                                </button>
                                            </div>
                                            @error('custArea')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-upc-scan text-primary me-1"></i>
                                                Customer Code
                                                <span class="badge bg-success bg-opacity-10 text-success ms-2">
                                                    <i class="bi bi-magic"></i> Auto
                                                </span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('custCode') is-invalid @enderror" 
                                                   name="custCode" 
                                                   value="{{ old('custCode') }}" 
                                                   placeholder="Leave blank for auto-generation"
                                                   autocomplete="off">
                                            @error('custCode')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-success">
                                                <i class="bi bi-stars"></i> Will auto-generate if left blank (CUST001, CUST002...)
                                            </small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-building text-primary me-1"></i>
                                                Business Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('bussnessName') is-invalid @enderror" 
                                                   name="bussnessName" 
                                                   value="{{ old('bussnessName') }}"
                                                   placeholder="Enter business name"
                                                   autocomplete="off" 
                                                   required>
                                            @error('bussnessName')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-person text-primary me-1"></i>
                                                Customer Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('custName') is-invalid @enderror" 
                                                   name="custName" 
                                                   value="{{ old('custName') }}"
                                                   placeholder="Enter customer name"
                                                   autocomplete="off" 
                                                   required>
                                            @error('custName')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-map text-primary me-1"></i>
                                                Customer Address <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control @error('custAddress') is-invalid @enderror" 
                                                      name="custAddress" 
                                                      rows="2"
                                                      placeholder="Enter complete address"
                                                      autocomplete="off" 
                                                      required>{{ old('custAddress') }}</textarea>
                                            @error('custAddress')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-building-check text-primary me-1"></i>
                                                    City <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" 
                                                       class="form-control @error('custCity') is-invalid @enderror" 
                                                       name="custCity" 
                                                       value="{{ old('custCity') }}"
                                                       placeholder="Enter city"
                                                       autocomplete="off" 
                                                       required>
                                                @error('custCity')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-telephone text-primary me-1"></i>
                                                    Contact <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light">
                                                        <i class="bi bi-telephone-fill text-primary"></i>
                                                    </span>
                                                    <input type="text" 
                                                           class="form-control @error('custContect') is-invalid @enderror" 
                                                           name="custContect" 
                                                           value="{{ old('custContect') }}"
                                                           placeholder="03XX-XXXXXXX"
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
                                                <i class="bi bi-book text-primary me-1"></i>
                                                Customer Page <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('custPage') is-invalid @enderror" 
                                                   name="custPage" 
                                                   value="{{ old('custPage') }}"
                                                   placeholder="Enter page reference"
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
                                                       value="{{ old('custdiscount', 0) }}"
                                                       placeholder="0.00"
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
                                                       value="{{ old('custPayTermDay', 0) }}"
                                                       placeholder="e.g., 30"
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
                                                       value="{{ old('custCreditLimit', 0) }}"
                                                       placeholder="0.00"
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
                                                   value="{{ old('custOppDate', now()->format('Y-m-d')) }}"
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
                                                <option value="Retail" {{ old('custCatagory') == 'Retail' ? 'selected' : '' }}>Retail Customer</option>
                                                <option value="Wholesale" {{ old('custCatagory') == 'Wholesale' ? 'selected' : '' }}>Wholesale Customer</option>
                                                <option value="VIP" {{ old('custCatagory') == 'VIP' ? 'selected' : '' }}>VIP Customer</option>
                                                <option value="Supplier" {{ old('custCatagory') == 'Supplier' ? 'selected' : '' }}>Supplier</option>
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
                                <!-- Quick Tips -->
                                <div class="alert alert-info border-0 shadow-sm mb-3">
                                    <h6 class="alert-heading">
                                        <i class="bi bi-lightbulb-fill me-2"></i>
                                        Quick Tips
                                    </h6>
                                    <ul class="mb-0 small ps-3">
                                        <li><strong>Customer Code:</strong> Leave blank for auto-generation (CUST001, CUST002...)</li>
                                        <li><strong>Area:</strong> Select existing or add new area</li>
                                        <li><strong>Payment Terms:</strong> Number of days for payment</li>
                                        <li><strong>Credit Limit:</strong> Maximum credit allowed</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="card border-0 shadow-sm mt-3">
                            <div class="card-footer bg-light border-0 py-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="bi bi-asterisk text-danger me-1"></i>
                                            Fields marked with <span class="text-danger fw-bold">*</span> are required
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
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="bi bi-save me-1"></i>
                                            Save Customer
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

<!-- Area Modal -->
<div class="modal fade" id="areaModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-geo-alt-fill me-2"></i>
                    Add New Area
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="areaForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Area Code</label>
                        <input type="text" name="areaCode" class="form-control" autocomplete="off" required>
                        <span class="text-danger error-areaCode"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Area Name</label>
                        <input type="text" name="areaName" class="form-control" autocomplete="off" required>
                        <span class="text-danger error-areaName"></span>  
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Agent Name</label>
                        <input type="text" name="agentName" class="form-control" autocomplete="off">
                        <span class="text-danger error-agentName"></span>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i>
                        Save Area
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
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
    border-color: #667eea;
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
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

.select2-container--default .select2-selection--single {
    height: 38px !important;
    border-color: #dee2e6;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
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
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Auto-uppercase customer code
    $('input[name="custCode"]').on('input', function() {
        if (this.value.trim() !== '') {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        }
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

// Area Modal Functions
function openAreaModal() {
    $('#areaModal').modal('show');
}

$('#areaForm').on('submit', function(e) {
    e.preventDefault();
    
    $('.error-areaCode, .error-areaName, .error-agentName').text('');
    
    $.ajax({
        url: '{{ route("area.store") }}',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Area added successfully!',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                $('#customerAreaSelect').append(
                    $('<option>', {
                        value: response.area.areaCode,
                        text: response.area.areaCode + ' - ' + response.area.areaName,
                        selected: true
                    })
                );
                
                $('#areaModal').modal('hide');
                $('#areaForm')[0].reset();
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                if (errors.areaCode) $('.error-areaCode').text(errors.areaCode[0]);
                if (errors.areaName) $('.error-areaName').text(errors.areaName[0]);
                if (errors.agentName) $('.error-agentName').text(errors.agentName[0]);
            }
        }
    });
});

$('#areaModal').on('hidden.bs.modal', function() {
    $('#areaForm')[0].reset();
    $('.error-areaCode, .error-areaName, .error-agentName').text('');
});
</script>

@endsection
