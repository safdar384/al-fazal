@php
    $fmt = $fmt ?? static fn ($n) => number_format((float) $n, 2);
    $customerLabel = 'All';
    if (! empty($filters['customer_code'])) {
        $match = collect($filterOptions['customers'] ?? [])->firstWhere('custCode', $filters['customer_code']);
        $customerLabel = $match
            ? (trim((string) ($match->bussnessName ?: $match->custName)) ?: $filters['customer_code'])
            : $filters['customer_code'];
    }
    $saleTypeLabel = match ($filters['sale_type'] ?? '') {
        'cash' => 'Cash',
        'credit' => 'Credit',
        default => 'All',
    };
@endphp

<div id="saleProfitLive">
    <div class="meta-bar">
        <div class="meta-box">
            <h6>Filters</h6>
            <div class="meta-grid">
                <span class="lbl">Period</span>
                <span class="val">{{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} – {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</span>
                <span class="lbl">Sale Type</span>
                <span class="val">{{ $saleTypeLabel }}</span>
                @if ($config['show']['invoice'] ?? false)
                    <span class="lbl">Invoice</span>
                    <span class="val">{{ ($filters['invoice_no'] ?? '') !== '' ? $filters['invoice_no'] : 'All' }}</span>
                @endif
                @if ($config['show']['area'] ?? false)
                    <span class="lbl">Area</span>
                    <span class="val">{{ ($filters['area'] ?? '') !== '' ? $filters['area'] : 'All' }}</span>
                @endif
                @if ($config['show']['customer'] ?? false)
                    <span class="lbl">Customer</span>
                    <span class="val" title="{{ $customerLabel }}">{{ $customerLabel }}</span>
                @endif
                @if ($config['show']['ajent'] ?? false)
                    <span class="lbl">Ajent</span>
                    <span class="val">{{ ($filters['ajent'] ?? '') !== '' ? $filters['ajent'] : 'All' }}</span>
                @endif
            </div>
        </div>
        <div class="meta-box">
            <h6>Report</h6>
            <div class="meta-grid">
                <span class="lbl">Generated</span>
                <span class="val">{{ now()->format('d-M-Y h:i A') }}</span>
                <span class="lbl">Invoices</span>
                <span class="val">{{ $summary['total_invoices'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    <div class="summary-stats">
        <div class="stat-box">
            <div class="stat-label">Invoices</div>
            <div class="stat-value">{{ $summary['total_invoices'] ?? 0 }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Sale Amount</div>
            <div class="stat-value">{{ $fmt($summary['total_sale'] ?? 0) }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Cost Amount</div>
            <div class="stat-value">{{ $fmt($summary['total_cost'] ?? 0) }}</div>
        </div>
        <div class="stat-box {{ ($summary['total_profit'] ?? 0) >= 0 ? 'value' : 'warn' }}">
            <div class="stat-label">Total Profit</div>
            <div class="stat-value">{{ $fmt($summary['total_profit'] ?? 0) }}</div>
        </div>
    </div>

    <table class="ledger-table">
        <thead>
            <tr>
                <th style="width:9%;">Date</th>
                <th style="width:10%;">Invoice</th>
                <th style="width:16%;">Customer</th>
                <th style="width:10%;">Area</th>
                <th style="width:10%;">Ajent</th>
                <th style="width:7%;">Type</th>
                <th style="width:8%;">Qty</th>
                <th style="width:10%;">Sale</th>
                <th style="width:10%;">Cost</th>
                <th style="width:10%;">Profit</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row->invoice_date)->format('d-m-Y') }}</td>
                    <td class="text-center">{{ $row->invoice_no }}</td>
                    <td class="cell-desc" title="{{ $row->customer_name }}">{{ $row->customer_name }}</td>
                    <td class="text-center">{{ $row->area }}</td>
                    <td class="text-center">{{ $row->ajent_name }}</td>
                    <td class="text-center">{{ ucfirst($row->sale_type) }}</td>
                    <td class="text-end amt">{{ $fmt($row->qty_sold) }}</td>
                    <td class="text-end amt">{{ $fmt($row->sale_amount) }}</td>
                    <td class="text-end">{{ $fmt($row->cost_amount) }}</td>
                    <td class="text-end {{ $row->profit_amount < 0 ? 'amt-neg' : 'amt-pos' }}">{{ $fmt($row->profit_amount) }}</td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="10">No sale invoices found for selected filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="6" class="text-end">TOTAL</td>
                    <td class="text-end">{{ $fmt($summary['total_qty']) }}</td>
                    <td class="text-end">{{ $fmt($summary['total_sale']) }}</td>
                    <td class="text-end">{{ $fmt($summary['total_cost']) }}</td>
                    <td class="text-end {{ ($summary['total_profit'] ?? 0) < 0 ? 'amt-neg' : 'amt-pos' }}">{{ $fmt($summary['total_profit']) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="report-footer">
        Profit = Item Sale − Cost + Bilty/Loader &nbsp;·&nbsp; &copy; {{ date('Y') }} Trade Gate
    </div>
</div>
