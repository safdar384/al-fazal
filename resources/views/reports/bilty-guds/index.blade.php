@extends('layout.app')

@section('title', 'Bilty Report by Guds')

@section('content')
<style>
  :root {
    --bg-navy: #0f2747;
    --bg-blue: #1a4a8a;
  }
  .bg-page-bar {
    border-bottom: 2px solid var(--bg-navy);
    margin-bottom: 1rem;
    padding-bottom: 0.35rem;
  }
  .bg-card {
    border: 0;
    border-radius: 0.85rem;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(15, 39, 71, 0.07);
  }
  .bg-head {
    background: linear-gradient(135deg, var(--bg-navy) 0%, var(--bg-blue) 100%);
    color: #fff;
  }
  .bg-toolbar {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
  }
  .bg-toolbar .form-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #64748b;
    font-weight: 700;
    margin-bottom: 0.25rem;
  }
  .btn-scope {
    border-radius: 999px;
    font-size: 0.8rem;
    padding: 0.35rem 0.85rem;
  }
  .btn-scope.active {
    background: #fff !important;
    color: var(--bg-navy) !important;
    border-color: #fff !important;
    font-weight: 700;
    box-shadow: 0 1px 4px rgba(0,0,0,0.12);
  }
  .bg-guds-bar {
    background: var(--bg-navy);
    color: #fff;
    border-radius: 4px 4px 0 0;
  }
  .bg-data-table {
    font-size: 0.84rem;
    border: 1px solid #d5dde6;
    border-top: none;
  }
  .bg-data-table thead th {
    background: #eef2f7;
    color: var(--bg-navy);
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
    border-bottom: 2px solid #cbd5e1;
  }
  .bg-data-table tfoot td {
    background: #f3f6fa;
    border-top: 1px solid #c5d0dc;
  }
  .bg-table-wrap {
    max-height: min(58vh, 620px);
    overflow: auto;
  }
  .table-success-soft { background: #f1faf3 !important; }
  .bg-row-check,
  .bg-select-all {
    width: 1.15rem;
    height: 1.15rem;
    cursor: pointer;
  }
  .bg-selected-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: #e8f0fe;
    color: var(--bg-blue);
    border-radius: 999px;
    padding: 0.2rem 0.7rem;
    font-size: 0.78rem;
    font-weight: 600;
  }
  .bg-assign-box {
    background: #fff;
    border: 1px solid #dbe3ef;
    border-radius: 0.65rem;
    padding: 0.55rem 0.7rem;
  }
  tr.bg-row-selected { background-color: rgba(26, 74, 138, 0.08) !important; }
  .bg-empty {
    text-align: center;
    color: #5f6f7f;
    padding: 2rem 1rem;
    border: 1px dashed #d5dde6;
    border-radius: 6px;
  }
  .bg-loading {
    text-align: center;
    color: #64748b;
    padding: 2.5rem 1rem;
  }
</style>

