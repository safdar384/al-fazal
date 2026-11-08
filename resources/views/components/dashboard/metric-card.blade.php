@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => 'graph-up',
    'accent' => '#0d6efd',
    'valueClass' => 'text-primary',
])

<div {{ $attributes->merge(['class' => 'col-6 col-md-4 col-xl-3']) }}>
    <div class="card erp-dash-card h-100 border-0" style="--dash-accent: {{ $accent }};">
        <div class="card-body d-flex gap-3 align-items-start">
            <div class="erp-dash-card__icon flex-shrink-0">
                <i class="bi bi-{{ $icon }}"></i>
            </div>
            <div class="min-w-0 flex-grow-1">
                <p class="erp-dash-card__title mb-1">{{ $title }}</p>
                <h4 class="erp-dash-card__value mb-0 {{ $valueClass }}">{{ $value }}</h4>
                @if ($subtitle)
                    <small class="text-muted d-block mt-1">{{ $subtitle }}</small>
                @endif
                @if (! $slot->isEmpty())
                    <div class="mt-2">{{ $slot }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
