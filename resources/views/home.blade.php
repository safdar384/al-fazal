@extends('layout.app')

@section('title', 'Business Dashboard')

@push('styles')
<style>
    .erp-dashboard {
        --erp-blue: #0f2747;
        --erp-blue-light: #1a4a8a;
        --erp-surface: #f4f8fc;
    }
    .erp-dashboard .app-content {
        background: var(--erp-surface);
    }
    .erp-dash-card {
        border-top: 3px solid var(--dash-accent, #0d6efd);
        box-shadow: 0 2px 8px rgba(15, 39, 71, 0.06);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .erp-dash-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 39, 71, 0.1);
    }
    .erp-dash-card__icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: color-mix(in srgb, var(--dash-accent, #0d6efd) 12%, white);
        color: var(--dash-accent, #0d6efd);
        font-size: 1.15rem;
    }
    .erp-dash-card__title {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin: 0;
    }
    .erp-dash-card__value {
        font-size: clamp(1rem, 2.5vw, 1.25rem);
        font-weight: 700;
        line-height: 1.2;
    }
    .erp-dash-panel {
        border: 0;
        box-shadow: 0 2px 8px rgba(15, 39, 71, 0.06);
        border-radius: 0.5rem;
        overflow: hidden;
    }
    .erp-dash-panel .card-header {
        background: #fff;
        border-bottom: 1px solid #e8eef5;
    }
    .erp-chart-box {
        min-height: 220px;
        position: relative;
    }
    .erp-dash-loading {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.85);
        z-index: 2;
    }
    .erp-dash-activity-table {
        font-size: 0.85rem;
    }
    .erp-dash-badge {
        font-size: 0.68rem;
    }
    .erp-dash-quick-link {
        font-size: 0.8rem;
    }
    .min-h-120 {
        min-height: 120px;
    }
    @media (max-width: 575.98px) {
        .erp-dash-card__value {
            font-size: 1rem;
        }
        .erp-chart-box {
            min-height: 200px;
        }
    }
</style>
@endpush

@section('content')
@php
    $currency = fn ($amount) => 'PKR ' . number_format((float) $amount, 2);
    $counts = $partyCounts ?? ['total_customers' => 0, 'total_suppliers' => 0];
@endphp

<main class="app-main erp-dashboard">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center gy-2">
                <div class="col-12 col-md-8">
                    <h3 class="mb-0 text-dark">
                        <i class="bi bi-speedometer2 text-primary me-2"></i>Business Dashboard
                    </h3>
                    <p class="text-muted small mb-0 mt-1">Overview of sales, purchases, parties, and cash flow</p>
                </div>
                <div class="col-12 col-md-4">
                    <ol class="breadcrumb float-md-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid pb-3">
            {{-- Primary KPI row --}}
            <div class="row g-3 mb-3">
                <x-dashboard.metric-card
                    title="Total Sales"
                    :value="$currency($summary['sales'])"
                    :subtitle="'This month: ' . $currency($thisMonth['sales'])"
                    icon="cart-check"
                    accent="#0d6efd"
                    value-class="text-primary"
                />
                <x-dashboard.metric-card
                    title="Total Purchase"
                    :value="$currency($summary['purchase'])"
                    :subtitle="'This month: ' . $currency($thisMonth['purchase'])"
                    icon="truck"
                    accent="#6f42c1"
                />
                <x-dashboard.metric-card
                    title="Total Orders"
                    :value="number_format($summary['orders'])"
                    :subtitle="'This month: ' . number_format($thisMonth['orders'])"
                    icon="journal-check"
                    accent="#fd7e14"
                    value-class="text-warning"
                />
                <x-dashboard.metric-card
                    title="Net Profit"
                    :value="$currency($profitEstimate)"
                    subtitle="Sales − (Purchase + Expenses)"
                    icon="graph-up-arrow"
                    :accent="$profitEstimate >= 0 ? '#16a34a' : '#dc2626'"
                    :value-class="$profitEstimate >= 0 ? 'text-success' : 'text-danger'"
                />
            </div>

            {{-- Secondary KPI row --}}
            <div class="row g-3 mb-4">
                <x-dashboard.metric-card
                    title="Total Customers"
                    :value="number_format($counts['total_customers'])"
                    subtitle="Active customer master records"
                    icon="people"
                    accent="#0dcaf0"
                    value-class="text-info"
                />
                <x-dashboard.metric-card
                    title="Total Suppliers"
                    :value="number_format($counts['total_suppliers'])"
                    subtitle="Supplier category in master"
                    icon="building"
                    accent="#6610f2"
                />
                <x-dashboard.metric-card
                    title="Recovery"
                    :value="$currency($summary['recovery'])"
                    :subtitle="'This month: ' . $currency($thisMonth['recovery'])"
                    icon="cash-coin"
                    accent="#16a34a"
                    value-class="text-success"
                >
                    <a href="{{ route('recovery.distribution') }}" class="btn btn-outline-success btn-sm erp-dash-quick-link">Market Recovery</a>
                </x-dashboard.metric-card>
                <x-dashboard.metric-card
                    title="Total Expenses"
                    :value="$currency($summary['expenses'])"
                    :subtitle="'This month: ' . $currency($thisMonth['expenses'])"
                    icon="wallet2"
                    accent="#dc2626"
                    value-class="text-danger"
                />
            </div>

            @include('components.accountingSummaryCards')

            <div class="row g-3 mb-4">
                <div class="col-12 col-lg-8">
                    <div class="card erp-dash-panel h-100">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="mb-0 fw-semibold">
                                <i class="bi bi-bar-chart-line text-primary me-1"></i>
                                Sales vs Purchase vs Expenses
                            </h5>
                            <span class="badge text-bg-light text-dark">Last 6 months</span>
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
                            <h5 class="mb-0 fw-semibold"><i class="bi bi-lightning-charge text-primary me-1"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('sale.invoice') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-receipt me-1"></i>New Sale Invoice
                                </a>
                                <a href="{{ route('order.index') }}" class="btn btn-outline-warning btn-sm">
                                    <i class="bi bi-journal-plus me-1"></i>New Order
                                </a>
                                <a href="{{ route('recovery.distribution') }}" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-bank2 me-1"></i>Market Recovery
                                </a>
                                <a href="{{ route('expense.transactionPage') }}" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-wallet2 me-1"></i>Post Expense
                                </a>
                                <a href="{{ route('Customer.index') }}" class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-people me-1"></i>Customers
                                </a>
                            </div>
                            <hr class="my-3">
                            <p class="small text-muted mb-2">Snapshot totals</p>
                            <ul class="list-unstyled small mb-0">
                                <li class="d-flex justify-content-between py-1 border-bottom">
                                    <span>Receivable parties</span>
                                    <strong class="text-success">{{ $accounting['customer_count'] ?? 0 }}</strong>
                                </li>
                                <li class="d-flex justify-content-between py-1">
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
                    <h5 class="mb-0 fw-semibold">
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
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.12)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3,
                    },
                    {
                        label: 'Purchase',
                        data: payload.purchaseSeries || [],
                        borderColor: '#6f42c1',
                        backgroundColor: 'rgba(111, 66, 193, 0.08)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3,
                    },
                    {
                        label: 'Expenses',
                        data: payload.expensesSeries || [],
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.08)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14 } },
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            maxTicksLimit: 6,
                            callback: (v) => 'PKR ' + Number(v).toLocaleString(),
                        },
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
