@php
    $laborLinks = [
        ['label' => 'Labour Report Summary (All)', 'route' => 'reports.labour.summary', 'icon' => 'bi-file-earmark-text'],
        ['label' => 'Labour by Worker', 'route' => 'reports.labour.by-worker', 'icon' => 'bi-person-workspace'],
        ['label' => 'Labour by Ajent', 'route' => 'reports.labour.by-ajent', 'icon' => 'bi-person-badge'],
        ['label' => 'Labour by Fitter', 'route' => 'reports.labour.by-fitter', 'icon' => 'bi-wrench-adjustable'],
        ['label' => 'Labour Advance', 'route' => 'reports.labour.advance', 'icon' => 'bi-cash-coin'],
    ];
@endphp

<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-warning bg-opacity-10 border-0">
        <h6 class="mb-0 fw-semibold text-warning">Labor Section</h6>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @foreach ($laborLinks as $link)
                <a href="{{ route($link['route']) }}" target="_blank" rel="noopener" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi {{ $link['icon'] }} me-2"></i>{{ $link['label'] }}</span>
                    <i class="bi bi-arrow-right-short fs-5"></i>
                </a>
            @endforeach
        </div>
    </div>
</div>
