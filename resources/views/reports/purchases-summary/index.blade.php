@extends('layout.app')

@section('title', 'Purchase Summary Report')

@section('content')
@include('partials.sale.erp-styles')

<style>
  .ss-kpi {
    display: flex; align-items: center; gap: 0.75rem;
    background: #fff; border: 1px solid #d5dde8; border-left: 4px solid #0f2747;
    border-radius: 4px; padding: 0.85rem 1rem; height: 100%;
    box-shadow: 0 2px 8px rgba(15, 39, 71, 0.05);
  }
  .ss-kpi.amt { border-left-color: #dc3545; background: #fdf0f1; }
  .ss-kpi.paid { border-left-color: #198754; background: #eefaf2; }
  .ss-kpi.due { border-left-color: #c77c00; background: #fff8ec; }
  .ss-kpi.count { border-left-color: #1a4a8a; background: #f4f7fb; }
  .ss-kpi-icon {
    width: 42px; height: 42px; border-radius: 6px; background: #e8eef5; color: #0f2747;
    display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;
  }
  .ss-kpi-icon.amt { background: #fde8ea; color: #dc3545; }
  .ss-kpi-icon.paid { background: #e8f5e9; color: #198754; }
  .ss-kpi-icon.due { background: #fff3cd; color: #c77c00; }
  .ss-kpi-icon.count { background: #dde5f0; color: #1a4a8a; }
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

  .psr-nav-tabs {
    border-bottom: 0;
    gap: 0.25rem;
    padding: 0.5rem 0.75rem 0;
    background: #f4f7fb;
  }
  .psr-nav-tabs .nav-link {
    font-size: 0.78rem;
    font-weight: 700;
    color: #6b7c8d;
    border: 1px solid transparent;
    border-radius: 4px 4px 0 0;
    padding: 0.45rem 0.9rem;
  }
  .psr-nav-tabs .nav-link:hover { color: #0f2747; background: #e8eef5; }
  .psr-nav-tabs .nav-link.active {
    color: #0f2747;
    background: #fff;
    border-color: #d5dde8 #d5dde8 #fff;
  }

  #purchaseReportLive.is-loading { opacity: 0.45; pointer-events: none; }
  #purchaseReportLive .card-footer {
    background: #fff !important;
    border-color: #e8eef5 !important;
  }
  #purchaseReportLive .page-link {
    color: #0f2747;
    border-color: #d5dde8;
    min-width: 2rem;
    text-align: center;
  }
  #purchaseReportLive .page-item.active .page-link {
    background: #0f2747;
    border-color: #0f2747;
    color: #fff;
  }
  #purchaseReportLive .page-item.disabled .page-link { color: #9aa8b8; }
  #purchaseReportLive .page-link:hover { background: #e8eef5; color: #0f2747; }

  @media print {
    .app-sidebar, .app-header, .app-content-header .breadcrumb, .btn, button, .no-print, .psr-nav-tabs { display: none !important; }
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
            <i class="bi bi-clipboard-data me-2"></i>Purchase Summary Report
          </h3>
          <p class="text-muted small mb-0" id="purchaseReportRange">
            {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}
          </p>
        </div>
        <div class="col-12 col-md-6">
          <ol class="breadcrumb float-md-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('reports.hub') }}">Reports</a></li>
            <li class="breadcrumb-item active" aria-current="page">Purchase Summary</li>
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
          <form id="purchaseReportFilter" method="GET" action="{{ route('reports.purchases-summary') }}" class="row g-3 align-items-end">
            <input type="hidden" name="tab" id="purchaseReportTab" value="{{ $tab }}">
            <div class="col-md-2">
              <label class="form-label ss-label">From Date</label>
              <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}">
            </div>
            <div class="col-md-2">
              <label class="form-label ss-label">To Date</label>
              <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}">
            </div>
            <div class="col-md-3">
              <label class="form-label ss-label">Supplier</label>
                    <select name="supplier_code" class="form-select form-select-sm">
                <option value="">All Suppliers</option>
                        @foreach($filterOptions['suppliers'] as $s)
                            <option value="{{ $s->custCode }}" @selected($filters['supplier_code'] === $s->custCode)>{{ $s->custName }}</option>
                        @endforeach
                    </select>
                </div>
            <div class="col-md-2">
              <label class="form-label ss-label">Payment</label>
                    <select name="payment_status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(['paid','partial','unpaid'] as $ps)
                            <option value="{{ $ps }}" @selected($filters['payment_status'] === $ps)>{{ ucfirst($ps) }}</option>
                        @endforeach
                    </select>
                </div>
            @include('partials.rowsPerPage', ['rowsId' => 'purchaseReportPerPage', 'rowsWrap' => 'col-md-auto', 'rowsValue' => (int) request('per_page', 25)])
            <div class="col-md-auto">
              <a href="{{ route('reports.purchases-summary') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
            </form>
        </div>
    </div>

      @include('reports.purchases-summary.live')
    </div>
</div>
</main>

<script>
(function () {
  const form = document.getElementById('purchaseReportFilter');
  if (!form) return;
  let requestId = 0;

  function syncRange() {
    const live = document.getElementById('purchaseReportLive');
    const range = document.getElementById('purchaseReportRange');
    if (!live || !range || !live.dataset.from || !live.dataset.to) return;
    range.textContent = live.dataset.from + ' — ' + live.dataset.to;
  }

  function loadFromParams(params) {
    const id = ++requestId;
    params.delete('fragment');
    params.delete('download_pdf');
    params.delete('print');
    const clean = params.toString();
    const fetchParams = new URLSearchParams(clean);
    fetchParams.set('fragment', '1');
    const live = document.getElementById('purchaseReportLive');
    if (live) live.classList.add('is-loading');

    fetch(form.action + '?' + fetchParams.toString(), {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
    }).then(function (res) {
      if (!res.ok) throw new Error('load failed');
      return res.text();
    }).then(function (html) {
      if (id !== requestId) return;
      const current = document.getElementById('purchaseReportLive');
      if (!current) return;
      current.outerHTML = html.trim();
      syncRange();
      history.replaceState(null, '', form.action + (clean ? '?' + clean : ''));
    }).catch(function () {
      const current = document.getElementById('purchaseReportLive');
      if (current) current.classList.remove('is-loading');
    });
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    loadFromParams(new URLSearchParams(new FormData(form)));
  });

  form.addEventListener('change', function (event) {
    if (!event.target.matches('select, input[type="date"]')) return;
    const params = new URLSearchParams(new FormData(form));
    params.delete('page');
    loadFromParams(params);
  });

  document.addEventListener('click', function (event) {
    const pageLink = event.target.closest('#purchaseReportLive .pagination a');
    if (pageLink) {
      event.preventDefault();
      const url = new URL(pageLink.href, window.location.origin);
      const params = new URLSearchParams(new FormData(form));
      const tabField = document.getElementById('purchaseReportTab');
      if (url.searchParams.get('tab') && tabField) tabField.value = url.searchParams.get('tab');
      if (url.searchParams.get('per_page') && form.elements.per_page) form.elements.per_page.value = url.searchParams.get('per_page');
      if (tabField) params.set('tab', tabField.value);
      if (url.searchParams.get('page')) params.set('page', url.searchParams.get('page'));
      loadFromParams(params);
      return;
    }

    const link = event.target.closest('#purchaseReportLive a[data-tab]');
    if (!link) return;
    event.preventDefault();
    const tab = document.getElementById('purchaseReportTab');
    if (tab) tab.value = link.getAttribute('data-tab') || 'invoices';
    const params = new URLSearchParams(new FormData(form));
    params.delete('page');
    loadFromParams(params);
  });
})();
</script>
@endsection
