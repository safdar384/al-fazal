@extends('layout.app')

@section('title', 'Add New Product')

@section('content')

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center gy-2">
                <div class="col-12 col-md-6">
                    <h3 class="mb-0 fw-bold" style="color:#0f2747;">
                        <i class="bi bi-box-seam-fill me-2"></i>Add New Product
                    </h3>
                    <p class="text-muted small mb-0">Stock Management · Create product entry</p>
                </div>
                <div class="col-12 col-md-6">
                    <ol class="breadcrumb float-md-end mb-0">
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

            <form action="{{ route('product.store') }}" method="POST" id="productForm" class="np-form" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="card np-card border-0">
                            <div class="card-header np-card-head">
                                <h6 class="mb-0 text-white fw-semibold">
                                    <i class="bi bi-info-circle me-2"></i>Product Information
                                </h6>
                            </div>
                            <div class="card-body p-3">

                                <div class="mb-2">
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
                                           id="productCodeInput"
                                           value="{{ old('productCode') }}" 
                                           placeholder="Leave blank for auto-generation"
                                           autocomplete="off"
                                           autofocus>
                          @error('productCode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                                    <small class="form-text text-success">
                                        <i class="bi bi-stars"></i> Will auto-generate (PRD0001, PRD0002...) — saved as UPPERCASE
                                    </small>
                        </div>

                        

                                <div class="mb-2">
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
                                           required>
                          @error('productName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>

                                <div class="mb-2">
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

                                <div class="mb-2">
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

                                <div class="mb-2">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-box-seam text-primary me-1"></i>
                                        Packing <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-flex gap-2">
                                        <select class="form-select @error('pakingName') is-invalid @enderror"
                                                id="packingSelect"
                                                required>
                                            <option value="">-- Select Packing --</option>
                                            @foreach($packings as $packing)
                                                <option value="{{ $packing->id }}"
                                                        data-name="{{ $packing->pakingName }}"
                                                        data-unit="{{ $packing->baseUnit ?: 'PIECE' }}"
                                                        data-qty="{{ $packing->qtyInPack ?: 1 }}"
                                                        @selected(
                                                            strcasecmp((string) old('pakingName'), (string) $packing->pakingName) === 0
                                                            && strcasecmp((string) old('unitPerPackName', 'Piece'), (string) ($packing->baseUnit ?: 'Piece')) === 0
                                                            && (int) old('piecesPerUnit', 1) === (int) ($packing->qtyInPack ?: 1)
                                                        )>
                                                    {{ $packing->displayLabel() }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-primary" onclick="openModal('packingModal')" title="Add Packing">
                                            <i class="bi bi-plus-circle"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" name="pakingName" id="pakingNameHidden" value="{{ old('pakingName') }}">
                                    <input type="hidden" name="unitPerPackName" id="unitPerPackNameHidden" value="{{ old('unitPerPackName', 'Piece') }}">
                                    <input type="hidden" name="piecesPerUnit" id="piecesPerUnitHidden" value="{{ old('piecesPerUnit', 1) }}">
                                    @error('pakingName')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-2">
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

                                <div class="mb-2">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-image text-primary me-1"></i>
                                        Product Image
                                    </label>
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="product-image-preview border rounded bg-light d-flex align-items-center justify-content-center overflow-hidden" style="width:72px;height:72px;flex-shrink:0;">
                                            <img id="productImagePreview" src="" alt="Preview" class="d-none w-100 h-100" style="object-fit:cover;">
                                            <i id="productImagePlaceholder" class="bi bi-image text-muted fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="file"
                                                   class="form-control form-control-sm @error('image') is-invalid @enderror"
                                                   name="image"
                                                   id="productImageInput"
                                                   accept="image/jpeg,image/jpg,image/png,image/webp">
                                            @error('image')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted d-block mt-1">JPG, PNG or WEBP. Max 5MB (auto-optimized to WebP ≤500px).</small>
                                            <button type="button" class="btn btn-sm btn-outline-danger mt-1 d-none" id="btnClearProductImage">
                                                <i class="bi bi-x-circle me-1"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                      </div>
                    </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card np-card border-0">
                            <div class="card-header np-card-head">
                                <h6 class="mb-0 text-white fw-semibold">
                                    <i class="bi bi-cash-stack me-2"></i>Pricing & Stock
                                </h6>
                            </div>
                            <div class="card-body p-3">

                                <div class="row g-2">
                                    <div class="col-md-6 mb-2">
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

                                    <div class="col-md-6 mb-2">
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

                                <div class="row g-2">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-semibold">
                                            <i class="bi bi-boxes text-info me-1"></i>
                                            Stock Quantity <span class="text-danger">*</span>
                                            <span class="badge bg-info bg-opacity-10 text-info ms-2 d-none" id="stockAutoBadge">
                                                <i class="bi bi-calculator"></i> Auto
                                            </span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="bi bi-stack text-info"></i>
                                            </span>
                                            <input type="number" 
                                                   class="form-control @error('stockQty') is-invalid @enderror" 
                                                   id="stockQtyField"
                                                   name="stockQty" 
                                                   value="{{ old('stockQty', 0) }}"
                                                   placeholder="0"
                                                   autocomplete="off" 
                                                   required>
                                            @error('stockQty')
                      <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                                        <small class="form-text text-muted d-none" id="stockAutoHint">
                                            <i class="bi bi-stars"></i> Calculated from variant stocks
                                        </small>
                                        <small class="form-text text-muted" id="stockManualHint">
                                            <i class="bi bi-info-circle"></i> Enter stock quantity or add variants below
                                        </small>
              </div>

                <div class="col-md-6 mb-2">
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

                                <div class="np-profit-box mb-0">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-graph-up-arrow fs-5" style="color:#0f2747;"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold small mb-1" style="color:#0f2747;">Profit Margin</div>
                                            <p class="mb-1 small" id="profitMargin">Fill in Sale Rate and Cost Rate to see profit margin</p>
                                            <small class="text-muted">Healthy margin: 20–30%</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card np-card border-0">
                            <div class="card-header np-card-head d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-white fw-semibold">
                                    <i class="bi bi-diagram-3 me-2"></i>Product Variants (Optional)
                                </h6>
                                <button type="button" class="btn btn-sm btn-light fw-semibold" id="btnAddVariant">
                                    <i class="bi bi-plus-circle me-1"></i>Add Variant
                                </button>
                            </div>
                            <div class="card-body p-3">
                                <div id="variantsContainer">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0 np-variants-table" id="variantsTable">
                                            <thead>
                                                <tr>
                                                    <th style="width: 16%">Variant Name</th>
                                                    <th style="width: 14%">Value</th>
                                                    <th style="width: 12%">Add. Cost</th>
                                                    <th style="width: 12%">Add. Price</th>
                                                    <th style="width: 10%">Stock</th>
                                                    <th style="width: 26%">Image</th>
                                                    <th style="width: 10%" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="variantRows"></tbody>
                                        </table>
                                    </div>
                                    <p class="text-muted small mb-0 mt-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Add variants like Size, Weight, Color variations, etc.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card np-card border-0">
                            <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <small class="text-muted">
                                    <span class="text-danger fw-bold">*</span> Required fields
                                </small>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('product.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-arrow-left me-1"></i>Back to List
                                    </a>
                                    <button type="reset" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                    </button>
                                    <button type="submit" class="btn btn-sm text-white px-4" style="background:#0f2747;border-color:#0f2747;">
                                        <i class="bi bi-save me-1"></i>Save Product
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
    .np-card {
        box-shadow: 0 2px 12px rgba(15, 39, 71, 0.08);
        border-radius: 4px;
        overflow: hidden;
        border: 1px solid #d5dde8;
    }
    .np-card-head {
        background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
        border: none;
        padding: 0.75rem 1rem;
    }
    .np-form .form-label {
        font-size: 0.78rem;
        margin-bottom: 0.25rem;
        color: #0f2747;
    }
    .np-form .form-control,
    .np-form .form-select {
        font-size: 0.85rem;
        border-color: #d5dde8;
    }
    .np-form .form-control:focus,
    .np-form .form-select:focus {
        border-color: #1a4a8a;
        box-shadow: 0 0 0 0.2rem rgba(15, 39, 71, 0.12);
    }
    .np-form .btn-outline-primary {
        color: #0f2747;
        border-color: #0f2747;
    }
    .np-form .btn-outline-primary:hover {
        background: #0f2747;
        border-color: #0f2747;
        color: #fff;
    }
    .np-profit-box {
        background: #f4f7fb;
        border: 1px solid #d5dde8;
        border-left: 4px solid #0f2747;
        border-radius: 4px;
        padding: 0.75rem 1rem;
    }
    .np-variants-table thead th {
        background: #0f2747;
        color: #fff;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        font-weight: 700;
        border-color: #0a1c33;
        white-space: nowrap;
    }
    .np-variants-table tbody td {
        vertical-align: middle;
        font-size: 0.82rem;
    }
    .modal-header.np-modal-head {
        background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
        color: #fff;
    }
</style>
@endpush

<script>
document.addEventListener('DOMContentLoaded', function() {
    function toProperCase(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .replace(/\b([a-z])/g, function(m, c) { return c.toUpperCase(); });
    }

    function focusProductCode() {
        const $code = $('#productCodeInput');
        if (!$code.length) return;
        $code.trigger('focus');
        const el = $code.get(0);
        if (el && typeof el.select === 'function') {
            try { el.select(); } catch (e) {}
        }
    }

    // Auto-uppercase product code
    const productCodeInput = document.querySelector('input[name="productCode"]');
    if (productCodeInput) {
        productCodeInput.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.value = this.value.toUpperCase().replace(/[^A-Z0-9\-]/g, '');
            }
        });
    }

    // Proper Case for product name (and typed variant fields)
    $(document).on('blur', 'input[name="productName"], #variantRows input[name*="[name]"], #variantRows input[name*="[value]"]', function() {
        if (this.value.trim() !== '') {
            this.value = toProperCase(this.value);
        }
    });

    // Avoid duplicate companyName fields (legacy hidden)
    $('#companyNameHidden').remove();
    function syncCompany() { /* company select posts its own name */ }

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
        } else if (profitDisplay) {
            profitDisplay.innerHTML = '';
        }
    }

    saleRateInput.addEventListener('input', calculateMargin);
    costRateInput.addEventListener('input', calculateMargin);

    function clearProductFieldErrors() {
        $('#productForm .is-invalid').removeClass('is-invalid');
        $('#productForm .np-ajax-error').remove();
    }

    function showProductFieldErrors(errors) {
        Object.keys(errors || {}).forEach(function(field) {
            let $input = $('#productForm [name="' + field + '"]');
            if (!$input.length && field.indexOf('.') !== -1) {
                // variants.0.name → variants[0][name]
                const parts = field.split('.');
                if (parts[0] === 'variants' && parts.length >= 3) {
                    $input = $('#productForm [name="variants[' + parts[1] + '][' + parts[2] + ']"]');
                }
            }
            if (!$input.length) return;
            $input.addClass('is-invalid');
            const msg = $('<div class="invalid-feedback d-block np-ajax-error"></div>').text(errors[field][0]);
            $input.after(msg);
        });
    }

    function resetProductForm() {
        const form = document.getElementById('productForm');
        if (form) form.reset();
        clearProductFieldErrors();
        $('#variantRows').empty();
        window.variantCounter = 0;
        $('#productImagePreview').addClass('d-none').attr('src', '');
        $('#productImagePlaceholder').removeClass('d-none');
        $('#btnClearProductImage').addClass('d-none');
        if (profitDisplay) profitDisplay.innerHTML = '';
        $('#stockQtyField').val(0).prop('readonly', false).removeClass('bg-light');
        $('#stockAutoBadge').addClass('d-none');
        $('#stockAutoHint').addClass('d-none');
        $('#stockManualHint').removeClass('d-none');
        syncCompany();
        setTimeout(focusProductCode, 50);
    }

    // AJAX save — stay on page, message, focus Product Code
    $('#productForm').on('submit', function(e) {
        e.preventDefault();
        clearProductFieldErrors();
        syncCompany();
        syncPackingFields();

        const $form = $(this);
        const $btn = $form.find('button[type="submit"]').prop('disabled', true);
        const originalHtml = $btn.html();
        $btn.html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');

        const formData = new FormData(this);

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function(res) {
            resetProductForm();
            const message = (res && res.message) ? res.message : 'Product created successfully!';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: message,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#0f2747',
                    allowOutsideClick: false,
                    allowEscapeKey: true,
                    didOpen: function() {
                        const btn = Swal.getConfirmButton();
                        if (btn) btn.focus();
                    }
                }).then(function() {
                    focusProductCode();
                });
            } else {
                alert(message);
                focusProductCode();
            }
        }).fail(function(xhr) {
            const errors = xhr.responseJSON && xhr.responseJSON.errors;
            if (errors) {
                showProductFieldErrors(errors);
                const first = Object.values(errors)[0];
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation',
                        text: (first && first[0]) || 'Please fix the errors.',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0f2747'
                    });
                }
                return;
            }
            const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Could not save product.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: msg,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#0f2747'
                });
            } else {
                alert(msg);
            }
        }).always(function() {
            $btn.prop('disabled', false).html(originalHtml);
        });
    });

    focusProductCode();

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
    @elseif($errors->has('catagoryName'))
        $('#categoryModal').modal('show');
    @endif
});
</script>

