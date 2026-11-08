@extends('layout.app')

@section('title', 'Purchase Summary')

@section('content')
@include('partials.sale.erp-styles')
@include('components.toast-notification')

<style>
  .ss-kpi {
    display: flex; align-items: center; gap: 0.75rem;
    background: #fff; border: 1px solid #d5dde8; border-left: 4px solid #0f2747;
    border-radius: 4px; padding: 0.85rem 1rem; height: 100%;
    box-shadow: 0 2px 8px rgba(15, 39, 71, 0.05);
  }
  .ss-kpi.amt { border-left-color: #198754; background: #eefaf2; }
  .ss-kpi.items { border-left-color: #c77c00; background: #fff8ec; }
  .ss-kpi.avg { border-left-color: #1a4a8a; background: #f4f7fb; }
  .ss-kpi-icon {
    width: 42px; height: 42px; border-radius: 6px; background: #e8eef5; color: #0f2747;
    display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;
  }
  .ss-kpi-icon.amt { background: #e8f5e9; color: #198754; }
  .ss-kpi-icon.items { background: #fff3cd; color: #c77c00; }
  .ss-kpi-icon.avg { background: #dde5f0; color: #1a4a8a; }
  .ss-kpi-lbl {
    font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.3px; color: #6b7c8d; margin-bottom: 0.15rem;
  }
  .ss-kpi-val { font-size: 1.05rem; font-weight: 700; color: #0f2747; line-height: 1.2; }

  .ss-card {
    box-shadow: 0 2px 12px rgba(15, 39, 71, 0.08);
    border-radius: 4px; overflow: visible; border: 1px solid #d5dde8;
  }
  .ss-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    border: none; border-radius: 4px 4px 0 0; padding: 0.75rem 1rem; color: #fff;
  }
  .ss-label { font-size: 0.78rem; color: #0f2747; margin-bottom: 0.25rem; font-weight: 600; }
  .ss-table thead th {
    background: #0f2747; color: #fff; font-size: 0.68rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.3px; border-color: #0a1c33;
    white-space: nowrap; vertical-align: middle; padding: 0.55rem 0.65rem;
  }
  .ss-table tbody td {
    font-size: 0.82rem; border-color: #e8eef5; vertical-align: middle; padding: 0.55rem 0.65rem;
  }
  .ss-table tbody tr:nth-child(even) td { background: #f4f7fb; }
  .ss-table tbody tr:hover td { background: #e8eef5; }

  .select2-container--bootstrap-5 .select2-selection {
    border: 1px solid #d5dde8 !important;
    border-radius: 0.375rem !important;
    min-height: calc(2.25rem + 2px) !important;
    display: flex;
    align-items: center;
  }
  .select2-container--bootstrap-5.select2-container--focus .select2-selection,
  .select2-container--bootstrap-5.select2-container--open .select2-selection {
    border-color: #1a4a8a !important;
    box-shadow: 0 0 0 0.2rem rgba(15, 39, 71, 0.12) !important;
  }
  .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
    padding-left: .75rem;
    padding-right: 2rem;
    line-height: 2.1rem;
    font-size: .9rem;
  }
  .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
    height: 100%;
    right: .65rem;
  }
  .select2-container--bootstrap-5 .select2-dropdown { border: 1px solid #d5dde8; z-index: 1061; }
  .select2-container--open { z-index: 1060 !important; }
  .select2-container--bootstrap-5 .select2-results__option--highlighted {
    background-color: #0f2747;
    color: #fff;
  }
  .select2-container--bootstrap-5 .select2-results > .select2-results__options {
    max-height: 260px !important;
    overflow-y: auto !important;
  }

  #purchaseSummaryLive.is-loading { opacity: 0.45; pointer-events: none; }
  #invoiceTable .purchase-actions .btn { margin: 0 1px; }
  #purchasePaginationBar {
    background: #fff !important;
    border-color: #e8eef5 !important;
  }
  #purchasePaginationControls .page-link {
    color: #0f2747;
    border-color: #d5dde8;
    min-width: 2rem;
    text-align: center;
  }
  #purchasePaginationControls .page-item.active .page-link {
    background: #0f2747;
    border-color: #0f2747;
    color: #fff;
  }
  #purchasePaginationControls .page-item.disabled .page-link { color: #9aa8b8; }
  #purchasePaginationControls .page-link:hover {
    background: #e8eef5;
    color: #0f2747;
  }
  #purchasePaginationControls .page-item.active .page-link:hover {
    background: #0f2747;
    color: #fff;
  }

  .modal-header.ss-card-head .btn-close { filter: invert(1); }
  
  @media print {
    .app-sidebar, .app-header, .app-content-header .breadcrumb, .btn, button, .no-print { display: none !important; }
    body, .app-main { margin: 0; padding: 0; }
    .card { box-shadow: none !important; border: none !important; }
  }
</style>

<main class="app-main erp-sale-page" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center gy-2">
        <div class="col-12 col-md-6">
          <h3 class="mb-0 fw-bold" style="color:#0f2747;">
            <i class="bi bi-receipt-cutoff me-2"></i>Purchase Summary
          </h3>
          <p class="text-muted small mb-0">Purchase · Invoice list &amp; totals</p>
        </div>
        <div class="col-12 col-md-6">
          <ol class="breadcrumb float-md-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Purchase</span></li>
            <li class="breadcrumb-item active" aria-current="page">Summary</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      
      <div class="card ss-card border-0 mb-3 no-print">
        <div class="card-header ss-card-head">
          <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-funnel me-2"></i>Filters</h6>
        </div>
        <div class="card-body p-3">
          <div class="row g-3 align-items-end">
            <div class="col-md-2">
              <label class="form-label ss-label">From Date</label>
              <input type="date" class="form-control form-control-sm" id="fromDate">
            </div>
            <div class="col-md-2">
              <label class="form-label ss-label">To Date</label>
              <input type="date" class="form-control form-control-sm" id="toDate" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-3">
              <label class="form-label ss-label">Supplier</label>
              <select class="form-select form-select-sm" id="filterSupplier">
                <option value="">All Suppliers</option>
                @isset($suppliers)
                  @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->custCode }}">{{ $supplier->custName }}{{ $supplier->custCity ? ' (' . $supplier->custCity . ')' : '' }}</option>
                  @endforeach
                @endisset
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label ss-label">Invoice Type</label>
              <select class="form-select form-select-sm" id="filterInvoiceType">
                <option value="">Purchase</option>
                <option value="return">Return Purchase</option>
              </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
              <button type="button" class="btn btn-sm text-white" id="btnFilter" style="background:#0f2747;border-color:#0f2747;">
                <i class="bi bi-search me-1"></i>Filter
              </button>
              <button type="button" class="btn btn-outline-secondary btn-sm" id="btnReset">
                <i class="bi bi-arrow-clockwise me-1"></i>Reset
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
          <div class="ss-kpi">
            <div class="ss-kpi-icon"><i class="bi bi-receipt"></i></div>
                <div>
              <div class="ss-kpi-lbl">Total Invoices</div>
              <div class="ss-kpi-val" id="totalInvoices">0</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3">
          <div class="ss-kpi amt">
            <div class="ss-kpi-icon amt"><i class="bi bi-cash-stack"></i></div>
                <div>
              <div class="ss-kpi-lbl">Total Amount</div>
              <div class="ss-kpi-val text-success" id="totalAmount">Rs. 0.00</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3">
          <div class="ss-kpi items">
            <div class="ss-kpi-icon items"><i class="bi bi-box-seam"></i></div>
                <div>
              <div class="ss-kpi-lbl">Total Items</div>
              <div class="ss-kpi-val" id="totalItems">0</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3">
          <div class="ss-kpi avg">
            <div class="ss-kpi-icon avg"><i class="bi bi-graph-up"></i></div>
                <div>
              <div class="ss-kpi-lbl">Avg. Invoice</div>
              <div class="ss-kpi-val" id="avgInvoice">Rs. 0.00</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card ss-card border-0" id="purchaseSummaryLive">
        <div class="card-header ss-card-head">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h6 class="mb-0 text-white fw-semibold" id="purchaseSummaryTableTitle">
              <i class="bi bi-table me-2"></i>Purchase Invoices
            </h6>
            <div class="d-flex flex-wrap align-items-center gap-2 no-print">
              <button type="button" class="btn btn-sm btn-outline-light" id="btnPrintReport">
              <i class="bi bi-printer me-1"></i>Print Report
            </button>
              <a href="{{ route('reports.purchases-summary') }}" class="btn btn-sm btn-outline-light">
              <i class="bi bi-clipboard-data me-1"></i>Report
            </a>
              <a href="{{ url('/PurchaseInvoice') }}" class="btn btn-sm btn-light fw-semibold">
                <i class="bi bi-plus-circle me-1"></i>New Invoice
            </a>
            </div>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 ss-table" id="invoiceTable">
              <thead>
                <tr>
                  <th class="text-center" style="width: 4%;">#</th>
                  <th style="width: 12%;">Invoice No</th>
                  <th style="width: 10%;">Date</th>
                  <th style="width: 20%;">Supplier</th>
                  <th style="width: 7%;" class="text-center">Items</th>
                  <th style="width: 10%;" class="text-end">Total</th>
                  <th style="width: 10%;" class="text-end">Paid</th>
                  <th style="width: 10%;" class="text-end">Due</th>
                  <th style="width: 8%;" class="text-center">Status</th>
                  <th style="width: 9%;" class="text-center text-nowrap no-print">Action</th>
                </tr>
              </thead>
              <tbody id="invoiceTableBody">
                <tr>
                  <td colspan="10" class="text-center py-4">
                    <div class="spinner-border" style="color:#0f2747;" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted mb-0">Loading invoices...</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2 no-print" id="purchasePaginationBar">
          <div class="d-flex flex-wrap align-items-center gap-2">
            <small class="text-muted" id="purchaseListMeta">Showing results</small>
            <label class="small text-muted mb-0" for="purchasePerPage">Rows</label>
            <select id="purchasePerPage" class="form-select form-select-sm" style="width: 5rem;" aria-label="Rows per page">
              <option value="10">10</option>
              <option value="25" selected>25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>
          <nav aria-label="Purchase invoice pagination">
            <ul class="pagination pagination-sm mb-0" id="purchasePaginationControls"></ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</main>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header ss-card-head">
        <h5 class="modal-title text-white"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this invoice?</p>
        <p class="fw-bold mb-0">Invoice No: <span id="deleteInvoiceNo"></span></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="btnConfirmDelete">
          <i class="bi bi-trash3 me-1"></i>Delete
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h5 class="modal-title"><i class="bi bi-cash me-1"></i>Record Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-2">Invoice: <strong id="payInvoiceNo"></strong></p>
        <p class="small text-muted mb-3">Due: <strong id="payDueLabel">0.00</strong></p>
        <div class="mb-2">
          <label class="form-label small fw-semibold mb-1">Amount</label>
          <input type="number" step="0.01" min="0.01" class="form-control form-control-sm" id="payAmount">
        </div>
        <div class="mb-2">
          <label class="form-label small fw-semibold mb-1">Payment Date</label>
          <input type="date" class="form-control form-control-sm" id="payDate" value="{{ date('Y-m-d') }}">
        </div>
        <div>
          <label class="form-label small fw-semibold mb-1">Notes</label>
          <input type="text" class="form-control form-control-sm" id="payNotes" maxlength="500" placeholder="Optional">
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success btn-sm" id="btnConfirmPay">
          <i class="bi bi-check2 me-1"></i>Save Payment
        </button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function () {
  let deleteInvoiceNo = null;
  let payInvoiceNo = null;
  let currentPage = 1;
  let suppressFilter = false;

  if ($.fn.select2) {
    $('#filterSupplier').select2({
      theme: 'bootstrap-5',
      placeholder: 'All Suppliers',
      allowClear: true,
      width: '100%'
    });
  }

  function fmt(n) {
    return parseFloat(n || 0).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function debounce(fn, delay) {
    delay = delay || 350;
    let t;
    return function () {
      const args = arguments;
      const ctx = this;
      clearTimeout(t);
      t = setTimeout(function () { fn.apply(ctx, args); }, delay);
    };
  }

  function buildFilters() {
    const filters = {};
    const fromDate = $('#fromDate').val();
    const toDate = $('#toDate').val();
    const supplierCode = $('#filterSupplier').val();
    const invoiceType = $('#filterInvoiceType').val();
    if (fromDate) filters.fromDate = fromDate;
    if (toDate) filters.toDate = toDate;
    if (supplierCode) filters.supplierCode = supplierCode;
    if (invoiceType) filters.invoice_type = invoiceType;
    return filters;
  }

  function isReturnFilter() {
    return $('#filterInvoiceType').val() === 'return';
  }

  function pageSize() {
    const n = parseInt($('#purchasePerPage').val(), 10);
    return [10, 25, 50, 100].indexOf(n) >= 0 ? n : 25;
  }

  function payBadge(status) {
    if (status === 'paid') return 'bg-success';
    if (status === 'partial') return 'bg-warning text-dark';
    return 'bg-danger';
  }

  function escapeHtml(str) {
    return String(str == null ? '' : str).replace(/[&<>"']/g, function (ch) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
    });
  }

  function updateStatistics(invoices, totalAmt, totalItems, meta) {
    totalAmt = totalAmt || 0;
    totalItems = totalItems || 0;
    const count = meta && meta.total != null ? meta.total : invoices.length;
    const isReturn = isReturnFilter();
    $('#totalInvoices').text(count);
    $('#totalAmount').text('Rs. ' + fmt(totalAmt));
    $('#totalItems').text(totalItems);
    const pageCount = invoices.length || 0;
    $('#avgInvoice').text('Rs. ' + fmt(pageCount ? totalAmt / pageCount : 0));
    $('.ss-kpi .ss-kpi-lbl').eq(0).text(isReturn ? 'Total Returns' : 'Total Invoices');
    $('.ss-kpi.avg .ss-kpi-lbl').text(isReturn ? 'Avg. Return' : 'Avg. Invoice');
    $('#purchaseSummaryTableTitle').html(
      isReturn
        ? '<i class="bi bi-table me-2"></i>Return Purchase Invoices'
        : '<i class="bi bi-table me-2"></i>Purchase Invoices'
    );
  }

  function paginationWindow(current, last, size) {
    size = size || 5;
    if (last <= size) {
      return Array.from({ length: last }, function (_, i) { return i + 1; });
    }
    var start = Math.max(1, current - Math.floor(size / 2));
    var end = start + size - 1;
    if (end > last) {
      end = last;
      start = Math.max(1, end - size + 1);
    }
    return Array.from({ length: end - start + 1 }, function (_, i) { return start + i; });
  }

  function renderPagination(meta) {
    const $controls = $('#purchasePaginationControls');
    $controls.empty();
    if (!meta) {
      $('#purchaseListMeta').text('');
      return;
    }
    const current = meta.current_page || 1;
    const last = meta.last_page || 1;
    const total = meta.total || 0;
    const from = total ? ((current - 1) * meta.per_page) + 1 : 0;
    const to = Math.min(current * meta.per_page, total);
    $('#purchaseListMeta').text(total ? ('Showing ' + from + '–' + to + ' of ' + total) : 'Showing 0 invoices');

    function pageBtn(page, label, disabled, active) {
      const cls = ['page-item'];
      if (disabled) cls.push('disabled');
      if (active) cls.push('active');
      const attrs = (disabled || active) ? '' : (' data-page="' + page + '"');
      return '<li class="' + cls.join(' ') + '"><a class="page-link" href="#"' + attrs + '>' + label + '</a></li>';
    }

    let html = '';
    html += pageBtn(1, '«', current <= 1, false);
    html += pageBtn(current - 1, '‹', current <= 1, false);
    paginationWindow(current, last, 5).forEach(function (p) {
      html += pageBtn(p, String(p), false, p === current);
    });
    html += pageBtn(current + 1, '›', current >= last, false);
    html += pageBtn(last, '»', current >= last, false);
    $controls.html(html);
  }

  function loadInvoices(filters, page) {
    filters = filters || {};
    page = page || 1;
    currentPage = page;
    $('#purchaseSummaryLive').addClass('is-loading');
    $('#invoiceTableBody').html(
      '<tr><td colspan="10" class="text-center py-4"><div class="spinner-border" style="color:#0f2747;"></div><p class="mt-2 text-muted mb-0">Loading invoices...</p></td></tr>'
    );

    const params = new URLSearchParams(Object.assign({}, filters, {
      page: String(page),
      per_page: String(pageSize())
    }));

    $.get('{{ url('/purchase/invoices') }}?' + params.toString())
      .done(function (response) {
        const invoices = response.data || [];
        const meta = response.meta || null;

        if (!invoices.length) {
          $('#invoiceTableBody').html('<tr><td colspan="10" class="text-center py-4 text-muted">No invoices found</td></tr>');
          renderPagination(meta);
          updateStatistics([], 0, 0, meta);
          return;
        }

        let rows = '';
        let totalAmount = 0;
        let totalItems = 0;
        const offset = meta ? (meta.current_page - 1) * meta.per_page : 0;

        invoices.forEach(function (inv, i) {
          const isReturn = inv.purchase_type === 'return' || inv.is_return === true || inv.payment_status === 'return';
          const total = parseFloat(inv.grandTotal || 0);
          const paid = isReturn ? 0 : parseFloat(inv.paid_amount || 0);
          const due = isReturn ? 0 : Math.max(0, Math.round((total - paid) * 100) / 100);
          totalAmount += total;
          totalItems += parseInt(inv.noOfItems || 0, 10) || 0;

          let formattedDate = inv.invoiceDate || '';
          if (inv.invoiceDate) {
            const d = new Date(inv.invoiceDate);
            if (!isNaN(d.getTime())) {
              formattedDate = String(d.getDate()).padStart(2, '0') + '-' +
                d.toLocaleString('en-US', { month: 'short' }) + '-' + d.getFullYear();
            }
          }

          const canPay = !isReturn && due > 0.009;
          const status = isReturn ? 'return' : (inv.payment_status || 'unpaid');
          const statusBadge = isReturn ? 'bg-secondary' : payBadge(status);
          const statusLabel = isReturn ? 'Return' : status;

          let actionBtns = ''
            + '<button type="button" class="btn btn-sm btn-outline-primary py-0 px-1 btnView" data-invoice="' + escapeHtml(inv.invoiceNo) + '" data-type="' + (isReturn ? 'return' : 'purchase') + '" title="View"><i class="bi bi-eye"></i></button> '
            + '<button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 btnEdit" data-invoice="' + escapeHtml(inv.invoiceNo) + '" data-type="' + (isReturn ? 'return' : 'purchase') + '" title="Edit"><i class="bi bi-pencil"></i></button> '
            + '<button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 btnDelete" data-invoice="' + escapeHtml(inv.invoiceNo) + '" data-type="' + (isReturn ? 'return' : 'purchase') + '" title="Delete"><i class="bi bi-trash3"></i></button>';
          if (canPay) {
            actionBtns = '<button type="button" class="btn btn-sm btn-outline-success btnPay py-0 px-1" data-invoice="' + escapeHtml(inv.invoiceNo) + '" data-due="' + due + '" title="Pay"><i class="bi bi-cash"></i></button> ' + actionBtns;
          }

          rows += '<tr>'
            + '<td>' + (offset + i + 1) + '</td>'
            + '<td><strong>' + escapeHtml(inv.invoiceNo) + '</strong></td>'
            + '<td>' + escapeHtml(formattedDate) + '</td>'
            + '<td>' + escapeHtml(inv.supplierName || '') + '</td>'
            + '<td class="text-center">' + (inv.noOfItems || 0) + '</td>'
            + '<td class="text-end fw-semibold">Rs. ' + fmt(total) + '</td>'
            + '<td class="text-end text-success">Rs. ' + fmt(paid) + '</td>'
            + '<td class="text-end ' + (due > 0 ? 'text-danger fw-semibold' : '') + '">Rs. ' + fmt(due) + '</td>'
            + '<td class="text-center"><span class="badge ' + statusBadge + '">' + escapeHtml(statusLabel) + '</span></td>'
            + '<td class="text-center text-nowrap purchase-actions no-print">' + actionBtns + '</td></tr>';
        });

        $('#invoiceTableBody').html(rows);
        renderPagination(meta);
        updateStatistics(invoices, totalAmount, totalItems, meta);
      })
      .fail(function () {
        $('#invoiceTableBody').html('<tr><td colspan="10" class="text-center text-danger py-4">Failed to load invoices</td></tr>');
        renderPagination(null);
        updateStatistics([], 0, 0, null);
        if (typeof showToastError === 'function') {
          showToastError('Error', 'Failed to load purchase invoices');
        }
      })
      .always(function () {
        $('#purchaseSummaryLive').removeClass('is-loading');
      });
  }

  function refreshFromFilters() {
    if (suppressFilter) return;
    loadInvoices(buildFilters(), 1);
  }

  $(document).on('click', '#purchasePaginationControls a[data-page]', function (e) {
    e.preventDefault();
    const page = parseInt($(this).data('page'), 10) || 1;
    if (page === currentPage) return;
    loadInvoices(buildFilters(), page);
  });

  $('#purchasePerPage').on('change', function () {
    loadInvoices(buildFilters(), 1);
  });

  $('#btnFilter').on('click', refreshFromFilters);
  $('#fromDate, #toDate').on('change', refreshFromFilters);
  $('#filterSupplier, #filterInvoiceType').on('change', debounce(refreshFromFilters, 350));

  $('#btnReset').on('click', function () {
    suppressFilter = true;
    $('#fromDate').val('');
    $('#toDate').val('{{ date('Y-m-d') }}');
    $('#filterSupplier').val('').trigger('change');
    $('#filterInvoiceType').val('');
    suppressFilter = false;
    loadInvoices(buildFilters(), 1);
  });

  $(document).on('click', '.btnView', function () {
    const invoiceNo = $(this).data('invoice');
    const type = String($(this).data('type') || '').toLowerCase();
    if (type === 'return') {
      window.open('{{ url('/purchase/return') }}/' + encodeURIComponent(invoiceNo) + '/preview', '_blank');
      return;
    }
    window.open('{{ url('/purchase/invoice') }}/' + encodeURIComponent(invoiceNo) + '/preview', '_blank');
  });

  $(document).on('click', '.btnEdit', function () {
    const invoiceNo = $(this).data('invoice');
    const type = String($(this).data('type') || '').toLowerCase();
    if (type === 'return') {
      window.location.href = '{{ url('/purchase/return/edit') }}/' + encodeURIComponent(invoiceNo);
      return;
    }
    window.location.href = '{{ url('/purchase/invoice/edit') }}/' + encodeURIComponent(invoiceNo);
  });

  let deleteInvoiceType = 'purchase';
  $(document).on('click', '.btnDelete', function () {
    deleteInvoiceNo = $(this).data('invoice');
    deleteInvoiceType = String($(this).data('type') || 'purchase').toLowerCase();
    $('#deleteInvoiceNo').text(deleteInvoiceNo);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteModal')).show();
  });

  $('#btnConfirmDelete').on('click', function () {
    if (!deleteInvoiceNo) return;
    const deleteUrl = deleteInvoiceType === 'return'
      ? '{{ url('/purchase/return/delete') }}/' + encodeURIComponent(deleteInvoiceNo)
      : '{{ url('/purchase/invoice/delete') }}/' + encodeURIComponent(deleteInvoiceNo);
    $.ajax({
      url: deleteUrl,
      method: 'DELETE',
      data: { _token: '{{ csrf_token() }}' }
    }).done(function (res) {
      if (res.success) {
        if (typeof showToastSuccess === 'function') {
          showToastSuccess('Deleted', deleteInvoiceType === 'return' ? 'Return deleted successfully' : 'Invoice deleted successfully');
        }
        bootstrap.Modal.getInstance(document.getElementById('deleteModal'))?.hide();
        loadInvoices(buildFilters(), currentPage);
      } else if (typeof showToastError === 'function') {
        showToastError('Failed', res.message || 'Could not delete');
      }
    }).fail(function (xhr) {
      if (typeof showToastError === 'function') {
        showToastError('Failed', (xhr.responseJSON && xhr.responseJSON.message) || 'Could not delete');
      }
    });
  });

  $(document).on('click', '.btnPay', function () {
    payInvoiceNo = $(this).data('invoice');
    const due = parseFloat($(this).data('due') || 0);
    $('#payInvoiceNo').text(payInvoiceNo);
    $('#payDueLabel').text('Rs. ' + fmt(due));
    $('#payAmount').val(due > 0 ? due.toFixed(2) : '');
    $('#payDate').val('{{ date('Y-m-d') }}');
    $('#payNotes').val('');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('payModal')).show();
  });

  $('#btnConfirmPay').on('click', function () {
    if (!payInvoiceNo) return;
    const amount = parseFloat($('#payAmount').val() || 0);
    if (!(amount > 0)) {
      if (typeof showToastError === 'function') showToastError('Required', 'Enter a valid payment amount');
      return;
    }
    $.ajax({
      url: '{{ url('/purchase/invoice') }}/' + encodeURIComponent(payInvoiceNo) + '/payment',
      method: 'POST',
      data: {
        _token: '{{ csrf_token() }}',
        amount: amount,
        payment_date: $('#payDate').val(),
        notes: $('#payNotes').val()
      }
    }).done(function (res) {
      if (res.success) {
        if (typeof showToastSuccess === 'function') showToastSuccess('Saved', res.message || 'Payment recorded');
        bootstrap.Modal.getInstance(document.getElementById('payModal'))?.hide();
        loadInvoices(buildFilters(), currentPage);
      } else if (typeof showToastError === 'function') {
        showToastError('Failed', res.message || 'Could not save payment');
      }
    }).fail(function (xhr) {
      if (typeof showToastError === 'function') {
        showToastError('Failed', (xhr.responseJSON && xhr.responseJSON.message) || 'Could not save payment');
      }
    });
  });

  $('#btnPrintReport').on('click', function () {
    const params = new URLSearchParams(buildFilters()).toString();
    window.open('{{ url('/purchase/invoices/print') }}' + (params ? ('?' + params) : ''), '_blank');
  });

  loadInvoices(buildFilters(), 1);
});
</script>

@endsection
