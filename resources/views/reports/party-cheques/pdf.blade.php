<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Party Cheques — {{ $scopeLabel }}</title>
    @include('partials.report-pdf-styles')
</head>
<body>
@php $fmt = static fn ($n) => number_format((float) $n, 2); @endphp

@include('components.invoiceHeaderPdf')

<div class="report-title">
    <h3>{{ $scopeLabel }}</h3>
</div>

<table class="meta-bar">
    <tr>
        <td class="meta-box">
            <h6>Filters</h6>
            <table class="meta-grid">
                <tr><td class="lbl">Scope</td><td class="val">{{ $scopeLabel }}</td></tr>
                <tr>
                    <td class="lbl">Period</td>
                    <td class="val">
                        @if(!empty($fromDate) || !empty($toDate))
                            {{ $fromDate ?: '…' }} to {{ $toDate ?: '…' }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
            </table>
        </td>
        <td class="meta-box">
            <h6>Report</h6>
            <table class="meta-grid">
                <tr><td class="lbl">Generated</td><td class="val">{{ now()->format('d-M-Y h:i A') }}</td></tr>
                <tr><td class="lbl">Cheques</td><td class="val">{{ $totals['count'] ?? 0 }}</td></tr>
                <tr><td class="lbl">Amount</td><td class="val">Rs. {{ $fmt($totals['amount'] ?? 0) }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="summary-stats">
    <tr>
        <td class="stat-box">
            <span class="stat-label">Total Cheques</span>
            <span class="stat-value">{{ (int) ($totals['count'] ?? 0) }}</span>
        </td>
        <td class="stat-box">
            <span class="stat-label">Cheque Amount</span>
            <span class="stat-value">Rs. {{ $fmt($totals['amount'] ?? 0) }}</span>
        </td>
        <td class="stat-box">
            <span class="stat-label">Pending</span>
            <span class="stat-value">{{ (int) ($totals['pending_count'] ?? 0) }} / {{ $fmt($totals['pending_amount'] ?? 0) }}</span>
        </td>
        <td class="stat-box">
            <span class="stat-label">Cleared</span>
            <span class="stat-value">{{ (int) ($totals['cleared_count'] ?? 0) }} / {{ $fmt($totals['cleared_amount'] ?? 0) }}</span>
        </td>
    </tr>
</table>

<table class="ledger-table">
    <thead>
        <tr>
            <th style="width:4%;">#</th>
            <th style="width:9%;">Due</th>
            <th style="width:9%;">Chq Date</th>
            <th style="width:22%;">Party</th>
            <th style="width:8%;">Area</th>
            <th style="width:12%;">Bank</th>
            <th style="width:10%;">Cheque No</th>
            <th style="width:10%;">Amount</th>
            <th style="width:8%;">Worker</th>
            <th style="width:8%;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($entries as $idx => $row)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-center">{{ $row['due_date'] ?? '—' }}</td>
                <td class="text-center">{{ $row['cheque_date'] ?? '—' }}</td>
                <td class="cell-desc">{{ $row['party'] ?? '—' }}</td>
                <td class="text-center">{{ $row['area'] ?? '—' }}</td>
                <td class="cell-desc">{{ $row['bank_name'] ?? '—' }}</td>
                <td class="text-center">{{ $row['cheque_no'] ?? '—' }}</td>
                <td class="amt-balance">{{ $fmt($row['amount'] ?? 0) }}</td>
                <td class="text-center">{{ $row['worker_name'] ?? '—' }}</td>
                <td class="text-center">{{ $row['status'] ?? '—' }}</td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="10">No cheques found.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="report-footer">Computer-generated report · © {{ date('Y') }} Al Fazal Traders</div>
</body>
</html>
