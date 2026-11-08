@extends('layout.app')

@section('title', 'Party Cheques')

@section('content')
<style>
  :root {
    --chq-navy: #0f2747;
    --chq-blue: #1a4a8a;
  }
  .chq-page-bar {
    border-bottom: 2px solid var(--chq-navy);
    margin-bottom: 1rem;
    padding-bottom: 0.35rem;
  }
  .chq-head {
    background: linear-gradient(135deg, var(--chq-navy) 0%, var(--chq-blue) 100%);
    color: #fff;
  }
  .chq-toolbar {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
  }
  .chq-toolbar .form-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #64748b;
    font-weight: 700;
    margin-bottom: 0.25rem;
  }
  .chq-scope-wrap .btn-scope {
    border-radius: 999px;
    font-size: 0.8rem;
    padding: 0.35rem 0.85rem;
  }
  .btn-scope.active {
    background: #fff !important;
    color: var(--chq-navy) !important;
    border-color: #fff !important;
    font-weight: 700;
    box-shadow: 0 1px 4px rgba(0,0,0,0.12);
  }
  .chq-stat-box {
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #fff;
    padding: 0.9rem 1rem;
    height: 100%;
    box-shadow: 0 1px 3px rgba(15, 39, 71, 0.06);
    border-top: 3px solid var(--chq-accent, #0d6efd);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
  }
  .chq-stat-box:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(15, 39, 71, 0.08);
  }
  .chq-stat-box .label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748b;
    font-weight: 700;
    margin: 0 0 0.35rem;
  }
  .chq-stat-box .value {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--chq-navy);
    margin: 0;
    line-height: 1.2;
  }
  .chq-stat-box .sub {
    font-size: 0.8rem;
    color: #64748b;
    margin: 0.3rem 0 0;
  }
  .chq-stat-box.stat-total { --chq-accent: #0d6efd; }
  .chq-stat-box.stat-amount { --chq-accent: #198754; }
  .chq-stat-box.stat-pending { --chq-accent: #ffc107; }
  .chq-stat-box.stat-cleared { --chq-accent: #20c997; }
  #chequeTable {
    font-size: 0.84rem;
  }
  #chequeTable thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #eef2f7;
    color: var(--chq-navy);
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    border-bottom: 2px solid #cbd5e1;
    white-space: nowrap;
    vertical-align: middle;
  }
  #chequeTable td {
    vertical-align: middle;
    white-space: nowrap;
    padding-top: 0.45rem;
    padding-bottom: 0.45rem;
  }
  #chequeTable .chq-clip {
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  #chequeTable tr.chq-carry {
    background-color: rgba(255, 193, 7, 0.1);
  }
  #chequeTable tr.chq-cleared {
    opacity: 0.82;
  }
  #chequeTable tr.chq-selected {
    background-color: rgba(26, 74, 138, 0.08);
  }
  .chq-table-wrap {
    max-height: min(62vh, 640px);
    overflow: auto;
  }
  .chq-selected-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: #e8f0fe;
    color: var(--chq-blue);
    border-radius: 999px;
    padding: 0.2rem 0.7rem;
    font-size: 0.78rem;
    font-weight: 600;
  }
  .chq-assign-box {
    background: #fff;
    border: 1px solid #dbe3ef;
    border-radius: 0.65rem;
    padding: 0.65rem 0.75rem;
  }
  .chq-card {
    border: 0;
    border-radius: 0.85rem;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(15, 39, 71, 0.07);
  }
</style>

