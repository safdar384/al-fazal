@extends('layout.app')

@section('title', 'Purchase Invoice')

@section('content')
@include('partials.sale.erp-styles')

<style>
  /* Professional Select Styling */
  .form-select {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    background-position: right 0.5rem center;
    font-size: 0.875rem;
  }
  
  .form-select:focus {
    border-color: #1a4a8a;
    box-shadow: 0 0 0 0.2rem rgba(15, 39, 71, 0.12);
    outline: 0;
  }
  
  .form-select option {
    padding: 8px 12px;
    font-size: 0.875rem;
  }
  
  /* Supplier Search Styling */
  #supplierSearch {
    border: 2px solid #e0e0e0;
    border-radius: 0.5rem;
    transition: all 0.3s ease-in-out;
    font-size: 0.875rem;
    background-color: #f8f9fa;
    padding: 0.5rem 0.75rem;
  }
  
  #supplierSearch:focus {
    border-color: #43cea2;
    background-color: #fff;
    box-shadow: 0 0 0 0.3rem rgba(67, 206, 162, 0.15);
    outline: 0;
  }
  
  #supplierSearch::placeholder {
    color: #999;
    font-style: italic;
  }
  
  #supplierDropdown {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 10000;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background: #fff;
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.2);
    margin-top: 4px;
    max-height: 350px;
    overflow-y: auto;
  }
  
  #supplierDropdown .dropdown-item {
    padding: 12px 15px;
    cursor: pointer;
    font-size: 0.875rem;
    border-bottom: 1px solid #f1f3f5;
    transition: all 0.15s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  #supplierDropdown .dropdown-item:last-child {
    border-bottom: none;
  }
  
  #supplierDropdown .dropdown-item:hover,
  #supplierDropdown .dropdown-item.active {
    background: linear-gradient(135deg, #43cea2 0%, #35b393 100%);
    color: #fff;
  }
  
  #supplierDropdown .dropdown-item.active .text-muted,
  #supplierDropdown .dropdown-item:hover .text-muted {
    color: rgba(255,255,255,0.9) !important;
  }
  
  #supplierDropdown .dropdown-item.active .badge,
  #supplierDropdown .dropdown-item:hover .badge {
    background-color: rgba(255,255,255,0.4) !important;
    color: #fff;
  }
  
  #supplierDropdown .dropdown-item strong {
    font-weight: 600;
    display: block;
    margin-bottom: 2px;
  }
  
  #supplierDropdown .dropdown-item .badge {
    font-size: 0.7rem;
    padding: 4px 8px;
  }
  
  /* Product Search Styling */
  .productSearch {
    border: 2px solid #e0e0e0;
    border-radius: 0.5rem;
    transition: all 0.3s ease-in-out;
    font-size: 0.875rem;
    background-color: #f8f9fa;
    padding: 0.5rem 0.75rem;
  }
  
  .productSearch:focus {
    border-color: #28a745;
    background-color: #fff;
    box-shadow: 0 0 0 0.3rem rgba(40, 167, 69, 0.15);
    outline: 0;
  }
  
  .productSearch::placeholder {
    color: #999;
    font-style: italic;
  }
  
  .productDropdown {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 10650;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    background: #fff;
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.2);
    margin-top: 4px;
    max-height: 350px;
    overflow-y: auto;
  }
  
  .productDropdown .dropdown-item {
    padding: 12px 15px;
    cursor: pointer;
    font-size: 0.875rem;
    border-bottom: 1px solid #f1f3f5;
    transition: all 0.15s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .productDropdown .dropdown-item:last-child {
    border-bottom: none;
  }
  
  .productDropdown .dropdown-item:hover,
  .productDropdown .dropdown-item.active {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    color: #fff;
  }
  
  .productDropdown .dropdown-item.active .text-muted,
  .productDropdown .dropdown-item:hover .text-muted {
    color: rgba(255,255,255,0.9) !important;
  }
  
  .productDropdown .dropdown-item strong {
    font-weight: 600;
    display: block;
    margin-bottom: 2px;
  }
  
  .product-search-thumb {
    width: 36px;
    height: 36px;
    object-fit: cover;
    flex-shrink: 0;
  }
  .product-search-thumb-empty {
    width: 36px;
    height: 36px;
    font-size: 0.85rem;
  }
  .productDropdown .dropdown-item .text-muted {
    font-size: 0.8rem;
  }

  /* Position relative wrapper for dropdowns */
  .position-relative {
    position: relative !important;
    overflow: visible !important;
  }

  /* Variant select enhancements */
  .variantWrapper select.variantSelect {
    background-image: linear-gradient(135deg,#e0f7ff 0%,#f2fcff 100%);
  }
  .variantWrapper select.variantSelect:focus {
    border-color: #17a2b8;
    box-shadow: 0 0 0 0.2rem rgba(23,162,184,.15);
  }
  .variantStockBadge {
    font-size: .65rem;
    margin-left: .35rem;
  }
  
  /* Supplier Select Enhanced */
  #supplierCode {
    max-width: 100%;
  }
  
  #supplierCode option {
    background: #fff;
    color: #333;
    padding: 10px;
  }
  
  #supplierCode option:hover {
    background: #f8f9fa;
  }
  
  /* Table and dropdown positioning */
  #productTable {
    position: relative;
  }
  
  #productTable td {
    position: relative;
    overflow: visible;
  }
  
  .sale-invoice-fluid #supplierSearch,
  .sale-invoice-fluid .productSearch {
    font-size: inherit;
    padding: inherit;
  }

  .sale-invoice-extra > .transport-calc {
    flex: 1 1 100%;
    min-width: 100%;
    display: flex;
    gap: 0.45rem;
    align-items: flex-start;
  }

  .transport-calc > div {
    flex: 1 1 0;
    min-width: 0;
  }

  @media (max-width: 991.98px) {
    .sale-invoice-fluid .sale-save-desktop { display: none !important; }
  }

  /* Navy ERP shell only — supplier/product list styles stay above */
  .si-card {
    box-shadow: 0 2px 12px rgba(15, 39, 71, 0.08);
    border-radius: 4px;
    overflow: visible;
    border: 1px solid #d5dde8;
  }
  .si-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    border: none;
    border-radius: 4px 4px 0 0;
    padding: 0.75rem 1rem;
    color: #fff;
  }
  .si-label {
    font-size: 0.78rem;
    color: #0f2747;
    margin-bottom: 0.25rem;
    font-weight: 600;
  }
  .si-table thead th {
    background: #0f2747;
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    border-color: #0a1c33;
    white-space: nowrap;
  }
  .si-table tbody tr:nth-child(even) td { background: #f4f7fb; }
  .si-table tbody tr:hover td { background: #e8eef5; }
  .si-grand {
    background: #eefaf2 !important;
    border-color: #a3d9b1 !important;
  }
  .btn-save-purchase {
    background: #0f2747;
    border-color: #0f2747;
    color: #fff;
  }
  .btn-save-purchase:hover {
    background: #1a4a8a;
    border-color: #1a4a8a;
    color: #fff;
  }
  .sale-invoice-fluid .form-control:focus:not(#supplierSearch):not(.productSearch),
  .sale-invoice-fluid .form-select:focus:not(.variantSelect) {
    border-color: #1a4a8a;
    box-shadow: 0 0 0 0.2rem rgba(15, 39, 71, 0.12);
  }

  @media print {
    .app-sidebar, .app-header, .app-content-header .breadcrumb, .btn, button, .no-print { display: none !important; }
    body, .app-main { margin: 0; padding: 0; }
    .card { box-shadow: none !important; border: none !important; }
  }
</style>

<main class="app-main erp-invoice-page sale-invoice-fluid" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid px-2 px-md-3">
      <div class="row align-items-center gy-2">
        <div class="col-12 col-md-6">
          <h3 class="mb-0 fw-bold" style="color:#0f2747;">
            <i class="bi bi-receipt me-2"></i>Purchase Invoice
          </h3>
          <p class="text-muted small mb-0">Purchase · Create &amp; save supplier invoice</p>
        </div>
        <div class="col-12 col-md-6">
          <ol class="breadcrumb float-md-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Purchase</span></li>
            <li class="breadcrumb-item active" aria-current="page">Invoice</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <!-- Invoice Header Card -->
      <div class="card si-card border-0 mb-3">
        <div class="card-header si-card-head">
          <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-file-earmark-text me-2"></i>Invoice Information</h6>
        </div>
        <div class="card-body p-2 p-md-3">
          <div class="sale-invoice-fields">
            <!-- Supplier -->
            <div class="sale-field-wide">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label si-label mb-0">
                  <i class="bi bi-person-badge me-1"></i>Supplier
                </label>
                <a href="javascript:void(0);" class="net-rate-link text-primary" id="addNetRateLink" title="Add purchase net rate for this supplier">
                  <i class="bi bi-plus-circle me-1"></i>Net Rate
                </a>
              </div>
              <div class="position-relative">
                <input type="text" 
                       class="form-control form-control-sm" 
                       id="supplierSearch" 
                       placeholder="Type to search supplier..."
                       autocomplete="off">
                <select class="form-select form-select-sm d-none" id="supplierCode">
                  <option value="">Select Supplier</option>
                  @foreach($suppliers as $supplier)
                    @php
                      $biz = trim((string) ($supplier->bussnessName ?: $supplier->custName));
                      $city = trim((string) ($supplier->custCity ?? ''));
                      $label = $city !== '' ? $biz.' — '.$city : $biz;
                    @endphp
                    <option value="{{ $supplier->custCode }}"
                            data-city="{{ $supplier->custCity }}"
                            data-name="{{ $supplier->custName }}"
                            data-business="{{ $supplier->bussnessName ?? '' }}"
                            data-balance="{{ $supplier->custBalance }}"
                            data-label="{{ $label }}">
                      {{ $label }}
                    </option>
                  @endforeach
                </select>
                <div id="supplierDropdown" class="dropdown-menu w-100" style="max-height: 300px; overflow-y: auto; display: none;"></div>
              </div>
            </div>
            <!-- City -->
            <div>
              <label class="form-label si-label">City</label>
              <input type="text" class="form-control form-control-sm" id="city" readonly>
            </div>

            <!-- Balance -->
            <div>
              <label class="form-label si-label" id="balanceFieldLabel">Payable Balance</label>
              <input type="text" class="form-control form-control-sm" id="balance" readonly>
            </div>

            <!-- Invoice No -->
            <div>
              <label class="form-label si-label">Invoice No</label>
              <input type="text" class="form-control form-control-sm" id="invoiceNo" value="{{ $nextInvoiceNo }}" readonly>
            </div>

            <!-- Invoice Date -->
            <div>
              <label class="form-label si-label">Date</label>
              <input type="date" class="form-control form-control-sm" id="invoiceDate" value="{{ date('Y-m-d') }}">
            </div>
          </div>
        </div>
      </div>

      <!-- Products Grid Card -->
      <div class="card si-card border-0 mb-3" id="purchaseProductsCard">
        <div class="card-header si-card-head py-2">
          <h6 class="mb-0 text-white fw-semibold">
            <i class="bi bi-cart-plus me-2"></i>Products
          </h6>
        </div>
        <div class="card-body p-0">
          <div class="erp-invoice-product-wrap table-responsive erp-table-compact">
            <table class="table table-hover mb-0 si-table" id="productTable">
              <thead>
                <tr>
                  <th style="width: 42%">Product / Variant</th>
                  <th style="width: 12%" class="text-center">Qty</th>
                  <th style="width: 15%" class="text-end">Rate</th>
                  <th style="width: 12%" class="text-end">Discount %</th>
                  <th style="width: 16%" class="text-end">Amount</th>
                  <th style="width: 10%" class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <div class="position-relative">
                      <div class="d-flex gap-2 align-items-start product-variant-line">
                        <div class="flex-grow-1">
                          <input type="text" 
                                 class="form-control form-control-sm productSearch" 
                                 placeholder="Type to search product..."
                                 autocomplete="off">
                          <select class="form-select form-select-sm d-none productCode">
                            <option value="">Select Product</option>
                            @php
                              $uniqueProducts = $products->unique('productCode');
                            @endphp
                            @foreach($uniqueProducts as $product)
                              <option value="{{ $product->productCode }}" 
                                      data-rate="{{ $product->saleRate }}"
                                      data-cost="{{ $product->costRate }}"
                                      data-name="{{ $product->productName }}"
                                      data-company="{{ $product->companyName }}">
                                {{ $product->productName }} - {{ $product->companyName }}
                              </option>
                            @endforeach
                          </select>
                          <div class="productDropdown dropdown-menu" style="display: none;"></div>
                        </div>
                        <div class="variantWrapper d-none" style="min-width:150px;">
                          <select class="form-select form-select-sm variantSelect">
                            <option value="">Variant</option>
                          </select>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td><input type="number" class="form-control form-control-sm text-center qty" value="1" min="1" placeholder="Qty"></td>
                  <td><input type="number" class="form-control form-control-sm text-end rate" value="" step="0.01" placeholder="Rate"></td>
                  <td><input type="number" class="form-control form-control-sm text-end discount" value="" step="0.01" min="0" max="100" placeholder="Disc %"></td>
                  <td><input type="text" class="form-control form-control-sm text-end amount" readonly value="" placeholder="Amount"></td>
                  <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                      <button type="button" class="btn btn-success" onclick="addRow(this)" title="Add Row">
                        <i class="bi bi-plus-lg"></i>
                      </button>
                      <button type="button" class="btn btn-danger btnDeleteRow" title="Delete Row">
                        <i class="bi bi-trash3"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="sale-invoice-split">
        <div class="card si-card border-0" id="transportCard">
          <div class="card-header si-card-head py-2">
            <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-truck me-2"></i>Transport</h6>
          </div>
          <div class="card-body p-2 p-md-3">
            <div class="sale-invoice-extra">
              <div class="sale-field-full">
                <label class="form-label si-label" for="transportCode">Transport</label>
                <select class="form-select form-select-sm" id="transportCode">
                  <option value="">Select transport</option>
                  @foreach($transports as $transport)
                    @php
                      $transportLabel = trim((string) ($transport->bussnessName ?: $transport->custName));
                    @endphp
                    <option value="{{ $transport->custCode }}"
                            data-name="{{ $transportLabel }}"
                            data-business="{{ $transport->bussnessName ?? '' }}">
                      {{ $transportLabel }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="form-label si-label" for="vehicleNumber">Vehicle Number</label>
                <input type="text" class="form-control form-control-sm" id="vehicleNumber" autocomplete="off">
              </div>
              <div>
                <label class="form-label si-label" for="dispatchNo">Dispatch No</label>
                <input type="text" class="form-control form-control-sm" id="dispatchNo" autocomplete="off">
              </div>
              <div class="transport-calc">
                <div>
                  <label class="form-label si-label" for="transportWeight">Weight (KG)</label>
                  <input type="number" class="form-control form-control-sm" id="transportWeight" step="0.01" min="0" placeholder="0">
                  <small class="text-muted" id="transportWeightHint">Auto from KG items. You can change it.</small>
                </div>
                <div>
                  <label class="form-label si-label" for="transportRate">Rate</label>
                  <input type="number" class="form-control form-control-sm" id="transportRate" step="0.01" min="0" placeholder="0">
                </div>
                <div>
                  <label class="form-label si-label" for="transportAmount">Amount</label>
                  <input type="text" class="form-control form-control-sm" id="transportAmount" readonly value="">
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card si-card border-0">
          <div class="card-header si-card-head py-2">
            <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-calculator me-2"></i>Invoice Summary</h6>
          </div>
          <div class="card-body p-2 p-md-3">
            <div class="sale-invoice-summary">
              <div>
                <label class="form-label si-label small">No. of Items</label>
                <input type="number" class="form-control form-control-sm" id="noOfItems" readonly value="1">
              </div>
              <div>
                <label class="form-label si-label small">Invoice Total</label>
                <input type="text" class="form-control form-control-sm fw-bold" id="invoiceTotal" readonly value="0.00">
              </div>
              <div class="sale-field-full">
                <label class="form-label si-label small">Invoice Discount %</label>
                <input type="number" class="form-control form-control-sm" id="invoiceDiscount" value="0" step="0.01" min="0" max="100">
              </div>
              <div class="sale-field-full">
                <label class="form-label si-label small text-success">Grand Total</label>
                <input type="text" class="form-control fw-bold text-success si-grand" id="grandTotal" readonly value="0.00">
              </div>
              <input type="hidden" id="corten" value="0">
              <input type="hidden" id="biltyExpanse" value="0">
              <input type="hidden" id="loaderExpanse" value="0">
              <input type="hidden" id="biltyBalance" value="0">
              <div class="sale-field-full sale-save-desktop">
                <button type="button" class="btn btn-save-purchase w-100 js-invoice-save" id="btnSaveInvoice">
                  <i class="bi bi-save me-2"></i>Save Purchase
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="erp-invoice-sticky-actions d-lg-none" role="toolbar" aria-label="Purchase invoice actions">
    <button type="button" class="btn btn-outline-secondary flex-shrink-0" onclick="addRow()">
      <i class="bi bi-plus-lg me-1"></i>Add line
    </button>
    <button type="button" class="btn btn-save-purchase flex-grow-1 js-invoice-save">
      <i class="bi bi-save me-1"></i>Save purchase
    </button>
  </div>

</main>

<script>
$(document).ready(function() {
  function fmt(n) { return parseFloat(n || 0).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
  
  // Map of productCode => variants
  window.productVariants = {
    @php
      $uniqueProducts = $products->unique('productCode');
    @endphp
    @foreach($uniqueProducts as $product)
      '{{ $product->productCode }}': [
        @foreach($product->variants as $variant)
        {
          id: {{ $variant->id }},
          name: '{{ $variant->variantName }}',
          value: '{{ $variant->variantValue }}',
          additionalPrice: {{ $variant->additionalPrice ?? 0 }},
          additionalCost: {{ $variant->additionalCost ?? 0 }},
          stockQty: {{ $variant->stockQty ?? 0 }}
        },
        @endforeach
      ],
    @endforeach
  };
  const productVariants = window.productVariants;

  // ---- SUPPLIER SEARCH WITH DROPDOWN ----
  const $supplierSearch = $('#supplierSearch');
  const $supplierSelect = $('#supplierCode');
  const $supplierDropdown = $('#supplierDropdown');
  let allSuppliers = [];
  
  // Build supplier array from select options
  $supplierSelect.find('option').each(function() {
    const $this = $(this);
    if($this.val()) {
      const business = String($this.attr('data-business') || '');
      const name = String($this.attr('data-name') || '');
      const city = String($this.attr('data-city') || '');
      const label = String($this.attr('data-label') || $this.text() || '');
      allSuppliers.push({
        code: $this.val(),
        name: name,
        business: business,
        city: city,
        balance: $this.attr('data-balance') || 0,
        label: label,
        display: label
      });
    }
  });

  function supplierLabel(s) {
    const biz = (s.business || s.name || '').trim();
    const city = (s.city || '').trim();
    return city ? `${biz} — ${city}` : biz;
  }
  
  // Filter and display suppliers
  function filterSuppliers(searchTerm) {
    const term = searchTerm.toLowerCase().trim();
    if(!term) {
      $supplierDropdown.hide().empty();
      return;
    }
    
    const filtered = allSuppliers.filter(s => 
      s.name.toLowerCase().includes(term) ||
      s.business.toLowerCase().includes(term) ||
      s.city.toLowerCase().includes(term) ||
      s.code.toLowerCase().includes(term) ||
      s.label.toLowerCase().includes(term)
    );
    
    if(filtered.length === 0) {
      $supplierDropdown.html('<div class="dropdown-item text-muted">No suppliers found</div>').show();
      return;
    }
    
    let html = '';
    filtered.slice(0, 10).forEach((s, idx) => {
      const title = (s.business || s.name || '').trim();
      const sub = [s.city, s.name && s.business ? s.name : ''].filter(Boolean).join(' · ');
      html += `<a class="dropdown-item ${idx===0?'active':''}" href="#" data-code="${s.code}" data-label="${supplierLabel(s).replace(/"/g, '&quot;')}">
        <div>
          <strong>${title}</strong>
          <div class="text-muted" style="font-size: 0.75rem;">${sub || '—'}</div>
        </div>
        <span class="badge bg-info">${s.code}</span>
      </a>`;
    });
    
    $supplierDropdown.html(html).show();
  }
  
  // Supplier search input events
  $supplierSearch.on('input', function() {
    filterSuppliers($(this).val());
  });
  
  $supplierSearch.on('focus', function() {
    if($(this).val()) filterSuppliers($(this).val());
  });
  
  $supplierSearch.on('blur', function() {
    setTimeout(() => $supplierDropdown.hide(), 200);
  });
  
  // Keyboard navigation in supplier dropdown
  $supplierSearch.on('keydown', function(e) {
    const $items = $supplierDropdown.find('.dropdown-item');
    const $active = $items.filter('.active');
    let newIndex = -1;
    
    if(e.key === 'ArrowDown') {
      e.preventDefault();
      if($items.length === 0) return;
      if($active.length === 0) newIndex = 0;
      else newIndex = Math.min($items.index($active) + 1, $items.length - 1);
      $items.removeClass('active').eq(newIndex).addClass('active');
    }
    else if(e.key === 'ArrowUp') {
      e.preventDefault();
      if($active.length === 0) return;
      newIndex = Math.max($items.index($active) - 1, 0);
      $items.removeClass('active').eq(newIndex).addClass('active');
    }
    else if(e.key === 'Enter') {
      e.preventDefault();
      e.stopPropagation();
      const $selected = $supplierDropdown.is(':visible') ? $items.filter('.active') : $();
      if($selected.length > 0 && $selected.data('code')) {
        const code = $selected.data('code');
        const sup = allSuppliers.find(s => s.code == code);
        selectSupplier(code, sup ? supplierLabel(sup) : $selected.data('label'));
      } else {
        $('#productTable tbody tr:first .productSearch').focus();
      }
    }
    else if(e.key === 'Escape') {
      $supplierDropdown.hide();
    }
  });
  
  // Click on supplier dropdown item
  $supplierDropdown.on('click', '.dropdown-item', function(e) {
    e.preventDefault();
    e.stopPropagation();
    const code = $(this).data('code');
    const sup = allSuppliers.find(s => s.code == code);
    const display = sup ? supplierLabel(sup) : ($(this).data('label') || $(this).text().trim());
    selectSupplier(code, display);
  });
  
  // Select supplier function
  function selectSupplier(code, displayText) {
    $supplierSearch.val(displayText);
    $supplierSelect.val(code).trigger('change');
    $supplierDropdown.hide();
    setTimeout(function () {
      $('#productTable tbody tr:first .productSearch').focus();
    }, 100);
  }

  // Supplier change -> set city + payable balance and load supplier products
  $('#supplierCode').on('change', function(){
    const supplierCode = $(this).val();
    const sel = $(this).find('option:selected');
    $('#city').val(sel.attr('data-city') || '');

    if(!supplierCode) {
      $('#balance').val('0.00');
      supplierProducts = [];
      $('.productCode').each(function(){
        $(this).val('').trigger('change');
      });
      return;
    }

    $.get(`{{ url('/api/party-balance') }}/${supplierCode}`)
      .done(res => {
        $('#balanceFieldLabel').text(res.balance_label || 'Payable Balance');
        $('#balance').val(fmt(res.balance ?? 0));
      })
      .fail(() => $('#balance').val(fmt(sel.attr('data-balance') || 0)));

    updateProductsForSupplier(supplierCode);
  });

  // Auto focus supplier search
  setTimeout(() => { $supplierSearch.focus(); }, 200);

  // ---- PRODUCT SEARCH WITH DROPDOWN ----
  // Build product array directly from Blade data
  let allProducts = [
    @php
      $uniqueProducts = $products->unique('productCode');
    @endphp
    @foreach($uniqueProducts as $product)
    {
      code: @json($product->productCode),
      name: @json($product->productName),
      company: @json($product->companyName),
      rate: {{ (float) $product->costRate }},
      cost: {{ (float) $product->costRate }},
      kgEach: {{ (float) $product->kgEach() }},
      image: @json($product->image_url),
      display: @json(trim($product->productName.' - '.$product->companyName)),
      hasVariants: {{ $product->variants->count() > 0 ? 'true' : 'false' }}
    },
    @endforeach
  ];
  
  // This will hold supplier-specific products when a supplier is selected
  let supplierProducts = [];
  let selectedSupplierCode = '';
  
  // Update products based on selected supplier
  function updateProductsForSupplier(supplierCode) {
    selectedSupplierCode = supplierCode;
    
    if (!supplierCode) {
      supplierProducts = [];
      return;
    }
    
    // Fetch products for this supplier from customer_product_rates
    $.get(`{{ url('/purchase/invoice/supplier-products') }}/${encodeURIComponent(supplierCode)}`)
      .done(function(data) {
        // Transform controller response to match our format
        supplierProducts = (data.products || []).map(p => ({
          code: p.productCode,
          name: p.productName,
          company: p.companyName,
          rate: p.specialRate || p.purchaseRate,
          cost: p.specialRate || p.purchaseRate,
          display: p.productName + ' - ' + p.companyName,
          variants: p.variants || [],  // Store variant info
          hasSpecialRate: p.hasSpecialRate || false
        }));
        console.log('Supplier products loaded:', supplierProducts.length);
      })
      .fail(function(err) {
        supplierProducts = [];
        console.log('Failed to load supplier products:', err);
      });
  }
  window.updateProductsForSupplier = updateProductsForSupplier;
  
  // Setup product search for a row
  function setupProductSearch($row) {
    const $searchInput = $row.find('.productSearch');
    const $productSelect = $row.find('.productCode');
    const $dropdown = $row.find('.productDropdown');

    function hideProductDropdown() {
      if (typeof erpResetFloatingDropdown === 'function') {
        erpResetFloatingDropdown($dropdown[0]);
      }
      $dropdown.hide();
    }

    function syncProductDropdownPosition() {
      if (typeof erpPositionFloatingDropdown === 'function' && $dropdown.is(':visible')) {
        erpPositionFloatingDropdown($searchInput[0], $dropdown[0]);
      }
    }
    
    // Filter products - use supplier products if supplier is selected, otherwise all products
    function filterProducts(searchTerm) {
      const term = searchTerm.toLowerCase().trim();
      if(!term) {
        $dropdown.empty();
        hideProductDropdown();
        return;
      }
      
      // If supplier is selected, use ONLY supplier products (filtered list)
      // If supplier is NOT selected, use all products
      let productsToFilter = [];
      if (selectedSupplierCode && supplierProducts.length > 0) {
        productsToFilter = supplierProducts;
      } else if (selectedSupplierCode && supplierProducts.length === 0) {
        // Supplier selected but has no products
        $dropdown.html('<div class="dropdown-empty text-muted px-3 py-2">No products available for this supplier</div>').show();
        syncProductDropdownPosition();
        return;
      } else {
        // No supplier selected, show all products
        productsToFilter = allProducts;
      }
      
      const filtered = productsToFilter.filter(p => 
        p.name.toLowerCase().includes(term) ||
        p.company.toLowerCase().includes(term) ||
        p.code.toLowerCase().includes(term)
      );
      
      if(filtered.length === 0) {
        $dropdown.html('<div class="dropdown-empty text-muted px-3 py-2">No products found</div>').show();
        syncProductDropdownPosition();
        return;
      }
      
      let html = '';
      filtered.slice(0, 10).forEach((p, idx) => {
        const rateDisplay = supplierProducts.length > 0 ? `Rs.${parseFloat(p.rate).toFixed(2)}` : '';
        const thumb = p.image
          ? `<img src="${p.image}" alt="" class="product-search-thumb rounded border">`
          : `<span class="product-search-thumb product-search-thumb-empty rounded border d-inline-flex align-items-center justify-content-center bg-light text-muted"><i class="bi bi-image"></i></span>`;
        html += `<a class="dropdown-item d-flex align-items-center gap-2 ${idx===0?'active':''}" href="#" data-code="${p.code}" data-rate="${p.rate}">
          ${thumb}
          <div class="flex-grow-1 min-w-0">
            <strong class="d-block text-truncate">${p.name}</strong>
            <div class="text-muted small text-truncate">${p.company || ''}</div>
          </div>
          ${rateDisplay ? `<span class="badge bg-info flex-shrink-0">${rateDisplay}</span>` : ''}
        </a>`;
      });
      
      $dropdown.html(html).show();
      syncProductDropdownPosition();
    }
    
    // Search input events — clear selection when text changes (Sale Invoice parity)
    $searchInput.on('input', function() {
      if ($productSelect.val()) {
        $productSelect.val('');
      }
      filterProducts($(this).val());
    });
    
    $searchInput.on('focus', function() {
      if($(this).val()) filterProducts($(this).val());
    });
    
    $searchInput.on('blur', function() {
      setTimeout(hideProductDropdown, 200);
    });
    
    // Keyboard navigation
    $searchInput.on('keydown', function(e) {
      const $items = $dropdown.find('.dropdown-item[data-code]');
      const $active = $items.filter('.active');
      let newIndex = -1;
      
      if(e.key === 'ArrowDown') {
        e.preventDefault();
        if($items.length === 0) return;
        if($active.length === 0) newIndex = 0;
        else newIndex = Math.min($items.index($active) + 1, $items.length - 1);
        $items.removeClass('active').eq(newIndex).addClass('active');
        syncProductDropdownPosition();
      }
      else if(e.key === 'ArrowUp') {
        e.preventDefault();
        if($active.length === 0) return;
        newIndex = Math.max($items.index($active) - 1, 0);
        $items.removeClass('active').eq(newIndex).addClass('active');
        syncProductDropdownPosition();
      }
      else if(e.key === 'Enter') {
        e.preventDefault();
        e.stopPropagation();
        if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
        const $selected = $dropdown.is(':visible') ? $items.filter('.active') : $();
        const typed = String($(this).val() || '').trim();
        if($selected.length > 0 && $selected.data('code')) {
          selectProduct($row, $selected.data('code'), $selected.text().trim(), $selected.data('rate'));
        } else if ($productSelect.val()) {
          if (typeof window.focusNextCashField === 'function') {
            window.focusNextCashField(this);
          } else {
            $row.find('.qty').focus().select();
          }
        } else if (!typed) {
          hideProductDropdown();
          const transport = document.getElementById('transportCode');
          if (transport) transport.focus();
        } else {
          showWarning('Product not found', 'No product matches your search. Clear the field or try another name/code.');
          hideProductDropdown();
          $(this).focus();
        }
      }
      else if(e.key === 'Escape') {
        hideProductDropdown();
      }
    });
    
    // Click on dropdown item
    $dropdown.on('click', '.dropdown-item', function(e) {
      e.preventDefault();
      e.stopPropagation();
      const code = $(this).data('code');
      const display = $(this).text().trim();
      const rate = $(this).data('rate');
      selectProduct($row, code, display, rate);
    });
  }
  
  // Select product function
  function productAlreadyOnInvoice(code, $row) {
    let used = false;
    $('#productTable tbody tr').each(function () {
      if (this === $row[0]) return;
      if (String($(this).find('.productCode').val() || '') === String(code)) used = true;
    });
    return used;
  }

  function selectProduct($row, code, displayText, rate) {
    if (productAlreadyOnInvoice(code, $row)) {
      if (typeof showWarning === 'function') showWarning('Duplicate product', 'This product is already added.');
      if (!$row.find('.productCode').val()) $row.find('.productSearch').val('');
      const $dup = $row.find('.productDropdown');
      if (typeof erpResetFloatingDropdown === 'function') erpResetFloatingDropdown($dup[0]);
      $dup.hide();
      $row.find('.productSearch').focus();
      return;
    }
    $row.find('.productSearch').val(displayText);
    const productMaster = allProducts.find(function (p) { return String(p.code) === String(code); });
    $row.data('kgEach', productMaster ? (parseFloat(productMaster.kgEach) || 0) : 0);
    $row.find('.productCode').val(code).trigger('change');
    $row.find('.rate').val(rate);
    const $pdd = $row.find('.productDropdown');
    if (typeof erpResetFloatingDropdown === 'function') {
      erpResetFloatingDropdown($pdd[0]);
    }
    $pdd.hide();

    // Store base rate
    $row.data('baseRate', rate);
    $row.data('currentRate', rate);
    const baseCost = $row.find('.productCode option:selected').data('cost') || 0;
    $row.data('baseCost', baseCost);

    // Find product in supplier products to get variants with rates
    const supplierProduct = supplierProducts.find(p => p.code === code);
    let variantsToUse = productVariants[code] || [];
    
    // If we have supplier-specific variants, filter to show only those
    if (supplierProduct && supplierProduct.variants && supplierProduct.variants.length > 0) {
      const supplierVariantIds = supplierProduct.variants.map(v => v.id);
      variantsToUse = variantsToUse.filter(v => supplierVariantIds.includes(v.id));
    }
    
    const $variantWrapper = $row.find('.variantWrapper');
    const $variantSelect = $row.find('.variantSelect');
    $variantSelect.empty().append('<option value="">Select Variant</option>');
    
    if(variantsToUse.length > 0){
      variantsToUse.forEach(v => {
        const disabled = v.stockQty <= 0 ? 'disabled' : '';
        
        // Check if this variant has a special rate in supplier products
        let variantRate = v.additionalCost > 0 ? v.additionalCost : null;
        if (supplierProduct && supplierProduct.variants) {
          const supplierVariant = supplierProduct.variants.find(sv => sv.id === v.id);
          if (supplierVariant) {
            variantRate = supplierVariant.specialRate;
          }
        }
        
        const text = variantRate ? `${v.name}: ${v.value} (Rs.${variantRate})` : `${v.name}: ${v.value}`;
        $variantSelect.append(`<option value="${v.id}" ${disabled} data-variant-rate="${variantRate || 0}" data-stock="${v.stockQty}" title="Stock: ${v.stockQty}">${text}</option>`);
      });
      $variantWrapper.removeClass('d-none');
      // Add stock badges
      $variantSelect.find('option').each(function(){
        const stock = $(this).data('stock');
        if(stock !== undefined && $(this).val()){
          if(stock <= 0){ $(this).text($(this).text() + ' [OUT]'); }
          else { $(this).text($(this).text() + ' ['+stock+']'); }
        }
      });
    } else {
      $variantWrapper.addClass('d-none');
    }
    
    function applyRowRate(newRate) {
      const r = parseFloat(newRate) || 0;
      $row.find('.rate').val(r);
      $row.data('baseRate', r);
      $row.data('currentRate', r);
      const qty = parseFloat($row.find('.qty').val()) || 1;
      const discount = parseFloat($row.find('.discount').val()) || 0;
      const amount = (qty * r) - ((qty * r * discount) / 100);
      $row.find('.amount').val(amount.toFixed(2));
      updateTotals();
    }

    applyRowRate(rate);

    // Load special rate from server when supplier + product are set
    if (selectedSupplierCode && code) {
      $.get(`{{ url('/purchase/invoice/product') }}/${encodeURIComponent(code)}`, { supplierCode: selectedSupplierCode })
        .done(function (data) {
          if (data.hasSpecialRate && data.purchaseRate != null) {
            applyRowRate(data.purchaseRate);
          }
          if (data.variants && data.variants.length > 0) {
            const $variantSelect = $row.find('.variantSelect');
            $variantSelect.find('option:not(:first)').each(function () {
              const vid = parseInt($(this).val(), 10);
              const apiVariant = data.variants.find(v => v.id === vid);
              if (apiVariant && apiVariant.hasSpecialRate && apiVariant.specialRate != null) {
                const label = $(this).text().replace(/\(Rs\.[^)]+\)/, `(Rs.${apiVariant.specialRate})`);
                $(this).text(label).attr('data-variant-rate', apiVariant.specialRate);
              }
            });
          }
        });
    }

    // Move to qty
    setTimeout(() => {
      $row.find('.qty').focus().select();
    }, 100);
  }
  
  // Variant change handler
  $(document).on('change', '.variantSelect', function(){
    const $row = $(this).closest('tr');
    const baseRate = parseFloat($row.data('baseRate')) || 0;
    const selectedOption = $(this).find('option:selected');
    const variantRate = parseFloat(selectedOption.data('variant-rate')) || 0;
    const rateCell = $row.find('.rate');
    
    if($(this).val() && variantRate > 0) {
      rateCell.val(variantRate.toFixed(2));
      $row.data('currentRate', variantRate);
    } else {
      rateCell.val(baseRate.toFixed(2));
      $row.data('currentRate', baseRate);
    }
    
    calculateRowAmount($row);
  });

  // Setup first row
  const firstRow = $('#productTable tbody tr:first');
  setupProductSearch(firstRow);

  $(window).on('resize.purchaseInvoiceProductDD', function() {
    $('#productTable .productDropdown').filter(':visible').each(function() {
      const $inp = $(this).closest('tr').find('.productSearch');
      if ($inp.length && typeof erpPositionFloatingDropdown === 'function') {
        erpPositionFloatingDropdown($inp[0], this);
      }
    });
  });

  // Make functions global so addRow can access them
  window.setupProductSearch = setupProductSearch;
  window.selectProduct = selectProduct;
  window.allProducts = allProducts;

  window.focusNextCashField = function (current) {
    const fields = Array.from(document.querySelectorAll('#main input, #main select, #main textarea, #productTable button[title="Add Row"], #btnSaveInvoice')).filter(function (el) {
      if (el.disabled || el.readOnly) return false;
      if ((el.type || '') === 'hidden') return false;
      if (el.closest('.d-none, .productDropdown, .modal')) return false;
      return el.offsetParent !== null || el === document.activeElement;
    });
    const index = fields.indexOf(current);
    const next = index >= 0 ? fields[index + 1] : null;
    const target = next || document.getElementById('btnSaveInvoice');
    if (!target) return;
    target.focus();
    if (target.tagName === 'INPUT' && target.type !== 'date' && typeof target.select === 'function') target.select();
  };

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    const el = e.target;
    if (!el || !el.closest || !el.closest('#main')) return;
    if (el.tagName === 'TEXTAREA' && e.shiftKey) return;
    if (el.classList && (el.classList.contains('productSearch') || el.id === 'supplierSearch')) return;
    const tag = el.tagName;
    const isField = tag === 'INPUT' || tag === 'SELECT' || tag === 'TEXTAREA' || el.id === 'btnSaveInvoice';
    if (!isField) return;
    if (el.id === 'btnSaveInvoice') {
      e.preventDefault();
      el.click();
      return;
    }
    e.preventDefault();
    window.focusNextCashField(el);
  });

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    const el = e.target;
    if (!el || !el.closest) return;
    const row = el.closest('#productTable tr');
    if (!row) return;
    if (el.classList.contains('qty')) {
      const code = row.querySelector('.productCode');
      if (!code || !code.value) {
        e.preventDefault();
        e.stopPropagation();
        const search = row.querySelector('.productSearch');
        if (search) search.focus();
        return;
      }
      const variantWrap = row.querySelector('.variantWrapper');
      const variant = row.querySelector('.variantSelect');
      if (variantWrap && !variantWrap.classList.contains('d-none') && variant && !variant.value) {
        e.preventDefault();
        e.stopPropagation();
        variant.focus();
      }
    }
  }, true);

  $(document).on('input', '.qty', function () {
    const row = $(this).closest('tr');
    if (!row.find('.productCode').val()) {
      row.find('.productSearch').focus();
      return;
    }
    const wrap = row.find('.variantWrapper');
    if (wrap.length && !wrap.hasClass('d-none') && !row.find('.variantSelect').val()) {
      row.find('.variantSelect').focus();
    }
  });

  function calculateRowAmount(row) {
    const qty = parseFloat(row.find('.qty').val()) || 0;
    const displayedRate = parseFloat(row.find('.rate').val()) || 0;
    const discount = parseFloat(row.find('.discount').val()) || 0;
    const amount = (qty * displayedRate) * (1 - discount / 100);
    row.find('.amount').val(amount.toFixed(2));
    updateTotals();
  }

  function updateTotals() {
    let total = 0, itemCount = 0;
    $('#productTable tbody tr').each(function(){
      const amount = parseFloat($(this).find('.amount').val()) || 0;
      const code = $(this).find('.productCode').val();
      if (code) itemCount++;
      total += amount;
    });
    $('#noOfItems').val(itemCount);
    $('#invoiceTotal').val(total.toFixed(2));
    updateGrandTotal();
    applyTransportWeight();
  }

  let transportWeightEdited = false;

  function transportKgTotal() {
    let kg = 0;
    $('#productTable tbody tr').each(function () {
      if (!$(this).find('.productCode').val()) return;
      const qty = parseFloat($(this).find('.qty').val()) || 0;
      const each = parseFloat($(this).data('kgEach')) || 0;
      kg += qty * each;
    });
    return Math.round(kg * 100) / 100;
  }

  function applyTransportWeight() {
    const kg = transportKgTotal();
    const hint = document.getElementById('transportWeightHint');
    if (hint) {
      hint.textContent = kg > 0
        ? ('Auto from KG items: ' + kg + ' kg. You can change it.')
        : 'Auto from KG items. You can change it.';
    }
    if (!transportWeightEdited) {
      $('#transportWeight').val(kg > 0 ? kg : '');
    }
    calcTransportAmount();
  }

  function calcTransportAmount() {
    const rateRaw = $('#transportRate').val();
    if (rateRaw === '' || rateRaw === null) {
      $('#transportAmount').val('');
      return;
    }
    const weight = parseFloat($('#transportWeight').val()) || 0;
    const rate = parseFloat(rateRaw) || 0;
    $('#transportAmount').val((Math.round(weight * rate * 100) / 100).toFixed(2));
  }

  $('#transportWeight').on('input', function () {
    transportWeightEdited = $(this).val().trim() !== '';
    calcTransportAmount();
  });
  $('#transportRate').on('input', calcTransportAmount);

  function updateGrandTotal() {
    const invoiceTotal = parseFloat($('#invoiceTotal').val()) || 0;
    const invoiceDiscount = parseFloat($('#invoiceDiscount').val()) || 0;
    const grandTotal = invoiceTotal * (1 - invoiceDiscount / 100);
    $('#grandTotal').val(grandTotal.toFixed(2));
  }

  function calculateBiltyBalance() {
    const corten = parseFloat($('#corten').val()) || 0;
    const biltyExpanse = parseFloat($('#biltyExpanse').val()) || 0;
    const loaderExpanse = parseFloat($('#loaderExpanse').val()) || 0;
    const biltyBalance = (corten * biltyExpanse) - loaderExpanse;
    $('#biltyBalance').val(biltyBalance.toFixed(2));
  }

  $(document).on('input', '.qty, .rate, .discount', function(){ calculateRowAmount($(this).closest('tr')); });
  $('#invoiceDiscount').on('input', updateGrandTotal);

  // Delete row handler
  $(document).on('click', '.btnDeleteRow', function(){
    const tbody = $('#productTable tbody');
    if(tbody.find('tr').length > 1) {
      $(this).closest('tr').remove();
      updateTotals();
    } else {
      showWarning('At least one product row is required');
    }
  });

  // Save purchase invoice (desktop + mobile sticky)
  $(document).on('click', '.js-invoice-save', function(){
    const supplierCode = $('#supplierCode').val();
    if(!supplierCode){ showWarning('Please select a supplier'); return; }

    const items = []; let hasError = false;
    $('#productTable tbody tr').each(function(){
      const code = $(this).find('.productCode').val(); if(!code) return;
      const qty = parseInt($(this).find('.qty').val()); if(qty <= 0){ showWarning('Quantity must be greater than 0'); hasError = true; return false; }
      const name = $(this).find('.productCode option:selected').text();
      const variantId = $(this).find('.variantSelect').val() || null;
      const $variantOpt = $(this).find('.variantSelect option:selected');
      const variantNameValue = $variantOpt.length && variantId ? $variantOpt.text() : '';
      const variantRate = $variantOpt.data('variant-rate') || 0;
      let variantName = '';
      let variantValue = '';
      if(variantNameValue){
        const parts = variantNameValue.split(':');
        if(parts.length >= 2){
          variantName = parts[0].trim();
          variantValue = parts[1].split('[')[0].split('(')[0].trim();
        }
      }
      items.push({ 
        productCode: code, productName: name, 
        variant_id: variantId,
        variant_name: variantName,
        variant_value: variantValue,
        variant_added_price: parseFloat(variantRate) || 0,
        qty: qty, rate: parseFloat($(this).find('.rate').val()), 
        discount: parseFloat($(this).find('.discount').val()) || 0, 
        amount: parseFloat($(this).find('.amount').val()) 
      });
    });
    if (hasError) return; if (items.length === 0){ showWarning('Please add at least one product'); return; }

    const $supOpt = $('#supplierCode option:selected');
    const supplierName = ($supOpt.data('business') || $supOpt.data('name') || $supOpt.text() || '').toString().trim();
    const data = {
      _token: '{{ csrf_token() }}',
      invoiceNo: $('#invoiceNo').val(), invoiceDate: $('#invoiceDate').val(),
      supplierCode: supplierCode, supplierName: supplierName,
      city: $('#city').val(), balance: parseFloat($('#balance').val().replace(/,/g, '')) || 0,
      transportCode: $('#transportCode').val() || null,
      transportName: $('#transportCode option:selected').data('name') || null,
      vehicleNumber: $('#vehicleNumber').val() || null,
      dispatchNo: $('#dispatchNo').val() || null,
      weight: $('#transportWeight').val() === '' ? null : (parseFloat($('#transportWeight').val()) || 0),
      transportRate: $('#transportRate').val() === '' ? null : (parseFloat($('#transportRate').val()) || 0),
      transportAmount: $('#transportAmount').val() === '' ? null : (parseFloat($('#transportAmount').val()) || 0),
      corten: parseInt($('#corten').val(), 10) || 0,
      biltyExpanse: parseFloat($('#biltyExpanse').val()) || 0,
      loaderExpanse: parseFloat($('#loaderExpanse').val()) || 0,
      biltyBalance: parseFloat($('#biltyBalance').val()) || 0,
      paid_amount: 0,
      noOfItems: parseInt($('#noOfItems').val()), invoiceTotal: parseFloat($('#invoiceTotal').val()),
      invoiceDiscount: parseFloat($('#invoiceDiscount').val()) || 0, grandTotal: parseFloat($('#grandTotal').val()),
      items: items
    };

    $.ajax({
      url: '{{ url("/purchase/invoice/store") }}', method: 'POST', data: data,
      success: function(res){ 
        if(res.success){ 
          showSuccess('Purchase saved successfully!', `Invoice No: ${res.invoiceNo}`); 
          // Open preview in new tab
          if(res.previewUrl) {
            setTimeout(() => {
              window.open(res.previewUrl, '_blank');
              location.reload();
            }, 1500);
          } else {
            setTimeout(() => location.reload(), 2000);
          }
        } else { 
          showError('Failed to save', res.message || ''); 
        } 
      },
      error: function(xhr){ console.error('Error:', xhr); showError('Failed to save', xhr.responseJSON?.message || ''); }
    });
  });
});