<!-- Company Modal -->
<div class="modal fade" id="companyModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header np-modal-head">
                <h5 class="modal-title"><i class="bi bi-building-fill me-2"></i>Manage Companies</h5>
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
                    @include('partials.product-lookup-list', [
                        'listId' => 'companyLookupList',
                        'items' => $proCompanys ?? [],
                        'usedNames' => $usedCompanyNames ?? [],
                        'nameField' => 'companyName',
                        'selectId' => '#companySelect',
                        'destroyUrlPrefix' => url('company'),
                    ])
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Close</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Colour Modal -->
<div class="modal fade" id="colourModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header np-modal-head">
                <h5 class="modal-title"><i class="bi bi-palette-fill me-2"></i>Manage Colours</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="colourForm" action="{{ route('colours.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Colour Name <span class="text-danger">*</span></label>
                        <input type="text" name="colourName" class="form-control" placeholder="e.g., Black, White, Red" autocomplete="off" required>
                    </div>
                    @include('partials.product-lookup-list', [
                        'listId' => 'colourLookupList',
                        'items' => $colours ?? [],
                        'usedNames' => $usedColourNames ?? [],
                        'nameField' => 'colourName',
                        'selectId' => '#colourSelect',
                        'destroyUrlPrefix' => url('colours'),
                    ])
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Close</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Packing Modal -->
<div class="modal fade" id="packingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header np-modal-head">
                <h5 class="modal-title"><i class="bi bi-box-seam-fill me-2"></i>Manage Packing</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="packingForm" action="{{ route('packing.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Packing Name <span class="text-danger">*</span></label>
                        <input type="text" name="pakingName" class="form-control" placeholder="e.g., Box, Bag, Pakit" autocomplete="off" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Base Unit <span class="text-danger">*</span></label>
                        <select name="baseUnit" class="form-select" required>
                            <option value="">-- Select Base Unit --</option>
                            @foreach(\App\Models\Packing::BASE_UNITS as $unit)
                                <option value="{{ $unit }}">{{ $unit }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Sale Qty / Rate is unit pe based (Piece, Kg, Gurs…).</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quantity in One Pack <span class="text-danger">*</span></label>
                        <input type="number" name="qtyInPack" class="form-control" placeholder="e.g., 12, 25, 50" min="1" step="1" autocomplete="off" required>
                        <small class="text-muted">Example: Box + Piece + 12 → 1 box = 12 pieces.</small>
                    </div>
                    @include('partials.product-lookup-list', [
                        'listId' => 'packingLookupList',
                        'items' => $packings ?? [],
                        'usedNames' => $usedPackingNames ?? [],
                        'nameField' => 'pakingName',
                        'selectId' => '#packingSelect',
                        'destroyUrlPrefix' => url('packing'),
                        'packingLabel' => true,
                    ])
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Close</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header np-modal-head">
                <h5 class="modal-title"><i class="bi bi-tag-fill me-2"></i>Manage Categories</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm" action="{{ route('catagory.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="catagoryName" class="form-control" placeholder="e.g., Electronics, Appliances" autocomplete="off" required>
                    </div>
                    @include('partials.product-lookup-list', [
                        'listId' => 'categoryLookupList',
                        'items' => $catagorys ?? [],
                        'usedNames' => $usedCatagoryNames ?? [],
                        'nameField' => 'catagoryName',
                        'selectId' => '#categorySelect',
                        'destroyUrlPrefix' => url('catagory'),
                    ])
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Close</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// AJAX modal saves without page reload and update selects
$(function(){
    function notify(msg, type='success'){
        const el=$('<div class="position-fixed top-0 end-0 p-3" style="z-index:2000;"><div class="toast align-items-center text-bg-'+type+' border-0 show" role="alert"><div class="d-flex"><div class="toast-body">'+msg+'</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div></div>');
        $('body').append(el); setTimeout(()=>el.remove(), 2000);
    }

    function appendLookupListItem(listId, selectId, id, name, label, destroyUrl) {
        const $list = $('#' + listId);
        $list.find('.product-lookup-empty').remove();
        const row = `
            <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 product-lookup-item" data-id="${id}" data-name="${name}">
                <span>${label || name}</span>
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-product-lookup"
                        title="Remove" data-id="${id}" data-name="${name}" data-url="${destroyUrl}" data-select="${selectId}">
                    <i class="bi bi-trash"></i>
                </button>
            </div>`;
        $list.append(row);
    }

    function selectOrAppendOption($select, name, label, extraAttrs) {
        const exists = $select.find('option').filter(function() {
            return ($(this).val() || '').toLowerCase() === String(name).toLowerCase();
        }).length > 0;
        if (!exists) {
            const opt = new Option(label || name, name, true, true);
            if (extraAttrs) {
                Object.keys(extraAttrs).forEach(k => $(opt).attr(k, extraAttrs[k]));
            }
            $select.append(opt);
        }
        $select.val(name).trigger('change');
    }

    // Company create via AJAX
    $('#companyForm').on('submit', function(e){ e.preventDefault(); const $f=$(this);
        $.post({url:'{{ route('company.store') }}', data:$f.serialize(), dataType:'json'})
            .done(function(res){
                if(res.success){
                    const name = res.data.companyName;
                    selectOrAppendOption($('#companySelect'), name, name);
                    $('#companyNameHidden').val(name);
                    appendLookupListItem('companyLookupList', '#companySelect', res.data.id, name, name, '{{ url('company') }}/' + res.data.id);
                    $f[0].reset();
                    notify('Company added');
                }
            })
            .fail(function(xhr){ notify(xhr.responseJSON?.message || 'Failed to add company','danger'); });
    });

    // Colour create via AJAX
    $('#colourForm').on('submit', function(e){ e.preventDefault(); const $f=$(this);
        $.post({url:$f.attr('action'), data:$f.serialize(), dataType:'json'})
            .done(function(res){
                if(res.success){
                    const name = res.data.colourName;
                    selectOrAppendOption($('#colourSelect'), name, name);
                    appendLookupListItem('colourLookupList', '#colourSelect', res.data.id, name, name, '{{ url('colours') }}/' + res.data.id);
                    $f[0].reset();
                    notify('Colour added');
                }
            })
            .fail(function(xhr){ notify(xhr.responseJSON?.message || 'Failed to add colour','danger'); });
    });

    window.syncPackingFields = function () {
        const $opt = $('#packingSelect option:selected');
        $('#pakingNameHidden').val($opt.data('name') || '');
        $('#unitPerPackNameHidden').val($opt.data('unit') || 'Piece');
        $('#piecesPerUnitHidden').val($opt.data('qty') || 1);
    };
    $('#packingSelect').on('change', window.syncPackingFields);
    window.syncPackingFields();

    // Packing create via AJAX
    $('#packingForm').on('submit', function(e){ e.preventDefault(); const $f=$(this);
        $.post({url:$f.attr('action'), data:$f.serialize(), dataType:'json'})
            .done(function(res){
                if(res.success){
                    const d = res.data;
                    const label = d.label || (d.pakingName + ' · ' + d.qtyInPack + ' ' + d.baseUnit);
                    const opt = new Option(label, d.id, true, true);
                    $(opt).attr({
                        'data-name': d.pakingName,
                        'data-unit': d.baseUnit,
                        'data-qty': d.qtyInPack
                    });
                    $('#packingSelect').append(opt).val(String(d.id)).trigger('change');
                    appendLookupListItem('packingLookupList', '#packingSelect', d.id, d.pakingName, label, '{{ url('packing') }}/' + d.id);
                    $f[0].reset();
                    notify('Packing added');
                }
            })
            .fail(function(xhr){ notify(xhr.responseJSON?.message || 'Failed to add packing','danger'); });
    });

    // Category create via AJAX
    $('#categoryForm').on('submit', function(e){ e.preventDefault(); const $f=$(this);
        $.post({url:$f.attr('action'), data:$f.serialize(), dataType:'json'})
            .done(function(res){
                if(res.success){
                    const name = res.data.catagoryName;
                    selectOrAppendOption($('#categorySelect'), name, name);
                    appendLookupListItem('categoryLookupList', '#categorySelect', res.data.id, name, name, '{{ url('catagory') }}/' + res.data.id);
                    $f[0].reset();
                    notify('Category added');
                }
            })
            .fail(function(xhr){ notify(xhr.responseJSON?.message || 'Failed to add category','danger'); });
    });

    // Safe delete for Company / Colour / Packing / Unit / Category
    $(document).on('click', '.btn-delete-product-lookup', function() {
        const $btn = $(this);
        const url = $btn.data('url');
        const name = $btn.data('name');
        const selectId = $btn.data('select');

        const doDelete = function() {
            const $list = $btn.closest('.product-lookup-list');
            $.ajax({
                url: url,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                dataType: 'json'
            }).done(function(res){
                if (!res.success) return;
                $btn.closest('.product-lookup-item').remove();
                const $select = $(selectId);
                $select.find('option').filter(function() {
                    return ($(this).val() || '').toLowerCase() === String(name).toLowerCase();
                }).remove();
                if (($select.val() || '').toLowerCase() === String(name).toLowerCase()) {
                    $select.val('').trigger('change');
                }
                if ($list.length && $list.find('.product-lookup-item').length === 0) {
                    $list.html('<div class="text-muted small product-lookup-empty">No items yet.</div>');
                }
                notify(res.message || 'Deleted');
            }).fail(function(xhr){
                notify(xhr.responseJSON?.message || 'Cannot delete item', 'danger');
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Remove item?',
                text: 'Delete "' + name + '"?',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545'
            }).then(function(result){ if (result.isConfirmed) doDelete(); });
        } else if (confirm('Delete "' + name + '"?')) {
            doDelete();
        }
    });

    // ---- Product Variants Management ----
    window.variantCounter = 0;

    // Toggle stock field based on variants
    function toggleStockField() {
        const hasVariants = $('#variantRows tr').length > 0;
        
        if (hasVariants) {
            // Has variants: make readonly and auto-calculate
            $('#stockQtyField').prop('readonly', true).addClass('bg-light');
            $('#stockAutoBadge').removeClass('d-none');
            $('#stockAutoHint').removeClass('d-none');
            $('#stockManualHint').addClass('d-none');
            calculateTotalStock();
        } else {
            // No variants: make editable
            $('#stockQtyField').prop('readonly', false).removeClass('bg-light');
            $('#stockAutoBadge').addClass('d-none');
            $('#stockAutoHint').addClass('d-none');
            $('#stockManualHint').removeClass('d-none');
        }
    }

    // Calculate total stock from variants
    function calculateTotalStock() {
        let total = 0;
        $('#variantRows input[name*="[stockQty]"]').each(function() {
            const qty = parseInt($(this).val()) || 0;
            total += qty;
        });
        $('#stockQtyField').val(total);
    }

    // Add variant row
    $('#btnAddVariant').on('click', function() {
        window.variantCounter++;
        const i = window.variantCounter;
        const row = `
            <tr data-variant-id="${i}">
                <td>
                    <input type="text" class="form-control form-control-sm" name="variants[${i}][name]" placeholder="e.g., Size, Weight" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="variants[${i}][value]" placeholder="e.g., Small, 1kg" required>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="variants[${i}][additionalCost]" placeholder="0.00" value="0">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="variants[${i}][additionalPrice]" placeholder="0.00" value="0">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm variant-stock" name="variants[${i}][stockQty]" placeholder="0" value="0">
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="variant-image-preview border rounded bg-light d-flex align-items-center justify-content-center overflow-hidden" style="width:40px;height:40px;flex-shrink:0;">
                            <img src="" alt="" class="d-none w-100 h-100" style="object-fit:cover;">
                            <i class="bi bi-image text-muted small"></i>
                        </div>
                        <input type="file" class="form-control form-control-sm variant-image-input" name="variants[${i}][image]" accept="image/jpeg,image/jpg,image/png,image/webp">
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btnRemoveVariant" data-variant-id="${i}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#variantRows').append(row);
        toggleStockField();
    });

    // Auto-calculate on variant stock change
    $(document).on('input', '.variant-stock', function() {
        calculateTotalStock();
    });

    // Remove variant row and recalculate
    $(document).on('click', '.btnRemoveVariant', function() {
        const variantId = $(this).data('variant-id');
        $(`tr[data-variant-id="${variantId}"]`).remove();
        toggleStockField();
    });

    // ---- Product / Variant image previews ----
    function previewImage(input, $img, $placeholder) {
        const file = input.files && input.files[0];
        if (!file) {
            $img.addClass('d-none').attr('src', '');
            if ($placeholder) $placeholder.removeClass('d-none');
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            $img.attr('src', e.target.result).removeClass('d-none');
            if ($placeholder) $placeholder.addClass('d-none');
        };
        reader.readAsDataURL(file);
    }

    $('#productImageInput').on('change', function() {
        previewImage(this, $('#productImagePreview'), $('#productImagePlaceholder'));
        $('#btnClearProductImage').toggleClass('d-none', !(this.files && this.files[0]));
    });

    $('#btnClearProductImage').on('click', function() {
        $('#productImageInput').val('');
        $('#productImagePreview').addClass('d-none').attr('src', '');
        $('#productImagePlaceholder').removeClass('d-none');
        $(this).addClass('d-none');
    });

    $(document).on('change', '.variant-image-input', function() {
        const $wrap = $(this).closest('td').find('.variant-image-preview');
        previewImage(this, $wrap.find('img'), $wrap.find('i'));
    });
});
</script>
@endpush
