@php
    $fmt = $fmt ?? static fn ($n) => number_format((float) $n, 2);
    $fmtRs = $fmtRs ?? static fn ($n) => 'Rs. ' . number_format((float) $n, 2);
    $customerLabel = 'All';
    if (! empty($filters['customer_code'])) {
        $match = collect($filterOptions['customers'] ?? [])->firstWhere('custCode', $filters['customer_code']);
        $customerLabel = $match->custName ?? $filters['customer_code'];
    }
    $saleTypeLabel = match ($filters['sale_type'] ?? '') {
        'cash' => 'Cash Sale',
        'credit' => 'Credit Sale',
        default => 'All',
    };
@endphp

<div id="saleReportLive">
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
                <span class="lbl">Sale Type</span>
                <span class="val">{{ $saleTypeLabel }}</span>
                @if ($config['show']['area'])
                    <span class="lbl">Area</span>
                    <span class="val">{{ $filters['area'] !== '' ? $filters['area'] : 'All' }}</span>
                @endif
                @if ($config['show']['customer'])
                    <span class="lbl">Customer</span>
                    <span class="val" title="{{ $customerLabel }}">{{ $customerLabel }}</span>
                @endif
                @if ($config['show']['company'])
                    <span class="lbl">Company</span>
                    <span class="val">{{ $filters['company'] !== '' ? $filters['company'] : 'All' }}</span>
                @endif
                @if ($config['show']['product'])
                    <span class="lbl">Product</span>
                    <span class="val">
                        @php
                            $productLabel = 'All';
                            if (! empty($filters['product_code'])) {
                                $pm = collect($filterOptions['products'] ?? [])->firstWhere('productCode', $filters['product_code']);
                                $productLabel = $pm->productName ?? $filters['product_code'];
                            }
                        @endphp
                        {{ $productLabel }}
                    </span>
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
            <div class="stat-label">Rows</div>
            <div class="stat-value">{{ $summary['total_rows'] ?? 0 }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Total Qty</div>
            <div class="stat-value">{{ $fmt($summary['total_quantity'] ?? 0) }}</div>
        </div>
        <div class="stat-box value">
            <div class="stat-label">Total Amount</div>
            <div class="stat-value">{{ $fmtRs($summary['total_amount'] ?? 0) }}</div>
        </div>
    </div>

    <table class="ledger-table">
        <thead>
            <tr>
                <th style="width:10%;">Date</th>
                <th style="width:12%;">Invoice No</th>
                <th style="width:8%;">Type</th>
                <th style="width:24%;">Customer</th>
                <th style="width:24%;">Product</th>
                <th style="width:10%;">Quantity</th>
                <th style="width:12%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row->report_date)->format('d-M-Y') }}</td>
                    <td class="text-center">{{ $row->invoice_no }}</td>
                    <td class="text-center">
                        @if(($row->sale_type ?? 'credit') === 'cash')
                            <span class="status-pill st-cash">Cash</span>
                        @else
                            <span class="status-pill st-credit">Credit</span>
                        @endif
                    </td>
                    <td class="cell-desc" title="{{ $row->customer_name }}">{{ $row->customer_name }}</td>
                    <td class="cell-desc" title="{{ $row->product_name }}">{{ $row->product_name }}</td>
                    <td class="text-end">{{ $fmt($row->quantity) }}</td>
                    <td class="text-end amt">{{ $fmt($row->amount) }}</td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="7">No report data found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end">TOTAL</td>
                    <td class="text-end">{{ $fmt($summary['total_quantity']) }}</td>
                    <td class="text-end">{{ $fmt($summary['total_amount']) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="report-footer">
        This is a computer-generated report. &copy; {{ date('Y') }} Trade Gate
    </div>
</div>
