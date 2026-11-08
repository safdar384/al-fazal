<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilty Report by Guds</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Source+Sans+3:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --ink: #12263a;
            --muted: #5f6f7f;
            --line: #d5dde6;
            --soft: #f5f7fa;
        }
        * { box-sizing: border-box; }
        @page { size: A4 portrait; margin: 12mm; }
        @media print {
            .no-print { display: none !important; }
            html, body {
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .sheet {
                box-shadow: none !important;
                border: none !important;
                margin: 0;
                max-width: 100%;
                padding: 0;
            }
            .bg-entry { break-inside: avoid; }
        }
        body {
            background: #e7edf4;
            font-family: "Source Sans 3", "Segoe UI", sans-serif;
            color: var(--ink);
            padding: 14px;
        }
        .sheet {
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 8px 28px rgba(18, 38, 58, 0.1);
            border-radius: 6px;
            padding: 18px 22px 24px;
        }
        .report-title {
            font-family: "Libre Baskerville", Georgia, serif;
            text-align: center;
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--ink);
            margin: 10px 0 4px;
            letter-spacing: 0.2px;
        }
        .report-meta {
            text-align: center;
            font-size: 0.82rem;
            color: var(--muted);
            margin-bottom: 18px;
            letter-spacing: 0.2px;
        }
        .bg-empty {
            text-align: center;
            color: var(--muted);
            padding: 2rem 1rem;
            border: 1px dashed var(--line);
            border-radius: 6px;
        }
        .bg-block { margin-bottom: 1.75rem; }
        .bg-guds-title {
            font-family: "Libre Baskerville", Georgia, serif;
            font-size: 1.28rem;
            font-weight: 700;
            color: var(--ink);
            margin: 0 0 1rem;
            padding: 0.25rem 0 0.55rem;
            border-bottom: 2px solid var(--ink);
            letter-spacing: 0.4px;
            text-transform: uppercase;
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 1rem;
        }
        .bg-guds-name { flex: 1; min-width: 0; }
        .bg-guds-count {
            font-family: "Source Sans 3", sans-serif;
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: none;
            white-space: nowrap;
            color: var(--ink);
        }
        .bg-entry {
            padding: 1rem 0 0.35rem;
            border-bottom: none;
        }
        .bg-receiver {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        .bg-entry-num {
            font-family: "Libre Baskerville", Georgia, serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.2;
            min-width: 1.6rem;
            flex-shrink: 0;
        }
        .bg-receiver-body { flex: 1; min-width: 0; }
        .bg-receiver-label {
            font-family: "Source Sans 3", sans-serif;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 0.35rem;
        }
        .bg-receiver-address {
            font-family: "Libre Baskerville", Georgia, serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.4;
        }
        .bg-metrics {
            display: flex;
            justify-content: flex-end;
            align-items: baseline;
            flex-wrap: wrap;
            gap: 1.75rem 2.5rem;
            margin-top: 1.35rem;
            padding-bottom: 0.85rem;
            border-bottom: 1.5px solid var(--ink);
        }
        .bg-metric {
            display: inline-flex;
            flex-direction: row;
            align-items: baseline;
            gap: 0.45rem;
            white-space: nowrap;
        }
        .bg-metric-lbl {
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--muted);
        }
        .bg-metric-lbl::after { content: ":"; }
        .bg-metric-val {
            font-family: "Libre Baskerville", Georgia, serif;
            font-size: 1.45rem;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.15;
            font-variant-numeric: tabular-nums;
        }
        .toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button type="button" class="btn btn-dark btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>Print
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.close()">Close</button>
    </div>

    <div class="sheet">
        @include('components.invoiceHeader')

        <div class="report-title">Bilty Report by Guds</div>
        <div class="report-meta">
            Period:
            {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}
            —
            {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}
            @if ($filters['guds_name'])
                · Guds: {{ $filters['guds_name'] }}
            @endif
            · Printed: {{ now()->format('d M Y h:i A') }}
        </div>

        @include('reports.bilty-guds.partials.table', ['report' => $report])
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 300);
        });
    </script>
</body>
</html>
