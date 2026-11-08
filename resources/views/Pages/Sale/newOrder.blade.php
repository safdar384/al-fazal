@extends('layout.app')

@section('title', 'New Order')

@section('content')
@include('partials.sale.erp-styles')

<style>
    #customerSearch, .productSearch {
        border: 2px solid #e0e0e0;
        border-radius: 0.5rem;
        background-color: #f8f9fa;
    transition: all 0.2s ease-in-out;
    }
    #customerSearch:focus, .productSearch:focus {
    border-color: #1a4a8a;
        background-color: #fff;
    box-shadow: 0 0 0 0.2rem rgba(15, 39, 71, 0.12);
        outline: 0;
    }
    #orderItemsTableWrap.table-responsive {
        overflow-x: auto !important;
        overflow-y: visible !important;
        -webkit-overflow-scrolling: touch;
    }
    #itemsTable tbody td {
        position: relative;
        overflow: visible !important;
    vertical-align: middle;
    }
    #itemsTable tbody tr.product-dropdown-open {
        position: relative;
        z-index: 200;
    }
    #customerDropdown, .productDropdown {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 10050;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background: #fff;
        box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.2);
        margin-top: 4px;
        max-height: 320px;
        overflow-y: auto;
        width: 100%;
        min-width: 260px;
        display: none;
        pointer-events: auto;
    }
    .dropdown-item-custom {
        padding: 10px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f1f3f5;
        font-size: 0.85rem;
    }
    .dropdown-item-custom:last-child { border-bottom: none; }
    .dropdown-item-custom:hover, .dropdown-item-custom.active {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
  }
  .dropdown-item-custom:hover .text-muted,
  .dropdown-item-custom.active .text-muted {
    color: rgba(255,255,255,0.9) !important;
  }
  .dropdown-item-custom.d-flex {
    align-items: center;
    gap: 0.5rem;
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
  .position-relative { position: relative !important; }
  #newOrderCard.card .card-body { overflow: visible !important; }

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
  }
  .sale-invoice-fluid .form-control:focus,
  .sale-invoice-fluid .form-select:focus {
    border-color: #1a4a8a;
    box-shadow: 0 0 0 0.2rem rgba(15, 39, 71, 0.12);
  }
  .sale-invoice-fluid .erp-invoice-product-wrap table {
    width: 100%;
    min-width: 40rem;
    table-layout: fixed;
  }
  .sale-invoice-fluid #itemsTable th,
  .sale-invoice-fluid #itemsTable td {
    padding: clamp(0.28rem, 0.45cqi, 0.55rem);
    vertical-align: middle;
  }
  .sale-invoice-fluid #itemsTable .form-control {
    min-height: clamp(1.7rem, 1.35rem + 0.9cqi, 2.2rem);
  }
  .sale-invoice-fluid #itemsTable .btn-group {
    display: flex;
    width: 100%;
  }
  .sale-invoice-fluid #itemsTable .btn-group .btn {
    flex: 1 1 auto;
    padding: clamp(0.15rem, 0.3cqi, 0.35rem) clamp(0.2rem, 0.35cqi, 0.45rem);
  }
  .sale-invoice-fluid #grandTotal {
    font-size: clamp(1.05rem, 0.9rem + 0.7cqi, 1.45rem);
  }
  .sale-invoice-fluid .sale-save-desktop .btn {
    min-height: clamp(2.2rem, 1.8rem + 1cqi, 2.75rem);
    font-size: clamp(0.9rem, 0.8rem + 0.35cqi, 1.05rem);
  }

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
  .badge-demand {
    background: #e8eef5;
    color: #0f2747;
    border: 1px solid #d5dde8;
        font-weight: 600;
    }
  .btn-save-order {
    background: #0f2747;
    border-color: #0f2747;
    color: #fff;
  }
  .btn-save-order:hover {
    background: #1a4a8a;
    border-color: #1a4a8a;
    color: #fff;
  }
  #itemsTable .row-actions {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
  }
  #itemsTable .btn-row-add,
  #itemsTable .btn-row-del {
    width: 2rem;
    height: 2rem;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.35rem;
    font-size: 0.85rem;
    line-height: 1;
  }
  #itemsTable .btn-row-add {
    background: #198754;
    border-color: #198754;
    color: #fff;
  }
  #itemsTable .btn-row-add:hover {
    background: #157347;
    border-color: #146c43;
    color: #fff;
  }
  #itemsTable .btn-row-del {
    background: #fff;
    border: 1px solid #f1b0b7;
    color: #dc3545;
  }
  #itemsTable .btn-row-del:hover:not(:disabled) {
    background: #dc3545;
    border-color: #dc3545;
    color: #fff;
  }
  #itemsTable .btn-row-del:disabled {
    opacity: 0.35;
    cursor: not-allowed;
  }

  @media (max-width: 991.98px) {
    .sale-invoice-fluid .sale-save-desktop { display: none !important; }
    }
