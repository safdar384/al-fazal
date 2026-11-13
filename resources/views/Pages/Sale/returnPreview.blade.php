<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Return - {{ $saleReturn->returnNo }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.invoice-doc-styles')
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

    <div class="invoice-container">
        @include('components.invoiceHeader')

        <div class="bill-row">
            <div>
                <div class="sec-label">Bill To</div>
                <div class="bill-name">{{ $buyerName }}</div>
                @if($buyerPhone)
                <div class="bill-line">{{ $buyerPhone }}</div>
                @endif
                @if($addressLine !== '')
                <div class="bill-line">{{ $addressLine }}</div>
                @endif
            </div>
            <div class="inv-side">
                <h1 class="inv-title">RETURN</h1>
                <div class="inv-type">Sale Return</div>
                <div class="inv-meta">
                    <div><span class="k">No.</span><span class="v">{{ $saleReturn->returnNo }}</span></div>
                    <div><span class="k">Date</span><span class="v">{{ \Carbon\Carbon::parse($saleReturn->returnDate)->format('d.m.Y') }}</span></div>
                    @if($saleReturn->originalInvoiceNo)
                    <div><span class="k">Invoice</span><span class="v">{{ $saleReturn->originalInvoiceNo }}</span></div>
                    @endif
                    <div><span class="k">Items</span><span class="v">{{ (int) $saleReturn->noOfItems }}</span></div>
                </div>
            </div>
        </div>

        <table class="items-table">
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
                        <td class="text-center {{ $cond === 'damage' ? 'cond-damage' : 'cond-good' }}">
                            {{ $cond === 'damage' ? 'Damage' : 'Good' }}
                        </td>
                        <td class="text-right">{{ $fmt($item->qty) }}</td>
                        <td class="text-right">{{ $fmt($item->rate) }}</td>
                        <td class="text-right">{{ $fmt($item->discount) }}</td>
                        <td class="text-right"><strong>{{ $fmt($item->amount) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="bottom-row">
            <div class="notes-box">
                <h6>Summary</h6>
                <p>Good Qty: <strong>{{ $fmt($saleReturn->goodQtyTotal) }}</strong></p>
                <p>Damage Qty: <strong>{{ $fmt($saleReturn->damageQtyTotal) }}</strong></p>
                @if ($saleReturn->comments)
                    <h6 style="margin-top:0.55rem;">Notes</h6>
                    <p>{{ $saleReturn->comments }}</p>
                @endif
            </div>
            <div class="totals-box">
                <div class="total-row">
                    <span>Subtotal</span>
                    <strong>{{ $fmt($saleReturn->invoiceTotal) }}</strong>
                </div>
                @if ((float) $saleReturn->invoiceDiscount > 0)
                <div class="total-row">
                    <span>Discount</span>
                    <strong>- {{ $fmt($saleReturn->invoiceDiscount) }}</strong>
                </div>
                @endif
                <div class="total-row grand-total">
                    <span>Total Credit</span>
                    <strong>Rs. {{ $fmt($saleReturn->grandTotal) }}</strong>
                </div>
            </div>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Customer Signature</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Authorized Signature</div>
            </div>
        </div>

        <div class="thanks-line">Thank you for your business</div>
    </div>

    <a href="{{ route('sale.return') }}" class="btn btn-secondary btn-back no-print">← Back</a>
    <a href="{{ route('sale.return.pdf', $saleReturn->returnNo) }}" class="btn btn-pdf no-print">PDF</a>
    <button type="button" onclick="window.print()" class="btn btn-primary btn-print no-print">🖨️ Print</button>
</body>
</html>
