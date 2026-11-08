@php
    $fmt = $fmt ?? static fn ($v) => number_format((float) ($v ?? 0), 2);
    $allTotal = (float) ($preview['customer_receivable'] ?? 0)
        + (float) ($preview['bank_balance'] ?? 0)
        + (float) ($preview['labour_balance'] ?? 0)
        + (float) ($preview['stock_balance'] ?? 0)
        - (float) ($preview['customer_payable'] ?? 0)
        - (float) ($preview['expense_balance'] ?? 0);
@endphp

<div id="plClosingsLive">
    <div class="meta-bar">
        <div class="meta-box">
            <h6>Closing Period</h6>
            <div class="meta-grid">
                <span class="lbl">From</span>
                <span class="val">{{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}</span>
                <span class="lbl">To</span>
                <span class="val">{{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</span>
            </div>
        </div>
        <div class="meta-box">
            <h6>Preview</h6>
            <div class="meta-grid">
                <span class="lbl">Net Profit</span>
                <span class="val {{ ($preview['net_profit'] ?? 0) >= 0 ? 'amt-pos' : 'amt-neg' }}">{{ $fmt($preview['net_profit'] ?? 0) }}</span>
                <span class="lbl">All Total</span>
                <span class="val">{{ $fmt($allTotal) }}</span>
            </div>
        </div>
    </div>

    <div class="summary-stats">
        <div class="stat-box">
            <div class="stat-label">Sales</div>
            <div class="stat-value">{{ $fmt($preview['sale_amount'] ?? 0) }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Cost</div>
            <div class="stat-value">{{ $fmt($preview['cost_amount'] ?? 0) }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Gross Profit</div>
            <div class="stat-value">{{ $fmt($preview['gross_profit'] ?? 0) }}</div>
        </div>
        <div class="stat-box {{ ($preview['net_profit'] ?? 0) >= 0 ? 'value' : 'warn' }}">
            <div class="stat-label">Net Profit</div>
            <div class="stat-value">{{ $fmt($preview['net_profit'] ?? 0) }}</div>
        </div>
    </div>

    <table class="ledger-table" style="margin-bottom:12px;">
        <thead>
            <tr>
                <th style="width:50%;">ERP Balance (as of {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }})</th>
                <th style="width:50%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="cell-desc">Customer receivable</td>
                <td class="text-end amt-pos">{{ $fmt($preview['customer_receivable'] ?? 0) }}</td>
            </tr>
            <tr>
                <td class="cell-desc">Customer payable</td>
                <td class="text-end amt-neg">{{ $fmt($preview['customer_payable'] ?? 0) }}</td>
            </tr>
            <tr>
                <td class="cell-desc">Bank balance</td>
                <td class="text-end amt">{{ $fmt($preview['bank_balance'] ?? 0) }}</td>
            </tr>
            <tr>
                <td class="cell-desc">Labour balance</td>
                <td class="text-end amt">{{ $fmt($preview['labour_balance'] ?? 0) }}</td>
            </tr>
            <tr>
                <td class="cell-desc">Expense balance</td>
                <td class="text-end amt">{{ $fmt($preview['expense_balance'] ?? 0) }}</td>
            </tr>
            <tr>
                <td class="cell-desc">Stock balance (at cost)</td>
                <td class="text-end amt">{{ $fmt($preview['stock_balance'] ?? 0) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td class="text-end">ALL TOTAL BALANCE</td>
                <td class="text-end {{ $allTotal >= 0 ? 'amt-pos' : 'amt-neg' }}">{{ $fmt($allTotal) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="report-title" style="margin-top:4px;">
        <h3 style="font-size:0.9rem;">Recent Closings</h3>
    </div>

    <table class="ledger-table">
        <thead>
            <tr>
                <th style="width:22%;">Period</th>
                <th style="width:12%;">Recv.</th>
                <th style="width:12%;">Pay.</th>
                <th style="width:12%;">Bank</th>
                <th style="width:12%;">Stock</th>
                <th style="width:14%;">Net Profit</th>
                <th style="width:16%;">Closed</th>
            </tr>
        </thead>
        <tbody>
            @forelse($closings as $c)
                <tr>
                    <td class="cell-desc">{{ $c->from_date->format('d-m-Y') }} → {{ $c->to_date->format('d-m-Y') }}</td>
                    <td class="text-end amt-pos">{{ $fmt($c->customer_receivable ?? 0) }}</td>
                    <td class="text-end amt-neg">{{ $fmt($c->customer_payable ?? 0) }}</td>
                    <td class="text-end">{{ $fmt($c->bank_balance ?? 0) }}</td>
                    <td class="text-end">{{ $fmt($c->stock_balance ?? 0) }}</td>
                    <td class="text-end {{ ($c->net_profit ?? 0) >= 0 ? 'amt-pos' : 'amt-neg' }}">{{ $fmt($c->net_profit) }}</td>
                    <td class="text-center">{{ $c->closed_at?->format('d-m-Y H:i') }}</td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="7">No ERP closings saved yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="report-footer">
        Recv. + Bank + Labour + Stock − Payable − Expense = All Total Balance
        &nbsp;·&nbsp; &copy; {{ date('Y') }} Trade Gate
    </div>
</div>
