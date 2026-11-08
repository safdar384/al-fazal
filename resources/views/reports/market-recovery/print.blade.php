<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Market Recovery — {{ $areaCode }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @include('reports.profit-loss.partials.navy-styles')
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        .ledger-container { max-width: 1400px; }
        .section-title {
            margin: 10px 0 6px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #0f2747;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #d5dde8;
            padding-bottom: 3px;
        }
        .ledger-table tbody td.cell-desc {
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
            .meta-bar, .stat-box, .ledger-table thead th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .ledger-table { font-size: 0.62rem; }
            .ledger-table thead th { font-size: 0.55rem; padding: 4px 3px; }
            .ledger-table tbody td { padding: 3px 4px; }
            .ledger-table tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
@php
    $fmt = static fn ($n) => number_format((float) $n, 2);
@endphp

    <div class="ledger-container">
        @include('reports.profit-loss.partials.navy-header', ['title' => 'Market Recovery Report'])

        <div class="meta-bar">
            <div class="meta-box">
                <h6>Filters</h6>
                <div class="meta-grid">
                    <span class="lbl">Area</span>
                    <span class="val">{{ $areaCode }} — {{ $areaName }}</span>
                    <span class="lbl">Agent</span>
                    <span class="val">{{ $agentName !== '' ? $agentName : '—' }}</span>
                    <span class="lbl">Status</span>
                    <span class="val">Pending (statuse = 0)</span>
                </div>
            </div>
            <div class="meta-box">
                <h6>Report</h6>
                <div class="meta-grid">
                    <span class="lbl">Generated</span>
                    <span class="val">{{ now()->format('d-M-Y h:i A') }}</span>
                    <span class="lbl">Entries</span>
                    <span class="val">{{ $entries->count() }}</span>
                    <span class="lbl">Recovery+Cheque</span>
                    <span class="val">{{ $fmt($totalCredit) }}</span>
                </div>
            </div>
        </div>

        <div class="summary-stats">
            <div class="stat-box">
                <div class="stat-label">Opening Total</div>
                <div class="stat-value">{{ $fmt($totals['opening'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Recovery</div>
                <div class="stat-value">{{ $fmt($totals['recovery'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Cheque</div>
                <div class="stat-value">{{ $fmt($totals['cheque'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Discount</div>
                <div class="stat-value">{{ $fmt($totals['discount'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Bilty</div>
                <div class="stat-value">{{ $fmt($totals['bilty'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Other</div>
                <div class="stat-value">{{ $fmt($totals['other'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Last Balance Total</div>
                <div class="stat-value">{{ $fmt($totals['last'] ?? 0) }}</div>
            </div>
        </div>

        <div class="section-title">Area Entries</div>
        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width:3%;">#</th>
                    <th style="width:6%;">Page No</th>
                    <th style="width:8%;">Date</th>
                    <th style="width:22%;">Description</th>
                    <th style="width:10%;">Opening Balance</th>
                    <th style="width:9%;">Recovery</th>
                    <th style="width:9%;">Cheque</th>
                    <th style="width:8%;">Discount</th>
                    <th style="width:8%;">Bilty</th>
                    <th style="width:8%;">Other</th>
                    <th style="width:9%;">Last Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($entries as $idx => $row)
                    @php
                        $recovery = (float) ($row['recovery'] ?? 0);
                        $cheque = (float) ($row['cheque'] ?? 0);
                        $discount = (float) ($row['discount'] ?? 0);
                        $bilty = (float) ($row['bilty'] ?? 0);
                        $other = (float) ($row['other'] ?? 0);
                        // Legacy flat rows (single category) if grouped keys missing
                        if ($recovery + $cheque + $discount + $bilty + $other <= 0) {
                            $amount = (float) ($row['credit'] ?? 0);
                            $category = (string) ($row['category'] ?? '');
                            if ($category === 'Cheque') {
                                $cheque = $amount;
                            } elseif ($category === 'Recovery') {
                                $recovery = $amount;
                            } elseif ($category === 'Discount') {
                                $discount = $amount;
                            } elseif ($category === 'Bilty') {
                                $bilty = $amount;
                            } else {
                                $other = $amount;
                            }
                        }
                        $desc = trim(($row['code'] ? $row['code'].' — ' : '').($row['description'] ?? ''));
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-center">{{ trim((string) ($row['page_no'] ?? '')) !== '' ? $row['page_no'] : '—' }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($row['date'])->format('d-m-Y') }}</td>
                        <td class="cell-desc">{{ $desc !== '' ? $desc : '—' }}</td>
                        <td class="text-end amt">{{ $fmt($row['opening_balance'] ?? 0) }}</td>
                        <td class="text-end">{{ $recovery > 0 ? $fmt($recovery) : '—' }}</td>
                        <td class="text-end">{{ $cheque > 0 ? $fmt($cheque) : '—' }}</td>
                        <td class="text-end">{{ $discount > 0 ? $fmt($discount) : '—' }}</td>
                        <td class="text-end">{{ $bilty > 0 ? $fmt($bilty) : '—' }}</td>
                        <td class="text-end">{{ $other > 0 ? $fmt($other) : '—' }}</td>
                        <td class="text-end amt">{{ $fmt($row['last_balance'] ?? 0) }}</td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="11">No pending recovery entries for this area.</td></tr>
                @endforelse
            </tbody>
            @if ($entries->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end">TOTAL</td>
                        <td class="text-end">{{ $fmt($totals['opening'] ?? 0) }}</td>
                        <td class="text-end">{{ $fmt($totals['recovery'] ?? 0) }}</td>
                        <td class="text-end">{{ $fmt($totals['cheque'] ?? 0) }}</td>
                        <td class="text-end">{{ $fmt($totals['discount'] ?? 0) }}</td>
                        <td class="text-end">{{ $fmt($totals['bilty'] ?? 0) }}</td>
                        <td class="text-end">{{ $fmt($totals['other'] ?? 0) }}</td>
                        <td class="text-end">{{ $fmt($totals['last'] ?? 0) }}</td>
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
