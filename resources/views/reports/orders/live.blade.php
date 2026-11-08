@php
    $fmt = $fmt ?? static fn ($n) => number_format((float) $n, 2);
    $fmtRs = $fmtRs ?? static fn ($n) => 'Rs. ' . number_format((float) $n, 2);
    $customerLabel = 'All';
    if (! empty($filters['customer_code'])) {
        $match = collect($filterOptions['customers'] ?? [])->firstWhere('custCode', $filters['customer_code']);
        $customerLabel = $match->custName ?? $filters['customer_code'];
    }
    $statusClass = $statusClass ?? static function ($status) {
        $s = strtolower(trim((string) $status));
        if ($s === 'pending') return 'st-pending';
        if ($s === 'confirmed') return 'st-confirmed';
        if ($s === 'in process') return 'st-process';
        if ($s === 'completed') return 'st-completed';
        if ($s === 'cancelled') return 'st-cancelled';
        return 'st-other';
    };
@endphp

<div id="orderReportLive">
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
                <span class="lbl">Area</span>
                <span class="val">{{ $filters['area'] !== '' ? $filters['area'] : 'All' }}</span>
                <span class="lbl">Customer</span>
                <span class="val" title="{{ $customerLabel }}">{{ $customerLabel }}</span>
                <span class="lbl">Status</span>
                <span class="val">{{ $filters['status'] !== '' ? $filters['status'] : 'All' }}</span>
            </div>
        </div>
        <div class="meta-box">
            <h6>Report</h6>
            <div class="meta-grid">
                <span class="lbl">Generated</span>
                <span class="val">{{ now()->format('d-M-Y h:i A') }}</span>
                <span class="lbl">Orders</span>
                <span class="val">{{ $overall['order_count'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    <div class="summary-stats">
        <div class="stat-box">
            <div class="stat-label">Orders</div>
            <div class="stat-value">{{ $overall['order_count'] ?? 0 }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Total Items</div>
            <div class="stat-value">{{ $overall['total_items'] ?? 0 }}</div>
        </div>
        <div class="stat-box value">
            <div class="stat-label">Order Value</div>
            <div class="stat-value">{{ $fmtRs($overall['total_amount'] ?? 0) }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Pending / Converted</div>
            <div class="stat-value">{{ ($overall['pending_count'] ?? 0) }} / {{ ($overall['converted_count'] ?? 0) }}</div>
        </div>
    </div>

    <table class="ledger-table">
        @if ($reportType === 'customer')
            <thead>
                <tr>
                    <th style="width:40%;">Customer</th>
                    <th style="width:15%;">Orders</th>
                    <th style="width:15%;">Items</th>
                    <th style="width:15%;">Pending</th>
                    <th style="width:15%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="cell-desc" title="{{ $row->customer_name }} ({{ $row->customer_code }})">
                            {{ $row->customer_name }} ({{ $row->customer_code }})
                        </td>
                        <td class="text-end">{{ $row->order_count }}</td>
                        <td class="text-end">{{ $row->total_items }}</td>
                        <td class="text-end">{{ $row->pending_orders }}</td>
                        <td class="text-end amt">{{ $fmt($row->total_amount) }}</td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="5">No data in the selected period.</td></tr>
                @endforelse
            </tbody>
        @else
            <thead>
                <tr>
                    <th style="width:14%;">Order No</th>
                    <th style="width:10%;">Date</th>
                    <th style="width:28%;">Customer</th>
                    <th style="width:8%;">Items</th>
                    <th style="width:14%;">Amount</th>
                    <th style="width:12%;">Status</th>
                    <th style="width:14%;">Invoice</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="text-center">{{ $row->orderNo }}</td>
                        <td class="text-center">{{ $row->orderDate?->format('d-M-Y') ?: '—' }}</td>
                        <td class="cell-desc" title="{{ $row->customerName }}">{{ $row->customerName ?: '—' }}</td>
                        <td class="text-center">{{ $row->noOfItems }}</td>
                        <td class="text-end amt">{{ $fmt($row->grandTotal) }}</td>
                        <td class="text-center">
                            <span class="status-pill {{ $statusClass($row->status) }}">{{ $row->status ?: '—' }}</span>
                        </td>
                        <td class="text-center">{{ $row->converted_invoice_no ?: '—' }}</td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="7">No orders in the selected period.</td></tr>
                @endforelse
            </tbody>
        @endif
    </table>

    <div class="report-footer">
        This is a computer-generated report. &copy; {{ date('Y') }} Trade Gate
    </div>
</div>
