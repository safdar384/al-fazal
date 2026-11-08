<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Party Cheque Detail — {{ $scopeLabel }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @include('reports.profit-loss.partials.navy-styles')
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        .ledger-container { max-width: 1400px; }
        .ledger-table tbody td.cell-desc,
        .ledger-table tbody td.cell-party {
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: unset !important;
            word-break: break-word;
            height: auto !important;
            text-align: left !important;
        }
        @media print {
            html, body {
                width: 297mm;
                margin: 0;
                padding: 0;
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .ledger-container {
                max-width: 100% !important;
                width: 100% !important;
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
            }
            .ledger-table { font-size: 0.58rem; }
            .ledger-table thead th { font-size: 0.52rem; padding: 4px 3px; }
            .ledger-table tbody td { padding: 3px 3px; }
            .ledger-table tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
@php
    $fmt = static fn ($n) => number_format((float) $n, 2);
    $fmtDate = static function ($d) {
        if (! $d) {
            return '—';
        }
        try {
            return \Carbon\Carbon::parse($d)->format('d-m-Y');
        } catch (\Throwable $e) {
            return (string) $d;
        }
    };
    $period = '—';
    if ($fromDate && $toDate) {
        $period = $fmtDate($fromDate).' to '.$fmtDate($toDate);
    } elseif ($fromDate) {
        $period = 'From '.$fmtDate($fromDate);
    } elseif ($toDate) {
        $period = 'Until '.$fmtDate($toDate);
    } elseif ($scope === 'today') {
        $period = 'Due on/before '.$fmtDate($today);
    } elseif ($scope === 'month') {
        $period = now()->format('M Y');
    }
@endphp

    <div class="ledger-container">
        @include('reports.profit-loss.partials.navy-header', ['title' => 'Party Cheque Detail'])

        <div class="meta-bar">
            <div class="meta-box">
                <h6>Filters</h6>
                <div class="meta-grid">
                    <span class="lbl">Scope</span>
                    <span class="val">{{ $scopeLabel }}</span>
                    <span class="lbl">Period</span>
                    <span class="val">{{ $period }}</span>
                </div>
            </div>
            <div class="meta-box">
                <h6>Report</h6>
                <div class="meta-grid">
                    <span class="lbl">Generated</span>
                    <span class="val">{{ now()->format('d-M-Y h:i A') }}</span>
                    <span class="lbl">Cheques</span>
                    <span class="val">{{ $totals['count'] ?? 0 }}</span>
                    <span class="lbl">Total Amount</span>
                    <span class="val">{{ $fmt($totals['amount'] ?? 0) }}</span>
                </div>
            </div>
        </div>

        <div class="summary-stats">
            <div class="stat-box">
                <div class="stat-label">Total Cheques</div>
                <div class="stat-value">{{ (int) ($totals['count'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Cheque Amount</div>
                <div class="stat-value">{{ $fmt($totals['amount'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Pending</div>
                <div class="stat-value">{{ (int) ($totals['pending_count'] ?? 0) }} / {{ $fmt($totals['pending_amount'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Cleared</div>
                <div class="stat-value">{{ (int) ($totals['cleared_count'] ?? 0) }} / {{ $fmt($totals['cleared_amount'] ?? 0) }}</div>
            </div>
        </div>

        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width:3%;">#</th>
                    <th style="width:7%;">Original Due</th>
                    <th style="width:7%;">Due</th>
                    <th style="width:7%;">Chq Date</th>
                    <th style="width:18%;">Party</th>
                    <th style="width:7%;">Area</th>
                    <th style="width:9%;">Bank</th>
                    <th style="width:8%;">Cheque No</th>
                    <th style="width:8%;">Amount</th>
                    <th style="width:4%;">Days</th>
                    <th style="width:8%;">Worker</th>
                    <th style="width:8%;">Description</th>
                    <th style="width:6%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($entries as $idx => $row)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-center">{{ $fmtDate($row['original_due_date'] ?? null) }}</td>
                        <td class="text-center">{{ $fmtDate($row['due_date'] ?? null) }}</td>
                        <td class="text-center">{{ $fmtDate($row['cheque_date'] ?? null) }}</td>
                        <td class="cell-party">{{ $row['party'] ?? ($row['customer_code'] ?? '—') }}</td>
                        <td class="text-center">{{ $row['area'] ?? '—' }}</td>
                        <td class="cell-desc">{{ $row['bank_name'] ?? '—' }}</td>
                        <td class="text-center">{{ $row['cheque_no'] ?? '—' }}</td>
                        <td class="text-end amt">{{ $fmt($row['amount'] ?? 0) }}</td>
                        <td class="text-center">{{ (int) ($row['days'] ?? 0) }}</td>
                        <td class="cell-desc">{{ $row['worker_name'] ?: '—' }}</td>
                        <td class="cell-desc">{{ $row['description'] ?: '—' }}</td>
                        <td class="text-center">{{ $row['status'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="13">No cheques found for selected filters.</td></tr>
                @endforelse
            </tbody>
            @if ($entries->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="8" class="text-end">TOTAL</td>
                        <td class="text-end">{{ $fmt($totals['amount'] ?? 0) }}</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            @endif
        </table>

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
