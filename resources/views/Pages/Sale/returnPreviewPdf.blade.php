<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sale Return - {{ $saleReturn->returnNo }}</title>
    @include('partials.doc-pdf-styles')
</head>
<body>
@php
    $fmt = static fn ($n) => number_format((float) $n, 2);
    $buyerName = $saleReturn->customerName;
    if ($saleReturn->customerCode && ! str_contains((string) $buyerName, (string) $saleReturn->customerCode)) {
        $buyerName .= ' ('.$saleReturn->customerCode.')';
    }
    $buyerPhone = optional($customer)->custContect ?? '';
    $buyerAddress = optional($customer)->custAddress ?? '';
    $buyerCity = $saleReturn->city ?: optional($customer)->custCity;
    $addressLine = trim($buyerAddress.($buyerCity ? ' ('.$buyerCity.')' : ''));
@endphp

@include('components.invoiceHeaderPdf')

<table class="bill-table">
    <tr>
        <td>
            <div class="sec-label">Bill To</div>
            <div class="bill-name">{{ $buyerName }}</div>
            @if($buyerPhone)<div class="bill-line">{{ $buyerPhone }}</div>@endif
            @if($addressLine !== '')<div class="bill-line">{{ $addressLine }}</div>@endif
        </td>
        <td class="right">
            <div class="pdf-title">Return</div>
            <div class="pdf-type">Sale Return</div>
            <div class="pdf-meta"><span class="k">No.</span>{{ $saleReturn->returnNo }}</div>
            <div class="pdf-meta"><span class="k">Date</span>{{ \Carbon\Carbon::parse($saleReturn->returnDate)->format('d.m.Y') }}</div>
            @if($saleReturn->originalInvoiceNo)
            <div class="pdf-meta"><span class="k">Invoice</span>{{ $saleReturn->originalInvoiceNo }}</div>
            @endif
            <div class="pdf-meta"><span class="k">Items</span>{{ (int) $saleReturn->noOfItems }}</div>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th style="width:4%;">#</th>
            <th>Product</th>
            <th style="width:12%;">Variant</th>
            <th class="text-center" style="width:10%;">Condition</th>
            <th class="text-right" style="width:8%;">Qty</th>
            <th class="text-right" style="width:10%;">Rate</th>
            <th class="text-right" style="width:8%;">Disc %</th>
            <th class="text-right" style="width:12%;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($saleReturn->items as $index => $item)
            @php
                $cond = strtolower((string) ($item->condition ?? 'good'));
                $variant = trim(implode(' ', array_filter([
                    $item->variant_name,
                    $item->variant_value ? '(' . $item->variant_value . ')' : null,
                ])));
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->productName }}</td>
                <td class="text-center">{{ $variant !== '' ? $variant : '—' }}</td>
                <td class="text-center">{{ $cond === 'damage' ? 'Damage' : 'Good' }}</td>
                <td class="text-right">{{ $fmt($item->qty) }}</td>
                <td class="text-right">{{ $fmt($item->rate) }}</td>
                <td class="text-right">{{ $fmt($item->discount) }}</td>
                <td class="text-right"><strong>{{ $fmt($item->amount) }}</strong></td>
            </tr>
        @endforeach
    </tbody>
</table>

<table class="bottom-table">
    <tr>
        <td class="notes-box">
            <h6>Summary</h6>
            <p>Good Qty: <strong>{{ $fmt($saleReturn->goodQtyTotal) }}</strong></p>
            <p>Damage Qty: <strong>{{ $fmt($saleReturn->damageQtyTotal) }}</strong></p>
            @if ($saleReturn->comments)
                <h6 style="margin-top:6px;">Notes</h6>
                <p>{{ $saleReturn->comments }}</p>
            @endif
        </td>
        <td style="width:2%;"></td>
        <td class="totals-box">
            <table class="total-row">
                <tr>
                    <td class="lbl">Subtotal</td>
                    <td class="amt">{{ $fmt($saleReturn->invoiceTotal) }}</td>
                </tr>
                @if ((float) $saleReturn->invoiceDiscount > 0)
                <tr>
                    <td class="lbl">Discount</td>
                    <td class="amt">- {{ $fmt($saleReturn->invoiceDiscount) }}</td>
                </tr>
                @endif
            </table>
            <table class="total-row grand">
                <tr>
                    <td class="lbl">Total Credit</td>
                    <td class="amt">Rs. {{ $fmt($saleReturn->grandTotal) }}</td>
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
