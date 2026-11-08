@extends('layout.app')

@section('title', 'Sale Summary')

@section('content')
@include('partials.sale.erp-styles')

<style>
  .badge-active { background-color: #198754; }
  .badge-cancelled { background-color: #dc3545; }
  .badge-cash-paid { background: #0f2747; }
  .badge-credit { background-color: #1a4a8a; }

  .ss-kpi {
    display: flex; align-items: center; gap: 0.75rem;
    background: #fff; border: 1px solid #d5dde8; border-left: 4px solid #0f2747;
    border-radius: 4px; padding: 0.85rem 1rem; height: 100%;
    box-shadow: 0 2px 8px rgba(15, 39, 71, 0.05);
  }
  .ss-kpi.amt { border-left-color: #198754; background: #eefaf2; }
  .ss-kpi.credit { border-left-color: #1a4a8a; background: #f4f7fb; }
  .ss-kpi.cash { border-left-color: #0f2747; background: #e8eef5; }
  .ss-kpi-icon {
    width: 42px; height: 42px; border-radius: 6px; background: #e8eef5; color: #0f2747;
    display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;
  }
  .ss-kpi-icon.amt { background: #e8f5e9; color: #198754; }
  .ss-kpi-icon.credit { background: #e8eef5; color: #1a4a8a; }
  .ss-kpi-icon.cash { background: #dde5f0; color: #0f2747; }
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

  #invoiceTable th.invoice-actions,
  #invoiceTable td.invoice-actions {
    white-space: nowrap;
    width: 1%;
    vertical-align: middle;
  }
  #invoiceTable .invoice-actions .btn-group {
    display: inline-flex;
    flex-wrap: nowrap;
    gap: 0.25rem;
  }
  #invoiceTable .invoice-actions .btn { flex: 0 0 auto; }

  #invoicePaginationControls .page-link {
    color: #0f2747;
    border-color: #d5dde8;
    min-width: 2rem;
    text-align: center;
  }
  #invoicePaginationControls .page-item.active .page-link {
    background: #0f2747;
    border-color: #0f2747;
    color: #fff;
  }
  #invoicePaginationControls .page-item.disabled .page-link {
    color: #9aa8b8;
  }
  #invoicePaginationControls .page-link:hover {
    background: #e8eef5;
    color: #0f2747;
  }
  #invoicePaginationControls .page-item.active .page-link:hover {
    background: #0f2747;
    color: #fff;
  }

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
            <i class="bi bi-receipt-cutoff me-2"></i>Sale Summary
          </h3>
          <p class="text-muted small mb-0">Sale · Invoice list &amp; totals</p>
        </div>
        <div class="col-12 col-md-6">
          <ol class="breadcrumb float-md-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Sale</span></li>
            <li class="breadcrumb-item active" aria-current="page">Summary</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">

      <div class="card ss-card border-0 mb-3">
        <div class="card-header ss-card-head">
          <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-funnel me-2"></i>Filters</h6>
        </div>
        <div class="card-body p-3">
          <div class="row g-3">
            <div class="col-md-2">
              <label class="form-label ss-label">From Date</label>
              <input type="date" class="form-control form-control-sm" id="fromDate" value="{{ now()->startOfMonth()->toDateString() }}">
            </div>
            <div class="col-md-2">
              <label class="form-label ss-label">To Date</label>
              <input type="date" class="form-control form-control-sm" id="toDate" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-3">
              <label class="form-label ss-label">Area</label>
              <select class="form-select form-select-sm" id="filterArea">
                <option value="">All Areas</option>
                @isset($areas)
                  @foreach($areas as $area)
                    <option value="{{ $area['code'] }}">{{ $area['code'] }}{{ $area['city'] ? '-' . $area['city'] : '' }}</option>
                  @endforeach
                @endisset
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label ss-label">Customer</label>
              <select class="form-select form-select-sm" id="filterCustomer">
                <option value="">All Customers</option>
                @isset($customers)
                  @foreach($customers as $cust)
                    <option value="{{ $cust->custCode }}">{{ $cust->custName }} - {{ $cust->custArea }}{{ $cust->custCity ? ' (' . $cust->custCity . ')' : '' }}</option>
                  @endforeach
                @endisset
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label ss-label">Invoice Type</label>
              <select class="form-select form-select-sm" id="filterSaleType">
                <option value="">All</option>
                <option value="credit">Credit Sale</option>
                <option value="cash">Cash Sale</option>
                <option value="return">Return Sale</option>
              </select>
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
          <div class="ss-kpi credit">
            <div class="ss-kpi-icon credit"><i class="bi bi-journal-text"></i></div>
            <div>
              <div class="ss-kpi-lbl">Credit Sale</div>
              <div class="ss-kpi-val" id="creditSaleAmount">Rs. 0.00</div>
            </div>
          </div>
        </div>
        <div class="col-6 col-xl-3">
          <div class="ss-kpi cash">
            <div class="ss-kpi-icon cash"><i class="bi bi-wallet2"></i></div>
            <div>
              <div class="ss-kpi-lbl">Cash Sale</div>
              <div class="ss-kpi-val" id="cashSaleAmount">Rs. 0.00</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card ss-card border-0">
        <div class="card-header ss-card-head">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h6 class="mb-0 text-white fw-semibold" id="saleSummaryTableTitle">
              <i class="bi bi-table me-2"></i>Sale Invoices
            </h6>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <button type="button" class="btn btn-sm btn-outline-light" id="btnPrintReport">
                <i class="bi bi-printer me-1"></i>Print Report
              </button>
              <a href="{{ url('/SaleInvoice') }}" class="btn btn-sm btn-light fw-semibold">
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
                  <th class="text-center" style="width: 3%">#</th>
                  <th style="width: 10%">Invoice No</th>
                  <th style="width: 9%">Order No</th>
                  <th style="width: 8%">Date</th>
                  <th style="width: 14%">Customer</th>
                  <th style="width: 16%">Address</th>
                  <th style="width: 5%" class="text-center">Items</th>
                  <th style="width: 5%" class="text-center">Corten</th>
                  <th style="width: 9%" class="text-end">Amount</th>
                  <th style="width: 12%">Bilty No.</th>
                  <th class="text-center invoice-actions">Action</th>
                </tr>
              </thead>
              <tbody id="invoiceTableBody">
                <tr>
                  <td colspan="11" class="text-center py-4">
                    <div class="spinner-border" style="color:#0f2747;" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted mb-0">Loading invoices...</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center gap-2 no-print" id="invoicePaginationBar" style="border-color:#e8eef5 !important;">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <small class="text-muted" id="invoiceListMeta">Showing results</small>
              <label class="small text-muted mb-0" for="salePerPage">Rows</label>
              <select id="salePerPage" class="form-select form-select-sm" style="width: 5rem;" aria-label="Rows per page">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
            </div>
            <nav aria-label="Invoice pagination">
              <ul class="pagination pagination-sm mb-0" id="invoicePaginationControls"></ul>
            </nav>
          </div>
        </div>
      </div>

    </div>
  </div>
</main>

<script>
$(document).ready(function() {

  // Customers dataset for dynamic filtering by area
  const allCustomers = @json($customers ?? []);

  // Enhance filter selects with Select2 (Bootstrap 5 theme assumed loaded globally)
  if ($.fn.select2) {
    $('#filterCustomer').select2({
      theme: 'bootstrap-5',
      placeholder: 'All Customers',
      allowClear: true,
      width: '100%',
      dropdownParent: $(document.body)
    });
    $('#filterArea').select2({
      theme: 'bootstrap-5',
      placeholder: 'All Areas',
      allowClear: true,
      width: '100%',
      dropdownParent: $(document.body)
    });
  }

  // Number formatter
  function fmt(n) {
    return parseFloat(n || 0).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  }

  // Debounce utility
  function debounce(fn, delay = 350) {
    let t; return function(...args){ clearTimeout(t); t = setTimeout(() => fn.apply(this,args), delay); };
  }

  // Build filters object from current UI state
  function buildFilters() {
    const filters = {};
    const fromDate = $('#fromDate').val();
    const toDate = $('#toDate').val();
    const area = $('#filterArea').val();
    const customerCode = $('#filterCustomer').val();
    if (fromDate) filters.fromDate = fromDate;
    if (toDate) filters.toDate = toDate;
    if (area) filters.area = area;
    if (customerCode) filters.customerCode = customerCode;
    const saleType = $('#filterSaleType').val();
    if (saleType) filters.sale_type = saleType;
    return filters;
  }

  let currentPage = 1;

  function pageSize() {
    const n = parseInt($('#salePerPage').val(), 10);
    return [10, 25, 50, 100].includes(n) ? n : 25;
  }

  function paginationWindow(current, last, size = 5) {
    if (last <= size) {
      return Array.from({ length: last }, (_, i) => i + 1);
    }
    let start = Math.max(1, current - Math.floor(size / 2));
    let end = start + size - 1;
    if (end > last) {
      end = last;
      start = Math.max(1, end - size + 1);
    }
    return Array.from({ length: end - start + 1 }, (_, i) => start + i);
  }

  function renderPagination(meta) {
    const $controls = $('#invoicePaginationControls');
    $controls.empty();
    if (!meta) {
      $('#invoiceListMeta').text('');
      return;
    }
    const current = meta.current_page || 1;
    const last = meta.last_page || 1;
    const total = meta.total || 0;
    const from = total ? ((current - 1) * meta.per_page) + 1 : 0;
    const to = Math.min(current * meta.per_page, total);
    $('#invoiceListMeta').text(total ? `Showing ${from}–${to} of ${total}` : 'Showing 0 invoices');

    const pageBtn = (page, label, disabled, active) => {
      const cls = ['page-item'];
      if (disabled) cls.push('disabled');
      if (active) cls.push('active');
      const attrs = disabled || active ? '' : ` data-page="${page}"`;
      return `<li class="${cls.join(' ')}"><a class="page-link" href="#"${attrs}>${label}</a></li>`;
    };

    let html = '';
    html += pageBtn(1, '«', current <= 1, false);
    html += pageBtn(current - 1, '‹', current <= 1, false);
    paginationWindow(current, last, 5).forEach((p) => {
      html += pageBtn(p, String(p), false, p === current);
    });
    html += pageBtn(current + 1, '›', current >= last, false);
    html += pageBtn(last, '»', current >= last, false);
    $controls.html(html);
  }

  $(document).on('click', '#invoicePaginationControls a[data-page]', function (e) {
    e.preventDefault();
    const page = parseInt($(this).data('page'), 10) || 1;
    if (page === currentPage) return;
    loadInvoices(buildFilters(), page);
  });

  $('#salePerPage').on('change', function () {
    currentPage = 1;
    loadInvoices(buildFilters(), 1);
  });

  // Load invoices (paginated)
  function loadInvoices(filters = {}, page = 1) {
    currentPage = page;
    $('#invoiceTableBody').html(`
      <tr>
        <td colspan="12" class="text-center py-4">
          <div class="spinner-border text-primary" role="status" aria-label="Loading"></div>
          <p class="mt-2 text-muted">Loading invoices...</p>
        </td>
      </tr>
    `);
    const params = new URLSearchParams({ ...filters, page: String(page), per_page: String(pageSize()) });
    $.get('{{ url("/sale/invoices") }}?' + params.toString())
      .done(function(response) {
        const invoices = response.data || [];
        const meta = response.meta || null;
        if (invoices.length === 0) {
          $('#invoiceTableBody').html(`
            <tr>
              <td colspan="11" class="text-center py-4">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="mt-2 text-muted mb-0">No invoices found</p>
                <small class="text-secondary">Adjust filters and try again.</small>
              </td>
            </tr>
          `);
          renderPagination(meta);
          updateStatistics([], meta);
          return;
        }
        let rows = '';
        const rowOffset = meta ? ((meta.current_page - 1) * meta.per_page) : 0;
        invoices.forEach((inv, i) => {
          // Format date: convert YYYY-MM-DD to DD-MMM-YYYY
          let formattedDate = inv.invoiceDate;
          if (inv.invoiceDate) {
            const dateObj = new Date(inv.invoiceDate);
            const day = String(dateObj.getDate()).padStart(2, '0');
            const month = dateObj.toLocaleString('en-US', { month: 'short' });
            const year = dateObj.getFullYear();
            formattedDate = `${day}-${month}-${year}`;
          }
          const addrPart = (inv.customerAddress || '').toString().trim();
          const cityPart = (inv.customerCity || inv.city || '').toString().trim();
          const address = [addrPart, cityPart].filter(Boolean).join(', ') || '—';
          const biltyVal = (inv.biltyNo || '').toString().replace(/"/g, '&quot;');
          const isReturn = inv.sale_type === 'return' || inv.is_return === true;
          const biltyCell = isReturn
            ? `<span class="text-muted">—</span>`
            : `<div class="input-group input-group-sm">
                  <input type="text" class="form-control form-control-sm bilty-no-input" maxlength="100"
                         value="${biltyVal}" placeholder="Bilty No." data-invoice="${inv.invoiceNo}" data-saved="${biltyVal}">
                  <button type="button" class="btn btn-outline-primary btn-save-bilty" data-invoice="${inv.invoiceNo}" title="Save Bilty No.">
                    <i class="bi bi-check-lg"></i>
                  </button>
                </div>`;
          const actionBtns = isReturn
            ? `<button class="btn btn-primary btnView" data-invoice="${inv.invoiceNo}" data-sale-type="return" title="View Return"><i class="bi bi-eye"></i></button>
                  <button class="btn btn-warning btnEdit" data-invoice="${inv.invoiceNo}" data-sale-type="return" title="Edit"><i class="bi bi-pencil"></i></button>
                  <button class="btn btn-danger btnDelete" data-invoice="${inv.invoiceNo}" data-sale-type="return" title="Delete"><i class="bi bi-trash3"></i></button>`
            : `<button class="btn btn-primary btnView" data-invoice="${inv.invoiceNo}" title="View Invoice"><i class="bi bi-eye"></i></button>
                  <button class="btn btn-warning btnEdit" data-invoice="${inv.invoiceNo}" data-sale-type="${inv.sale_type || ''}" title="Edit"><i class="bi bi-pencil"></i></button>
                  <button class="btn btn-danger btnDelete" data-invoice="${inv.invoiceNo}" title="Delete"><i class="bi bi-trash3"></i></button>`;

          rows += `
            <tr data-invoice="${inv.invoiceNo}">
              <td>${rowOffset + i + 1}</td>
              <td><strong>${inv.invoiceNo}</strong></td>
              <td>${inv.orderNo ? inv.orderNo : '-'}</td>
              <td>${formattedDate}</td>
              <td>${inv.customerName}</td>
              <td class="small" title="${address.replace(/"/g, '&quot;')}">${address}</td>
              <td class="text-center">${inv.noOfItems}</td>
              <td class="text-center">${inv.corten || 0}</td>
              <td class="text-end fw-bold">₨${fmt(inv.grandTotal)}</td>
              <td>${biltyCell}</td>
              <td class="text-center invoice-actions">
                <div class="btn-group btn-group-sm" role="group" aria-label="Invoice actions">
                  ${actionBtns}
                </div>
              </td>
            </tr>`;
        });
        $('#invoiceTableBody').html(rows);
        renderPagination(meta);
        updateStatistics(invoices, meta);
      })
      .fail(function(xhr) {
        $('#invoiceTableBody').html(`
          <tr>
            <td colspan="11" class="text-center py-4 text-danger">
              <i class="bi bi-exclamation-circle fs-1"></i>
              <p class="mt-2 mb-0">Failed to load invoices</p>
              <small class="text-secondary">Error: ${xhr.status} - Please check console for details.</small>
            </td>
          </tr>
        `);
        renderPagination(null);
        updateStatistics([], null);
      });
  }

  function updateStatistics(invoices, meta = null) {
    const count = meta && meta.total != null ? meta.total : invoices.length;
    const totalAmt = meta && meta.total_amount != null
      ? meta.total_amount
      : invoices.reduce((sum, inv) => sum + parseFloat(inv.grandTotal || 0), 0);
    const creditAmt = meta && meta.credit_amount != null ? meta.credit_amount : 0;
    const cashAmt = meta && meta.cash_amount != null ? meta.cash_amount : 0;
    const isReturn = $('#filterSaleType').val() === 'return';
    $('#totalInvoices').text(count);
    $('#totalAmount').text('Rs. ' + fmt(totalAmt));
    $('#creditSaleAmount').text('Rs. ' + fmt(creditAmt));
    $('#cashSaleAmount').text('Rs. ' + fmt(cashAmt));
    $('.ss-kpi.credit .ss-kpi-lbl').text(isReturn ? 'Return Amount' : 'Credit Sale');
    $('.ss-kpi.cash .ss-kpi-lbl').text(isReturn ? '—' : 'Cash Sale');
    $('#saleSummaryTableTitle').html(
      isReturn
        ? '<i class="bi bi-table me-2"></i>Return Sale Invoices'
        : '<i class="bi bi-table me-2"></i>Sale Invoices'
    );
  }

  function saveBiltyNo(invoiceNo, $input, $btn) {
    const biltyNo = String($input.val() || '').trim();
    $btn.prop('disabled', true);
    $.ajax({
      url: `{{ url('/sale/invoice') }}/${encodeURIComponent(invoiceNo)}/bilty-no`,
      method: 'PUT',
      data: {
        _token: '{{ csrf_token() }}',
        biltyNo: biltyNo
      },
      success: function (res) {
        if (res.success) {
          $input.data('saved', res.biltyNo || '');
          $input.val(res.biltyNo || '');
          if (typeof showSuccess === 'function') {
            showSuccess('Saved', 'Bilty No. updated for ' + invoiceNo);
          }
        } else if (typeof showError === 'function') {
          showError('Failed', res.message || 'Could not save Bilty No.');
        }
      },
      error: function (xhr) {
        if (typeof showError === 'function') {
          showError('Failed', xhr.responseJSON?.message || 'Could not save Bilty No.');
        }
      },
      complete: function () {
        $btn.prop('disabled', false);
      }
    });
  }

  $(document).on('click', '.btn-save-bilty', function () {
    const invoiceNo = $(this).data('invoice');
    const $input = $(this).closest('td').find('.bilty-no-input');
    saveBiltyNo(invoiceNo, $input, $(this));
  });

  $(document).on('keydown', '.bilty-no-input', function (e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const $input = $(this);
    const $btn = $input.closest('td').find('.btn-save-bilty');
    saveBiltyNo($input.data('invoice'), $input, $btn);
  });

  // View invoice / return preview
  $(document).on('click', '.btnView', function() {
    const invoiceNo = $(this).data('invoice');
    const saleType = String($(this).data('sale-type') || '').toLowerCase();
    if (saleType === 'return') {
      window.open(`{{ url('/sale/return') }}/${encodeURIComponent(invoiceNo)}/preview`, '_blank');
      return;
    }
    window.open(`{{ url('/sale/invoice') }}/${invoiceNo}/preview`, '_blank');
  });

  // Edit invoice / return
  $(document).on('click', '.btnEdit', function() {
    const invoiceNo = $(this).data('invoice');
    const saleType = String($(this).data('sale-type') || '').toLowerCase();
    if (saleType === 'return') {
      window.location.href = `{{ url('/sale/return/edit') }}/${encodeURIComponent(invoiceNo)}`;
    } else if (saleType === 'cash') {
      window.location.href = `{{ url('/cash-sale/edit') }}/${invoiceNo}`;
    } else {
      window.location.href = `{{ url('/sale/invoice/edit') }}/${invoiceNo}`;
    }
  });

  // Delete invoice / return — same SweetAlert confirmation as Customer Account Transaction
  $(document).on('click', '.btnDelete', function() {
    const invoiceNo = $(this).data('invoice');
    const saleType = String($(this).data('sale-type') || '').toLowerCase();
    if (!invoiceNo) return;
    const isReturn = saleType === 'return';

    const run = function () {
      $.ajax({
        url: isReturn
          ? `{{ url('/sale/return/delete') }}/${encodeURIComponent(invoiceNo)}`
          : `{{ url('/sale/invoice/delete') }}/${invoiceNo}`,
        method: 'DELETE',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
          if (res.success) {
            if (typeof Swal !== 'undefined') {
              Swal.fire({ icon: 'success', title: 'Deleted', timer: 1400, showConfirmButton: false });
            }
            loadInvoices(buildFilters(), currentPage);
          } else if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Delete Failed', text: res.message || 'Failed to delete' });
          }
        },
        error: function(xhr) {
          if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Delete Failed', text: xhr.responseJSON?.message || 'Failed to delete' });
          }
        }
      });
    };

    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'warning',
        title: 'Confirm Delete?',
        text: isReturn
          ? ('This will remove sale return ' + invoiceNo + '.')
          : ('This will remove sale invoice ' + invoiceNo + '.'),
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete'
      }).then(function (r) { if (r.isConfirmed) run(); });
    } else if (confirm('Delete this invoice?')) {
      run();
    }
  });

  // Filter button
  $('#btnFilter').on('click', function() {
    loadInvoices(buildFilters(), 1);
  });

  // Reset button
  $('#btnReset').on('click', function() {
    $('#fromDate').val('{{ now()->startOfMonth()->toDateString() }}');
    $('#toDate').val('{{ date("Y-m-d") }}');
    $('#filterCustomer').val('').trigger('change');
    $('#filterArea').val('').trigger('change');
    $('#filterSaleType').val('');
    loadInvoices(buildFilters(), 1);
  });

  // Area change: filter customers list
  $('#filterArea').on('change', function() {
    const area = $(this).val();
    let options = '<option value="">All Customers</option>';
    const filtered = area ? allCustomers.filter(c => c.custArea === area) : allCustomers;
    filtered.forEach(c => {
      options += `<option value="${c.custCode}">${c.custName} - ${c.custArea}${c.custCity ? ' ('+c.custCity+')' : ''}</option>`;
    });
    $('#filterCustomer').html(options).val('').trigger('change');
  });

  // Customer change: auto filter invoices (respect current date & area selections)
  $('#filterCustomer').on('change', debounce(function() {
    loadInvoices(buildFilters(), 1);
  }));

  $('#filterSaleType').on('change', function() {
    loadInvoices(buildFilters(), 1);
  });

  // Print Report button
  $('#btnPrintReport').on('click', function() {
    const filters = buildFilters();
    const params = new URLSearchParams(filters).toString();
    const printUrl = '{{ url("/sale/invoices/print") }}' + (params ? ('?' + params) : '');
    window.open(printUrl, '_blank');
  });

  $('#fromDate, #toDate').on('change', debounce(function () {
    loadInvoices(buildFilters(), 1);
  }));

  // Initial load: current month only
  loadInvoices(buildFilters(), 1);
});
</script>

@endsection
