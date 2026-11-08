@php
    $fmt = $fmt ?? static fn ($n) => number_format((float) $n, 2);
@endphp

<div id="plStatementLive">
    <div class="meta-bar">
        <div class="meta-box">
            <h6>Period</h6>
            <div class="meta-grid">
                <span class="lbl">From</span>
                <span class="val">{{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }}</span>
                <span class="lbl">To</span>
                <span class="val">{{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</span>
            </div>
        </div>
        <div class="meta-box">
            <h6>Report</h6>
            <div class="meta-grid">
                <span class="lbl">Generated</span>
                <span class="val">{{ now()->format('d-M-Y h:i A') }}</span>
                <span class="lbl">Purchases (ref.)</span>
                <span class="val">{{ $fmt($figures['purchase_amount'] ?? 0) }}</span>
            </div>
        </div>
    </div>

    <div class="summary-stats">
        <div class="stat-box">
            <div class="stat-label">Sales</div>
            <div class="stat-value">{{ $fmt($figures['sale_amount'] ?? 0) }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Gross Profit</div>
            <div class="stat-value {{ ($figures['gross_profit'] ?? 0) >= 0 ? 'amt-pos' : 'amt-neg' }}" style="color:inherit;">
                {{ $fmt($figures['gross_profit'] ?? 0) }}
            </div>
        </div>
        <div class="stat-box {{ ($figures['net_profit'] ?? 0) >= 0 ? 'value' : 'warn' }}">
            <div class="stat-label">Net Profit</div>
            <div class="stat-value">{{ $fmt($figures['net_profit'] ?? 0) }}</div>
        </div>
    </div>

    <div class="pl-statement">
        <div class="pl-row">
            <span>Sales (invoice total)</span>
            <strong class="amt">{{ $fmt($figures['sale_amount'] ?? 0) }}</strong>
        </div>
        <div class="pl-row">
            <span>Cost of goods sold</span>
            <strong class="amt-neg">({{ $fmt($figures['cost_amount'] ?? 0) }})</strong>
        </div>
        <div class="pl-row sub">
            <span>Gross profit</span>
            <strong class="{{ ($figures['gross_profit'] ?? 0) >= 0 ? 'amt-pos' : 'amt-neg' }}">{{ $fmt($figures['gross_profit'] ?? 0) }}</strong>
        </div>
        <div class="pl-row">
            <span>Sale bilty / loader</span>
            <strong class="amt-pos">{{ $fmt($figures['sale_bilty_amount'] ?? 0) }}</strong>
        </div>
        <div class="pl-row">
            <span>Expenses (ledger)</span>
            <strong class="amt-neg">({{ $fmt($figures['expense_amount'] ?? 0) }})</strong>
        </div>
        <div class="pl-row">
            <span>
                Voucher Discount
                <span class="hint">Discount {{ $fmt($figures['ledger_discount_amount'] ?? 0) }} · Bilty {{ $fmt($figures['ledger_bilty_amount'] ?? 0) }}</span>
            </span>
            <strong class="amt-neg">({{ $fmt($figures['voucher_discount_amount'] ?? 0) }})</strong>
        </div>
        <div class="pl-row net">
            <span>
                Net profit
                <span class="hint">Purchases in period (reference): {{ $fmt($figures['purchase_amount'] ?? 0) }}</span>
            </span>
            <strong>{{ $fmt($figures['net_profit'] ?? 0) }}</strong>
        </div>
    </div>

    <div class="report-footer no-print">
        <a href="{{ route('reports.profit-loss.closings', ['from_date' => $fromDate, 'to_date' => $toDate]) }}" target="_blank" rel="noopener" style="color:#0f2747;font-weight:600;text-decoration:none;">
            Save as period closing →
        </a>
        &nbsp;·&nbsp; This is a computer-generated report. &copy; {{ date('Y') }} Trade Gate
    </div>
</div>