</style>

<main class="app-main erp-invoice-page sale-invoice-fluid" id="main" tabindex="-1">
    <div class="app-content-header">
    <div class="container-fluid px-2 px-md-3">
            <div class="row align-items-center gy-2">
                <div class="col-12 col-md-6">
          <h3 class="mb-0 fw-bold" style="color:#0f2747;">
            <i class="bi bi-journal-plus me-2"></i>New Order
          </h3>
          <p class="text-muted small mb-0">Sale · Demand only — no stock · no ledger · no payment</p>
                    </div>
        <div class="col-12 col-md-6">
          <ol class="breadcrumb float-md-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Sale</span></li>
            <li class="breadcrumb-item active" aria-current="page">New Order</li>
          </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid px-2 px-md-3">
                    <form id="orderForm">
                        @csrf
        <div class="card si-card border-0 mb-3" id="newOrderCard">
          <div class="card-header si-card-head">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
              <h6 class="mb-0 text-white fw-semibold">
                <i class="bi bi-file-earmark-text me-2"></i>Order Information
              </h6>
              <span class="badge badge-demand">Demand only</span>
                            </div>
                            </div>
          <div class="card-body p-2 p-md-3">
            <div class="sale-invoice-fields">
              <div class="sale-field-wide position-relative">
                <label class="form-label si-label"><i class="bi bi-person-badge me-1"></i>Customer</label>
                <input type="text" id="customerSearch" class="form-control form-control-sm" placeholder="Type to search customer..." autocomplete="off" required>
                                <input type="hidden" name="customerCode" id="customerCode">
                                <input type="hidden" name="customerName" id="customerName">
                                <div id="customerDropdown"></div>
                            </div>
              <div>
                <label class="form-label si-label">City</label>
                                <input type="text" name="city" id="city" class="form-control form-control-sm" readonly>
                            </div>
              <div>
                <label class="form-label si-label">Date</label>
                <input type="date" name="orderDate" id="orderDate" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required>
              </div>
              <div>
                <label class="form-label si-label">Order No</label>
                <input type="text" name="orderNo" id="orderNo" class="form-control form-control-sm" value="{{ $nextOrderNo }}" readonly>
              </div>
            </div>
          </div>
        </div>

        <div class="card si-card border-0 mb-2">
          <div class="card-header si-card-head py-2">
            <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-cart-plus me-2"></i>Products</h6>
          </div>
          <div class="card-body p-0">
            <div class="erp-invoice-product-wrap table-responsive erp-table-compact" id="orderItemsTableWrap">
              <table class="table table-hover table-sm table-bordered align-middle mb-0 si-table" id="itemsTable">
                <colgroup>
                  <col style="width: 32%;">
                  <col style="width: 12%;">
                  <col style="width: 14%;">
                  <col style="width: 14%;">
                  <col style="width: 18%;">
                  <col style="width: 10%;">
                </colgroup>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="sale-invoice-split">
          <div class="card si-card border-0">
            <div class="card-header si-card-head py-2">
              <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-info-circle me-2"></i>Additional Information</h6>
            </div>
            <div class="card-body p-2 p-md-3">
              <div class="sale-invoice-extra">
                <div class="sale-field-wide">
                  <label class="form-label si-label">Guds Name</label>
                                <select name="gudsName" id="gudsName" class="form-select form-select-sm">
                                    <option value="">Select Guds</option>
                                    @foreach($guds as $gud)
                                        <option value="{{ $gud->gudsName }}">{{ $gud->gudsName }}</option>
                                    @endforeach
                                </select>
                            </div>
                <div class="sale-field-full">
                  <label class="form-label si-label">Notes</label>
                                <input type="text" name="notes" id="notes" class="form-control form-control-sm" placeholder="Optional order notes">
                                <input type="hidden" name="comments" id="comments" value="">
                            </div>
                        </div>
                                </div>
                            </div>
          <div class="card si-card border-0">
            <div class="card-header si-card-head py-2">
              <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-calculator me-2"></i>Order Summary</h6>
            </div>
            <div class="card-body p-2 p-md-3">
              <div class="sale-invoice-summary">
                <div class="sale-summary-row">
                  <div>
                    <label class="form-label si-label small">No. of Items</label>
                    <input type="number" name="noOfItems" id="noOfItems" class="form-control form-control-sm" value="0" readonly>
                  </div>
                  <div>
                    <label class="form-label si-label small">Order Total</label>
                    <input type="text" name="orderTotal" id="orderTotal" class="form-control form-control-sm fw-bold" value="0.00" readonly>
                  </div>
                </div>
                <div class="sale-summary-row">
                  <div>
                    <label class="form-label si-label small">Customer Discount %</label>
                    <input type="text" class="form-control form-control-sm fw-semibold text-primary" id="summaryCustomerDiscount" readonly value="0" placeholder="0">
                  </div>
                  <div>
                    <label class="form-label si-label small">Discount Amount</label>
                    <input type="text" name="orderDiscount" id="orderDiscount" class="form-control form-control-sm" value="0.00" readonly>
                  </div>
                </div>
                <div class="sale-field-full">
                  <label class="form-label si-label small text-success">Grand Total</label>
                  <input type="text" name="grandTotal" id="grandTotal" class="form-control fw-bold text-success si-grand" value="0.00" readonly>
                        </div>
                <div class="sale-field-full sale-save-desktop d-flex gap-2">
                  <button type="submit" class="btn btn-save-order flex-grow-1" id="btnSaveOrder">
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
    <button type="submit" form="orderForm" class="btn btn-save-order flex-grow-1" id="btnSaveOrderSticky">
            <i class="bi bi-check2-circle me-1"></i>Save order
        </button>
    <a href="{{ route('order.summary') }}" class="btn btn-outline-secondary flex-shrink-0">Back</a>
    </div>
