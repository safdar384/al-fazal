@extends('layout.app')

@section('title', 'Sales Summary Report')

@section('content')
@include('partials.sale.erp-styles')
@php
    $queryBase = array_filter([
        'from_date' => $fromDate,
        'to_date' => $toDate,
        'customer_code' => $filters['customer_code'],
        'product_code' => $filters['product_code'],
        'area' => $filters['area'],
        'sale_type' => $filters['sale_type'],
        'per_page' => request('per_page'),
    ], fn ($v) => $v !== null && $v !== '');
@endphp

<style>
  .ss-kpi {
    display: flex; align-items: center; gap: 0.75rem;
    background: #fff; border: 1px solid #d5dde8; border-left: 4px solid #0f2747;
    border-radius: 4px; padding: 0.85rem 1rem; height: 100%;
    box-shadow: 0 2px 8px rgba(15, 39, 71, 0.05);
  }
  .ss-kpi.amt { border-left-color: #198754; background: #eefaf2; }
  .ss-kpi.credit { border-left-color: #1a4a8a; background: #f4f7fb; }
  .ss-kpi.cash { border-left-color: #0f2747; background: #e8eef5; }
  .ss-kpi.discount { border-left-color: #c77c00; background: #fff8ec; }
  .ss-kpi.paid { border-left-color: #198754; background: #eefaf2; }
  .ss-kpi.due { border-left-color: #dc3545; background: #fdf0f1; }
  .ss-kpi-icon {
    width: 42px; height: 42px; border-radius: 6px; background: #e8eef5; color: #0f2747;
    display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;
  }
  .ss-kpi-icon.amt { background: #e8f5e9; color: #198754; }
  .ss-kpi-icon.credit { background: #e8eef5; color: #1a4a8a; }
  .ss-kpi-icon.cash { background: #dde5f0; color: #0f2747; }
  .ss-kpi-icon.discount { background: #fff3cd; color: #c77c00; }
  .ss-kpi-icon.paid { background: #e8f5e9; color: #198754; }
  .ss-kpi-icon.due { background: #fde8ea; color: #dc3545; }
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
  .ss-table td.ss-address {
    white-space: normal; word-break: break-word; min-width: 180px; max-width: 320px;
    line-height: 1.3; vertical-align: top;
  }
  .ss-table tbody tr:nth-child(even) td { background: #f4f7fb; }
  .ss-table tbody tr:hover td { background: #e8eef5; }
  .ss-table tfoot td {
    background: #e8eef5; font-weight: 700; color: #0f2747;
    border-color: #d5dde8; font-size: 0.82rem; padding: 0.55rem 0.65rem;
  }

  .ssr-nav-tabs {
    border-bottom: 0;
    gap: 0.25rem;
    padding: 0.5rem 0.75rem 0;
    background: #f4f7fb;
  }
  .ssr-nav-tabs .nav-link {
    font-size: 0.78rem;
    font-weight: 700;
    color: #6b7c8d;
    border: 1px solid transparent;
    border-radius: 4px 4px 0 0;
    padding: 0.45rem 0.9rem;
  }
  .ssr-nav-tabs .nav-link:hover { color: #0f2747; background: #e8eef5; }
  .ssr-nav-tabs .nav-link.active {
    color: #0f2747;
    background: #fff;
    border-color: #d5dde8 #d5dde8 #fff;
  }

  .badge-cash { background: #198754; }
  .badge-credit { background: #0f2747; }

  #ssrLive.is-loading { opacity: 0.45; pointer-events: none; }

  #ssrLive .card-footer {
    background: #fff !important;
    border-color: #e8eef5 !important;
  }
  #ssrLive .page-link {
    color: #0f2747;
    border-color: #d5dde8;
    min-width: 2rem;
    text-align: center;
  }
  #ssrLive .page-item.active .page-link {
    background: #0f2747;
    border-color: #0f2747;
    color: #fff;
  }
  #ssrLive .page-item.disabled .page-link { color: #9aa8b8; }
  #ssrLive .page-link:hover { background: #e8eef5; color: #0f2747; }

  @media print {
    .app-sidebar, .app-header, .app-content-header .breadcrumb, .btn, button, .no-print, .ssr-nav-tabs { display: none !important; }
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
            <i class="bi bi-clipboard-data me-2"></i>Sales Summary Report
          </h3>
          <p class="text-muted small mb-0" id="ssrPeriod">
            {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}
          </p>
        </div>
        <div class="col-12 col-md-6">
          <ol class="breadcrumb float-md-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('reports.hub') }}">Reports</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sales Summary</li>
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
          <form id="ssrFilterForm" method="GET" action="{{ route('reports.sales-summary') }}" class="row g-3 align-items-end">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="col-md-2">
              <label class="form-label ss-label">From Date</label>
              <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}" required>
            </div>
            <div class="col-md-2">
              <label class="form-label ss-label">To Date</label>
              <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}" required>
            </div>
            <div class="col-md-2">
              <label class="form-label ss-label">Customer</label>
              <select name="customer_code" class="form-select form-select-sm">
                <option value="">All Customers</option>
                @foreach ($filterOptions['customers'] as $c)
                  <option value="{{ $c->custCode }}" @selected($filters['customer_code'] === $c->custCode)>
                    {{ $c->custName }} ({{ $c->custCode }})
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label ss-label">Product</label>
              <select name="product_code" class="form-select form-select-sm">
                <option value="">All Products</option>
                @foreach ($filterOptions['products'] as $p)
                  <option value="{{ $p->productCode }}" @selected($filters['product_code'] === $p->productCode)>
                    {{ $p->productName }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label ss-label">Area</label>
              <select name="area" class="form-select form-select-sm">
                <option value="">All Areas</option>
                @foreach ($filterOptions['areas'] as $area)
                  <option value="{{ $area }}" @selected($filters['area'] === $area)>{{ $area }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label ss-label">Invoice Type</label>
              <select name="sale_type" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="cash" @selected($filters['sale_type'] === 'cash')>Cash Sale</option>
                <option value="credit" @selected($filters['sale_type'] === 'credit')>Credit Sale</option>
              </select>
            </div>
            @include('partials.rowsPerPage', ['rowsId' => 'ssrPerPage', 'rowsValue' => (int) request('per_page', 25)])
          </form>
        </div>
      </div>

      @include('reports.sales-summary.live')

    </div>
  </div>
</main>

<script>
(function () {
    const form = document.getElementById('ssrFilterForm');
    if (!form) return;

    let requestId = 0;

    function syncPrint(params) {
        const printLink = document.getElementById('ssrPrint');
        if (!printLink) return;
        const printParams = new URLSearchParams(params);
        printParams.delete('page');
        printParams.delete('fragment');
        printParams.set('print', '1');
        printLink.href = form.action + '?' + printParams.toString();
    }

    function applyParamsToForm(params) {
        ['from_date', 'to_date', 'customer_code', 'product_code', 'area', 'sale_type', 'tab', 'per_page'].forEach(function (name) {
            if (form.elements[name] && params.has(name)) {
                form.elements[name].value = params.get(name) || '';
            }
        });
    }

    function loadFromParams(params) {
        const id = ++requestId;
        params.delete('fragment');
        params.delete('print');
        params.delete('download_pdf');
        params.delete('export_excel');
        const clean = params.toString();
        const fetchParams = new URLSearchParams(clean);
        fetchParams.set('fragment', '1');
        const live = document.getElementById('ssrLive');
        if (live) live.classList.add('is-loading');

        fetch(form.action + '?' + fetchParams.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        }).then(function (res) {
            if (!res.ok) throw new Error('load failed');
            return res.text();
        }).then(function (html) {
            if (id !== requestId) return;
            const current = document.getElementById('ssrLive');
            if (!current) return;
            current.outerHTML = html.trim();
            const next = document.getElementById('ssrLive');
            const period = document.getElementById('ssrPeriod');
            if (period && next) period.textContent = next.dataset.period || period.textContent;
            history.replaceState(null, '', form.action + (clean ? '?' + clean : ''));
            syncPrint(new URLSearchParams(clean));
        }).catch(function () {
            const current = document.getElementById('ssrLive');
            if (current) current.classList.remove('is-loading');
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const params = new URLSearchParams(new FormData(form));
        params.delete('page');
        loadFromParams(params);
    });

    form.addEventListener('change', function () {
        const params = new URLSearchParams(new FormData(form));
        params.delete('page');
        loadFromParams(params);
    });

    document.addEventListener('click', function (e) {
        const link = e.target.closest('#ssrLive a');
        if (!link) return;
        if (link.id === 'ssrPrint' || link.getAttribute('target') === '_blank') return;
        const url = new URL(link.href, window.location.origin);
        if (url.pathname !== new URL(form.action, window.location.origin).pathname) return;
        e.preventDefault();
        applyParamsToForm(url.searchParams);
        loadFromParams(url.searchParams);
    });
})();
</script>
@endsection
