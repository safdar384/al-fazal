<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Return - {{ $purchaseReturn->returnNo }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4; margin: 8mm; }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; background: #fff !important; }
            .invoice-container {
                box-shadow: none !important; border: none !important; border-radius: 0;
                margin: 0; padding: 0; width: 100% !important; max-width: none !important;
            }
            .items-table thead th {
                background: #0f2747 !important; color: #fff !important;
                -webkit-print-color-adjust: exact; print-color-adjust: exact;
            }
            .cond-good { color: #198754 !important; }
            .cond-damage { color: #dc3545 !important; }
        }
        body {
            background: #e8eef5;
            font-family: 'Segoe UI', 'Helvetica Neue', Tahoma, Geneva, Verdana, sans-serif;
            padding: 12px;
            color: #1a1a1a;
        }
        .invoice-container {
            width: min(96vw, 76rem);
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 4px 24px rgba(15, 39, 71, 0.12);
            border-radius: 4px;
            padding: 14px 16px 16px;
        }
        .company-header {
            display: flex; justify-content: space-between; align-items: flex-start;
            border-bottom: 2px solid #0f2747; padding-bottom: 8px; margin-bottom: 8px;
        }
        .company-name { font-size: 1.55rem; font-weight: 700; color: #0f2747; letter-spacing: 0.4px; }
        .company-tagline { color: #5a6a7a; font-size: 0.72rem; margin-top: 2px; }
        .company-contact { color: #6b7c8d; font-size: 0.68rem; margin-top: 1px; }
        .company-monogram {
            flex: 0 0 72px; text-align: center; border: 2px solid #0f2747;
            border-radius: 4px; padding: 4px 6px;
        }
        .monogram-logo { font-size: 1.75rem; font-weight: 800; color: #0f2747; line-height: 1; }
        .monogram-text { font-size: 0.52rem; color: #5a6a7a; font-weight: 700; letter-spacing: 1px; }
        .report-title { text-align: center; margin: 6px 0 8px; }
        .report-title h3 {
            font-size: 1.05rem; font-weight: 700; margin: 0; color: #0f2747;
            letter-spacing: 0.8px; text-transform: uppercase;
        }
        .meta-bar { display: grid; grid-template-columns: 1.2fr 1fr; gap: 8px; margin-bottom: 10px; }
        .meta-box {
            background: #f4f7fb; border: 1px solid #d5dde8; border-left: 3px solid #0f2747;
            border-radius: 3px; padding: 7px 10px;
        }
        .meta-box h6 {
            margin: 0 0 4px; font-size: 0.68rem; font-weight: 700; color: #0f2747;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .meta-grid { display: grid; grid-template-columns: auto 1fr; column-gap: 8px; row-gap: 2px; font-size: 0.72rem; }
        .meta-grid .lbl { color: #6b7c8d; font-weight: 600; }
        .meta-grid .val { color: #1a1a1a; font-weight: 600; }
        .summary-stats { display: flex; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; }
        .stat-box {
            flex: 1; min-width: 110px; text-align: center; padding: 8px 6px; background: #f4f7fb;
            border: 1px solid #d5dde8; border-radius: 3px;
        }
        .stat-label { font-size: 0.62rem; color: #6b7c8d; font-weight: 700; text-transform: uppercase; margin-bottom: 2px; }
        .stat-value { font-size: 0.92rem; font-weight: 700; color: #0f2747; }
        .stat-box.good .stat-value { color: #198754; }
        .stat-box.damage .stat-value { color: #dc3545; }
        .items-table {
            width: 100%; border-collapse: collapse; font-size: 0.72rem; margin-bottom: 10px;
        }
        .items-table thead th {
            background: #0f2747; color: #fff; padding: 6px 5px; font-size: 0.62rem; font-weight: 700;
            text-align: center; border: 1px solid #0a1c33; text-transform: uppercase;
        }
        .items-table tbody td, .items-table tfoot td {
            padding: 4px 5px; border: 1px solid #cfd8e3; vertical-align: middle;
        }
        .items-table tbody tr:nth-child(even) td { background: #f4f7fb; }
        .items-table tfoot td { background: #e8eef5; font-weight: 700; color: #0f2747; }
        .text-end { text-align: right !important; }
        .text-center { text-align: center !important; }
        .cond-good { color: #198754; font-weight: 700; }
        .cond-damage { color: #dc3545; font-weight: 700; }
        .totals-box {
            margin-left: auto; width: min(280px, 100%);
            border: 1px solid #d5dde8; border-radius: 3px; overflow: hidden;
        }
        .total-row {
            display: flex; justify-content: space-between; padding: 6px 10px;
            border-bottom: 1px solid #eef1f5; font-size: 0.78rem;
        }
        .total-row:last-child { border-bottom: none; }
        .total-row.grand { background: #0f2747; color: #fff; font-weight: 700; font-size: 0.9rem; }
        .footer-notes {
            margin-top: 8px; padding: 8px 10px; background: #f4f7fb;
            border: 1px solid #d5dde8; border-radius: 3px; font-size: 0.72rem;
        }
        .signature-section {
            display: flex; justify-content: space-between; gap: 24px; margin-top: 28px;
        }
        .signature-box { flex: 1; text-align: center; font-size: 0.7rem; color: #6b7c8d; }
        .signature-line {
            border-top: 1px solid #0f2747; margin-top: 40px; padding-top: 6px; color: #0f2747; font-weight: 600;
        }
        .report-footer {
            margin-top: 10px; border-top: 1px solid #d5dde8; padding-top: 6px;
            color: #8a97a6; text-align: center; font-size: 0.65rem;
        }
        .btn-print, .btn-back {
            position: fixed; bottom: 24px; padding: 9px 18px; font-size: 0.85rem;
            box-shadow: 0 4px 14px rgba(0,0,0,0.18); z-index: 1000; border-radius: 4px;
        }
        .btn-back { left: 24px; }
        .btn-print { right: 24px; background: #0f2747; border-color: #0f2747; }
        .btn-print:hover { background: #1a4a8a; border-color: #1a4a8a; }
    </style>
</head>
<body>
@php
    $fmt = static fn ($n) => number_format((float) $n, 2);
    $partyName = $purchaseReturn->supplierName;
    if ($purchaseReturn->supplierCode && ! str_contains((string) $partyName, (string) $purchaseReturn->supplierCode)) {
        $partyName .= ' ('.$purchaseReturn->supplierCode.')';
    }
    $partyPhone = optional($supplier)->custContect ?? '';
    $partyAddress = optional($supplier)->custAddress ?? '';
    $partyCity = $purchaseReturn->city ?: optional($supplier)->custCity;
    $addressLine = trim($partyAddress.($partyCity ? ' ('.$partyCity.')' : ''));
@endphp

    <div class="invoice-container">
        @include('components.invoiceHeader')

        <div class="report-title">
            <h3>Purchase Return</h3>
        </div>

        <div class="meta-bar">
            <div class="meta-box">
                <h6>Supplier</h6>
                <div class="meta-grid">
                    <span class="lbl">Name</span>
                    <span class="val">{{ $partyName }}@if($partyPhone) · {{ $partyPhone }}@endif</span>
                    <span class="lbl">Address</span>
                    <span class="val">{{ $addressLine !== '' ? $addressLine : '—' }}</span>
                </div>
            </div>
            <div class="meta-box">
                <h6>Return</h6>
                <div class="meta-grid">
                    <span class="lbl">Return No</span>
                    <span class="val">{{ $purchaseReturn->returnNo }}</span>
                    <span class="lbl">Date</span>
                    <span class="val">{{ \Carbon\Carbon::parse($purchaseReturn->returnDate)->format('d-m-Y') }}</span>
                    @if($purchaseReturn->originalInvoiceNo)
                        <span class="lbl">Invoice</span>
                        <span class="val">{{ $purchaseReturn->originalInvoiceNo }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="summary-stats">
            <div class="stat-box">
                <div class="stat-label">Items</div>
                <div class="stat-value">{{ (int) $purchaseReturn->noOfItems }}</div>
            </div>
            <div class="stat-box good">
                <div class="stat-label">Good Qty (Stock −)</div>
                <div class="stat-value">{{ $fmt($purchaseReturn->goodQtyTotal) }}</div>
            </div>
            <div class="stat-box damage">
                <div class="stat-label">Damage Qty</div>
                <div class="stat-value">{{ $fmt($purchaseReturn->damageQtyTotal) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Supplier Debit</div>
                <div class="stat-value">{{ $fmt($purchaseReturn->grandTotal) }}</div>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:4%;">#</th>
                    <th style="width:28%;">Product</th>
                    <th style="width:12%;">Variant</th>
                    <th style="width:10%;">Condition</th>
                    <th style="width:8%;">Qty</th>
                    <th style="width:10%;">Rate</th>
                    <th style="width:8%;">Disc %</th>
                    <th style="width:12%;">Amount</th>
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
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->productName }}</td>
                        <td class="text-center">{{ $variant !== '' ? $variant : '—' }}</td>
                        <td class="text-center {{ $cond === 'damage' ? 'cond-damage' : 'cond-good' }}">
                            {{ $cond === 'damage' ? 'Damage' : 'Good' }}
                        </td>
                        <td class="text-end">{{ $fmt($item->qty) }}</td>
                        <td class="text-end">{{ $fmt($item->rate) }}</td>
                        <td class="text-end">{{ $fmt($item->discount) }}</td>
                        <td class="text-end"><strong>{{ $fmt($item->amount) }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-box">
            <div class="total-row">
                <span>Subtotal</span>
                <strong>{{ $fmt($purchaseReturn->invoiceTotal) }}</strong>
            </div>
            @if ((float) $purchaseReturn->invoiceDiscount > 0)
                <div class="total-row">
                    <span>Discount</span>
                    <strong style="color:#dc3545;">- {{ $fmt($purchaseReturn->invoiceDiscount) }}</strong>
                </div>
            @endif
            <div class="total-row grand">
                <span>Total Debit</span>
                <strong>Rs. {{ $fmt($purchaseReturn->grandTotal) }}</strong>
            </div>
        </div>

        @if ($purchaseReturn->comments)
            <div class="footer-notes">
                <strong>Notes:</strong> {{ $purchaseReturn->comments }}
            </div>
        @endif

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Received By</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Authorized By</div>
            </div>
        </div>

        <div class="report-footer">
            This is a computer-generated document. &copy; {{ date('Y') }} Trade Gate
        </div>
    </div>

    <a href="{{ route('purchase.return') }}" class="btn btn-secondary btn-back no-print">
        <i class="bi bi-arrow-left"></i> Back
    </a>
    <button type="button" onclick="window.print()" class="btn btn-primary btn-print no-print">
        <i class="bi bi-printer"></i> Print
    </button>
</body>
</html>
