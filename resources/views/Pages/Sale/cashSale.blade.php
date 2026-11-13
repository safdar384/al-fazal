@extends('layout.app')

@section('title', 'Cash Invoice')

@section('content')
@include('partials.sale.erp-styles')

<style>
  .cash-paid-badge {
    background: rgba(255, 255, 255, 0.18);
    border: 1px solid rgba(255, 255, 255, 0.35);
    color: #fff;
    font-size: 0.72rem;
    letter-spacing: 0.04em;
    font-weight: 600;
  }
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
  
  /* Cash invoice — name on receipt only (no customer master) */
  #cashBuyerName, #cashBuyerCity {
    border: 1px solid #d5dde8;
    border-radius: 0.375rem;
    background-color: #f8f9fa;
  }
  #cashBuyerName:focus, #cashBuyerCity:focus {
    border-color: #1a4a8a;
    background-color: #fff;
    box-shadow: 0 0 0 0.2rem rgba(15, 39, 71, 0.12);
    outline: 0;
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

  .product-search-wrap {
    position: relative;
  }
  .productSearch.has-product {
    padding-right: 1.85rem;
  }
  .btn-clear-product {
    position: absolute;
    right: 4px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 5;
    width: 22px;
    height: 22px;
    padding: 0;
    border: none;
    border-radius: 50%;
    background: transparent;
    color: #6b7c8d;
    display: none;
    align-items: center;
    justify-content: center;
    line-height: 1;
    font-size: 0.75rem;
  }
  .btn-clear-product:hover {
    background: #fde8ea;
    color: #dc3545;
  }
  
  /* Product dropdown: positioned fixed via erpPositionFloatingDropdown (global.js) so list is not clipped by horizontal scroll */
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
    min-width: 100%;
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
  
  .productDropdown .dropdown-item .text-muted {
    font-size: 0.8rem;
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
  
  #productTable td {
    position: relative;
    overflow: visible;
  }

  .badge.bg-info {
    background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
  }
  
  .badge.bg-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
  }
  
  /* Navy ERP shell */
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
  .erp-invoice-page .form-control:focus,
  .erp-invoice-page .form-select:focus {
    border-color: #1a4a8a;
    box-shadow: 0 0 0 0.2rem rgba(15, 39, 71, 0.12);
  }
  #bankPaymentsTable thead th {
    background: #e8eef5;
    color: #0f2747;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    border-color: #d5dde8;
  }
  .cash-bank-match {
    background: #f4f7fb !important;
    border-color: #d5dde8 !important;
  }

  .sale-invoice-fields,
  .sale-invoice-extra,
  .sale-invoice-summary,
  .sale-invoice-split {
    display: flex;
    flex-wrap: wrap;
    align-items: stretch;
    gap: clamp(0.4rem, 0.9cqi, 0.9rem);
  }

  .sale-invoice-fields > div,
  .sale-invoice-extra > div,
  .sale-invoice-summary > div {
    flex: 1 1 9rem;
    min-width: min(100%, 8rem);
    max-width: 100%;
  }

  .sale-invoice-fields .sale-field-wide,
  .sale-invoice-extra .sale-field-wide {
    flex: 2 1 14rem;
    min-width: min(100%, 12rem);
  }

  .sale-invoice-extra .sale-field-full,
  .sale-invoice-summary .sale-field-full {
    flex: 1 1 100%;
    min-width: 100%;
  }

  .sale-invoice-summary .sale-summary-row {
    flex: 1 1 100%;
    min-width: 100%;
    display: flex;
    flex-wrap: wrap;
    gap: clamp(0.4rem, 0.9cqi, 0.9rem);
  }

  .sale-invoice-summary .sale-summary-row > div {
    flex: 1 1 8rem;
    min-width: min(100%, 7.5rem);
  }

  .sale-invoice-split > .card {
    flex: 1 1 18rem;
    min-width: min(100%, 16rem);
    max-width: 100%;
  }

  @media print {
    .app-sidebar, .app-header, .app-content-header .breadcrumb, .btn, button, .no-print { display: none !important; }
    body, .app-main { margin: 0; padding: 0; }
    .card { box-shadow: none !important; border: none !important; }
  }
</style>

