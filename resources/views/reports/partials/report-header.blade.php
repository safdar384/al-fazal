{{-- $label (string), optional $subtitle (string) --}}
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <nav aria-label="breadcrumb" class="small">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('reports.hub') }}">Reports</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $label }}</li>
            </ol>
        </nav>
        <h4 class="mb-0"><i class="bi {{ $icon ?? 'bi-file-earmark-bar-graph' }} text-primary me-2"></i>{{ $label }}</h4>
        @isset($subtitle)
            <p class="text-muted small mb-0">{{ $subtitle }}</p>
        @endisset
    </div>
</div>
