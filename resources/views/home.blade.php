@extends('layout.app')

@section('title', 'Business Dashboard')

@push('styles')
<style>
    .erp-dashboard {
        --dash-ink: #0b1f3a;
        --dash-muted: #5b6b7c;
        --dash-line: rgba(15, 39, 71, 0.08);
        --dash-bg-a: #eef6ff;
        --dash-bg-b: #f3fbf7;
        --dash-bg-c: #fff8ef;
    }

    .erp-dashboard .app-content {
        background:
            radial-gradient(900px 420px at 8% -10%, rgba(14, 165, 233, 0.16), transparent 55%),
            radial-gradient(700px 380px at 92% 0%, rgba(16, 185, 129, 0.12), transparent 50%),
            radial-gradient(640px 360px at 70% 100%, rgba(245, 158, 11, 0.10), transparent 55%),
            linear-gradient(180deg, var(--dash-bg-a) 0%, #f7fafc 42%, var(--dash-bg-b) 100%);
    }

    .erp-dash-hero {
        position: relative;
        overflow: hidden;
        border-radius: 1rem;
        padding: 1.35rem 1.5rem;
        color: #fff;
        background:
            linear-gradient(125deg, #0b1f3a 0%, #134e6f 48%, #0f766e 100%);
        box-shadow: 0 14px 34px rgba(11, 31, 58, 0.22);
        animation: erpDashFadeUp 0.45s ease both;
    }
    .erp-dash-hero::before {
        content: "";
        position: absolute;
        width: 280px;
        height: 280px;
        right: -60px;
        top: -90px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,0.18), transparent 68%);
        pointer-events: none;
    }
    .erp-dash-hero::after {
        content: "";
        position: absolute;
        width: 180px;
        height: 180px;
        left: 38%;
        bottom: -100px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(34, 211, 238, 0.25), transparent 70%);
        pointer-events: none;
    }
    .erp-dash-hero__brand {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        object-fit: contain;
        background: rgba(255,255,255,0.14);
        padding: 6px;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.18);
    }
    .erp-dash-hero h3 {
        font-size: clamp(1.2rem, 2vw, 1.55rem);
        font-weight: 800;
        letter-spacing: 0.01em;
        margin: 0;
    }
    .erp-dash-hero p {
        margin: 0.25rem 0 0;
        opacity: 0.86;
        font-size: 0.92rem;
    }
    .erp-dash-hero__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        justify-content: flex-end;
    }
    .erp-dash-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        background: rgba(255,255,255,0.14);
        border: 1px solid rgba(255,255,255,0.18);
        backdrop-filter: blur(4px);
    }

    .erp-kpi-card {
        position: relative;
        overflow: hidden;
        border-radius: 1rem;
        background: #fff;
        border: 1px solid rgba(15, 39, 71, 0.06);
        box-shadow: 0 8px 22px rgba(15, 39, 71, 0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        animation: erpDashFadeUp 0.5s ease both;
    }
    .erp-kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 28px rgba(15, 39, 71, 0.12);
    }
    .erp-kpi-card__accent {
        height: 4px;
        background: var(--kpi-grad);
    }
    .erp-kpi-card__body {
        padding: 1rem 1rem 1.05rem;
    }
    .erp-kpi-card__top {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        margin-bottom: 0.65rem;
    }
    .erp-kpi-card__icon {
        width: 2.55rem;
        height: 2.55rem;
        border-radius: 0.8rem;
        display: grid;
        place-items: center;
        color: #fff;
        background: var(--kpi-grad);
        box-shadow: 0 8px 16px var(--kpi-soft);
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .erp-kpi-card__title {
        margin: 0;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--dash-muted);
    }
    .erp-kpi-card__value {
        margin: 0;
        font-size: clamp(1.05rem, 2.4vw, 1.35rem);
        font-weight: 800;
        color: var(--kpi-text);
        line-height: 1.2;
        word-break: break-word;
    }
    .erp-kpi-card__sub {
        margin: 0.35rem 0 0;
        font-size: 0.78rem;
        color: #64748b;
    }
    .erp-kpi-card__slot {
        margin-top: 0.65rem;
    }
    .erp-kpi-card__slot .btn {
        border-radius: 0.55rem;
        font-weight: 600;
    }

    .row.g-3.mb-3 > [class*="col-"]:nth-child(1) .erp-kpi-card { animation-delay: 0.04s; }
    .row.g-3.mb-3 > [class*="col-"]:nth-child(2) .erp-kpi-card { animation-delay: 0.08s; }
    .row.g-3.mb-3 > [class*="col-"]:nth-child(3) .erp-kpi-card { animation-delay: 0.12s; }
    .row.g-3.mb-3 > [class*="col-"]:nth-child(4) .erp-kpi-card { animation-delay: 0.16s; }
    .row.g-3.mb-4 > [class*="col-"]:nth-child(1) .erp-kpi-card { animation-delay: 0.18s; }
    .row.g-3.mb-4 > [class*="col-"]:nth-child(2) .erp-kpi-card { animation-delay: 0.22s; }
    .row.g-3.mb-4 > [class*="col-"]:nth-child(3) .erp-kpi-card { animation-delay: 0.26s; }
    .row.g-3.mb-4 > [class*="col-"]:nth-child(4) .erp-kpi-card { animation-delay: 0.30s; }

    .erp-balance-card {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        border-radius: 1rem;
        padding: 1.15rem 1.25rem;
        color: #fff;
        box-shadow: 0 12px 28px rgba(15, 39, 71, 0.12);
        animation: erpDashFadeUp 0.55s ease both;
    }
    .erp-balance-card--in {
        background: linear-gradient(135deg, #065f46 0%, #10b981 100%);
    }
    .erp-balance-card--out {
        background: linear-gradient(135deg, #9f1239 0%, #f43f5e 100%);
    }
    .erp-balance-card__icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.85rem;
        display: grid;
        place-items: center;
        background: rgba(255,255,255,0.18);
        font-size: 1.35rem;
        flex-shrink: 0;
    }
    .erp-balance-card__label {
        margin: 0;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        opacity: 0.88;
    }
    .erp-balance-card__value {
        margin: 0.2rem 0 0.15rem;
        font-weight: 800;
        font-size: clamp(1.15rem, 2.5vw, 1.45rem);
    }
    .erp-balance-card small {
        opacity: 0.85;
    }

    .erp-dash-panel {
        border: 0;
        border-radius: 1rem;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 10px 26px rgba(15, 39, 71, 0.07);
        border: 1px solid rgba(15, 39, 71, 0.05);
        animation: erpDashFadeUp 0.55s ease both;
    }
    .erp-dash-panel .card-header {
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border-bottom: 1px solid var(--dash-line);
    }
    .erp-dash-panel .card-header h5 {
        color: var(--dash-ink);
        font-weight: 750;
    }
    .erp-chart-box {
        min-height: 240px;
        position: relative;
    }
    .erp-dash-loading {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.88);
        z-index: 2;
    }
    .erp-dash-activity-table {
        font-size: 0.85rem;
    }
    .erp-dash-badge {
        font-size: 0.68rem;
    }
    .erp-quick-btn {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        border: 0;
        border-radius: 0.8rem;
        padding: 0.7rem 0.85rem;
        font-weight: 650;
        font-size: 0.86rem;
        color: #fff !important;
        text-decoration: none !important;
        transition: transform 0.15s ease, filter 0.15s ease, box-shadow 0.15s ease;
        box-shadow: 0 8px 16px rgba(15, 39, 71, 0.12);
    }
    .erp-quick-btn:hover {
        transform: translateY(-2px);
        filter: brightness(1.05);
        color: #fff !important;
    }
    .erp-quick-btn i {
        width: 1.7rem;
        height: 1.7rem;
        border-radius: 0.5rem;
        display: grid;
        place-items: center;
        background: rgba(255,255,255,0.2);
    }
    .erp-quick-btn--sale { background: linear-gradient(135deg, #1d4ed8, #0ea5e9); }
    .erp-quick-btn--order { background: linear-gradient(135deg, #c2410c, #f59e0b); }
    .erp-quick-btn--recovery { background: linear-gradient(135deg, #047857, #10b981); }
    .erp-quick-btn--expense { background: linear-gradient(135deg, #be123c, #f43f5e); }
    .erp-quick-btn--customers { background: linear-gradient(135deg, #0e7490, #22d3ee); }

    .erp-snapshot-list li {
        padding: 0.45rem 0;
        border-bottom: 1px dashed rgba(15, 39, 71, 0.1);
    }
    .erp-snapshot-list li:last-child {
        border-bottom: 0;
    }

    .min-h-120 { min-height: 120px; }

    @keyframes erpDashFadeUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 575.98px) {
        .erp-dash-hero {
            padding: 1.1rem;
        }
        .erp-dash-hero__chips {
            justify-content: flex-start;
            margin-top: 0.85rem;
        }
        .erp-chart-box {
            min-height: 210px;
        }
    }
</style>
@endpush

@section('content')
@php
    $currency = fn ($amount) => 'PKR ' . number_format((float) $amount, 2);
    $counts = $partyCounts ?? ['total_customers' => 0, 'total_suppliers' => 0];
    $userName = auth()->user()?->name ?? 'Admin';
@endphp

<main class="app-main erp-dashboard">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="erp-dash-hero mb-1">
                <div class="row align-items-center gy-3 position-relative" style="z-index:1;">
                    <div class="col-lg-7">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ asset('favicon.png') }}" alt="Al Fazal" class="erp-dash-hero__brand">
                            <div>
                                <h3>Al Fazal Business Dashboard</h3>
                                <p>Welcome back, {{ $userName }} — sales, purchases, parties & cash overview</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="erp-dash-hero__chips">
                            <span class="erp-dash-chip"><i class="bi bi-calendar3"></i>{{ now()->format('d M Y') }}</span>
                            <span class="erp-dash-chip"><i class="bi bi-clock"></i>{{ now()->format('h:i A') }}</span>
                            <span class="erp-dash-chip"><i class="bi bi-graph-up-arrow"></i>Live KPIs</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid pb-4">
            {{-- Primary KPI row --}}
            <div class="row g-3 mb-3">
                <x-dashboard.metric-card
                    title="Total Sales"
                    :value="$currency($summary['sales'])"
                    :subtitle="'This month: ' . $currency($thisMonth['sales'])"
                    icon="cart-check"
                    tone="blue"
                />
                <x-dashboard.metric-card
                    title="Total Purchase"
                    :value="$currency($summary['purchase'])"
                    :subtitle="'This month: ' . $currency($thisMonth['purchase'])"
                    icon="truck"
                    tone="violet"
                />
                <x-dashboard.metric-card
                    title="Total Orders"
                    :value="number_format($summary['orders'])"
                    :subtitle="'This month: ' . number_format($thisMonth['orders'])"
                    icon="journal-check"
                    tone="amber"
                />
                <x-dashboard.metric-card
                    title="Net Profit"
                    :value="$currency($profitEstimate)"
                    subtitle="Sales − (Purchase + Expenses)"
                    icon="graph-up-arrow"
                    :tone="$profitEstimate >= 0 ? 'emerald' : 'rose'"
                />
            </div>

            {{-- Secondary KPI row --}}
            <div class="row g-3 mb-4">
                <x-dashboard.metric-card
                    title="Total Customers"
                    :value="number_format($counts['total_customers'])"
                    subtitle="Active customer master records"
                    icon="people"
                    tone="cyan"
                />
                <x-dashboard.metric-card
                    title="Total Suppliers"
                    :value="number_format($counts['total_suppliers'])"
                    subtitle="Supplier category in master"
                    icon="building"
                    tone="indigo"
                />
                <x-dashboard.metric-card
                    title="Recovery"
                    :value="$currency($summary['recovery'])"
                    :subtitle="'This month: ' . $currency($thisMonth['recovery'])"
                    icon="cash-coin"
                    tone="emerald"
                >
                    <a href="{{ route('recovery.distribution') }}" class="btn btn-sm btn-success">Market Recovery</a>
                </x-dashboard.metric-card>
                <x-dashboard.metric-card
                    title="Total Expenses"
                    :value="$currency($summary['expenses'])"
                    :subtitle="'This month: ' . $currency($thisMonth['expenses'])"
                    icon="wallet2"
                    tone="rose"
                />
            </div>

            @include('components.accountingSummaryCards')

            <div class="row g-3 mb-4">
                <div class="col-12 col-lg-8">
                    <div class="card erp-dash-panel h-100">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="mb-0">
                                <i class="bi bi-bar-chart-line text-primary me-1"></i>
                                Sales vs Purchase vs Expenses
                            </h5>
                            <span class="badge rounded-pill text-bg-primary-subtle text-primary border border-primary-subtle">Last 6 months</span>
                        </div>
                        <div class="card-body">
                            <div class="position-relative erp-chart-box">
                                <div id="dashboardChartLoading" class="erp-dash-loading">
                                    <div class="spinner-border text-primary" role="status" aria-label="Loading chart"></div>
                                </div>
                                <canvas id="dashboardTrendChart" height="120" aria-label="Trend chart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="card erp-dash-panel h-100">
                        <div class="card-header py-3">
                            <h5 class="mb-0"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('sale.invoice') }}" class="erp-quick-btn erp-quick-btn--sale">
                                    <i class="bi bi-receipt"></i>New Sale Invoice
                                </a>
                                <a href="{{ route('order.index') }}" class="erp-quick-btn erp-quick-btn--order">
                                    <i class="bi bi-journal-plus"></i>New Order
                                </a>
                                <a href="{{ route('recovery.distribution') }}" class="erp-quick-btn erp-quick-btn--recovery">
                                    <i class="bi bi-bank2"></i>Market Recovery
                                </a>
                                <a href="{{ route('expense.transactionPage') }}" class="erp-quick-btn erp-quick-btn--expense">
                                    <i class="bi bi-wallet2"></i>Post Expense
                                </a>
                                <a href="{{ route('Customer.index') }}" class="erp-quick-btn erp-quick-btn--customers">
                                    <i class="bi bi-people"></i>Customers
                                </a>
                            </div>
                            <hr class="my-3">
                            <p class="small text-muted mb-2 fw-semibold">Snapshot totals</p>
                            <ul class="list-unstyled small mb-0 erp-snapshot-list">
                                <li class="d-flex justify-content-between">
                                    <span>Receivable parties</span>
                                    <strong class="text-success">{{ $accounting['customer_count'] ?? 0 }}</strong>
                                </li>
                                <li class="d-flex justify-content-between">
                                    <span>Payable parties</span>
                                    <strong class="text-danger">{{ $accounting['supplier_count'] ?? 0 }}</strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card erp-dash-panel">
                <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history text-primary me-1"></i>Recent Activity
                    </h5>
                    <span class="text-muted small">Latest 10 transactions</span>
                </div>
                <div id="dashboardActivityWrap" class="card-body p-0 position-relative min-h-120">
                    <div id="dashboardActivityLoading" class="erp-dash-loading">
                        <div class="spinner-border text-primary spinner-border-sm" role="status" aria-label="Loading activity"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const chartUrl = @json(route('dashboard.charts'));
    const activityUrl = @json(route('dashboard.recent-activity'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    async function fetchJson(url) {
        const res = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
            },
            credentials: 'same-origin',
        });
        if (!res.ok) throw new Error('Request failed');
        return res.json();
    }

    function initChart(payload) {
        const canvas = document.getElementById('dashboardTrendChart');
        const loader = document.getElementById('dashboardChartLoading');
        if (!canvas || typeof Chart === 'undefined') return;

        if (loader) loader.remove();

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: payload.labels || [],
                datasets: [
                    {
                        label: 'Sales',
                        data: payload.salesSeries || [],
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(14, 165, 233, 0.16)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#0284c7',
                    },
                    {
                        label: 'Purchase',
                        data: payload.purchaseSeries || [],
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124, 58, 237, 0.10)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#7c3aed',
                    },
                    {
                        label: 'Expenses',
                        data: payload.expensesSeries || [],
                        borderColor: '#e11d48',
                        backgroundColor: 'rgba(244, 63, 94, 0.10)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#e11d48',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14, usePointStyle: true } },
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            maxTicksLimit: 6,
                            callback: (v) => 'PKR ' + Number(v).toLocaleString(),
                        },
                        grid: { color: 'rgba(15, 39, 71, 0.06)' },
                    },
                },
            },
        });
    }

    async function loadChart() {
        const loader = document.getElementById('dashboardChartLoading');
        try {
            const data = await fetchJson(chartUrl);
            initChart(data);
        } catch (e) {
            if (loader) {
                loader.innerHTML = '<span class="text-danger small">Unable to load chart.</span>';
            }
        }
    }

    async function loadActivity() {
        const wrap = document.getElementById('dashboardActivityWrap');
        const loader = document.getElementById('dashboardActivityLoading');
        if (!wrap) return;

        try {
            const data = await fetchJson(activityUrl);
            if (loader) loader.remove();
            wrap.innerHTML = data.html || '<div class="p-4 text-muted text-center">No activity.</div>';
        } catch (e) {
            if (loader) loader.remove();
            wrap.innerHTML = '<div class="p-4 text-danger text-center small">Unable to load recent activity.</div>';
        }
    }

    loadChart();
    loadActivity();
})();
</script>
@endpush
