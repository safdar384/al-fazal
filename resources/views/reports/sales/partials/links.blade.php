@php
    $saleLinks = [
        ['label' => 'View Sale Report Summary', 'route' => 'reports.sales.summary', 'icon' => 'bi-file-earmark-text'],
        ['label' => 'View Sale Report by Company', 'route' => 'reports.sales.by-company', 'icon' => 'bi-buildings'],
        ['label' => 'View Sale Report by Product', 'route' => 'reports.sales.by-product', 'icon' => 'bi-box-seam'],
        ['label' => 'View Sale Report by Area', 'route' => 'reports.sales.by-area', 'icon' => 'bi-geo-alt'],
        ['label' => 'View Sale Report by Customer', 'route' => 'reports.sales.by-customer', 'icon' => 'bi-people'],
    ];
@endphp

<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-success bg-opacity-10 border-0">
        <h6 class="mb-0 fw-semibold text-success">Sale Section</h6>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @foreach ($saleLinks as $link)
                <a href="{{ route($link['route']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi {{ $link['icon'] }} me-2"></i>{{ $link['label'] }}</span>
                    <i class="bi bi-arrow-right-short fs-5"></i>
                </a>
            @endforeach
        </div>
    </div>
</div>