</main>

<script>
const products = {!! $products->map(function ($p) {
    $stock = (float) ($p->stockQty ?? 0);
    if ($p->relationLoaded('variants') && $p->variants->count() > 0) {
        $stock = (float) $p->variants->sum('stockQty');
    }

    return [
        'code' => $p->productCode,
        'name' => $p->productName,
        'company' => $p->companyName ?? '',
        'rate' => (float) ($p->saleRate ?? 0),
        'stockQty' => $stock,
        'image' => $p->image_url,
        'hasVariants' => $p->relationLoaded('variants') && $p->variants->count() > 0,
    ];
})->values()->toJson() !!};
let customerDiscountPct = 0;
const tbody = document.querySelector('#itemsTable tbody');

/** Move focus to next logical field when user presses Enter */
function focusAndSelect(el) {
    if (!el || el.disabled) return false;
    try {
        el.focus();
        if (el.tagName === 'INPUT' && el.type !== 'date' && el.type !== 'hidden' && typeof el.select === 'function') {
            el.select();
        }
    } catch (_) {}
    return true;
}

function getHeaderFocusChain() {
    return [
        document.getElementById('city'),
        document.getElementById('orderDate'),
        document.getElementById('orderNo'),
    ].filter(Boolean);
}

function focusGuds() {
    const guds = document.getElementById('gudsName');
    if (guds) focusAndSelect(guds);
}

function focusFirstProductSearch() {
    const ps = tbody.querySelector('tr .productSearch');
    return ps ? focusAndSelect(ps) : false;
}

/**
 * @param {HTMLElement} fromEl
 */
