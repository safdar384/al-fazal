@php
    $purchaseLinks = [
        ['label' => 'View Purchase Report Summary', 'route' => 'reports.purchases.summary', 'icon' => 'bi-file-earmark-text'],
        ['label' => 'View Purchase Report by Company', 'route' => 'reports.purchases.by-company', 'icon' => 'bi-buildings'],
        ['label' => 'View Purchase Report by Product', 'route' => 'reports.purchases.by-product', 'icon' => 'bi-box-seam'],
        ['label' => 'View Purchase Report by Area', 'route' => 'reports.purchases.by-area', 'icon' => 'bi-geo-alt'],
        ['label' => 'View Purchase Report by Supplier', 'route' => 'reports.purchases.by-supplier', 'icon' => 'bi-people'],
        ['label' => 'View Purchase Report by Supplier and Product', 'route' => 'reports.purchases.by-supplier-product', 'icon' => 'bi-diagram-3'],
    ];
@endphp

<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-primary bg-opacity-10 border-0">
        <h6 class="mb-0 fw-semibold text-primary">Purchase Section</h6>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @foreach ($purchaseLinks as $link)
                <a href="{{ route($link['route']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi {{ $link['icon'] }} me-2"></i>{{ $link['label'] }}</span>
                    <i class="bi bi-arrow-right-short fs-5"></i>
                </a>
            @endforeach
        </div>
    </div>
</div>
