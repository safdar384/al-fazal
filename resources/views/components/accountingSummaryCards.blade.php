@isset($accounting)
@php
    $currency = fn ($amount) => 'PKR ' . number_format((float) $amount, 2);
@endphp
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="erp-balance-card erp-balance-card--in h-100">
            <div class="erp-balance-card__icon">
                <i class="bi bi-arrow-down-left-circle-fill"></i>
            </div>
            <div>
                <p class="erp-balance-card__label">Total Receivable</p>
                <h4 class="erp-balance-card__value">{{ $currency($accounting['total_receivable']) }}</h4>
                <small>{{ $accounting['customer_count'] }} customer(s) with outstanding balance</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="erp-balance-card erp-balance-card--out h-100">
            <div class="erp-balance-card__icon">
                <i class="bi bi-arrow-up-right-circle-fill"></i>
            </div>
            <div>
                <p class="erp-balance-card__label">Total Payable</p>
                <h4 class="erp-balance-card__value">{{ $currency($accounting['total_payable']) }}</h4>
                <small>{{ $accounting['supplier_count'] }} supplier(s) with outstanding balance</small>
            </div>
        </div>
    </div>
</div>
@endisset
