@extends('layout.app')

@section('title', 'Bank Ledger')

@section('content')

@include('components.toast-notification')

<style>
    .bank-card-head {
        background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
        color: #fff;
    }
    #bankLedgerDropdown.dropdown-menu {
    max-height: 300px;
    overflow-y: auto;
        width: 100%;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-top: 4px;
  }
    #bankLedgerDropdown .dropdown-item.active,
    #bankLedgerDropdown .dropdown-item:hover {
        background: linear-gradient(135deg, #1a4a8a 0%, #0f2747 100%);
        color: #fff;
    }
    #bankLedgerDropdown .dropdown-item:hover .text-muted,
    #bankLedgerDropdown .dropdown-item.active .text-muted {
        color: rgba(255,255,255,0.9) !important;
    }
    #ledgerTable {
        font-size: 0.875rem;
    }
    #ledgerTable th,
    #ledgerTable td {
        white-space: nowrap;
        vertical-align: middle;
        padding-top: 0.35rem;
        padding-bottom: 0.35rem;
    }
    #ledgerTable td.ledger-desc {
        max-width: 28rem;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    @media print {
        .app-sidebar, .app-header, .app-content-header, #filterCard, .no-print { display: none !important; }
        #ledgerTable td.ledger-desc { max-width: none; overflow: visible; text-overflow: clip; }
  }
</style>

<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
            <div class="row align-items-center">
        <div class="col-sm-6">
                    <h3 class="mb-0"><i class="bi bi-journal-text text-primary me-2"></i>Bank Ledger</h3>
        </div>
        <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('bank.account.transaction') }}">Bank</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Ledger</li>
          </ol>
        </div>
      </div>
            <hr class="mb-0" style="border-top: 2px solid #0d6efd;">
        </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
            <div class="card shadow-sm mb-4" id="filterCard">
                <div class="card-header bank-card-head py-2">
                    <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filters</h5>
        </div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6 col-lg-4" style="max-width: 24rem;">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bi bi-bank text-primary me-1"></i>Bank
              </label>
              <div class="position-relative">
                <input type="text"
                       class="form-control form-control-sm"
                       id="bankLedgerSearch"
                       placeholder="Type to search bank..."
                       autocomplete="off">
                <select class="form-select d-none" id="bankLedgerCode">
                  <option value="">Select Bank</option>
                  @foreach($bankAccounts as $b)
                    <option value="{{ $b->bankAccount }}"
                            data-name="{{ $b->bankName }}"
                            data-branch="{{ $b->branchCode ?? '' }}">
                      {{ $b->bankName }} — {{ $b->bankAccount }}@if($b->branchCode) ({{ $b->branchCode }})@endif
                    </option>
                  @endforeach
                </select>
                                <div id="bankLedgerDropdown" class="dropdown-menu" style="display: none;"></div>
                            </div>
              </div>
            </div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3 col-lg-2">
                            <label class="form-label small fw-semibold mb-1">Bank Name</label>
              <input class="form-control form-control-sm" id="bankLedgerName" type="text" placeholder="Auto-filled" disabled>
            </div>
            <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">From Date</label>
              <input class="form-control form-control-sm" type="date" id="fromDate">
            </div>
            <div class="col-md-2">
                            <label class="form-label small fw-semibold mb-1">To Date</label>
                            <input class="form-control form-control-sm" type="date" id="toDate">
            </div>
                        <div class="col-md-3 col-lg-2">
                            <label class="form-label small fw-semibold mb-1">Running Balance</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-light">₨</span>
                <input class="form-control fw-bold" id="runningBalance" type="text" value="0.00" disabled>
              </div>
            </div>
                        <div class="col-md-auto d-flex gap-2">
                            <button type="button" class="btn btn-secondary btn-sm" id="btnPrint">
                <i class="bi bi-printer me-1"></i>Print
              </button>
                            <button type="button" class="btn btn-danger btn-sm" id="btnPdf">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
              </button>
                            <a href="{{ route('bank.account.transaction') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-arrow-left-right me-1"></i>Transaction
                            </a>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm" id="ledgerCard">
                <div class="card-header bank-card-head d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
                    <h5 class="mb-0"><i class="bi bi-table me-2"></i>Ledger Entries</h5>
                    <span class="badge bg-light text-dark" id="entryCount">0 entries</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0" id="ledgerTable">
              <thead class="table-primary">
                <tr>
                                    <th style="width: 110px;">Date</th>
                                    <th>Description</th>
                                    <th class="text-end" style="width: 130px;">Credit</th>
                                    <th class="text-end" style="width: 130px;">Debit</th>
                                    <th class="text-end" style="width: 130px;">Balance</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="5" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox display-6 d-block mb-2 text-secondary"></i>
                    <p class="mb-0">Select a bank to view ledger entries</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer bg-light py-2" id="summaryFooter" style="display:none;">
                    <div class="row text-center g-0">
                        <div class="col-md-4 py-2">
                            <small class="text-muted d-block">Total Credit</small>
                            <strong class="text-success" id="totalCredit">0.00</strong>
              </div>
                        <div class="col-md-4 py-2 border-start border-end">
                            <small class="text-muted d-block">Total Debit</small>
                            <strong class="text-danger" id="totalDebit">0.00</strong>
            </div>
                        <div class="col-md-4 py-2">
                            <small class="text-muted d-block">Closing Balance</small>
                            <strong class="text-primary" id="netBalance">0.00</strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
