<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expense Report</title>
    @include('partials.report-pdf-styles')
</head>
<body>
@php
    $fmt = static fn ($n) => number_format((float) $n, 2);
    $totalDebit = (float) $rows->sum('debit');
    $totalCredit = (float) $rows->sum('credit');
    $count = $rows->count();
@endphp

@include('components.invoiceHeaderPdf')

<div class="report-title">
    <h3>Expense Ledger Report</h3>
</div>

<table class="meta-bar">
    <tr>
        <td class="meta-box">
            <h6>Filters</h6>
            <table class="meta-grid">
                <tr><td class="lbl">Period</td><td class="val">{{ $periodText }}</td></tr>
                @if(request('expenseCode'))
                    <tr><td class="lbl">Expense</td><td class="val">{{ request('expenseCode') }}</td></tr>
                @endif
                @if(request('category'))
                    <tr><td class="lbl">Category</td><td class="val">{{ request('category') }}</td></tr>
                @endif
            </table>
        </td>
        <td class="meta-box">
            <h6>Report</h6>
            <table class="meta-grid">
                <tr><td class="lbl">Generated</td><td class="val">{{ now()->format('d-M-Y h:i A') }}</td></tr>
                <tr><td class="lbl">Entries</td><td class="val">{{ $count }}</td></tr>
                <tr><td class="lbl">Debit</td><td class="val">Rs. {{ $fmt($totalDebit) }}</td></tr>
                <tr><td class="lbl">Credit</td><td class="val">Rs. {{ $fmt($totalCredit) }}</td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="summary-stats">
    <tr>
        <td class="stat-box">
            <span class="stat-label">Entries</span>
            <span class="stat-value">{{ $count }}</span>
        </td>
        <td class="stat-box">
            <span class="stat-label">Total Debit</span>
            <span class="stat-value">Rs. {{ $fmt($totalDebit) }}</span>
        </td>
        <td class="stat-box">
            <span class="stat-label">Total Credit</span>
            <span class="stat-value">Rs. {{ $fmt($totalCredit) }}</span>
        </td>
    </tr>
</table>

<table class="ledger-table">
    <thead>
        <tr>
            <th style="width:10%;">Date</th>
            <th style="width:18%;">Expense</th>
            <th style="width:8%;">Code</th>
            <th style="width:34%;">Description</th>
            <th style="width:10%;">Category</th>
            <th style="width:10%;">Debit</th>
            <th style="width:10%;">Credit</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
            <tr>
                <td class="text-center">{{ $row->entryDate ? \Carbon\Carbon::parse($row->entryDate)->format('d-M-Y') : '—' }}</td>
                <td class="cell-desc">{{ $row->expenseName ?? '—' }}</td>
                <td class="text-center">{{ $row->expenseCode ?? '—' }}</td>
                <td class="cell-desc">{{ $row->description ?? '—' }}</td>
                <td class="text-center">{{ $row->category ?? '—' }}</td>
                <td class="amt-debit">{{ ((float) ($row->debit ?? 0)) > 0 ? $fmt($row->debit) : '—' }}</td>
                <td class="amt-credit">{{ ((float) ($row->credit ?? 0)) > 0 ? $fmt($row->credit) : '—' }}</td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="7">No transactions found.</td></tr>
        @endforelse
    </tbody>
    @if($rows->isNotEmpty())
    <tfoot>
        <tr>
            <th colspan="5" class="text-end">Totals</th>
            <th class="text-end">{{ $fmt($totalDebit) }}</th>
            <th class="text-end">{{ $fmt($totalCredit) }}</th>
        </tr>
    </tfoot>
    @endif
</table>

<div class="report-footer">Computer-generated report · © {{ date('Y') }} Al Fazal Traders</div>
</body>
</html>
