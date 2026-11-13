<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Market Voucher</title>
    @include('partials.doc-pdf-styles')
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        body { font-size: 9px; }
        table.data th, table.data td { padding: 2px 3px; font-size: 8.5px; }
    </style>
</head>
<body>
@include('components.invoiceHeaderPdf')

@php
    $fmt = fn ($n) => number_format((float) $n, 2);
    $rows = $report['rows'];
    $totals = $report['totals'];
    $meta = $report['meta'];
    $cellAmt = static function ($n) use ($fmt) {
        $v = (float) $n;
        return $v > 0 ? $fmt($v) : '—';
    };
@endphp

<div class="report-h1">Market Voucher</div>
<div class="report-meta">
    Area: {{ $meta['area_code'] }} — {{ $meta['area_name'] }}
    @if (!empty($meta['agent_name'])) · Agent: {{ $meta['agent_name'] }} @endif
    · Period: {{ \Carbon\Carbon::parse($meta['from'])->format('d M Y') }} — {{ \Carbon\Carbon::parse($meta['to'])->format('d M Y') }}
</div>

<table class="data">
    <thead>
        <tr>
            <th class="text-center">Page</th>
            <th>Customer</th>
            <th class="text-center">City</th>
            <th class="text-right">Pre Balance</th>
            <th class="text-right">Delivery</th>
            <th class="text-right">Balance</th>
            <th class="text-right">Recovery</th>
            <th class="text-right">Discount</th>
            <th class="text-right">Bilty</th>
            <th class="text-right">Other</th>
            <th class="text-right">Last Balance</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td class="text-center">{{ $row->page_no }}</td>
                <td>{{ $row->customer_name }}</td>
                <td class="text-center">{{ $row->city }}</td>
                <td class="text-right">{{ $fmt($row->pre_balance) }}</td>
                <td class="text-right">{{ $fmt($row->delivery) }}</td>
                <td class="text-right">{{ $fmt($row->balance) }}</td>
                <td class="text-right">{{ $cellAmt($row->recovery) }}</td>
                <td class="text-right">{{ $cellAmt($row->discount) }}</td>
                <td class="text-right">{{ $cellAmt($row->bilty) }}</td>
                <td class="text-right">{{ $cellAmt($row->other) }}</td>
                <td class="text-right">{{ $fmt($row->last_balance) }}</td>
            </tr>
        @empty
            <tr><td colspan="11" class="text-center">No records found.</td></tr>
        @endforelse
    </tbody>
    @if ($rows->isNotEmpty())
    <tfoot>
        <tr>
            <th colspan="3" class="text-right">Totals</th>
            <th class="text-right">{{ $fmt($totals['pre_balance'] ?? 0) }}</th>
            <th class="text-right">{{ $fmt($totals['delivery'] ?? 0) }}</th>
            <th class="text-right">{{ $fmt($totals['balance'] ?? 0) }}</th>
            <th class="text-right">{{ $fmt($totals['recovery'] ?? 0) }}</th>
            <th class="text-right">{{ $fmt($totals['discount'] ?? 0) }}</th>
            <th class="text-right">{{ $fmt($totals['bilty'] ?? 0) }}</th>
            <th class="text-right">{{ $fmt($totals['other'] ?? 0) }}</th>
            <th class="text-right">{{ $fmt($totals['last_balance'] ?? 0) }}</th>
        </tr>
    </tfoot>
    @endif
</table>
</body>
</html>