function focusNextFieldFrom(fromEl) {
    if (fromEl && fromEl.id === 'gudsName') {
        focusAndSelect(document.getElementById('notes'));
        return;
    }
    if (fromEl && fromEl.id === 'notes') {
        const save = document.getElementById('btnSaveOrder');
        if (save) save.focus();
        return;
    }

    const header = getHeaderFocusChain();
    const hIdx = header.indexOf(fromEl);
    if (hIdx !== -1) {
        if (hIdx < header.length - 1) {
            focusAndSelect(header[hIdx + 1]);
        } else {
            focusFirstProductSearch();
        }
        return;
    }

    const tr = fromEl.closest ? fromEl.closest('tr') : null;
    if (!tr || !tbody.contains(tr)) return;

    if (fromEl.classList.contains('productSearch')) {
        const typed = String(fromEl.value || '').trim();
        if (!tr.querySelector('.productCode').value) {
            if (typed) {
                showWarning('Product not found', 'No product matches your search. Clear the field or try another name/code.');
                focusAndSelect(fromEl);
                return;
            }
            focusGuds();
            return;
        }
        focusAndSelect(tr.querySelector('.qty'));
        return;
    }
    if (fromEl.classList.contains('qty')) {
        if (!tr.querySelector('.productCode').value) {
            focusAndSelect(tr.querySelector('.productSearch'));
            return;
        }
        focusAndSelect(tr.querySelector('.rate'));
        return;
    }
    if (fromEl.classList.contains('rate')) {
        focusAndSelect(tr.querySelector('.discount'));
        return;
    }
    if (fromEl.classList.contains('discount')) {
        const addBtn = tr.querySelector('.addRowInline');
        if (addBtn) addBtn.focus();
    }
}

/** Enter key → next field (respect dropdowns: caller may preventDefault first) */
function bindEnterToNext(el) {
    if (!el) return;
    el.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        focusNextFieldFrom(el);
    });
}

function addRow(fromButton) {
    if (fromButton && fromButton.nodeType === 1) {
        const sourceRow = fromButton.closest('tr');
        if (!sourceRow || !sourceRow.querySelector('.productCode').value) {
            focusGuds();
            return;
        }
    }
    const tr = document.createElement('tr');
    const discPh = customerDiscountPct > 0 ? ('Cust ' + customerDiscountPct + '%') : 'Disc %';
    const discVal = customerDiscountPct > 0 ? customerDiscountPct : '';
    tr.innerHTML = `
        <td>
            <div class="position-relative">
                <input type="text" class="form-control form-control-sm productSearch" placeholder="Type to search product..." autocomplete="off" required>
                <input type="hidden" class="productCode">
                <input type="hidden" class="productName">
                <div class="productDropdown"></div>
            </div>
        </td>
        <td><input type="number" class="form-control form-control-sm text-center qty" min="1" value="1" required placeholder="Qty"></td>
        <td><input type="number" class="form-control form-control-sm text-end rate" min="0" step="0.01" value="" required placeholder="Rate"></td>
        <td><input type="number" class="form-control form-control-sm text-end discount" min="0" max="100" step="0.01" value="${discVal}" placeholder="${discPh}"></td>
        <td><input type="number" class="form-control form-control-sm text-end amount" value="" readonly placeholder="Amount"></td>
        <td class="text-center">
            <div class="row-actions">
                <button type="button" class="btn btn-row-add addRowInline" title="Add row">
                    <i class="bi bi-plus-lg"></i>
                </button>
                <button type="button" class="btn btn-row-del removeRow" title="Delete row">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>
        </td>
    `;
    tbody.appendChild(tr);
    bindRow(tr);
    syncRemoveButtons();
    recalc();
    if (fromButton && fromButton.nodeType === 1) {
        const search = tr.querySelector('.productSearch');
        setTimeout(() => focusAndSelect(search), 50);
    }
}

function syncRemoveButtons() {
    const rows = [...tbody.querySelectorAll('tr')];
    rows.forEach(function (row, index) {
        const btn = row.querySelector('.removeRow');
        if (!btn) return;
        // First row always stays; extra rows can be deleted
        const locked = index === 0 || rows.length <= 1;
        btn.disabled = locked;
        btn.title = locked ? 'First row cannot be deleted' : 'Delete row';
    });
}

