@isset($accounting)
@php
    $currency = fn ($amount) => 'PKR ' . number_format((float) $amount, 2);
@endphp
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card metric-card h-100 border-0" style="border-top-color:#16a34a !important;">
            <div class="card-body">
                <p class="metric-title mb-1">Total Receivable</p>
                <h4 class="metric-value mb-1 text-success">{{ $currency($accounting['total_receivable']) }}</h4>
                <small class="text-muted">{{ $accounting['customer_count'] }} customer(s) with outstanding balance</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card metric-card h-100 border-0" style="border-top-color:#dc2626 !important;">
            <div class="card-body">
                <p class="metric-title mb-1">Total Payable</p>
                <h4 class="metric-value mb-1 text-danger">{{ $currency($accounting['total_payable']) }}</h4>
                <small class="text-muted">{{ $accounting['supplier_count'] }} supplier(s) with outstanding balance</small>
            </div>
        </div>
    </div>
</div>
@endisset
