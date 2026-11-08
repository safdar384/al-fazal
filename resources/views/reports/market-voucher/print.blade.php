<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Market Voucher — {{ $report['meta']['area_code'] }}</title>
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
            .mv-report-table thead th {
                background: #0f2747 !important;
                color: #fff !important;
            }
            .mv-report-table tbody tr:nth-child(even) td {
                background: #f4f7fb !important;
            }
            .mv-report-table tfoot td {
                background: #e8eef5 !important;
            }
            .meta-bar, .meta-box {
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
        .company-tagline { color: #5a6a7a; font-size: 0.72rem; margin-top: 2px; }
        .company-contact { color: #6b7c8d; font-size: 0.68rem; margin-top: 1px; }
        .company-monogram {
            flex: 0 0 72px;
            text-align: center;
            border: 2px solid #0f2747;
            border-radius: 4px;
            padding: 4px 6px;
        }
        .monogram-logo { font-size: 1.75rem; font-weight: 800; color: #0f2747; line-height: 1; }
        .monogram-text { font-size: 0.52rem; color: #5a6a7a; font-weight: 700; letter-spacing: 1px; }
        .report-title { text-align: center; margin: 6px 0 8px; }
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
        .mv-report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 0.68rem;
            margin-bottom: 10px;
        }
        .mv-report-table thead th {
            background: #0f2747;
            color: #fff;
            padding: 6px 4px;
            font-size: 0.6rem;
            font-weight: 700;
            text-align: center;
            border: 1px solid #0a1c33;
            text-transform: uppercase;
            letter-spacing: 0.25px;
        }
        .mv-report-table tbody td,
        .mv-report-table tfoot td {
            padding: 3px 4px;
            border: 1px solid #cfd8e3;
            vertical-align: middle;
            height: 24px;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .mv-report-table tbody tr:nth-child(even) td { background: #f4f7fb; }
        .mv-report-table tfoot td {
            background: #e8eef5;
            font-weight: 700;
            color: #0f2747;
        }
        .mv-report-table .text-end { text-align: right !important; }
        .mv-report-table .text-center { text-align: center !important; }
        .mv-manual-cell { background: #fff !important; min-height: 14px; }
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
    $meta = $report['meta'];
    $fromLabel = \Carbon\Carbon::parse($meta['from'])->format('d-M-Y');
    $toLabel = \Carbon\Carbon::parse($meta['to'])->format('d-M-Y');
@endphp

    <div class="ledger-container">
        @include('components.invoiceHeader')

        <div class="report-title">
            <h3>MARKET VOUCHER</h3>
        </div>

        <div class="meta-bar">
            <div class="meta-box">
                <h6>Filters</h6>
                <div class="meta-grid">
                    <span class="lbl">Area</span>
                    <span class="val">{{ $meta['area_code'] }} — {{ $meta['area_name'] }}</span>
                    <span class="lbl">Agent</span>
                    <span class="val">{{ $meta['agent_name'] ?: '—' }}</span>
                    <span class="lbl">Period</span>
                    <span class="val">{{ $fromLabel }} to {{ $toLabel }}</span>
                </div>
            </div>
            <div class="meta-box">
                <h6>Report</h6>
                <div class="meta-grid">
                    <span class="lbl">Generated</span>
                    <span class="val">{{ now()->format('d-M-Y h:i A') }}</span>
                    <span class="lbl">Customers</span>
                    <span class="val">{{ $report['rows']->count() }}</span>
                </div>
            </div>
        </div>

        @include('reports.market-voucher.partials.table', ['report' => $report])

        <div class="report-footer">
            This is a computer-generated report. &copy; {{ date('Y') }} Trade Gate
        </div>
    </div>

    <button type="button" class="btn btn-secondary btn-back no-print" onclick="window.close()">
        <i class="bi bi-x-lg"></i> Close
    </button>
    <button type="button" onclick="window.print()" class="btn btn-primary btn-print no-print">
        <i class="bi bi-printer"></i> Print Report
    </button>
</body>
</html>