function bindRow(tr) {
    const productCode = tr.querySelector('.productCode');
    const productName = tr.querySelector('.productName');
    const productSearch = tr.querySelector('.productSearch');
    const productDropdown = tr.querySelector('.productDropdown');
    const qty = tr.querySelector('.qty');
    const rate = tr.querySelector('.rate');
    const discount = tr.querySelector('.discount');
    const amount = tr.querySelector('.amount');

    function rowCalc() {
        const q = parseFloat(qty.value || 0);
        const r = parseFloat(rate.value || 0);
        const d = parseFloat(discount.value || 0);
        const gross = q * r;
        const net = gross - (gross * d / 100);
        amount.value = net.toFixed(2);
        recalc();
    }
    function setProductDropdownOpen(open) {
        tr.classList.toggle('product-dropdown-open', !!open);
    }

    function renderProducts(term = '') {
        const q = term.toLowerCase().trim();
        if (!q) {
            productDropdown.innerHTML = '';
            productDropdown.style.display = 'none';
            setProductDropdownOpen(false);
            if (typeof erpResetFloatingDropdown === 'function') erpResetFloatingDropdown(productDropdown);
            return;
        }
        const filtered = products.filter(p =>
            (p.name || '').toLowerCase().includes(q) ||
            (p.code || '').toLowerCase().includes(q) ||
            (p.company || '').toLowerCase().includes(q)
        ).slice(0, 10);
        if (filtered.length === 0) {
            productDropdown.innerHTML = '<div class="dropdown-empty text-muted px-2 py-2">No products found</div>';
            productDropdown.style.display = 'block';
            setProductDropdownOpen(true);
            if (typeof erpPositionFloatingDropdown === 'function') {
                erpPositionFloatingDropdown(productSearch, productDropdown);
            }
            return;
        }
        productDropdown.innerHTML = filtered.map((p, idx) => {
            const stock = parseFloat(p.stockQty) || 0;
            const stockClass = stock <= 0 ? 'bg-danger' : (stock <= 5 ? 'bg-warning text-dark' : 'bg-success');
            const thumb = p.image
                ? `<img src="${p.image}" alt="" class="product-search-thumb rounded border">`
                : `<span class="product-search-thumb product-search-thumb-empty rounded border d-inline-flex align-items-center justify-content-center bg-light text-muted"><i class="bi bi-image"></i></span>`;
            return `
            <div class="dropdown-item-custom d-flex ${idx === 0 ? 'active' : ''}" data-code="${p.code}" data-name="${p.name}" data-rate="${p.rate}">
                ${thumb}
                <div class="flex-grow-1 min-w-0">
                    <strong class="d-block text-truncate">${p.name}</strong>
                    <div class="text-muted small text-truncate">${p.code}${p.company ? ' · ' + p.company : ''}</div>
                </div>
                <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                    <span class="badge ${stockClass}">Stock: ${Number.isInteger(stock) ? stock : stock.toFixed(2)}</span>
                    <span class="badge bg-info">Rs.${parseFloat(p.rate).toFixed(2)}</span>
                </div>
            </div>`;
        }).join('');
        productDropdown.style.display = 'block';
        setProductDropdownOpen(true);
        if (typeof erpPositionFloatingDropdown === 'function') {
            erpPositionFloatingDropdown(productSearch, productDropdown);
        }
    }
    function selectProduct(code, name, r) {
        const pMeta = products.find(function (x) { return String(x.code) === String(code); });
        // Non-variant products: block duplicate. Variant products: allow multiple lines.
        const hasVariants = !!(pMeta && pMeta.hasVariants);
        if (!hasVariants) {
            const duplicate = [...tbody.querySelectorAll('tr')].some(function (other) {
                return other !== tr && other.querySelector('.productCode').value === code;
            });
            if (duplicate) {
                productSearch.value = '';
                productCode.value = '';
                productName.value = '';
                productDropdown.style.display = 'none';
                setProductDropdownOpen(false);
                if (typeof erpResetFloatingDropdown === 'function') erpResetFloatingDropdown(productDropdown);
                showWarning('Duplicate product', 'This product is already added.');
                productSearch.focus();
                return;
            }
        }
        productCode.value = code;
        productName.value = name;
        productSearch.value = `${name} (${code})`;
        rate.value = parseFloat(r || 0).toFixed(2);
        if (customerDiscountPct > 0 && !(parseFloat(discount.value) > 0)) {
            discount.value = customerDiscountPct;
        }
        productDropdown.style.display = 'none';
        setProductDropdownOpen(false);
        if (typeof erpResetFloatingDropdown === 'function') {
            erpResetFloatingDropdown(productDropdown);
        }
        rowCalc();
        setTimeout(() => {
            qty.focus();
            qty.select();
        }, 50);
    }

    productSearch.addEventListener('focus', () => {
        if (productSearch.value.trim()) renderProducts(productSearch.value);
    });
    productSearch.addEventListener('input', () => {
        productCode.value = '';
        productName.value = '';
        renderProducts(productSearch.value);
    });
    /* Keep focus on input until click completes — avoids list disappearing before selection */
    productDropdown.addEventListener('mousedown', (e) => e.preventDefault());
    productDropdown.addEventListener('click', (e) => {
        const item = e.target.closest('.dropdown-item-custom');
        if (!item) return;
        e.stopPropagation();
        selectProduct(item.dataset.code, item.dataset.name, item.dataset.rate);
    });
    productSearch.addEventListener('keydown', (e) => {
        const items = [...productDropdown.querySelectorAll('.dropdown-item-custom[data-code]')];
        const activeIndex = items.findIndex(i => i.classList.contains('active'));

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!items.length) return;
            const next = activeIndex < 0 ? 0 : Math.min(activeIndex + 1, items.length - 1);
            items.forEach(i => i.classList.remove('active'));
            items[next].classList.add('active');
            if (productDropdown.style.display === 'block' && typeof erpPositionFloatingDropdown === 'function') {
                erpPositionFloatingDropdown(productSearch, productDropdown);
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (!items.length) return;
            const prev = activeIndex <= 0 ? 0 : activeIndex - 1;
            items.forEach(i => i.classList.remove('active'));
            items[prev].classList.add('active');
            if (productDropdown.style.display === 'block' && typeof erpPositionFloatingDropdown === 'function') {
                erpPositionFloatingDropdown(productSearch, productDropdown);
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();
            if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
            const selected = items.find(i => i.classList.contains('active') && i.dataset.code);
            const typed = String(productSearch.value || '').trim();
            if (selected && selected.dataset.code) {
                selectProduct(selected.dataset.code, selected.dataset.name, selected.dataset.rate);
            } else if (productCode.value) {
                focusNextFieldFrom(productSearch);
            } else if (!typed) {
                productDropdown.style.display = 'none';
                setProductDropdownOpen(false);
                if (typeof erpResetFloatingDropdown === 'function') erpResetFloatingDropdown(productDropdown);
                focusGuds();
            } else {
                showWarning('Product not found', 'No product matches your search. Clear the field or try another name/code.');
                productDropdown.style.display = 'none';
                setProductDropdownOpen(false);
                if (typeof erpResetFloatingDropdown === 'function') erpResetFloatingDropdown(productDropdown);
                productSearch.focus();
            }
        } else if (e.key === 'Escape') {
            productDropdown.style.display = 'none';
            setProductDropdownOpen(false);
            if (typeof erpResetFloatingDropdown === 'function') {
                erpResetFloatingDropdown(productDropdown);
            }
        }
    });

    qty.addEventListener('input', function () {
        if (!productCode.value) {
            productSearch.focus();
            return;
        }
        rowCalc();
    });
    rate.addEventListener('input', rowCalc);
    discount.addEventListener('input', rowCalc);
    bindEnterToNext(qty);
    bindEnterToNext(rate);
    bindEnterToNext(discount);
    tr.querySelector('.addRowInline').addEventListener('click', function () {
        addRow(this);
    });
    tr.querySelector('.removeRow').addEventListener('click', function () {
        const rows = [...tbody.querySelectorAll('tr')];
        const index = rows.indexOf(tr);
        if (index === 0 || rows.length <= 1) {
            if (typeof showWarning === 'function') {
                showWarning('First row cannot be deleted');
            }
            return;
        }
        setProductDropdownOpen(false);
        if (typeof erpResetFloatingDropdown === 'function') {
            erpResetFloatingDropdown(productDropdown);
        }
        tr.remove();
        syncRemoveButtons();
        recalc();
    });
}