<main class="app-main erp-invoice-page sale-invoice-fluid" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center gy-2">
        <div class="col-12 col-md-6">
          <h3 class="mb-0 fw-bold" style="color:#0f2747;">
            <i class="bi bi-cash-coin me-2"></i>Cash Invoice
          </h3>
          <p class="text-muted small mb-0">Sale · Walk-in / immediate settlement</p>
        </div>
        <div class="col-12 col-md-6">
          <ol class="breadcrumb float-md-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Sale</span></li>
            <li class="breadcrumb-item active" aria-current="page">Cash Invoice</li>
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
          <h6 class="mb-0 text-white fw-semibold d-flex flex-wrap align-items-center gap-2">
            <span><i class="bi bi-receipt-cutoff me-2"></i>Cash Sale Invoice</span>
            <span class="badge cash-paid-badge">PAID — Immediate settlement</span>
          </h6>
        </div>
        <div class="card-body p-2 p-md-3">
          <div class="sale-invoice-fields">
            <div class="sale-field-wide">
              <label class="form-label fw-semibold si-label">
                <i class="bi bi-person me-1"></i>Name <span class="text-muted fw-normal">(text on invoice only)</span>
              </label>
              <input type="text" class="form-control form-control-sm" id="cashBuyerName" maxlength="255" placeholder="Type buyer / walk-in name…" autocomplete="name">
            </div>
            <div>
              <label class="form-label fw-semibold si-label">City <span class="text-muted fw-normal">(optional)</span></label>
              <input type="text" class="form-control form-control-sm" id="cashBuyerCity" maxlength="255" placeholder="Optional" autocomplete="address-level2">
            </div>
            <div>
              <label class="form-label fw-semibold si-label">Invoice No</label>
              <input type="text" class="form-control form-control-sm" id="invoiceNo" value="{{ $nextInvoiceNo }}" readonly>
            </div>
            <div>
              <label class="form-label fw-semibold si-label">Date</label>
              <input type="date" class="form-control form-control-sm" id="invoiceDate" value="{{ date('Y-m-d') }}">
            </div>
          </div>
        </div>
      </div>

      <!-- Products Grid Card -->
      <div class="card si-card border-0 mb-2">
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
                        <div class="flex-grow-1 product-search-wrap">
                          <input type="text" 
                                 class="form-control form-control-sm productSearch" 
                                 placeholder="Type to search product..."
                                 autocomplete="off">
                          <button type="button" class="btn-clear-product" title="Clear product" aria-label="Clear product">
                            <i class="bi bi-x-lg"></i>
                          </button>
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
                        <div class="variantWrapper d-none">
                          <select class="form-select form-select-sm variantSelect">
                            <option value="">Variant</option>
                          </select>
                        </div>
                      </div>
                      <!-- rateBreakdown removed -->
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

      <!-- Bottom Section: Bank payments & Totals -->
      <div class="sale-invoice-split">
        <!-- Left: Bank payment split -->
        <div class="card si-card border-0">
            <div class="card-header si-card-head py-2">
              <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-bank me-2"></i>Bank details (split payment)</h6>
            </div>
            <div class="card-body p-2">
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-2" id="bankPaymentsTable">
                  <thead>
                    <tr>
                      <th>Bank</th>
                      <th class="text-end" style="width: 28%;">Amount</th>
                      <th style="width: 2.75rem;"></th>
                    </tr>
                  </thead>
                  <tbody id="bankPaymentsBody">
                    <tr class="bank-pay-row">
                      <td>
                        <select class="form-select form-select-sm bank-account-select">
                          <option value="">Select bank</option>
                          @foreach($bankAccounts as $bank)
                            <option value="{{ $bank->bankAccount }}" {{ $bank->bankAccount === 'CASH' ? 'selected' : '' }}>{{ $bank->bankName }} — {{ $bank->bankAccount }}@if($bank->branchCode) ({{ $bank->branchCode }})@endif</option>
                          @endforeach
                        </select>
                      </td>
                      <td>
                        <input type="number" class="form-control form-control-sm text-end bank-amount-input" step="0.01" min="0" placeholder="0.00" value="0.00">
                      </td>
                      <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-remove-bank-row" disabled title="Remove row"><i class="bi bi-x-lg"></i></button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <button type="button" class="btn btn-outline-secondary btn-sm" id="btnAddBankRow">
                <i class="bi bi-plus-lg me-1"></i>Add bank line
              </button>
              <div class="cash-bank-match mt-2 p-2 rounded border small">
                <span class="fw-semibold">Allocated:</span>
                <span id="bankAllocatedTotal" style="color:#1a4a8a;">0.00</span>
                <span class="text-muted mx-2">|</span>
                <span class="fw-semibold">Must match:</span>
                <span id="bankGrandTotalHint">0.00</span>
                <span class="text-muted mx-2">|</span>
                <span class="fw-semibold">Remaining:</span>
                <span id="bankRemainingBalance" class="fw-semibold text-muted" title="Must match minus allocated">0.00</span>
                <span id="bankMatchMsg" class="ms-2 fw-semibold"></span>
              </div>
              <div class="col-12 mt-2">
                <label class="form-label fw-semibold si-label">Comments</label>
                <textarea class="form-control form-control-sm" id="comments" rows="2"></textarea>
              </div>
            </div>
          </div>

        <!-- Right: Totals -->
        <div class="card si-card border-0">
            <div class="card-header si-card-head py-2">
              <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-calculator me-2"></i>Invoice Summary</h6>
            </div>
            <div class="card-body p-2">
              <div class="sale-invoice-summary">
                <div class="sale-summary-row">
                  <div>
                    <label class="form-label fw-semibold si-label">No. of Items</label>
                    <input type="number" class="form-control form-control-sm" id="noOfItems" readonly value="1">
                  </div>
                  <div>
                    <label class="form-label fw-semibold si-label">Invoice Total</label>
                    <input type="text" class="form-control form-control-sm fw-bold" id="invoiceTotal" readonly value="0.00">
                  </div>
                </div>
                <div class="sale-summary-row">
                  <div>
                    <label class="form-label fw-semibold si-label">Customer Discount %</label>
                    <input type="number" class="form-control form-control-sm fw-semibold text-primary" id="summaryCustomerDiscount" value="" step="0.01" min="0" max="100" placeholder="0">
                  </div>
                  <div>
                    <label class="form-label fw-semibold si-label">
                      Discount Amount
                      <i class="bi bi-info-circle" title="Works only when no product line has discount."></i>
                    </label>
                    <input type="number" class="form-control form-control-sm" id="invoiceDiscount" value="" step="0.01" min="0" placeholder="0.00">
                  </div>
                </div>
                <div class="sale-field-full">
                  <small class="text-muted d-block sale-discount-note" id="invoiceDiscountHint">
                    <i class="bi bi-exclamation-circle"></i> If any product has discount, invoice discount is disabled.
                  </small>
                  <small class="text-warning d-none d-block" id="invoiceDiscountLockedNote">
                    <i class="bi bi-lock"></i> Invoice discount locked — product line discount is applied.
                  </small>
                </div>
                <div class="sale-field-full">
                  <label class="form-label fw-semibold si-label text-success">Grand Total</label>
                  <input type="text" class="form-control form-control-sm fw-bold text-success si-grand" id="grandTotal" readonly value="0.00">
                </div>
                <div class="sale-field-full">
                  <button type="button" class="btn w-100 text-white" id="btnSaveInvoice" style="background:#0f2747;border-color:#0f2747;">
                    <i class="bi bi-save me-2"></i>Save Cash Invoice
                  </button>
                </div>
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
$(document).ready(function() {
  let bankManaged = false;
  let freezeBankForDiscount = false;
  let discountBankTimer = null;

  function banksMatchGrand() {
    let allocated = 0;
    $('#bankPaymentsBody tr').each(function() {
      const code = $(this).find('.bank-account-select').val();
      const amt = parseFloat($(this).find('.bank-amount-input').val()) || 0;
      if (code && amt > 0) allocated += amt;
    });
    const grand = parseFloat($('#grandTotal').val()) || 0;
    return Math.abs(allocated - grand) <= 0.02;
  }

  function warnBankAfterDiscount() {
    const grand = (parseFloat($('#grandTotal').val()) || 0).toFixed(2);
    const message = 'Invoice discount changed the Grand Total to ' + grand + '. Set the bank amounts to this total. The invoice will not be saved until the bank matches.';
    if (typeof Swal !== 'undefined') {
      if (Swal.isVisible()) return;
      Swal.fire({
        icon: 'warning',
        title: 'Set the bank',
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#d33'
      }).then(function() {
        const amt = document.querySelector('#bankPaymentsBody .bank-amount-input');
        if (amt) {
          amt.focus();
          if (typeof amt.select === 'function') amt.select();
        }
      });
      return;
    }
    showWarning('Set the bank', message, 4000);
  }
  // Cash invoice: name is text for print only — no customer master / balance.
  setTimeout(() => { $('#cashBuyerName').focus(); }, 200);

  // ---- Product Search with Dropdown ----
  // Build product array directly from Blade data (ensure unique products)
  let allProducts = [
    @php
      $uniqueProducts = $products->unique('productCode');
    @endphp
    @foreach($uniqueProducts as $product)
    {
      code: @json($product->productCode),
      name: @json($product->productName),
      company: @json($product->companyName),
      rate: {{ (float) $product->saleRate }},
      cost: {{ (float) $product->costRate }},
      stockQty: {{ (float) ($product->stockQty ?? 0) }},
      image: @json($product->image_url),
      display: @json(trim($product->productName.' - '.$product->companyName)),
      hasVariants: {{ $product->variants->count() > 0 ? 'true' : 'false' }}
    },
    @endforeach
  ];

  // Map of productCode => variants
  const productVariants = {
    @foreach($products as $product)
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

  window.allProducts = allProducts;
  window.productVariants = productVariants;
  @include('partials.sale.product-duplicate-check')
  
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
    
    // Filter products
    function filterProducts(searchTerm) {
      const term = searchTerm.toLowerCase().trim();
      if(!term) {
        $dropdown.empty();
        hideProductDropdown();
        return;
      }
      
      const filtered = allProducts.filter(p => 
        p.name.toLowerCase().includes(term) ||
        p.company.toLowerCase().includes(term) ||
        p.code.toLowerCase().includes(term)
      );
      
      if(filtered.length === 0) {
        $dropdown.html('<div class="dropdown-item text-muted">No products found</div>').show();
        syncProductDropdownPosition();
        return;
      }
      
      let html = '';
      filtered.slice(0, 10).forEach((p, idx) => {
        let stock = parseFloat(p.stockQty) || 0;
        if (p.hasVariants && productVariants[p.code] && productVariants[p.code].length) {
          stock = productVariants[p.code].reduce((sum, v) => sum + (parseFloat(v.stockQty) || 0), 0);
        }
        const stockClass = stock <= 0 ? 'bg-danger' : (stock <= 5 ? 'bg-warning text-dark' : 'bg-success');
        const thumb = p.image
          ? `<img src="${p.image}" alt="" class="product-search-thumb rounded border" onerror="this.style.display='none'">`
          : `<span class="product-search-thumb product-search-thumb-empty rounded border d-inline-flex align-items-center justify-content-center bg-light text-muted"><i class="bi bi-image"></i></span>`;
        html += `<a class="dropdown-item d-flex align-items-center gap-2 ${idx===0?'active':''}" href="#" data-code="${p.code}" data-rate="${p.rate}">
          ${thumb}
          <div class="flex-grow-1 min-w-0">
            <strong class="d-block text-truncate">${p.name}</strong>
            <div class="text-muted small text-truncate">${p.code}${p.company ? ' · ' + p.company : ''}</div>
          </div>
          <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
            <span class="badge ${stockClass}">Stock: ${Number.isInteger(stock) ? stock : stock.toFixed(2)}</span>
            <span class="badge bg-info">Rs.${parseFloat(p.rate).toFixed(2)}</span>
          </div>
        </a>`;
      });
      
      $dropdown.html(html).show();
      syncProductDropdownPosition();
    }
    
    // Search input events
    $searchInput.on('input', function() {
      filterProducts($(this).val());
    });
    
    $searchInput.on('focus', function() {
      if($(this).val()) filterProducts($(this).val());
    });
    
    // Close dropdown only when clicking outside
    $(document).on('click', function(e) {
      if(!$(e.target).closest($searchInput).length && !$(e.target).closest($dropdown).length) {
        hideProductDropdown();      }
    });
    
    // Stop propagation when clicking inside dropdown
    $dropdown.on('click', function(e) {
      e.stopPropagation();
    });
    
    $searchInput.on('click', function(e) {
      e.stopPropagation();
    });
    
    // Keyboard navigation
    $searchInput.on('keydown', function(e) {
      const $items = $dropdown.find('.dropdown-item');
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
          const bank = document.querySelector('#bankPaymentsBody .bank-account-select');
          if (bank) bank.focus();
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
    
    // Click on dropdown item - use event delegation
    $dropdown.on('click', '.dropdown-item', function(e) {
      e.preventDefault();
      e.stopPropagation();
      const code = $(this).data('code');
      const display = $(this).text().trim();
      const rate = $(this).data('rate');
      selectProduct($row, code, display, rate);
    });
  }
  
  // Select product function (variant products may repeat; uniqueness is per variant)
  function selectProduct($row, code, displayText, rate) {
    if (window.productAlreadyOnInvoice(code, $row)) {
      showWarning('Duplicate product', 'This product is already added.');
      if (!$row.find('.productCode').val()) $row.find('.productSearch').val('');
      const $pdd = $row.find('.productDropdown');
      if (typeof erpResetFloatingDropdown === 'function') erpResetFloatingDropdown($pdd[0]);
      $pdd.hide();
      $row.find('.productSearch').focus();
      return;
    }
    const pMeta = allProducts.find(function (x) { return String(x.code) === String(code); });
    const cleanLabel = pMeta
      ? (pMeta.name + ' (' + pMeta.code + ')')
      : (displayText || code);
    $row.find('.productSearch').val(cleanLabel);
    $row.find('.productCode').val(code);
    $row.find('.rate').val(rate);
    const $pdd = $row.find('.productDropdown');
    if (typeof erpResetFloatingDropdown === 'function') {
      erpResetFloatingDropdown($pdd[0]);
    }
    $pdd.hide();

    // Store base rate separately for reference
    $row.data('baseRate', rate);
    $row.data('currentRate', rate); // Track currently displayed rate
    // Base cost
    const baseCost = $row.find('.productCode option:selected').data('cost') || 0;
    $row.data('baseCost', baseCost);

    // Trigger change event with slight delay to ensure API call completes
    setTimeout(() => {
      $row.find('.productCode').trigger('change');
    }, 50);
    
    // Calculate amount and move to qty
    const qty = parseFloat($row.find('.qty').val()) || 0;
    const discount = parseFloat($row.find('.discount').val()) || 0;
    const amount = (qty * rate) - ((qty * rate * discount) / 100);
    $row.find('.amount').val(amount.toFixed(2));
    updateTotals();
    syncProductClearBtn($row);
  }

  function syncProductClearBtn($row) {
    const has = !!$row.find('.productCode').val();
    $row.find('.btn-clear-product').css('display', has ? 'inline-flex' : 'none');
    $row.find('.productSearch').toggleClass('has-product', has);
  }

  function clearProductSelection($row) {
    $row.find('.productSearch').val('').removeClass('has-product');
    $row.find('.productCode').val('');
    $row.find('.rate').val('');
    $row.find('.qty').val(1);
    $row.find('.discount').val('');
    $row.find('.amount').val('');
    $row.find('.variantWrapper').addClass('d-none');
    $row.find('.variantSelect').html('<option value="">Variant</option>');
    $row.find('.specialRateBadge').remove();
    $row.find('.btn-clear-product').hide();
    $row.removeData();
    const $pdd = $row.find('.productDropdown');
    if (typeof erpResetFloatingDropdown === 'function') erpResetFloatingDropdown($pdd[0]);
    $pdd.hide();
    if (typeof updateTotals === 'function') updateTotals();
    $row.find('.productSearch').focus();
  }

  $(document).on('click', '.btn-clear-product', function (e) {
    e.preventDefault();
    e.stopPropagation();
    clearProductSelection($(this).closest('tr'));
  });
  
  // Variant change handler - show variant rate or reset to base rate
  $(document).on('change', '.variantSelect', function(){
    const $row = $(this).closest('tr');
    const baseRate = parseFloat($row.data('baseRate')) || 0;
    const baseCostRate = parseFloat($row.data('costRate')) || 0;
    const standardRate = parseFloat($row.data('standardRate')) || 0;
    const selectedOption = $(this).find('option:selected');
    const variantRate = parseFloat(selectedOption.data('variant-rate')) || 0;
    const variantAdditionalCost = parseFloat(selectedOption.data('additional-cost')) || 0;
    const variantCostRate = parseFloat(selectedOption.data('cost-rate')) || 0;
    const hasSpecial = selectedOption.data('has-special') === true || selectedOption.data('has-special') === 'true';
    const rateCell = $row.find('.rate');
    const selectedVariantId = $(this).val();

    if (selectedVariantId && typeof window.assertUniqueProductVariant === 'function'
        && !window.assertUniqueProductVariant($row, selectedVariantId)) {
      $(this).val('');
      $row.find('.specialRateBadge').remove();
      rateCell.val(baseRate.toFixed(2));
      $row.data('currentRate', baseRate);
      $row.data('costRate', baseCostRate);
      calculateRowAmount($row);
      return;
    }
    
    // Remove old badge
    $row.find('.specialRateBadge').remove();
    
    // If variant selected and has rate, use variant rate; otherwise use base rate
    if($(this).val() && variantRate > 0) {
      rateCell.val(variantRate.toFixed(2));
      $row.data('currentRate', variantRate);
      
      // Calculate total cost rate: variant cost rate (if available) or base cost + variant additional cost
      const totalCostRate = variantCostRate > 0 ? variantCostRate : (baseCostRate + variantAdditionalCost);
      $row.data('costRate', totalCostRate);
      
      // Show special rate badge for variant
      if(hasSpecial) {
        rateCell.after(`<span class="specialRateBadge badge bg-warning text-dark ms-2" title="Standard: Rs. ${standardRate}">Variant Special Rate</span>`);
      }
    } else {
      rateCell.val(baseRate.toFixed(2));
      $row.data('currentRate', baseRate);
      $row.data('costRate', baseCostRate);
      
      // Restore base product special rate badge if exists
      const baseHasSpecial = baseRate !== standardRate && standardRate > 0;
      if(baseHasSpecial) {
        rateCell.after(`<span class="specialRateBadge badge bg-warning text-dark ms-2" title="Standard: Rs. ${standardRate}">Special Rate</span>`);
      }
    }
    
    calculateRowAmount($row);
  });

  // Setup first row
  setupProductSearch($('#productTable tbody tr:first'));

  $(window).on('resize.cashSaleProductDD', function() {
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

  @include('partials.sale.row-stock-check')
  @include('partials.sale.cash-enter-nav')

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
      return;
    }

    if (el.classList.contains('variantSelect') && !el.value) {
      e.preventDefault();
      e.stopPropagation();
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

  // Load product details when selected (no customer — list / standard rates only)
  $(document).on('change', '.productCode', function(){
    const productCode = $(this).val();
    const row = $(this).closest('tr');
    const $select = $(this);
    
    if(!productCode) {
      row.find('.rate').val('0');
      row.find('.specialRateBadge').remove();
      row.find('.variantWrapper').addClass('d-none');
      calculateRowAmount(row);
      return;
    }

    const customerCode = '';
    const rateCell = row.find('.rate');
    
    // Fetch product details with customer context for special rate
    $.get(`{{ url('/sale/invoice/product') }}/${encodeURIComponent(productCode)}`, { customerCode: customerCode })
      .done(function(data){
        // Store base rate and cost rate
        row.data('baseRate', data.saleRate);
        row.data('standardRate', data.standardRate);
        row.data('costRate', data.costRate || 0);
        rateCell.val(data.saleRate);
        
        // Remove old badge
        row.find('.specialRateBadge').remove();
        
        // Show special rate indicator for base product
        if(data.hasSpecialRate){
          rateCell.after(`<span class="specialRateBadge badge bg-warning text-dark ms-2" title="Standard: Rs. ${data.standardRate}">Special Rate</span>`);
        }
        
        // Handle variants with special rates
        const $variantWrapper = row.find('.variantWrapper');
        const $variantSelect = row.find('.variantSelect');
        $variantSelect.empty().append('<option value="">Select Variant</option>');
        
        // Check if we have variants from API
        let variantsData = (data.variants && data.variants.length > 0) ? data.variants : (productVariants[productCode] || []);
        
        if(variantsData && variantsData.length > 0){
          const usedVariantIds = (typeof window.usedVariantIdsForProduct === 'function')
            ? window.usedVariantIdsForProduct(productCode, row)
            : [];
          variantsData.forEach(v => {
            const alreadyUsed = usedVariantIds.indexOf(String(v.id)) !== -1;
            const disabled = (v.stockQty <= 0 || alreadyUsed) ? 'disabled' : '';
            // Determine what rate to show: special rate > variant rate > base rate
            let displayRate = v.hasSpecialRate ? v.specialRate : (v.additionalPrice > 0 ? v.additionalPrice : null);
            const specialBadge = v.hasSpecialRate ? ' ⭐' : '';
            let text = displayRate ? `${v.name}: ${v.value} (Rs.${displayRate})${specialBadge}` : `${v.name}: ${v.value}`;
            if (alreadyUsed) text += ' [Added]';
            
            $variantSelect.append(`<option value="${v.id}" ${disabled} 
              data-variant-rate="${displayRate || 0}" 
              data-has-special="${v.hasSpecialRate}" 
              data-special-rate="${v.specialRate || 0}" 
              data-standard-rate="${data.standardRate}" 
              data-stock="${v.stockQty}"
              data-additional-cost="${v.additionalCost || 0}"
              data-cost-rate="${v.costRate || 0}"
              title="Stock: ${v.stockQty}">${text}</option>`);
          });
          $variantWrapper.removeClass('d-none');
          
          // Add stock badges
          $variantSelect.find('option').each(function(){
            const stock = $(this).data('stock');
            if(stock !== undefined && $(this).val()){
              if(stock <= 0){ $(this).text($(this).text() + ' [OUT]'); }
              else if ($(this).text().indexOf('[Added]') === -1) { $(this).text($(this).text() + ' ['+stock+']'); }
            }
          });
        } else {
          $variantWrapper.addClass('d-none');
        }
        
        calculateRowAmount(row);
        if ($variantWrapper.is(':visible')) {
          $variantSelect.focus();
        } else {
          row.find('.qty').focus().select();
        }
      })
      .fail(function(){
        // Fallback to data attribute
        const rate = $select.find('option:selected').data('rate') || 0;
        rateCell.val(rate);
        calculateRowAmount(row);
        row.find('.qty').focus().select();
      });
  });

  // Calculate row amount
  function calculateRowAmount(row) {
    const qty = parseFloat(row.find('.qty').val()) || 0;
    const displayedRate = parseFloat(row.find('.rate').val()) || 0;
    const discount = parseFloat(row.find('.discount').val()) || 0;
    // Use displayed rate directly (whether base or variant rate)
    const amount = (qty * displayedRate) * (1 - discount / 100);
    row.find('.amount').val(amount.toFixed(2));
    updateTotals();
  }

  let invoiceDiscountPct = 0;

  function anyProductLineHasDiscount() {
    let has = false;
    $('#productTable tbody tr').each(function () {
      if (!$(this).find('.productCode').val()) return;
      if ((parseFloat($(this).find('.discount').val()) || 0) > 0) {
        has = true;
      }
    });
    return has;
  }

  /** Line discount wins: if any product has discount, invoice discount is locked at 0. */
  function syncInvoiceDiscountMode() {
    const invoiceTotal = parseFloat($('#invoiceTotal').val()) || 0;
    const lineHas = anyProductLineHasDiscount();
    const $inv = $('#invoiceDiscount');
    const $pct = $('#summaryCustomerDiscount');

    if (lineHas) {
      $inv.val('').prop('readonly', true).addClass('bg-light');
      $pct.prop('readonly', true).addClass('bg-light');
      $('#invoiceDiscountLockedNote').removeClass('d-none');
      $('#invoiceDiscountHint').addClass('d-none');
    } else {
      $inv.prop('readonly', false).removeClass('bg-light');
      $pct.prop('readonly', false).removeClass('bg-light');
      $('#invoiceDiscountLockedNote').addClass('d-none');
      $('#invoiceDiscountHint').removeClass('d-none');
      if (invoiceDiscountPct > 0 && invoiceTotal > 0) {
        $inv.val(((invoiceTotal * invoiceDiscountPct) / 100).toFixed(2));
      } else if (invoiceDiscountPct <= 0) {
        if (!$inv.val()) $inv.val('');
      } else {
        $inv.val('0.00');
      }
    }
    updateGrandTotal();
  }

  // Update totals
  function updateTotals() {
    let total = 0;
    let itemCount = 0;
    
    $('#productTable tbody tr').each(function(){
      const amount = parseFloat($(this).find('.amount').val()) || 0;
      const productCode = $(this).find('.productCode').val();
      if(productCode) itemCount++;
      total += amount;
    });
    
    $('#noOfItems').val(itemCount);
    $('#invoiceTotal').val(total.toFixed(2));
    syncInvoiceDiscountMode();
  }

  // Update grand total (invoice discount ignored when line discount exists)
  function updateGrandTotal() {
    const invoiceTotal = parseFloat($('#invoiceTotal').val()) || 0;
    let d = 0;
    if (!anyProductLineHasDiscount()) {
      d = parseFloat($('#invoiceDiscount').val()) || 0;
      if (d < 0) d = 0;
      if (d > invoiceTotal) {
        d = invoiceTotal;
        $('#invoiceDiscount').val(d.toFixed(2));
      }
    }
    $('#grandTotal').val((invoiceTotal - d).toFixed(2));
    applyCashBankAmount();
  }

  // CASH line shows Grand Total minus any other bank lines.
  function applyCashBankAmount() {
    const grand = parseFloat($('#grandTotal').val()) || 0;
    const $rows = $('#bankPaymentsBody tr.bank-pay-row');
    let $cash = $();

    $rows.each(function() {
      const isCash = String($(this).find('.bank-account-select').val() || '').toUpperCase() === 'CASH';
      if (isCash && !$cash.length) {
        $cash = $(this);
      }
    });

    if ($cash.length && !$cash.data('amountManual') && !freezeBankForDiscount) {
      let others = 0;
      $rows.each(function() {
        if (this === $cash[0]) return;
        others += parseFloat($(this).find('.bank-amount-input').val()) || 0;
      });
      const cashAmt = Math.max(0, grand - others);
      $cash.find('.bank-amount-input').val(cashAmt.toFixed(2));
    }

    updateBankPaymentSummary();
  }

  function updateBankPaymentSummary() {
    let allocated = 0;
    $('#bankPaymentsBody tr').each(function(){
      const code = $(this).find('.bank-account-select').val();
      const amt = parseFloat($(this).find('.bank-amount-input').val()) || 0;
      if(code && amt > 0) allocated += amt;
    });
    const grand = parseFloat($('#grandTotal').val()) || 0;
    $('#bankAllocatedTotal').text(allocated.toFixed(2));
    $('#bankGrandTotalHint').text(grand.toFixed(2));
    const $rem = $('#bankRemainingBalance');
    const remaining = grand - allocated;
    const $msg = $('#bankMatchMsg').empty();
    $rem.removeClass('text-success text-danger text-warning text-muted');
    if (grand <= 0) {
      $rem.addClass('text-muted').text('—');
      return;
    }
    $rem.text(remaining.toFixed(2));
    if (Math.abs(remaining) <= 0.02) {
      $rem.addClass('text-success');
    } else if (remaining > 0) {
      $rem.addClass('text-warning');
    } else {
      $rem.addClass('text-danger');
    }
    if(Math.abs(allocated - grand) <= 0.02) {
      $msg.removeClass('text-danger').addClass('text-success').text('✓ OK');
    } else {
      $msg.removeClass('text-success').addClass('text-danger').text('≠ mismatch');
    }
  }

  function refreshBankRemoveButtons() {
    const rows = $('#bankPaymentsBody tr').length;
    $('#bankPaymentsBody .btn-remove-bank-row').prop('disabled', rows <= 1);
  }

  $('#btnAddBankRow').on('click', function(){
    bankManaged = true;
    const $first = $('#bankPaymentsBody tr.bank-pay-row').first();
    const $clone = $first.clone();
    $clone.find('.bank-account-select').val('');
    $clone.find('.bank-amount-input').val('');
    $clone.removeData('amountManual');
    $('#bankPaymentsBody').append($clone);
    refreshBankRemoveButtons();
    applyCashBankAmount();
  });

  $(document).on('click', '.btn-remove-bank-row', function(){
    if($('#bankPaymentsBody tr').length <= 1) return;
    bankManaged = true;
    $(this).closest('tr').remove();
    refreshBankRemoveButtons();
    applyCashBankAmount();
  });

  $(document).on('input', '.bank-amount-input', function() {
    bankManaged = true;
    const $row = $(this).closest('tr');
    const isCash = String($row.find('.bank-account-select').val() || '').toUpperCase() === 'CASH';
    if (isCash) {
      $row.data('amountManual', true);
      updateBankPaymentSummary();
      return;
    }
    applyCashBankAmount();
  });

  $(document).on('change', '.bank-account-select', function() {
    bankManaged = true;
    $(this).closest('tr').removeData('amountManual');
    applyCashBankAmount();
  });

  $(document).on('input', '.qty, .rate, .discount', function(){
    calculateRowAmount($(this).closest('tr'));
    if ($(this).hasClass('discount')) {
      syncInvoiceDiscountMode();
    }
  });

  $('#summaryCustomerDiscount').on('input', function () {
    if (anyProductLineHasDiscount()) {
      $(this).val(invoiceDiscountPct > 0 ? String(Number(invoiceDiscountPct.toFixed(2))) : '');
      return;
    }
    invoiceDiscountPct = parseFloat($(this).val()) || 0;
    if (invoiceDiscountPct < 0) invoiceDiscountPct = 0;
    if (invoiceDiscountPct > 100) invoiceDiscountPct = 100;
    const holdBank = bankManaged;
    if (holdBank) freezeBankForDiscount = true;
    syncInvoiceDiscountMode();
    clearTimeout(discountBankTimer);
    if (!holdBank) return;
    discountBankTimer = setTimeout(function() {
      if (!banksMatchGrand()) warnBankAfterDiscount();
    }, 450);
  });

  $('#invoiceDiscount').on('input', function() {
    if (anyProductLineHasDiscount()) {
      $(this).val('');
      return;
    }
    invoiceDiscountPct = 0;
    $('#summaryCustomerDiscount').val('');
    const holdBank = bankManaged;
    if (holdBank) freezeBankForDiscount = true;
    updateGrandTotal();
    clearTimeout(discountBankTimer);
    if (!holdBank) return;
    discountBankTimer = setTimeout(function() {
      if (!banksMatchGrand()) warnBankAfterDiscount();
    }, 450);
  });
  refreshBankRemoveButtons();
  applyCashBankAmount();

  // Delete row
  $(document).on('click', '.btnDeleteRow', function(){
    const tbody = $('#productTable tbody');
    if(tbody.find('tr').length > 1) {
      $(this).closest('tr').remove();
      updateTotals();
    } else {
      showWarning('At least one product row is required');
    }
  });

  // Save cash invoice
  $('#btnSaveInvoice').on('click', function(){
    // Collect items
    const items = [];
    let hasError = false;

    $('#productTable tbody tr').each(function(){
      const productCode = $(this).find('.productCode').val();
      if(!productCode) return;

      const qty = parseInt($(this).find('.qty').val());
      if(qty <= 0){
        showWarning('Quantity must be greater than 0');
        hasError = true;
        return false;
      }

      const productName = $(this).find('.productCode option:selected').text();

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
          // Remove stock badge and rate from variant value
          variantValue = parts[1].split('[')[0].split('(')[0].trim();
        }
      }
      items.push({
        productCode: productCode,
        productName: productName,
        variant_id: variantId,
        variant_name: variantName,
        variant_value: variantValue,
        variant_added_price: parseFloat(variantRate) || 0,
        qty: qty,
        rate: parseFloat($(this).find('.rate').val()),
        cost_rate: parseFloat($(this).closest('tr').data('costRate')) || 0,
        discount: parseFloat($(this).find('.discount').val()) || 0,
        amount: parseFloat($(this).find('.amount').val())
      });
    });

    if(hasError) return;

    if(items.length === 0){
      showWarning('Please add at least one product');
      return;
    }

    const grandTotal = parseFloat($('#grandTotal').val()) || 0;
    if(grandTotal <= 0){
      showWarning('Grand total must be greater than zero');
      return;
    }

    const bankPayments = [];
    $('#bankPaymentsBody tr').each(function(){
      const code = $(this).find('.bank-account-select').val();
      const amt = parseFloat($(this).find('.bank-amount-input').val()) || 0;
      if(code && amt > 0){
        bankPayments.push({ bank_account: code, amount: amt });
      }
    });
    if(bankPayments.length === 0){
      showWarning('Add at least one bank with an amount');
      return;
    }
    let bankSum = 0;
    bankPayments.forEach(function(r){ bankSum += r.amount; });
    if(Math.abs(bankSum - grandTotal) > 0.02){
      if ((parseFloat($('#invoiceDiscount').val()) || 0) > 0 && (bankManaged || freezeBankForDiscount)) {
        warnBankAfterDiscount();
      } else {
        showWarning('Bank amounts must add up to Grand Total (allocated '+bankSum.toFixed(2)+', required '+grandTotal.toFixed(2)+')');
      }
      return;
    }

    const data = {
      _token: '{{ csrf_token() }}',
      invoiceNo: $('#invoiceNo').val(),
      invoiceDate: $('#invoiceDate').val(),
      buyer_name: $('#cashBuyerName').val(),
      buyer_city: $('#cashBuyerCity').val(),
      comments: $('#comments').val(),
      noOfItems: parseInt($('#noOfItems').val()),
      invoiceTotal: parseFloat($('#invoiceTotal').val()),
      invoiceDiscount: anyProductLineHasDiscount() ? 0 : (parseFloat($('#invoiceDiscount').val()) || 0),
      grandTotal: grandTotal,
      items: items,
      bank_payments: bankPayments
    };

    $.ajax({
      url: '{{ route("cash.sale.store") }}',
      method: 'POST',
      data: data,
      success: function(res){
        if(res.success){
          showSuccess('Cash invoice saved!', `Invoice No: ${res.invoiceNo}`);
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
      error: function(xhr){
        console.error('Error:', xhr);
        showError('Failed to save', xhr.responseJSON?.message || '');
      }
    });
  });

});

function focusCashNextAfterProducts() {
  const bank = document.querySelector('#bankPaymentsBody .bank-account-select');
  if (bank) bank.focus();
}

function cashRowCanAdd(row) {
  if (!row || !row.length) return true;
  if (!row.find('.productCode').val()) {
    focusCashNextAfterProducts();
    return false;
  }
  const variantWrap = row.find('.variantWrapper');
  if (variantWrap.length && !variantWrap.hasClass('d-none') && !row.find('.variantSelect').val()) {
    row.find('.variantSelect').focus();
    return false;
  }
  return true;
}

// Add row function
function addRow(btn) {
  const row = btn && btn.nodeType === 1 ? $(btn).closest('tr') : $('#productTable tbody tr').last();
  if (!cashRowCanAdd(row)) return;
  if (typeof window.assertProductRowStock === 'function' && !window.assertProductRowStock(row)) {
    return;
  }

  const tbody = $('#productTable tbody');
  
  // Create new row HTML with search functionality
  const newRowHTML = `
    <tr>
      <td>
        <div class="position-relative">
          <div class="d-flex gap-2 align-items-start product-variant-line">
            <div class="flex-grow-1 product-search-wrap">
              <input type="text" 
                     class="form-control form-control-sm productSearch" 
                     placeholder="Type to search product..."
                     autocomplete="off">
              <button type="button" class="btn-clear-product" title="Clear product" aria-label="Clear product">
                <i class="bi bi-x-lg"></i>
              </button>
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
            <div class="variantWrapper d-none">
              <select class="form-select form-select-sm variantSelect">
                <option value="">Variant</option>
              </select>
            </div>
          </div>
          <!-- rateBreakdown removed -->
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
  `;
  
  // Append new row
  const newRow = $(newRowHTML);
  tbody.append(newRow);
  
  // Setup product search for new row
  setupProductSearch(newRow);
  setTimeout(()=>{ newRow.find('.productSearch').focus(); },100);
  
  // Update totals
  updateTotals();
}
</script>

@endsection
