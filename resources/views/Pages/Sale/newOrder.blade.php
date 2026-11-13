@extends('layout.app')

@section('title', 'New Order')

@section('content')
@include('partials.sale.erp-styles')
@include('components.toast-notification')

@php
    $productCatalog = $products->map(function ($p) {
        $stock = (float) ($p->stockQty ?? 0);
        if ($p->relationLoaded('variants') && $p->variants->count() > 0) {
            $stock = (float) $p->variants->sum('stockQty');
        }

        return [
            'code' => $p->productCode,
            'name' => $p->productName,
            'company' => $p->companyName ?? '',
            'category' => $p->catagoryName ?? '',
            'packing' => $p->pakingName ?? '',
            'unit' => $p->unitPerPackName ?? '',
            'piecesPerUnit' => $p->piecesPerUnit,
            'rate' => (float) ($p->saleRate ?? 0),
            'stockQty' => $stock,
            'image' => $p->image_url,
            'hasVariants' => $p->relationLoaded('variants') && $p->variants->count() > 0,
        ];
    })->values();
@endphp

<style>
  .no-page {
    --navy: #0f2747;
    --navy-2: #1a4a8a;
    --gold: #c49a3c;
    --gold-deep: #9a7528;
    --soft: #f0f5fb;
    --line: #cfd9e6;
    --ink: #0c1222;
    --teal: #0d9488;
    --coral: #e11d48;
    --emerald: #059669;
    --sky: #0284c7;
    --amber: #d97706;
    --violet: #7c3aed;
    background:
      radial-gradient(ellipse 80% 50% at 0% 0%, rgba(196, 154, 60, 0.14), transparent 55%),
      radial-gradient(ellipse 70% 45% at 100% 8%, rgba(26, 74, 138, 0.12), transparent 50%),
      linear-gradient(180deg, #e8eef6 0%, #f5f7fb 42%, #eef2f7 100%);
  }
  .no-page .app-content-header {
    position: relative;
  }
  .no-page .app-content-header h3 {
    color: var(--navy);
    font-weight: 800;
    letter-spacing: -0.01em;
  }
  .no-page .app-content-header h3 i {
    color: var(--gold);
  }
  .no-page .page-sub {
    color: #4b5d73;
    font-weight: 600;
  }
  .no-page .page-rule {
    border: 0;
    height: 3px;
    margin: 0.85rem 0 0;
    border-radius: 999px;
    background: linear-gradient(90deg, var(--navy) 0%, var(--navy-2) 35%, var(--gold) 70%, transparent 100%);
  }
  .no-card {
    border: 1px solid rgba(15, 39, 71, 0.1);
    border-radius: 12px;
    box-shadow: 0 8px 28px rgba(15, 39, 71, 0.08);
    background: rgba(255, 255, 255, 0.96);
    overflow: hidden;
  }
  .no-card-head {
    background: linear-gradient(125deg, #0b1f3a 0%, var(--navy) 42%, var(--navy-2) 78%, #2a6bb5 100%);
    color: #fff;
    padding: 0.8rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    position: relative;
  }
  .no-card-head::after {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--gold), #f0d48a, transparent 70%);
  }
  .no-card-head h6 {
    margin: 0;
    font-weight: 700;
    letter-spacing: 0.02em;
  }
  .no-card-body { padding: 1rem; }
  .no-label {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--navy);
    margin-bottom: 0.3rem;
  }
  .btn-new-customer {
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-deep) 100%);
    border: 0;
    color: #fff;
    font-weight: 700;
    border-radius: 999px;
    box-shadow: 0 4px 12px rgba(196, 154, 60, 0.35);
  }
  .btn-new-customer:hover {
    filter: brightness(1.06);
    color: #fff;
  }
  .customer-info-panel {
    background: linear-gradient(145deg, #fff 0%, #eef5ff 100%);
    border: 1px solid #bfd0e8;
    border-left: 4px solid var(--navy-2);
    border-radius: 8px;
    padding: 0.75rem 0.9rem;
    min-height: 4.5rem;
  }
  .customer-info-panel .ci-title {
    font-weight: 800;
    color: var(--navy);
    margin-bottom: 0.15rem;
  }
  .customer-info-panel .ci-meta {
    font-size: 0.84rem;
    color: #445066;
  }
  .product-toolbar {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    margin-bottom: 0.9rem;
  }
  .product-search-wrap {
    position: relative;
  }
  .product-search-wrap i {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--navy-2);
    pointer-events: none;
  }
  .product-toolbar .form-control {
    min-height: 2.5rem;
    padding-left: 2.25rem;
    border-radius: 999px;
    border: 1.5px solid #b8c7db;
    background: linear-gradient(180deg, #fff 0%, #f7faff 100%);
    font-weight: 600;
  }
  .product-toolbar .form-control:focus {
    border-color: var(--navy-2);
    box-shadow: 0 0 0 0.2rem rgba(26, 74, 138, 0.15);
  }
  .product-cat-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
  }
  .product-cat-chip {
    border: 0;
    border-radius: 999px;
    padding: 0.38rem 0.95rem;
    font-size: 0.78rem;
    font-weight: 800;
    line-height: 1.2;
    cursor: pointer;
    color: #fff;
    box-shadow: 0 3px 10px rgba(15, 39, 71, 0.16);
    transition: transform 0.15s ease, filter 0.15s ease, box-shadow 0.15s ease;
  }
  .product-cat-chip:hover {
    transform: translateY(-1px);
    filter: brightness(1.06);
  }
  .product-cat-chip.active {
    outline: 2px solid #fff;
    box-shadow: 0 0 0 3px currentColor, 0 6px 16px rgba(15, 39, 71, 0.22);
    transform: translateY(-1px);
  }
  .product-cat-chip.tone-all {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-2) 100%);
    color: #fff;
  }
  .product-cat-chip.tone-0 { background: linear-gradient(135deg, #0d9488, #14b8a6); }
  .product-cat-chip.tone-1 { background: linear-gradient(135deg, #0284c7, #38bdf8); }
  .product-cat-chip.tone-2 { background: linear-gradient(135deg, #d97706, #f59e0b); }
  .product-cat-chip.tone-3 { background: linear-gradient(135deg, #e11d48, #fb7185); }
  .product-cat-chip.tone-4 { background: linear-gradient(135deg, #059669, #34d399); }
  .product-cat-chip.tone-5 { background: linear-gradient(135deg, #7c3aed, #a78bfa); }
  .product-cat-chip.tone-6 { background: linear-gradient(135deg, #b45309, #c49a3c); }
  .product-cat-chip.tone-7 { background: linear-gradient(135deg, #1d4ed8, #60a5fa); }
  .product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(148px, 1fr));
    gap: 0.7rem;
    max-height: 420px;
    overflow: auto;
    padding: 0.2rem 0.15rem 0.35rem 0;
  }
  .product-card {
    border: 1px solid #d5e0ee;
    border-radius: 12px;
    background: #fff;
    cursor: pointer;
    transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    overflow: hidden;
    text-align: left;
    padding: 0;
  }
  .product-card:hover,
  .product-card.active {
    border-color: var(--navy-2);
    box-shadow: 0 10px 22px rgba(15, 39, 71, 0.14);
    transform: translateY(-2px);
  }
  .product-card.active {
    outline: 2px solid rgba(196, 154, 60, 0.75);
  }
  .product-card-img {
    height: 96px;
    background: linear-gradient(145deg, #e8eef8, #f8fafc);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }
  .product-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .product-card-img .ph {
    color: #8a97a6;
    font-size: 1.4rem;
  }
  .product-card-body {
    padding: 0.55rem 0.6rem 0.65rem;
  }
  .product-card-body .pc-name {
    font-size: 0.82rem;
    font-weight: 800;
    color: var(--ink);
    line-height: 1.25;
    margin-bottom: 0.15rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 2.1em;
  }
  .product-card-body .pc-code {
    font-size: 0.72rem;
    color: #667589;
    font-weight: 600;
  }
  .product-card-body .pc-rate {
    margin-top: 0.3rem;
    font-size: 0.8rem;
    font-weight: 800;
    color: #fff;
    display: inline-block;
    padding: 0.12rem 0.5rem;
    border-radius: 999px;
    background: linear-gradient(135deg, var(--emerald), #10b981);
  }
  .picker-img {
    width: 112px;
    height: 112px;
    border-radius: 12px;
    border: 2px solid #c5d6ec;
    background: #fff;
    object-fit: cover;
  }
  .picker-img-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #8a97a6;
    font-size: 1.8rem;
    background: linear-gradient(145deg, #eef2f7, #fff);
  }
  #pickerRateBadge .badge {
    font-weight: 700;
  }
  #addToCartModal .modal-content {
    border: 0;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 18px 48px rgba(15, 39, 71, 0.22);
  }
  #addToCartModal .modal-header {
    background: linear-gradient(125deg, #0b1f3a 0%, var(--navy) 45%, var(--navy-2) 100%);
    color: #fff;
    border: 0;
  }
  #addToCartModal .modal-header::after {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--gold), #f0d48a, transparent 75%);
  }
  #addToCartModal .modal-header {
    position: relative;
  }
  #addToCartModal .modal-body {
    background: linear-gradient(160deg, #f7faff 0%, #fff 45%, #fff8eb 100%);
  }
  #addToCartModal .picker-hero {
    display: flex;
    gap: 1rem;
    align-items: center;
    padding: 0.85rem 1rem;
    border-radius: 12px;
    border: 1px solid #bfd0e8;
    background: rgba(255, 255, 255, 0.9);
    margin-bottom: 1rem;
  }
  #addToCartModal .picker-hero-title {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--navy);
    margin-bottom: 0.2rem;
  }
  #addToCartModal .modal-footer {
    border-top: 1px solid #d5e0ee;
    background: #f8fafc;
  }
  .cart-table img.cart-thumb {
    width: 42px;
    height: 42px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid var(--line);
  }
  .cart-thumb-empty {
    width: 42px;
    height: 42px;
    border-radius: 6px;
    border: 1px solid var(--line);
    background: #eef2f7;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #8a97a6;
  }
  .cart-table th {
    background: linear-gradient(135deg, var(--navy), var(--navy-2));
    color: #fff;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    white-space: nowrap;
  }
  .cart-table td {
    vertical-align: middle;
    font-size: 0.88rem;
  }
  .totals-box {
    background: linear-gradient(160deg, #0f2747 0%, #1a4a8a 55%, #2563a8 100%);
    border: 0;
    border-radius: 12px;
    padding: 0.95rem 1rem;
    color: #fff;
    box-shadow: 0 10px 24px rgba(15, 39, 71, 0.22);
  }
  .totals-box .t-row {
    display: flex;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.4rem;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.92);
  }
  .totals-box .t-grand {
    border-top: 2px solid rgba(240, 212, 138, 0.55);
    margin-top: 0.45rem;
    padding-top: 0.55rem;
    font-size: 1.15rem;
    color: #f6e2a8;
  }
  .btn-save-order {
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-deep) 100%);
    border: 0;
    color: #fff;
    font-weight: 800;
    border-radius: 999px;
    box-shadow: 0 6px 16px rgba(196, 154, 60, 0.35);
  }
  .btn-save-order:hover { color: #fff; filter: brightness(1.06); }
  .select2-container--bootstrap-5 .select2-selection {
    min-height: 2.35rem !important;
    border: 1.5px solid #b8c7db !important;
    border-radius: 8px !important;
  }
  .empty-cart {
    border: 1px dashed #b8c7db;
    border-radius: 12px;
    padding: 1.5rem 1rem;
    text-align: center;
    color: #4b5d73;
    background: linear-gradient(180deg, #f7faff, #eef3f9);
  }
  #productResultCount {
    background: rgba(255, 255, 255, 0.18);
    border: 1px solid rgba(255, 255, 255, 0.28);
    border-radius: 999px;
    padding: 0.15rem 0.65rem;
    font-weight: 700;
  }
  .btn-save-order:disabled,
  .btn-save-order.disabled {
    opacity: 0.55;
    filter: grayscale(0.25);
    box-shadow: none;
    pointer-events: none;
  }
  .field-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.15rem rgba(220, 53, 69, 0.18) !important;
  }

  /* —— Mobile app shell —— */
  @media (max-width: 991.98px) {
    .no-page.app-main {
      background:
        radial-gradient(ellipse 90% 40% at 10% -5%, rgba(196, 154, 60, 0.16), transparent 55%),
        linear-gradient(180deg, #e6edf6 0%, #f4f7fb 100%);
    }
    .no-page .app-content-header {
      padding-top: 0.35rem;
      padding-bottom: 0;
    }
    .no-page .app-content-header .breadcrumb,
    .no-page .page-sub { display: none; }
    .no-page .app-content-header h3 {
      font-size: 1.25rem;
    }
    .no-page .page-rule { margin-top: 0.55rem; }
    .no-page .app-content {
      padding-bottom: calc(5.75rem + env(safe-area-inset-bottom, 0px));
    }
    .no-card {
      border-radius: 14px;
      box-shadow: 0 6px 20px rgba(15, 39, 71, 0.1);
    }
    .no-card-head {
      padding: 0.7rem 0.85rem;
    }
    .no-card-body { padding: 0.85rem; }
    .product-grid {
      max-height: none;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 0.55rem;
    }
    .product-card-img { height: 110px; }
    .product-cat-chips {
      flex-wrap: nowrap;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      padding-bottom: 0.15rem;
      gap: 0.4rem;
      scrollbar-width: none;
    }
    .product-cat-chips::-webkit-scrollbar { display: none; }
    .product-cat-chip {
      flex: 0 0 auto;
      padding: 0.45rem 1rem;
      font-size: 0.8rem;
    }
    .product-toolbar .form-control {
      min-height: 2.75rem;
      font-size: 1rem;
    }
    .customer-info-panel { min-height: 3.5rem; }
    .cart-table thead { display: none; }
    .cart-table,
    .cart-table tbody,
    .cart-table tr,
    .cart-table td {
      display: block;
      width: 100%;
    }
    .cart-table tr {
      background: #fff;
      border: 1px solid #d5e0ee;
      border-radius: 12px;
      margin-bottom: 0.65rem;
      padding: 0.65rem 0.7rem;
      box-shadow: 0 3px 10px rgba(15, 39, 71, 0.06);
    }
    .cart-table td {
      border: 0 !important;
      padding: 0.2rem 0 !important;
    }
    .cart-table td:first-child { margin-bottom: 0.35rem; }
    .cart-table td:nth-child(2),
    .cart-table td:nth-child(3) {
      display: inline-block;
      width: 48%;
      vertical-align: top;
    }
    .cart-table td:nth-child(4) {
      margin-top: 0.35rem;
      font-size: 1rem;
    }
    .cart-table td:last-child {
      position: absolute;
      top: 0.45rem;
      right: 0.45rem;
      width: auto;
    }
    .cart-table tr { position: relative; padding-right: 2.6rem; }
    .cart-table .cart-qty,
    .cart-table .cart-rate {
      min-height: 2.5rem;
      font-size: 1rem;
    }
    .totals-box { border-radius: 14px; }
    .totals-box .t-grand { font-size: 1.2rem; }
    #addToCartModal .modal-dialog {
      margin: 0.5rem;
      max-width: none;
    }
    #addToCartModal .modal-content {
      border-radius: 16px;
    }
    #addToCartModal .picker-hero {
      flex-direction: column;
      text-align: center;
    }
    #addToCartModal .picker-img {
      width: 132px;
      height: 132px;
    }
    #addToCartModal .form-control {
      min-height: 2.75rem;
      font-size: 1.05rem;
    }
    #addToCartModal .modal-footer .btn {
      min-height: 2.75rem;
      font-weight: 700;
    }
    .erp-invoice-sticky-actions {
      display: flex !important;
      align-items: center;
      gap: 0.5rem;
      position: fixed;
      left: 0;
      right: 0;
      bottom: calc(var(--erp-mobile-nav-h, 3.75rem) + env(safe-area-inset-bottom, 0px));
      z-index: 1038;
      padding: 0.5rem 0.7rem;
      background: rgba(248, 250, 252, 0.96);
      backdrop-filter: blur(8px);
      border-top: 1px solid #d5dde8;
      box-shadow: 0 -8px 24px rgba(15, 39, 71, 0.12);
    }
    .erp-invoice-sticky-actions .btn {
      min-height: 2.85rem;
      font-weight: 800;
      border-radius: 999px;
    }
  }
  @media (max-width: 575.98px) {
    .product-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .product-card-body .pc-name { font-size: 0.78rem; }
  }
</style>

<main class="app-main no-page erp-invoice-page" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-7">
          <h3 class="mb-0"><i class="bi bi-bag-plus me-2"></i>New Order</h3>
          <div class="page-sub small mt-1">Book customer demand — no stock deduction</div>
        </div>
        <div class="col-sm-5">
          <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('order.summary') }}">Orders</a></li>
            <li class="breadcrumb-item active">New</li>
          </ol>
        </div>
      </div>
      <hr class="page-rule mb-3">
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <form id="orderForm" autocomplete="off">
        @csrf
        <input type="hidden" name="customerCode" id="customerCode" value="">
        <input type="hidden" name="customerName" id="customerName" value="">
        <input type="hidden" name="comments" id="comments" value="">

        <div class="row g-3">
          {{-- Customer --}}
          <div class="col-12">
            <div class="no-card">
              <div class="no-card-head">
                <h6><i class="bi bi-person-badge me-2"></i>Customer</h6>
                <button type="button" class="btn btn-sm btn-new-customer" id="btnOpenNewCustomer">
                  <i class="bi bi-plus-lg me-1"></i>New Customer
                </button>
              </div>
              <div class="no-card-body">
                <div class="row g-3 align-items-stretch">
                  <div class="col-lg-5">
                    <label class="no-label" for="customerSelect">Search customer <span class="text-danger">*</span></label>
                    <select id="customerSelect" class="form-select" style="width:100%;" required></select>
                  </div>
                  <div class="col-lg-4">
                    <label class="no-label">Selected customer</label>
                    <div class="customer-info-panel" id="customerInfoPanel">
                      <div class="ci-title text-muted">No customer selected</div>
                      <div class="ci-meta">Search above or add a new customer</div>
                    </div>
                  </div>
                  <div class="col-lg-3">
                    <div class="row g-2">
                      <div class="col-6">
                        <label class="no-label" for="orderDate">Order Date</label>
                        <input type="date" name="orderDate" id="orderDate" class="form-control form-control-sm"
                               value="{{ now()->format('Y-m-d') }}" required>
                      </div>
                      <div class="col-6">
                        <label class="no-label" for="orderNo">Order No</label>
                        <input type="text" name="orderNo" id="orderNo" class="form-control form-control-sm fw-bold"
                               value="{{ $nextOrderNo }}" readonly>
                      </div>
                      <div class="col-6">
                        <label class="no-label" for="city">City</label>
                        <input type="text" name="city" id="city" class="form-control form-control-sm" readonly>
                      </div>
                      <div class="col-6">
                        <label class="no-label" for="gudsName">Transport</label>
                        <select name="gudsName" id="gudsName" class="form-select form-select-sm">
                          <option value="">Optional</option>
                          @foreach($guds as $g)
                            <option value="{{ $g->gudsName }}">{{ $g->gudsName }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Products + picker --}}
          <div class="col-lg-7">
            <div class="no-card h-100">
              <div class="no-card-head">
                <h6><i class="bi bi-box-seam me-2"></i>Products</h6>
                <span class="small text-white-50" id="productResultCount">0 shown</span>
              </div>
              <div class="no-card-body">
                <div class="product-toolbar">
                  <div class="product-cat-chips" id="productCategoryChips" role="group" aria-label="Product categories">
                    <button type="button" class="product-cat-chip tone-all active" data-category="">All</button>
                    @foreach($productCategories as $cat)
                      <button type="button" class="product-cat-chip tone-{{ $loop->index % 8 }}" data-category="{{ $cat }}">{{ $cat }}</button>
                    @endforeach
                  </div>
                  <input type="hidden" id="productCategory" value="">
                  <div class="product-search-wrap">
                    <i class="bi bi-search"></i>
                    <input type="search" id="productSearch" class="form-control form-control-sm"
                           placeholder="Search by name or code..." autocomplete="off">
                  </div>
                </div>

                <div id="productGrid" class="product-grid" aria-live="polite"></div>
                <div class="small text-muted mt-2">
                  <i class="bi bi-hand-index-thumb me-1"></i>Tap a product to open <strong>Add to Cart</strong>
                </div>
              </div>
            </div>
          </div>

          {{-- Cart / summary --}}
          <div class="col-lg-5">
            <div class="no-card h-100">
              <div class="no-card-head">
                <h6><i class="bi bi-cart3 me-2"></i>Order Cart</h6>
                <span class="badge bg-light text-dark" id="cartCountBadge">0 items</span>
              </div>
              <div class="no-card-body">
                <div id="emptyCart" class="empty-cart mb-3">
                  <i class="bi bi-bag display-6 d-block mb-2 text-muted"></i>
                  Tap a product, then <strong>Add to Cart</strong> in the popup
                </div>

                <div class="table-responsive mb-3 d-none" id="cartWrap">
                  <table class="table table-sm cart-table align-middle mb-0">
                    <thead>
                      <tr>
                        <th>Product</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Rate</th>
                        <th class="text-end">Total</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody id="cartBody"></tbody>
                  </table>
                </div>

                <div class="mb-3">
                  <label class="no-label" for="notes">Notes</label>
                  <textarea name="notes" id="notes" class="form-control form-control-sm" rows="2"
                            placeholder="Optional order notes..."></textarea>
                </div>

                <div class="totals-box">
                  <div class="t-row"><span>Lines</span><strong id="statLines">0</strong></div>
                  <div class="t-row"><span>Total Qty</span><strong id="statQty">0</strong></div>
                  <div class="t-row"><span>Subtotal</span><strong id="statSubtotal">0.00</strong></div>
                  <div class="t-row"><span>Line Discount</span><strong id="statDiscount">0.00</strong></div>
                  <div class="t-row t-grand"><span>Grand Total</span><strong id="statGrand">Rs. 0.00</strong></div>
                </div>

                <input type="hidden" id="noOfItems" value="0">
                <input type="hidden" id="orderTotal" value="0.00">
                <input type="hidden" id="orderDiscount" value="0.00">
                <input type="hidden" id="grandTotal" value="0.00">
                <input type="hidden" id="summaryCustomerDiscount" value="0">

                <div class="d-none d-lg-flex gap-2 mt-3">
                  <button type="submit" class="btn btn-save-order flex-grow-1" id="btnSaveOrder" disabled>
                    <i class="bi bi-check2-circle me-1"></i>Save Order
                  </button>
                  <a href="{{ route('order.summary') }}" class="btn btn-outline-secondary">Back</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="erp-invoice-sticky-actions d-lg-none" role="toolbar" aria-label="Order actions">
    <button type="submit" form="orderForm" class="btn btn-save-order flex-grow-1" id="btnSaveOrderSticky" disabled>
      <i class="bi bi-check2-circle me-1"></i>Save <span class="sticky-cart-hint">(0)</span>
    </button>
    <a href="{{ route('order.summary') }}" class="btn btn-outline-secondary flex-shrink-0">Back</a>
  </div>
