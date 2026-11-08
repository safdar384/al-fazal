@php
    $productLinks = [
        ['label' => 'All Products Report', 'route' => 'reports.products.all', 'icon' => 'bi-box-seam'],
        ['label' => 'Product by Company', 'route' => 'reports.products.by-company', 'icon' => 'bi-buildings'],
        ['label' => 'Product by Category', 'route' => 'reports.products.by-category', 'icon' => 'bi-tags'],
        ['label' => 'Product by Name', 'route' => 'reports.products.by-name', 'icon' => 'bi-card-heading'],
        ['label' => 'Short Stock Products', 'route' => 'reports.products.short-stock', 'icon' => 'bi-exclamation-triangle'],
    ];
@endphp

<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-info bg-opacity-10 border-0">
        <h6 class="mb-0 fw-semibold text-info">Product Section</h6>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @foreach ($productLinks as $link)
                <a href="{{ route($link['route']) }}" target="_blank" rel="noopener" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi {{ $link['icon'] }} me-2"></i>{{ $link['label'] }}</span>
                    <i class="bi bi-arrow-right-short fs-5"></i>
                </a>
            @endforeach
        </div>
    </div>
</div>
