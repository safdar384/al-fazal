@extends('layout.app')

@section('title', 'Order Summary')

@section('content')
@include('partials.sale.erp-styles')

<style>
  .ss-kpi {
    display: flex; align-items: center; gap: 0.75rem;
    background: #fff; border: 1px solid #d5dde8; border-left: 4px solid #0f2747;
    border-radius: 4px; padding: 0.85rem 1rem; height: 100%;
    box-shadow: 0 2px 8px rgba(15, 39, 71, 0.05);
  }
  .ss-kpi.pending { border-left-color: #c77c00; background: #fff8ec; }
  .ss-kpi.confirmed { border-left-color: #0dcaf0; background: #eef9fc; }
  .ss-kpi.process { border-left-color: #1a4a8a; background: #f4f7fb; }
  .ss-kpi.completed { border-left-color: #198754; background: #eefaf2; }
  .ss-kpi.cancelled { border-left-color: #dc3545; background: #fdf0f1; }
  .ss-kpi-icon {
    width: 42px; height: 42px; border-radius: 6px; background: #e8eef5; color: #0f2747;
    display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;
  }
  .ss-kpi-icon.pending { background: #fff3cd; color: #c77c00; }
  .ss-kpi-icon.confirmed { background: #cff4fc; color: #087990; }
  .ss-kpi-icon.process { background: #e8eef5; color: #1a4a8a; }
  .ss-kpi-icon.completed { background: #e8f5e9; color: #198754; }
  .ss-kpi-icon.cancelled { background: #fde8ea; color: #dc3545; }
  .ss-kpi-lbl {
    font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.3px; color: #6b7c8d; margin-bottom: 0.15rem;
  }
  .ss-kpi-val { font-size: 1.05rem; font-weight: 700; color: #0f2747; line-height: 1.2; }
  .ss-kpi-sub { font-size: 0.78rem; color: #6b7c8d; font-weight: 600; margin-top: 0.15rem; }

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

  .order-status-badge { font-size: 0.72rem; font-weight: 600; }
  .order-actions .btn-group {
    display: inline-flex;
    flex-wrap: nowrap;
    gap: 0.25rem;
  }
  .order-actions .btn { flex: 0 0 auto; }

  #orderSummaryLive.is-loading { opacity: 0.45; pointer-events: none; }
  #orderSummaryLive .card-footer {
    background: #fff !important;
    border-color: #e8eef5 !important;
  }
  #orderSummaryLive .page-link {
    color: #0f2747;
    border-color: #d5dde8;
    min-width: 2rem;
    text-align: center;
  }
  #orderSummaryLive .page-item.active .page-link {
    background: #0f2747;
    border-color: #0f2747;
    color: #fff;
  }
  #orderSummaryLive .page-item.disabled .page-link { color: #9aa8b8; }
  #orderSummaryLive .page-link:hover { background: #e8eef5; color: #0f2747; }

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
            <i class="bi bi-journal-text me-2"></i>Order Summary
          </h3>
          <p class="text-muted small mb-0">Sale · Customer demand only — no stock or accounting impact</p>
        </div>
        <div class="col-12 col-md-6">
          <ol class="breadcrumb float-md-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Sale</span></li>
            <li class="breadcrumb-item active" aria-current="page">Order Summary</li>
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
          <form method="GET" action="{{ route('order.summary') }}" id="orderSummaryFilter" class="row g-3 align-items-end">
            <div class="col-md-2">
              <label class="form-label ss-label">From Date</label>
              <input type="date" class="form-control form-control-sm" name="fromDate" value="{{ $fromDate ?? '' }}">
            </div>
            <div class="col-md-2">
              <label class="form-label ss-label">To Date</label>
              <input type="date" class="form-control form-control-sm" name="toDate" value="{{ $toDate ?? '' }}">
            </div>
            <div class="col-md-2">
              <label class="form-label ss-label">Status</label>
              <select class="form-select form-select-sm" name="status">
                <option value="">All</option>
                @foreach($statuses ?? [] as $st)
                  <option value="{{ $st }}" @selected(($status ?? '') === $st)>{{ $st }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label ss-label">Area</label>
              <select class="form-select form-select-sm" name="area" id="orderSummaryArea">
                <option value="">All Areas</option>
                @foreach($areas ?? [] as $ar)
                  <option value="{{ $ar }}" @selected(($area ?? '') === $ar)>{{ $ar }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label ss-label">Customer</label>
              <select class="form-select form-select-sm" name="customerCode" id="orderSummaryCustomer">
                <option value="">All Customers</option>
                @foreach($customers as $cust)
                  <option value="{{ $cust->custCode }}"
                    data-area="{{ $cust->custArea ?? '' }}"
                    @selected(($customerCode ?? '') === $cust->custCode)>
                    {{ $cust->custName }}
                  </option>
                @endforeach
              </select>
            </div>
            @include('partials.rowsPerPage', ['rowsId' => 'orderSummaryPerPage'])
            <div class="col-md-auto">
              <a href="{{ route('order.summary') }}" id="orderSummaryReset" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
          </form>
        </div>
      </div>

      @include('Pages.Sale.orderSummaryLive')
    </div>
  </div>
</main>

<script>
(function () {
  const form = document.getElementById('orderSummaryFilter');
  if (!form) return;
  window.__orderSummaryAlive = true;
  let requestId = 0;

  (function cascadeAreaCustomer() {
    var parent = document.getElementById('orderSummaryArea');
    var child = document.getElementById('orderSummaryCustomer');
    if (!parent || !child) return;

    var allOptions = Array.prototype.slice.call(child.querySelectorAll('option')).map(function (opt) {
      return {
        value: opt.value,
        text: opt.textContent,
        key: (opt.getAttribute('data-area') || '').trim()
      };
    });

    function rebuild(parentValue, keepValue) {
      var current = keepValue !== undefined ? keepValue : child.value;
      child.innerHTML = '';

      allOptions.forEach(function (item) {
        if (item.value === '') {
          var allOpt = document.createElement('option');
          allOpt.value = '';
          allOpt.textContent = 'All Customers';
          child.appendChild(allOpt);
          return;
        }
        if (parentValue && item.key.toUpperCase() !== parentValue.toUpperCase()) return;
        var opt = document.createElement('option');
        opt.value = item.value;
        opt.textContent = item.text;
        opt.setAttribute('data-area', item.key);
        child.appendChild(opt);
      });

      var stillValid = Array.prototype.some.call(child.options, function (o) {
        return o.value === current;
      });
      child.value = stillValid ? current : '';
    }

    parent.addEventListener('change', function () {
      rebuild(parent.value.trim(), '');
    });

    form._rebuildOrderCustomers = rebuild;

    if (parent.value) {
      rebuild(parent.value.trim(), child.value);
    }
  })();

  function loadFromParams(params) {
    const id = ++requestId;
    params.delete('fragment');
    params.delete('page');
    const clean = params.toString();
    const fetchParams = new URLSearchParams(clean);
    fetchParams.set('fragment', '1');
    const live = document.getElementById('orderSummaryLive');
    if (live) live.classList.add('is-loading');

    fetch(form.action + '?' + fetchParams.toString(), {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
    }).then(function (res) {
      if (!res.ok) throw new Error('load failed');
      return res.text();
    }).then(function (html) {
      if (id !== requestId) return;
      const current = document.getElementById('orderSummaryLive');
      if (!current) return;
      current.outerHTML = html.trim();
      history.replaceState(null, '', form.action + (clean ? '?' + clean : ''));
    }).catch(function () {
      const current = document.getElementById('orderSummaryLive');
      if (current) current.classList.remove('is-loading');
    });
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    loadFromParams(new URLSearchParams(new FormData(form)));
  });

  form.addEventListener('change', function (event) {
    if (!event.target.matches('select, input[type="date"]')) return;
    loadFromParams(new URLSearchParams(new FormData(form)));
  });

  document.addEventListener('click', function (event) {
    const reset = event.target.closest('#orderSummaryReset');
    if (reset) {
      event.preventDefault();
      Array.from(form.elements).forEach(function (field) {
        if (field.name && field.name !== 'per_page') field.value = '';
      });
      if (form.elements.per_page) form.elements.per_page.value = '25';
      if (typeof form._rebuildOrderCustomers === 'function') {
        form._rebuildOrderCustomers('', '');
      }
      loadFromParams(new URLSearchParams(new FormData(form)));
      return;
    }

    const link = event.target.closest('#orderSummaryLive .pagination a');
    if (!link) return;
    const url = new URL(link.href, window.location.origin);
    if (url.pathname !== new URL(form.action, window.location.origin).pathname) return;
    event.preventDefault();
    const params = new URLSearchParams(new FormData(form));
    if (url.searchParams.get('page')) params.set('page', url.searchParams.get('page'));
    const id = ++requestId;
    params.delete('fragment');
    const fetchParams = new URLSearchParams(params);
    fetchParams.set('fragment', '1');
    const live = document.getElementById('orderSummaryLive');
    if (live) live.classList.add('is-loading');
    fetch(form.action + '?' + fetchParams.toString(), {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
    }).then(function (res) {
      if (!res.ok) throw new Error('load failed');
      return res.text();
    }).then(function (html) {
      if (id !== requestId) return;
      const current = document.getElementById('orderSummaryLive');
      if (!current) return;
      current.outerHTML = html.trim();
      history.replaceState(null, '', form.action + (params.toString() ? '?' + params.toString() : ''));
    }).catch(function () {
      const current = document.getElementById('orderSummaryLive');
      if (current) current.classList.remove('is-loading');
    });
  });

  document.addEventListener('click', async function (event) {
    const btn = event.target.closest('.btn-convert-invoice');
    if (!btn || !document.getElementById('orderSummaryLive')?.contains(btn)) return;
    const orderNo = btn.dataset.order;
    if (!confirm('Convert order ' + orderNo + ' to a Sale Invoice? This will post normal sale accounting.')) return;
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    btn.disabled = true;
    try {
      const res = await fetch('{{ url('/order') }}/' + encodeURIComponent(orderNo) + '/convert-to-invoice', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'Content-Type': 'application/json' },
      });
      const data = await res.json();
      if (data.success) {
        alert(data.message);
        if (data.previewUrl) window.open(data.previewUrl, '_blank');
        loadFromParams(new URLSearchParams(new FormData(form)));
      } else {
        alert(data.message || 'Conversion failed');
        btn.disabled = false;
      }
    } catch (e) {
      alert('Error: ' + e.message);
      btn.disabled = false;
    }
  });
})();
</script>
@endsection
