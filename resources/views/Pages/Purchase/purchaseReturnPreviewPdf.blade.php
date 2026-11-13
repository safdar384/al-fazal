<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Return - {{ $purchaseReturn->returnNo }}</title>
    @include('partials.doc-pdf-styles')
</head>
<body>
@php
    $fmt = static fn ($n) => number_format((float) $n, 2);
    $supplierName = $purchaseReturn->supplierName;
    if ($purchaseReturn->supplierCode && ! str_contains((string) $supplierName, (string) $purchaseReturn->supplierCode)) {
        $supplierName .= ' ('.$purchaseReturn->supplierCode.')';
    }
    $supplierPhone = optional($supplier)->custContect ?? '';
    $supplierCity = optional($supplier)->custCity ?? '';
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
            <div class="pdf-title">Return</div>
            <div class="pdf-type">Purchase Return</div>
            <div class="pdf-meta"><span class="k">No.</span>{{ $purchaseReturn->returnNo }}</div>
            <div class="pdf-meta"><span class="k">Date</span>{{ \Carbon\Carbon::parse($purchaseReturn->returnDate)->format('d.m.Y') }}</div>
            @if($purchaseReturn->originalInvoiceNo)
            <div class="pdf-meta"><span class="k">Invoice</span>{{ $purchaseReturn->originalInvoiceNo }}</div>
            @endif
            <div class="pdf-meta"><span class="k">Items</span>{{ (int) $purchaseReturn->noOfItems }}</div>
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
        @foreach ($purchaseReturn->items as $index => $item)
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
            <p>Good Qty: <strong>{{ $fmt($purchaseReturn->goodQtyTotal) }}</strong></p>
            <p>Damage Qty: <strong>{{ $fmt($purchaseReturn->damageQtyTotal) }}</strong></p>
            @if ($purchaseReturn->comments)
                <h6 style="margin-top:6px;">Notes</h6>
                <p>{{ $purchaseReturn->comments }}</p>
            @endif
        </td>
        <td style="width:2%;"></td>
        <td class="totals-box">
            <table class="total-row">
                <tr>
                    <td class="lbl">Subtotal</td>
                    <td class="amt">{{ $fmt($purchaseReturn->invoiceTotal) }}</td>
                </tr>
                @if ((float) $purchaseReturn->invoiceDiscount > 0)
                <tr>
                    <td class="lbl">Discount</td>
                    <td class="amt">- {{ $fmt($purchaseReturn->invoiceDiscount) }}</td>
                </tr>
                @endif
            </table>
            <table class="total-row grand">
                <tr>
                    <td class="lbl">Total Debit</td>
                    <td class="amt">Rs. {{ $fmt($purchaseReturn->grandTotal) }}</td>
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