</main>

{{-- Add to Cart Modal --}}
<div class="modal fade" id="addToCartModal" tabindex="-1" aria-labelledby="addToCartModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addToCartModalLabel">
          <i class="bi bi-cart-plus me-2"></i>Add to Cart
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="picker-hero">
          <div id="pickerImageWrap" class="picker-img picker-img-empty flex-shrink-0"><i class="bi bi-image"></i></div>
          <div class="min-w-0">
            <div class="picker-hero-title" id="pickerName">Select a product</div>
            <div class="small text-muted" id="pickerMeta">Code, packing and rate will appear here</div>
            <div class="mt-2" id="pickerRateBadge"></div>
          </div>
        </div>
        <div class="row g-3">
          <div class="col-4">
            <label class="no-label" for="pickerQty">Qty</label>
            <input type="number" id="pickerQty" class="form-control" min="0.01" step="0.01" value="1">
          </div>
          <div class="col-4">
            <label class="no-label" for="pickerRate">Rate</label>
            <input type="number" id="pickerRate" class="form-control" min="0" step="0.01" value="">
          </div>
          <div class="col-4">
            <label class="no-label" for="pickerDiscount">Disc %</label>
            <input type="number" id="pickerDiscount" class="form-control" min="0" max="100" step="0.01" value="0">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-save-order px-4" id="btnAddToOrder" disabled>
          <i class="bi bi-cart-plus me-1"></i>Add to Cart
        </button>
      </div>
    </div>
  </div>