$(function () {
    const $searchInput = $('#bankLedgerSearch');
    const $bankSelect = $('#bankLedgerCode');
    const $dropdown = $('#bankLedgerDropdown');
    let allBanks = [];

    $bankSelect.find('option').each(function () {
      if ($(this).val()) {
        allBanks.push({
          code: $(this).val(),
          name: $(this).data('name'),
          branch: $(this).data('branch'),
          display: $(this).text()
        });
      }
    });

    function filterBanks(term) {
      const t = (term || '').toLowerCase().trim();
        if (!t) {
            $dropdown.hide().empty();
            return;
        }
        const filtered = allBanks.filter(b =>
          (b.name && b.name.toLowerCase().includes(t)) ||
          (b.branch && String(b.branch).toLowerCase().includes(t)) ||
          (b.code && String(b.code).toLowerCase().includes(t))
        );
      if (filtered.length === 0) {
        $dropdown.html('<div class="dropdown-item text-muted">No bank found</div>').show();
        return;
      }
      let html = '';
      filtered.slice(0, 20).forEach((b, idx) => {
            html += `<a class="dropdown-item ${idx === 0 ? 'active' : ''}" href="javascript:void(0);" data-code="${b.code}">
          <strong>${b.name}</strong>
                <div class="text-muted small">Code: ${b.code}${b.branch ? ' | Branch: ' + b.branch : ''}</div>
        </a>`;
      });
      $dropdown.html(html).show();
    }

    $searchInput.on('input', function () { filterBanks($(this).val()); });
    $searchInput.on('focus', function () { filterBanks($(this).val()); });

    $dropdown.on('click', '.dropdown-item', function (e) {
      e.preventDefault();
      const code = $(this).data('code');
      const bank = allBanks.find(b => b.code === code);
      $bankSelect.val(code).trigger('change');
      $searchInput.val(bank ? bank.name : '');
      $dropdown.hide();
    });

    $searchInput.on('keydown', function (e) {
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

    $(document).on('click', function (e) {
      if (!$(e.target).closest('#bankLedgerSearch, #bankLedgerDropdown').length) $dropdown.hide();
    });

    function fmt(n) {
      return (Number(n || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function renderLedger(entries) {
      const $tb = $('#ledgerTable tbody').empty();
      if (!entries || !entries.length) {
        $tb.append(`
          <tr>
            <td colspan="5" class="text-center text-muted py-5">
                        <i class="bi bi-inbox display-6 d-block mb-2 text-secondary"></i>
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
            const desc = String(e.description ?? '').replace(/\s+/g, ' ').trim();
        $tb.append(`
          <tr>
                    <td>${e.date}</td>
                    <td class="ledger-desc" title="${desc.replace(/"/g, '&quot;')}">${desc}</td>
                    <td class="text-end">${parseFloat(e.credit || 0) > 0 ? fmt(e.credit) : '-'}</td>
                    <td class="text-end">${parseFloat(e.debit || 0) > 0 ? fmt(e.debit) : '-'}</td>
                    <td class="text-end fw-semibold">${fmt(e.balance)}</td>
          </tr>
        `);
      });

      const last = entries[entries.length - 1]?.balance || 0;
      $('#runningBalance').val(fmt(last));
      $('#entryCount').text(`${entries.length} ${entries.length === 1 ? 'entry' : 'entries'}`);
      $('#totalCredit').text(fmt(totalCredit));
      $('#totalDebit').text(fmt(totalDebit));
      $('#netBalance').text(fmt(last));
        $('#summaryFooter').show();
    }

    function loadLedger() {
      const code = $('#bankLedgerCode').val();
      const $opt = $('#bankLedgerCode').find('option:selected');
      const name = $opt.data('name') || '';
      $('#bankLedgerName').val(name);
      if (!code) return;

      const from = $('#fromDate').val();
      const to = $('#toDate').val();
      const qs = $.param({ from, to });
      const url = `{{ url('/bank-ledger') }}/${encodeURIComponent(code)}?${qs}`;
      $.get(url)
        .done(res => renderLedger(res.entries))
        .fail(() => {
          if (typeof showToastError === 'function') {
            showToastError('Error', 'Failed to load bank ledger');
          } else {
            alert('Failed to load bank ledger');
          }
        });
    }

    $('#bankLedgerCode').on('change', loadLedger);
    $('#fromDate, #toDate').on('change', function () {
      if ($('#bankLedgerCode').val()) loadLedger();
    });

    function bankLedgerPreviewUrl(downloadPdf) {
      const code = $('#bankLedgerCode').val();
      if (!code) {
        if (typeof showToastError === 'function') {
          showToastError('Error', 'Please select a bank first');
        } else {
          alert('Please select a bank first');
        }
        return null;
      }
      const from = $('#fromDate').val();
      const to = $('#toDate').val();
      const qs = $.param({ from, to, ...(downloadPdf ? { download_pdf: 1 } : {}) });
      return `{{ url('/bank-ledger/preview') }}/${encodeURIComponent(code)}?${qs}`;
    }

    $('#btnPrint').on('click', function (e) {
      e.preventDefault();
      const url = bankLedgerPreviewUrl(false);
      if (url) window.open(url, '_blank');
    });

    $('#btnPdf').on('click', function (e) {
      e.preventDefault();
      const url = bankLedgerPreviewUrl(true);
      if (url) window.open(url, '_blank');
    });
  });
</script>

@endsection
