<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Summary</title>
    @include('partials.report-pdf-styles')
</head>
<body>
@php
    $fmt = static fn ($n) => number_format((float) $n, 2);
    $count = count($orders);
    $totalGrand = (float) collect($orders)->sum('grandTotal');
    $fromLabel = request('fromDate') ? \Carbon\Carbon::parse(request('fromDate'))->format('d-M-Y') : 'Start';
    $toLabel = request('toDate') ? \Carbon\Carbon::parse(request('toDate'))->format('d-M-Y') : 'End';
    $customerLabel = request('customerCode') ?: 'All';
    $statusLabel = request('status') ?: 'All';
    $areaLabel = request('area') ?: 'All';
@endphp

@include('components.invoiceHeaderPdf')

<div class="report-title">
    <h3>Order Summary Report</h3>
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
                <tr><td class="lbl">Status</td><td class="val">{{ $statusLabel }}</td></tr>
            </table>
        </td>
        <td class="meta-box">
            <h6>Report</h6>
            <table class="meta-grid">
                <tr><td class="lbl">Generated</td><td class="val">{{ now()->format('d-M-Y h:i A') }}</td></tr>
                <tr><td class="lbl">Orders</td><td class="val">{{ $count }}</td></tr>
                <tr><td class="lbl">Total</td><td class="val">Rs. {{ $fmt($totalGrand) }}</td></tr>
            </table>
        </td>
    </tr>
</table>

@if(!empty($statusTotals) && is_iterable($statusTotals))
<table class="summary-stats">
    <tr>
        @foreach($statusTotals as $st => $amt)
            <td class="stat-box">
                <span class="stat-label">{{ is_string($st) ? $st : 'Status' }}</span>
                <span class="stat-value">{{ is_numeric($amt) ? 'Rs. '.$fmt($amt) : $amt }}</span>
            </td>
        @endforeach
    </tr>
</table>
@endif

<table class="ledger-table">
    <thead>
        <tr>
            <th style="width:4%;">#</th>
            <th style="width:12%;">Order No</th>
            <th style="width:10%;">Date</th>
            <th style="width:28%;">Customer</th>
            <th style="width:12%;">Status</th>
            <th style="width:10%;">Items</th>
            <th style="width:12%;">Total</th>
            <th style="width:12%;">Grand</th>
        </tr>
    </thead>
    <tbody>
        @forelse($orders as $idx => $order)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-center">{{ $order->orderNo ?? '—' }}</td>
                <td class="text-center">{{ !empty($order->orderDate) ? \Carbon\Carbon::parse($order->orderDate)->format('d-M-Y') : '—' }}</td>
                <td class="cell-desc">{{ $order->customerName ?? '—' }}</td>
                <td class="text-center">{{ $order->status ?? '—' }}</td>
                <td class="text-end">{{ $order->noOfItems ?? ($order->items_count ?? '—') }}</td>
                <td class="text-end">{{ $fmt($order->orderTotal ?? 0) }}</td>
                <td class="amt-balance">{{ $fmt($order->grandTotal ?? 0) }}</td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="8">No orders found.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="report-footer">Computer-generated report · © {{ date('Y') }} Al Fazal Traders</div>
</body>
</html>
