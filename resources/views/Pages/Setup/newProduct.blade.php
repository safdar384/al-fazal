@extends('layout.app')

@section('title', 'Add New Product')

@section('content')

<main class="app-main" id="main" tabindex="-1">
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold">
                        <i class="bi bi-box-seam-fill text-primary me-2"></i>
                        Add New Product
                    </h3>
                    <p class="text-muted small mb-0">Create a new product entry</p>
                </div>
              <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('product.index') }}">Products</a></li>
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

            <form action="{{ route('product.store') }}" method="POST" id="productForm">
                    @csrf

                      <div class="row g-3">
                    <!-- Left Column - Product Info -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h6 class="text-primary mb-3 pb-2 border-bottom">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Product Information
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-upc-scan text-primary me-1"></i>
                                        Product Code
                                        <span class="badge bg-success bg-opacity-10 text-success ms-2">
                                            <i class="bi bi-magic"></i> Auto
                                        </span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('productCode') is-invalid @enderror" 
                                           name="productCode" 
                                           value="{{ old('productCode') }}" 
                                           placeholder="Leave blank for auto-generation"
                                           autocomplete="off">
                          @error('productCode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                                    <small class="form-text text-success">
                                        <i class="bi bi-stars"></i> Will auto-generate (PRD0001, PRD0002...)
                                    </small>
                        </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-box text-primary me-1"></i>
                                        Product Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('productName') is-invalid @enderror" 
                                           name="productName" 
                                           value="{{ old('productName') }}"
                                           placeholder="Enter product name"
                                           autocomplete="off" 
                                           autofocus
                                           required>
                          @error('productName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-building text-primary me-1"></i>
                                        Company Name <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-flex gap-2">
                                        <select class="form-select @error('companyName') is-invalid @enderror" 
                                                name="companyName" 
                                                id="companySelect"
                                                required>
                                            <option value="">-- Select Company --</option>
                              @foreach($proCompanys as $company)
                                                <option value="{{ $company->companyName }}" {{ old('companyName') == $company->companyName ? 'selected' : '' }}>
                                        {{ $company->companyName }}
                                    </option>
                                @endforeach
                            </select>
                                        <button type="button" class="btn btn-outline-primary" onclick="openModal('companyModal')" title="Add Company">
                                            <i class="bi bi-plus-circle"></i>
                                        </button>
                                    </div>
                            @error('companyName')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-palette text-primary me-1"></i>
                                            Colour <span class="text-danger">*</span>
                                        </label>
                                        <div class="d-flex gap-2">
                                            <select class="form-select @error('colourName') is-invalid @enderror" 
                                                    name="colourName" 
                                                    id="colourSelect"
                                                    required>
                                                <option value="">-- Select Colour --</option>
                            @foreach($colours as $colour)
                                                    <option value="{{ $colour->colourName }}" {{ old('colourName') == $colour->colourName ? 'selected' : '' }}>
                                  {{ $colour->colourName }}
                              </option>
                            @endforeach
                          </select>
                                            <button type="button" class="btn btn-outline-primary" onclick="openModal('colourModal')" title="Add Colour">
                                                <i class="bi bi-plus-circle"></i>
                                            </button>
                                        </div>
                           @error('colourName')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-box-seam text-primary me-1"></i>
                                            Packing <span class="text-danger">*</span>
                                        </label>
                                        <div class="d-flex gap-2">
                                            <select class="form-select @error('pakingName') is-invalid @enderror" 
                                                    name="pakingName" 
                                                    id="packingSelect"
                                                    required>
                                                <option value="">-- Select Packing --</option>
                                @foreach($packings as $packing)
                                                    <option value="{{ $packing->pakingName }}" {{ old('pakingName') == $packing->pakingName ? 'selected' : '' }}>
                                        {{ $packing->pakingName }}
                                    </option>
                                @endforeach
                            </select>
                                            <button type="button" class="btn btn-outline-primary" onclick="openModal('packingModal')" title="Add Packing">
                                                <i class="bi bi-plus-circle"></i>
                                            </button>
                                        </div>
                            @error('pakingName')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        </div>
                        
                                <div class="mb-0">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-tag text-primary me-1"></i>
                                        Category <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-flex gap-2">
                                        <select class="form-select @error('catagoryName') is-invalid @enderror" 
                                                name="catagoryName" 
                                                id="categorySelect"
                                                required>
                                            <option value="">-- Select Category --</option>
                                            @foreach($catagorys as $category)
                                                <option value="{{ $category->catagoryName }}" {{ old('catagoryName') == $category->catagoryName ? 'selected' : '' }}>
                                                    {{ $category->catagoryName }}
                                </option>
                            @endforeach
                          </select>
                                        <button type="button" class="btn btn-outline-primary" onclick="openModal('categoryModal')" title="Add Category">
                                            <i class="bi bi-plus-circle"></i>
                                        </button>
                                    </div>
                          @error('catagoryName')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
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
                                                   value="{{ old('saleRate', 0) }}"
                                                   placeholder="0.00"
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
                                                   value="{{ old('costRate', 0) }}"
                                                   placeholder="0.00"
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
                                                   value="{{ old('stockQty', 0) }}"
                                                   placeholder="0"
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
                                                   value="{{ old('lessQty', 10) }}"
                                                   placeholder="10"
                                                   autocomplete="off" 
                                                   required>
                                            @error('lessQty')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <small class="form-text text-muted">
                                            <i class="bi bi-info-circle"></i>
                                            Alert when stock falls below this
                                        </small>
                </div>
              </div>

                                <!-- Profit Margin Display -->
                                <div class="alert alert-info border-0 shadow-sm mb-0">
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-graph-up-arrow fs-4 me-3"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Profit Margin Calculator</h6>
                                            <p class="mb-1" id="profitMargin">Fill in Sale Rate and Cost Rate to see profit margin</p>
                                            <small class="text-muted">Healthy margin: 20-30%</small>
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
                                    <i class="bi bi-asterisk text-danger me-1"></i>
                                    Fields marked with <span class="text-danger fw-bold">*</span> are required
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
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-save me-1"></i>
                                    Save Product
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
    border-color: #667eea;
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
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
            if (this.value.trim() !== '') {
                this.value = this.value.toUpperCase().replace(/[^A-Z0-9\-]/g, '');
            }
        });
    }

    // Calculate profit margin
    const saleRateInput = document.querySelector('input[name="saleRate"]');
    const costRateInput = document.querySelector('input[name="costRate"]');
    const profitDisplay = document.getElementById('profitMargin');

    function calculateMargin() {
        const saleRate = parseFloat(saleRateInput.value) || 0;
        const costRate = parseFloat(costRateInput.value) || 0;

        if (saleRate > 0 && costRate > 0) {
            const profit = saleRate - costRate;
            const margin = ((profit / saleRate) * 100).toFixed(2);
            const profitAmount = profit.toFixed(2);

            let color = 'success';
            let icon = 'check-circle';
            if (margin < 15) {
                color = 'danger';
                icon = 'exclamation-triangle';
            } else if (margin < 25) {
                color = 'warning';
                icon = 'info-circle';
            }

            profitDisplay.innerHTML = `
                <i class="bi bi-${icon} me-1"></i>
                <strong class="text-${color}">Profit: Rs. ${profitAmount} (${margin}%)</strong>
            `;
        }
    }

    saleRateInput.addEventListener('input', calculateMargin);
    costRateInput.addEventListener('input', calculateMargin);

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

