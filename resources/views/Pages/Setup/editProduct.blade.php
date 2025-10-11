@extends('layout.app')

@section('title', 'Edit Product')

@section('content')

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold">
                        <i class="bi bi-pencil-square text-warning me-2"></i>
                        Edit Product
                    </h3>
                    <p class="text-muted small mb-0">Update product information</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('product.index') }}">Products</a></li>
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

            <!-- Product Summary Header -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body bg-gradient-warning text-white py-3">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <div class="d-flex align-items-center">
                                <div class="bg-white bg-opacity-25 p-3 rounded-circle me-3">
                                    <i class="bi bi-box-seam fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">{{ $product->productName }}</h5>
                                    <small class="opacity-75">
                                        <i class="bi bi-upc-scan me-1"></i>{{ $product->productCode }} | 
                                        <i class="bi bi-building me-1"></i>{{ $product->companyName }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 text-md-end mt-3 mt-md-0">
                            <div class="row g-2">
                                <div class="col-4 text-center">
                                    <small class="d-block opacity-75">Stock</small>
                                    <h5 class="mb-0 fw-bold">{{ $product->stockQty }}</h5>
                                </div>
                                <div class="col-4 text-center">
                                    <small class="d-block opacity-75">Sale Rate</small>
                                    <h5 class="mb-0 fw-bold">{{ number_format($product->saleRate, 0) }}</h5>
                                </div>
                                <div class="col-4 text-center">
                                    <small class="d-block opacity-75">Value</small>
                                    <h5 class="mb-0 fw-bold">{{ number_format($product->saleRate * $product->stockQty, 0) }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('product.update', $product->id) }}" method="POST" id="editProductForm">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <!-- Left Column - Product Info -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h6 class="text-warning mb-3 pb-2 border-bottom">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Product Information
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-upc-scan text-warning me-1"></i>
                                        Product Code <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('productCode') is-invalid @enderror" 
                                           name="productCode" 
                                           value="{{ old('productCode', $product->productCode) }}" 
                                           autocomplete="off"
                                           required>
                                    @error('productCode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-box text-warning me-1"></i>
                                        Product Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('productName') is-invalid @enderror" 
                                           name="productName" 
                                           value="{{ old('productName', $product->productName) }}"
                                           autocomplete="off" 
                                           required>
                                    @error('productName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-building text-warning me-1"></i>
                                        Company Name <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('companyName') is-invalid @enderror" 
                                            name="companyName" 
                                            required>
                                        <option value="">-- Select Company --</option>
                                        @foreach($proCompanys as $company)
                                            <option value="{{ $company->companyName }}" {{ old('companyName', $product->companyName) == $company->companyName ? 'selected' : '' }}>
                                                {{ $company->companyName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('companyName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-palette text-warning me-1"></i>
                                            Colour <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('colourName') is-invalid @enderror" 
                                                name="colourName" 
                                                required>
                                            <option value="">-- Select Colour --</option>
                                            @foreach($colours as $colour)
                                                <option value="{{ $colour->colourName }}" {{ old('colourName', $product->colourName) == $colour->colourName ? 'selected' : '' }}>
                                                    {{ $colour->colourName }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('colourName')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-box-seam text-warning me-1"></i>
                                            Packing <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('pakingName') is-invalid @enderror" 
                                                name="pakingName" 
                                                required>
                                            <option value="">-- Select Packing --</option>
                                            @foreach($packings as $packing)
                                                <option value="{{ $packing->pakingName }}" {{ old('pakingName', $product->pakingName) == $packing->pakingName ? 'selected' : '' }}>
                                                    {{ $packing->pakingName }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('pakingName')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-tag text-warning me-1"></i>
                                        Category <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('catagoryName') is-invalid @enderror" 
                                            name="catagoryName" 
                                            required>
                                        <option value="">-- Select Category --</option>
                                        @foreach($catagorys as $category)
                                            <option value="{{ $category->catagoryName }}" {{ old('catagoryName', $product->catagoryName) == $category->catagoryName ? 'selected' : '' }}>
                                                {{ $category->catagoryName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('catagoryName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Pricing & Stock -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h6 class="text-success mb-3 pb-2 border-bottom">
                                    <i class="bi bi-cash-stack me-2"></i>
                                    Pricing & Stock Information
                                </h6>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-currency-dollar text-success me-1"></i>
                                            Sale Rate <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-success bg-opacity-10 text-success fw-bold">Rs.</span>
                                            <input type="number" 
                                                   step="0.01" 
                                                   class="form-control @error('saleRate') is-invalid @enderror" 
                                                   name="saleRate" 
                                                   value="{{ old('saleRate', $product->saleRate) }}"
                                                   autocomplete="off" 
                                                   required>
                                            @error('saleRate')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-cart text-warning me-1"></i>
                                            Cost Rate <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-warning bg-opacity-10 text-warning fw-bold">Rs.</span>
                                            <input type="number" 
                                                   step="0.01" 
                                                   class="form-control @error('costRate') is-invalid @enderror" 
                                                   name="costRate" 
                                                   value="{{ old('costRate', $product->costRate) }}"
                                                   autocomplete="off" 
                                                   required>
                                            @error('costRate')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-boxes text-info me-1"></i>
                                            Stock Quantity <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="bi bi-stack text-info"></i>
                                            </span>
                                            <input type="number" 
                                                   class="form-control @error('stockQty') is-invalid @enderror" 
                                                   name="stockQty" 
                                                   value="{{ old('stockQty', $product->stockQty) }}"
                                                   autocomplete="off" 
                                                   required>
                                            @error('stockQty')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-exclamation-triangle text-danger me-1"></i>
                                            Minimum Quantity <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-danger bg-opacity-10 text-danger">
                                                <i class="bi bi-alarm"></i>
                                            </span>
                                            <input type="number" 
                                                   class="form-control @error('lessQty') is-invalid @enderror" 
                                                   name="lessQty" 
                                                   value="{{ old('lessQty', $product->lessQty) }}"
                                                   autocomplete="off" 
                                                   required>
                                            @error('lessQty')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Current Stats Cards -->
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="card border-0 bg-success bg-opacity-10">
                                            <div class="card-body p-3">
                                                <small class="text-muted d-block">Current Profit/Unit</small>
                                                <h5 class="mb-0 text-success fw-bold">Rs. {{ number_format($product->saleRate - $product->costRate, 2) }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-0 bg-info bg-opacity-10">
                                            <div class="card-body p-3">
                                                <small class="text-muted d-block">Stock Status</small>
                                                @if($product->stockQty <= $product->lessQty)
                                                    <h6 class="mb-0 text-danger fw-bold">
                                                        <i class="bi bi-exclamation-triangle"></i> Low Stock
                                                    </h6>
                                                @else
                                                    <h6 class="mb-0 text-success fw-bold">
                                                        <i class="bi bi-check-circle"></i> Healthy
                                                    </h6>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
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
                                <a href="{{ route('product.index') }}" class="btn btn-light me-2">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Back to List
                                </a>
                                <button type="reset" class="btn btn-outline-secondary me-2">
                                    <i class="bi bi-arrow-clockwise me-1"></i>
                                    Reset
                                </button>
                                <button type="submit" class="btn btn-warning text-white px-4">
                                    <i class="bi bi-save me-1"></i>
                                    Update Product
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<style>
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

.form-control:focus, .form-select:focus {
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
    // Auto-uppercase product code
    const productCodeInput = document.querySelector('input[name="productCode"]');
    if (productCodeInput) {
        productCodeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9\-]/g, '');
        });
    }

    // Highlight changed fields
    const form = document.getElementById('editProductForm');
    const originalValues = {};
    
    form.querySelectorAll('input, select').forEach(input => {
        originalValues[input.name] = input.value;
        
        input.addEventListener('input change', function() {
            if (this.value !== originalValues[this.name]) {
                this.classList.add('border-warning', 'border-2');
            } else {
                this.classList.remove('border-warning', 'border-2');
            }
        });
    });

    // Auto-dismiss alerts
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert-success, .alert-danger');
        alerts.forEach(alert => {
            if (bootstrap.Alert) {
                new bootstrap.Alert(alert).close();
            }
        });
    }, 5000);
});
</script>

@endsection
