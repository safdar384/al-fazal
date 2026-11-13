<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cheque in Process</title>
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
            .ledger-table { font-size: 0.6rem; }
            .ledger-table thead th { font-size: 0.54rem; padding: 4px 3px; }
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
    }
@endphp

    <div class="ledger-container">
        @include('reports.profit-loss.partials.navy-header', ['title' => 'Cheque in Process'])

        <div class="meta-bar">
            <div class="meta-box">
                <h6>Filters</h6>
                <div class="meta-grid">
                    <span class="lbl">Worker</span>
                    <span class="val">{{ $workerLabel }}</span>
                    <span class="lbl">Period</span>
                    <span class="val">{{ $period }}</span>
                    <span class="lbl">Search</span>
                    <span class="val">{{ $q !== '' ? $q : '—' }}</span>
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
                <div class="stat-label">In Process</div>
                <div class="stat-value">{{ (int) ($totals['count'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Total Amount</div>
                <div class="stat-value">{{ $fmt($totals['amount'] ?? 0) }}</div>
            </div>
        </div>

        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width:3%;">#</th>
                    <th style="width:8%;">Due</th>
                    <th style="width:8%;">Chq Date</th>
                    <th style="width:10%;">Worker</th>
                    <th style="width:20%;">Party</th>
                    <th style="width:10%;">Bank</th>
                    <th style="width:9%;">Cheque No</th>
                    <th style="width:9%;">Amount</th>
                    <th style="width:13%;">Paid To</th>
                    <th style="width:10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($entries as $idx => $row)
                    @php
                        $paidTo = '—';
                        if (! empty($row['is_paid'])) {
                            $type = ($row['payee_type'] ?? '') === 'bank' ? 'Bank' : 'Party';
                            $paidTo = trim($type.' — '.($row['payee_name'] ?? ''));
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-center">{{ $fmtDate($row['due_date'] ?? null) }}</td>
                        <td class="text-center">{{ $fmtDate($row['cheque_date'] ?? null) }}</td>
                        <td class="cell-desc">{{ $row['worker_name'] ?: '—' }}</td>
                        <td class="cell-party">{{ $row['party'] ?? ($row['customer_code'] ?? '—') }}</td>
                        <td class="cell-desc">{{ $row['bank_name'] ?? '—' }}</td>
                        <td class="text-center">{{ $row['cheque_no'] ?? '—' }}</td>
                        <td class="text-end amt">{{ $fmt($row['amount'] ?? 0) }}</td>
                        <td class="cell-desc">{{ $paidTo }}</td>
                        <td class="text-center">{{ $row['status'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="10">No worker cheques found for selected filters.</td></tr>
                @endforelse
            </tbody>
            @if ($entries->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="7" class="text-end">TOTAL</td>
                        <td class="text-end">{{ $fmt($totals['amount'] ?? 0) }}</td>
                        <td colspan="2"></td>
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
    <a href="{{ request()->fullUrlWithQuery(['download_pdf' => 1]) }}" class="btn btn-danger btn-pdf no-print">
        <i class="bi bi-file-earmark-pdf"></i> PDF
    </a>
    <button type="button" onclick="window.print()" class="btn btn-primary btn-print no-print">
        <i class="bi bi-printer"></i> Print Report
    </button>
</body>
</html>
