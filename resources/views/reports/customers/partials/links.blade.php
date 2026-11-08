@php
    $customerLinks = [
        ['label' => 'Customer Balance', 'route' => 'reports.customers.balance', 'icon' => 'bi-wallet2'],
        ['label' => 'Customer Balance by Area', 'route' => 'reports.customers.balance-area', 'icon' => 'bi-geo-alt'],
        ['label' => 'Customer Balance by Name', 'route' => 'reports.customers.balance-name', 'icon' => 'bi-person-badge'],
    ];
@endphp

<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-info bg-opacity-10 border-0">
        <h6 class="mb-0 fw-semibold text-info">Customer Section</h6>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @foreach ($customerLinks as $link)
                <a href="{{ route($link['route']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi {{ $link['icon'] }} me-2"></i>{{ $link['label'] }}</span>
                    <i class="bi bi-arrow-right-short fs-5"></i>
                </a>
            @endforeach
        </div>
    </div>
</div>