<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center bg-page-bar">
        <div class="col-sm-6">
          <h3 class="mb-0">
            <i class="bi bi-truck text-primary me-2"></i>Bilty Report by Guds
          </h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sale.invoice') }}">Sales</a></li>
            <li class="breadcrumb-item active" aria-current="page">Bilty by Guds</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <div class="card bg-card mb-3">
        <div class="card-header bg-head py-2 px-3">
          <div class="d-flex flex-wrap align-items-center gap-2 justify-content-between">
            <div>
              <strong class="d-block" id="bgListTitle">All Bilty</strong>
              <small class="text-white-50">Select rows, then Mark Delivered once</small>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
              <button type="button" class="btn btn-sm btn-danger fw-semibold" id="btnPdfBiltyGuds" title="Download PDF">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
              </button>
              <button type="button" class="btn btn-sm btn-outline-light fw-semibold" id="btnPrintBiltyGuds" title="Print report">
                <i class="bi bi-printer me-1"></i>Print Report
              </button>
              <div class="btn-group flex-wrap" role="group" aria-label="Delivery scope">
                <button type="button" class="btn btn-sm btn-outline-light btn-scope active" data-scope="all" id="btnAllBilty">
                  All <span class="badge text-bg-light ms-1" id="cntAll">0</span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-light btn-scope" data-scope="delivered" id="btnDeliveredBilty">
                  Delivered <span class="badge text-bg-light ms-1" id="cntDelivered">0</span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-light btn-scope" data-scope="non_delivered" id="btnNonDeliveredBilty">
                  Non Delivered <span class="badge text-bg-light ms-1" id="cntNonDelivered">0</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-toolbar px-3 py-3">
          <div class="row g-2 align-items-end">
            <div class="col-6 col-lg-2">
              <label class="form-label" for="bgFromDate">From Date</label>
              <input type="date" class="form-control form-control-sm" id="bgFromDate" value="{{ $fromDate }}">
            </div>
            <div class="col-6 col-lg-2">
              <label class="form-label" for="bgToDate">To Date</label>
              <input type="date" class="form-control form-control-sm" id="bgToDate" value="{{ $toDate }}">
            </div>
            <div class="col-12 col-lg-3">
              <label class="form-label" for="bgGuds">Guds</label>
              <select class="form-select form-select-sm" id="bgGuds">
                <option value="">— All Guds —</option>
                @foreach ($gudsOptions as $name)
                  <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 col-lg-5">
              <div class="bg-assign-box">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                  <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="bgOnlyBilty" value="1" checked>
                    <label class="form-check-label small" for="bgOnlyBilty">Only bilty rows</label>
                  </div>
                  <span class="bg-selected-pill" id="bgSelectedHint">
                    <i class="bi bi-check2-square"></i> 0 selected
                  </span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                  <button type="button" class="btn btn-sm btn-success" id="btnMarkDelivered" disabled>
                    <i class="bi bi-truck me-1"></i>Mark Delivered
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-warning" id="btnUnmarkDelivered" disabled>
                    <i class="bi bi-x-circle me-1"></i>Unmark
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="btnResetBiltyFilters" title="Reset filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card-body" id="bgReportBody">
          <div class="bg-loading">
            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
            Loading bilty data…
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection

