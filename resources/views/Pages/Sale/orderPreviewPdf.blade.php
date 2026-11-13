<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Order - {{ $order->orderNo }}</title>
    @include('partials.doc-pdf-styles')
</head>
<body>
@php
    $buyerName = $order->customerName;
    if ($order->customerCode && ! str_contains((string) $buyerName, (string) $order->customerCode)) {
        $buyerName .= ' ('.$order->customerCode.')';
    }
@endphp

@include('components.invoiceHeaderPdf')

<table class="bill-table">
    <tr>
        <td>
            <div class="sec-label">Bill To</div>
            <div class="bill-name">{{ $buyerName }}</div>
            @if($order->city)<div class="bill-line">{{ $order->city }}</div>@endif
        </td>
        <td class="right">
            <div class="pdf-title">Order</div>
            <div class="pdf-type">{{ $order->status ?? 'Pending' }}</div>
            <div class="pdf-meta"><span class="k">No.</span>{{ $order->orderNo }}</div>
            <div class="pdf-meta"><span class="k">Date</span>{{ \Carbon\Carbon::parse($order->orderDate)->format('d.m.Y') }}</div>
            <div class="pdf-meta"><span class="k">Items</span>{{ $order->items->count() }}</div>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th style="width:4%;">#</th>
            <th>Product</th>
            <th class="text-right" style="width:10%;">Qty</th>
            <th class="text-right" style="width:12%;">Rate</th>
            <th class="text-right" style="width:12%;">Disc %</th>
            <th class="text-right" style="width:14%;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($order->items as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->productName }} ({{ $item->productCode }})</td>
            <td class="text-right">{{ number_format((float) $item->qty, 0) }}</td>
            <td class="text-right">{{ number_format((float) $item->rate, 2) }}</td>
            <td class="text-right">{{ number_format((float) $item->discount, 2) }}</td>
            <td class="text-right"><strong>{{ number_format((float) $item->amount, 2) }}</strong></td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="bottom-table">
    <tr>
        <td class="notes-box">
            <h6>Delivery</h6>
            <p>Transport: <strong>{{ $order->gudsName ?: '—' }}</strong></p>
            @if($order->notes || $order->comments)
                <h6 style="margin-top:6px;">Notes</h6>
                <p>{{ $order->notes ?: $order->comments }}</p>
            @endif
            @if($order->converted_invoice_no)
                <h6 style="margin-top:6px;">Converted</h6>
                <p>Invoice: {{ $order->converted_invoice_no }}</p>
            @endif
            <p style="margin-top:6px;">Demand only — no stock/ledger until converted to Sale Invoice.</p>
        </td>
        <td style="width:2%;"></td>
        <td class="totals-box">
            <table class="total-row">
                <tr>
                    <td class="lbl">Subtotal</td>
                    <td class="amt">{{ number_format((float) $order->orderTotal, 2) }}</td>
                </tr>
                @if((float) $order->orderDiscount > 0)
                <tr>
                    <td class="lbl">Discount</td>
                    <td class="amt">- {{ number_format((float) $order->orderDiscount, 2) }}</td>
                </tr>
                @endif
            </table>
            <table class="total-row grand">
                <tr>
                    <td class="lbl">Total</td>
                    <td class="amt">Rs. {{ number_format((float) $order->grandTotal, 2) }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="sig-table">
    <tr>
        <td><div class="sig-line">Customer Signature</div></td>
        <td style="text-align:right;"><div class="sig-line" style="margin-left:auto;">Authorized Signature</div></td>
    </tr>
</table>
<div class="thanks">Thank you for your business</div>
</body>
</html>
