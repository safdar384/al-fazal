<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Invoice - {{ $invoice->invoiceNo }}</title>
    @include('partials.doc-pdf-styles')
</head>
<body>
@php
    $supplierName = $invoice->supplierName;
    if ($invoice->supplierCode && ! str_contains((string) $supplierName, (string) $invoice->supplierCode)) {
        $supplierName .= ' ('.$invoice->supplierCode.')';
    }
    $supplierPhone = optional($supplier)->custContect ?? '';
    $supplierCity = optional($supplier)->custCity ?? '';
    $hasTransportBlock = ($transportDisplayName ?? null)
        || $invoice->vehicleNumber
        || $invoice->dispatchNo
        || $invoice->weight;
@endphp

@include('components.invoiceHeaderPdf')

<table class="bill-table">
    <tr>
        <td>
            <div class="sec-label">Supplier</div>
            <div class="bill-name">{{ $supplierName }}</div>
            @if($supplierPhone)<div class="bill-line">{{ $supplierPhone }}</div>@endif
            @if($supplierCity)<div class="bill-line">{{ $supplierCity }}</div>@endif
        </td>
        <td class="right">
            <div class="pdf-title">Invoice</div>
            <div class="pdf-type">Purchase</div>
            <div class="pdf-meta"><span class="k">No.</span>{{ $invoice->invoiceNo }}</div>
            <div class="pdf-meta"><span class="k">Date</span>{{ date('d.m.Y', strtotime($invoice->invoiceDate)) }}</div>
            <div class="pdf-meta"><span class="k">Status</span>{{ $invoice->status }}</div>
            <div class="pdf-meta"><span class="k">Items</span>{{ $invoice->items->count() }}</div>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th style="width:4%;">#</th>
            <th>Product</th>
            <th style="width:16%;">Packing</th>
            <th class="text-right" style="width:8%;">Qty</th>
            <th class="text-right" style="width:12%;">Rate</th>
            <th class="text-right" style="width:10%;">Disc</th>
            <th class="text-right" style="width:14%;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items as $index => $item)
            @php
                $product = \App\Models\newProduct::where('productCode', $item->productCode)->first();
                $packName = trim((string) ($product->pakingName ?? ''));
                $packing = $packName !== '' ? $packName : 'N/A';
                if ($product) {
                    $kgEach = (float) $product->kgEach();
                    if ($kgEach > 0) {
                        $lineQty = (float) ($item->qty ?? 0);
                        $totalKg = $lineQty * $kgEach;
                        $kgLabel = fmod($totalKg, 1.0) === 0.0
                            ? (string) (int) $totalKg
                            : rtrim(rtrim(number_format($totalKg, 2, '.', ''), '0'), '.');
                        $packing = ($packName !== '' ? $packName : 'BAG').' ('.$kgLabel.' KG)';
                    }
                }
                $discountAmount = ($item->qty * $item->rate) * ($item->discount / 100);
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $item->productName }}</strong></td>
                <td>{{ $packing }}</td>
                <td class="text-right">{{ number_format($item->qty) }}</td>
                <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                <td class="text-right">{{ number_format($discountAmount, 0) }}</td>
                <td class="text-right"><strong>{{ number_format($item->amount, 2) }}</strong></td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="bottom-table">
    <tr>
        <td class="notes-box">
            @if($hasTransportBlock)
                <h6>Transport</h6>
                <p>Transport: <strong>{{ ($transportDisplayName ?? null) ?: '-' }}</strong></p>
                <p>Vehicle: <strong>{{ $invoice->vehicleNumber ?: '-' }}</strong></p>
                <p>Dispatch No: <strong>{{ $invoice->dispatchNo ?: '-' }}</strong></p>
                <p>Weight: <strong>{{ $invoice->weight !== null ? rtrim(rtrim(number_format((float) $invoice->weight, 2), '0'), '.').' kg' : '-' }}</strong></p>
            @else
                <h6>Notes</h6>
                <p>—</p>
            @endif
            @if($invoice->comments)
                <h6 style="margin-top:6px;">Notes</h6>
                <p>{{ $invoice->comments }}</p>
            @endif
        </td>
        <td style="width:2%;"></td>
        <td class="totals-box">
            <table class="total-row">
                <tr>
                    <td class="lbl">Subtotal</td>
                    <td class="amt">{{ number_format($invoice->invoiceTotal, 2) }}</td>
                </tr>
                @if($invoice->invoiceDiscount > 0)
                @php
                    $discountPercent = $invoice->invoiceDiscount;
                    $discountAmount = $invoice->invoiceTotal * ($discountPercent / 100);
                @endphp
                <tr>
                    <td class="lbl">Discount ({{ number_format($discountPercent, 2) }}%)</td>
                    <td class="amt">- {{ number_format($discountAmount, 0) }}</td>
                </tr>
                @endif
            </table>
            <table class="total-row grand">
                <tr>
                    <td class="lbl">Total</td>
                    <td class="amt">Rs. {{ number_format($invoice->grandTotal, 2) }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="sig-table">
    <tr>
        <td><div class="sig-line">Supplier Signature</div></td>
        <td style="text-align:right;"><div class="sig-line" style="margin-left:auto;">Authorized Signature</div></td>
    </tr>
</table>
<div class="thanks">Thank you for your business</div>
</body>
</html>
