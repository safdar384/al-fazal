<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Summary</title>
    @include('partials.report-pdf-styles')
</head>
<body>
@php
    $fmt = static fn ($n) => number_format((float) $n, 2);
    $totalAmount = (float) $invoices->sum('grandTotal');
    $totalPaid = (float) $invoices->sum('paid_amount');
    $totalDue = max(0, $totalAmount - $totalPaid);
    $count = $invoices->count();
    $avg = $count > 0 ? $totalAmount / $count : 0;
    $isReturnReport = request('invoice_type') === 'return' || request('purchase_type') === 'return';
    $fromLabel = request('fromDate') ? \Carbon\Carbon::parse(request('fromDate'))->format('d-M-Y') : 'Start';
    $toLabel = request('toDate') ? \Carbon\Carbon::parse(request('toDate'))->format('d-M-Y') : 'End';
    $supplierLabel = request('supplierCode') ? ($invoices->first()->supplierName ?? request('supplierCode')) : 'All';
@endphp

@include('components.invoiceHeaderPdf')

<div class="report-title">
    <h3>{{ $isReturnReport ? 'Return Purchase Summary Report' : 'Purchase Summary Report' }}</h3>
</div>

<table class="meta-bar">
    <tr>
        <td class="meta-box">
            <h6>Filters</h6>
            <table class="meta-grid">
                <tr>
                    <td class="lbl">Period</td>
                    <td class="val">
                        @if(request('fromDate') || request('toDate'))
                            {{ $fromLabel }} to {{ $toLabel }}
                        @else
                            All Time
                        @endif
                    </td>
                </tr>
                <tr><td class="lbl">Supplier</td><td class="val">{{ $supplierLabel }}</td></tr>
            </table>
        </td>
        <td class="meta-box">
            <h6>Report</h6>
            <table class="meta-grid">
                <tr><td class="lbl">Generated</td><td class="val">{{ now()->format('d-M-Y h:i A') }}</td></tr>
                <tr><td class="lbl">Invoices</td><td class="val">{{ $count }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="summary-stats">
    <tr>
        <td class="stat-box">
            <span class="stat-label">Invoices</span>
            <span class="stat-value">{{ $count }}</span>
        </td>
        <td class="stat-box">
            <span class="stat-label">Total Amount</span>
            <span class="stat-value">Rs. {{ $fmt($totalAmount) }}</span>
        </td>
        <td class="stat-box">
            <span class="stat-label">Paid</span>
            <span class="stat-value">Rs. {{ $fmt($totalPaid) }}</span>
        </td>
        <td class="stat-box">
            <span class="stat-label">Due</span>
            <span class="stat-value">Rs. {{ $fmt($totalDue) }}</span>
        </td>
        <td class="stat-box">
            <span class="stat-label">Avg. Invoice</span>
            <span class="stat-value">Rs. {{ $fmt($avg) }}</span>
        </td>
    </tr>
</table>

<table class="ledger-table">
    <thead>
        <tr>
            <th style="width:4%;">#</th>
            <th style="width:12%;">Invoice</th>
            <th style="width:10%;">Date</th>
            <th style="width:28%;">Supplier</th>
            <th style="width:12%;">Total</th>
            <th style="width:12%;">Paid</th>
            <th style="width:12%;">Balance</th>
            <th style="width:10%;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($invoices as $idx => $inv)
            @php
                $total = (float) ($inv->grandTotal ?? 0);
                $paid = (float) ($inv->paid_amount ?? 0);
            @endphp
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-center">{{ $inv->invoiceNo ?? $inv->returnNo ?? '—' }}</td>
                <td class="text-center">{{ !empty($inv->invoiceDate) ? \Carbon\Carbon::parse($inv->invoiceDate)->format('d-M-Y') : '—' }}</td>
                <td class="cell-desc">{{ $inv->supplierName ?? '—' }}</td>
                <td class="text-end">{{ $fmt($total) }}</td>
                <td class="amt-credit">{{ $fmt($paid) }}</td>
                <td class="amt-debit">{{ $fmt(max(0, $total - $paid)) }}</td>
                <td class="text-center">{{ $inv->status ?? '—' }}</td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="8">No records found.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="report-footer">Computer-generated report · © {{ date('Y') }} Al Fazal Traders</div>
</body>
</html>
