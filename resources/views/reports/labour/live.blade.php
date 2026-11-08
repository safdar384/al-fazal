@php
    $fmt = $fmt ?? static fn ($n) => number_format((float) $n, 2);
    $fmtRs = $fmtRs ?? static fn ($n) => 'Rs. ' . number_format((float) $n, 2);
    $isAdvance = ($config['mode'] ?? '') === 'advance';
    $isBalances = ($config['mode'] ?? '') === 'balances';
    $labourLabel = 'All';
    if (! empty($filters['labour_code'])) {
        $match = collect($filterOptions['labours'] ?? [])->firstWhere('labourCode', $filters['labour_code']);
        $labourLabel = $match->labourName ?? $filters['labour_code'];
    }
    $categoryLabel = 'All';
    if (! empty($filters['category'])) {
        $categoryLabel = $filterOptions['categories'][$filters['category']] ?? ucfirst($filters['category']);
    }
@endphp

<div id="labourReportLive">
    <div class="meta-bar">
        <div class="meta-box">
            <h6>Filters</h6>
            <div class="meta-grid">
                @if ($config['show']['dates'] ?? true)
                    <span class="lbl">Period</span>
                    <span class="val">
                        {{ \Carbon\Carbon::parse($fromDate)->format('d-M-Y') }}
                        to
                        {{ \Carbon\Carbon::parse($toDate)->format('d-M-Y') }}
                    </span>
                @endif
                @if ($config['show']['category'] ?? false)
                    <span class="lbl">Category</span>
                    <span class="val">{{ $categoryLabel }}</span>
                @endif
                @if ($config['show']['labour'] ?? false)
                    <span class="lbl">Labour</span>
                    <span class="val" title="{{ $labourLabel }}">{{ $labourLabel }}</span>
                @endif
                @if (! ($config['show']['category'] ?? false) && in_array($reportType, ['worker', 'ajent', 'fitter'], true))
                    <span class="lbl">Category</span>
                    <span class="val">{{ $filterOptions['categories'][$reportType] ?? ucfirst($reportType) }}</span>
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
        @if ($isAdvance)
            <div class="stat-box">
                <div class="stat-label">Labours</div>
                <div class="stat-value">{{ $summary['total_rows'] ?? 0 }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">With Advance</div>
                <div class="stat-value">{{ $summary['with_advance'] ?? 0 }}</div>
            </div>
            <div class="stat-box value">
                <div class="stat-label">Total Advance</div>
                <div class="stat-value">{{ $fmtRs($summary['total_advance'] ?? 0) }}</div>
            </div>
        @elseif ($isBalances)
            <div class="stat-box">
                <div class="stat-label">Total Labours</div>
                <div class="stat-value">{{ $summary['total_rows'] ?? 0 }}</div>
            </div>
            <div class="stat-box value">
                <div class="stat-label">Total Balance</div>
                <div class="stat-value">{{ $fmtRs($summary['total_balance'] ?? 0) }}</div>
            </div>
        @else
            <div class="stat-box">
                <div class="stat-label">Transactions</div>
                <div class="stat-value">{{ $summary['total_rows'] ?? 0 }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Labours</div>
                <div class="stat-value">{{ $summary['labour_count'] ?? 0 }}</div>
            </div>
            <div class="stat-box value">
                <div class="stat-label">Total Balance</div>
                <div class="stat-value">{{ $fmtRs($summary['total_balance'] ?? 0) }}</div>
            </div>
        @endif
    </div>

    @if ($isAdvance)
        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width:12%;">Code</th>
                    <th style="width:34%;">Labour Name</th>
                    <th style="width:14%;">Category</th>
                    <th style="width:20%;">Advance</th>
                    <th style="width:20%;">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="text-center">{{ $row->labour_code }}</td>
                        <td class="cell-desc" title="{{ $row->labour_name }}">{{ $row->labour_name }}</td>
                        <td class="text-center">
                            <span class="status-pill st-{{ $row->category }}">{{ $row->category_label }}</span>
                        </td>
                        <td class="text-end {{ (float) $row->advance > 0 ? 'amt-adv' : '' }}">
                            {{ $fmt($row->advance) }}
                        </td>
                        <td class="text-end amt">{{ $fmt($row->balance) }}</td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="5">No labour records found.</td></tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end">TOTAL</td>
                        <td class="text-end amt-adv">{{ $fmt($summary['total_advance']) }}</td>
                        <td class="text-end">{{ $fmt($summary['total_balance']) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @elseif ($isBalances)
        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width:14%;">Code</th>
                    <th style="width:40%;">Labour Name</th>
                    <th style="width:18%;">Category</th>
                    <th style="width:28%;">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="text-center">{{ $row->labour_code }}</td>
                        <td class="cell-desc" title="{{ $row->labour_name }}">{{ $row->labour_name }}</td>
                        <td class="text-center">
                            <span class="status-pill st-{{ $row->category }}">{{ $row->category_label }}</span>
                        </td>
                        <td class="text-end amt">{{ $fmt($row->balance) }}</td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="4">No labour records found.</td></tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end">TOTAL BALANCE</td>
                        <td class="text-end amt">{{ $fmt($summary['total_balance']) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @else
        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width:14%;">Date</th>
                    <th style="width:40%;">Labour</th>
                    <th style="width:18%;">Category</th>
                    <th style="width:28%;">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="text-center">{{ \Carbon\Carbon::parse($row->report_date)->format('d-M-Y') }}</td>
                        <td class="cell-desc" title="{{ $row->labour_name }} ({{ $row->labour_code }})">
                            {{ $row->labour_name }}
                        </td>
                        <td class="text-center">
                            <span class="status-pill st-{{ $row->category }}">{{ $row->category_label }}</span>
                        </td>
                        <td class="text-end amt">{{ $fmt($row->balance) }}</td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="4">No labour transactions in this period.</td></tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end">TOTAL BALANCE</td>
                        <td class="text-end amt">{{ $fmt($summary['total_balance']) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @endif

    <div class="report-footer">
        This is a computer-generated report. &copy; {{ date('Y') }} Trade Gate
    </div>
</div>