<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center chq-page-bar">
        <div class="col-sm-6">
          <h3 class="mb-0">
            <i class="bi bi-bank2 text-primary me-2"></i>Party Cheque Detail
          </h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('accountTransaction.index') }}">Accounts</a></li>
            <li class="breadcrumb-item active" aria-current="page">Party Cheques</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <div class="row g-3 mb-3 align-items-stretch">
        <div class="col-6 col-md-3">
          <div class="chq-stat-box stat-total">
            <p class="label"><i class="bi bi-collection me-1"></i>Total Cheques</p>
            <p class="value text-primary" id="statTotalChq">0</p>
            <p class="sub" id="statTotalHint">In current list</p>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="chq-stat-box stat-amount">
            <p class="label"><i class="bi bi-cash-stack me-1"></i>Cheque Amount</p>
            <p class="value text-success" id="statTotalAmount">0.00</p>
            <p class="sub">List total</p>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="chq-stat-box stat-pending">
            <p class="label"><i class="bi bi-hourglass-split me-1"></i>Pending Total</p>
            <p class="value text-warning" id="statPendingCount">0</p>
            <p class="sub">Amount: <strong id="statPendingAmount">0.00</strong></p>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="chq-stat-box stat-cleared">
            <p class="label"><i class="bi bi-check2-circle me-1"></i>Cleared Total</p>
            <p class="value text-success" id="statClearedCount">0</p>
            <p class="sub">Amount: <strong id="statClearedAmount">0.00</strong></p>
          </div>
        </div>
      </div>

      <div class="card chq-card mb-3">
        <div class="card-header chq-head py-2 px-3">
          <div class="d-flex flex-wrap align-items-center gap-2 justify-content-between">
            <div>
              <strong class="d-block" id="chequeListTitle">Today Cheques</strong>
              <small class="text-white-50">Select cheques and assign a Worker</small>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <button type="button" class="btn btn-sm btn-outline-light fw-semibold" id="btnPrintPartyCheques" title="Print party cheque report">
                <i class="bi bi-printer me-1"></i>Print Report
              </button>
              <div class="chq-scope-wrap btn-group flex-wrap" role="group" aria-label="Cheque scope">
                <button type="button" class="btn btn-sm btn-outline-light btn-scope active" data-scope="today" id="btnTodayChq">
                  <i class="bi bi-calendar-day me-1"></i>Today
                </button>
                <button type="button" class="btn btn-sm btn-outline-light btn-scope" data-scope="month" id="btnMonthChq">
                  <i class="bi bi-calendar-month me-1"></i>This Month
                </button>
                <button type="button" class="btn btn-sm btn-outline-light btn-scope" data-scope="pending" id="btnPendingChq">
                  <i class="bi bi-hourglass-split me-1"></i>Pending
                </button>
                <button type="button" class="btn btn-sm btn-outline-light btn-scope" data-scope="all" id="btnAllChq">
                  <i class="bi bi-list-ul me-1"></i>All
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="chq-toolbar px-3 py-3">
          <div class="row g-3 align-items-end">
            <div class="col-6 col-lg-2">
              <label class="form-label" for="chqFromDate">From Date</label>
              <input type="date" class="form-control form-control-sm" id="chqFromDate">
            </div>
            <div class="col-6 col-lg-2">
              <label class="form-label" for="chqToDate">To Date</label>
              <input type="date" class="form-control form-control-sm" id="chqToDate">
            </div>
            <div class="col-12 col-lg-3">
              <label class="form-label d-none d-lg-block">&nbsp;</label>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-primary flex-grow-1" id="btnApplyDateRange">
                  <i class="bi bi-funnel me-1"></i>Apply
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnClearDateRange" title="Clear dates">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>
            </div>
            <div class="col-12 col-lg-5">
              <div class="chq-assign-box">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                  <label class="form-label mb-0" for="chqWorkerSelect">
                    <i class="bi bi-person-badge me-1"></i>Worker
                    <span class="text-muted fw-normal normal-case">(category: Worker)</span>
                  </label>
                  <span class="chq-selected-pill" id="chqSelectedHint">
                    <i class="bi bi-check2-square"></i> 0 selected
                  </span>
                </div>
                <div class="input-group input-group-sm">
                  <select class="form-select" id="chqWorkerSelect">
                    <option value="">Select Worker</option>
                    @forelse(($workers ?? []) as $w)
                      <option value="{{ $w->id }}">{{ $w->labourName }} ({{ $w->labourCode }})</option>
                    @empty
                      <option value="" disabled>No workers found</option>
                    @endforelse
                  </select>
                  <button type="button" class="btn btn-success px-3" id="btnAssignWorker" title="Assign worker to selected cheques">
                    <i class="bi bi-person-check me-1"></i>Assign
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive chq-table-wrap">
            <table class="table table-hover table-striped align-middle mb-0" id="chequeTable">
              <thead>
                <tr>
                  <th class="text-center" style="width:2.5rem;">
                    <input type="checkbox" class="form-check-input" id="chqSelectAll" title="Select all">
                  </th>
                  <th>Original Due</th>
                  <th>Due</th>
                  <th>Chq Date</th>
                  <th>Party</th>
                  <th>Area</th>
                  <th>Bank</th>
                  <th>Cheque No</th>
                  <th class="text-end">Amount</th>
                  <th class="text-center">Days</th>
                  <th>Worker</th>
                  <th>Description</th>
                  <th>Status</th>
                  <th class="text-center">To Pay</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="14" class="text-muted py-5 px-3 text-center">
                    <i class="bi bi-hourglass-split d-block mb-2 fs-4 text-secondary"></i>
                    Loading cheques...
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card-footer bg-white border-top py-2 px-3">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <small class="text-muted" id="chequeMeta">—</small>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <label class="small text-muted mb-0" for="chequePerPage">Rows</label>
              <select id="chequePerPage" class="form-select form-select-sm" style="width:auto;">
                <option value="10">10</option>
                <option value="15" selected>15</option>
                <option value="25">25</option>
                <option value="50">50</option>
              </select>
              <nav id="chequePager" aria-label="Cheque pagination"></nav>
              <a href="{{ route('partyCheques.inProcess') }}" class="btn btn-sm btn-outline-info">
                <i class="bi bi-hourglass-top me-1"></i>In Process
              </a>
              <button type="button" class="btn btn-sm btn-outline-primary" id="btnRefreshCheques">
                <i class="bi bi-arrow-clockwise me-1"></i>Refresh
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

