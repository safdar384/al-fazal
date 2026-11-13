<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Invoice - {{ $invoice->invoiceNo }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.invoice-doc-styles')
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

    <div class="invoice-container">
        @include('components.invoiceHeader')

        <div class="bill-row">
            <div>
                <div class="sec-label">Supplier</div>
                <div class="bill-name">{{ $supplierName }}</div>
                @if($supplierPhone)
                <div class="bill-line">{{ $supplierPhone }}</div>
                @endif
                @if($supplierCity)
                <div class="bill-line">{{ $supplierCity }}</div>
                @endif
            </div>
            <div class="inv-side">
                <h1 class="inv-title">INVOICE</h1>
                <div class="inv-type">Purchase</div>
                <div class="inv-meta">
                    <div><span class="k">No.</span><span class="v">{{ $invoice->invoiceNo }}</span></div>
                    <div><span class="k">Date</span><span class="v">{{ date('d.m.Y', strtotime($invoice->invoiceDate)) }}</span></div>
                    <div><span class="k">Status</span><span class="v">{{ $invoice->status }}</span></div>
                    <div><span class="k">Items</span><span class="v">{{ $invoice->items->count() }}</span></div>
                </div>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th>Product</th>
                    <th style="width: 14%;">Packing</th>
                    <th class="text-right" style="width: 8%;">Qty</th>
                    <th class="text-right" style="width: 12%;">Rate</th>
                    <th class="text-right" style="width: 11%;">Disc</th>
                    <th class="text-right" style="width: 14%;">Amount</th>
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
                    <td class="product-name"><strong>{{ $item->productName }}</strong></td>
                    <td>{{ $packing }}</td>
                    <td class="text-right">{{ number_format($item->qty) }}</td>
                    <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                    <td class="text-right">{{ number_format($discountAmount, 0) }}</td>
                    <td class="text-right"><strong>{{ number_format($item->amount, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="bottom-row">
            <div class="notes-box">
                @if($hasTransportBlock)
                    <h6>Transport</h6>
                    <div class="transport-grid">
                        <p><span>Transport</span><strong>{{ ($transportDisplayName ?? null) ?: '-' }}</strong></p>
                        <p><span>Vehicle</span><strong>{{ $invoice->vehicleNumber ?: '-' }}</strong></p>
                        <p><span>Dispatch No</span><strong>{{ $invoice->dispatchNo ?: '-' }}</strong></p>
                        <p><span>Weight</span><strong>{{ $invoice->weight !== null ? rtrim(rtrim(number_format((float) $invoice->weight, 2), '0'), '.').' kg' : '-' }}</strong></p>
                    </div>
                @else
                    <h6>Notes</h6>
                    <p class="muted">—</p>
                @endif
                @if($invoice->comments)
                    <h6 style="margin-top:0.55rem;">Notes</h6>
                    <p>{{ $invoice->comments }}</p>
                @endif
            </div>
            <div class="totals-box">
                <div class="total-row">
                    <span>Subtotal</span>
                    <strong>{{ number_format($invoice->invoiceTotal, 2) }}</strong>
                </div>
                @if($invoice->invoiceDiscount > 0)
                @php
                    $discountPercent = $invoice->invoiceDiscount;
                    $discountAmount = $invoice->invoiceTotal * ($discountPercent / 100);
                @endphp
                <div class="total-row">
                    <span>Discount ({{ number_format($discountPercent, 2) }}%)</span>
                    <strong>- {{ number_format($discountAmount, 0) }}</strong>
                </div>
                @endif
                <div class="total-row grand-total">
                    <span>Total</span>
                    <strong>Rs. {{ number_format($invoice->grandTotal, 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Supplier Signature</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Authorized Signature</div>
            </div>
        </div>

        <div class="thanks-line">Thank you for your business</div>
    </div>

    <a href="{{ route('purchaseInvoice.pdf', $invoice->invoiceNo) }}" class="btn btn-pdf no-print">PDF</a>
    <button type="button" onclick="window.print()" class="btn btn-primary btn-print no-print">🖨️ Print</button>
    <a href="{{ route('purchase.invoice') }}" class="btn btn-secondary btn-back no-print">← Back</a>
</body>
</html>