function recalc() {
    const rows = [...tbody.querySelectorAll('tr')];
    let orderTotal = 0, grandTotal = 0;
    rows.forEach(tr => {
        const q = parseFloat(tr.querySelector('.qty').value || 0);
        const r = parseFloat(tr.querySelector('.rate').value || 0);
        const a = parseFloat(tr.querySelector('.amount').value || 0);
        orderTotal += q * r;
        grandTotal += a;
    });
    const discountTotal = orderTotal - grandTotal;
    document.getElementById('orderTotal').value = orderTotal.toFixed(2);
    document.getElementById('orderDiscount').value = discountTotal.toFixed(2);
    document.getElementById('grandTotal').value = grandTotal.toFixed(2);
    document.getElementById('noOfItems').value = rows.length;
}

const customers = {!! $customers->map(function ($c) {
    $biz = trim((string) ($c->bussnessName ?: $c->custName));
    $city = trim((string) ($c->custCity ?? ''));

    return [
        'code' => $c->custCode,
        'name' => $c->custName,
        'business' => $c->bussnessName ?? '',
        'city' => $city,
        'discount' => (float) ($c->custdiscount ?? 0),
        'label' => $city !== '' ? $biz.' — '.$city : $biz,
    ];
})->values()->toJson() !!};

function setCustomerDiscountDisplay(pct) {
    customerDiscountPct = parseFloat(pct) || 0;
    const el = document.getElementById('summaryCustomerDiscount');
    if (el) {
        el.value = customerDiscountPct > 0 ? String(Number(customerDiscountPct.toFixed(2))) : '0';
    }
    const ph = customerDiscountPct > 0 ? ('Cust ' + customerDiscountPct + '%') : 'Disc %';
    tbody.querySelectorAll('tr .discount').forEach(function (inp) {
        inp.setAttribute('placeholder', ph);
    });
}