{{-- Pay / Transfer Modal --}}
<div class="modal fade" id="chequePayModal" tabindex="-1" aria-labelledby="chequePayModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header chq-head py-2">
        <h5 class="modal-title" id="chequePayModalLabel">
          <i class="bi bi-cash-coin me-2"></i>To Pay / Transfer Cheque
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="chequePayForm">
        <div class="modal-body">
          <input type="hidden" id="payChequeId" value="">
          <div class="mb-3 p-2 rounded bg-light border">
            <div class="small text-muted">Cheque</div>
            <div class="fw-semibold" id="payChqInfo">—</div>
            <div class="small" id="payChqParty">—</div>
          </div>

          <label class="form-label small fw-semibold">Transfer Type</label>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="pay-type-card d-block active border rounded p-2" id="payTypeBankCard" style="cursor:pointer;">
                <input type="radio" name="payee_type" value="bank" checked class="d-none">
                <div class="fw-semibold"><i class="bi bi-bank me-1 text-success"></i>Transfer to Bank</div>
                <small class="text-muted">Deposit cheque in bank</small>
              </label>
            </div>
            <div class="col-6">
              <label class="pay-type-card d-block border rounded p-2" id="payTypePartyCard" style="cursor:pointer;">
                <input type="radio" name="payee_type" value="party" class="d-none">
                <div class="fw-semibold"><i class="bi bi-person me-1 text-primary"></i>Transfer to Party</div>
                <small class="text-muted">Pay to customer</small>
              </label>
            </div>
          </div>

          <div class="mb-3" id="payBankWrap">
            <label class="form-label small fw-semibold" for="payBankSelect">Bank Account</label>
            <select class="form-select form-select-sm" id="payBankSelect">
              <option value="">Select Bank</option>
              @foreach(($bankAccounts ?? []) as $bank)
                <option value="{{ $bank->bankAccount }}">
                  {{ $bank->bankName }} — {{ $bank->bankAccount }}@if($bank->branchCode) ({{ $bank->branchCode }})@endif
                </option>
              @endforeach
            </select>
          </div>

          <div class="mb-3 d-none" id="payPartyWrap">
            <label class="form-label small fw-semibold" for="payPartySearch">Customer / Party</label>
            <div class="position-relative">
              <input type="text" class="form-control form-control-sm" id="payPartySearch"
                     placeholder="Type to search party..." autocomplete="off">
              <input type="hidden" id="payPartyCode" value="">
              <select class="d-none" id="payPartySelect">
                <option value="">Select Party</option>
                @foreach(($parties ?? []) as $p)
                  <option value="{{ $p->custCode }}"
                          data-name="{{ $p->custName }}"
                          data-business="{{ $p->bussnessName ?? '' }}"
                          data-area="{{ $p->custArea ?? '' }}">
                    {{ $p->custName }}@if(!empty($p->bussnessName)) | {{ $p->bussnessName }}@endif
                  </option>
                @endforeach
              </select>
              <div id="payPartyDropdown" class="dropdown-menu w-100" style="display:none; max-height:260px; overflow-y:auto;"></div>
            </div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label small fw-semibold" for="payAmount">Amount</label>
              <input type="number" step="0.01" min="0.01" class="form-control form-control-sm bg-light" id="payAmount" readonly>
            </div>
            <div class="col-6">
              <label class="form-label small fw-semibold" for="payDate">Date</label>
              <input type="date" class="form-control form-control-sm" id="payDate" value="{{ now()->format('Y-m-d') }}">
            </div>
          </div>

          <div class="mb-1">
            <label class="form-label small fw-semibold" for="payRemark">Remark</label>
            <input type="text" class="form-control form-control-sm" id="payRemark" maxlength="255"
                   placeholder="Optional remark...">
          </div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-success" id="btnPaySave">
            <i class="bi bi-check2-circle me-1"></i>Save Pay
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  .pay-type-card.active {
    border-color: #1a4a8a !important;
    background: #eef4ff;
  }
