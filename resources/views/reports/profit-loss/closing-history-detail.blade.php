<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Closing Detail #{{ $closing->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @include('reports.profit-loss.partials.navy-styles')
    <style>
        .stat-box.warn { background: #fdf0f1; border-color: #f1b0b7; }
        .btn-pdf { display: none; }
    </style>
</head>
<body>
@php
    $fmt = static fn ($v) => number_format((float) ($v ?? 0), 2);
    $allTotal = (float) ($closing->customer_receivable ?? 0)
        + (float) ($closing->bank_balance ?? 0)
        + (float) ($closing->labour_balance ?? 0)
        + (float) ($closing->stock_balance ?? 0)
        - (float) ($closing->customer_payable ?? 0)
        - (float) ($closing->expense_balance ?? 0);
@endphp
    <div class="ledger-container">
        @include('reports.profit-loss.partials.navy-header', ['title' => 'Closing Detail #'.$closing->id])

        <div class="meta-bar">
            <div class="meta-box">
                <h6>Closing Info</h6>
                <div class="meta-grid">
                    <span class="lbl">Period</span>
                    <span class="val">{{ $closing->from_date->format('d M Y') }} – {{ $closing->to_date->format('d M Y') }}</span>
                    <span class="lbl">Closed at</span>
                    <span class="val">{{ $closing->closed_at?->format('d M Y H:i') }}</span>
                    @if($closing->notes)
                        <span class="lbl">Notes</span>
                        <span class="val" title="{{ $closing->notes }}">{{ $closing->notes }}</span>
                    @endif
                </div>
            </div>
            <div class="meta-box">
                <h6>Result</h6>
                <div class="meta-grid">
                    <span class="lbl">Net Profit</span>
                    <span class="val" style="color:{{ $closing->net_profit >= 0 ? '#198754' : '#dc3545' }};">{{ $fmt($closing->net_profit) }}</span>
                    <span class="lbl">All Total</span>
                    <span class="val">{{ $fmt($allTotal) }}</span>
                </div>
            </div>
        </div>

        <div class="summary-stats">
            <div class="stat-box {{ $closing->net_profit >= 0 ? 'value' : 'warn' }}">
                <div class="stat-label">Net Profit</div>
                <div class="stat-value">{{ $fmt($closing->net_profit) }}</div>
            </div>
            <div class="stat-box {{ $allTotal >= 0 ? 'value' : 'warn' }}">
                <div class="stat-label">All Total Balance</div>
                <div class="stat-value">{{ $fmt($allTotal) }}</div>
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
                    <span>+ Now Profit</span>
                    <strong class="amt-pos">{{ $fmt($reconciliation['now_profit'] ?? 0) }}</strong>
                </div>
                <div class="pl-row sub">
                    <span>= Expected Total</span>
                    <strong class="amt">{{ $fmt($reconciliation['expected_total_balance'] ?? 0) }}</strong>
                </div>
                <div class="pl-row">
                    <span>Current All Total</span>
                    <strong class="amt">{{ $fmt($reconciliation['current_all_total_balance'] ?? 0) }}</strong>
                </div>
                <div class="pl-row net">
                    <span>
                        Difference
                        <span class="hint">{{ ($reconciliation['is_balanced'] ?? false) ? 'Balanced' : 'Variance detected' }}</span>
                    </span>
                    <strong>{{ $fmt($reconciliation['balance_difference'] ?? 0) }}</strong>
                </div>
            </div>
        @endif

        <div class="pl-statement" style="margin-bottom:12px;">
            <div class="pl-row sub"><span>Profit &amp; Loss (period)</span><strong></strong></div>
            <div class="pl-row"><span>Sales</span><strong class="amt">{{ $fmt($closing->sale_amount) }}</strong></div>
            <div class="pl-row"><span>Cost of goods</span><strong class="amt-neg">({{ $fmt($closing->cost_amount) }})</strong></div>
            <div class="pl-row"><span>Gross profit</span><strong class="amt">{{ $fmt($closing->gross_profit ?? 0) }}</strong></div>
            <div class="pl-row"><span>Sale bilty / loader</span><strong class="amt-pos">+ {{ $fmt($closing->sale_bilty_amount) }}</strong></div>
            <div class="pl-row"><span>Period expenses</span><strong class="amt-neg">({{ $fmt($closing->expense_amount) }})</strong></div>
            <div class="pl-row"><span>Voucher discount</span><strong class="amt-neg">({{ $fmt($closing->voucher_discount_amount ?? ($closing->ledger_discount_amount + $closing->ledger_bilty_amount)) }})</strong></div>
            <div class="pl-row"><span>Purchases (ref.)</span><strong class="amt">{{ $fmt($closing->purchase_amount ?? 0) }}</strong></div>
            <div class="pl-row net"><span>Net profit</span><strong>{{ $fmt($closing->net_profit) }}</strong></div>
        </div>

        <div class="pl-statement">
            <div class="pl-row sub"><span>ERP balances (at closing)</span><strong></strong></div>
            <div class="pl-row"><span>Customer receivable</span><strong class="amt-pos">{{ $fmt($closing->customer_receivable ?? 0) }}</strong></div>
            <div class="pl-row"><span>Customer payable</span><strong class="amt-neg">{{ $fmt($closing->customer_payable ?? 0) }}</strong></div>
            <div class="pl-row"><span>Bank balance</span><strong class="amt">{{ $fmt($closing->bank_balance ?? 0) }}</strong></div>
            <div class="pl-row"><span>Labour balance</span><strong class="amt">{{ $fmt($closing->labour_balance ?? 0) }}</strong></div>
            <div class="pl-row"><span>Expense balance</span><strong class="amt">{{ $fmt($closing->expense_balance ?? 0) }}</strong></div>
            <div class="pl-row"><span>Stock (at cost)</span><strong class="amt">{{ $fmt($closing->stock_balance ?? 0) }}</strong></div>
            <div class="pl-row net"><span>All Total Balance</span><strong>{{ $fmt($allTotal) }}</strong></div>
        </div>

        <div class="report-footer">
            This is a computer-generated report. &copy; {{ date('Y') }} Trade Gate
        </div>
    </div>

    <button type="button" class="btn btn-secondary btn-back no-print" onclick="window.close()">
        <i class="bi bi-x-lg"></i> Close
    </button>
    <a href="{{ route('reports.profit-loss.closing-history', ['from_date' => $closing->from_date->format('Y-m-d'), 'to_date' => $closing->to_date->format('Y-m-d')]) }}" class="btn btn-outline-secondary btn-pdf no-print" style="display:inline-block;">
        History
    </a>
    <button type="button" class="btn btn-primary btn-print no-print" onclick="window.print()">
        <i class="bi bi-printer"></i> Print
    </button>
</body>
</html>
