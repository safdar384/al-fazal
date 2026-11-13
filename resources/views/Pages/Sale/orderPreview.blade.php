<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Order - {{ $order->orderNo }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.invoice-doc-styles')
</head>
<body>
@php
    $statusText = strtoupper($order->status ?? 'PENDING');
    $buyerName = $order->customerName;
    if ($order->customerCode && ! str_contains((string) $buyerName, (string) $order->customerCode)) {
        $buyerName .= ' ('.$order->customerCode.')';
    }
@endphp

    <div class="invoice-container">
        <div class="status-watermark">{{ $statusText }}</div>

        @include('components.invoiceHeader')

        <div class="bill-row">
            <div>
                <div class="sec-label">Bill To</div>
                <div class="bill-name">{{ $buyerName }}</div>
                @if($order->city)
                <div class="bill-line">{{ $order->city }}</div>
                @endif
            </div>
            <div class="inv-side">
                <h1 class="inv-title">ORDER</h1>
                <div class="inv-type">{{ $order->status ?? 'Pending' }}</div>
                <div class="inv-meta">
                    <div><span class="k">No.</span><span class="v">{{ $order->orderNo }}</span></div>
                    <div><span class="k">Date</span><span class="v">{{ \Carbon\Carbon::parse($order->orderDate)->format('d.m.Y') }}</span></div>
                    <div><span class="k">Items</span><span class="v">{{ $order->items->count() }}</span></div>
                </div>
            </div>
        </div>

        <table class="items-table">
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

        <div class="bottom-row">
            <div class="notes-box">
                <h6>Delivery</h6>
                <p>Transport: <strong>{{ $order->gudsName ?: '—' }}</strong></p>
                @if($order->notes || $order->comments)
                    <h6 style="margin-top:0.55rem;">Notes</h6>
                    <p>{{ $order->notes ?: $order->comments }}</p>
                @endif
                @if($order->converted_invoice_no)
                    <h6 style="margin-top:0.55rem;">Converted</h6>
                    <p>
                        Invoice:
                        <a href="{{ route('sale.invoice.preview', $order->converted_invoice_no) }}" target="_blank">
                            {{ $order->converted_invoice_no }}
                        </a>
                    </p>
                @endif
                <p class="muted" style="margin-top:0.55rem;">Demand only — no stock/ledger until converted to Sale Invoice.</p>
            </div>
            <div class="totals-box">
                <div class="total-row">
                    <span>Subtotal</span>
                    <strong>{{ number_format((float) $order->orderTotal, 2) }}</strong>
                </div>
                @if((float) $order->orderDiscount > 0)
                <div class="total-row">
                    <span>Discount</span>
                    <strong>- {{ number_format((float) $order->orderDiscount, 2) }}</strong>
                </div>
                @endif
                <div class="total-row grand-total">
                    <span>Total</span>
                    <strong>Rs. {{ number_format((float) $order->grandTotal, 2) }}</strong>
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

    <a class="btn btn-secondary btn-back no-print" href="{{ route('order.summary') }}">← Back</a>
    @if($order->canConvertToInvoice())
    <button type="button" class="btn btn-convert no-print" id="btnConvertOrder">Convert to Invoice</button>
    @endif
    <a href="{{ route('order.pdf', $order->orderNo) }}" class="btn btn-pdf no-print">PDF</a>
    <button type="button" class="btn btn-primary btn-print no-print" onclick="window.print()">🖨️ Print</button>

@if($order->canConvertToInvoice())
<script>
document.getElementById('btnConvertOrder')?.addEventListener('click', async function () {
    if (!confirm('Create Sale Invoice from this order?')) return;
    const res = await fetch('{{ route('order.convert', $order->orderNo) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
    });
    const data = await res.json();
    alert(data.message || (data.success ? 'Done' : 'Failed'));
    if (data.success && data.previewUrl) {
        window.open(data.previewUrl, '_blank');
        location.reload();
    }
});
</script>
@endif
</body>
</html>
