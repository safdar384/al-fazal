<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Ledger — {{ $customer->custName }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        @media print {
            .no-print { display: none !important; }
            html, body {
                width: 210mm;
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
                min-height: 0;
            }
            .ledger-table thead th {
                background: #0f2747 !important;
                color: #fff !important;
            }
            .ledger-table tbody tr:nth-child(even) td {
                background: #f4f7fb !important;
            }
            .meta-bar, .summary-section {
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
            max-width: 820px;
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
            grid-template-columns: 1.25fr 1fr;
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

        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 0.72rem;
            margin-bottom: 10px;
        }
        .ledger-table thead th {
            background: #0f2747;
            color: #fff;
            padding: 7px 6px;
            font-size: 0.65rem;
            font-weight: 700;
            text-align: center;
            border: 1px solid #0a1c33;
            vertical-align: middle;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .ledger-table tbody td {
            padding: 5px 6px;
            border: 1px solid #cfd8e3;
            vertical-align: middle;
            height: 28px;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ledger-table tbody tr:nth-child(even) td { background: #f4f7fb; }
        .ledger-table tbody tr:hover td { background: #eaf1f9; }
        .ledger-table .text-end { text-align: right !important; }
        .ledger-table .text-center { text-align: center !important; }
        .ledger-table .cell-desc {
            text-align: left !important;
            font-weight: 500;
        }
        .ledger-table .amt-credit { color: #198754; font-weight: 600; }
        .ledger-table .amt-debit { color: #dc3545; font-weight: 600; }
        .ledger-table .amt-balance { color: #0f2747; font-weight: 700; }
        .empty-row td {
            text-align: center;
            color: #6b7c8d;
            padding: 18px 8px !important;
            white-space: normal !important;
        }
        .summary-section {
            display: flex;
            border: 1px solid #0f2747;
            border-radius: 3px;
            overflow: hidden;
            background: #fff;
        }
        .summary-item {
            flex: 1;
            text-align: center;
            padding: 9px 6px;
        }
        .summary-item:not(:last-child) {
            border-right: 1px solid #d5dde8;
        }
        .summary-item small {
            display: block;
            color: #6b7c8d;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
        }
        .summary-item h6 {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 700;
        }
        .summary-item.credit h6 { color: #198754; }
        .summary-item.debit h6 { color: #dc3545; }
        .summary-item.balance h6 { color: #0f2747; }
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
    $fmtAmt = static function ($value) {
        $n = (float) ($value ?? 0);
        return $n > 0 ? number_format($n, 2) : '—';
    };
    $totalCredit = 0.0;
    $totalDebit = 0.0;
@endphp

    <div class="ledger-container">
        @include('components.invoiceHeader')

        <div class="report-title">
            <h3>CUSTOMER LEDGER REPORT</h3>
        </div>

        <div class="meta-bar">
            <div class="meta-box">
                <h6>Customer</h6>
                <div class="meta-grid">
                    <span class="lbl">Code</span><span class="val">{{ $customer->custCode }}</span>
                    <span class="lbl">Name</span><span class="val" title="{{ $customer->custName }}">{{ $customer->custName }}</span>
                    @if(!empty($customer->bussnessName))
                        <span class="lbl">Business</span><span class="val" title="{{ $customer->bussnessName }}">{{ $customer->bussnessName }}</span>
                    @endif
                    <span class="lbl">Area</span><span class="val">{{ $customer->custArea ?: '—' }}</span>
                    <span class="lbl">City</span><span class="val">{{ $customer->custCity ?: '—' }}</span>
                </div>
            </div>
            <div class="meta-box">
                <h6>Report</h6>
                <div class="meta-grid">
                    <span class="lbl">Print Date</span><span class="val">{{ now()->format('d-M-Y') }}</span>
                    <span class="lbl">Period</span><span class="val">{{ $periodText }}</span>
                    <span class="lbl">Closing Bal.</span><span class="val">Rs. {{ number_format((float) $balance, 2) }}</span>
                </div>
            </div>
        </div>

        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width: 14%;">Date</th>
                    <th style="width: 42%;">Description</th>
                    <th style="width: 15%;">Credit</th>
                    <th style="width: 15%;">Debit</th>
                    <th style="width: 14%;">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    @php
                        $totalCredit += (float) ($entry->credit ?? 0);
                        $totalDebit += (float) ($entry->debit ?? 0);
                        $dateRaw = $entry->customerDate ?? null;
                        try {
                            $dateLabel = $dateRaw ? \Carbon\Carbon::parse($dateRaw)->format('d-M-Y') : '—';
                        } catch (\Throwable $e) {
                            $dateLabel = is_string($dateRaw) ? $dateRaw : '—';
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $dateLabel }}</td>
                        <td class="cell-desc" title="{{ $entry->description ?? '' }}">{{ $entry->description ?: '—' }}</td>
                        <td class="text-end amt-credit">{{ $fmtAmt($entry->credit ?? 0) }}</td>
                        <td class="text-end amt-debit">{{ $fmtAmt($entry->debit ?? 0) }}</td>
                        <td class="text-end amt-balance">{{ number_format((float) ($entry->balance ?? 0), 2) }}</td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="5">No transactions in the selected period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="summary-section">
            <div class="summary-item credit">
                <small>Total Credit</small>
                <h6>Rs. {{ number_format($totalCredit, 2) }}</h6>
            </div>
            <div class="summary-item debit">
                <small>Total Debit</small>
                <h6>Rs. {{ number_format($totalDebit, 2) }}</h6>
            </div>
            <div class="summary-item balance">
                <small>Closing Balance</small>
                <h6>Rs. {{ number_format((float) $balance, 2) }}</h6>
            </div>
        </div>
    </div>

    <button class="btn btn-secondary btn-back no-print" type="button" onclick="window.close()">
        <i class="bi bi-arrow-left"></i> Back
    </button>
    <button class="btn btn-primary btn-print no-print" type="button" onclick="window.print()">
        <i class="bi bi-printer"></i> Print
    </button>
</body>
</html>