function addRow(btn) {
  const row = btn ? $(btn).closest('tr') : $('#productTable tbody tr').last();
  if (row.length && !row.find('.productCode').val()) {
    const transport = document.getElementById('transportCode');
    if (transport) transport.focus();
    return;
  }
  const variantWrap = row.find('.variantWrapper');
  if (variantWrap.length && !variantWrap.hasClass('d-none') && !row.find('.variantSelect').val()) {
    row.find('.variantSelect').focus();
    return;
  }
  const tbody = $('#productTable tbody');
  const newRowHTML = `
    <tr>
      <td>
        <div class="position-relative">
          <div class="d-flex gap-2 align-items-start product-variant-line">
            <div class="flex-grow-1">
              <input type="text" 
                     class="form-control form-control-sm productSearch" 
                     placeholder="Type to search product..."
                     autocomplete="off">
              <select class="form-select form-select-sm d-none productCode">
                <option value="">Select Product</option>
                @php
                  $uniqueProducts = $products->unique('productCode');
                @endphp
                @foreach($uniqueProducts as $product)
                  <option value="{{ $product->productCode }}" 
                          data-rate="{{ $product->costRate }}"
                          data-cost="{{ $product->costRate }}"
                          data-name="{{ $product->productName }}"
                          data-company="{{ $product->companyName }}">
                    {{ $product->productName }} - {{ $product->companyName }}
                  </option>
                @endforeach
              </select>
              <div class="productDropdown dropdown-menu" style="display: none;"></div>
            </div>
            <div class="variantWrapper d-none" style="min-width:150px;">
              <select class="form-select form-select-sm variantSelect">
                <option value="">Variant</option>
              </select>
            </div>
          </div>
        </div>
      </td>
      <td><input type="number" class="form-control form-control-sm text-center qty" value="1" min="1" placeholder="Qty"></td>
      <td><input type="number" class="form-control form-control-sm text-end rate" value="" step="0.01" placeholder="Rate"></td>
      <td><input type="number" class="form-control form-control-sm text-end discount" value="" step="0.01" min="0" max="100" placeholder="Disc %"></td>
      <td><input type="text" class="form-control form-control-sm text-end amount" readonly value="" placeholder="Amount"></td>
      <td class="text-center">
        <div class="btn-group btn-group-sm" role="group">
          <button type="button" class="btn btn-success" onclick="addRow(this)" title="Add Row">
            <i class="bi bi-plus-lg"></i>
          </button>
          <button type="button" class="btn btn-danger btnDeleteRow" title="Delete Row">
            <i class="bi bi-trash3"></i>
          </button>
        </div>
      </td>
    </tr>`;
  const newRow = $(newRowHTML);
  tbody.append(newRow);
  
  // Setup product search for the new row
  if(typeof window.setupProductSearch === 'function') {
    window.setupProductSearch(newRow);
  }
  setTimeout(function () { newRow.find('.productSearch').focus(); }, 100);
}
</script>

@include('partials.purchase.net-rate-modal')
@endsection
