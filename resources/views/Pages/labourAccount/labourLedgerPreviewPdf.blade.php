<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Labour Ledger — {{ $labour->labourName ?? '' }}</title>
    @include('partials.report-pdf-styles')
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

@include('components.invoiceHeaderPdf')

<div class="report-title">
    <h3>Labour Ledger Report</h3>
</div>

<table class="meta-bar">
    <tr>
        <td class="meta-box">
            <h6>Labour</h6>
            <table class="meta-grid">
                <tr><td class="lbl">Code</td><td class="val">{{ $labour->labourCode ?? '—' }}</td></tr>
                <tr><td class="lbl">Name</td><td class="val">{{ $labour->labourName ?? '—' }}</td></tr>
                <tr><td class="lbl">Category</td><td class="val">{{ $category ?? ($labour->labourCatagory ?? '—') }}</td></tr>
            </table>
        </td>
        <td class="meta-box">
            <h6>Report</h6>
            <table class="meta-grid">
                <tr><td class="lbl">Print Date</td><td class="val">{{ now()->format('d-M-Y') }}</td></tr>
                <tr><td class="lbl">Period</td><td class="val">{{ $periodText }}</td></tr>
                <tr><td class="lbl">Closing Bal.</td><td class="val">Rs. {{ number_format((float) $balance, 2) }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="ledger-table">
    <thead>
        <tr>
            <th style="width:14%;">Date</th>
            <th style="width:42%;">Description</th>
            <th style="width:15%;">Credit</th>
            <th style="width:15%;">Debit</th>
            <th style="width:14%;">Balance</th>
        </tr>
    </thead>
    <tbody>
        @forelse($entries as $entry)
            @php
                $totalCredit += (float) ($entry->credit ?? 0);
                $totalDebit += (float) ($entry->debit ?? 0);
                $dateRaw = $entry->transactionDate ?? ($entry->date ?? null);
                try {
                    $dateLabel = $dateRaw ? \Carbon\Carbon::parse($dateRaw)->format('d-M-Y') : '—';
                } catch (\Throwable $e) {
                    $dateLabel = (string) ($dateRaw ?? '—');
                }
            @endphp
            <tr>
                <td class="text-center">{{ $dateLabel }}</td>
                <td class="cell-desc">{{ $entry->description ?? '—' }}</td>
                <td class="amt-credit">{{ $fmtAmt($entry->credit ?? 0) }}</td>
                <td class="amt-debit">{{ $fmtAmt($entry->debit ?? 0) }}</td>
                <td class="amt-balance">{{ number_format((float) ($entry->balance ?? 0), 2) }}</td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="5">No transactions in the selected period.</td></tr>
        @endforelse
    </tbody>
</table>

<table class="summary-section">
    <tr>
        <td class="summary-item credit">
            <small>Total Credit</small>
            <div class="val">Rs. {{ number_format($totalCredit, 2) }}</div>
        </td>
        <td class="summary-item debit">
            <small>Total Debit</small>
            <div class="val">Rs. {{ number_format($totalDebit, 2) }}</div>
        </td>
        <td class="summary-item balance">
            <small>Closing Balance</small>
            <div class="val">Rs. {{ number_format((float) $balance, 2) }}</div>
        </td>
    </tr>
</table>

<div class="report-footer">Computer-generated report · © {{ date('Y') }} Al Fazal Traders</div>
</body>
</html>
