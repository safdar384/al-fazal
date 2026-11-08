@extends('layout.app')

@section('title', 'Labour Ledger')

@section('content')

@include('components.toast-notification')

<style>
  /* Labour list dropdown — do not change */
  .position-relative .dropdown-menu {
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-top: 4px;
  }
  .position-relative .dropdown-menu .dropdown-item {
    padding: 10px 15px;
    cursor: pointer;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
  }
  .position-relative .dropdown-menu .dropdown-item:hover,
  .position-relative .dropdown-menu .dropdown-item.active {
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    color: white;
    border-left-color: #ffc107;
    transform: translateX(3px);
  }
  .position-relative .dropdown-menu .dropdown-item strong {
    font-size: 0.95rem;
    display: block;
    margin-bottom: 3px;
  }
  .position-relative .dropdown-menu .dropdown-item .text-muted {
    font-size: 0.8rem;
    opacity: 0.8;
  }
  .position-relative .dropdown-menu .dropdown-item:hover .text-muted,
  .position-relative .dropdown-menu .dropdown-item.active .text-muted {
    color: rgba(255, 255, 255, 0.9) !important;
  }
  .position-relative input[type="text"].form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
  }

  /* Page layout */
  .ledger-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
  }
  @media print {
    .app-sidebar, .app-header, .app-content-header, #filterCard, .no-print { display: none !important; }
  }
</style>

