@extends('layout.app')

@section('title', 'Reporting Dashboard')

@section('content')

<style>
  .rh-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
  }
  .rh-kpi {
    background: #fff;
    border: 1px solid #d5dde8;
    border-left: 4px solid #0f2747;
    border-radius: 4px;
    padding: 0.75rem 1rem;
    height: 100%;
  }
  .rh-kpi.recv { border-left-color: #198754; background: #eefaf2; }
  .rh-kpi.pay { border-left-color: #dc3545; background: #fdf0f1; }
  .rh-kpi.net { border-left-color: #1a4a8a; background: #f4f7fb; }
  .rh-kpi .lbl {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: #6b7c8d;
    margin-bottom: 0.2rem;
  }
  .rh-kpi .val {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f2747;
    margin: 0;
  }
  .rh-kpi.recv .val { color: #198754; }
  .rh-kpi.pay .val { color: #dc3545; }
  .rh-module {
    border: 1px solid #d5dde8;
    border-radius: 4px;
    overflow: hidden;
    height: 100%;
    background: #fff;
    box-shadow: 0 2px 10px rgba(15, 39, 71, 0.06);
    transition: box-shadow 0.2s ease, transform 0.2s ease;
  }
  .rh-module:hover {
    box-shadow: 0 6px 18px rgba(15, 39, 71, 0.12);
    transform: translateY(-2px);
  }
  .rh-module-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
    padding: 0.7rem 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.65rem;
  }
  .rh-module-icon {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    background: rgba(255,255,255,0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .rh-module-icon i { font-size: 1.1rem; }
  .rh-module-title {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
    letter-spacing: 0.2px;
  }
  .rh-module-hint {
    margin: 0;
    font-size: 0.68rem;
    opacity: 0.85;
  }
  .rh-links {
    list-style: none;
    margin: 0;
    padding: 0;
  }
  .rh-links a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.45rem 0.85rem;
    font-size: 0.8rem;
    color: #1a1a1a;
    text-decoration: none;
    border-bottom: 1px solid #eef1f5;
    transition: background 0.15s ease, color 0.15s ease;
  }
  .rh-links a:last-child { border-bottom: none; }
  .rh-links a:hover {
    background: #f4f7fb;
    color: #0f2747;
  }
  .rh-links a i.chev {
    font-size: 0.7rem;
    color: #8a97a6;
  }
  .rh-links a:hover i.chev { color: #0f2747; }
</style>

@php
    $fmt = fn ($n) => number_format((float) $n, 2);

    $modules = [
        [
            'key' => 'sale',
            'label' => 'Sale',
            'icon' => 'bi-bag-check',
            'hint' => 'Invoices, summaries & orders',
            'new_tab' => true,
            'links' => [
                ['label' => 'Sales Summary Report (ERP)', 'route' => 'reports.sales-summary', 'params' => ['print' => 1]],
                ['label' => 'Bilty Report by Guds', 'route' => 'reports.bilty-guds'],
                ['label' => 'Sale Report Summary', 'route' => 'reports.sales.summary'],
                ['label' => 'Sale Report by Company', 'route' => 'reports.sales.by-company'],
                ['label' => 'Sale Report by Product', 'route' => 'reports.sales.by-product'],
                ['label' => 'Sale Report by Area', 'route' => 'reports.sales.by-area'],
                ['label' => 'Sale Report by Customer', 'route' => 'reports.sales.by-customer'],
                                        ['label' => 'Order Summary Report', 'route' => 'reports.orders.summary'],
                                        ['label' => 'Pending Orders Report', 'route' => 'reports.orders.pending'],
                                        ['label' => 'Customer Order History', 'route' => 'reports.orders.customer-history'],
            ],
        ],
        [
            'key' => 'purchase',
            'label' => 'Purchase',
            'icon' => 'bi-cart-plus',
            'hint' => 'Purchase invoices & summaries',
            'new_tab' => true,
            'links' => [
                ['label' => 'Purchase Summary Report (ERP)', 'route' => 'reports.purchases-summary', 'params' => ['print' => 1]],
                ['label' => 'Purchase Report Summary', 'route' => 'reports.purchases.summary'],
                ['label' => 'Purchase by Company', 'route' => 'reports.purchases.by-company'],
                ['label' => 'Purchase by Product', 'route' => 'reports.purchases.by-product'],
                ['label' => 'Purchase by Area', 'route' => 'reports.purchases.by-area'],
                ['label' => 'Purchase by Supplier', 'route' => 'reports.purchases.by-supplier'],
                ['label' => 'Purchase by Supplier & Product', 'route' => 'reports.purchases.by-supplier-product'],
            ],
        ],
        [
            'key' => 'customer',
            'label' => 'Customer',
            'icon' => 'bi-people',
            'hint' => 'Balances, areas & market voucher',
            'new_tab' => true,
            'links' => [
                                        ['label' => 'Market Voucher', 'route' => 'reports.market-voucher'],
                                        ['label' => 'Customer Balance', 'route' => 'reports.customers.balance'],
                                        ['label' => 'Customer Balance by Area', 'route' => 'reports.customers.balance-area'],
                                        ['label' => 'Customer Balance by Name', 'route' => 'reports.customers.balance-name'],
            ],
        ],
        [
            'key' => 'labor',
            'label' => 'Labour',
            'icon' => 'bi-tools',
            'hint' => 'Labour transactions & advance',
            'new_tab' => true,
            'links' => [
                ['label' => 'Labour Report Summary (All)', 'route' => 'reports.labour.summary'],
                ['label' => 'Labour by Worker', 'route' => 'reports.labour.by-worker'],
                ['label' => 'Labour by Ajent', 'route' => 'reports.labour.by-ajent'],
                ['label' => 'Labour by Fitter', 'route' => 'reports.labour.by-fitter'],
                ['label' => 'Labour Advance', 'route' => 'reports.labour.advance'],
            ],
        ],
        [
            'key' => 'expense',
            'label' => 'Expense',
            'icon' => 'bi-cash-stack',
            'hint' => 'Expense ledger & categories',
            'new_tab' => true,
            'links' => [
                ['label' => 'Expense Report Summary', 'route' => 'reports.expense.summary'],
                ['label' => 'Expenses by Category', 'route' => 'reports.expense.by-category'],
            ],
        ],
        [
            'key' => 'product',
            'label' => 'Product',
            'icon' => 'bi-box-seam',
            'hint' => 'Stock & product listings',
            'new_tab' => true,
            'links' => [
                ['label' => 'All Products Report', 'route' => 'reports.products.all'],
                ['label' => 'Product by Company', 'route' => 'reports.products.by-company'],
                ['label' => 'Product by Category', 'route' => 'reports.products.by-category'],
                ['label' => 'Product by Name', 'route' => 'reports.products.by-name'],
                ['label' => 'Short Stock Products', 'route' => 'reports.products.short-stock'],
            ],
        ],
        [
            'key' => 'profit-loss',
            'label' => 'Profit & Loss',
            'icon' => 'bi-graph-up-arrow',
            'hint' => 'Profit analysis & closings',
            'new_tab' => true,
            'links' => [
                ['label' => 'Sale Invoice Profit (All)', 'route' => 'reports.sale-profit.all', 'params' => ['from_date' => $fromDate, 'to_date' => $toDate]],
                ['label' => 'Sale Invoice Profit by Area', 'route' => 'reports.sale-profit.by-area', 'params' => ['from_date' => $fromDate, 'to_date' => $toDate]],
                ['label' => 'Sale Invoice Profit by Customer', 'route' => 'reports.sale-profit.by-customer', 'params' => ['from_date' => $fromDate, 'to_date' => $toDate]],
                ['label' => 'Sale Invoice Profit by Ajent', 'route' => 'reports.sale-profit.by-ajent', 'params' => ['from_date' => $fromDate, 'to_date' => $toDate]],
                ['label' => 'Profit & Loss Statement', 'route' => 'reports.profit-loss.statement', 'params' => ['from_date' => $fromDate, 'to_date' => $toDate]],
                ['label' => 'ERP Balance Closing', 'route' => 'reports.profit-loss.closings', 'params' => ['from_date' => $fromDate, 'to_date' => $toDate]],
                ['label' => 'Closing History Report', 'route' => 'reports.profit-loss.closing-history', 'params' => ['from_date' => $fromDate, 'to_date' => $toDate]],
            ],
        ],
                                    ];
                                @endphp

<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-6">
          <h3 class="mb-0">
            <i class="bi bi-grid-1x2 text-primary me-2"></i>Reporting Dashboard
          </h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reports</li>
          </ol>
        </div>
      </div>
      <hr class="mb-0" style="border-top: 2px solid #0d6efd;">
                                </div>
                            </div>

  <div class="app-content">
    <div class="container-fluid">
      <p class="text-muted small mb-3">
        Set a default period, then open any module report. The selected dates are passed into each report.
      </p>

      {{-- Period filter --}}
      <div class="card shadow-sm mb-3">
        <div class="card-header rh-card-head py-2">
          <h5 class="mb-0"><i class="bi bi-calendar-range me-2"></i>Default Period</h5>
        </div>
        <div class="card-body py-3">
          <form id="rhPeriodForm" class="row g-2 align-items-end">
            <div class="col-md-3">
              <label for="from_date" class="form-label small fw-semibold mb-1">From Date</label>
              <input type="date" name="from_date" id="from_date" class="form-control form-control-sm"
                     value="{{ $fromDate }}" max="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-3">
              <label for="to_date" class="form-label small fw-semibold mb-1">To Date</label>
              <input type="date" name="to_date" id="to_date" class="form-control form-control-sm"
                     value="{{ $toDate }}" max="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6 d-flex flex-wrap gap-2">
              <button type="submit" class="btn btn-primary btn-sm" id="btnApplyPeriod">
                <i class="bi bi-funnel me-1"></i>Apply Period
              </button>
              <button type="button" class="btn btn-outline-secondary btn-sm" id="btnThisMonth">
                <i class="bi bi-arrow-counterclockwise me-1"></i>This Month
              </button>
              <span class="align-self-center small text-muted ms-md-2" id="rhActivePeriod">
                Active: <strong id="rhFromLabel">{{ \Carbon\Carbon::parse($fromDate)->format('d-M-Y') }}</strong>
                → <strong id="rhToLabel">{{ \Carbon\Carbon::parse($toDate)->format('d-M-Y') }}</strong>
              </span>
            </div>
          </form>
                                </div>
                            </div>

      {{-- Portfolio KPIs --}}
      @if (!empty($portfolio))
        <div class="row g-2 mb-3" id="rhPortfolioKpis">
          <div class="col-md-4">
            <div class="rh-kpi recv">
              <div class="lbl">Total Receivable</div>
              <p class="val" id="rhRecv">Rs. {{ $fmt($portfolio['total_receivable']) }}</p>
                                </div>
                            </div>
          <div class="col-md-4">
            <div class="rh-kpi pay">
              <div class="lbl">Total Payable</div>
              <p class="val" id="rhPay">Rs. {{ $fmt($portfolio['total_payable']) }}</p>
                                </div>
                            </div>
          <div class="col-md-4">
            <div class="rh-kpi net">
              <div class="lbl">Net (Payable − Receivable)</div>
              <p class="val" id="rhNet" style="color: {{ ($portfolio['net_balance'] ?? 0) >= 0 ? '#dc3545' : '#198754' }};">
                Rs. {{ $fmt($portfolio['net_balance'] ?? 0) }}
              </p>
            </div>
                                </div>
                            </div>
                        @endif

      {{-- Report modules --}}
      <div class="row g-3">
        @foreach ($modules as $mod)
          <div class="col-12 col-md-6 col-xl-4">
            <div class="rh-module">
              <div class="rh-module-head">
                <div class="rh-module-icon">
                  <i class="bi {{ $mod['icon'] }}"></i>
                    </div>
                <div>
                  <h5 class="rh-module-title">{{ $mod['label'] }}</h5>
                  <p class="rh-module-hint">{{ $mod['hint'] }}</p>
                </div>
              </div>
              <ul class="rh-links">
                @foreach ($mod['links'] as $link)
                  @php
                    $params = array_merge(
                      ['from_date' => $fromDate, 'to_date' => $toDate],
                      $link['params'] ?? []
                    );
                  @endphp
                  <li>
                    <a href="{{ route($link['route'], $params) }}"
                       class="rh-report-link"
                       data-base="{{ route($link['route']) }}"
                       data-extra="{{ e(json_encode($link['params'] ?? [])) }}"
                       @if (!empty($mod['new_tab'])) target="_blank" rel="noopener" @endif>
                      <span>{{ $link['label'] }}</span>
                      <i class="bi bi-chevron-right chev"></i>
                    </a>
                  </li>
                @endforeach
              </ul>
                </div>
            </div>
        @endforeach
      </div>
    </div>
</div>
</main>
@endsection

@push('scripts')
<script>
(function () {
  const form = document.getElementById('rhPeriodForm');
  const fromInput = document.getElementById('from_date');
  const toInput = document.getElementById('to_date');
  const btnApply = document.getElementById('btnApplyPeriod');
  const btnMonth = document.getElementById('btnThisMonth');
  const hubUrl = @json(route('reports.hub'));
  const portfolioUrl = @json(route('reports.hub.portfolio'));

  if (!form || !fromInput || !toInput) return;

  function fmtLabel(iso) {
    if (!iso) return '—';
    const d = new Date(iso + 'T00:00:00');
    if (isNaN(d.getTime())) return iso;
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
  }

  function buildLinkUrl(base, from, to, extra) {
    const params = new URLSearchParams();
    params.set('from_date', from);
    params.set('to_date', to);
    if (extra && typeof extra === 'object') {
      Object.keys(extra).forEach(function (k) {
        if (extra[k] !== null && extra[k] !== undefined && extra[k] !== '') {
          params.set(k, extra[k]);
        }
      });
    }
    return base + (base.indexOf('?') >= 0 ? '&' : '?') + params.toString();
  }

  function updateReportLinks(from, to) {
    document.querySelectorAll('a.rh-report-link').forEach(function (a) {
      let extra = {};
      try { extra = JSON.parse(a.getAttribute('data-extra') || '{}'); } catch (e) {}
      const base = a.getAttribute('data-base') || a.href.split('?')[0];
      a.href = buildLinkUrl(base, from, to, extra);
    });
  }

  function updateActiveLabel(from, to) {
    const fromEl = document.getElementById('rhFromLabel');
    const toEl = document.getElementById('rhToLabel');
    if (fromEl) fromEl.textContent = fmtLabel(from);
    if (toEl) toEl.textContent = fmtLabel(to);
  }

  function refreshPortfolio() {
    return fetch(portfolioUrl, {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
      .then(function (res) { if (!res.ok) throw res; return res.json(); })
      .then(function (data) {
        if (!data.success) return;
        const f = data.formatted || {};
        const p = data.portfolio || {};
        const recv = document.getElementById('rhRecv');
        const pay = document.getElementById('rhPay');
        const net = document.getElementById('rhNet');
        if (recv) recv.textContent = 'Rs. ' + (f.total_receivable || '0.00');
        if (pay) pay.textContent = 'Rs. ' + (f.total_payable || '0.00');
        if (net) {
          net.textContent = 'Rs. ' + (f.net_balance || '0.00');
          net.style.color = (p.net_balance || 0) >= 0 ? '#dc3545' : '#198754';
        }
      })
      .catch(function (err) { console.error(err); });
  }

  function applyPeriod(from, to) {
    if (!from || !to) {
      if (typeof showWarning === 'function') showWarning('Please select date range');
      return;
    }

    const original = btnApply ? btnApply.innerHTML : '';
    if (btnApply) {
      btnApply.disabled = true;
      btnApply.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Updating...';
    }

    updateReportLinks(from, to);
    updateActiveLabel(from, to);
    history.replaceState(null, '', hubUrl + '?from_date=' + encodeURIComponent(from) + '&to_date=' + encodeURIComponent(to));

    refreshPortfolio().finally(function () {
      if (btnApply) {
        btnApply.disabled = false;
        btnApply.innerHTML = original || '<i class="bi bi-funnel me-1"></i>Apply Period';
      }
    });
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    applyPeriod(fromInput.value, toInput.value);
  });

  if (btnMonth) {
    btnMonth.addEventListener('click', function () {
      const now = new Date();
      const y = now.getFullYear();
      const m = String(now.getMonth() + 1).padStart(2, '0');
      const from = y + '-' + m + '-01';
      const to = y + '-' + m + '-' + String(now.getDate()).padStart(2, '0');
      fromInput.value = from;
      toInput.value = to;
      applyPeriod(from, to);
    });
  }
})();
</script>
@endpush
