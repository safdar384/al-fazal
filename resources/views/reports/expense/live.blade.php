@php
    $fmt = $fmt ?? static fn ($n) => number_format((float) $n, 2);
    $fmtRs = $fmtRs ?? static fn ($n) => 'Rs. ' . number_format((float) $n, 2);
    $isCategory = ($config['mode'] ?? '') === 'category';
    $expenseLabel = 'All';
    if (! empty($filters['expense_code'])) {
        $match = collect($filterOptions['expenses'] ?? [])->firstWhere('expenseCode', $filters['expense_code']);
        $expenseLabel = $match->expenseName ?? $filters['expense_code'];
    }
    $categoryLabel = ($filters['category'] ?? '') !== '' ? $filters['category'] : 'All';
@endphp

<div id="expenseReportLive">
    <div class="meta-bar">
        <div class="meta-box">
            <h6>Filters</h6>
            <div class="meta-grid">
                <span class="lbl">Period</span>
                <span class="val">
                    {{ \Carbon\Carbon::parse($fromDate)->format('d-M-Y') }}
                    to
                    {{ \Carbon\Carbon::parse($toDate)->format('d-M-Y') }}
                </span>
                @if ($config['show']['category'] ?? false)
                    <span class="lbl">Category</span>
                    <span class="val">{{ $categoryLabel }}</span>
                @endif
                @if ($config['show']['expense'] ?? false)
                    <span class="lbl">Expense</span>
                    <span class="val" title="{{ $expenseLabel }}">{{ $expenseLabel }}</span>
                @endif
            </div>
        </div>
        <div class="meta-box">
            <h6>Report</h6>
            <div class="meta-grid">
                <span class="lbl">Generated</span>
                <span class="val">{{ now()->format('d-M-Y h:i A') }}</span>
                <span class="lbl">Rows</span>
                <span class="val">{{ $summary['total_rows'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    <div class="summary-stats">
        <div class="stat-box">
            <div class="stat-label">{{ $isCategory ? 'Categories' : 'Entries' }}</div>
            <div class="stat-value">{{ $summary['total_rows'] ?? 0 }}</div>
        </div>
        @if ($isCategory)
            <div class="stat-box">
                <div class="stat-label">Total Entries</div>
                <div class="stat-value">{{ $summary['total_entries'] ?? 0 }}</div>
            </div>
        @endif
        <div class="stat-box value">
            <div class="stat-label">Total Amount</div>
            <div class="stat-value">{{ $fmtRs($summary['total_amount'] ?? 0) }}</div>
        </div>
    </div>

    @if ($isCategory)
        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width:50%;">Category</th>
                    <th style="width:20%;">Entries</th>
                    <th style="width:30%;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="cell-desc" title="{{ $row->category }}">{{ $row->category }}</td>
                        <td class="text-center">{{ $row->entry_count }}</td>
                        <td class="text-end amt">{{ $fmt($row->total_amount) }}</td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="3">No expense data in this period.</td></tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
                <tfoot>
                    <tr>
                        <td class="text-end">TOTAL</td>
                        <td class="text-center">{{ $summary['total_entries'] }}</td>
                        <td class="text-end amt">{{ $fmt($summary['total_amount']) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @else
        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width:10%;">Date</th>
                    <th style="width:10%;">Code</th>
                    <th style="width:14%;">Category</th>
                    <th style="width:30%;">Description</th>
                    <th style="width:14%;">Payment</th>
                    <th style="width:22%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="text-center">{{ \Carbon\Carbon::parse($row->report_date)->format('d-M-Y') }}</td>
                        <td class="text-center">{{ $row->expense_code }}</td>
                        <td class="cell-desc" title="{{ $row->category }}">{{ $row->category }}</td>
                        <td class="cell-desc" title="{{ $row->description }}">{{ $row->description }}</td>
                        <td class="text-center">{{ $row->payment_method }}</td>
                        <td class="text-end amt">{{ $fmt($row->amount) }}</td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="6">No expense entries in this period.</td></tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end">TOTAL AMOUNT</td>
                        <td class="text-end amt">{{ $fmt($summary['total_amount']) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @endif

    <div class="report-footer">
        This is a computer-generated report. &copy; {{ date('Y') }} Trade Gate
    </div>
</div>
