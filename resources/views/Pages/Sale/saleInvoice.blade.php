@extends('layout.app')

@section('title', 'Sale Invoice')

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
  
  /* Customer Search Styling */
  #customerSearch {
    border: 2px solid #e0e0e0;
    border-radius: 0.5rem;
    transition: all 0.3s ease-in-out;
    font-size: 0.875rem;
    background-color: #f8f9fa;
    padding: 0.5rem 0.75rem;
  }
  
  #customerSearch:focus {
    border-color: #1a4a8a;
    background-color: #fff;
    box-shadow: 0 0 0 0.3rem rgba(15, 39, 71, 0.12);
    outline: 0;
  }
  
  #customerSearch::placeholder {
    color: #999;
    font-style: italic;
  }
  
  #customerDropdown {
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
  
  #customerDropdown .dropdown-item {
    padding: 12px 15px;
    cursor: pointer;
    font-size: 0.875rem;
    border-bottom: 1px solid #f1f3f5;
    transition: all 0.15s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  #customerDropdown .dropdown-item:last-child {
    border-bottom: none;
  }
  
  #customerDropdown .dropdown-item:hover,
  #customerDropdown .dropdown-item.active {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
  }
  
  #customerDropdown .dropdown-item.active .text-muted,
  #customerDropdown .dropdown-item:hover .text-muted {
    color: rgba(255,255,255,0.9) !important;
  }
  
  #customerDropdown .dropdown-item.active .badge,
  #customerDropdown .dropdown-item:hover .badge {
    background-color: rgba(255,255,255,0.4) !important;
    color: #fff;
  }
  
  #customerDropdown .dropdown-item strong {
    font-weight: 600;
    display: block;
    margin-bottom: 2px;
  }
  
  #customerDropdown .dropdown-item .badge {
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

  /* Fluid layout: fields, cards, and the product grid grow and shrink with the page width */
  .sale-invoice-fluid {
    container-type: inline-size;
    container-name: sale-invoice;
  }

  .sale-invoice-fluid .app-content > .container-fluid,
  .sale-invoice-fluid .app-content-header > .container-fluid {
    max-width: 100%;
    width: 100%;
    padding-left: clamp(0.45rem, 1.4cqi, 1.35rem);
    padding-right: clamp(0.45rem, 1.4cqi, 1.35rem);
  }

  .sale-invoice-fluid .app-content-header h3 {
    font-size: clamp(1.05rem, 0.85rem + 1.1cqi, 1.55rem);
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

  .sale-invoice-fluid .form-label {
    font-size: clamp(0.75rem, 0.68rem + 0.28cqi, 0.92rem);
    margin-bottom: 0.25rem;
  }

  .sale-invoice-fluid .form-control,
  .sale-invoice-fluid .form-select {
    width: 100%;
    min-width: 0;
    max-width: 100%;
    font-size: clamp(0.78rem, 0.7rem + 0.32cqi, 0.98rem);
    min-height: clamp(1.85rem, 1.45rem + 1.15cqi, 2.45rem);
    padding-top: clamp(0.15rem, 0.35cqi, 0.4rem);
    padding-bottom: clamp(0.15rem, 0.35cqi, 0.4rem);
  }

  .sale-invoice-fluid textarea.form-control {
    min-height: clamp(3.2rem, 2.4rem + 2cqi, 5rem);
  }

  .sale-invoice-fluid .card-header h5,
  .sale-invoice-fluid .card-header h6 {
    font-size: clamp(0.85rem, 0.75rem + 0.4cqi, 1.1rem);
  }

  .sale-invoice-fluid .erp-invoice-product-wrap {
    container-type: inline-size;
    container-name: sale-products;
  }

  .sale-invoice-fluid .erp-invoice-product-wrap table {
    width: 100%;
    min-width: 40rem;
    table-layout: fixed;
  }

  .sale-invoice-fluid #productTable th,
  .sale-invoice-fluid #productTable td {
    padding: clamp(0.28rem, 0.45cqi, 0.55rem);
    vertical-align: middle;
    white-space: normal;
  }

  .sale-invoice-fluid #grandTotal {
    font-size: clamp(1.05rem, 0.9rem + 0.7cqi, 1.45rem);
  }

  .sale-invoice-fluid .sale-save-desktop .btn {
    min-height: clamp(2.2rem, 1.8rem + 1cqi, 2.75rem);
    font-size: clamp(0.9rem, 0.8rem + 0.35cqi, 1.05rem);
  }

  @container sale-invoice (max-width: 62rem) {
    .sale-invoice-fluid .sale-discount-note {
      display: none;
    }
  }

  .sale-invoice-fluid #productTable .form-control {
    min-height: clamp(1.7rem, 1.35rem + 0.9cqi, 2.2rem);
  }

  .sale-invoice-fluid .product-variant-line {
    min-width: 0;
  }

  .sale-invoice-fluid .product-variant-line > .flex-grow-1 {
    min-width: 0;
    flex: 1 1 auto;
  }

  .sale-invoice-fluid .variantWrapper {
    min-width: 0 !important;
    width: clamp(5.5rem, 28%, 11rem);
    flex: 0 1 clamp(5.5rem, 28%, 11rem);
  }

  .sale-invoice-fluid #productTable .btn-group {
    display: flex;
    width: 100%;
  }

  .sale-invoice-fluid #productTable .btn-group .btn {
    flex: 1 1 auto;
    padding: clamp(0.15rem, 0.3cqi, 0.35rem) clamp(0.2rem, 0.35cqi, 0.45rem);
  }

  .sale-invoice-fluid .input-group {
    min-width: 0;
    flex-wrap: nowrap;
  }

  .sale-invoice-fluid .input-group > .form-select,
  .sale-invoice-fluid .input-group > .form-control {
    min-width: 0;
    flex: 1 1 auto;
  }

  @media (max-width: 991.98px) {
    .sale-invoice-fluid .sale-save-desktop {
      display: none;
    }
  }

  @container sale-products (max-width: 40rem) {
    .sale-invoice-fluid .product-variant-line {
      flex-direction: column;
      align-items: stretch;
    }
    .sale-invoice-fluid .variantWrapper {
      width: 100%;
      flex-basis: auto;
    }
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
  .sale-invoice-fluid .form-control:focus,
  .sale-invoice-fluid .form-select:focus {
    border-color: #1a4a8a;
    box-shadow: 0 0 0 0.2rem rgba(15, 39, 71, 0.12);
  }
  .erp-invoice-sticky-actions {
    border-top-color: #d5dde8;
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
            <i class="bi bi-receipt me-2"></i>Sale Invoice
          </h3>
          <p class="text-muted small mb-0">Sale · Create &amp; save customer invoice</p>
        </div>
        <div class="col-12 col-md-6">
          <ol class="breadcrumb float-md-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Sale</span></li>
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
            <!-- Customer -->
            <div class="sale-field-wide">
              <label class="form-label fw-semibold si-label">
                <i class="bi bi-person-badge me-1"></i>Customer
              </label>
              <div class="position-relative">
                <input type="text" 
                       class="form-control form-control-sm" 
                       id="customerSearch" 
                       placeholder="Type to search customer..."
                       autocomplete="off">
                <select class="form-select form-select-sm d-none" id="customerCode">
                  <option value="">Select Customer</option>
                  @foreach($customers as $customer)
                    @php
                      $biz = trim((string) ($customer->bussnessName ?: $customer->custName));
                      $city = trim((string) ($customer->custCity ?? ''));
                      $label = $city !== '' ? $biz.' — '.$city : $biz;
                    @endphp
                    <option value="{{ $customer->custCode }}"
                            data-city="{{ $customer->custCity }}"
                            data-name="{{ $customer->custName }}"
                            data-business="{{ $customer->bussnessName ?? '' }}"
                            data-area="{{ $customer->custArea }}"
                            data-discount="{{ $customer->custdiscount ?? 0 }}"
                            data-label="{{ $label }}">
                      {{ $label }}
                    </option>
                  @endforeach
                </select>
                <div id="customerDropdown" class="dropdown-menu w-100" style="max-height: 300px; overflow-y: auto; display: none;"></div>
              </div>
            </div>
            <div>
              <label class="form-label fw-semibold si-label">Order No</label>
              <select class="form-select form-select-sm" id="orderNo">
                <option value="">Select order</option>
                <option value="__none__">No Order</option>
              </select>
            </div>
            <!-- City -->
            <div>
              <label class="form-label fw-semibold si-label">City</label>
              <input type="text" class="form-control form-control-sm" id="city" readonly>
            </div>

            <!-- Balance -->
            <div>
              <label class="form-label fw-semibold si-label" id="balanceFieldLabel">Receivable Balance</label>
              <input type="text" class="form-control form-control-sm" id="balance" readonly>
            </div>

            <!-- Invoice No -->
            <div>
              <label class="form-label fw-semibold si-label">Invoice No</label>
              <input type="text" class="form-control form-control-sm" id="invoiceNo" value="{{ $nextInvoiceNo }}" readonly>
            </div>

            <!-- Invoice Date -->
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

      <!-- Bottom Section: Additional Info & Totals -->
      <div class="sale-invoice-split">
        <!-- Left: Additional Information -->
        <div class="card si-card border-0">
            <div class="card-header si-card-head py-2">
              <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-info-circle me-2"></i>Additional Information</h6>
            </div>
            <div class="card-body p-2">
              <div class="sale-invoice-extra">
                <div class="sale-field-wide">
                  <label class="form-label fw-semibold si-label">Guds Name</label>
                  <div class="input-group input-group-sm">
                    <select class="form-select form-select-sm" id="gudsName">
                      <option value="">Select Guds</option>
                      @foreach($guds as $gud)
                        <option value="{{ $gud->gudsName }}" data-expanse="{{ $gud->gudsExpanse }}" data-loader="{{ $gud->gudsLoader }}">{{ $gud->gudsName }}</option>
                      @endforeach
                    </select>
                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#gudsModal" title="Add New Guds">
                      <i class="bi bi-plus-lg"></i>
                    </button>
                  </div>
                </div>
                <div>
                  <label class="form-label fw-semibold si-label">No. of Corten</label>
                  <input type="number" class="form-control form-control-sm" id="corten" value="0">
                </div>
                <div>
                  <label class="form-label fw-semibold si-label">Bilty Expense</label>
                  <input type="number" class="form-control form-control-sm" id="biltyExpanse" value="0" step="0.01">
                </div>
                <div>
                  <label class="form-label fw-semibold si-label">Loader Expense</label>
                  <input type="number" class="form-control form-control-sm" id="loaderExpanse" value="0" step="0.01">
                </div>
                <div class="sale-field-full">
                  <label class="form-label fw-semibold si-label">Bilty Balance</label>
                  <input type="text" class="form-control form-control-sm" id="biltyBalance" readonly value="0.00">
                </div>
                <div class="sale-field-full">
                  <label class="form-label fw-semibold si-label">Comments</label>
                  <textarea class="form-control form-control-sm" id="comments" rows="2"></textarea>
                </div>
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
                    <input type="text" class="form-control form-control-sm fw-semibold text-primary" id="summaryCustomerDiscount" readonly value="0" placeholder="0">
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
                  <input type="text" class="form-control fw-bold text-success si-grand" id="grandTotal" readonly value="0.00">
                </div>
                <div class="sale-field-full sale-save-desktop">
                  <button type="button" class="btn w-100 text-white js-invoice-save" id="btnSaveInvoice" style="background:#0f2747;border-color:#0f2747;">
                    <i class="bi bi-save me-2"></i>Save Invoice
                  </button>
                </div>
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>

  <div class="erp-invoice-sticky-actions d-lg-none" role="toolbar" aria-label="Invoice actions">
    <button type="button" class="btn btn-outline-secondary flex-shrink-0" onclick="addRow()">
      <i class="bi bi-plus-lg me-1"></i>Add line
    </button>
    <button type="button" class="btn flex-grow-1 text-white js-invoice-save" style="background:#0f2747;border-color:#0f2747;">
      <i class="bi bi-save me-1"></i>Save invoice
    </button>
  </div>

</main>

<!-- Guds Modal -->
<div class="modal fade" id="gudsModal" tabindex="-1" aria-labelledby="gudsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header si-card-head">
        <h5 class="modal-title text-white" id="gudsModalLabel"><i class="bi bi-truck me-2"></i>Add New Guds</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="gudsForm">
          <div class="mb-3">
            <label for="modalGudsName" class="form-label fw-semibold si-label">Guds Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="modalGudsName" required>
          </div>
          <div class="mb-3">
            <label for="modalGudsExpanse" class="form-label fw-semibold si-label">Guds Expense <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="modalGudsExpanse" value="0" step="0.01" min="0" required>
          </div>
          <div class="mb-3">
            <label for="modalGudsLoader" class="form-label fw-semibold si-label">Guds Loader <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="modalGudsLoader" value="0" step="0.01" min="0" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm text-white" id="btnSaveGuds" style="background:#0f2747;border-color:#0f2747;">
          <i class="bi bi-save me-1"></i>Save Guds
        </button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  // ---- Customer Search with Dropdown ----
  const $searchInput = $('#customerSearch');
  const $customerSelect = $('#customerCode');
  const $dropdown = $('#customerDropdown');
  let allCustomers = [];
  let selectedCustomerCode = '';
  
  // Build customer array from select options
  $customerSelect.find('option').each(function() {
    if($(this).val()) {
      const business = String($(this).data('business') || '');
      const name = String($(this).data('name') || '');
      const city = String($(this).data('city') || '');
      const label = String($(this).data('label') || $(this).text() || '');
      allCustomers.push({
        code: $(this).val(),
        name: name,
        business: business,
        area: String($(this).data('area') || ''),
        city: city,
        discount: $(this).data('discount') || 0,
        label: label,
        display: label
      });
    }
  });

  function customerLabel(c) {
    const biz = (c.business || c.name || '').trim();
    const city = (c.city || '').trim();
    return city ? `${biz} — ${city}` : biz;
  }
  
  // Filter and display customers
  function filterCustomers(searchTerm) {
    const term = searchTerm.toLowerCase().trim();
    if(!term) {
      $dropdown.hide().empty();
      return;
    }
    
    const filtered = allCustomers.filter(c => 
      c.name.toLowerCase().includes(term) ||
      c.business.toLowerCase().includes(term) ||
      c.city.toLowerCase().includes(term) ||
      c.area.toLowerCase().includes(term) ||
      c.code.toLowerCase().includes(term) ||
      c.label.toLowerCase().includes(term)
    );
    
    if(filtered.length === 0) {
      $dropdown.html('<div class="dropdown-item text-muted">No customers found</div>').show();
      return;
    }
    
    let html = '';
    filtered.slice(0, 10).forEach((c, idx) => {
      const title = (c.business || c.name || '').trim();
      const sub = [c.city, c.name && c.business ? c.name : ''].filter(Boolean).join(' · ');
      html += `<a class="dropdown-item ${idx===0?'active':''}" href="javascript:void(0);" data-code="${c.code}" data-city="${c.city}" data-label="${customerLabel(c).replace(/"/g, '&quot;')}">
        <div>
          <strong>${title}</strong>
          <div class="text-muted" style="font-size: 0.75rem;">${sub || '—'}</div>
        </div>
        <span class="badge bg-info">${c.code}</span>
      </a>`;
    });
    
    $dropdown.html(html).show();
  }
  
  // Search input events
  $searchInput.on('input', function() {
    filterCustomers($(this).val());
  });
  
  $searchInput.on('focus', function() {
    if($(this).val()) filterCustomers($(this).val());
  });
  
  // Close dropdown only when clicking outside
  $(document).on('click', function(e) {
    if(!$(e.target).closest('#customerSearch, #customerDropdown').length) {
      $dropdown.hide();
    }
  });
  
  // Stop propagation when clicking inside dropdown
  $dropdown.on('click', function(e) {
    e.stopPropagation();
  });
  
  $searchInput.on('click', function(e) {
    e.stopPropagation();
  });
  
  // Keyboard navigation in dropdown
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
      const $selected = $dropdown.is(':visible') ? $items.filter('.active') : $();
      if($selected.length > 0) {
        const code = $selected.data('code');
        const cust = allCustomers.find(c => c.code == code);
        selectCustomer(code, cust ? customerLabel(cust) : $selected.data('label'), $selected.data('city'));
      } else {
        const firstProduct = $('#productTable tbody tr:first .productSearch');
        firstProduct.focus();
      }
    }
    else if(e.key === 'Escape') {
      $dropdown.hide();
      $(this).blur();
    }
  });
  
  // Click on dropdown item
  $dropdown.on('click', '.dropdown-item', function(e) {
    e.preventDefault();
    e.stopPropagation();
    const code = $(this).data('code');
    const cust = allCustomers.find(c => c.code == code);
    const display = cust ? customerLabel(cust) : ($(this).data('label') || $(this).text().trim());
    const city = $(this).data('city');
    selectCustomer(code, display, city);
  });
  
  // Select customer function
  function selectCustomer(code, displayText, city) {
    selectedCustomerCode = code;
    $searchInput.val(displayText);
    $customerSelect.val(code).trigger('change');
    $('#city').val(city);

    $dropdown.hide();

    setTimeout(() => {
      const orderSelect = document.getElementById('orderNo');
      if (orderSelect) orderSelect.focus();
    }, 100);
  }
  
  // Auto focus search input
  setTimeout(() => { $searchInput.focus(); }, 200);

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
        $dropdown.html('<div class="dropdown-empty text-muted px-3 py-2">No products found</div>').show();
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
          ? `<img src="${p.image}" alt="" class="product-search-thumb rounded border">`
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
    
    // Search input events — clear selection when text changes (same as Cash / New Order)
    $searchInput.on('input', function() {
      if ($productSelect.val()) {
        $productSelect.val('');
        syncProductClearBtn($row);
      }
      filterProducts($(this).val());
    });
    
    $searchInput.on('focus', function() {
      if($(this).val()) filterProducts($(this).val());
    });
    
    // Close dropdown only when clicking outside
    $(document).on('click', function(e) {
      if(!$(e.target).closest($searchInput).length && !$(e.target).closest($dropdown).length) {
        hideProductDropdown();
      }
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
          const guds = document.getElementById('gudsName');
          if (guds) guds.focus();
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
    
    // Apply customer discount on line when empty
    if (customerDiscountPct > 0 && !(parseFloat($row.find('.discount').val()) > 0)) {
      $row.find('.discount').val(customerDiscountPct);
    }

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
    $row.find('.discount').val(customerDiscountPct > 0 ? customerDiscountPct : '');
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

  $(window).on('resize.saleInvoiceProductDD', function() {
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
  function resetOrderSelect(label) {
    $('#orderNo').html(
      '<option value="">' + (label || 'Select order') + '</option>' +
      '<option value="__none__">No Order</option>'
    );
  }

  function clearProductRows() {
    const $tbody = $('#productTable tbody');
    const $row = $tbody.find('tr:first').clone();
    $row.find('.productSearch').val('');
    $row.find('.productCode').val('');
    $row.find('.qty').val(1);
    $row.find('.rate').val('').attr('placeholder', 'Rate');
    $row.find('.discount').val(customerDiscountPct > 0 ? customerDiscountPct : '')
      .attr('placeholder', customerDiscountPct > 0 ? ('Cust ' + customerDiscountPct + '%') : 'Disc %');
    $row.find('.amount').val('').attr('placeholder', 'Amount');
    $row.find('.qty').attr('placeholder', 'Qty');
    $row.find('.variantWrapper').addClass('d-none');
    $row.find('.variantSelect').html('<option value="">Variant</option>');
    $row.find('.specialRateBadge').remove();
    $row.removeData();
    $tbody.empty().append($row);
    if (typeof window.setupProductSearch === 'function') window.setupProductSearch($row);
    updateTotals();
  }

  function applyOrderToInvoice(order) {
    const items = order.items || [];
    if ($('#gudsName option[value="' + (order.gudsName || '') + '"]').length) {
      $('#gudsName').val(order.gudsName).trigger('change');
    }
    if (!items.length) {
      clearProductRows();
      return;
    }
    const $tbody = $('#productTable tbody');
    const $seed = $tbody.find('tr:first').clone();
    $tbody.empty();
    items.forEach(function (item) {
      const $row = $seed.clone();
      $row.find('.variantWrapper').addClass('d-none');
      $row.find('.variantSelect').html('<option value="">Variant</option>');
      $row.find('.specialRateBadge').remove();
      $row.removeData();
      $tbody.append($row);
      if (typeof window.setupProductSearch === 'function') window.setupProductSearch($row);
      const optionText = $row.find('.productCode option[value="' + item.productCode + '"]').text();
      $row.find('.productSearch').val((optionText || item.productName || '').trim());
      $row.find('.qty').val(item.qty);
      $row.find('.discount').val(item.discount || 0);
      $row.find('.rate').val(parseFloat(item.rate || 0).toFixed(2));
      $row.data('keepOrderPrice', {
        qty: item.qty,
        rate: item.rate,
        discount: item.discount || 0
      });
      $row.find('.productCode').val(item.productCode).trigger('change');
      if (typeof syncProductClearBtn === 'function') syncProductClearBtn($row);
    });
  }

  function loadCustomerOrders(customerCode) {
    resetOrderSelect('Loading...');
    $.get(`{{ url('/sale/customer') }}/${encodeURIComponent(customerCode)}/orders`)
      .done(function (orders) {
        resetOrderSelect('Select order');
        (orders || []).forEach(function (order) {
          $('#orderNo').append(
            $('<option>', {
              value: order.orderNo,
              text: order.orderNo + ' — ' + order.orderDate + ' — Rs. ' + order.grandTotal
            })
          );
        });
        if (orders.length === 1) {
          $('#orderNo').val(orders[0].orderNo).trigger('change');
        }
      })
      .fail(function () {
        resetOrderSelect('Select order');
      });
  }

  $('#orderNo').on('change', function () {
    const orderNo = $(this).val();
    // No Order / empty: keep manually added products
    if (!orderNo || orderNo === '__none__') {
      return;
    }
    // Real order selected: replace grid with order products
    $.get(`{{ url('/sale/order') }}/${encodeURIComponent(orderNo)}`)
      .done(function (order) {
        applyOrderToInvoice(order);
      })
      .fail(function (xhr) {
        showWarning('Order', xhr.responseJSON?.message || 'Could not load this order.');
      });
  });

  // Number formatter
  function fmt(n) {
    return parseFloat(n || 0).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  }

  let customerDiscountPct = 0;

  function setCustomerDiscountDisplay(pct) {
    customerDiscountPct = parseFloat(pct) || 0;
    // Summary: show % like 30 next to Discount Amount
    $('#summaryCustomerDiscount').val(
      customerDiscountPct > 0 ? String(Number(customerDiscountPct.toFixed(2))) : '0'
    );
    // Placeholder on line discount fields shows customer discount
    const ph = customerDiscountPct > 0 ? ('Cust ' + customerDiscountPct + '%') : 'Disc %';
    $('#productTable tbody .discount').attr('placeholder', ph);
  }

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

  function applyCustomerDiscountToProductLines() {
    const pct = customerDiscountPct;
    $('#productTable tbody tr').each(function () {
      const $row = $(this);
      if (pct > 0) {
        $row.find('.discount').val(pct);
      } else {
        $row.find('.discount').val('');
      }
      if ($row.find('.productCode').val()) {
        calculateRowAmount($row);
      }
    });
  }

  /** Line discount wins: if any product has discount, invoice discount is locked at 0. */
  function syncInvoiceDiscountMode() {
    const invoiceTotal = parseFloat($('#invoiceTotal').val()) || 0;
    const lineHas = anyProductLineHasDiscount();
    const $inv = $('#invoiceDiscount');

    if (lineHas) {
      $inv.val('').prop('readonly', true).addClass('bg-light');
      $('#invoiceDiscountLockedNote').removeClass('d-none');
      $('#invoiceDiscountHint').addClass('d-none');
    } else {
      $inv.prop('readonly', false).removeClass('bg-light');
      $('#invoiceDiscountLockedNote').addClass('d-none');
      $('#invoiceDiscountHint').removeClass('d-none');
      if (customerDiscountPct > 0 && invoiceTotal > 0) {
        $inv.val(((invoiceTotal * customerDiscountPct) / 100).toFixed(2));
      } else if (customerDiscountPct <= 0) {
        if (!$inv.val()) $inv.val('');
      } else {
        $inv.val('0.00');
      }
    }
    updateGrandTotal();
  }

  // Load customer details
  $('#customerCode').on('change', function(){
    const customerCode = $(this).val();
    if(!customerCode) {
      $('#city').val('');
      $('#balance').val('');
      setCustomerDiscountDisplay(0);
      $('#invoiceDiscount').val('').prop('readonly', false).removeClass('bg-light');
      $('#invoiceDiscountLockedNote').addClass('d-none');
      $('#invoiceDiscountHint').removeClass('d-none');
      resetOrderSelect();
      clearProductRows();
      updateGrandTotal();
      return;
    }

    const city = $(this).find('option:selected').data('city');
    const discountPct = parseFloat($(this).find('option:selected').data('discount')) || 0;
    $('#city').val(city || '');
    setCustomerDiscountDisplay(discountPct);

    clearProductRows();
    applyCustomerDiscountToProductLines();
    syncInvoiceDiscountMode();
    loadCustomerOrders(customerCode);

    // Load balance from server
    $.get(`{{ url('/sale/customer') }}/${customerCode}`)
      .done(res => {
        $('#balanceFieldLabel').text(res.balance_label || 'Receivable Balance');
        $('#balance').val(fmt(res.balance ?? 0));
      })
      .fail(err => {
        console.error('Error:', err);
        $('#balance').val('0.00');
      });
    
    // Refresh rates for already selected products
    $('#productTable tbody tr').each(function(){
      const row = $(this);
      const productCode = row.find('.productCode').val();
      if(productCode){
        row.find('.productCode').trigger('change');
      }
    });
  });

  // Load product details when selected
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

    const customerCode = $('#customerCode').val() || '';
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
        row.find('.packHintBadge').remove();
        
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
        
        const keep = row.data('keepOrderPrice');
        if (keep) {
          row.find('.qty').val(keep.qty);
          rateCell.val(parseFloat(keep.rate || 0).toFixed(2));
          row.find('.discount').val(keep.discount || 0);
          row.removeData('keepOrderPrice');
        }
        calculateRowAmount(row);
        if (!keep) {
          if ($variantWrapper.is(':visible')) {
            $variantSelect.focus();
          } else {
            row.find('.qty').focus().select();
          }
        }
      })
      .fail(function(){
        const keep = row.data('keepOrderPrice');
        const rate = keep ? keep.rate : ($select.find('option:selected').data('rate') || 0);
        if (keep) {
          row.find('.qty').val(keep.qty);
          row.find('.discount').val(keep.discount || 0);
          row.removeData('keepOrderPrice');
        }
        rateCell.val(parseFloat(rate || 0).toFixed(2));
        calculateRowAmount(row);
        if (!keep) row.find('.qty').focus().select();
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

  // Update grand total (invoice discount is a fixed Rs. amount; ignored when line discount exists)
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
  }

  // Calculate bilty balance
  function calculateBiltyBalance() {
    const corten = parseFloat($('#corten').val()) || 0;
    const biltyExpanse = parseFloat($('#biltyExpanse').val()) || 0;
    const loaderExpanse = parseFloat($('#loaderExpanse').val()) || 0;
    
    const biltyBalance = (corten * biltyExpanse) - loaderExpanse;
    $('#biltyBalance').val(biltyBalance.toFixed(2));
  }

  // Event listeners
  $(document).on('input', '.qty, .rate, .discount', function(){
    calculateRowAmount($(this).closest('tr'));
    if ($(this).hasClass('discount')) {
      syncInvoiceDiscountMode();
    }
  });

  $('#invoiceDiscount').on('input', function () {
    if (anyProductLineHasDiscount()) {
      $(this).val('');
      return;
    }
    updateGrandTotal();
  });
  $('#corten, #biltyExpanse, #loaderExpanse').on('input', calculateBiltyBalance);

  // Store guds loader rate
  let gudsLoaderRate = 0;

  // Load guds expenses when selected
  $('#gudsName').on('change', function(){
    const selectedOption = $(this).find('option:selected');
    const gudsExpanse = selectedOption.data('expanse') || 0;
    gudsLoaderRate = selectedOption.data('loader') || 0;
    
    $('#biltyExpanse').val(gudsExpanse);
    
    // Calculate loader expense based on corten quantity
    const corten = parseFloat($('#corten').val()) || 0;
    const loaderExpanse = corten * gudsLoaderRate;
    $('#loaderExpanse').val(loaderExpanse.toFixed(2));
    
    calculateBiltyBalance();
  });

  // Update loader expense when corten changes
  $('#corten').on('input', function(){
    const corten = parseFloat($(this).val()) || 0;
    const loaderExpanse = corten * gudsLoaderRate;
    $('#loaderExpanse').val(loaderExpanse.toFixed(2));
    calculateBiltyBalance();
  });

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

  // Save invoice (desktop + mobile sticky)
  $(document).on('click', '.js-invoice-save', function(){
    const $saveBtns = $('.js-invoice-save');
    if ($saveBtns.prop('disabled')) {
      return;
    }

    const customerCode = $('#customerCode').val();
    if(!customerCode){
      showWarning('Please select a customer');
      return;
    }

    const orderNo = $('#orderNo').val();
    if(!orderNo){
      showWarning('Order number required', 'Select the customer order, or choose No Order.');
      const orderSelect = document.getElementById('orderNo');
      if (orderSelect) orderSelect.focus();
      return;
    }

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

    const $custOpt = $('#customerCode option:selected');
    const customerName = ($custOpt.data('business') || $custOpt.data('name') || $custOpt.text() || '').toString().trim();

    const data = {
      _token: '{{ csrf_token() }}',
      invoiceNo: $('#invoiceNo').val(),
      invoiceDate: $('#invoiceDate').val(),
      customerCode: customerCode,
      customerName: customerName,
      orderNo: orderNo,
      city: $('#city').val(),
      balance: parseFloat($('#balance').val().replace(/,/g, '')) || 0,
      gudsName: $('#gudsName').val(),
      corten: parseInt($('#corten').val()) || 0,
      biltyExpanse: parseFloat($('#biltyExpanse').val()) || 0,
      loaderExpanse: parseFloat($('#loaderExpanse').val()) || 0,
      biltyBalance: parseFloat($('#biltyBalance').val()) || 0,
      comments: $('#comments').val(),
      noOfItems: parseInt($('#noOfItems').val()),
      invoiceTotal: parseFloat($('#invoiceTotal').val()),
      invoiceDiscount: anyProductLineHasDiscount() ? 0 : (parseFloat($('#invoiceDiscount').val()) || 0),
      grandTotal: parseFloat($('#grandTotal').val()),
      items: items
    };

    $saveBtns.prop('disabled', true);

    $.ajax({
      url: '{{ url("/sale/invoice/store") }}',
      method: 'POST',
      data: data,
      success: function(res){
        if(res.success){
          if (res.invoiceNo) {
            $('#invoiceNo').val(res.invoiceNo);
          }
          showSuccess('Invoice saved successfully!', `Invoice No: ${res.invoiceNo}`);
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
        const msg = xhr.responseJSON?.message
          || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join(' ') : '')
          || 'Could not save invoice.';
        showError('Failed to save', msg);
      },
      complete: function () {
        $saveBtns.prop('disabled', false);
      }
    });
  });

  // Save Guds
  $('#btnSaveGuds').on('click', function(){
    const gudsName = $('#modalGudsName').val().trim();
    const gudsExpanse = $('#modalGudsExpanse').val();
    const gudsLoader = $('#modalGudsLoader').val();

    if(!gudsName){
      showWarning('Please enter guds name');
      return;
    }

    $.ajax({
      url: '{{ url("/sale/guds/store") }}',
      method: 'POST',
      data: {
        _token: '{{ csrf_token() }}',
        gudsName: gudsName,
        gudsExpanse: gudsExpanse,
        gudsLoader: gudsLoader
      },
      success: function(res){
        if(res.success){
          showSuccess('Guds saved successfully!');
          
          // Add to dropdown
          const newOption = new Option(res.data.gudsName, res.data.gudsName, true, true);
          $(newOption).attr('data-expanse', res.data.gudsExpanse);
          $(newOption).attr('data-loader', res.data.gudsLoader);
          $('#gudsName').append(newOption).trigger('change');
          
          // Close modal and reset form
          $('#gudsModal').modal('hide');
          $('#gudsForm')[0].reset();
        } else {
          showError('Failed to save', res.message || '');
        }
      },
      error: function(xhr){
        console.error('Error:', xhr);
        let errorMessage = 'Failed to save Guds';
        
        // Handle validation errors (422 status)
        if(xhr.status === 422 && xhr.responseJSON?.errors) {
          const errors = xhr.responseJSON.errors;
          const errorList = Object.values(errors).flat();
          errorMessage = errorList.join(', ');
        } else if(xhr.responseJSON?.message) {
          errorMessage = xhr.responseJSON.message;
        }
        
        showError(errorMessage);
      }
    });
  });

  // Reset guds form when modal closes
  $('#gudsModal').on('hidden.bs.modal', function(){
    $('#gudsForm')[0].reset();
  });
});

// Add row function
function addRow(btn) {
  const row = btn ? $(btn).closest('tr') : $('#productTable tbody tr').last();
  if (row.length && !row.find('.productCode').val()) {
    const guds = document.getElementById('gudsName');
    if (guds) guds.focus();
    return;
  }
  const variantWrap = row.find('.variantWrapper');
  if (variantWrap.length && !variantWrap.hasClass('d-none') && !row.find('.variantSelect').val()) {
    row.find('.variantSelect').focus();
    return;
  }
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
  if (typeof customerDiscountPct !== 'undefined' && customerDiscountPct > 0) {
    newRow.find('.discount').val(customerDiscountPct).attr('placeholder', 'Cust ' + customerDiscountPct + '%');
  } else if (typeof customerDiscountPct !== 'undefined') {
    newRow.find('.discount').attr('placeholder', 'Disc %');
  }
  
  // Setup product search for new row
  setupProductSearch(newRow);
  setTimeout(()=>{ newRow.find('.productSearch').focus(); },100);
  
  // Update totals
  updateTotals();
}
</script>

@endsection
