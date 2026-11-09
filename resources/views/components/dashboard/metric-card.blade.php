@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => 'graph-up',
    'accent' => '#0d6efd',
    'valueClass' => '',
    'tone' => 'blue',
])

@php
    $tones = [
        'blue' => ['grad' => 'linear-gradient(135deg, #1d4ed8 0%, #0ea5e9 100%)', 'soft' => 'rgba(14, 165, 233, 0.14)', 'text' => '#0c4a6e'],
        'violet' => ['grad' => 'linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%)', 'soft' => 'rgba(124, 58, 237, 0.12)', 'text' => '#4c1d95'],
        'amber' => ['grad' => 'linear-gradient(135deg, #c2410c 0%, #f59e0b 100%)', 'soft' => 'rgba(245, 158, 11, 0.14)', 'text' => '#9a3412'],
        'emerald' => ['grad' => 'linear-gradient(135deg, #047857 0%, #10b981 100%)', 'soft' => 'rgba(16, 185, 129, 0.14)', 'text' => '#065f46'],
        'rose' => ['grad' => 'linear-gradient(135deg, #be123c 0%, #f43f5e 100%)', 'soft' => 'rgba(244, 63, 94, 0.12)', 'text' => '#9f1239'],
        'cyan' => ['grad' => 'linear-gradient(135deg, #0e7490 0%, #22d3ee 100%)', 'soft' => 'rgba(34, 211, 238, 0.14)', 'text' => '#155e75'],
        'indigo' => ['grad' => 'linear-gradient(135deg, #312e81 0%, #6366f1 100%)', 'soft' => 'rgba(99, 102, 241, 0.12)', 'text' => '#312e81'],
        'slate' => ['grad' => 'linear-gradient(135deg, #334155 0%, #64748b 100%)', 'soft' => 'rgba(100, 116, 139, 0.12)', 'text' => '#1e293b'],
    ];
    $t = $tones[$tone] ?? $tones['blue'];
@endphp

<div {{ $attributes->merge(['class' => 'col-6 col-md-4 col-xl-3']) }}>
    <div class="erp-kpi-card h-100" style="--kpi-grad: {{ $t['grad'] }}; --kpi-soft: {{ $t['soft'] }}; --kpi-text: {{ $t['text'] }};">
        <div class="erp-kpi-card__accent"></div>
        <div class="erp-kpi-card__body">
            <div class="erp-kpi-card__top">
                <div class="erp-kpi-card__icon">
                    <i class="bi bi-{{ $icon }}"></i>
                </div>
                <p class="erp-kpi-card__title">{{ $title }}</p>
            </div>
            <h4 class="erp-kpi-card__value {{ $valueClass }}">{{ $value }}</h4>
            @if ($subtitle)
                <p class="erp-kpi-card__sub">{{ $subtitle }}</p>
            @endif
            @if (! $slot->isEmpty())
                <div class="erp-kpi-card__slot">{{ $slot }}</div>
            @endif
        </div>
    </div>
</div>
