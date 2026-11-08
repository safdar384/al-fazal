<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Summary Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page {
            size: A4 landscape;
            margin: 5mm;
        }
        @media print {
            .no-print { display: none !important; }
            html, body {
                width: auto !important;
                height: auto !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                overflow: visible !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .ledger-container {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: none !important;
                width: 100% !important;
                page-break-after: avoid;
                break-after: avoid;
            }
            .company-header {
                padding-bottom: 4px !important;
                margin-bottom: 4px !important;
            }
            .company-name { font-size: 1.15rem !important; }
            .company-monogram { display: none !important; }
            .company-tagline, .company-contact { font-size: 0.58rem !important; }
            .report-title { margin: 2px 0 4px !important; }
            .report-title h3 { font-size: 0.88rem !important; }
            .meta-bar { margin-bottom: 4px !important; gap: 4px !important; }
            .meta-box { padding: 4px 6px !important; }
            .summary-stats { margin-bottom: 4px !important; gap: 4px !important; }
            .stat-box { padding: 4px 3px !important; }
            .stat-value { font-size: 0.78rem !important; }
            .ledger-table {
                font-size: 0.58rem !important;
                margin-bottom: 2px !important;
            }
            .ledger-table thead th {
                background: #0f2747 !important;
                color: #fff !important;
                padding: 3px 3px !important;
                font-size: 0.55rem !important;
            }
            .ledger-table tbody td,
            .ledger-table tfoot td {
                padding: 2px 3px !important;
                height: auto !important;
                min-height: 0 !important;
                line-height: 1.15 !important;
            }
            .ledger-table tbody td.cell-address,
            .ledger-table tbody td.cell-desc {
                white-space: normal !important;
                overflow: visible !important;
                text-overflow: clip !important;
                word-break: break-word;
                height: auto !important;
            }
            .ledger-table tbody tr:nth-child(even) td {
                background: #f4f7fb !important;
            }
            .ledger-table tfoot td {
                background: #e8eef5 !important;
            }
            .ledger-table thead { display: table-header-group; }
            .ledger-table tfoot { display: table-footer-group; }
            .ledger-table tr { page-break-inside: avoid; break-inside: avoid; }
            .report-footer {
                margin-top: 3px !important;
                padding-top: 3px !important;
                font-size: 0.55rem !important;
            }
            .meta-bar, .summary-stats, .stat-box, .status-pill, .type-pill {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        body {
            background: #e8eef5;
            font-family: 'Segoe UI', 'Helvetica Neue', Tahoma, Geneva, Verdana, sans-serif;
            color: #1a1a1a;
            padding: 12px;
        }
        .ledger-container {
            width: 100%;
            max-width: 285mm;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 4px 24px rgba(15, 39, 71, 0.12);
            border-radius: 4px;
            padding: 10px 12px 12px;
        }
        .company-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f2747;
            padding-bottom: 8px;
            margin-bottom: 8px;
            gap: 12px;
        }
        .company-name {
            font-size: 1.55rem;
            font-weight: 700;
            color: #0f2747;
            letter-spacing: 0.4px;
            line-height: 1.1;
        }
        .company-tagline {
            color: #5a6a7a;
            font-size: 0.72rem;
            margin-top: 2px;
        }
        .company-contact {
            color: #6b7c8d;
            font-size: 0.68rem;
            margin-top: 1px;
        }
        .company-monogram {
            flex: 0 0 72px;
            text-align: center;
            border: 2px solid #0f2747;
            border-radius: 4px;
            padding: 4px 6px;
        }
        .monogram-logo {
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f2747;
            line-height: 1;
        }
        .monogram-text {
            font-size: 0.52rem;
            color: #5a6a7a;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .report-title {
            text-align: center;
            margin: 6px 0 8px;
        }
        .report-title h3 {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0;
            color: #0f2747;
            letter-spacing: 0.8px;
        }
        .meta-bar {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 8px;
            margin-bottom: 10px;
        }
        .meta-box {
            background: #f4f7fb;
            border: 1px solid #d5dde8;
            border-left: 3px solid #0f2747;
            border-radius: 3px;
            padding: 7px 10px;
        }
        .meta-box h6 {
            margin: 0 0 4px;
            font-size: 0.68rem;
            font-weight: 700;
            color: #0f2747;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            column-gap: 8px;
            row-gap: 2px;
            font-size: 0.72rem;
        }
        .meta-grid .lbl { color: #6b7c8d; font-weight: 600; }
        .meta-grid .val {
            color: #1a1a1a;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .summary-stats {
            display: flex;
            gap: 8px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        .stat-box {
            flex: 1 1 8rem;
            text-align: center;
            padding: 8px 6px;
            background: #f4f7fb;
            border: 1px solid #d5dde8;
            border-radius: 3px;
        }
        .stat-label {
            font-size: 0.62rem;
            color: #6b7c8d;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
        }
        .stat-value {
            font-size: 0.92rem;
            font-weight: 700;
            color: #0f2747;
        }
        .stat-box.amount .stat-value { color: #198754; }
        .stat-box.credit .stat-value { color: #1a4a8a; }
        .stat-box.cash .stat-value { color: #0f2747; }

        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 0.68rem;
            margin-bottom: 10px;
        }
        .ledger-table thead th {
            background: #0f2747;
            color: #fff;
            padding: 6px 5px;
            font-size: 0.62rem;
            font-weight: 700;
            text-align: center;
            border: 1px solid #0a1c33;
            vertical-align: middle;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .ledger-table tbody td,
        .ledger-table tfoot td {
            padding: 4px 5px;
            border: 1px solid #cfd8e3;
            vertical-align: middle;
            height: 26px;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ledger-table tbody tr:nth-child(even) td { background: #f4f7fb; }
        .ledger-table tbody tr:hover td { background: #eaf1f9; }
        .ledger-table tfoot td {
            background: #e8eef5;
            font-weight: 700;
            color: #0f2747;
        }
        .ledger-table .text-end { text-align: right !important; }
        .ledger-table .text-center { text-align: center !important; }
        .ledger-table .cell-desc {
            text-align: left !important;
            font-weight: 500;
        }
        .ledger-table .cell-address {
            text-align: left !important;
            font-weight: 500;
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: clip !important;
            word-break: break-word;
            line-height: 1.25;
            height: auto !important;
            min-height: 26px;
        }
        .ledger-table .amt-total { color: #0f2747; font-weight: 700; }
        .status-pill, .type-pill {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 0.58rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .status-active { background: #e8f5e9; color: #198754; }
        .status-cancelled { background: #fde8ea; color: #842029; }
        .type-cash { background: #e8eef5; color: #0f2747; }
        .type-credit { background: #e8f0fe; color: #1a4a8a; }
        .empty-row td {
            text-align: center;
            color: #6b7c8d;
            padding: 18px 8px !important;
            white-space: normal !important;
        }
        .report-footer {
            margin-top: 8px;
            border-top: 1px solid #d5dde8;
            padding-top: 6px;
            color: #8a97a6;
            text-align: center;
            font-size: 0.65rem;
        }
        .btn-print, .btn-back {
            position: fixed;
            bottom: 24px;
            padding: 9px 18px;
            font-size: 0.85rem;
            box-shadow: 0 4px 14px rgba(0,0,0,0.18);
            z-index: 1000;
            border-radius: 4px;
        }
        .btn-back { left: 24px; }
        .btn-print { right: 24px; background: #0f2747; border-color: #0f2747; }
        .btn-print:hover { background: #1a4a8a; border-color: #1a4a8a; }
    </style>
</head>
<body>
@php
    $fmt = static fn ($n) => number_format((float) $n, 2);
    $count = $invoices->count();
    $totalAmount = (float) $invoices->sum('grandTotal');
    $cashSaleAmount = (float) $invoices->where('sale_type', 'cash')->sum('grandTotal');
    $creditSaleAmount = $totalAmount - $cashSaleAmount;
    $totalItems = (int) $invoices->sum('noOfItems');
    $totalCorten = (int) $invoices->sum('corten');
    $avg = $count > 0 ? $totalAmount / $count : 0;

    $fromLabel = request('fromDate')
        ? \Carbon\Carbon::parse(request('fromDate'))->format('d-M-Y')
        : 'Start';
    $toLabel = request('toDate')
        ? \Carbon\Carbon::parse(request('toDate'))->format('d-M-Y')
        : 'End';
    $customerLabel = request('customerCode')
        ? ($invoices->first()->customerName ?? request('customerCode'))
        : 'All';
    $areaLabel = request('area') ?: 'All';
    $typeLabel = match (request('sale_type')) {
        'cash' => 'Cash Sale',
        'credit' => 'Credit Sale',
        'return' => 'Return Sale',
        default => 'All',
    };
@endphp

    <div class="ledger-container">
        @include('components.invoiceHeader')
        
        <div class="report-title">
            <h3>{{ request('sale_type') === 'return' ? 'RETURN SALE SUMMARY REPORT' : 'SALE SUMMARY REPORT' }}</h3>
        </div>

        <div class="meta-bar">
            <div class="meta-box">
                <h6>Filters</h6>
                <div class="meta-grid">
                    <span class="lbl">Period</span>
                    <span class="val">
            @if(request('fromDate') || request('toDate'))
                            {{ $fromLabel }} to {{ $toLabel }}
                    @else
                            All Time
                    @endif
                </span>
                    <span class="lbl">Customer</span>
                    <span class="val" title="{{ $customerLabel }}">{{ $customerLabel }}</span>
                    <span class="lbl">Area</span>
                    <span class="val">{{ $areaLabel }}</span>
                    <span class="lbl">Type</span>
                    <span class="val">{{ $typeLabel }}</span>
                </div>
            </div>
            <div class="meta-box">
                <h6>Report</h6>
                <div class="meta-grid">
                    <span class="lbl">Generated</span><span class="val">{{ now()->format('d-M-Y h:i A') }}</span>
                    <span class="lbl">Invoices</span><span class="val">{{ $count }}</span>
                    <span class="lbl">Items</span><span class="val">{{ number_format($totalItems) }}</span>
                    <span class="lbl">Corten</span><span class="val">{{ number_format($totalCorten) }}</span>
                </div>
            </div>
        </div>
        
        <div class="summary-stats">
            <div class="stat-box">
                <div class="stat-label">Invoices</div>
                <div class="stat-value">{{ $count }}</div>
            </div>
            <div class="stat-box amount">
                <div class="stat-label">Total Amount</div>
                <div class="stat-value">Rs. {{ $fmt($totalAmount) }}</div>
            </div>
            <div class="stat-box credit">
                <div class="stat-label">Credit Sale</div>
                <div class="stat-value">Rs. {{ $fmt($creditSaleAmount) }}</div>
            </div>
            <div class="stat-box cash">
                <div class="stat-label">Cash Sale</div>
                <div class="stat-value">Rs. {{ $fmt($cashSaleAmount) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Avg. Invoice</div>
                <div class="stat-value">Rs. {{ $fmt($avg) }}</div>
            </div>
        </div>
        
        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width:3%;">#</th>
                    <th style="width:9%;">Invoice</th>
                    <th style="width:8%;">Order</th>
                    <th style="width:7%;">Date</th>
                    <th style="width:13%;">Customer</th>
                    <th style="width:28%;">Address</th>
                    <th style="width:5%;">Items</th>
                    <th style="width:5%;">Corten</th>
                    <th style="width:11%;">Amount</th>
                    <th style="width:11%;">Bilty No.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $index => $invoice)
                    @php
                        try {
                            $dateLabel = $invoice->invoiceDate
                                ? \Carbon\Carbon::parse($invoice->invoiceDate)->format('d-M-Y')
                                : '—';
                        } catch (\Throwable $e) {
                            $dateLabel = '—';
                        }
                        $addrPart = trim((string) ($invoice->customerAddress ?? ''));
                        $cityPart = trim((string) ($invoice->customerCity ?? $invoice->city ?? ''));
                        $address = trim(implode(', ', array_filter([$addrPart, $cityPart], fn ($p) => $p !== ''))) ?: '—';
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center"><strong>{{ $invoice->invoiceNo }}</strong></td>
                        <td class="text-center">{{ $invoice->orderNo ?: '—' }}</td>
                        <td class="text-center">{{ $dateLabel }}</td>
                        <td class="cell-desc">{{ $invoice->customerName }}</td>
                        <td class="cell-address">{{ $address }}</td>
                        <td class="text-center">{{ $invoice->noOfItems }}</td>
                        <td class="text-center">{{ $invoice->corten ?: 0 }}</td>
                        <td class="text-end amt-total">Rs. {{ $fmt($invoice->grandTotal) }}</td>
                        <td class="text-center">{{ $invoice->biltyNo ?: '—' }}</td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="10">No invoices found for the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($count > 0)
            <tfoot>
                <tr>
                    <td colspan="6" class="text-end">TOTAL</td>
                    <td class="text-center">{{ number_format($totalItems) }}</td>
                    <td class="text-center">{{ number_format($totalCorten) }}</td>
                    <td class="text-end">Rs. {{ $fmt($totalAmount) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
        
        <div class="report-footer">
            Computer-generated report · © {{ date('Y') }} Trade Gate
        </div>
    </div>
    
    <a href="{{ url('/saleSummary') }}" class="btn btn-secondary btn-back no-print">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
    <button type="button" onclick="window.print()" class="btn btn-primary btn-print no-print">
        <i class="bi bi-printer me-1"></i>Print Report
    </button>
</body>
</html>
