@extends('layout.app')

@section('title', 'Supplier Ledger')

@section('content')

    @php
        $balanceLabel = $balanceLabel ?? ($summary['balance_label'] ?? 'Payable Balance');
    @endphp

@include('components.toast-notification')

<style>
  /* Supplier list dropdown — same style as Customer Ledger Report */
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
  }
  .position-relative .dropdown-menu::-webkit-scrollbar { width: 6px; }
  .position-relative .dropdown-menu::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }
  .position-relative .dropdown-menu::-webkit-scrollbar-thumb {
    background: #667eea;
    border-radius: 10px;
  }
  .position-relative .dropdown-menu::-webkit-scrollbar-thumb:hover {
    background: #764ba2;
  }

  .ledger-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
  }
  #supplierLedgerTable {
    font-size: 0.875rem;
  }
  #supplierLedgerTable th,
  #supplierLedgerTable td {
    white-space: nowrap;
    vertical-align: middle;
    padding-top: 0.35rem;
    padding-bottom: 0.35rem;
  }
  #supplierLedgerTable td.ledger-desc {
    max-width: 18rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  #supplierLedgerTable .text-end { font-variant-numeric: tabular-nums; }
  .date-dmy-group .date-dmy-picker {
    max-width: 2.4rem;
    padding-left: 0.2rem;
    padding-right: 0.2rem;
    flex: 0 0 2.4rem;
  }
  @media print {
    .app-sidebar, .app-header, .app-content-header, #filterCard, .no-print { display: none !important; }
    #supplierLedgerTable td.ledger-desc { max-width: none; overflow: visible; text-overflow: clip; }
  }
</style>

