@extends('layout.app')

@section('title', 'Cheque in Process')

@section('content')
<style>
  .chq-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
  }
  #processTable {
    font-size: 0.875rem;
    white-space: nowrap;
  }
  #processTable th,
  #processTable td {
    vertical-align: middle;
    white-space: nowrap;
    padding-top: 0.4rem;
    padding-bottom: 0.4rem;
  }
  #processTable .chq-clip {
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .chq-stat-box {
    border: 1px solid #e2e8f0;
    border-radius: 0.65rem;
    background: #fff;
    padding: 0.85rem 1rem;
    height: 100%;
    box-shadow: 0 1px 3px rgba(15, 39, 71, 0.06);
    border-top: 3px solid var(--chq-accent, #0d6efd);
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
    font-size: 1.35rem;
    font-weight: 700;
    color: #0f2747;
    margin: 0;
    line-height: 1.2;
  }
  .chq-stat-box .sub {
    font-size: 0.8rem;
    color: #64748b;
    margin: 0.25rem 0 0;
  }
  .chq-stat-box.stat-total { --chq-accent: #0d6efd; }
  .chq-stat-box.stat-amount { --chq-accent: #198754; }
  #payPartyDropdown.dropdown-menu {
    max-height: 260px;
    overflow-y: auto;
    width: 100%;
  }
  .pay-type-card {
    border: 1px solid #dbe3ef;
    border-radius: 0.65rem;
    padding: 0.75rem;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
  }
  .pay-type-card.active {
    border-color: #1a4a8a;
    background: #eef4ff;
  }
  .pay-type-card input { display: none; }
</style>

<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-6">
          <h3 class="mb-0"><i class="bi bi-hourglass-top text-primary me-2"></i>Cheque in Process</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('partyCheques.index') }}">Party Cheques</a></li>
            <li class="breadcrumb-item active" aria-current="page">In Process</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
          <div class="chq-stat-box stat-total">
            <p class="label"><i class="bi bi-collection me-1"></i>In Process</p>
            <p class="value text-primary" id="statCount">0</p>
            <p class="sub">Worker assigned</p>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="chq-stat-box stat-amount">
            <p class="label"><i class="bi bi-cash-stack me-1"></i>Total Amount</p>
            <p class="value text-success" id="statAmount">0.00</p>
            <p class="sub">Filtered list</p>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0 mb-3">
        <div class="card-header chq-head py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
          <strong class="mb-0"><i class="bi bi-table me-2"></i>Worker Cheques</strong>
          <button type="button" class="btn btn-sm btn-outline-light fw-semibold" id="btnPrintInProcess" title="Print cheque in process report">
            <i class="bi bi-printer me-1"></i>Print Report
          </button>
        </div>
        <div class="card-body border-bottom py-2">
          <div class="row g-2 align-items-end">
            <div class="col-md-4">
              <label class="form-label small fw-semibold mb-1" for="procSearch">Search</label>
              <input type="text" class="form-control form-control-sm" id="procSearch"
                     placeholder="Name, bank, chq no, party, paid to, date…">
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold mb-1" for="procWorker">Worker</label>
              <select class="form-select form-select-sm" id="procWorker">
                <option value="">All Workers</option>
                @foreach(($workers ?? []) as $w)
                  <option value="{{ $w->id }}">{{ $w->labourName }} ({{ $w->labourCode }})</option>
                @endforeach
              </select>
            </div>
            <div class="col-6 col-md-2">
              <label class="form-label small fw-semibold mb-1" for="procFrom">From</label>
              <input type="date" class="form-control form-control-sm" id="procFrom">
            </div>
            <div class="col-6 col-md-2">
              <label class="form-label small fw-semibold mb-1" for="procTo">To</label>
              <input type="date" class="form-control form-control-sm" id="procTo">
            </div>
            <div class="col-md-1">
              <button type="button" class="btn btn-sm btn-primary w-100" id="btnProcSearch">
                <i class="bi bi-search"></i>
              </button>
            </div>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0" id="processTable">
              <thead class="table-light">
                <tr>
                  <th>Due</th>
                  <th>Chq Date</th>
                  <th>Worker</th>
                  <th>Party</th>
                  <th>Bank</th>
                  <th>Cheque No</th>
                  <th class="text-end">Amount</th>
                  <th>Paid To</th>
                  <th>Status</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="10" class="text-muted py-4 px-3 text-center">Loading...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer bg-light py-2">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <small class="text-muted" id="procMeta">—</small>
            <div class="d-flex align-items-center gap-2">
              <select id="procPerPage" class="form-select form-select-sm" style="width:auto;">
                <option value="10">10</option>
                <option value="15" selected>15</option>
                <option value="25">25</option>
                <option value="50">50</option>
              </select>
              <nav id="procPager"></nav>
              <a href="{{ route('partyCheques.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Party Cheques
              </a>
              <button type="button" class="btn btn-sm btn-outline-primary" id="btnProcRefresh">
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
          <i class="bi bi-cash-coin me-2"></i>Pay / Transfer Cheque
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
              <label class="pay-type-card d-block active" id="payTypeBankCard">
                <input type="radio" name="payee_type" value="bank" checked>
                <div class="fw-semibold"><i class="bi bi-bank me-1 text-success"></i>Transfer to Bank</div>
                <small class="text-muted">Deposit cheque in bank</small>
              </label>
            </div>
            <div class="col-6">
              <label class="pay-type-card d-block" id="payTypePartyCard">
                <input type="radio" name="payee_type" value="party">
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
                          data-area="{{ $p->custArea ?? '' }}"
                          data-city="{{ $p->custCity ?? '' }}">
                    {{ $p->custName }}@if(!empty($p->bussnessName)) | {{ $p->bussnessName }}@endif
                  </option>
                @endforeach
              </select>
              <div id="payPartyDropdown" class="dropdown-menu w-100" style="display:none;"></div>
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

