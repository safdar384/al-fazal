<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Summary Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page {
            size: A4 landscape;
            margin: 8mm;
        }
        @media print {
            .no-print { display: none !important; }
            html, body {
                width: 297mm;
                margin: 0;
                padding: 0;
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .ledger-container {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            .ledger-table thead th {
                background: #0f2747 !important;
                color: #fff !important;
            }
            .ledger-table tbody tr:nth-child(even) td {
                background: #f4f7fb !important;
            }
            .ledger-table tfoot td {
                background: #e8eef5 !important;
            }
            .meta-bar, .summary-stats, .stat-box, .status-pill {
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
            max-width: 1120px;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 4px 24px rgba(15, 39, 71, 0.12);
            border-radius: 4px;
            padding: 14px 16px 16px;
        }
        .company-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f2747;
            padding-bottom: 8px;
            margin-bottom: 8px;
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
        }
        .stat-box {
            flex: 1;
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
        .stat-box.amount .stat-value { color: #dc3545; }
        .stat-box.paid .stat-value { color: #198754; }
        .stat-box.due .stat-value { color: #c77c00; }

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
        .ledger-table .amt-total { color: #0f2747; font-weight: 700; }
        .ledger-table .amt-paid { color: #198754; font-weight: 600; }
        .ledger-table .amt-due { color: #c77c00; font-weight: 600; }
        .status-pill {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 0.58rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .status-paid { background: #e8f5e9; color: #198754; }
        .status-partial { background: #fff3cd; color: #856404; }
        .status-unpaid { background: #fde8ea; color: #842029; }
        .status-other { background: #eef1f4; color: #41464b; }
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
    $totalAmount = (float) $invoices->sum('grandTotal');
    $totalPaid = (float) $invoices->sum('paid_amount');
    $totalDue = max(0, $totalAmount - $totalPaid);
    $totalItems = (int) $invoices->sum('noOfItems');
    $isReturnReport = request('invoice_type') === 'return' || request('purchase_type') === 'return';
    $totalLines = (int) $invoices->sum(function ($inv) {
        $items = $inv->items ?? null;

        return $items ? $items->count() : (int) ($inv->noOfItems ?? 0);
    });
    $totalQty = (float) $invoices->sum(function ($inv) {
        $items = $inv->items ?? null;

        return $items ? (float) $items->sum('qty') : 0;
    });
    $count = $invoices->count();
    $avg = $count > 0 ? $totalAmount / $count : 0;

    $fromLabel = request('fromDate')
        ? \Carbon\Carbon::parse(request('fromDate'))->format('d-M-Y')
        : 'Start';
    $toLabel = request('toDate')
        ? \Carbon\Carbon::parse(request('toDate'))->format('d-M-Y')
        : 'End';
    $supplierLabel = request('supplierCode')
        ? ($invoices->first()->supplierName ?? request('supplierCode'))
        : 'All';

    $statusClass = static function ($status) {
        $s = strtolower(trim((string) $status));
        if ($s === 'paid') return 'status-paid';
        if ($s === 'partial') return 'status-partial';
        if ($s === 'unpaid' || $s === 'pending') return 'status-unpaid';
        if ($s === 'return') return 'status-other';
        return 'status-other';
    };
@endphp

    <div class="ledger-container">
        @include('components.invoiceHeader')

        <div class="report-title">
            <h3>{{ $isReturnReport ? 'RETURN PURCHASE SUMMARY REPORT' : 'PURCHASE SUMMARY REPORT' }}</h3>
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
                    <span class="lbl">Supplier</span>
                    <span class="val" title="{{ $supplierLabel }}">{{ $supplierLabel }}</span>
                </div>
            </div>
            <div class="meta-box">
                <h6>Report</h6>
                <div class="meta-grid">
                    <span class="lbl">Generated</span><span class="val">{{ now()->format('d-M-Y h:i A') }}</span>
                    <span class="lbl">Invoices</span><span class="val">{{ $count }}</span>
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
            <div class="stat-box paid">
                <div class="stat-label">Paid</div>
                <div class="stat-value">Rs. {{ $fmt($totalPaid) }}</div>
            </div>
            <div class="stat-box due">
                <div class="stat-label">Due</div>
                <div class="stat-value">Rs. {{ $fmt($totalDue) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Avg. Invoice</div>
                <div class="stat-value">Rs. {{ $fmt($avg) }}</div>
            </div>
        </div>

        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width:4%;">#</th>
                    <th style="width:11%;">Invoice</th>
                    <th style="width:9%;">Date</th>
                    <th style="width:18%;">Supplier</th>
                    <th style="width:10%;">City</th>
                    <th style="width:6%;">Items</th>
                    <th style="width:7%;">Qty</th>
                    <th style="width:10%;">Total</th>
                    <th style="width:9%;">Paid</th>
                    <th style="width:9%;">Due</th>
                    <th style="width:7%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $index => $invoice)
                    @php
                        $isReturnRow = ($invoice->purchase_type ?? null) === 'return' || ! empty($invoice->is_return);
                        $paid = $isReturnRow ? 0.0 : (float) ($invoice->paid_amount ?? 0);
                        $total = (float) ($invoice->grandTotal ?? 0);
                        $due = $isReturnRow ? 0.0 : max(0, round($total - $paid, 2));
                        $status = $isReturnRow ? 'Return' : ($invoice->payment_status ?: ($invoice->status ?: '—'));
                        $rowItems = $invoice->items ?? collect();
                        try {
                            $dateLabel = $invoice->invoiceDate
                                ? \Carbon\Carbon::parse($invoice->invoiceDate)->format('d-M-Y')
                                : '—';
                        } catch (\Throwable $e) {
                            $dateLabel = '—';
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center" title="{{ $invoice->invoiceNo }}">{{ $invoice->invoiceNo ?: '—' }}</td>
                        <td class="text-center">{{ $dateLabel }}</td>
                        <td class="cell-desc" title="{{ $invoice->supplierName }}">{{ $invoice->supplierName ?: '—' }}</td>
                        <td class="text-center" title="{{ $invoice->city ?? '' }}">{{ $invoice->city ?: '—' }}</td>
                        <td class="text-center">{{ $rowItems->count() ?: (int) ($invoice->noOfItems ?? 0) }}</td>
                        <td class="text-end">{{ $fmt($rowItems->sum('qty')) }}</td>
                        <td class="text-end amt-total">{{ $fmt($total) }}</td>
                        <td class="text-end amt-paid">{{ $fmt($paid) }}</td>
                        <td class="text-end amt-due">{{ $fmt($due) }}</td>
                        <td class="text-center">
                            <span class="status-pill {{ $statusClass($status) }}">{{ $status }}</span>
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="11">No invoices found for the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($count > 0)
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end">TOTAL</td>
                    <td class="text-center">{{ $totalLines }}</td>
                    <td class="text-end">{{ $fmt($totalQty) }}</td>
                    <td class="text-end">{{ $fmt($totalAmount) }}</td>
                    <td class="text-end">{{ $fmt($totalPaid) }}</td>
                    <td class="text-end">{{ $fmt($totalDue) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>

        <div class="report-footer">
            This is a computer-generated report. &copy; {{ date('Y') }} Trade Gate
        </div>
    </div>

    <a href="{{ url('/purchaseSummary') }}" class="btn btn-secondary btn-back no-print">
        <i class="bi bi-arrow-left"></i> Back
    </a>
    <button type="button" onclick="window.print()" class="btn btn-primary btn-print no-print">
        <i class="bi bi-printer"></i> Print Report
    </button>
</body>
</html>
