<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bilty Report by Guds</title>
    @include('partials.doc-pdf-styles')
    <style>
        .guds-title { font-size: 12px; font-weight: 700; margin: 10px 0 4px; color: #000; border-bottom: 1px solid #000; padding-bottom: 2px; }
        .entry { margin: 4px 0 8px; border: 1px solid #000; padding: 4px 6px; }
        .addr { font-size: 10px; font-weight: 700; color: #000; }
        .metrics { font-size: 10px; color: #000; margin-top: 2px; }
    </style>
</head>
<body>
@include('components.invoiceHeaderPdf')

<div class="report-h1">Bilty Report by Guds</div>
<div class="report-meta">
    Period: {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}
    @if ($filters['guds_name']) · Guds: {{ $filters['guds_name'] }} @endif
</div>

@php
    $fmt = fn ($n) => number_format((float) $n, 2);
    $fmtInt = fn ($n) => number_format((int) $n);
@endphp

@if ($report['groups']->isEmpty())
    <p style="color:#000;">No bilty / guds records found for the selected period.</p>
@else
    @foreach ($report['groups'] as $group)
        <div class="guds-title">
            {{ $group['guds_name'] }}
            <span style="float:right;">Total: {{ $fmtInt($group['totals']['invoice_count']) }}</span>
        </div>
        @foreach ($group['invoices'] as $i => $inv)
            <div class="entry">
                <div class="addr">{{ $i + 1 }}. {{ $inv->address_display }}</div>
                <div class="metrics">
                    Corten: {{ $fmtInt($inv->corten ?? 0) }}
                    · Bilty Expense: {{ $fmt($inv->biltyExpanse ?? 0) }}
                </div>
            </div>
        @endforeach
    @endforeach
@endif
</body>
</html>
