@extends('layout.app')

@section('title', 'Customer Special Rates')

@section('content')

<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center gy-2">
        <div class="col-12 col-md-6">
          <h3 class="mb-0 fw-bold" style="color:#0f2747;">
            <i class="bi bi-percent me-2"></i>Customer Special Discount Rates
          </h3>
          <p class="text-muted small mb-0">Stock Management · Customer-specific product rates</p>
        </div>
        <div class="col-12 col-md-6">
          <ol class="breadcrumb float-md-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Stock Management</span></li>
            <li class="breadcrumb-item active">Special Rates</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <div class="sr-select-panel sr-select-customer">
            <div class="sr-select-panel-head">
              <span class="sr-select-icon"><i class="bi bi-person-fill"></i></span>
              <div>
                <div class="sr-select-title">Select Customer</div>
                <div class="sr-select-sub">Choose customer for special rates</div>
              </div>
            </div>
            <div class="sr-select-panel-body">
              <select class="form-select select2" id="customerCode">
                <option value="">-- Select Customer --</option>
                @foreach($customers as $customer)
                  <option value="{{ $customer->custCode }}">{{ $customer->custName }} - {{ $customer->custCode }} ({{ $customer->custArea }})</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="sr-select-panel sr-select-product">
            <div class="sr-select-panel-head">
              <span class="sr-select-icon"><i class="bi bi-box-seam"></i></span>
              <div>
                <div class="sr-select-title">Select Product</div>
                <div class="sr-select-sub">Choose product to set special rate</div>
              </div>
            </div>
            <div class="sr-select-panel-body">
              <div class="d-flex gap-2">
                <div class="flex-grow-1">
                  <select class="form-select select2" id="productCode">
                    <option value="">-- Select Product --</option>
                    @foreach($products as $product)
                      <option value="{{ $product->productCode }}">{{ $product->productName }} - {{ $product->companyName }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="variantWrapper d-none" style="min-width:180px;">
                  <select class="form-select variantSelect" id="variantSelect">
                    <option value="">-- Select Variant --</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <div class="card sr-info-card sr-info-customer h-100 border-0" id="customerInfoCard" style="display:none;">
            <div class="sr-info-head">
              <span class="sr-info-head-icon"><i class="bi bi-person-badge"></i></span>
              <div>
                <h6 class="sr-info-title mb-0">Customer Information</h6>
                <small class="sr-info-head-sub">Selected customer details</small>
              </div>
            </div>
            <div class="card-body p-3">
              <div class="row g-2">
                <div class="col-6">
                  <div class="sr-info-item">
                    <span class="sr-meta"><i class="bi bi-hash me-1"></i>Code</span>
                    <strong class="sr-val" id="custCodeDisplay"></strong>
                  </div>
                </div>
                <div class="col-6">
                  <div class="sr-info-item">
                    <span class="sr-meta"><i class="bi bi-person me-1"></i>Name</span>
                    <strong class="sr-val" id="custNameDisplay"></strong>
                  </div>
                </div>
                <div class="col-6">
                  <div class="sr-info-item">
                    <span class="sr-meta"><i class="bi bi-geo-alt me-1"></i>Area</span>
                    <strong class="sr-val" id="custAreaDisplay"></strong>
                  </div>
                </div>
                <div class="col-6">
                  <div class="sr-info-item">
                    <span class="sr-meta"><i class="bi bi-building me-1"></i>City</span>
                    <strong class="sr-val" id="custCityDisplay"></strong>
                  </div>
                </div>
                <div class="col-6">
                  <div class="sr-info-item sr-info-item-accent">
                    <span class="sr-meta"><i class="bi bi-percent me-1"></i>General Discount</span>
                    <strong class="sr-val"><span id="custDiscountDisplay"></span>%</strong>
                  </div>
                </div>
                <div class="col-6">
                  <div class="sr-info-item sr-info-item-accent">
                    <span class="sr-meta"><i class="bi bi-wallet2 me-1"></i>Balance</span>
                    <strong class="sr-val" id="custBalanceDisplay"></strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card sr-info-card sr-info-product h-100 border-0" id="productInfoCard" style="display:none;">
            <div class="sr-info-head">
              <span class="sr-info-head-icon"><i class="bi bi-box-seam"></i></span>
              <div>
                <h6 class="sr-info-title mb-0">Product Information</h6>
                <small class="sr-info-head-sub">Selected product details</small>
              </div>
            </div>
            <div class="card-body p-3">
              <div class="row g-2">
                <div class="col-6">
                  <div class="sr-info-item">
                    <span class="sr-meta"><i class="bi bi-hash me-1"></i>Code</span>
                    <strong class="sr-val" id="prodCodeDisplay"></strong>
                  </div>
                </div>
                <div class="col-6">
                  <div class="sr-info-item">
                    <span class="sr-meta"><i class="bi bi-tag me-1"></i>Name</span>
                    <strong class="sr-val" id="prodNameDisplay"></strong>
                  </div>
                </div>
                <div class="col-6">
                  <div class="sr-info-item">
                    <span class="sr-meta"><i class="bi bi-building me-1"></i>Company</span>
                    <strong class="sr-val" id="prodCompanyDisplay"></strong>
                  </div>
                </div>
                <div class="col-6">
                  <div class="sr-info-item">
                    <span class="sr-meta"><i class="bi bi-boxes me-1"></i>Stock</span>
                    <strong class="sr-val" id="prodStockDisplay"></strong>
                  </div>
                </div>
                <div class="col-6">
                  <div class="sr-info-item sr-info-item-accent">
                    <span class="sr-meta"><i class="bi bi-cash me-1"></i>Cost Rate</span>
                    <strong class="sr-val">Rs. <span id="prodCostDisplay"></span></strong>
                  </div>
                </div>
                <div class="col-6">
                  <div class="sr-info-item sr-info-item-accent">
                    <span class="sr-meta"><i class="bi bi-currency-dollar me-1"></i>Sale Rate</span>
                    <strong class="sr-val sr-val-sale">Rs. <span id="prodSaleDisplay"></span></strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card sr-card border-0 mb-3" id="rateSettingCard" style="display:none;">
        <div class="card-header sr-card-head">
          <h6 class="mb-0 text-white fw-semibold">
            <i class="bi bi-currency-dollar me-2"></i>Set Special Rate
          </h6>
        </div>
        <div class="card-body p-3">
          <form id="rateForm">
            <div class="row g-3">
              <div class="col-md-2">
                <label class="form-label fw-semibold sr-label">Rate Type <span class="text-danger">*</span></label>
                <select class="form-select" id="rateType" required>
                  <option value="Sale" selected>Sale</option>
                  <option value="Purchase">Purchase</option>
                </select>
                <small class="text-muted">Sale or Purchase rate</small>
              </div>
              <div class="col-md-2">
                <label class="form-label fw-semibold sr-label">Effective From <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="effectiveFrom" required>
                <small class="text-muted">Rate starts from this date</small>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-semibold sr-label">Special Rate <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text">Rs.</span>
                  <input type="number" class="form-control" id="specialRate" step="0.01" min="0" required>
                </div>
                <small class="text-muted">Special rate for this customer</small>
              </div>
              <div class="col-md-2">
                <label class="form-label fw-semibold sr-label">Additional Discount %</label>
                <input type="number" class="form-control" id="discountPercent" step="0.01" min="0" max="100" value="0">
                <small class="text-muted">Optional extra discount</small>
              </div>
              <div class="col-md-3">
                <label class="form-label fw-semibold sr-label">Remarks</label>
                <input type="text" class="form-control" id="remarks" placeholder="Optional notes">
              </div>
              <div class="col-12 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-sm text-white" style="background:#0f2747;border-color:#0f2747;">
                  <i class="bi bi-save me-1"></i>Save Special Rate
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnReset">
                  <i class="bi bi-x-circle me-1"></i>Reset
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="card sr-card border-0" id="ratesTableCard" style="display:none;">
        <div class="card-header sr-card-head">
          <h6 class="mb-0 text-white fw-semibold">
            <i class="bi bi-list-check me-2"></i>Saved Special Rates for Selected Customer
          </h6>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 sr-table">
              <thead>
                <tr>
                  <th>Product Code</th>
                  <th>Product Name</th>
                  <th>Company</th>
                  <th>Type</th>
                  <th class="text-end">Standard Rate</th>
                  <th class="text-end">Special Rate</th>
                  <th class="text-end">Discount %</th>
                  <th>Effective From</th>
                  <th>Remarks</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody id="ratesTableBody"></tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</main>

<div class="modal fade" id="rateHistoryModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header sr-modal-head">
        <h5 class="modal-title"><i class="bi bi-clock-history me-2"></i>Rate Change History</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <h6 id="historyProductName" class="mb-3 text-muted"></h6>
        <div class="table-responsive">
          <table class="table table-bordered sr-table mb-0">
            <thead>
              <tr>
                <th>Effective From</th>
                <th>Type</th>
                <th class="text-end">Special Rate</th>
                <th class="text-end">Discount %</th>
                <th>Remarks</th>
                <th class="text-center">Status</th>
              </tr>
            </thead>
            <tbody id="historyTableBody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
  .sr-card {
    box-shadow: 0 2px 12px rgba(15, 39, 71, 0.08);
    border-radius: 4px;
    overflow: visible;
    border: 1px solid #d5dde8;
  }
  .sr-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    border: none;
    border-radius: 4px 4px 0 0;
    padding: 0.75rem 1rem;
  }
  .sr-card .card-body {
    overflow: visible;
    position: relative;
    z-index: 2;
  }
  .sr-label { font-size: 0.78rem; color: #0f2747; margin-bottom: 0.25rem; }

  /* Colorful select panels */
  .sr-select-panel {
    border-radius: 8px;
    overflow: visible;
    box-shadow: 0 4px 16px rgba(15, 39, 71, 0.1);
    border: 1px solid transparent;
    height: 100%;
  }
  .sr-select-customer {
    background: linear-gradient(160deg, #eef3fb 0%, #ffffff 55%);
    border-color: #b8c9de;
  }
  .sr-select-product {
    background: linear-gradient(160deg, #eaf8ef 0%, #ffffff 55%);
    border-color: #a8d9b8;
  }
  .sr-select-panel-head {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid rgba(15, 39, 71, 0.08);
  }
  .sr-select-customer .sr-select-panel-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
  }
  .sr-select-product .sr-select-panel-head {
    background: linear-gradient(135deg, #146c43 0%, #198754 55%, #20c997 100%);
    color: #fff;
  }
  .sr-select-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,0.18);
    font-size: 1.15rem; flex-shrink: 0;
  }
  .sr-select-title { font-weight: 700; font-size: 0.95rem; line-height: 1.2; }
  .sr-select-sub { font-size: 0.72rem; opacity: 0.85; }
  .sr-select-panel-body { padding: 1rem; }

  .sr-select-customer .select2-container--bootstrap-5 .select2-selection {
    border: 2px solid #9eb6d4 !important;
    background: #f4f8fd !important;
    border-radius: 8px !important;
    min-height: 44px !important;
  }
  .sr-select-customer .select2-container--bootstrap-5.select2-container--focus .select2-selection,
  .sr-select-customer .select2-container--bootstrap-5.select2-container--open .select2-selection {
    border-color: #1a4a8a !important;
    box-shadow: 0 0 0 0.25rem rgba(26, 74, 138, 0.18) !important;
    background: #fff !important;
  }
  .sr-select-product .select2-container--bootstrap-5 .select2-selection {
    border: 2px solid #8fceb0 !important;
    background: #f3fbf6 !important;
    border-radius: 8px !important;
    min-height: 44px !important;
  }
  .sr-select-product .select2-container--bootstrap-5.select2-container--focus .select2-selection,
  .sr-select-product .select2-container--bootstrap-5.select2-container--open .select2-selection {
    border-color: #198754 !important;
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.18) !important;
    background: #fff !important;
  }
  .sr-select-product .variantSelect {
    border: 2px solid #8fceb0;
    background: #f3fbf6;
    border-radius: 8px;
    min-height: 44px;
  }
  .sr-select-product .variantSelect:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.18);
  }

  .select2-container { z-index: 20 !important; }
  .select2-container--open { z-index: 1060 !important; }
  .select2-dropdown { z-index: 1061 !important; }
  .select2-container--bootstrap-5 .select2-selection {
    display: flex !important;
    align-items: center;
  }
  .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
    line-height: 1.4;
    padding-left: 0.85rem;
    font-weight: 600;
    color: #0f2747;
  }
  .select2-container--bootstrap-5 .select2-results__option--highlighted {
    background-color: #0f2747 !important;
    color: #fff !important;
  }
  body.sr-product-dd-open .select2-container--bootstrap-5 .select2-results__option--highlighted {
    background-color: #198754 !important;
  }

  /* Colorful info cards */
  .sr-info-card {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(15, 39, 71, 0.12);
    color: #1a1a1a;
  }
  .sr-info-customer {
    background: linear-gradient(165deg, #e8eef8 0%, #f7faff 40%, #ffffff 100%);
    border: 1px solid #9eb6d4;
  }
  .sr-info-product {
    background: linear-gradient(165deg, #e6f7ec 0%, #f4fcf7 40%, #ffffff 100%);
    border: 1px solid #8fceb0;
  }
  .sr-info-head {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
  }
  .sr-info-customer .sr-info-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
  }
  .sr-info-product .sr-info-head {
    background: linear-gradient(135deg, #146c43 0%, #198754 55%, #20c997 100%);
    color: #fff;
  }
  .sr-info-head-icon {
    width: 42px; height: 42px; border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,0.18);
    font-size: 1.2rem; flex-shrink: 0;
  }
  .sr-info-title { font-weight: 700; font-size: 0.98rem; color: inherit; }
  .sr-info-head-sub { opacity: 0.85; font-size: 0.72rem; }
  .sr-info-item {
    background: #fff;
    border: 1px solid rgba(15, 39, 71, 0.08);
    border-radius: 6px;
    padding: 0.55rem 0.7rem;
    height: 100%;
    box-shadow: 0 1px 3px rgba(15, 39, 71, 0.04);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
  }
  .sr-info-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(15, 39, 71, 0.08);
  }
  .sr-info-customer .sr-info-item-accent {
    background: linear-gradient(135deg, #e8eef8 0%, #ffffff 100%);
    border-color: #9eb6d4;
  }
  .sr-info-product .sr-info-item-accent {
    background: linear-gradient(135deg, #e6f7ec 0%, #ffffff 100%);
    border-color: #8fceb0;
  }
  .sr-meta {
    display: block;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: #5a6a7a;
    margin-bottom: 0.2rem;
  }
  .sr-info-customer .sr-meta { color: #1a4a8a; }
  .sr-info-product .sr-meta { color: #146c43; }
  .sr-val {
    display: block;
    font-size: 0.92rem;
    font-weight: 700;
    color: #0f2747;
    line-height: 1.25;
    word-break: break-word;
  }
  .sr-val-sale { color: #198754 !important; }

  .sr-table thead th {
    background: #0f2747;
    color: #fff;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border-color: #0a1c33;
    white-space: nowrap;
    vertical-align: middle;
  }
  .sr-table tbody td {
    font-size: 0.82rem;
    border-color: #e8eef5;
    vertical-align: middle;
  }
  .sr-table tbody tr:nth-child(even) td { background: #f4f7fb; }
  .sr-code {
    display: inline-block;
    padding: 0.15rem 0.45rem;
    border-radius: 3px;
    background: #e8eef5;
    color: #0f2747;
    font-weight: 700;
    font-size: 0.75rem;
  }
  .sr-modal-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
  }
  .form-control:focus, .form-select:focus {
    border-color: #1a4a8a;
    box-shadow: 0 0 0 0.2rem rgba(15, 39, 71, 0.12);
  }
</style>
@endpush

<script>
$(document).ready(function() {
  const productVariants = @json($productVariants);

  $('#customerCode').select2({
    theme: 'bootstrap-5',
    width: '100%',
    dropdownParent: $(document.body)
  });
  $('#productCode').select2({
    theme: 'bootstrap-5',
    width: '100%',
    dropdownParent: $(document.body)
  });
  $('#productCode').on('select2:open', function () {
    document.body.classList.add('sr-product-dd-open');
  }).on('select2:close', function () {
    document.body.classList.remove('sr-product-dd-open');
  });

  const fmt = (n) => parseFloat(n || 0).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2});

  $('#effectiveFrom').val(new Date().toISOString().split('T')[0]);

  $('#customerCode').on('change', function(){
    const custCode = $(this).val();
    if(!custCode) {
      $('#customerInfoCard, #ratesTableCard').hide();
      return;
    }

    $.ajax({
      url: `{{ url('/customer-discount/customer') }}/${encodeURIComponent(custCode)}`,
      method: 'GET',
      success: function(res){
        $('#custCodeDisplay').text(res.custCode);
        $('#custNameDisplay').text(res.custName);
        $('#custAreaDisplay').text(res.custArea);
        $('#custCityDisplay').text(res.custCity);
        $('#custDiscountDisplay').text(res.custdiscount);
        $('#custBalanceDisplay').text(fmt(res.balance));
        $('#customerInfoCard').fadeIn();
        loadCustomerRates(custCode);
        checkFormReady();
      },
      error: function(){ showError('Error loading customer info'); }
    });
  });

  $('#productCode').on('change', function(){
    const prodCode = $(this).val();
    const variantWrapper = $('.variantWrapper');
    const variantSelect = $('#variantSelect');

    if(!prodCode) {
      $('#productInfoCard').hide();
      variantWrapper.addClass('d-none');
      variantSelect.html('<option value="">-- Select Variant --</option>');
      return;
    }

    const variants = productVariants[prodCode] || [];
    variantSelect.html('<option value="">-- Select Variant --</option>');

    if(variants.length > 0){
      variants.forEach(v => {
        const stockBadge = v.stockQty > 0 ? ` [${v.stockQty}]` : ' [OUT]';
        const opt = new Option(`${v.name}: ${v.value}${stockBadge}`, v.id);
        opt.disabled = v.stockQty <= 0;
        $(opt).data('additional-price', v.additionalPrice);
        $(opt).data('variant-name', v.name);
        $(opt).data('variant-value', v.value);
        variantSelect.append(opt);
      });
      variantWrapper.removeClass('d-none');
    } else {
      variantWrapper.addClass('d-none');
    }

    $.ajax({
      url: `{{ url('/customer-discount/product') }}/${encodeURIComponent(prodCode)}`,
      method: 'GET',
      success: function(res){
        $('#prodCodeDisplay').text(res.productCode);
        $('#prodNameDisplay').text(res.productName);
        $('#prodCompanyDisplay').text(res.companyName);
        $('#prodStockDisplay').text(res.stock);
        $('#prodCostDisplay').text(fmt(res.purchaseRate));
        $('#prodSaleDisplay').text(fmt(res.saleRate));
        $('#productInfoCard').fadeIn();

        $('#specialRate').data('baseRate', res.saleRate);
        $('#specialRate').val(res.saleRate);
        checkFormReady();
      },
      error: function(){ showError('Error loading product info'); }
    });
  });

  $('#variantSelect').on('change', function(){
    const selectedOpt = $(this).find('option:selected');
    const addPrice = parseFloat(selectedOpt.data('additional-price')) || 0;
    const baseRate = parseFloat($('#specialRate').data('baseRate')) || 0;
    $('#specialRate').val((baseRate + addPrice).toFixed(2));
  });

  function checkFormReady(){
    if($('#customerCode').val() && $('#productCode').val()){
      $('#rateSettingCard').fadeIn();
    }
  }

  $('#rateForm').on('submit', function(e){
    e.preventDefault();

    const custCode = $('#customerCode').val();
    const prodCode = $('#productCode').val();

    if(!custCode || !prodCode){
      showWarning('Please select customer and product');
      return;
    }

    const variantId = $('#variantSelect').val() || null;
    const variantOpt = $('#variantSelect').find('option:selected');
    const variantName = variantOpt.data('variant-name') || null;
    const variantValue = variantOpt.data('variant-value') || null;

    const data = {
      _token: '{{ csrf_token() }}',
      customerCode: custCode,
      productCode: prodCode,
      variant_id: variantId,
      variant_name: variantName,
      variant_value: variantValue,
      effective_from: $('#effectiveFrom').val(),
      specialRate: $('#specialRate').val(),
      rateType: $('#rateType').val() || 'Sale',
      discountPercent: $('#discountPercent').val() || 0,
      remarks: $('#remarks').val()
    };

    $.ajax({
      url: '{{ url("/customer-discount/store") }}',
      method: 'POST',
      data: data,
      success: function(res){
        if(res.success){
          showSuccess('Special rate saved successfully!');
          loadCustomerRates(custCode);
          $('#productCode').val('').trigger('change');
          $('#productInfoCard, #rateSettingCard').hide();
          $('#specialRate, #discountPercent, #remarks').val('');
          $('#effectiveFrom').val(new Date().toISOString().split('T')[0]);
        } else {
          showError(res.message);
        }
      },
      error: function(xhr){
        showError(xhr.responseJSON?.message || 'Error saving rate');
      }
    });
  });

  function loadCustomerRates(custCode){
    $.ajax({
      url: `{{ url('/customer-discount/rates') }}/${encodeURIComponent(custCode)}`,
      method: 'GET',
      success: function(res){
        const tbody = $('#ratesTableBody').empty();

        if(res.data.length === 0){
          tbody.append('<tr><td colspan="10" class="text-center text-muted py-3">No special rates saved</td></tr>');
          $('#ratesTableCard').fadeIn();
          return;
        }

        res.data.forEach(rate => {
          const typeBadge = rate.rateType === 'Purchase'
            ? '<span class="badge text-bg-warning text-dark">Purchase</span>'
            : '<span class="badge text-bg-success">Sale</span>';
          tbody.append(`
            <tr>
              <td><span class="sr-code">${rate.productCode}</span></td>
              <td>${rate.productName}</td>
              <td>${rate.companyName}</td>
              <td>${typeBadge}</td>
              <td class="text-end">Rs. ${fmt(rate.saleRate)}</td>
              <td class="text-end"><strong class="text-success">Rs. ${fmt(rate.specialRate)}</strong></td>
              <td class="text-end">${rate.discountPercent}%</td>
              <td><span class="badge" style="background:#e8eef5;color:#0f2747;">${rate.effective_from}</span></td>
              <td>${rate.remarks || '-'}</td>
              <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-secondary me-1 btn-view-history" data-product-code="${rate.productCode}" data-product-name="${String(rate.productName).replace(/"/g, '&quot;')}">
                  <i class="bi bi-clock-history"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteRate(${rate.id})">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          `);
        });

        $('#ratesTableCard').fadeIn();
      }
    });
  }

  $('#btnReset').on('click', function(){
    $('#productCode').val('').trigger('change');
    $('#productInfoCard, #rateSettingCard').hide();
    $('#specialRate, #discountPercent, #remarks').val('');
    $('#effectiveFrom').val(new Date().toISOString().split('T')[0]);
  });

  $(document).on('click', '.btn-view-history', function(){
    viewHistory($(this).data('product-code'), $(this).attr('data-product-name') || '');
  });

  function viewHistory(productCode, productName){
    const custCode = $('#customerCode').val();
    $('#historyProductName').text(`Product: ${productName || productCode} (${productCode})`);

    $.ajax({
      url: `{{ url('/customer-discount/history') }}/${encodeURIComponent(custCode)}/${encodeURIComponent(productCode)}`,
      method: 'GET',
      success: function(res){
        const tbody = $('#historyTableBody').empty();

        if(res.data.length === 0){
          tbody.append('<tr><td colspan="6" class="text-center text-muted">No history available</td></tr>');
        } else {
          res.data.forEach(h => {
            const statusBadge = h.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
            const typeBadge = h.rateType === 'Purchase'
              ? '<span class="badge text-bg-warning text-dark">Purchase</span>'
              : '<span class="badge text-bg-success">Sale</span>';
            tbody.append(`
              <tr class="${h.is_active ? 'table-success' : ''}">
                <td>${h.effective_from}</td>
                <td>${typeBadge}</td>
                <td class="text-end">Rs. ${fmt(h.specialRate)}</td>
                <td class="text-end">${h.discountPercent}%</td>
                <td>${h.remarks || '-'}</td>
                <td class="text-center">${statusBadge}</td>
              </tr>
            `);
          });
        }

        $('#rateHistoryModal').modal('show');
      },
      error: function(){ showError('Error loading rate history'); }
    });
  }

  window.deleteRate = function(id){
    if(!confirm('Deactivate this special rate?')) return;

    $.ajax({
      url: `{{ url('/customer-discount') }}/${id}`,
      method: 'DELETE',
      data: { _token: '{{ csrf_token() }}' },
      success: function(res){
        if(res.success){
          showSuccess('Rate deactivated successfully!');
          loadCustomerRates($('#customerCode').val());
        } else {
          showError(res.message);
        }
      },
      error: function(){ showError('Error deactivating rate'); }
    });
  };
});
</script>

@endsection
