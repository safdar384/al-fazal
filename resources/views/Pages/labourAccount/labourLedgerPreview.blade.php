<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Labour Ledger — {{ $labour->labourName }}</title>
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
                min-height: 0;
            }
            .ledger-table thead th {
                background: #0f2747 !important;
                color: #fff !important;
            }
            .ledger-table tbody tr:nth-child(even) td {
                background: #f4f7fb !important;
            }
            .type-pill, .meta-bar, .summary-section {
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
        .meta-grid .val { color: #1a1a1a; font-weight: 600; }

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
        .ledger-table tbody td {
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
        .ledger-table .text-end { text-align: right !important; }
        .ledger-table .text-center { text-align: center !important; }
        .ledger-table .text-left { text-align: left !important; }
        .ledger-table .cell-desc {
            text-align: left !important;
            font-weight: 500;
        }
        .ledger-table .amt-credit { color: #198754; font-weight: 600; }
        .ledger-table .amt-debit { color: #dc3545; font-weight: 600; }
        .ledger-table .amt-balance { color: #0f2747; font-weight: 700; }
        .type-pill {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 0.58rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .type-work { background: #e7f1ff; color: #0b5ed7; }
        .type-cash { background: #e8f5e9; color: #198754; }
        .type-other { background: #eef1f4; color: #495057; }
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
            padding: 8px 6px;
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
            font-size: 0.92rem;
            font-weight: 700;
        }
        .summary-item.credit h6 { color: #198754; }
        .summary-item.debit h6 { color: #dc3545; }
        .summary-item.balance h6 { color: #0f2747; }
        .btn-print, .btn-back, .btn-pdf {
            position: fixed;
            bottom: 24px;
            padding: 9px 18px;
            font-size: 0.85rem;
            box-shadow: 0 4px 14px rgba(0,0,0,0.18);
            z-index: 1000;
            border-radius: 4px;
        }
        .btn-back { left: 24px; }
        .btn-pdf { right: 140px; }
        .btn-print { right: 24px; background: #0f2747; border-color: #0f2747; }
        .btn-print:hover { background: #1a4a8a; border-color: #1a4a8a; }
    </style>
</head>
<body>
@php
    $category = $category ?? 'worker';
    $fmt = static function ($value) {
        if ($value === null || $value === '') {
            return '—';
        }
        return number_format((float) $value, 2);
    };
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
            <h3>LABOUR LEDGER REPORT</h3>
        </div>

        <div class="meta-bar">
            <div class="meta-box">
                <h6>Labour</h6>
                <div class="meta-grid">
                    <span class="lbl">Code</span><span class="val">{{ $labour->labourCode }}</span>
                    <span class="lbl">Name</span><span class="val">{{ $labour->labourName }}</span>
                    <span class="lbl">Category</span><span class="val">{{ $labour->labourCatagory }}</span>
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
                @if($category === 'ajent')
                <tr>
                    <th style="width:8%;">Date</th>
                    <th style="width:22%;">Description</th>
                    <th style="width:12%;">Bank</th>
                    <th style="width:9%;">Market</th>
                    <th style="width:8%;">Recovery</th>
                    <th style="width:6%;">Comm%</th>
                    <th style="width:6%;">Rec%</th>
                    <th style="width:6%;">Adv</th>
                    <th style="width:8%;">Credit</th>
                    <th style="width:8%;">Debit</th>
                    <th style="width:9%;">Balance</th>
                </tr>
                @elseif($category === 'fitter')
                <tr>
                    <th style="width:8%;">Date</th>
                    <th style="width:9%;">Txn No</th>
                    <th style="width:7%;">Type</th>
                    <th style="width:28%;">Product / Description</th>
                    <th style="width:10%;">Work</th>
                    <th style="width:6%;">Qty</th>
                    <th style="width:8%;">Rate</th>
                    <th style="width:8%;">Credit</th>
                    <th style="width:8%;">Debit</th>
                    <th style="width:8%;">Balance</th>
                </tr>
                @else
                <tr>
                    <th style="width:10%;">Date</th>
                    <th style="width:36%;">Description</th>
                    <th style="width:16%;">Bank</th>
                    <th style="width:8%;">Advance</th>
                    <th style="width:10%;">Credit</th>
                    <th style="width:10%;">Debit</th>
                    <th style="width:10%;">Balance</th>
                </tr>
                @endif
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    @php
                        $totalCredit += (float) ($entry->credit ?? 0);
                        $totalDebit += (float) ($entry->debit ?? 0);
                        $dateLabel = $entry->date ?? $entry->transactionDate ?? '';
                        if ($dateLabel instanceof \Carbon\Carbon) {
                            $dateLabel = $dateLabel->format('d-M-Y');
                        } elseif (is_string($dateLabel) && preg_match('/^\d{4}-\d{2}-\d{2}/', $dateLabel)) {
                            try {
                                $dateLabel = \Carbon\Carbon::parse($dateLabel)->format('d-M-Y');
                            } catch (\Throwable $e) {
                            }
                        }
                    @endphp

                    @if($category === 'ajent')
                    <tr>
                        <td class="text-center">{{ $dateLabel ?: '—' }}</td>
                        <td class="cell-desc" title="{{ $entry->description ?? '' }}">{{ $entry->description ?: '—' }}</td>
                        <td class="text-center" title="{{ $entry->bankAccount ?? '' }}">{{ $entry->bankAccount ?: '—' }}</td>
                        <td class="text-end">{{ $fmt($entry->marketBalance ?? null) }}</td>
                        <td class="text-end">{{ $fmt($entry->recovery ?? null) }}</td>
                        <td class="text-end">{{ $fmt($entry->commissionPercent ?? null) }}</td>
                        <td class="text-end">{{ $fmt($entry->recoveryCommPercent ?? null) }}</td>
                        <td class="text-center">{{ !empty($entry->isAdvance) ? 'Yes' : 'No' }}</td>
                        <td class="text-end amt-credit">{{ $fmtAmt($entry->credit ?? 0) }}</td>
                        <td class="text-end amt-debit">{{ $fmtAmt($entry->debit ?? 0) }}</td>
                        <td class="text-end amt-balance">{{ number_format((float) ($entry->balance ?? 0), 2) }}</td>
                    </tr>
                    @elseif($category === 'fitter')
                    @php
                        $isCash = strcasecmp((string) ($entry->txnType ?? ''), 'Cash') === 0;
                        $productLabel = $entry->productName ?: ($entry->description ?? '');
                        $txnType = (string) ($entry->txnType ?: '—');
                        $typeClass = $isCash ? 'type-cash' : (strcasecmp($txnType, 'Work') === 0 ? 'type-work' : 'type-other');
                    @endphp
                    <tr>
                        <td class="text-center">{{ $dateLabel ?: '—' }}</td>
                        <td class="text-center">{{ $entry->txnNo ?: '—' }}</td>
                        <td class="text-center">
                            <span class="type-pill {{ $typeClass }}">{{ $txnType }}</span>
                        </td>
                        <td class="cell-desc" title="{{ $productLabel }}">{{ $productLabel ?: '—' }}</td>
                        <td class="text-center" title="{{ $isCash ? '' : ($entry->workName ?? '') }}">{{ $isCash ? '—' : ($entry->workName ?: '—') }}</td>
                        <td class="text-end">{{ $isCash ? '—' : $fmt($entry->qty ?? null) }}</td>
                        <td class="text-end">{{ $isCash ? '—' : $fmt($entry->rate ?? null) }}</td>
                        <td class="text-end amt-credit">{{ $fmtAmt($entry->credit ?? 0) }}</td>
                        <td class="text-end amt-debit">{{ $fmtAmt($entry->debit ?? 0) }}</td>
                        <td class="text-end amt-balance">{{ number_format((float) ($entry->balance ?? 0), 2) }}</td>
                    </tr>
                    @else
                    <tr>
                        <td class="text-center">{{ $dateLabel ?: '—' }}</td>
                        <td class="cell-desc" title="{{ $entry->description ?? '' }}">{{ $entry->description ?: '—' }}</td>
                        <td class="text-center" title="{{ $entry->bankAccount ?? '' }}">{{ $entry->bankAccount ?: '—' }}</td>
                        <td class="text-center">{{ !empty($entry->isAdvance) ? 'Yes' : 'No' }}</td>
                        <td class="text-end amt-credit">{{ $fmtAmt($entry->credit ?? 0) }}</td>
                        <td class="text-end amt-debit">{{ $fmtAmt($entry->debit ?? 0) }}</td>
                        <td class="text-end amt-balance">{{ number_format((float) ($entry->balance ?? 0), 2) }}</td>
                    </tr>
                    @endif
                @empty
                    <tr class="empty-row">
                        <td colspan="{{ $category === 'ajent' ? 11 : ($category === 'fitter' ? 10 : 7) }}">
                            No transactions in the selected period.
                        </td>
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
    <a href="{{ request()->fullUrlWithQuery(['download_pdf' => 1]) }}" class="btn btn-danger btn-pdf no-print">
        <i class="bi bi-file-earmark-pdf"></i> PDF
    </a>
    <button class="btn btn-primary btn-print no-print" type="button" onclick="window.print()">
        <i class="bi bi-printer"></i> Print
    </button>
</body>
</html>