@push('scripts')
<script>
$(function () {
  const dataUrl = @json(route('reports.bilty-guds.data', [], false));
  const printUrl = @json(route('reports.bilty-guds.print', [], false));
  const bulkUrl = @json(route('reports.bilty-guds.delivered-bulk', [], false));
  const csrf = $('meta[name="csrf-token"]').attr('content') || @json(csrf_token());
  const defaultFrom = @json($fromDate);
  const defaultTo = @json($toDate);

  let currentScope = 'all';
  let loadSeq = 0;

  const titles = {
    all: 'All Bilty',
    delivered: 'Delivered Bilty',
    non_delivered: 'Non Delivered Bilty'
  };

  function filters() {
    return {
      from_date: $('#bgFromDate').val() || '',
      to_date: $('#bgToDate').val() || '',
      guds_name: $('#bgGuds').val() || '',
      only_with_bilty: $('#bgOnlyBilty').is(':checked') ? 1 : 0,
      delivery_status: currentScope
    };
  }

  function selectedChecks() {
    return $('#bgReportBody .bg-row-check:checked');
  }

  function selectedInvoiceNos(onlyDelivered) {
    const nos = [];
    selectedChecks().each(function () {
      const no = String($(this).data('invoice') || '').trim();
      if (!no) return;
      const isDelivered = String($(this).data('delivered')) === '1';
      if (onlyDelivered === true && !isDelivered) return;
      if (onlyDelivered === false && isDelivered) return;
      nos.push(no);
    });
    return nos;
  }

  function updateSelectedHint() {
    const $checks = $('#bgReportBody .bg-row-check');
    const $selected = $checks.filter(':checked');
    const n = $selected.length;
    const pendingSelected = selectedInvoiceNos(false).length; // not yet delivered
    const deliveredSelected = selectedInvoiceNos(true).length; // already delivered

    $('#bgSelectedHint').html('<i class="bi bi-check2-square"></i> ' + n + ' selected');
    // Mark Delivered only for non-delivered selected rows
    $('#btnMarkDelivered').prop('disabled', pendingSelected === 0);
    // Unmark only for already-delivered selected rows
    $('#btnUnmarkDelivered').prop('disabled', deliveredSelected === 0);

    $checks.each(function () {
      $(this).closest('tr').toggleClass('bg-row-selected', $(this).is(':checked'));
    });
    $('#bgReportBody .bg-select-all').each(function () {
      const $table = $(this).closest('table');
      const $rows = $table.find('.bg-row-check');
      const checked = $rows.filter(':checked').length;
      const all = $rows.length;
      $(this).prop('checked', all > 0 && checked === all);
      $(this).prop('indeterminate', checked > 0 && checked < all);
    });
  }

  function setScope(scope) {
    const allowed = ['all', 'delivered', 'non_delivered'];
    currentScope = allowed.indexOf(scope) >= 0 ? scope : 'all';
    $('.btn-scope').removeClass('active');
    $('.btn-scope[data-scope="' + currentScope + '"]').addClass('active');
    $('#bgListTitle').text(titles[currentScope] || 'Bilty');
    loadReport();
  }

  function updateCounts(counts) {
    counts = counts || {};
    $('#cntAll').text(counts.all ?? 0);
    $('#cntDelivered').text(counts.delivered ?? 0);
    $('#cntNonDelivered').text(counts.non_delivered ?? 0);
  }

  function loadReport() {
    const seq = ++loadSeq;
    const params = filters();

    $('#bgReportBody').html(
      '<div class="bg-loading"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Loading bilty data…</div>'
    );
    updateSelectedHint();

    $.ajax({
      url: dataUrl,
      method: 'GET',
      data: params,
      success: function (res) {
        if (seq !== loadSeq) return;
        if (!res.success) {
          $('#bgReportBody').html('<div class="bg-empty">Could not load bilty data.</div>');
          updateSelectedHint();
          return;
        }
        $('#bgReportBody').html(res.html || '<div class="bg-empty">No data.</div>');
        updateCounts(res.counts);
        updateSelectedHint();
      },
      error: function (xhr) {
        if (seq !== loadSeq) return;
        console.error('bilty-guds/data', xhr.status, xhr.responseText);
        $('#bgReportBody').html('<div class="bg-empty">Failed to load bilty data.</div>');
        updateSelectedHint();
      }
    });
  }

  function bulkDelivered(delivered) {
    // Mark: only pending; Unmark: only already delivered
    const nos = selectedInvoiceNos(delivered ? false : true);
    if (!nos.length) {
      if (typeof showError === 'function') {
        showError(
          delivered ? 'Already delivered' : 'Not delivered',
          delivered
            ? 'Selected rows are already delivered. Mark Delivered is only for pending rows.'
            : 'Selected rows are not delivered yet.'
        );
      }
      return;
    }

    const $btns = $('#btnMarkDelivered, #btnUnmarkDelivered').prop('disabled', true);

    $.ajax({
      url: bulkUrl,
      method: 'POST',
      data: {
        _token: csrf,
        delivered: delivered ? 1 : 0,
        invoice_nos: nos
      },
      success: function (res) {
        if (!res.success) {
          if (typeof showError === 'function') {
            showError('Failed', res.message || 'Could not update.');
          }
          updateSelectedHint();
          return;
        }
        if (typeof showSuccess === 'function') {
          showSuccess(
            'Saved',
            (delivered ? 'Marked delivered: ' : 'Unmarked: ') + (res.updated || nos.length) + ' invoice(s)'
          );
        }
        loadReport();
      },
      error: function (xhr) {
        if (typeof showError === 'function') {
          showError('Failed', xhr.responseJSON?.message || 'Could not update delivered status.');
        }
        updateSelectedHint();
      },
      complete: function () {
        $btns.prop('disabled', false);
        updateSelectedHint();
      }
    });
  }

  $('#btnPrintBiltyGuds').on('click', function () {
    const params = new URLSearchParams(filters());
    const w = window.open(printUrl + '?' + params.toString(), '_blank');
    if (!w && typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'warning', title: 'Popup blocked', text: 'Please allow popups to print.' });
    }
  });

  $('.btn-scope').on('click', function () {
  $('#btnPdfBiltyGuds').on('click', function () {
    const params = new URLSearchParams(filters());
    window.location.href = pdfUrl + '?' + params.toString();
  });

    setScope($(this).data('scope'));
  });

  $('#bgGuds, #bgOnlyBilty').on('change', function () {
    loadReport();
  });

  $('#bgFromDate, #bgToDate').on('change', function () {
    loadReport();
  });

  $('#btnResetBiltyFilters').on('click', function () {
    $('#bgFromDate').val(defaultFrom);
    $('#bgToDate').val(defaultTo);
    $('#bgGuds').val('');
    $('#bgOnlyBilty').prop('checked', true);
    setScope('all');
  });

  $('#btnMarkDelivered').on('click', function () {
    bulkDelivered(true);
  });

  $('#btnUnmarkDelivered').on('click', function () {
    bulkDelivered(false);
  });

  $(document).on('change', '.bg-row-check', function () {
    updateSelectedHint();
  });

  $(document).on('change', '.bg-select-all', function () {
    const checked = $(this).is(':checked');
    $(this).closest('table').find('.bg-row-check').prop('checked', checked);
    updateSelectedHint();
  });

  loadReport();
});
</script>
@endpush
