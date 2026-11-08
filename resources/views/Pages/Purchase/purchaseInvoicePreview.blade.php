<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Invoice - {{ $invoice->invoiceNo }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            
            .invoice-container {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0;
                margin: 0;
                padding: 0;
                width: 100% !important;
                max-width: none !important;
                font-size: 8.5pt;
            }

            .invoice-container .company-header {
                flex-wrap: nowrap !important;
            }

            .info-section {
                display: grid !important;
                grid-template-columns: minmax(0, 1.35fr) minmax(0, 0.9fr) !important;
                gap: 2mm !important;
                width: 100% !important;
            }

            .bottom-section {
                display: grid !important;
                grid-template-columns: minmax(0, 1.35fr) minmax(0, 0.85fr) !important;
                gap: 2mm !important;
                width: 100% !important;
            }

            .bottom-section.has-transport .transport-box {
                grid-column: 1 !important;
            }

            .bottom-section.has-transport .totals-section {
                grid-column: 2 !important;
            }

            .info-box,
            .delivery-info,
            .totals-section {
                min-width: 0;
            }

            .items-table th,
            .items-table td {
                padding: 2px 4px !important;
            }

            .signature-section {
                margin-top: 8mm;
            }

            .signature-line {
                margin-top: 10mm;
            }
            
            @page {
                size: A4;
                margin: 8mm;
            }
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', 'Helvetica Neue', Tahoma, Geneva, Verdana, sans-serif;
            padding: clamp(0.75rem, 2vw, 2.5rem);
            min-height: 100vh;
        }
        
        .invoice-container {
            width: min(96vw, 76rem);
            margin: 0 auto;
            background: #ffffff;
            box-shadow: 0 8px 32px rgba(15, 39, 71, 0.12);
            border-radius: 10px;
            padding: clamp(1rem, 2.2vw, 2.25rem);
            font-size: clamp(0.95rem, 0.72rem + 0.55vw, 1.2rem);
            line-height: 1.45;
        }

        .invoice-container .company-header {
            gap: 0.65rem;
            padding-bottom: 0.35rem;
            margin-bottom: 0.35rem;
            flex-wrap: wrap;
        }

        .invoice-container .company-name {
            font-size: clamp(1.05rem, 0.9rem + 0.45vw, 1.35rem);
            margin-bottom: 0;
        }

        .invoice-container .company-tagline,
        .invoice-container .company-contact {
            font-size: clamp(0.68rem, 0.62rem + 0.12vw, 0.78rem);
        }

        .invoice-container .company-monogram {
            flex: 0 0 auto;
        }

        .invoice-container .monogram-logo {
            font-size: clamp(1.55rem, 1.2rem + 0.7vw, 2.05rem);
            margin-bottom: 0;
        }

        .invoice-container .monogram-text {
            font-size: 0.62rem;
        }
        
        .info-section,
        .bottom-section {
            display: grid;
            gap: clamp(0.35rem, 0.8vw, 0.65rem);
            align-items: stretch;
        }

        .info-section {
            grid-template-columns: minmax(0, 1.6fr) minmax(12rem, 0.9fr);
            margin-bottom: clamp(0.45rem, 1vw, 0.75rem);
        }

        .bottom-section {
            grid-template-columns: minmax(0, 1.4fr) minmax(12rem, 0.85fr);
            margin-bottom: 0.35rem;
            align-items: start;
        }

        .bottom-section.totals-only {
            grid-template-columns: minmax(0, 1.4fr) minmax(12rem, 0.85fr);
        }

        /* Transport left, Totals right */
        .bottom-section.has-transport .transport-box {
            grid-column: 1;
        }

        .bottom-section.has-transport .totals-section {
            grid-column: 2;
            justify-self: stretch;
        }

        .bottom-section.totals-only .totals-section {
            grid-column: 2;
            justify-self: stretch;
            max-width: 18rem;
        }
        
        .info-box,
        .delivery-info {
            min-width: 0;
            background: #f8fafc;
            border: 1px solid #d7dee7;
            border-top: 3px solid #34495e;
            border-radius: 4px;
            padding: 0.35rem 0.55rem 0.45rem;
        }
        
        .info-box.invoice-info {
            border-top-color: #1a5276;
            background: #f4f7fb;
        }

        .delivery-info {
            border-top-color: #1e8449;
        }
        
        .info-box h6,
        .delivery-info h6 {
            margin: 0 0 0.25rem;
            font-weight: 700;
            font-size: 0.78em;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #5d6d7e;
            background: transparent;
            padding: 0;
            border: 0;
        }

        .delivery-info h6 {
            color: #1e8449;
        }
        
        .info-box p,
        .delivery-info p {
            margin: 0.08rem 0;
            font-size: 0.9em;
            color: #1c2833;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }
        
        .table-scroll {
            width: 100%;
            overflow-x: auto;
            margin-bottom: 0.45rem;
        }

        .items-table {
            width: 100%;
            margin-bottom: 0;
            border-collapse: collapse;
            font-size: 0.9em;
            box-shadow: none;
            table-layout: fixed;
        }
        
        .items-table thead {
            background: #34495e;
            color: white;
        }
        
        .items-table th,
        .items-table td {
            vertical-align: middle;
        }

        .items-table th {
            padding: 0.28rem 0.4rem;
            text-align: left;
            font-size: 0.78em;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        
        .items-table td {
            padding: 0.22rem 0.4rem;
            border-bottom: 1px solid #e8eef3;
            font-size: 0.9em;
            line-height: 1.2;
        }

        .items-table td.product-name {
            overflow-wrap: break-word;
        }
        
        .items-table tbody tr {
            transition: background 0.2s ease;
        }
        
        .items-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .items-table tbody tr:nth-child(even) {
            background: #fafbfc;
        }
        
        .text-right {
            text-align: right !important;
            white-space: nowrap;
        }
        
        .totals-section {
            min-width: 0;
        }
        
        .totals-box {
            width: 100%;
            background: white;
            border: 1px solid #d7dee7;
            border-top: 3px solid #1a252f;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.28rem 0.55rem;
            border-bottom: 1px solid #ecf0f1;
            font-size: 0.84em;
            color: #2c3e50;
        }
        
        .total-row span {
            font-weight: 500;
        }
        
        .total-row strong {
            text-align: right;
            font-weight: 700;
            white-space: nowrap;
        }
        
        .total-row.grand-total {
            background: #1a252f;
            color: white;
            padding: 0.38rem 0.55rem;
            font-size: 0.92em;
            font-weight: 700;
            border-bottom: none;
            margin-top: 0;
        }
        
        .signature-section {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 0.85rem;
            padding-top: 0.45rem;
            border-top: 1px solid #ecf0f1;
        }
        
        .signature-box {
            text-align: center;
            flex: 1 1 10rem;
            max-width: 16rem;
        }
        
        .signature-line {
            border-top: 1.5px solid #2c3e50;
            margin-top: 1.35rem;
            padding-top: 0.25rem;
            font-weight: 600;
            font-size: 0.82em;
            color: #2c3e50;
        }
        
        .footer-text {
            text-align: center;
            margin-top: clamp(0.75rem, 1.5vw, 1.25rem);
            padding-top: 0.5rem;
            border-top: 1px solid #ecf0f1;
            color: #95a5a6;
            font-size: 0.85em;
            font-style: italic;
        }
        
        .btn-print {
            position: fixed;
            bottom: 1.25rem;
            right: 1.25rem;
            padding: 0.55rem 1rem;
            font-size: clamp(0.8rem, 0.74rem + 0.2vw, 0.95rem);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 1000;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.25);
        }
        
        .btn-back {
            position: fixed;
            bottom: 1.25rem;
            left: 1.25rem;
            padding: 0.55rem 1rem;
            font-size: clamp(0.8rem, 0.74rem + 0.2vw, 0.95rem);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 1000;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.25);
        }
        
        .invoice-type-badge {
            text-align: center;
            font-size: 0.78em;
            font-weight: 700;
            color: #c0392b;
            letter-spacing: 0.08em;
            margin: 0 0 0.4rem;
        }

        .transport-box {
            margin-bottom: 0;
            min-width: 0;
        }

        .transport-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.35rem 0.75rem;
        }

        .transport-grid span {
            display: block;
            font-size: 0.72em;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #5d6d7e;
        }

        .transport-grid strong {
            font-weight: 600;
            color: #1c2833;
            overflow-wrap: anywhere;
        }

        @media screen and (max-width: 720px) {
            .info-section,
            .bottom-section,
            .bottom-section.totals-only {
                grid-template-columns: 1fr;
            }

            .bottom-section.totals-only .totals-section,
            .bottom-section.has-transport .totals-section,
            .bottom-section.has-transport .transport-box {
                grid-column: 1;
                max-width: none;
            }

            .transport-grid {
                grid-template-columns: 1fr 1fr;
            }

            .items-table {
                font-size: 0.78em;
            }

            .items-table th,
            .items-table td {
                padding: 0.22rem 0.18rem;
            }

            .signature-box {
                max-width: none;
            }

            body {
                padding-bottom: 5.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Include Professional Company Header Component -->
        @include('Components.invoiceHeader')
        
        <!-- Invoice Type Badge -->
        <div class="invoice-type-badge">📦 PURCHASE INVOICE</div>
        
        <!-- Supplier Information -->
        <div class="info-section">
            <div class="info-box">
                <h6>Supplier Details</h6>
                <p><strong>{{ $invoice->supplierName }}</strong> ({{ $invoice->supplierCode }})</p>
                @if($supplier && $supplier->custCity)
                <p>{{ $supplier->custCity }}@if($supplier->custContect), {{ $supplier->custContect }}@endif</p>
                @endif
            </div>
            <div class="info-box invoice-info">
                <h6>Invoice Info</h6>
                <p><strong>No:</strong> {{ $invoice->invoiceNo }}</p>
                <p><strong>Date:</strong> {{ date('d-M-Y', strtotime($invoice->invoiceDate)) }}</p>
                <p><strong>Status:</strong> {{ $invoice->status }}</p>
            </div>
        </div>
        
        <!-- Items Table -->
        <div class="table-scroll">
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th>Product</th>
                    <th style="width: 14%;">Packing</th>
                    <th class="text-right" style="width: 8%;">Qty</th>
                    <th class="text-right" style="width: 12%;">Rate</th>
                    <th class="text-right" style="width: 12%;">Disc</th>
                    <th class="text-right" style="width: 14%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                @php
                    $product = \App\Models\newProduct::where('productCode', $item->productCode)->first();
                    $packName = trim((string) ($product->pakingName ?? ''));
                    $packing = $packName !== '' ? $packName : 'N/A';

                    // KG items only: total weight = Qty × KG-per-BAG (piecesPerUnit)
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

                    // Calculate discount amount: discount is percentage (e.g., 10 for 10%)
                    $discountAmount = ($item->qty * $item->rate) * ($item->discount / 100);
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="product-name"><strong>{{ $item->productName }}</strong></td>
                    <td>{{ $packing }}</td>
                    <td class="text-right">{{ number_format($item->qty) }}</td>
                    <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                    <td class="text-right">{{ number_format($discountAmount, 0) }}</td>
                    <td class="text-right"><strong>Rs. {{ number_format($item->amount, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        @php
            $hasTransportBlock = $transportDisplayName
                || $invoice->vehicleNumber
                || $invoice->dispatchNo
                || $invoice->weight
                || $invoice->transportRate
                || $invoice->transportAmount;
        @endphp

        <!-- Bottom: Transport (left) + Totals (right) -->
        <div class="bottom-section{{ $hasTransportBlock ? ' has-transport' : ' totals-only' }}">
            @if($hasTransportBlock)
            <div class="info-box transport-box">
                <h6>Transport</h6>
                <div class="transport-grid">
                    <p><span>Transport</span><strong>{{ $transportDisplayName ?: '-' }}</strong></p>
                    <p><span>Vehicle Number</span><strong>{{ $invoice->vehicleNumber ?: '-' }}</strong></p>
                    <p><span>Dispatch No</span><strong>{{ $invoice->dispatchNo ?: '-' }}</strong></p>
                    <p><span>Weight</span><strong>{{ $invoice->weight !== null ? rtrim(rtrim(number_format((float) $invoice->weight, 2), '0'), '.').' kg' : '-' }}</strong></p>
                    <p><span>Rate</span><strong>{{ $invoice->transportRate !== null ? number_format((float) $invoice->transportRate, 2) : '-' }}</strong></p>
                    <p><span>Amount</span><strong>{{ $invoice->transportAmount !== null ? 'Rs. '.number_format((float) $invoice->transportAmount, 2) : '-' }}</strong></p>
                </div>
            </div>
            @else
            <div></div>
            @endif

            <div class="totals-section">
                <div class="totals-box">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <strong>Rs. {{ number_format($invoice->invoiceTotal, 2) }}</strong>
                    </div>
                    @if($invoice->invoiceDiscount > 0)
                    @php
                        $discountPercent = $invoice->invoiceDiscount;
                        $discountAmount = $invoice->invoiceTotal * ($discountPercent / 100);
                    @endphp
                    <div class="total-row">
                        <span>Discount ({{ number_format($discountPercent, 2) }}%):</span>
                        <strong style="color: #e74c3c;">- Rs. {{ number_format($discountAmount, 0) }}</strong>
                    </div>
                    @endif
                    <div class="total-row grand-total">
                        <span>GRAND TOTAL:</span>
                        <strong>Rs. {{ number_format($invoice->grandTotal, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        @if($hasTransportBlock && $invoice->comments)
        <div class="delivery-info" style="margin-top: 0.35rem;">
            <h6>Notes</h6>
            <p style="color: #666; font-style: italic;">{{ $invoice->comments }}</p>
        </div>
        @endif
        
        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Supplier Signature</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Authorized Signatory</div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer-text">
            Thank you for your business! | Generated on {{ now()->format('d-M-Y H:i') }}
        </div>
    </div>
    
    <!-- Print Button -->
    <button onclick="window.print()" class="btn btn-danger btn-print no-print">
        🖨️ Print Invoice
    </button>
    
    <!-- Back Button -->
    <a href="{{ route('purchase.invoice') }}" class="btn btn-secondary btn-back no-print">
        ← Back
    </a>
</body>
</html>
