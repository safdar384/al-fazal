<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sale Summary</title>
    @include('partials.report-pdf-styles')
</head>
<body>
@php
    $fmt = static fn ($n) => number_format((float) $n, 2);
    $count = $invoices->count();
    $totalAmount = (float) $invoices->sum('grandTotal');
    $cashSaleAmount = (float) $invoices->where('sale_type', 'cash')->sum('grandTotal');
    $creditSaleAmount = $totalAmount - $cashSaleAmount;
    $totalItems = (int) $invoices->sum('noOfItems');
    $totalCorten = (int) $invoices->sum('corten');
    $avg = $count > 0 ? $totalAmount / $count : 0;
    $isReturn = request('sale_type') === 'return';
    $fromLabel = request('fromDate') ? \Carbon\Carbon::parse(request('fromDate'))->format('d-M-Y') : 'Start';
    $toLabel = request('toDate') ? \Carbon\Carbon::parse(request('toDate'))->format('d-M-Y') : 'End';
    $customerLabel = request('customerCode') ? ($invoices->first()->customerName ?? request('customerCode')) : 'All';
    $areaLabel = request('area') ?: 'All';
    $typeLabel = match (request('sale_type')) {
        'cash' => 'Cash Sale',
        'credit' => 'Credit Sale',
        'return' => 'Return Sale',
        default => 'All',
    };
@endphp

@include('components.invoiceHeaderPdf')

<div class="report-title">
    <h3>{{ $isReturn ? 'Return Sale Summary Report' : 'Sale Summary Report' }}</h3>
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
                <tr><td class="lbl">Customer</td><td class="val">{{ $customerLabel }}</td></tr>
                <tr><td class="lbl">Area</td><td class="val">{{ $areaLabel }}</td></tr>
                <tr><td class="lbl">Type</td><td class="val">{{ $typeLabel }}</td></tr>
            </table>
        </td>
        <td class="meta-box">
            <h6>Report</h6>
            <table class="meta-grid">
                <tr><td class="lbl">Generated</td><td class="val">{{ now()->format('d-M-Y h:i A') }}</td></tr>
                <tr><td class="lbl">Invoices</td><td class="val">{{ $count }}</td></tr>
                <tr><td class="lbl">Items</td><td class="val">{{ number_format($totalItems) }}</td></tr>
                <tr><td class="lbl">Corten</td><td class="val">{{ number_format($totalCorten) }}</td></tr>
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
            <span class="stat-label">Credit Sale</span>
            <span class="stat-value">Rs. {{ $fmt($creditSaleAmount) }}</span>
        </td>
        <td class="stat-box">
            <span class="stat-label">Cash Sale</span>
            <span class="stat-value">Rs. {{ $fmt($cashSaleAmount) }}</span>
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
            <th style="width:10%;">Invoice</th>
            <th style="width:9%;">Order</th>
            <th style="width:8%;">Date</th>
            <th style="width:22%;">Customer</th>
            <th style="width:8%;">Type</th>
            <th style="width:7%;">Items</th>
            <th style="width:10%;">Total</th>
            <th style="width:10%;">Discount</th>
            <th style="width:12%;">Grand</th>
        </tr>
    </thead>
    <tbody>
        @forelse($invoices as $idx => $inv)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-center">{{ $inv->invoiceNo ?? $inv->returnNo ?? '—' }}</td>
                <td class="text-center">{{ $inv->orderNo ?? '—' }}</td>
                <td class="text-center">{{ !empty($inv->invoiceDate) ? \Carbon\Carbon::parse($inv->invoiceDate)->format('d-M-Y') : '—' }}</td>
                <td class="cell-desc">{{ $inv->customerName ?? '—' }}</td>
                <td class="text-center">{{ $inv->sale_type ?? ($inv->invoice_type ?? '—') }}</td>
                <td class="text-end">{{ $inv->noOfItems ?? ($inv->items_count ?? '—') }}</td>
                <td class="text-end">{{ $fmt($inv->invoiceTotal ?? 0) }}</td>
                <td class="text-end">{{ $fmt($inv->invoiceDiscount ?? 0) }}</td>
                <td class="amt-balance">{{ $fmt($inv->grandTotal ?? 0) }}</td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="10">No records found.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="report-footer">Computer-generated report · © {{ date('Y') }} Al Fazal Traders</div>
</body>
</html>