</div>

{{-- New Customer Modal --}}
<div class="modal fade" id="newCustomerModal" tabindex="-1" aria-labelledby="newCustomerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#0f2747,#1a4a8a);color:#fff;">
        <h5 class="modal-title" id="newCustomerModalLabel"><i class="bi bi-person-plus me-2"></i>New Customer</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="quickCustomerForm">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="no-label">Area *</label>
              <select name="custArea" class="form-select form-select-sm" required>
                <option value="">Select area</option>
                @foreach($custAreas as $area)
                  <option value="{{ $area->areaCode }}">{{ $area->areaName }} ({{ $area->areaCode }})</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="no-label">Business Name *</label>
              <input type="text" name="bussnessName" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-4">
              <label class="no-label">Contact Person *</label>
              <input type="text" name="custName" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-8">
              <label class="no-label">Address *</label>
              <input type="text" name="custAddress" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-4">
              <label class="no-label">City *</label>
              <input type="text" name="custCity" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-4">
              <label class="no-label">Phone *</label>
              <input type="text" name="custContect" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-4">
              <label class="no-label">Page / Route *</label>
              <input type="text" name="custPage" class="form-control form-control-sm" value="Main" required>
            </div>
            <div class="col-md-4">
              <label class="no-label">Category *</label>
              <select name="custCatagory" class="form-select form-select-sm" required>
                @foreach($custCategories as $cat)
                  <option value="{{ $cat }}" @selected($cat === 'Retailers')>{{ $cat }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="no-label">Discount % *</label>
              <input type="number" name="custdiscount" class="form-control form-control-sm" value="0" min="0" max="100" step="0.01" required>
            </div>
            <div class="col-md-3">
              <label class="no-label">Pay Terms (days) *</label>
              <input type="number" name="custPayTermDay" class="form-control form-control-sm" value="0" min="0" required>
            </div>
            <div class="col-md-3">
              <label class="no-label">Credit Limit *</label>
              <input type="number" name="custCreditLimit" class="form-control form-control-sm" value="0" min="0" step="0.01" required>
            </div>
            <div class="col-md-3">
              <label class="no-label">Opening Date *</label>
              <input type="date" name="custOppDate" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required>
            </div>
          </div>
          <div class="small text-muted mt-2">Customer code auto-generates if left blank.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-save-order" id="btnSaveQuickCustomer">
            <i class="bi bi-check2-circle me-1"></i>Save Customer
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
  if (typeof bootstrap === 'undefined') {
    console.error('Bootstrap JS missing — New Order modals will not work.');
  }
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.content
    || document.querySelector('input[name="_token"]')?.value;
  const routes = {
    customers: @json(route('order.customers.search')),
    products: @json(route('order.products.search')),
    productDetails: @json(url('/order/product')),
    store: @json(route('order.store')),
    customerStore: @json(route('Customer.store')),
  };

  let allProducts = @json($productCatalog);
  let cart = [];
  let selectedProduct = null;
  let customerDiscountPct = 0;
  let productSearchTimer = null;

  const els = {
    customerSelect: $('#customerSelect'),
    customerInfo: document.getElementById('customerInfoPanel'),
    customerCode: document.getElementById('customerCode'),
    customerName: document.getElementById('customerName'),
    city: document.getElementById('city'),
    productSearch: document.getElementById('productSearch'),
    productCategory: document.getElementById('productCategory'),
    productGrid: document.getElementById('productGrid'),
    productResultCount: document.getElementById('productResultCount'),
    pickerImageWrap: document.getElementById('pickerImageWrap'),
    pickerName: document.getElementById('pickerName'),
    pickerMeta: document.getElementById('pickerMeta'),
    pickerRateBadge: document.getElementById('pickerRateBadge'),
    pickerQty: document.getElementById('pickerQty'),
    pickerRate: document.getElementById('pickerRate'),
    pickerDiscount: document.getElementById('pickerDiscount'),
    btnAddToOrder: document.getElementById('btnAddToOrder'),
    cartBody: document.getElementById('cartBody'),
    cartWrap: document.getElementById('cartWrap'),
    emptyCart: document.getElementById('emptyCart'),
    cartCountBadge: document.getElementById('cartCountBadge'),
  };

  function money(n) {
    return (parseFloat(n) || 0).toFixed(2);
  }

  function lineAmount(qty, rate, discount) {
    const q = Math.max(0, parseFloat(qty) || 0);
    const r = Math.max(0, parseFloat(rate) || 0);
    const d = Math.max(0, Math.min(100, parseFloat(discount) || 0));
    return +(q * r * (1 - d / 100)).toFixed(2);
  }

  function thumbHtml(src, cls) {
    if (src) {
      return `<img src="${src}" alt="" class="${cls}" onerror="this.style.display='none'">`;
    }
    return `<span class="${cls} cart-thumb-empty"><i class="bi bi-image"></i></span>`;
  }

  function setCustomerDiscount(pct) {
    customerDiscountPct = parseFloat(pct) || 0;
    document.getElementById('summaryCustomerDiscount').value = String(customerDiscountPct);
    if (!(selectedProduct && els.pickerDiscount.value !== '' && parseFloat(els.pickerDiscount.value) > 0)) {
      els.pickerDiscount.value = customerDiscountPct > 0 ? customerDiscountPct : 0;
    }
  }

  function renderCustomerInfo(c) {
    if (!c) {
      els.customerInfo.innerHTML = `
        <div class="ci-title text-muted">No customer selected</div>
        <div class="ci-meta">Search above or add a new customer</div>`;
      return;
    }
    const title = (c.business || c.name || c.text || '').trim();
    const bits = [
      c.code ? `Code: ${c.code}` : '',
      c.phone ? `Phone: ${c.phone}` : '',
      c.city ? `City: ${c.city}` : '',
      customerDiscountPct > 0 ? `Discount: ${customerDiscountPct}%` : '',
    ].filter(Boolean);
    els.customerInfo.innerHTML = `
      <div class="ci-title">${title}</div>
      <div class="ci-meta">${bits.join(' · ') || 'Customer selected'}</div>
      ${c.address ? `<div class="ci-meta mt-1">${c.address}</div>` : ''}`;
  }

  function selectCustomerData(c) {
    if (!c) return;
    els.customerCode.value = c.code || c.id || '';
    els.customerName.value = (c.business || c.name || '').trim();
    els.city.value = c.city || '';
    setCustomerDiscount(c.discount || 0);
    renderCustomerInfo(c);
  }

  // Select2 AJAX customer search
  els.customerSelect.select2({
    theme: 'bootstrap-5',
    width: '100%',
    placeholder: 'Type customer name, code, city...',
    allowClear: true,
    ajax: {
      url: routes.customers,
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return { q: params.term || '', limit: 30 };
      },
      processResults: function (data) {
        const rows = data.results || data.customers || [];
        return {
          results: rows.map(function (c) {
            return {
              id: c.code,
              text: c.text || c.label || ((c.business || c.name) + (c.city ? ' — ' + c.city : '')),
              code: c.code,
              name: c.name,
              business: c.business,
              city: c.city,
              phone: c.phone,
              address: c.address,
              area: c.area,
              discount: c.discount,
            };
          }),
        };
      },
    },
    minimumInputLength: 0,
  });

  els.customerSelect.on('select2:select', function (e) {
    selectCustomerData(e.params.data);
  });
  els.customerSelect.on('select2:clear', function () {
    els.customerCode.value = '';
    els.customerName.value = '';
    els.city.value = '';
    setCustomerDiscount(0);
    renderCustomerInfo(null);
  });

  function renderProductGrid(list) {
    els.productResultCount.textContent = `${list.length} shown`;
    if (!list.length) {
      els.productGrid.innerHTML = `<div class="text-muted small p-3">No products found</div>`;
      return;
    }
    els.productGrid.innerHTML = list.map(function (p) {
      const active = selectedProduct && selectedProduct.code === p.code ? 'active' : '';
      const img = p.image
        ? `<img src="${p.image}" alt="" onerror="this.parentElement.innerHTML='<i class=\\'bi bi-image ph\\'></i>'">`
        : `<i class="bi bi-image ph"></i>`;
      return `
        <button type="button" class="product-card ${active}" data-code="${p.code}">
          <div class="product-card-img">${img}</div>
          <div class="product-card-body">
            <div class="pc-name">${p.name || ''}</div>
            <div class="pc-code">${p.code || ''}${p.company ? ' · ' + p.company : ''}</div>
            <div class="pc-rate">Rs. ${money(p.rate)}</div>
          </div>
        </button>`;
    }).join('');
  }

  function filterLocalProducts() {
    const q = (els.productSearch.value || '').toLowerCase().trim();
    const cat = (els.productCategory.value || '').trim();
    let list = allProducts.slice();
    if (cat) {
      list = list.filter(function (p) {
        return String(p.category || '').trim() === cat;
      });
    }
    if (q) {
      list = list.filter(function (p) {
        return (p.name || '').toLowerCase().includes(q)
          || (p.code || '').toLowerCase().includes(q)
          || (p.company || '').toLowerCase().includes(q);
      });
    }
    // Show full filtered catalog (all products on load / category)
    renderProductGrid(list);
  }

  els.productSearch.addEventListener('input', function () {
    clearTimeout(productSearchTimer);
    productSearchTimer = setTimeout(filterLocalProducts, 120);
  });

  const categoryChips = document.getElementById('productCategoryChips');
  if (categoryChips) {
    categoryChips.addEventListener('click', function (e) {
      const chip = e.target.closest('.product-cat-chip[data-category]');
      if (!chip) return;
      categoryChips.querySelectorAll('.product-cat-chip').forEach(function (btn) {
        btn.classList.toggle('active', btn === chip);
      });
      els.productCategory.value = chip.getAttribute('data-category') || '';
      filterLocalProducts();
    });
  }

  els.productGrid.addEventListener('click', function (e) {
    const btn = e.target.closest('.product-card[data-code]');
    if (!btn) return;
    const code = btn.getAttribute('data-code');
    const local = allProducts.find(p => p.code === code);
    if (local) chooseProduct(local);
  });

  const addToCartModalEl = document.getElementById('addToCartModal');
  function getAddToCartModal() {
    if (!addToCartModalEl || typeof bootstrap === 'undefined') return null;
    return bootstrap.Modal.getOrCreateInstance(addToCartModalEl);
  }

  async function chooseProduct(p) {
    selectedProduct = p;
    filterLocalProducts();
    els.pickerName.textContent = p.name || p.code;
    const packingBits = [p.packing, p.unit, p.piecesPerUnit ? (p.piecesPerUnit + ' / unit') : '']
      .filter(Boolean).join(' · ');
    els.pickerMeta.textContent = `${p.code}${p.company ? ' · ' + p.company : ''}${packingBits ? ' · ' + packingBits : ''}`;
    els.pickerImageWrap.className = 'picker-img flex-shrink-0' + (p.image ? '' : ' picker-img-empty');
    els.pickerImageWrap.innerHTML = p.image
      ? `<img src="${p.image}" alt="" class="picker-img" onerror="this.parentElement.classList.add('picker-img-empty');this.parentElement.innerHTML='<i class=\\'bi bi-image\\'></i>';">`
      : `<i class="bi bi-image"></i>`;
    els.pickerQty.value = 1;
    els.pickerRate.value = money(p.rate);
    els.pickerDiscount.value = customerDiscountPct > 0 ? customerDiscountPct : 0;
    els.pickerRateBadge.innerHTML = `<span class="badge text-bg-primary">Sale rate Rs. ${money(p.rate)}</span>`;
    els.btnAddToOrder.disabled = false;
    const addModal = getAddToCartModal();
    if (addModal) addModal.show();
    setTimeout(function () { els.pickerQty.focus(); els.pickerQty.select(); }, 280);

    const customerCode = els.customerCode.value;
    if (!customerCode) return;
    try {
      const res = await fetch(`${routes.productDetails}/${encodeURIComponent(p.code)}?customerCode=${encodeURIComponent(customerCode)}`, {
        headers: { 'Accept': 'application/json' },
      });
      if (!res.ok) return;
      const details = await res.json();
      if (details.hasSpecialRate && details.saleRate != null) {
        els.pickerRate.value = money(details.saleRate);
        els.pickerRateBadge.innerHTML = `
          <span class="badge text-bg-success">Customer rate Rs. ${money(details.saleRate)}</span>
          <span class="badge text-bg-light text-dark ms-1">Std ${money(details.standardRate)}</span>`;
      }
      if (details.image) {
        selectedProduct.image = details.image;
        els.pickerImageWrap.className = 'picker-img flex-shrink-0';
        els.pickerImageWrap.innerHTML = `<img src="${details.image}" alt="" class="picker-img">`;
      }
      if (details.packing) selectedProduct.packing = details.packing;
      if (details.unit) selectedProduct.unit = details.unit;
    } catch (_) {}
  }

  function setSaveEnabled(enabled) {
    [document.getElementById('btnSaveOrder'), document.getElementById('btnSaveOrderSticky')].forEach(function (btn) {
      if (!btn) return;
      btn.disabled = !enabled;
      btn.classList.toggle('disabled', !enabled);
    });
    document.querySelectorAll('.sticky-cart-hint').forEach(function (el) {
      el.textContent = '(' + cart.length + ')';
    });
  }

  function renderCart() {
    const has = cart.length > 0;
    els.emptyCart.classList.toggle('d-none', has);
    els.cartWrap.classList.toggle('d-none', !has);
    els.cartCountBadge.textContent = `${cart.length} item${cart.length === 1 ? '' : 's'}`;
    setSaveEnabled(has);

    els.cartBody.innerHTML = cart.map(function (row, idx) {
      const amt = lineAmount(row.qty, row.rate, row.discount);
      const pack = [row.packing, row.unit].filter(Boolean).join(' / ');
      const qtyBad = !(parseFloat(row.qty) > 0);
      const rateBad = !(parseFloat(row.rate) >= 0) || row.rate === '' || row.rate === null;
      return `
        <tr data-idx="${idx}">
          <td>
            <div class="d-flex align-items-center gap-2">
              ${row.image
                ? `<img src="${row.image}" class="cart-thumb" alt="" onerror="this.replaceWith(Object.assign(document.createElement('span'),{className:'cart-thumb-empty',innerHTML:'<i class=\\'bi bi-image\\'></i>'}))">`
                : `<span class="cart-thumb-empty"><i class="bi bi-image"></i></span>`}
              <div class="min-w-0">
                <div class="fw-semibold text-truncate" style="max-width:11rem;">${row.productName}</div>
                <div class="small text-muted">${row.productCode}${pack ? ' · ' + pack : ''}</div>
              </div>
            </div>
          </td>
          <td class="text-center" style="min-width:4.5rem;">
            <div class="small text-muted d-lg-none mb-1">Qty</div>
            <input type="number" inputmode="decimal" class="form-control form-control-sm text-center cart-qty ${qtyBad ? 'field-invalid' : ''}" min="0.01" step="0.01" value="${row.qty}">
          </td>
          <td class="text-end" style="min-width:5rem;">
            <div class="small text-muted d-lg-none mb-1 text-end">Rate</div>
            <input type="number" inputmode="decimal" class="form-control form-control-sm text-end cart-rate ${rateBad ? 'field-invalid' : ''}" min="0" step="0.01" value="${money(row.rate)}">
            <div class="small text-muted">D ${money(row.discount)}%</div>
          </td>
          <td class="text-end fw-bold">Rs. ${money(amt)}</td>
          <td class="text-end">
            <button type="button" class="btn btn-sm btn-outline-danger cart-remove" title="Remove">
              <i class="bi bi-trash3"></i>
            </button>
          </td>
        </tr>`;
    }).join('');

    recalc();
  }

  function recalc() {
    let gross = 0;
    let net = 0;
    let qtySum = 0;
    cart.forEach(function (row) {
      const q = parseFloat(row.qty) || 0;
      const r = parseFloat(row.rate) || 0;
      const d = parseFloat(row.discount) || 0;
      gross += q * r;
      net += lineAmount(q, r, d);
      qtySum += q;
    });
    const discountRs = gross - net;
    document.getElementById('statLines').textContent = String(cart.length);
    document.getElementById('statQty').textContent = Number.isInteger(qtySum) ? String(qtySum) : qtySum.toFixed(2);
    document.getElementById('statSubtotal').textContent = money(gross);
    document.getElementById('statDiscount').textContent = money(discountRs);
    document.getElementById('statGrand').textContent = 'Rs. ' + money(net);
    document.getElementById('noOfItems').value = String(cart.length);
    // Store net as orderTotal; flat header discount stays 0 (line % already applied)
    document.getElementById('orderTotal').value = money(net);
    document.getElementById('orderDiscount').value = '0.00';
    document.getElementById('grandTotal').value = money(net);
  }

  function warn(title, text) {
    if (typeof showWarning === 'function') showWarning(title, text);
    else if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: title, text: text, confirmButtonColor: '#0f2747' });
    else alert(text || title);
  }

  els.btnAddToOrder.addEventListener('click', function () {
    if (!selectedProduct) return;
    if (!els.customerCode.value) {
      warn('Select customer', 'Please select a customer first.');
      return;
    }
    const qty = parseFloat(els.pickerQty.value || 0);
    const rate = parseFloat(els.pickerRate.value || 0);
    const discount = parseFloat(els.pickerDiscount.value || 0);
    els.pickerQty.classList.toggle('field-invalid', !(qty > 0));
    els.pickerRate.classList.toggle('field-invalid', !(rate >= 0) || els.pickerRate.value === '');
    els.pickerDiscount.classList.toggle('field-invalid', discount < 0 || discount > 100);
    if (!(qty > 0)) {
      warn('Quantity required', 'Enter a valid quantity greater than 0.');
      els.pickerQty.focus();
      return;
    }
    if (els.pickerRate.value === '' || Number.isNaN(rate) || rate < 0) {
      warn('Rate required', 'Enter a valid rate (0 or more).');
      els.pickerRate.focus();
      return;
    }
    if (Number.isNaN(discount) || discount < 0 || discount > 100) {
      warn('Discount', 'Discount must be between 0 and 100.');
      els.pickerDiscount.focus();
      return;
    }

    // Non-variant products: block duplicate lines (same as previous New Order rule)
    if (!selectedProduct.hasVariants) {
      const dup = cart.some(r => r.productCode === selectedProduct.code);
      if (dup) {
        warn('Duplicate product', 'This product is already in the cart.');
        return;
      }
    }

    cart.push({
      productCode: selectedProduct.code,
      productName: selectedProduct.name,
      qty: qty,
      rate: rate,
      discount: discount,
      packing: selectedProduct.packing || '',
      unit: selectedProduct.unit || '',
      image: selectedProduct.image || '',
      hasVariants: !!selectedProduct.hasVariants,
    });
    renderCart();
    const addModal = getAddToCartModal();
    if (addModal) addModal.hide();
    els.btnAddToOrder.disabled = true;
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Added to order',
        showConfirmButton: false,
        timer: 1200,
      });
    }
    setTimeout(function () { els.productSearch.focus(); }, 220);
  });

  els.cartBody.addEventListener('input', function (e) {
    const tr = e.target.closest('tr[data-idx]');
    if (!tr) return;
    const idx = parseInt(tr.getAttribute('data-idx'), 10);
    if (!cart[idx]) return;
    if (e.target.classList.contains('cart-qty')) {
      cart[idx].qty = parseFloat(e.target.value || 0);
    }
    if (e.target.classList.contains('cart-rate')) {
      cart[idx].rate = parseFloat(e.target.value || 0);
    }
    renderCart();
  });

  els.cartBody.addEventListener('click', function (e) {
    const btn = e.target.closest('.cart-remove');
    if (!btn) return;
    const tr = btn.closest('tr[data-idx]');
    const idx = parseInt(tr.getAttribute('data-idx'), 10);
    const row = cart[idx];
    const doRemove = function () {
      cart.splice(idx, 1);
      renderCart();
    };
    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'question',
        title: 'Remove product?',
        text: row ? row.productName : '',
        showCancelButton: true,
        confirmButtonText: 'Remove',
        confirmButtonColor: '#0f2747',
      }).then(function (r) { if (r.isConfirmed) doRemove(); });
    } else {
      doRemove();
    }
  });

  // New customer modal
  const modalEl = document.getElementById('newCustomerModal');
  function getCustomerModal() {
    if (!modalEl || typeof bootstrap === 'undefined') return null;
    return bootstrap.Modal.getOrCreateInstance(modalEl);
  }
  document.getElementById('btnOpenNewCustomer').addEventListener('click', function () {
    document.getElementById('quickCustomerForm').reset();
    document.querySelector('#quickCustomerForm [name="custOppDate"]').value = @json(now()->format('Y-m-d'));
    document.querySelector('#quickCustomerForm [name="custPage"]').value = 'Main';
    document.querySelector('#quickCustomerForm [name="custdiscount"]').value = '0';
    const customerModal = getCustomerModal();
    if (customerModal) customerModal.show();
    else if (typeof showError === 'function') showError('Modal could not open. Refresh the page.');
  });

  document.getElementById('quickCustomerForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const form = e.currentTarget;
    const btn = document.getElementById('btnSaveQuickCustomer');
    btn.disabled = true;
    const fd = new FormData(form);
    try {
      const res = await fetch(routes.customerStore, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': CSRF,
        },
        body: fd,
      });
      const data = await res.json();
      if (!res.ok || !data.success) {
        const errors = data.errors || {};
        const first = Object.values(errors)[0];
        const msg = (first && first[0]) || data.message || 'Could not save customer.';
        if (typeof showError === 'function') showError(msg);
        else alert(msg);
        return;
      }
      const c = data.customer;
      const option = new Option(c.text || c.label || c.custName, c.code || c.custCode, true, true);
      els.customerSelect.append(option).trigger('change');
      selectCustomerData({
        id: c.code || c.custCode,
        code: c.code || c.custCode,
        name: c.name || c.custName,
        business: c.business || '',
        city: c.city || '',
        phone: c.phone || '',
        address: c.address || '',
        discount: c.discount || 0,
        text: c.text || c.label,
      });
      const customerModal = getCustomerModal();
      if (customerModal) customerModal.hide();
      if (typeof showSuccess === 'function') showSuccess(data.message || 'Customer created');
    } catch (err) {
      if (typeof showError === 'function') showError('Could not save customer.');
      else alert('Could not save customer.');
    } finally {
      btn.disabled = false;
    }
  });

  function firstValidationMessage(data) {
    if (!data) return '';
    if (data.message && data.message !== 'The given data was invalid.') return data.message;
    const errors = data.errors || {};
    const firstKey = Object.keys(errors)[0];
    if (firstKey && errors[firstKey] && errors[firstKey][0]) return errors[firstKey][0];
    return data.message || '';
  }

  function validateBeforeSave() {
    const customerCode = els.customerCode.value;
    const customerName = els.customerName.value;
    const orderDate = document.getElementById('orderDate').value;
    const orderNo = document.getElementById('orderNo').value;

    if (!customerCode || !customerName) {
      warn('Customer required', 'Please select a customer before saving.');
      try { els.customerSelect.select2('open'); } catch (_) {}
      return null;
    }
    if (!orderDate) {
      warn('Order date required', 'Please select order date.');
      document.getElementById('orderDate').classList.add('field-invalid');
      document.getElementById('orderDate').focus();
      return null;
    }
    document.getElementById('orderDate').classList.remove('field-invalid');
    if (!orderNo) {
      warn('Order number missing', 'Order number is required.');
      return null;
    }
    if (!cart.length) {
      warn('Product required', 'Add at least one product before saving. Empty order cannot be saved.');
      return null;
    }

    const items = [];
    for (let i = 0; i < cart.length; i++) {
      const row = cart[i];
      const qty = parseFloat(row.qty);
      const rate = parseFloat(row.rate);
      const discount = parseFloat(row.discount) || 0;
      if (!row.productCode || !row.productName) {
        warn('Invalid product', 'Cart has an invalid product line. Remove it and add again.');
        return null;
      }
      if (!(qty > 0)) {
        warn('Invalid quantity', (row.productName || 'Product') + ' — quantity must be greater than 0.');
        renderCart();
        return null;
      }
      if (Number.isNaN(rate) || rate < 0) {
        warn('Invalid rate', (row.productName || 'Product') + ' — enter a valid rate.');
        renderCart();
        return null;
      }
      if (discount < 0 || discount > 100) {
        warn('Invalid discount', (row.productName || 'Product') + ' — discount must be 0 to 100.');
        return null;
      }
      items.push({
        productCode: row.productCode,
        productName: row.productName,
        qty: qty,
        rate: rate,
        discount: discount,
        amount: lineAmount(qty, rate, discount),
      });
    }

    if (!items.length) {
      warn('Product required', 'Add at least one valid product before saving.');
      return null;
    }

    return items;
  }

  document.getElementById('orderForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const items = validateBeforeSave();
    if (!items) return;

    const notes = document.getElementById('notes').value || '';
    const payload = {
      _token: CSRF,
      orderNo: document.getElementById('orderNo').value,
      orderDate: document.getElementById('orderDate').value,
      customerCode: els.customerCode.value,
      customerName: els.customerName.value,
      city: els.city.value || '',
      gudsName: document.getElementById('gudsName').value || '',
      comments: notes,
      notes: notes,
      noOfItems: items.length,
      orderTotal: parseFloat(document.getElementById('orderTotal').value || 0),
      orderDiscount: 0,
      grandTotal: parseFloat(document.getElementById('grandTotal').value || 0),
      items,
    };

    const saveBtns = [document.getElementById('btnSaveOrder'), document.getElementById('btnSaveOrderSticky')];
    saveBtns.forEach(b => { if (b) b.disabled = true; });

    try {
      const res = await fetch(routes.store, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify(payload),
      });
      const data = await res.json().catch(function () { return {}; });
      if (!res.ok || !data.success) {
        const msg = firstValidationMessage(data) || 'Failed to save order';
        if (typeof showError === 'function') showError(msg);
        else warn('Cannot save', msg);
        return;
      }
      if (typeof Swal !== 'undefined') {
        await Swal.fire({
          icon: 'success',
          title: 'Order saved',
          text: data.message || ('Order ' + data.orderNo + ' created'),
          confirmButtonColor: '#0f2747',
          timer: 1400,
          showConfirmButton: false,
        });
      }
      window.location.href = data.previewUrl;
    } catch (err) {
      if (typeof showError === 'function') showError('Failed to save order');
      else warn('Error', 'Failed to save order');
    } finally {
      setSaveEnabled(cart.length > 0);
    }
  });

  // initial render — all products
  filterLocalProducts();
  renderCart();
})();
</script>
@endpush
