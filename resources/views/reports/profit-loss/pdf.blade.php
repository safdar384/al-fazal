<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profit &amp; Loss Statement</title>
    @include('partials.doc-pdf-styles')
    <style>
        .pl-row { width: 100%; border-collapse: collapse; margin-top: 2px; }
        .pl-row td { border-bottom: 1px solid #000; padding: 5px 4px; color: #000; font-size: 11px; }
        .pl-row .amt { text-align: right; font-weight: 700; }
        .pl-row.sub td { font-weight: 700; }
        .pl-row.net td {
            border-top: 2px solid #000;
            border-bottom: 2.5px double #000;
            font-weight: 700;
            font-size: 12px;
            padding-top: 6px;
        }
        .hint { display: block; font-size: 8px; font-weight: 400; color: #000; }
        .cards { width: 100%; border-collapse: collapse; margin: 8px 0 10px; }
        .cards td { border: 1px solid #000; padding: 6px; text-align: center; color: #000; }
        .cards .lbl { font-size: 8px; text-transform: uppercase; }
        .cards .val { font-size: 12px; font-weight: 700; margin-top: 2px; }
    </style>
</head>
<body>
@php
    $fmt = static fn ($n) => number_format((float) $n, 2);
@endphp

@include('components.invoiceHeaderPdf')

<div class="report-h1">Profit &amp; Loss Statement</div>
<div class="report-meta">
    Period: {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}
</div>

<table class="cards">
    <tr>
        <td>
            <div class="lbl">Sales</div>
            <div class="val">{{ $fmt($figures['sale_amount'] ?? 0) }}</div>
        </td>
        <td>
            <div class="lbl">Gross Profit</div>
            <div class="val">{{ $fmt($figures['gross_profit'] ?? 0) }}</div>
        </td>
        <td>
            <div class="lbl">Net Profit</div>
            <div class="val">{{ $fmt($figures['net_profit'] ?? 0) }}</div>
        </td>
    </tr>
</table>

<table class="pl-row">
    <tr>
        <td>Sales (invoice total)</td>
        <td class="amt">{{ $fmt($figures['sale_amount'] ?? 0) }}</td>
    </tr>
</table>
<table class="pl-row">
    <tr>
        <td>Cost of goods sold</td>
        <td class="amt">({{ $fmt($figures['cost_amount'] ?? 0) }})</td>
    </tr>
</table>
<table class="pl-row sub">
    <tr>
        <td>Gross profit</td>
        <td class="amt">{{ $fmt($figures['gross_profit'] ?? 0) }}</td>
    </tr>
</table>
<table class="pl-row">
    <tr>
        <td>Sale bilty / loader</td>
        <td class="amt">{{ $fmt($figures['sale_bilty_amount'] ?? 0) }}</td>
    </tr>
</table>
<table class="pl-row">
    <tr>
        <td>Expenses (ledger)</td>
        <td class="amt">({{ $fmt($figures['expense_amount'] ?? 0) }})</td>
    </tr>
</table>
<table class="pl-row">
    <tr>
        <td>
            Voucher Discount
            <span class="hint">Discount {{ $fmt($figures['ledger_discount_amount'] ?? 0) }} · Bilty {{ $fmt($figures['ledger_bilty_amount'] ?? 0) }}</span>
        </td>
        <td class="amt">({{ $fmt($figures['voucher_discount_amount'] ?? 0) }})</td>
    </tr>
</table>
<table class="pl-row net">
    <tr>
        <td>
            Net profit
            <span class="hint">Purchases in period (reference): {{ $fmt($figures['purchase_amount'] ?? 0) }}</span>
        </td>
        <td class="amt">{{ $fmt($figures['net_profit'] ?? 0) }}</td>
    </tr>
</table>
</body>
</html>