<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-6">
          <h3 class="mb-0"><i class="bi bi-journal-text text-primary me-2"></i>Labour Ledger</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('labour.account.index') }}">Account Transaction</a></li>
            <li class="breadcrumb-item active" aria-current="page">Labour Ledger</li>
          </ol>
        </div>
      </div>
      <hr class="mb-0" style="border-top: 2px solid #0d6efd;">
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <div class="card shadow-sm mb-4" id="filterCard">
        <div class="card-header ledger-card-head py-2">
          <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filters</h5>
        </div>
        <div class="card-body">
          <div class="row g-2 mb-3">
            <div class="col-md-4">
              <label class="form-label small fw-semibold mb-1">
                <i class="bi bi-person-workspace text-primary me-1"></i>Select Labour
              </label>
              <div class="position-relative">
                <input type="text"
                       class="form-control form-control-sm"
                       id="labourSearch"
                       placeholder="Type to search labour..."
                       autocomplete="off">
                <select class="form-select d-none" id="labourCode">
                  <option value="">Select Labour</option>
                  @foreach($labours as $l)
                    <option value="{{ $l->labourCode }}"
                            data-name="{{ $l->labourName }}"
                            data-category="{{ $l->labourCatagory }}"
                            data-balance="{{ $l->balance }}">
                      {{ $l->labourCode }} — {{ $l->labourName }} ({{ $l->labourCatagory }})
                    </option>
                  @endforeach
                </select>
                <div id="labourDropdown" class="dropdown-menu w-100" style="display: none;"></div>
              </div>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-semibold mb-1">
                <i class="bi bi-person-badge text-success me-1"></i>Labour Name
              </label>
              <input class="form-control form-control-sm" id="labourName" type="text" placeholder="Auto-filled" disabled>
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-semibold mb-1">
                <i class="bi bi-calendar-range text-info me-1"></i>From Date
              </label>
              <input class="form-control form-control-sm" type="date" id="fromDate">
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-semibold mb-1">
                <i class="bi bi-calendar-check text-info me-1"></i>To Date
              </label>
              <input class="form-control form-control-sm" type="date" id="toDate" value="{{ now()->format('Y-m-d') }}">
            </div>
          </div>

          <div class="row g-2 align-items-end">
            <div class="col-md-3">
              <label class="form-label small fw-semibold mb-1">
                <i class="bi bi-wallet2 text-warning me-1"></i>Running Balance (period)
              </label>
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-light">₨</span>
                <input class="form-control fw-bold" id="runningBalance" type="text" value="0.00" disabled>
              </div>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-semibold mb-1">
                <i class="bi bi-piggy-bank text-secondary me-1"></i>Stored balance (master)
              </label>
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-light">₨</span>
                <input class="form-control" id="masterBalance" type="text" value="0.00" disabled title="From labour record (not filtered by date)">
              </div>
            </div>
            <div class="col-md-6 d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-secondary btn-sm" id="btnPrint">
                <i class="bi bi-printer me-1"></i>Print
              </button>
              <a href="{{ route('labour.account.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-left-right me-1"></i>Transaction
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm" id="ledgerCard">
        <div class="card-header ledger-card-head d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 no-print">
          <h5 class="mb-0"><i class="bi bi-table me-2"></i>Ledger Entries</h5>
          <span class="badge bg-light text-dark" id="entryCount">0 entries</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0 table-sm" id="ledgerTable" style="font-size: 0.875rem;">
              <thead class="table-primary" id="ledgerTableHead">
                <tr>
                  <th style="width: 100px;"><i class="bi bi-calendar3 me-1"></i>Date</th>
                  <th><i class="bi bi-file-text me-1"></i>Description</th>
                  <th class="text-end" style="width: 120px;"><i class="bi bi-arrow-down-circle text-success me-1"></i>Credit</th>
                  <th class="text-end" style="width: 120px;"><i class="bi bi-arrow-up-circle text-danger me-1"></i>Debit</th>
                  <th class="text-end" style="width: 120px;"><i class="bi bi-cash-stack me-1"></i>Balance</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="5" class="text-center text-muted py-5">
                    <i class="bi bi-inbox display-4 d-block mb-2 text-secondary"></i>
                    <p class="mb-0">Select a labour to view ledger entries</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer bg-light py-2" id="summaryFooter" style="display:none;">
          <div class="row text-center" style="margin: 0;">
            <div class="col-md-4" style="padding: 8px 5px;">
              <div class="p-1">
                <small class="text-muted d-block" style="font-size: 0.75rem;">Total Credit</small>
                <h6 class="mb-0 text-success" id="totalCredit">0.00</h6>
              </div>
            </div>
            <div class="col-md-4 border-start border-end" style="padding: 8px 5px;">
              <div class="p-1">
                <small class="text-muted d-block" style="font-size: 0.75rem;">Total Debit</small>
                <h6 class="mb-0 text-danger" id="totalDebit">0.00</h6>
              </div>
            </div>
            <div class="col-md-4" style="padding: 8px 5px;">
              <div class="p-1">
                <small class="text-muted d-block" style="font-size: 0.75rem;">Net (Debit − Credit)</small>
                <h6 class="mb-0 text-primary" id="netBalance">0.00</h6>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
  $(function(){
    const $searchInput = $('#labourSearch');
    const $labourSelect = $('#labourCode');
    const $dropdown = $('#labourDropdown');
    let allLabours = [];

    $labourSelect.find('option').each(function() {
      if ($(this).val()) {
        allLabours.push({
          code: $(this).val(),
          name: $(this).data('name'),
          category: $(this).data('category'),
          balance: $(this).data('balance'),
          display: $(this).text()
        });
      }
    });

    function filterLabours(term) {
      const t = term.toLowerCase().trim();
      let filtered = allLabours;
      if (t) {
        filtered = filtered.filter(l =>
          (l.name && l.name.toLowerCase().includes(t)) ||
          (l.code && l.code.toLowerCase().includes(t)) ||
          (l.category && String(l.category).toLowerCase().includes(t))
        );
      }
      if (filtered.length === 0) {
        $dropdown.html('<div class="dropdown-item text-muted">No labour found</div>').show();
        return;
      }
      let html = '';
      filtered.slice(0, 20).forEach((l, idx) => {
        html += `<a class="dropdown-item ${idx===0?'active':''}" href="javascript:void(0);" data-code="${l.code}">
          <strong>${l.name}</strong>
          <div class="text-muted">Code: ${l.code} | ${l.category}</div>
        </a>`;
      });
      $dropdown.html(html).show();
    }

    $searchInput.on('input', function() { filterLabours($(this).val()); });
    $searchInput.on('focus', function() { filterLabours($(this).val()); });

    $dropdown.on('click', '.dropdown-item', function(e) {
      e.preventDefault();
      const code = $(this).data('code');
      const labour = allLabours.find(l => l.code === code);
      $labourSelect.val(code).trigger('change');
      $searchInput.val(labour ? labour.name : '');
      $dropdown.hide();
    });

    $searchInput.on('keydown', function(e) {
      const $items = $dropdown.find('.dropdown-item');
      const $active = $items.filter('.active');
      let $next;
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        $next = $active.nextAll('.dropdown-item').eq(0);
        if ($next.length) { $items.removeClass('active'); $next.addClass('active'); }
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        $next = $active.prevAll('.dropdown-item').eq(0);
        if ($next.length) { $items.removeClass('active'); $next.addClass('active'); }
      } else if (e.key === 'Enter') {
        e.preventDefault();
        if ($active.length) $active.click();
      }
    });

    $(document).on('click', function(e) {
      if (!$(e.target).closest('.position-relative').length) $dropdown.hide();
    });

    function fmt(n) {
      return (Number(n || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fmtOrDash(n) {
      if (n === null || n === undefined || n === '') return '-';
      return fmt(n);
    }

    function escapeHtml(str) {
      return String(str ?? '').replace(/[&<>"']/g, function (ch) {
        return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
      });
    }

    function categoryKind(cat) {
      const c = String(cat || '').toLowerCase().trim();
      if (c === 'ajent' || c === 'agent') return 'ajent';
      if (c.indexOf('fitter') !== -1) return 'fitter';
      return 'worker';
    }

    function setLedgerHeaders(kind) {
      let cols = '';
      if (kind === 'ajent') {
        cols = `
          <th style="width:100px;">Date</th>
          <th>Description</th>
          <th>Bank</th>
          <th class="text-end">Market Bal.</th>
          <th class="text-end">Recovery</th>
          <th class="text-end">Comm%</th>
          <th class="text-end">Rec%</th>
          <th class="text-center">Advance</th>
          <th class="text-end">Credit</th>
          <th class="text-end">Debit</th>
          <th class="text-end">Balance</th>`;
      } else if (kind === 'fitter') {
        cols = `
          <th style="width:100px;">Date</th>
          <th>Txn No</th>
          <th>Type</th>
          <th>Product / Description</th>
          <th>Work</th>
          <th class="text-end">Qty</th>
          <th class="text-end">Rate</th>
          <th class="text-end">Credit</th>
          <th class="text-end">Debit</th>
          <th class="text-end">Balance</th>`;
      } else {
        cols = `
          <th style="width:100px;">Date</th>
          <th>Description</th>
          <th>Bank</th>
          <th class="text-center">Advance</th>
          <th class="text-end">Credit</th>
          <th class="text-end">Debit</th>
          <th class="text-end">Balance</th>`;
      }
      $('#ledgerTableHead').html('<tr>' + cols + '</tr>');
      return kind === 'ajent' ? 11 : (kind === 'fitter' ? 10 : 7);
    }

    function renderLedger(entries, kind) {
      kind = kind || 'worker';
      const colCount = setLedgerHeaders(kind);
      const $tb = $('#ledgerTable tbody').empty();
      if (!entries || !entries.length) {
        $tb.append(`
          <tr>
            <td colspan="${colCount}" class="text-center text-muted py-5">
              <i class="bi bi-inbox display-4 d-block mb-2 text-secondary"></i>
              <p class="mb-0">No ledger entries in this period</p>
            </td>
          </tr>
        `);
        $('#runningBalance').val('0.00');
        $('#entryCount').text('0 entries');
        $('#summaryFooter').hide();
        return;
      }

      let totalCredit = 0, totalDebit = 0;
      entries.forEach(e => {
        totalCredit += parseFloat(e.credit || 0);
        totalDebit += parseFloat(e.debit || 0);
        let rowHtml = '';
        if (kind === 'ajent') {
          rowHtml = `
            <tr>
              <td><span class="badge bg-light text-dark">${escapeHtml(e.date || '')}</span></td>
              <td>${escapeHtml(e.description || '')}</td>
              <td>${escapeHtml(e.bankAccount || '-')}</td>
              <td class="text-end">${fmtOrDash(e.marketBalance)}</td>
              <td class="text-end">${fmtOrDash(e.recovery)}</td>
              <td class="text-end">${fmtOrDash(e.commissionPercent)}</td>
              <td class="text-end">${fmtOrDash(e.recoveryCommPercent)}</td>
              <td class="text-center">${e.isAdvance ? 'Yes' : 'No'}</td>
              <td class="text-end text-success fw-semibold">${fmt(e.credit)}</td>
              <td class="text-end text-danger fw-semibold">${fmt(e.debit)}</td>
              <td class="text-end fw-bold">${fmt(e.balance)}</td>
            </tr>`;
        } else if (kind === 'fitter') {
          const isCash = String(e.txnType || '').toLowerCase() === 'cash';
          rowHtml = `
            <tr>
              <td><span class="badge bg-light text-dark">${escapeHtml(e.date || '')}</span></td>
              <td>${escapeHtml(e.txnNo || '-')}</td>
              <td>${escapeHtml(e.txnType || '-')}</td>
              <td>${escapeHtml(e.productName || e.description || '')}</td>
              <td>${escapeHtml(isCash ? '-' : (e.workName || '-'))}</td>
              <td class="text-end">${isCash ? '-' : fmtOrDash(e.qty)}</td>
              <td class="text-end">${isCash ? '-' : fmtOrDash(e.rate)}</td>
              <td class="text-end text-success fw-semibold">${fmt(e.credit)}</td>
              <td class="text-end text-danger fw-semibold">${fmt(e.debit)}</td>
              <td class="text-end fw-bold">${fmt(e.balance)}</td>
            </tr>`;
        } else {
          rowHtml = `
            <tr>
              <td><span class="badge bg-light text-dark">${escapeHtml(e.date || '')}</span></td>
              <td>${escapeHtml(e.description || '')}</td>
              <td>${escapeHtml(e.bankAccount || '-')}</td>
              <td class="text-center">${e.isAdvance ? 'Yes' : 'No'}</td>
              <td class="text-end text-success fw-semibold">${fmt(e.credit)}</td>
              <td class="text-end text-danger fw-semibold">${fmt(e.debit)}</td>
              <td class="text-end fw-bold">${fmt(e.balance)}</td>
            </tr>`;
        }
        $tb.append(rowHtml);
      });

      const last = entries[entries.length - 1]?.balance || 0;
      $('#runningBalance').val(fmt(last));
      $('#entryCount').text(`${entries.length} ${entries.length === 1 ? 'entry' : 'entries'}`);
      $('#totalCredit').text(fmt(totalCredit));
      $('#totalDebit').text(fmt(totalDebit));
      $('#netBalance').text(fmt(last));
      $('#summaryFooter').fadeIn();
    }

    function loadLedger() {
      const code = $('#labourCode').val();
      const $opt = $('#labourCode').find('option:selected');
      const name = $opt.data('name') || '';
      const category = $opt.data('category') || '';
      const masterBal = $opt.data('balance');
      $('#labourName').val(name);
      $('#masterBalance').val(fmt(masterBal));
      if (!code) return;

      const from = $('#fromDate').val();
      const to = $('#toDate').val();
      const qs = $.param({ from, to });
      const url = `{{ url('/labour-ledger') }}/${encodeURIComponent(code)}?${qs}`;
      $.get(url)
        .done(res => {
          const kind = res.category || categoryKind(res.labourCatagory || category);
          renderLedger(res.entries || [], kind);
        })
        .fail(() => {
          if (typeof showToastError === 'function') {
            showToastError('Error', 'Failed to load labour ledger');
          } else {
            alert('Failed to load labour ledger');
          }
        });
    }

    $('#labourCode').on('change', loadLedger);
    $('#fromDate, #toDate').on('change', function() {
      if ($('#labourCode').val()) loadLedger();
    });

    $('#btnPrint').on('click', function(e) {
      e.preventDefault();
      const code = $('#labourCode').val();
      if (!code) {
        if (typeof showToastError === 'function') {
          showToastError('Error', 'Please select a labour first');
        } else {
          alert('Please select a labour first');
        }
        return;
      }
      const from = $('#fromDate').val();
      const to = $('#toDate').val();
      const qs = $.param({ from, to });
      const url = `{{ url('/labour-ledger/preview') }}/${encodeURIComponent(code)}?${qs}`;
      window.open(url, '_blank');
    });
  });
</script>

@endsection