function applyCustomerDiscountToProductLines() {
    const pct = customerDiscountPct;
    tbody.querySelectorAll('tr').forEach(function (tr) {
        const disc = tr.querySelector('.discount');
        if (!disc) return;
        disc.value = pct > 0 ? pct : '';
        const code = tr.querySelector('.productCode')?.value;
        if (code) {
            const qty = parseFloat(tr.querySelector('.qty')?.value || 0);
            const rate = parseFloat(tr.querySelector('.rate')?.value || 0);
            const d = parseFloat(disc.value || 0);
            const gross = qty * rate;
            tr.querySelector('.amount').value = (gross - (gross * d / 100)).toFixed(2);
        }
    });
    recalc();
}
const customerSearch = document.getElementById('customerSearch');
const customerDropdown = document.getElementById('customerDropdown');

function customerLabel(c) {
    const biz = (c.business || c.name || '').trim();
    const city = (c.city || '').trim();
    return city ? `${biz} — ${city}` : biz;
}

function renderCustomers(term = '') {
    const q = term.toLowerCase().trim();
    const filtered = customers.filter(c =>
        (c.name || '').toLowerCase().includes(q) ||
        (c.business || '').toLowerCase().includes(q) ||
        (c.city || '').toLowerCase().includes(q) ||
        (c.code || '').toLowerCase().includes(q) ||
        (c.label || '').toLowerCase().includes(q)
    ).slice(0, 40);
    customerDropdown.innerHTML = filtered.map((c, idx) => {
        const title = (c.business || c.name || '').trim();
        const sub = [c.city, c.business && c.name ? c.name : ''].filter(Boolean).join(' · ');
        return `
        <div class="dropdown-item-custom ${idx === 0 ? 'active' : ''}" data-code="${c.code}" data-name="${(c.business || c.name || '').replace(/"/g, '&quot;')}" data-city="${c.city}">
            <strong>${title}</strong>
            <div class="text-muted" style="font-size:0.75rem;">${sub || c.code}</div>
        </div>`;
    }).join('');
    customerDropdown.style.display = filtered.length ? 'block' : 'none';
}

function selectCustomer(code, name, city) {
    const cust = customers.find(c => c.code == code);
    const storeName = cust ? ((cust.business || cust.name || '').trim()) : name;
    document.getElementById('customerCode').value = code;
    document.getElementById('customerName').value = storeName;
    customerSearch.value = cust ? customerLabel(cust) : `${name}${city ? ' — ' + city : ''}`;
    document.getElementById('city').value = city || '';
    customerDropdown.style.display = 'none';
    setCustomerDiscountDisplay(cust ? (cust.discount || 0) : 0);
    applyCustomerDiscountToProductLines();
    focusFirstProductSearch();
}