</style>

<script>
$(function () {
  const listUrl = @json(route('partyCheques.list', [], false));
  const printUrl = @json(route('partyCheques.print', [], false));
  const assignUrl = @json(route('partyCheques.assignWorker', [], false));
  const payUrlBase = @json(url('/party-cheques'));
  const csrf = $('meta[name="csrf-token"]').attr('content');
  let currentScope = 'today';
  let currentPage = 1;
  let payModal = null;
  const rowsById = {};
  if (window.bootstrap && bootstrap.Modal) {
    payModal = new bootstrap.Modal(document.getElementById('chequePayModal'));
  }

  const fmt = (n) => (parseFloat(n || 0)).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });

  function esc(s) {
    return String(s ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function dateFilters() {
    return {
      from_date: $('#chqFromDate').val() || '',
      to_date: $('#chqToDate').val() || ''
    };
  }

  $('#btnPrintPartyCheques').on('click', function () {
    const dates = dateFilters();
    const params = new URLSearchParams();
    params.set('scope', currentScope || 'today');
    if (dates.from_date) params.set('from_date', dates.from_date);
    if (dates.to_date) params.set('to_date', dates.to_date);
    const w = window.open(printUrl + '?' + params.toString(), '_blank');
    if (!w) {
      Swal.fire({ icon: 'warning', title: 'Popup blocked', text: 'Please allow popups to print.' });
    }
  });

  function updateSelectedHint() {
    const $checks = $('#chequeTable tbody .chq-row-check');
    const n = $checks.filter(':checked').length;
    const total = $checks.length;
    $('#chqSelectedHint').html('<i class="bi bi-check2-square"></i> ' + n + ' selected');
    $('#chqSelectAll').prop('checked', total > 0 && n === total);
    $('#chqSelectAll').prop('indeterminate', n > 0 && n < total);
    $checks.each(function () {
      $(this).closest('tr').toggleClass('chq-selected', $(this).is(':checked'));
    });
  }

  function setScope(scope) {
    const allowed = ['today', 'pending', 'all', 'month'];
    currentScope = allowed.indexOf(scope) >= 0 ? scope : 'today';
    currentPage = 1;
    $('.btn-scope').removeClass('active');
    $('.btn-scope[data-scope="' + currentScope + '"]').addClass('active');
    const titles = {
      today: 'Today Cheques',
      month: 'This Month Cheques',
      pending: 'Pending Cheques',
      all: 'All Cheques'
    };
    $('#chequeListTitle').text(titles[currentScope] || 'Cheques');
    loadCheques();
  }

  function updateStats(meta) {
    $('#statTotalChq').text(meta.count != null ? meta.count : 0);
    $('#statTotalAmount').text(fmt(meta.total_amount || 0));
    $('#statPendingCount').text(meta.pending_count != null ? meta.pending_count : 0);
    $('#statPendingAmount').text(fmt(meta.pending_amount || 0));
    $('#statClearedCount').text(meta.cleared_count != null ? meta.cleared_count : 0);
    $('#statClearedAmount').text(fmt(meta.cleared_amount || 0));
    const hints = {
      today: 'Today scope',
      month: 'This month',
      pending: 'Pending filter',
      all: 'All cheques'
    };
    $('#statTotalHint').text(hints[meta.scope] || 'In current list');
  }

  function renderPager(meta) {
    const $pager = $('#chequePager').empty();
    const last = parseInt(meta.last_page, 10) || 1;
    const page = parseInt(meta.page, 10) || 1;
    if (last <= 1) return;

    let html = '<ul class="pagination pagination-sm mb-0">';
    html += '<li class="page-item' + (page <= 1 ? ' disabled' : '') + '">' +
      '<a class="page-link" href="#" data-page="' + (page - 1) + '">Prev</a></li>';
    html += '<li class="page-item disabled"><span class="page-link">' + page + ' / ' + last + '</span></li>';
    html += '<li class="page-item' + (page >= last ? ' disabled' : '') + '">' +
      '<a class="page-link" href="#" data-page="' + (page + 1) + '">Next</a></li>';
    html += '</ul>';
    $pager.html(html);
  }

  function statusBadge(status) {
    const s = String(status || '');
    if (s === 'Cleared') return '<span class="badge text-bg-success">Cleared</span>';
    if (s === 'Pending') return '<span class="badge text-bg-warning text-dark">Pending</span>';
    return '<span class="badge text-bg-secondary">' + esc(s || '-') + '</span>';
  }

  function renderRows(rows) {
    const $tb = $('#chequeTable tbody').empty();
    Object.keys(rowsById).forEach(function (k) { delete rowsById[k]; });
    $('#chqSelectAll').prop('checked', false).prop('indeterminate', false);
    if (!rows || !rows.length) {
      $tb.html(
        '<tr><td colspan="14" class="text-muted py-5 px-3 text-center">' +
        '<i class="bi bi-inbox d-block mb-2 fs-3 text-secondary"></i>No cheques found</td></tr>'
      );
      updateSelectedHint();
      return;
    }
    rows.forEach(function (r) {
      rowsById[String(r.id)] = r;
      const party = esc(r.party || r.customer_code || '-');
      const desc = esc(r.description || '-');
      const bank = esc(r.bank_name || '-');
      const worker = esc(r.worker_name || '—');
      const carry = !!r.is_carry;
      const isCleared = String(r.status) === 'Cleared';
      const paid = !!r.is_paid;
      const days = parseInt(r.days, 10) || 0;
      const daysCell = days > 0
        ? '<span class="badge text-bg-danger">' + days + '</span>'
        : '<span class="text-muted">0</span>';
      const dueCell = carry
        ? esc(r.due_date || '-') + ' <span class="badge text-bg-warning">Carry</span>'
        : esc(r.due_date || '-');
      const actions = paid
        ? '<span class="badge text-bg-success">Paid</span>'
        : `<button type="button" class="btn btn-sm btn-primary py-0 px-2 btn-to-pay" data-id="${r.id}" title="To Pay">
              <i class="bi bi-cash-coin me-1"></i>To Pay
            </button>`;
      const rowClass = [carry ? 'chq-carry' : '', isCleared ? 'chq-cleared' : ''].filter(Boolean).join(' ');
      const workerCell = r.worker_name
        ? '<span class="badge text-bg-info text-dark">' + worker + '</span>'
        : '<span class="text-muted">—</span>';
      $tb.append(`
        <tr data-id="${r.id}" class="${rowClass}">
          <td class="text-center">
            <input type="checkbox" class="form-check-input chq-row-check" value="${r.id}">
          </td>
          <td>${esc(r.original_due_date || r.due_date || '-')}</td>
          <td>${dueCell}</td>
          <td>${esc(r.cheque_date || '-')}</td>
          <td class="chq-clip" title="${party}">${party}</td>
          <td>${esc(r.area || '-')}</td>
          <td class="chq-clip" title="${bank}">${bank}</td>
          <td class="fw-semibold">${esc(r.cheque_no || '-')}</td>
          <td class="text-end fw-semibold">${fmt(r.amount)}</td>
          <td class="text-center">${daysCell}</td>
          <td class="chq-clip" title="${worker}">${workerCell}</td>
          <td class="chq-clip" title="${desc}">${desc}</td>
          <td>${statusBadge(r.status)}</td>
          <td class="text-center">${actions}</td>
        </tr>
      `);
    });
    updateSelectedHint();
  }

  function loadCheques() {
    $('#chequeMeta').text('Loading...');
    const perPage = parseInt($('#chequePerPage').val(), 10) || 15;
    const dates = dateFilters();
    return $.ajax({
      url: listUrl,
      method: 'GET',
      data: {
        scope: currentScope,
        page: currentPage,
        per_page: perPage,
        from_date: dates.from_date || undefined,
        to_date: dates.to_date || undefined
      },
      dataType: 'json',
      cache: false,
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    }).done(function (res) {
      const rows = (res && Array.isArray(res.data)) ? res.data : [];
      const meta = res.meta || {};
      currentPage = parseInt(meta.page, 10) || 1;
      renderRows(rows);
      updateStats(meta);
      renderPager(meta);
      let label = '';
      if (meta.count) {
        label = 'Showing ' + (meta.from || 0) + '–' + (meta.to || 0) + ' of ' + meta.count;
      } else {
        label = '0 cheques';
      }
      if (meta.scope === 'today') {
        label += ' · Today (' + (meta.today || '') + ')';
      } else if (meta.scope === 'month') {
        label += ' · This month';
      } else if (meta.scope === 'pending') {
        label += ' · Pending only';
      } else {
        label += ' · All';
      }
      if (dates.from_date || dates.to_date) {
        label += ' · ' + (dates.from_date || '…') + ' → ' + (dates.to_date || '…');
      }
      $('#chequeMeta').text(label);
    }).fail(function (xhr) {
      $('#chequeTable tbody').html(
        '<tr><td colspan="14" class="text-danger py-5 px-3 text-center">Failed to load cheques</td></tr>'
      );
      updateStats({
        count: 0, total_amount: 0, pending_count: 0, pending_amount: 0,
        cleared_count: 0, cleared_amount: 0
      });
      $('#chequePager').empty();
      $('#chequeMeta').text('Load failed');
      updateSelectedHint();
      console.error('party-cheques/list', xhr.status, xhr.responseText);
    });
  }

  function setPayType(type) {
    $('input[name="payee_type"][value="' + type + '"]').prop('checked', true);
    $('#payTypeBankCard, #payTypePartyCard').removeClass('active');
    if (type === 'bank') {
      $('#payTypeBankCard').addClass('active');
      $('#payBankWrap').removeClass('d-none');
      $('#payPartyWrap').addClass('d-none');
    } else {
      $('#payTypePartyCard').addClass('active');
      $('#payBankWrap').addClass('d-none');
      $('#payPartyWrap').removeClass('d-none');
    }
  }

  function openPayModal(row) {
    if (!row || !row.id) return;
    $('#payChequeId').val(row.id);
    $('#payChqInfo').text((row.cheque_no || '-') + ' · ' + (row.bank_name || '-') + ' · Rs ' + fmt(row.amount));
    $('#payChqParty').text((row.party || row.customer_code || '-') + (row.worker_name ? ' · Worker: ' + row.worker_name : ''));
    $('#payAmount').val(Number(row.amount || 0).toFixed(2));
    $('#payDate').val(new Date().toISOString().slice(0, 10));
    $('#payRemark').val('');
    $('#payBankSelect').val('');
    $('#payPartyCode').val('');
    $('#payPartySearch').val('');
    $('#payPartyDropdown').hide();
    setPayType('bank');
    if (payModal) payModal.show();
    else $('#chequePayModal').modal('show');
  }

  function filterParties(q) {
    q = String(q || '').toLowerCase().trim();
    const list = [];
    $('#payPartySelect option').each(function () {
      const code = $(this).val();
      if (!code) return;
      const name = String($(this).data('name') || '');
      const business = String($(this).data('business') || '');
      const area = String($(this).data('area') || '');
      const hay = (name + ' ' + business + ' ' + area + ' ' + code).toLowerCase();
      if (!q || hay.indexOf(q) >= 0) {
        list.push({ code, name, business, area });
      }
    });
    return list.slice(0, 20);
  }

  function renderPartyDropdown(list) {
    const $dd = $('#payPartyDropdown').empty();
    if (!list.length) {
      $dd.html('<div class="dropdown-item text-muted small">No party found</div>').show();
      return;
    }
    list.forEach(function (p, idx) {
      $dd.append(
        '<button type="button" class="dropdown-item' + (idx === 0 ? ' active' : '') + '" data-code="' + esc(p.code) + '">' +
        '<strong>' + esc(p.name) + (p.business ? ' | ' + esc(p.business) : '') + '</strong>' +
        '<div class="small text-muted">' + esc(p.area || '') + ' · ' + esc(p.code) + '</div></button>'
      );
    });
    $dd.show();
  }

  function assignWorker() {
    const workerId = $('#chqWorkerSelect').val();
    const ids = $('#chequeTable tbody .chq-row-check:checked').map(function () {
      return parseInt($(this).val(), 10);
    }).get().filter(Boolean);

    if (!workerId) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'warning', title: 'Required', text: 'Please select a worker' });
      } else {
        alert('Please select a worker');
      }
      return;
    }
    if (!ids.length) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'warning', title: 'Required', text: 'Select at least one cheque' });
      } else {
        alert('Select at least one cheque');
      }
      return;
    }

    const workerName = $('#chqWorkerSelect option:selected').text();
    const run = function () {
      $.ajax({
        url: assignUrl,
        method: 'POST',
        data: {
          _token: csrf,
          worker_id: workerId,
          cheque_ids: ids
        },
        dataType: 'json',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      }).done(function (res) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'success',
            title: 'Assigned',
            text: res.message || 'Worker assigned',
            timer: 1600,
            showConfirmButton: false
          });
        }
        loadCheques();
      }).fail(function (xhr) {
        const msg = xhr.responseJSON?.message || 'Assign failed';
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'error', title: 'Failed', text: msg });
        } else {
          alert(msg);
        }
      });
    };

    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'question',
        title: 'Assign worker?',
        text: 'Assign "' + workerName + '" to ' + ids.length + ' cheque(s)?',
        showCancelButton: true,
        confirmButtonText: 'Yes, Assign',
        confirmButtonColor: '#198754'
      }).then(function (r) {
        if (r.isConfirmed) run();
      });
    } else if (confirm('Assign worker to selected cheques?')) {
      run();
    }
  }

  $('.btn-scope').on('click', function () {
    setScope($(this).data('scope'));
  });

  $('#btnApplyDateRange').on('click', function () {
    currentPage = 1;
    loadCheques();
  });

  $('#btnClearDateRange').on('click', function () {
    $('#chqFromDate, #chqToDate').val('');
    currentPage = 1;
    loadCheques();
  });

  $('#btnAssignWorker').on('click', assignWorker);

  $('#chqSelectAll').on('change', function () {
    const on = $(this).is(':checked');
    $('#chequeTable tbody .chq-row-check').prop('checked', on);
    updateSelectedHint();
  });

  $(document).on('change', '.chq-row-check', updateSelectedHint);

  $('#btnRefreshCheques').on('click', function () {
    loadCheques();
  });

  $('#chequePerPage').on('change', function () {
    currentPage = 1;
    loadCheques();
  });

  $(document).on('click', '#chequePager a[data-page]', function (e) {
    e.preventDefault();
    const p = parseInt($(this).data('page'), 10) || 1;
    if (p < 1 || $(this).parent().hasClass('disabled')) return;
    currentPage = p;
    loadCheques();
  });

  $(document).on('click', '.btn-to-pay', function () {
    const id = String($(this).data('id') || '');
    openPayModal(rowsById[id]);
  });

  $('.pay-type-card').on('click', function () {
    setPayType($(this).find('input').val());
  });

  $('#payPartySearch').on('focus input', function () {
    renderPartyDropdown(filterParties($(this).val()));
  });

  $(document).on('click', '#payPartyDropdown .dropdown-item[data-code]', function () {
    const code = $(this).data('code');
    const $opt = $('#payPartySelect option[value="' + code + '"]');
    const name = $opt.data('name') || '';
    const business = $opt.data('business') || '';
    $('#payPartyCode').val(code);
    $('#payPartySearch').val(business ? (name + ' | ' + business) : name);
    $('#payPartyDropdown').hide();
  });

  $(document).on('click', function (e) {
    if (!$(e.target).closest('#payPartySearch, #payPartyDropdown').length) {
      $('#payPartyDropdown').hide();
    }
  });

  $('#chequePayForm').on('submit', function (e) {
    e.preventDefault();
    const id = $('#payChequeId').val();
    const payeeType = $('input[name="payee_type"]:checked').val() || 'bank';
    const payeeCode = payeeType === 'bank'
      ? ($('#payBankSelect').val() || '').trim()
      : ($('#payPartyCode').val() || '').trim();
    const amount = parseFloat($('#payAmount').val()) || 0;
    const remark = ($('#payRemark').val() || '').trim();
    const paidAt = $('#payDate').val() || '';

    if (!id) return;
    if (!payeeCode) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'warning',
          title: 'Required',
          text: payeeType === 'bank' ? 'Please select a bank' : 'Please select a party'
        });
      }
      return;
    }

    const $btn = $('#btnPaySave').prop('disabled', true);
    $.ajax({
      url: payUrlBase + '/' + encodeURIComponent(id) + '/pay',
      method: 'POST',
      data: {
        _token: csrf,
        payee_type: payeeType,
        payee_code: payeeCode,
        amount: amount,
        remark: remark || null,
        paid_at: paidAt || null
      },
      dataType: 'json',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    }).done(function (res) {
      if (payModal) payModal.hide();
      else $('#chequePayModal').modal('hide');
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'success',
          title: 'Paid',
          text: res.message || 'Cheque paid',
          timer: 1600,
          showConfirmButton: false
        });
      }
      loadCheques();
    }).fail(function (xhr) {
      const msg = xhr.responseJSON?.message || 'Pay failed';
      if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'error', title: 'Failed', text: msg });
      } else {
        alert(msg);
      }
    }).always(function () {
      $btn.prop('disabled', false);
    });
  });

  loadCheques();
});
</script>
@endsection