// Modal Functions
function openModal(modalId) {
    $('#' + modalId).modal('show');
}

// Auto-reopen modals if there are validation errors
$(document).ready(function() {
    @if($errors->has('companyName'))
        $('#companyModal').modal('show');
    @elseif($errors->has('colourName'))
        $('#colourModal').modal('show');
    @elseif($errors->has('pakingName'))
        $('#packingModal').modal('show');
    @elseif($errors->has('catagoryName'))
        $('#categoryModal').modal('show');
    @endif
});
</script>

<!-- Company Modal -->
<div class="modal fade" id="companyModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-building-fill me-2"></i>
                    Add New Company
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="companyForm">
                                          @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="companyName" class="form-control" placeholder="Enter company name" autocomplete="off" required>
                        <span class="text-danger error-companyName"></span>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save
                    </button>
            </div>
            </form>
        </div>
      </div>
              </div>

<!-- Colour Modal -->
<div class="modal fade" id="colourModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-palette-fill me-2"></i>
                    Add New Colour
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('colours.store') }}" method="POST">
                @csrf
              <div class="modal-body">
                <div class="mb-3">
                        <label class="form-label fw-semibold">Colour Name <span class="text-danger">*</span></label>
                        <input type="text" name="colourName" class="form-control @error('colourName') is-invalid @enderror" placeholder="e.g., Black, White, Red" value="{{ old('colourName') }}" autocomplete="off" required>
                        @error('colourName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
              </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save
                    </button>
              </div>
            </form>
                  </div>
              </div>
            </div>

<!-- Packing Modal -->
<div class="modal fade" id="packingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-box-seam-fill me-2"></i>
                    Add New Packing
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('packing.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Packing Name <span class="text-danger">*</span></label>
                        <input type="text" name="pakingName" class="form-control @error('pakingName') is-invalid @enderror" placeholder="e.g., Box, Carton, Pack" value="{{ old('pakingName') }}" autocomplete="off" required>
                        @error('pakingName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-tag-fill me-2"></i>
                    Add New Category
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('catagory.store') }}" method="POST">
                                          @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="catagoryName" class="form-control @error('catagoryName') is-invalid @enderror" placeholder="e.g., Electronics, Appliances" value="{{ old('catagoryName') }}" autocomplete="off" required>
                        @error('catagoryName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save
                    </button>
            </div>
            </form>
        </div>
    </div>
</div>

@endsection
