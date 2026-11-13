<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sale Invoice - {{ $invoice->invoiceNo }}</title>
    @include('partials.doc-pdf-styles')
</head>
<body>
@php
    $buyerName = $invoice->customerName;
    if (($invoice->sale_type ?? 'credit') !== 'cash' && $invoice->customerCode && ! str_contains((string) $buyerName, (string) $invoice->customerCode)) {
        $buyerName .= ' ('.$invoice->customerCode.')';
    }
    $buyerPhone = optional($customer)->custContect ?? '';
    $buyerAddress = optional($customer)->custAddress ?? '';
    $buyerCity = $invoice->city ?: optional($customer)->custCity;
    $addressLine = trim($buyerAddress);
    if ($buyerCity) {
        $addressLine = trim($addressLine.' ('.$buyerCity.')');
    }
    $saleTypeLabel = (($invoice->sale_type ?? 'credit') === 'cash') ? 'Cash Sale' : 'Credit Sale';
    $isCashBanks = (($invoice->sale_type ?? 'credit') === 'cash') && $invoice->bankPayments->count() > 0;
    $hasDeliveryInfo = $invoice->gudsName || ($invoice->corten > 0);
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
            <div class="pdf-title">Invoice</div>
            <div class="pdf-type">{{ $saleTypeLabel }}</div>
            <div class="pdf-meta"><span class="k">No.</span>{{ $invoice->invoiceNo }}</div>
            <div class="pdf-meta"><span class="k">Date</span>{{ date('d.m.Y', strtotime($invoice->invoiceDate)) }}</div>
            @if(!empty($invoice->orderNo))
            <div class="pdf-meta"><span class="k">Order</span>{{ $invoice->orderNo }}</div>
            @endif
            <div class="pdf-meta"><span class="k">Items</span>{{ $invoice->noOfItems }}</div>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th style="width:4%;">#</th>
            <th>Product</th>
            <th style="width:14%;">Packing</th>
            <th class="text-right" style="width:8%;">Qty</th>
            <th class="text-right" style="width:12%;">Rate</th>
            <th class="text-right" style="width:10%;">Disc</th>
            <th class="text-right" style="width:13%;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items as $index => $item)
            @php
                $product = \App\Models\newProduct::where('productCode', $item->productCode)->first();
                $packing = $product ? $product->pakingName : 'N/A';
                $discountAmount = ($item->qty * $item->rate) * ($item->discount / 100);
                $company = trim((string) optional($product)->companyName);
                $productLabel = trim((string) $item->productName);
                if ($company !== '' && str_ends_with($productLabel, ' - '.$company)) {
                    $productLabel = trim(substr($productLabel, 0, -strlen(' - '.$company)));
                }
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $productLabel }}</strong>
                    @if($company !== '')<br>{{ $company }}@endif
                </td>
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
            @if($isCashBanks)
                <h6>Payment</h6>
                @foreach($invoice->bankPayments as $bp)
                    <p>
                        {{ optional($bp->bankAccount)->accountName ?? 'Bank' }}:
                        Rs. {{ number_format((float) $bp->amount, 2) }}
                    </p>
                @endforeach
            @else
                <h6>Delivery</h6>
                @if($invoice->gudsName)<p>Transport: {{ $invoice->gudsName }}</p>@endif
                @if($invoice->corten > 0)<p>Corten: {{ $invoice->corten }}</p>@endif
                @unless($hasDeliveryInfo)<p>—</p>@endunless
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
                <tr>
                    <td class="lbl">Discount</td>
                    <td class="amt">- {{ number_format($invoice->invoiceDiscount, 2) }}</td>
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
        <td><div class="sig-line">Customer Signature</div></td>
        <td style="text-align:right;"><div class="sig-line" style="margin-left:auto;">Authorized Signature</div></td>
    </tr>
</table>
<div class="thanks">Thank you for your business</div>
</body>
</html>
