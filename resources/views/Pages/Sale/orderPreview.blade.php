<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Order - {{ $order->orderNo }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: clamp(0.75rem, 2vw, 2rem);
            padding-bottom: 5rem;
            background: #f5f5f5;
        }
        .wrap {
            position: relative;
            width: min(96vw, 76rem);
            margin: 0 auto;
            background: #fff;
            padding: clamp(0.75rem, 2vw, 1.5rem);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,.1);
            overflow: hidden;
            font-size: clamp(0.82rem, 0.72rem + 0.35vw, 1.05rem);
        }
        .wrap .company-header {
            gap: clamp(0.4rem, 1vw, 0.85rem);
            flex-wrap: wrap;
            padding-bottom: 0.35rem;
            margin-bottom: 0.45rem;
        }
        .wrap .company-name { font-size: clamp(1.05rem, 0.9rem + 0.45vw, 1.35rem); margin-bottom: 0; }
        .wrap .company-tagline,
        .wrap .company-contact { font-size: clamp(0.68rem, 0.62rem + 0.12vw, 0.78rem); }
        .wrap .company-monogram { flex: 0 0 auto; }
        .wrap .monogram-logo { font-size: clamp(1.55rem, 1.2rem + 0.7vw, 2.4rem); margin-bottom: 0; }
        .wrap .monogram-text { font-size: 0.62rem; }
        .top {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
            gap: clamp(0.45rem, 1.2vw, 1rem);
            margin-bottom: clamp(0.5rem, 1.2vw, 0.85rem);
        }
        .top-right { text-align: right; }
        .title { font-size: clamp(1rem, 0.85rem + 0.45vw, 1.25rem); font-weight: 700; }
        .table-wrap { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 0.6rem; table-layout: fixed; }
        th:nth-child(1), td:nth-child(1) { width: 7%; }
        th:nth-child(3), td:nth-child(3) { width: 12%; }
        th:nth-child(4), td:nth-child(4) { width: 16%; }
        th:nth-child(5), td:nth-child(5) { width: 18%; }
        th:nth-child(6), td:nth-child(6) { width: 18%; }
        th { white-space: normal; line-height: 1.2; }
        th, td {
            border: 1px solid #ddd;
            padding: clamp(0.25rem, 0.45vw, 0.45rem);
            font-size: clamp(0.72rem, 0.66rem + 0.2vw, 0.88rem);
        }
        th { background: #f0f0f0; text-align: left; }
        td.text-right { text-align: right; white-space: nowrap; }
        th.text-right { text-align: right; }
        .note {
            margin-top: clamp(0.6rem, 1.4vw, 1rem);
            padding: clamp(0.4rem, 0.8vw, 0.65rem);
            border-left: 4px solid #0d6efd;
            background: #eef4ff;
            font-size: 0.92em;
        }
        .extra { font-size: 0.92em; margin-top: 0.55rem; }
        .status-watermark {
            position: absolute;
            top: 42%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: clamp(2.4rem, 10vw, 5.6rem);
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            opacity: 0.1;
            pointer-events: none;
            user-select: none;
            z-index: 1;
            white-space: nowrap;
            max-width: 95%;
        }
        .wm-pending { color: #f59e0b; }
        .wm-confirmed { color: #0dcaf0; }
        .wm-process { color: #0d6efd; }
        .wm-completed { color: #198754; }
        .wm-cancelled { color: #dc3545; }
        .top, table, .note, .extra { position: relative; z-index: 2; }
        .actions {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 20;
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            justify-content: space-between;
            align-items: center;
            padding: 0.55rem clamp(0.6rem, 2vw, 1.25rem);
            background: rgba(255,255,255,0.96);
            box-shadow: 0 -2px 10px rgba(0,0,0,.08);
        }
        .actions-right { display: flex; flex-wrap: wrap; gap: 0.45rem; margin-left: auto; }
        .btn {
            padding: 0.5rem 0.85rem;
            border: none;
            border-radius: 4px;
            color: #fff;
            text-decoration: none;
            font-size: clamp(0.78rem, 0.72rem + 0.2vw, 0.9rem);
            cursor: pointer;
        }
        .print { background: #0d6efd; }
        .back { background: #6c757d; }
        .convert { background: #198754; }
        @media screen and (max-width: 720px) {
            .top { grid-template-columns: 1fr; }
            .top-right { text-align: left; }
        }
        @media print {
            .actions { display: none !important; }
            body { background: #fff; padding: 0; }
            .wrap {
                box-shadow: none;
                width: 100%;
                max-width: none;
                border-radius: 0;
                padding: 0;
                font-size: 9.5pt;
            }
            .top { grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr) !important; }
            .top-right { text-align: right !important; }
            .wrap .company-header { flex-wrap: nowrap !important; }
            table { table-layout: auto; }
            th, td { font-size: 8.5pt; padding: 2px 4px; }
            @page { size: A4; margin: 10mm; }
        }
    </style>
</head>
<body>
<div class="wrap">
    @php
        $statusText = strtoupper($order->status ?? 'PENDING');
        $watermarkClass = match ($order->status) {
            'Confirmed' => 'wm-confirmed',
            'In Process' => 'wm-process',
            'Completed' => 'wm-completed',
            'Cancelled' => 'wm-cancelled',
            default => 'wm-pending',
        };
    @endphp
    <div class="status-watermark {{ $watermarkClass }}">{{ $statusText }}</div>

    @include('Components.invoiceHeader')
    <div class="top">
        <div>
            <div class="title">Customer Order</div>
            <div><strong>Order No:</strong> {{ $order->orderNo }}</div>
            <div><strong>Date:</strong> {{ \Carbon\Carbon::parse($order->orderDate)->format('d-M-Y') }}</div>
        </div>
        <div class="top-right">
            <div><strong>Customer:</strong> {{ $order->customerName }} ({{ $order->customerCode }})</div>
            <div><strong>City:</strong> {{ $order->city }}</div>
            <div><strong>Guds:</strong> {{ $order->gudsName ?: '-' }}</div>
        </div>
    </div>

    <div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Product</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Rate</th>
            <th class="text-right">Discount %</th>
            <th class="text-right">Amount</th>
        </tr>
        </thead>
        <tbody>
        @foreach($order->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->productName }} ({{ $item->productCode }})</td>
                <td class="text-right">{{ number_format((float)$item->qty, 0) }}</td>
                <td class="text-right">{{ number_format((float)$item->rate, 2) }}</td>
                <td class="text-right">{{ number_format((float)$item->discount, 2) }}</td>
                <td class="text-right">{{ number_format((float)$item->amount, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <th colspan="5" class="text-right">Order Total</th>
            <th class="text-right">{{ number_format((float)$order->orderTotal, 2) }}</th>
        </tr>
        <tr>
            <th colspan="5" class="text-right">Discount</th>
            <th class="text-right">{{ number_format((float)$order->orderDiscount, 2) }}</th>
        </tr>
        <tr>
            <th colspan="5" class="text-right">Grand Total</th>
            <th class="text-right">{{ number_format((float)$order->grandTotal, 2) }}</th>
        </tr>
        </tfoot>
    </table>
    </div>

    @if($order->notes || $order->comments)
        <p class="extra"><strong>Notes:</strong> {{ $order->notes ?: $order->comments }}</p>
    @endif

    @if($order->converted_invoice_no)
        <p class="extra" style="color:#198754;"><strong>Converted to invoice:</strong>
            <a href="{{ route('sale.invoice.preview', $order->converted_invoice_no) }}" target="_blank">{{ $order->converted_invoice_no }}</a>
        </p>
    @endif

    <div class="note">
        <strong>Note:</strong> Customer demand only — no stock, ledger, or payment impact until converted to a Sale Invoice.
    </div>
</div>

<div class="actions">
    <a class="btn back" href="{{ route('order.summary') }}">Back</a>
    <div class="actions-right">
        @if($order->canConvertToInvoice())
        <button type="button" class="btn convert" id="btnConvertOrder">Convert to Invoice</button>
        @endif
        <button class="btn print" onclick="window.print()">Print</button>
    </div>
</div>
@if($order->canConvertToInvoice())
<script>
document.getElementById('btnConvertOrder')?.addEventListener('click', async function() {
  if (!confirm('Create Sale Invoice from this order?')) return;
  const res = await fetch('{{ route('order.convert', $order->orderNo) }}', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
  });
  const data = await res.json();
  alert(data.message || (data.success ? 'Done' : 'Failed'));
  if (data.success && data.previewUrl) { window.open(data.previewUrl, '_blank'); location.reload(); }
});
</script>
@endif
</body>
</html>