<script>
$(function () {
  const listUrl = @json(route('partyCheques.inProcessList', [], false));
  const printUrl = @json(route('partyCheques.inProcessPrint', [], false));
  const payUrlBase = @json(url('/party-cheques'));
  const csrf = $('meta[name="csrf-token"]').attr('content');
  let currentPage = 1;
  let searchTimer = null;
  let payModal = null;
  const rowsById = {};

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

  if (window.bootstrap && bootstrap.Modal) {
    payModal = new bootstrap.Modal(document.getElementById('chequePayModal'));
  }

  $('#btnPrintInProcess').on('click', function () {
    const params = new URLSearchParams();
    const q = ($('#procSearch').val() || '').trim();
    const workerId = $('#procWorker').val() || '';
    const fromDate = $('#procFrom').val() || '';
    const toDate = $('#procTo').val() || '';
    if (q) params.set('q', q);
    if (workerId) params.set('worker_id', workerId);
    if (fromDate) params.set('from_date', fromDate);
    if (toDate) params.set('to_date', toDate);
    const qs = params.toString();
    const w = window.open(printUrl + (qs ? ('?' + qs) : ''), '_blank');
    if (!w) {
      Swal.fire({ icon: 'warning', title: 'Popup blocked', text: 'Please allow popups to print.' });
    }
  });

  function statusBadge(status) {
    const s = String(status || '');
    if (s === 'Cleared') return '<span class="badge text-bg-success">Cleared</span>';
    if (s === 'Pending') return '<span class="badge text-bg-warning text-dark">Pending</span>';
    return '<span class="badge text-bg-secondary">' + esc(s || '-') + '</span>';
  }

  function renderPager(meta) {
    const $pager = $('#procPager').empty();
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

  function renderRows(rows) {
    const $tb = $('#processTable tbody').empty();
    Object.keys(rowsById).forEach(function (k) { delete rowsById[k]; });
    if (!rows || !rows.length) {
      $tb.html('<tr><td colspan="10" class="text-muted py-4 px-3 text-center">No worker cheques found</td></tr>');
      return;
    }
    rows.forEach(function (r) {
      rowsById[String(r.id)] = r;
      const party = esc(r.party || r.customer_code || '-');
      const bank = esc(r.bank_name || '-');
      const worker = esc(r.worker_name || '-');
      const paid = !!r.is_paid;
      const paidTo = paid
        ? ('<span class="badge ' + (r.payee_type === 'bank' ? 'text-bg-success' : 'text-bg-primary') + '">' +
            esc(r.payee_type === 'bank' ? 'Bank' : 'Party') + '</span> ' +
            '<span class="chq-clip d-inline-block align-middle" style="max-width:120px;" title="' + esc(r.payee_name || '') + '">' +
            esc(r.payee_name || '-') + '</span>')
        : '<span class="text-muted">—</span>';
      const action = paid
        ? '<span class="text-muted small">Paid</span>'
        : '<button type="button" class="btn btn-sm btn-primary py-0 px-2 btn-pay-chq" data-id="' + r.id + '">' +
            '<i class="bi bi-cash-coin"></i> Pay</button>';
      $tb.append(`
        <tr>
          <td>${esc(r.due_date || '-')}</td>
          <td>${esc(r.cheque_date || '-')}</td>
          <td class="fw-semibold chq-clip" title="${worker}">${worker}</td>
          <td class="chq-clip" title="${party}">${party}</td>
          <td class="chq-clip" title="${bank}">${bank}</td>
          <td class="fw-semibold">${esc(r.cheque_no || '-')}</td>
          <td class="text-end">${fmt(r.amount)}</td>
          <td>${paidTo}</td>
          <td>${statusBadge(r.status)}</td>
          <td class="text-center">${action}</td>
        </tr>
      `);
    });
  }

  function loadList() {
    $('#procMeta').text('Loading...');
    const perPage = parseInt($('#procPerPage').val(), 10) || 15;
    return $.ajax({
      url: listUrl,
      method: 'GET',
      data: {
        q: ($('#procSearch').val() || '').trim() || undefined,
        worker_id: $('#procWorker').val() || undefined,
        from_date: $('#procFrom').val() || undefined,
        to_date: $('#procTo').val() || undefined,
        page: currentPage,
        per_page: perPage
      },
      dataType: 'json',
      cache: false,
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    }).done(function (res) {
      const rows = (res && Array.isArray(res.data)) ? res.data : [];
      const meta = res.meta || {};
      currentPage = parseInt(meta.page, 10) || 1;
      renderRows(rows);
      renderPager(meta);
      $('#statCount').text(meta.count != null ? meta.count : 0);
      $('#statAmount').text(fmt(meta.total_amount || 0));
      let label = meta.count
        ? ('Showing ' + (meta.from || 0) + '–' + (meta.to || 0) + ' of ' + meta.count)
        : '0 cheques';
      $('#procMeta').text(label);
    }).fail(function () {
      $('#processTable tbody').html(
        '<tr><td colspan="10" class="text-danger py-4 px-3 text-center">Failed to load</td></tr>'
      );
      $('#statCount').text('0');
      $('#statAmount').text('0.00');
      $('#procPager').empty();
      $('#procMeta').text('Load failed');
    });
  }

  function searchNow() {
    currentPage = 1;
    loadList();
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

  $('.pay-type-card').on('click', function () {
    const type = $(this).find('input').val();
    setPayType(type);
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

  $(document).on('click', '.btn-pay-chq', function () {
    const id = String($(this).data('id') || '');
    openPayModal(rowsById[id]);
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
    if (amount <= 0) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'warning', title: 'Required', text: 'Invalid amount' });
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
      loadList();
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

  $('#btnProcSearch').on('click', searchNow);
  $('#procWorker, #procFrom, #procTo, #procPerPage').on('change', searchNow);
  $('#procSearch').on('input', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(searchNow, 350);
  });
  $('#procSearch').on('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      searchNow();
    }
  });
  $('#btnProcRefresh').on('click', loadList);

  $(document).on('click', '#procPager a[data-page]', function (e) {
    e.preventDefault();
    const p = parseInt($(this).data('page'), 10) || 1;
    if (p < 1 || $(this).parent().hasClass('disabled')) return;
    currentPage = p;
    loadList();
  });

  loadList();
});
</script>
@endsection
