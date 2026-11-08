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

            <form action="{{ route('product.update', $product->id) }}" method="POST" id="editProductForm" enctype="multipart/form-data">
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

                                <div class="mb-2">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-building text-warning me-1"></i>
                                        Company Name <span class="text-danger">*</span>
                                    </label>
                                    @php
                                        $currentCompany = old('companyName', $product->companyName);
                                        $companyExists = $proCompanys->contains(function($c) use($currentCompany){
                                            return strtolower($c->companyName) === strtolower($currentCompany);
                                        });
                                    @endphp
                                    <div class="input-group input-group-sm">
                                        <select class="form-select @error('companyName') is-invalid @enderror" 
                                                id="companySelect"
                                                name="companyName" 
                                                required>
                                            <option value="">-- Select Company --</option>
                                            @foreach($proCompanys as $company)
                                                <option value="{{ $company->companyName }}" {{ strtolower($currentCompany) == strtolower($company->companyName) ? 'selected' : '' }}>
                                                    {{ $company->companyName }}
                                                </option>
                                            @endforeach
                                            @if(!$companyExists && !empty($currentCompany))
                                                <option value="{{ $currentCompany }}" selected>{{ $currentCompany }} (Not In List)</option>
                                            @endif
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
                                        <i class="bi bi-palette text-warning me-1"></i>
                                        Colour <span class="text-danger">*</span>
                                    </label>
                                    @php
                                        $currentColour = old('colourName', $product->colourName);
                                        $colourExists = $colours->contains(function($c) use($currentColour){
                                            return strtolower($c->colourName) === strtolower($currentColour);
                                        });
                                    @endphp
                                    <div class="input-group input-group-sm">
                                        <select class="form-select @error('colourName') is-invalid @enderror"
                                                id="colourSelect"
                                                name="colourName"
                                                required>
                                            <option value="">-- Select Colour --</option>
                                            @foreach($colours as $colour)
                                                <option value="{{ $colour->colourName }}" {{ strtolower($currentColour) == strtolower($colour->colourName) ? 'selected' : '' }}>
                                                    {{ $colour->colourName }}
                                                </option>
                                            @endforeach
                                            @if(!$colourExists && !empty($currentColour))
                                                <option value="{{ $currentColour }}" selected>{{ $currentColour }} (Not In List)</option>
                                            @endif
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
                                        <i class="bi bi-box-seam text-warning me-1"></i>
                                        Packing <span class="text-danger">*</span>
                                    </label>
                                    @php
                                        $currentPacking = old('pakingName', $product->pakingName);
                                        $currentUnit = old('unitPerPackName', $product->unitPerPackName);
                                        $currentPieces = (int) old('piecesPerUnit', $product->piecesPerUnit ?: 1);
                                        $selectedPackingId = null;
                                        foreach ($packings as $packing) {
                                            if (strcasecmp((string) $packing->pakingName, (string) $currentPacking) === 0
                                                && strcasecmp((string) ($packing->baseUnit ?: ''), (string) $currentUnit) === 0
                                                && (int) ($packing->qtyInPack ?: 1) === $currentPieces) {
                                                $selectedPackingId = $packing->id;
                                                break;
                                            }
                                        }
                                    @endphp
                                    <div class="input-group input-group-sm">
                                        <select class="form-select @error('pakingName') is-invalid @enderror"
                                                id="packingSelect"
                                                required>
                                            <option value="">-- Select Packing --</option>
                                            @foreach($packings as $packing)
                                                <option value="{{ $packing->id }}"
                                                        data-name="{{ $packing->pakingName }}"
                                                        data-unit="{{ $packing->baseUnit ?: 'PIECE' }}"
                                                        data-qty="{{ $packing->qtyInPack ?: 1 }}"
                                                        @selected($selectedPackingId === $packing->id)>
                                                    {{ $packing->displayLabel() }}
                                                </option>
                                            @endforeach
                                            @if(!$selectedPackingId && !empty($currentPacking))
                                                <option value="legacy"
                                                        data-name="{{ $currentPacking }}"
                                                        data-unit="{{ $currentUnit ?: 'PIECE' }}"
                                                        data-qty="{{ $currentPieces }}"
                                                        selected>
                                                    {{ $currentPacking }} · {{ $currentPieces }} {{ $currentUnit ?: 'PIECE' }} (Not In List)
                                                </option>
                                            @endif
                                        </select>
                                        <button type="button" class="btn btn-outline-primary" onclick="openModal('packingModal')" title="Add Packing">
                                            <i class="bi bi-plus-circle"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" name="pakingName" id="pakingNameHidden" value="{{ $currentPacking }}">
                                    <input type="hidden" name="unitPerPackName" id="unitPerPackNameHidden" value="{{ $currentUnit }}">
                                    <input type="hidden" name="piecesPerUnit" id="piecesPerUnitHidden" value="{{ $currentPieces }}">
                                    @error('pakingName')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('unitPerPackName')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('piecesPerUnit')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-2">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-tag text-warning me-1"></i>
                                        Category <span class="text-danger">*</span>
                                    </label>
                                    @php
                                        $currentCategory = old('catagoryName', $product->catagoryName);
                                        $categoryExists = $catagorys->contains(function($c) use($currentCategory){
                                            return strtolower($c->catagoryName) === strtolower($currentCategory);
                                        });
                                    @endphp
                                    <div class="input-group input-group-sm">
                                        <select class="form-select @error('catagoryName') is-invalid @enderror" 
                                                id="categorySelect"
                                                name="catagoryName" 
                                                required>
                                            <option value="">-- Select Category --</option>
                                            @foreach($catagorys as $category)
                                                <option value="{{ $category->catagoryName }}" {{ strtolower($currentCategory) == strtolower($category->catagoryName) ? 'selected' : '' }}>
                                                    {{ $category->catagoryName }}
                                                </option>
                                            @endforeach
                                            @if(!$categoryExists && !empty($currentCategory))
                                                <option value="{{ $currentCategory }}" selected>{{ $currentCategory }} (Not In List)</option>
                                            @endif
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
                                        <i class="bi bi-image text-warning me-1"></i>
                                        Product Image
                                    </label>
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="product-image-preview border rounded bg-light d-flex align-items-center justify-content-center overflow-hidden" style="width:72px;height:72px;flex-shrink:0;">
                                            <img id="productImagePreview"
                                                 src="{{ $product->image_url }}"
                                                 alt="Product image"
                                                 class="{{ $product->image_url ? '' : 'd-none' }} w-100 h-100"
                                                 style="object-fit:cover;">
                                            <i id="productImagePlaceholder" class="bi bi-image text-muted fs-4 {{ $product->image_url ? 'd-none' : '' }}"></i>
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
                                            <div class="form-check mt-1 {{ $product->image ? '' : 'd-none' }}" id="removeProductImageWrap">
                                                <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="removeProductImage">
                                                <label class="form-check-label small text-danger" for="removeProductImage">Remove current image</label>
                                            </div>
                                        </div>
                                    </div>
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
                                                   value="{{ old('stockQty', $product->stockQty) }}"
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

                <!-- Product Variants Section -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h6 class="text-info mb-0">
                                    <i class="bi bi-diagram-3 me-2"></i>
                                    Product Variants (Optional)
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-info" id="btnAddVariant">
                                    <i class="bi bi-plus-circle me-1"></i>
                                    Add Variant
                                </button>
                            </div>

                            <div id="variantsContainer">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0" id="variantsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 14%">Variant Name</th>
                                                <th style="width: 12%">Value</th>
                                                <th style="width: 11%">Add. Cost</th>
                                                <th style="width: 11%">Add. Price</th>
                                                <th style="width: 9%">Stock</th>
                                                <th style="width: 33%">Image</th>
                                                <th style="width: 10%" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="variantRows">
                                            @foreach($product->variants as $variant)
                                            <tr data-variant-id="{{ $variant->id }}" data-existing="true">
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" name="existing_variants[{{ $variant->id }}][name]" value="{{ $variant->variantName }}" placeholder="e.g., Size, Weight" required>
                                                    <input type="hidden" name="existing_variants[{{ $variant->id }}][id]" value="{{ $variant->id }}">
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" name="existing_variants[{{ $variant->id }}][value]" value="{{ $variant->variantValue }}" placeholder="e.g., Small, 1kg" required>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" class="form-control form-control-sm" name="existing_variants[{{ $variant->id }}][additionalCost]" value="{{ $variant->additionalCost }}" placeholder="0.00">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" class="form-control form-control-sm" name="existing_variants[{{ $variant->id }}][additionalPrice]" value="{{ $variant->additionalPrice }}" placeholder="0.00">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm variant-stock" name="existing_variants[{{ $variant->id }}][stockQty]" value="{{ $variant->stockQty }}" placeholder="0">
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                        <div class="variant-image-preview border rounded bg-light d-flex align-items-center justify-content-center overflow-hidden" style="width:40px;height:40px;flex-shrink:0;">
                                                            <img src="{{ $variant->image_url }}"
                                                                 alt=""
                                                                 class="{{ $variant->image_url ? '' : 'd-none' }} w-100 h-100"
                                                                 style="object-fit:cover;">
                                                            <i class="bi bi-image text-muted small {{ $variant->image_url ? 'd-none' : '' }}"></i>
                                                        </div>
                                                        <input type="file"
                                                               class="form-control form-control-sm variant-image-input"
                                                               name="existing_variants[{{ $variant->id }}][image]"
                                                               accept="image/jpeg,image/jpg,image/png,image/webp"
                                                               style="max-width:160px;">
                                                        @if($variant->image)
                                                            <div class="form-check mb-0">
                                                                <input class="form-check-input variant-remove-image" type="checkbox" name="existing_variants[{{ $variant->id }}][remove_image]" value="1" id="removeVariantImage{{ $variant->id }}">
                                                                <label class="form-check-label small text-danger" for="removeVariantImage{{ $variant->id }}">Remove</label>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger btnRemoveVariant" data-variant-id="{{ $variant->id }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <p class="text-muted small mb-0 mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Add variants like Size (Small, Medium, Large), Weight (1kg, 5kg), Color variations, etc.
                                </p>
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

<!-- Company Modal -->
<div class="modal fade" id="companyModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-building me-2"></i>Manage Companies</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="companyForm" action="{{ route('company.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="companyName" class="form-control" placeholder="e.g., Sony, Samsung" autocomplete="off" required>
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
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-palette-fill me-2"></i>Manage Colours</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="colourForm" action="{{ route('colours.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Colour Name <span class="text-danger">*</span></label>
                        <input type="text" name="colourName" class="form-control" placeholder="e.g., Red, Blue" autocomplete="off" required>
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
            <div class="modal-header bg-primary text-white">
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
            <div class="modal-header bg-primary text-white">
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

@push('scripts')
<script>
$(function(){
    // Modal helper
    function openModal(modalId) {
        $('#' + modalId).modal('show');
    }
    window.openModal = openModal;

    function notify(msg, type='success'){
        const el=$('<div class="position-fixed top-0 end-0 p-3" style="z-index:2000;"><div class="toast align-items-center text-bg-'+type+' border-0 show" role="alert"><div class="d-flex"><div class="toast-body">'+msg+'</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div></div>');
        $('body').append(el); setTimeout(()=>el.remove(), 2000);
    }

    // Company name sync
    $('#companySelect').on('change', function() {
        $('#companyNameHidden').val($(this).val());
    });

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
    let variantCounter = {{ $product->variants->count() }};
    let deletedVariants = [];

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

    // Add new variant row
    $('#btnAddVariant').on('click', function() {
        variantCounter++;
        const row = `
            <tr data-variant-id="new_${variantCounter}">
                <td>
                    <input type="text" class="form-control form-control-sm" name="variants[${variantCounter}][name]" placeholder="e.g., Size, Weight" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="variants[${variantCounter}][value]" placeholder="e.g., Small, 1kg" required>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="variants[${variantCounter}][additionalCost]" placeholder="0.00" value="0">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="variants[${variantCounter}][additionalPrice]" placeholder="0.00" value="0">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm variant-stock" name="variants[${variantCounter}][stockQty]" placeholder="0" value="0">
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="variant-image-preview border rounded bg-light d-flex align-items-center justify-content-center overflow-hidden" style="width:40px;height:40px;flex-shrink:0;">
                            <img src="" alt="" class="d-none w-100 h-100" style="object-fit:cover;">
                            <i class="bi bi-image text-muted small"></i>
                        </div>
                        <input type="file" class="form-control form-control-sm variant-image-input" name="variants[${variantCounter}][image]" accept="image/jpeg,image/jpg,image/png,image/webp">
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btnRemoveVariant" data-variant-id="new_${variantCounter}">
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

    // Remove variant row
    $(document).on('click', '.btnRemoveVariant', function() {
        const variantId = $(this).data('variant-id');
        const $row = $(`tr[data-variant-id="${variantId}"]`);
        
        // If existing variant, mark for deletion
        if ($row.data('existing')) {
            const id = variantId;
            deletedVariants.push(id);
            // Add hidden input for deletion
            $('#editProductForm').append(`<input type="hidden" name="deleted_variants[]" value="${id}">`);
        }
        
        $row.remove();
        toggleStockField();
    });

    // ---- Product / Variant image previews ----
    function previewImage(input, $img, $placeholder) {
        const file = input.files && input.files[0];
        if (!file) {
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
        if (this.files && this.files[0]) {
            $('#removeProductImage').prop('checked', false);
            $('#removeProductImageWrap').removeClass('d-none');
        }
    });

    $('#removeProductImage').on('change', function() {
        if (this.checked) {
            $('#productImageInput').val('');
            $('#productImagePreview').addClass('d-none').attr('src', '');
            $('#productImagePlaceholder').removeClass('d-none');
        }
    });

    $(document).on('change', '.variant-image-input', function() {
        const $wrap = $(this).closest('td').find('.variant-image-preview');
        previewImage(this, $wrap.find('img'), $wrap.find('i'));
        $(this).closest('td').find('.variant-remove-image').prop('checked', false);
    });

    $(document).on('change', '.variant-remove-image', function() {
        if (this.checked) {
            const $td = $(this).closest('td');
            $td.find('.variant-image-input').val('');
            $td.find('.variant-image-preview img').addClass('d-none').attr('src', '');
            $td.find('.variant-image-preview i').removeClass('d-none');
        }
    });

    // Initialize on page load
    toggleStockField();
});
</script>
@endpush
