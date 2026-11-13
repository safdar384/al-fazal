<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supplier Ledger — {{ $selectedSupplierName }}</title>
    @include('partials.report-pdf-styles')
</head>
<body>
@php
    $fmt = static fn ($value) => $value === null || $value === '' ? '—' : number_format((float) $value, 2);
    $fmtAmt = static function ($value) {
        $n = (float) ($value ?? 0);
        return $n > 0 ? number_format($n, 2) : '—';
    };
    $balanceLabel = ($summary ?? [])['balance_label'] ?? ($balanceLabel ?? 'Payable Balance');
    $typeLabels = [
        'purchase' => 'Purchase',
        'sale' => 'Sale',
        'payment' => 'Payment',
        'discount' => 'Discount',
        'other' => 'Other',
        'Opening' => 'Opening',
    ];
    $periodText = $periodText ?? (($fromDate ?? '').' to '.($toDate ?? ''));
@endphp

@include('components.invoiceHeaderPdf')

<div class="report-title">
    <h3>Supplier Ledger Report</h3>
</div>

<table class="meta-bar">
    <tr>
        <td class="meta-box">
            <h6>Supplier</h6>
            <table class="meta-grid">
                <tr><td class="lbl">Code</td><td class="val">{{ $selectedSupplierCode }}</td></tr>
                <tr><td class="lbl">Name</td><td class="val">{{ $selectedSupplierName }}</td></tr>
            </table>
        </td>
        <td class="meta-box">
            <h6>Report</h6>
            <table class="meta-grid">
                <tr><td class="lbl">Print Date</td><td class="val">{{ now()->format('d-M-Y') }}</td></tr>
                <tr><td class="lbl">Period</td><td class="val">{{ $periodText }}</td></tr>
                @if (($transactionType ?? 'all') !== 'all')
                    <tr><td class="lbl">Filter</td><td class="val">{{ $typeLabels[$transactionType] ?? $transactionType }}</td></tr>
                @endif
                <tr><td class="lbl">Closing</td><td class="val">Rs. {{ $fmt(($summary ?? [])['closing_balance'] ?? 0) }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="ledger-table">
    <thead>
        <tr>
            <th style="width:8%;">Date</th>
            <th style="width:10%;">Invoice</th>
            <th style="width:8%;">Type</th>
            <th style="width:26%;">Product</th>
            <th style="width:7%;">Qty</th>
            <th style="width:9%;">Rate</th>
            <th style="width:10%;">Debit</th>
            <th style="width:10%;">Credit</th>
            <th style="width:12%;">{{ $balanceLabel }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($ledgerRows as $row)
            @php
                $typeKey = (string) ($row['transaction_type'] ?? '');
                $typeLabel = $typeLabels[$typeKey] ?? ucfirst($typeKey ?: '—');
            @endphp
            <tr>
                <td class="text-center">{{ $row['date'] ?? '—' }}</td>
                <td class="text-center">{{ $row['invoice_no'] ?? '—' }}</td>
                <td class="text-center">{{ $typeLabel }}</td>
                <td class="cell-desc">{{ $row['product_name'] ?? '—' }}</td>
                <td class="text-end">{{ $fmt($row['qty'] ?? null) }}</td>
                <td class="text-end">{{ $fmt($row['final_rate'] ?? $row['rate'] ?? null) }}</td>
                <td class="amt-debit">{{ $fmtAmt($row['debit'] ?? 0) }}</td>
                <td class="amt-credit">{{ $fmtAmt($row['credit'] ?? 0) }}</td>
                <td class="amt-balance">{{ $fmt($row['running_balance'] ?? 0) }}</td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="9">No transactions in the selected period.</td></tr>
        @endforelse
    </tbody>
</table>

@if ($summary)
<table class="summary-section">
    <tr>
        <td class="summary-item debit">
            <small>Total Debit</small>
            <div class="val">Rs. {{ $fmt($summary['total_debit']) }}</div>
        </td>
        <td class="summary-item credit">
            <small>Total Credit</small>
            <div class="val">Rs. {{ $fmt($summary['total_credit']) }}</div>
        </td>
        <td class="summary-item balance">
            <small>Closing {{ $balanceLabel }}</small>
            <div class="val">Rs. {{ $fmt($summary['closing_balance']) }}</div>
        </td>
    </tr>
</table>
@endif

<div class="report-footer">Computer-generated report · © {{ date('Y') }} Al Fazal Traders</div>
</body>
</html>