<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-6">
          <h3 class="mb-0"><i class="bi bi-journal-text text-primary me-2"></i>Supplier Ledger</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('accountTransaction.index') }}">Accounts</a></li>
            <li class="breadcrumb-item active" aria-current="page">Supplier Ledger</li>
          </ol>
        </div>
      </div>
      <hr class="mb-0" style="border-top: 2px solid #0d6efd;">
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
    @if (!empty($pdfError))
        <div class="alert alert-danger no-print py-2">
            <i class="bi bi-exclamation-triangle me-1"></i>{{ $pdfError }}
        </div>
    @endif

    @if (isset($pdfAvailable) && ! $pdfAvailable)
        <div class="alert alert-warning no-print py-2">
            <i class="bi bi-info-circle me-1"></i>PDF download is disabled because the dompdf package is not installed.
        </div>
    @endif

      <div class="card shadow-sm mb-4" id="filterCard">
        <div class="card-header ledger-card-head py-2">
          <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filters</h5>
        </div>
        <div class="card-body">
          <form method="GET" action="{{ route('supplier.ledger.generate') }}" id="supplierLedgerForm">
            <div class="row g-2 mb-3">
              <div class="col-md-6 col-lg-4" style="max-width: 24rem;">
                <label class="form-label small fw-semibold mb-1">
                  <i class="bi bi-person-badge text-primary me-1"></i>Select Supplier
                </label>
                <div class="position-relative">
                  <input type="text"
                         class="form-control form-control-sm"
                         id="supplierSearch"
                         placeholder="Type to search supplier..."
                         autocomplete="off"
                         value="{{ $selectedSupplierName ?? '' }}">
                  <select name="supplier_code" class="form-select d-none" id="supplierCodeSelect" required>
                            <option value="">Select supplier…</option>
                            @foreach ($suppliers as $supplier)
                                <option
                                    value="{{ $supplier->supplierCode }}"
                        data-name="{{ $supplier->supplierName }}"
                        data-area="{{ $supplier->supplierArea ?? '' }}"
                        data-city="{{ $supplier->supplierCity ?? '' }}"
                                    @selected(($selectedSupplierCode ?? '') === $supplier->supplierCode)
                                >
                                    {{ $supplier->supplierName }} ({{ $supplier->supplierCode }})
                                </option>
                            @endforeach
                        </select>
                  <div id="supplierDropdown" class="dropdown-menu w-100" style="display: none;"></div>
                </div>
              </div>
                    </div>

            <div class="row g-2 align-items-end">
                    <div class="col-md-3 col-lg-2">
                        <label class="form-label small fw-semibold mb-1">From Date</label>
                <div class="input-group input-group-sm date-dmy-group">
                  <input type="text" class="form-control form-control-sm" id="fromDateDisplay"
                         placeholder="DD/MM/YYYY" maxlength="10" autocomplete="off"
                         value="{{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }}">
                  <input type="date" class="form-control form-control-sm date-dmy-picker" id="fromDatePicker"
                         value="{{ $fromDate }}" title="Pick date">
                  <input type="hidden" name="from_date" id="fromDateIso" value="{{ $fromDate }}">
                </div>
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <label class="form-label small fw-semibold mb-1">To Date</label>
                <div class="input-group input-group-sm date-dmy-group">
                  <input type="text" class="form-control form-control-sm" id="toDateDisplay"
                         placeholder="DD/MM/YYYY" maxlength="10" autocomplete="off"
                         value="{{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}">
                  <input type="date" class="form-control form-control-sm date-dmy-picker" id="toDatePicker"
                         value="{{ $toDate }}" title="Pick date">
                  <input type="hidden" name="to_date" id="toDateIso" value="{{ $toDate }}">
                </div>
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <label class="form-label small fw-semibold mb-1">Transaction Type</label>
                <select name="transaction_type" id="transactionType" class="form-select form-select-sm">
                  @foreach (['all' => 'All', 'purchase' => 'Purchase', 'sale' => 'Sale', 'payment' => 'Payment', 'discount' => 'Discount', 'transport' => 'Transport'] as $val => $label)
                                <option value="{{ $val }}" @selected(($transactionType ?? 'all') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-lg-2">
                <label class="form-label small fw-semibold mb-1" id="supplierBalanceLabel">{{ $balanceLabel }}</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text bg-light">₨</span>
                  <input class="form-control fw-bold" id="supplierBalanceValue" type="text" value="0.00" disabled>
                </div>
              </div>
              <div class="col-md-auto d-flex gap-2">
                <button type="button" class="btn btn-secondary btn-sm" id="btnSupplierLedgerPrint" style="display:none;">
                  <i class="bi bi-printer me-1"></i>Print
                </button>
                @if (!empty($pdfAvailable))
                  <a href="#" class="btn btn-outline-danger btn-sm" id="btnSupplierLedgerPdf" style="display:none;">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                  </a>
                @endif
              </div>
                </div>
            </form>
        </div>
    </div>

      <div class="card shadow-sm" id="ledgerCard">
        <div class="card-header ledger-card-head d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 no-print">
          <h5 class="mb-0"><i class="bi bi-table me-2"></i>Ledger Entries</h5>
          <span class="badge bg-light text-dark" id="ledgerMetaBadge">0 entries</span>
            </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0" id="supplierLedgerTable">
              <thead class="table-primary">
                <tr>
                  <th style="width: 100px;">Date</th>
                  <th style="width: 110px;">Invoice No</th>
                  <th style="width: 100px;">Type</th>
                            <th>Product</th>
                  <th class="text-end" style="width: 80px;">Qty</th>
                  <th class="text-end" style="width: 90px;">Rate</th>
                  <th class="text-end" style="width: 110px;">Debit</th>
                  <th class="text-end" style="width: 110px;">Credit</th>
                  <th class="text-end" style="width: 120px;" id="balanceColHead">{{ $balanceLabel }}</th>
                        </tr>
                    </thead>
              <tbody id="supplierLedgerTbody">
                <tr>
                  <td colspan="9" class="text-muted py-4 px-3">Select a supplier and click Generate to view ledger entries</td>
                            </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light py-2" id="ledgerSummaryFooter" style="display:none;">
          <div class="row text-center g-0">
            <div class="col-md-4 py-2">
              <small class="text-muted d-block">Total Debit</small>
              <strong class="text-danger" id="sumTotalDebit">0.00</strong>
            </div>
            <div class="col-md-4 py-2 border-start border-end">
              <small class="text-muted d-block">Total Credit</small>
              <strong class="text-success" id="sumTotalCredit">0.00</strong>
            </div>
            <div class="col-md-4 py-2">
              <small class="text-muted d-block" id="sumBalanceLabel">{{ $balanceLabel }}</small>
              <strong class="text-danger" id="sumClosingBalance">0.00</strong>
            </div>
            </div>
        </div>
      </div>
    </div>
</div>
</main>
@endsection

@push('scripts')
<script>
(function () {
    const balanceUrlBase = @json(url('/supplier-ledger/balance'));
  const $searchInput = $('#supplierSearch');
  const $supplierSelect = $('#supplierCodeSelect');
  const $dropdown = $('#supplierDropdown');
  const $balanceValue = $('#supplierBalanceValue');
  const $balanceLabel = $('#supplierBalanceLabel');
  let allSuppliers = [];

  $supplierSelect.find('option').each(function () {
    if ($(this).val()) {
      allSuppliers.push({
        code: $(this).val(),
        name: ($(this).data('name') || $(this).text() || '').toString(),
        area: ($(this).data('area') || '').toString(),
        city: ($(this).data('city') || '').toString(),
        display: $(this).text()
      });
    }
  });

  function filterSuppliers(searchTerm) {
    const term = (searchTerm || '').toLowerCase().trim();
    let filtered = allSuppliers;
    if (term) {
      filtered = filtered.filter(function (s) {
        return s.name.toLowerCase().includes(term)
          || s.code.toLowerCase().includes(term)
          || s.area.toLowerCase().includes(term)
          || s.city.toLowerCase().includes(term);
      });
    }
    if (filtered.length === 0) {
      $dropdown.html('<div class="dropdown-item text-muted">No suppliers found</div>').show();
      return;
    }
    let html = '';
    filtered.slice(0, 15).forEach(function (s, idx) {
      html += '<a class="dropdown-item ' + (idx === 0 ? 'active' : '') + '" href="javascript:void(0);" data-code="' + s.code + '">' +
        '<strong>' + s.name + '</strong>' +
        '<div class="text-muted">Code: ' + s.code + ' | Area: ' + (s.area || '—') + ' | City: ' + (s.city || '—') + '</div>' +
        '</a>';
    });
    $dropdown.html(html).show();
  }

  function selectSupplier(code) {
    const supplier = allSuppliers.find(function (s) { return s.code === code; });
    if (!supplier) return;
    $supplierSelect.val(code);
    $searchInput.val(supplier.name);
    $dropdown.hide();
    loadSupplierBalance(code);
    loadLedger();
  }

  function applyBalanceStyle(balanceType) {
    const isPayable = balanceType === 'payable';
    const colorClass = isPayable ? 'text-danger' : 'text-success';
    $balanceLabel
      .removeClass('text-danger text-success text-muted')
      .addClass(colorClass)
      .text(isPayable ? 'Payable Balance' : 'Receivable Balance');
    $balanceValue
      .removeClass('text-danger text-success')
      .addClass(colorClass);
  }

    async function loadSupplierBalance(code) {
    if (!code) {
      $balanceValue.val('0.00').removeClass('text-danger text-success');
      $balanceLabel.removeClass('text-danger text-success').addClass('text-muted').text('Payable Balance');
      return;
    }
    try {
      const res = await fetch(balanceUrlBase + '/' + encodeURIComponent(code), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            });
      if (!res.ok) return;
            const data = await res.json();
      const bal = Number(data.balance ?? 0);
      $balanceValue.val(bal.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
      }));
      applyBalanceStyle(data.balance_type || (bal > 0 ? 'payable' : 'receivable'));
        } catch (e) {
      // ignore
    }
  }

  $searchInput.on('input', function () {
    filterSuppliers($(this).val());
  });

  $searchInput.on('focus', function () {
    filterSuppliers($(this).val());
  });

  $dropdown.on('click', '.dropdown-item[data-code]', function (e) {
    e.preventDefault();
    selectSupplier($(this).data('code'));
  });

  $searchInput.on('keydown', function (e) {
    const $items = $dropdown.find('.dropdown-item[data-code]');
    const $active = $items.filter('.active');
    let $next;
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      if (!$dropdown.is(':visible')) filterSuppliers($(this).val());
      $next = $active.nextAll('.dropdown-item[data-code]').eq(0);
      if ($next.length) {
        $items.removeClass('active');
        $next.addClass('active');
      }
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      $next = $active.prevAll('.dropdown-item[data-code]').eq(0);
      if ($next.length) {
        $items.removeClass('active');
        $next.addClass('active');
      }
    } else if (e.key === 'Escape') {
      $dropdown.hide();
    } else if (e.key === 'Enter') {
      e.preventDefault();
      if ($active.length && $dropdown.is(':visible')) {
        $active.click();
      }
    }
  });

  $(document).on('click', function (e) {
    if (!$(e.target).closest('#supplierSearch, #supplierDropdown').length) {
      $dropdown.hide();
    }
  });

  const typeLabels = {
    purchase: 'Purchase',
    sale: 'Sale',
    payment: 'Payment',
    discount: 'Discount',
    transport: 'Transport',
    other: 'Other',
    Opening: 'Opening'
  };
  const pdfAvailable = @json(!empty($pdfAvailable));
  let lastGenerated = null;

  function fmtNum(value) {
    if (value === null || value === undefined || value === '') return '—';
    const n = Number(value);
    if (!isFinite(n)) return '—';
    return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  /** Display: Date/Month/Year (DD/MM/YYYY) */
  function fmtDate(value) {
    if (!value) return '—';
    const s = String(value).trim();
    const m = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (m) return m[3] + '/' + m[2] + '/' + m[1];
    const dmy = s.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
    if (dmy) {
      return String(dmy[1]).padStart(2, '0') + '/' + String(dmy[2]).padStart(2, '0') + '/' + dmy[3];
    }
    const d = new Date(s);
    if (!isNaN(d.getTime())) {
      const dd = String(d.getDate()).padStart(2, '0');
      const mm = String(d.getMonth() + 1).padStart(2, '0');
      const yyyy = d.getFullYear();
      return dd + '/' + mm + '/' + yyyy;
    }
    return s;
  }

  /** Parse DD/MM/YYYY → YYYY-MM-DD (or null) */
  function parseDmyToIso(value) {
    const s = String(value || '').trim();
    const m = s.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
    if (!m) return null;
    const dd = parseInt(m[1], 10);
    const mm = parseInt(m[2], 10);
    const yyyy = parseInt(m[3], 10);
    if (mm < 1 || mm > 12 || dd < 1 || dd > 31) return null;
    const dt = new Date(yyyy, mm - 1, dd);
    if (dt.getFullYear() !== yyyy || dt.getMonth() !== mm - 1 || dt.getDate() !== dd) return null;
    return yyyy + '-' + String(mm).padStart(2, '0') + '-' + String(dd).padStart(2, '0');
  }

  function syncDatePair(displayId, pickerId, isoId) {
    const $display = $(displayId);
    const $picker = $(pickerId);
    const $iso = $(isoId);

    $picker.on('change', function () {
      const iso = $(this).val();
      if (!iso) return;
      $iso.val(iso);
      $display.val(fmtDate(iso));
      loadLedger();
    });

    $display.on('change blur', function () {
      const iso = parseDmyToIso($(this).val());
      if (!iso) {
        // restore from iso if invalid
        const cur = $iso.val();
        if (cur) $display.val(fmtDate(cur));
                return;
            }
      $iso.val(iso);
      $picker.val(iso);
      $display.val(fmtDate(iso));
      loadLedger();
    });
  }

  syncDatePair('#fromDateDisplay', '#fromDatePicker', '#fromDateIso');
  syncDatePair('#toDateDisplay', '#toDatePicker', '#toDateIso');

  $('#transactionType').on('change', function () {
    loadLedger();
  });

  function typeBadge(typeKey) {
    const label = typeLabels[typeKey] || (typeKey ? String(typeKey).charAt(0).toUpperCase() + String(typeKey).slice(1) : '—');
    let cls = 'text-bg-secondary';
    if (typeKey === 'purchase') cls = 'text-bg-danger';
    else if (typeKey === 'sale') cls = 'text-bg-success';
    else if (typeKey === 'payment') cls = 'text-bg-primary';
    else if (typeKey === 'discount') cls = 'text-bg-info';
    else if (typeKey === 'transport') cls = 'text-bg-warning';
    return '<span class="badge ' + cls + '">' + label + '</span>';
  }

  function renderLedger(data) {
    const rows = Array.isArray(data.rows) ? data.rows : [];
    const $tb = $('#supplierLedgerTbody').empty();
    const balLabel = data.balance_label || 'Payable Balance';

    $('#balanceColHead').text(balLabel);
    $('#sumBalanceLabel').text(balLabel);

    if (!rows.length) {
      $tb.html('<tr><td colspan="9" class="text-muted py-4 px-3">No transactions in the selected period.</td></tr>');
    } else {
      rows.forEach(function (row) {
        const typeKey = row.transaction_type || '';
        const product = row.product_name || '—';
        const openingClass = row.is_opening ? ' class="table-warning fw-semibold"' : '';
        const debit = Number(row.debit || 0) > 0 ? fmtNum(row.debit) : '—';
        const credit = Number(row.credit || 0) > 0 ? fmtNum(row.credit) : '—';
        const rate = row.final_rate != null ? row.final_rate : row.rate;
        $tb.append(
          '<tr' + openingClass + '>' +
            '<td>' + fmtDate(row.date) + '</td>' +
            '<td class="font-monospace small">' + (row.invoice_no || '—') + '</td>' +
            '<td>' + typeBadge(typeKey) + '</td>' +
            '<td class="ledger-desc" title="' + String(product).replace(/"/g, '&quot;') + '">' + product + '</td>' +
            '<td class="text-end">' + fmtNum(row.qty) + '</td>' +
            '<td class="text-end">' + fmtNum(rate) + '</td>' +
            '<td class="text-end text-danger">' + debit + '</td>' +
            '<td class="text-end text-success">' + credit + '</td>' +
            '<td class="text-end fw-semibold text-danger">' + fmtNum(row.running_balance) + '</td>' +
          '</tr>'
        );
      });
    }

    const summary = data.summary || {};
    $('#sumTotalDebit').text(fmtNum(summary.total_debit || 0));
    $('#sumTotalCredit').text(fmtNum(summary.total_credit || 0));
    $('#sumClosingBalance').text(fmtNum(summary.closing_balance || 0));
    $('#ledgerSummaryFooter').show();

    $('#ledgerMetaBadge').text(
      (data.supplier_name || '') + ' (' + (data.supplier_code || '') + ') · ' +
      fmtDate(data.from_date) + ' → ' + fmtDate(data.to_date)
    );

    lastGenerated = {
      supplier_code: data.supplier_code,
      from_date: data.from_date,
      to_date: data.to_date,
      transaction_type: data.transaction_type || 'all'
    };

    $('#btnSupplierLedgerPrint').show();
    if (pdfAvailable) {
      const pdfParams = new URLSearchParams({
        supplier_code: lastGenerated.supplier_code,
        from_date: lastGenerated.from_date,
        to_date: lastGenerated.to_date,
        transaction_type: lastGenerated.transaction_type,
        download_pdf: '1'
      });
      $('#btnSupplierLedgerPdf').attr('href', @json(route('supplier.ledger.generate')) + '?' + pdfParams.toString()).show();
    }
  }

  let loadTimer = null;
  function loadLedger() {
    const code = $supplierSelect.val();
    if (!code) return;
    const fromDate = $('#fromDateIso').val();
    const toDate = $('#toDateIso').val();
    const txnType = $('#transactionType').val() || 'all';
    if (!fromDate || !toDate) return;

    clearTimeout(loadTimer);
    loadTimer = setTimeout(function () {
      $.ajax({
        url: @json(route('supplier.ledger.generate')),
        method: 'GET',
        data: {
          supplier_code: code,
          from_date: fromDate,
          to_date: toDate,
          transaction_type: txnType,
          ajax: 1
        },
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      })
        .done(function (res) {
          if (!res || res.success === false) return;
          renderLedger(res);
          loadSupplierBalance(code);
        })
        .fail(function () {
          // silent — user can retry by changing filter
        });
    }, 150);
  }

  $('#supplierLedgerForm').on('submit', function (e) {
    e.preventDefault();
    loadLedger();
  });

  if ($supplierSelect.val()) {
    const selected = allSuppliers.find(function (s) { return s.code === $supplierSelect.val(); });
    if (selected) $searchInput.val(selected.name);
    loadSupplierBalance($supplierSelect.val());
    loadLedger();
  }

  $('#btnSupplierLedgerPrint').on('click', function (e) {
    e.preventDefault();
    if (!lastGenerated || !lastGenerated.supplier_code) {
      alert('Select a supplier first.');
      return;
    }
    const params = new URLSearchParams({
      from_date: lastGenerated.from_date,
      to_date: lastGenerated.to_date,
      transaction_type: lastGenerated.transaction_type
    });
    window.open(
      `{{ url('/supplier-ledger/preview') }}/${encodeURIComponent(lastGenerated.supplier_code)}?${params}`,
      '_blank'
    );
  });
})();
</script>
@endpush
