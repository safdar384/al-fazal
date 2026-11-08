@php
    $fmt = $fmt ?? static fn ($v) => number_format((float) ($v ?? 0), 2);
    $rowAllTotal = static function ($c) {
        return (float) ($c->customer_receivable ?? 0)
            + (float) ($c->bank_balance ?? 0)
            + (float) ($c->labour_balance ?? 0)
            + (float) ($c->stock_balance ?? 0)
            - (float) ($c->customer_payable ?? 0)
            - (float) ($c->expense_balance ?? 0);
    };
@endphp

<div id="plHistoryLive">
    <div class="meta-bar">
        <div class="meta-box">
            <h6>Filter</h6>
            <div class="meta-grid">
                <span class="lbl">From</span>
                <span class="val">{{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}</span>
                <span class="lbl">To</span>
                <span class="val">{{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</span>
            </div>
        </div>
        <div class="meta-box">
            <h6>Summary</h6>
            <div class="meta-grid">
                <span class="lbl">Closings</span>
                <span class="val">{{ $closings->count() }}</span>
                <span class="lbl">Latest</span>
                <span class="val">{{ ($totals['latest_closed_at'] ?? null) ? $totals['latest_closed_at']->format('d M Y H:i') : '—' }}</span>
            </div>
        </div>
    </div>

    @if($closings->isNotEmpty())
        <div class="summary-stats">
            <div class="stat-box">
                <div class="stat-label">Receivable</div>
                <div class="stat-value" style="color:#198754;">{{ $fmt($totals['customer_receivable'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Payable</div>
                <div class="stat-value" style="color:#dc3545;">{{ $fmt($totals['customer_payable'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Bank</div>
                <div class="stat-value">{{ $fmt($totals['bank_balance'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Stock</div>
                <div class="stat-value">{{ $fmt($totals['stock_balance'] ?? 0) }}</div>
            </div>
            <div class="stat-box {{ ($totals['all_total_balance'] ?? 0) >= 0 ? 'value' : 'warn' }}">
                <div class="stat-label">All Total</div>
                <div class="stat-value">{{ $fmt($totals['all_total_balance'] ?? 0) }}</div>
            </div>
            <div class="stat-box {{ ($totals['net_profit'] ?? 0) >= 0 ? 'value' : 'warn' }}">
                <div class="stat-label">Sum Net Profit</div>
                <div class="stat-value">{{ $fmt($totals['net_profit'] ?? 0) }}</div>
            </div>
        </div>

        @if($reconciliation)
            <div class="pl-statement" style="margin-bottom:12px;">
                <div class="pl-row sub">
                    <span>Balance reconciliation</span>
                    <strong></strong>
                </div>
                <div class="pl-row">
                    <span>Pre Closing Balance</span>
                    <strong class="amt">{{ $fmt($reconciliation['pre_closing_balance'] ?? 0) }}</strong>
                </div>
                <div class="pl-row">
                    <span>+ Now Profit (this closing period)</span>
                    <strong class="amt-pos">{{ $fmt($reconciliation['now_profit'] ?? 0) }}</strong>
                </div>
                <div class="pl-row sub">
                    <span>= Total Balance (expected)</span>
                    <strong class="amt">{{ $fmt($reconciliation['expected_total_balance'] ?? 0) }}</strong>
                </div>
                <div class="pl-row">
                    <span>All Total Balance (current closing)</span>
                    <strong class="amt">{{ $fmt($reconciliation['current_all_total_balance'] ?? 0) }}</strong>
                </div>
                <div class="pl-row net">
                    <span>
                        Difference
                        <span class="hint">{{ ($reconciliation['is_balanced'] ?? false) ? 'Expected total matches current closing.' : 'Variance — check purchases, payments, stock, or period dates.' }}</span>
                    </span>
                    <strong>{{ $fmt($reconciliation['balance_difference'] ?? 0) }}</strong>
                </div>
            </div>
        @endif
    @endif

    <table class="ledger-table" style="font-size:0.62rem;">
        <thead>
            <tr>
                <th>Closed</th>
                <th>Period</th>
                <th>Sales</th>
                <th>Cost</th>
                <th>Gross</th>
                <th>Net</th>
                <th>Recv.</th>
                <th>Pay.</th>
                <th>Bank</th>
                <th>Labour</th>
                <th>Exp.</th>
                <th>Stock</th>
                <th>All Total</th>
                <th class="no-print">View</th>
            </tr>
        </thead>
        <tbody>
            @forelse($closings as $c)
                @php $allTotal = $rowAllTotal($c); @endphp
                <tr>
                    <td class="text-center">{{ $c->closed_at?->format('d-m-Y H:i') }}</td>
                    <td class="cell-desc">{{ $c->from_date->format('d-m-Y') }} – {{ $c->to_date->format('d-m-Y') }}</td>
                    <td class="text-end">{{ $fmt($c->sale_amount) }}</td>
                    <td class="text-end">{{ $fmt($c->cost_amount) }}</td>
                    <td class="text-end">{{ $fmt($c->gross_profit ?? 0) }}</td>
                    <td class="text-end {{ ($c->net_profit ?? 0) >= 0 ? 'amt-pos' : 'amt-neg' }}">{{ $fmt($c->net_profit) }}</td>
                    <td class="text-end amt-pos">{{ $fmt($c->customer_receivable ?? 0) }}</td>
                    <td class="text-end amt-neg">{{ $fmt($c->customer_payable ?? 0) }}</td>
                    <td class="text-end">{{ $fmt($c->bank_balance ?? 0) }}</td>
                    <td class="text-end">{{ $fmt($c->labour_balance ?? 0) }}</td>
                    <td class="text-end">{{ $fmt($c->expense_balance ?? 0) }}</td>
                    <td class="text-end">{{ $fmt($c->stock_balance ?? 0) }}</td>
                    <td class="text-end {{ $allTotal >= 0 ? 'amt-pos' : 'amt-neg' }}">{{ $fmt($allTotal) }}</td>
                    <td class="text-center no-print">
                        <a href="{{ route('reports.profit-loss.closing-history.show', $c->id) }}" target="_blank" rel="noopener" style="color:#0f2747;font-weight:700;">View</a>
                    </td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="14">No closings found for this date range.</td>
                </tr>
            @endforelse
        </tbody>
        @if($closings->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="2" class="text-end">TOTALS</td>
                    <td class="text-end">{{ $fmt($totals['sale_amount']) }}</td>
                    <td class="text-end">{{ $fmt($totals['cost_amount']) }}</td>
                    <td class="text-end">{{ $fmt($totals['gross_profit']) }}</td>
                    <td class="text-end {{ ($totals['net_profit'] ?? 0) >= 0 ? 'amt-pos' : 'amt-neg' }}">{{ $fmt($totals['net_profit']) }}</td>
                    <td class="text-end amt-pos">{{ $fmt($totals['customer_receivable']) }}</td>
                    <td class="text-end amt-neg">{{ $fmt($totals['customer_payable']) }}</td>
                    <td class="text-end">{{ $fmt($totals['bank_balance']) }}</td>
                    <td class="text-end">{{ $fmt($totals['labour_balance']) }}</td>
                    <td class="text-end">{{ $fmt($totals['expense_balance']) }}</td>
                    <td class="text-end">{{ $fmt($totals['stock_balance']) }}</td>
                    <td class="text-end {{ ($totals['all_total_balance'] ?? 0) >= 0 ? 'amt-pos' : 'amt-neg' }}">{{ $fmt($totals['all_total_balance']) }}</td>
                    <td class="no-print"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="report-footer">
        This is a computer-generated report. &copy; {{ date('Y') }} Trade Gate
    </div>
</div>