customerSearch.addEventListener('focus', () => {
    if (customerSearch.value.trim()) renderCustomers(customerSearch.value);
});
customerSearch.addEventListener('input', () => {
    document.getElementById('customerCode').value = '';
    document.getElementById('customerName').value = '';
    setCustomerDiscountDisplay(0);
    renderCustomers(customerSearch.value);
});
customerDropdown.addEventListener('mousedown', (e) => e.preventDefault());
customerDropdown.addEventListener('click', (e) => {
    const item = e.target.closest('.dropdown-item-custom');
    if (!item) return;
    selectCustomer(item.dataset.code, item.dataset.name, item.dataset.city);
});
customerSearch.addEventListener('keydown', (e) => {
    const items = [...customerDropdown.querySelectorAll('.dropdown-item-custom')];
    const activeIndex = items.findIndex(i => i.classList.contains('active'));
    const listOpen = customerDropdown.style.display !== 'none' && items.length > 0;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (!items.length) return;
        const next = activeIndex < 0 ? 0 : Math.min(activeIndex + 1, items.length - 1);
        items.forEach(i => i.classList.remove('active'));
        items[next].classList.add('active');
        return;
    }
    if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (!items.length) return;
        const prev = activeIndex <= 0 ? 0 : activeIndex - 1;
        items.forEach(i => i.classList.remove('active'));
        items[prev].classList.add('active');
        return;
    }
    if (e.key === 'Enter') {
        e.preventDefault();
        if (listOpen) {
            const pick = items.find(i => i.classList.contains('active')) || items[0];
            selectCustomer(pick.dataset.code, pick.dataset.name, pick.dataset.city);
            return;
        }
        focusFirstProductSearch();
        return;
    }
    if (e.key === 'Escape') {
        customerDropdown.style.display = 'none';
    }
});
bindEnterToNext(document.getElementById('city'));
bindEnterToNext(document.getElementById('orderDate'));
bindEnterToNext(document.getElementById('orderNo'));
bindEnterToNext(document.getElementById('gudsName'));
bindEnterToNext(document.getElementById('notes'));
document.addEventListener('click', (e) => {
    if (!e.target.closest('#customerSearch') && !e.target.closest('#customerDropdown')) {
        customerDropdown.style.display = 'none';
    }
    if (!e.target.closest('.productSearch') && !e.target.closest('.productDropdown')) {
        document.querySelectorAll('.productDropdown').forEach(d => {
            d.style.display = 'none';
            if (typeof erpResetFloatingDropdown === 'function') {
                erpResetFloatingDropdown(d);
            }
        });
        document.querySelectorAll('#itemsTable tbody tr.product-dropdown-open').forEach(r => r.classList.remove('product-dropdown-open'));
    }
});

window.addEventListener('resize', () => {
    document.querySelectorAll('#itemsTable tbody .productDropdown').forEach(d => {
        if (d.style.display !== 'block') return;
        const tr = d.closest('tr');
        const inp = tr && tr.querySelector('.productSearch');
        if (inp && typeof erpPositionFloatingDropdown === 'function') {
            erpPositionFloatingDropdown(inp, d);
        }
    });
});

document.getElementById('orderForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const customerCode = document.getElementById('customerCode').value;
    const customerName = document.getElementById('customerName').value;
    if (!customerCode || !customerName) return alert('Select customer');

    const items = [...tbody.querySelectorAll('tr')].map(tr => {
        const code = tr.querySelector('.productCode').value;
        const name = tr.querySelector('.productName').value;
        return {
            productCode: code,
            productName: name || '',
            qty: parseFloat(tr.querySelector('.qty').value || 0),
            rate: parseFloat(tr.querySelector('.rate').value || 0),
            discount: parseFloat(tr.querySelector('.discount').value || 0),
            amount: parseFloat(tr.querySelector('.amount').value || 0),
        };
    }).filter(i => i.productCode);
    if (items.length === 0) return alert('Add at least one item.');

    const payload = {
        _token: document.querySelector('input[name="_token"]').value,
        orderNo: document.querySelector('input[name="orderNo"]').value,
        orderDate: document.querySelector('input[name="orderDate"]').value,
        customerCode,
        customerName,
        city: document.getElementById('city').value,
        gudsName: document.querySelector('select[name="gudsName"]').value,
        comments: document.querySelector('input[name="comments"]').value,
        noOfItems: parseInt(document.getElementById('noOfItems').value || '0', 10),
        orderTotal: parseFloat(document.getElementById('orderTotal').value || 0),
        orderDiscount: parseFloat(document.getElementById('orderDiscount').value || 0),
        grandTotal: parseFloat(document.getElementById('grandTotal').value || 0),
        items
    };

    const res = await fetch(`{{ route('order.store') }}`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (!res.ok || !data.success) return alert(data.message || 'Failed to save order');
    window.location.href = data.previewUrl;
});

addRow();
setTimeout(() => focusAndSelect(document.getElementById('customerSearch')), 200);
</script>
@endsection
