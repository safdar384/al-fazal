@php
    $fmt = $fmt ?? static fn ($n) => number_format((float) $n, 2);
    $fmtRs = $fmtRs ?? static fn ($n) => 'Rs. ' . number_format((float) $n, 2);
    $supplierLabel = 'All';
    if (! empty($filters['supplier_code'])) {
        $match = collect($filterOptions['suppliers'] ?? [])->firstWhere('supplierCode', $filters['supplier_code']);
        $supplierLabel = $match->supplierName ?? $filters['supplier_code'];
    }
    $productLabel = 'All';
    if (! empty($filters['product_code'])) {
        $pm = collect($filterOptions['products'] ?? [])->firstWhere('productCode', $filters['product_code']);
        $productLabel = $pm->productName ?? $filters['product_code'];
    }
@endphp

<div id="purchaseReportLive">
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
                @if ($config['show']['area'])
                    <span class="lbl">Area</span>
                    <span class="val">{{ $filters['area'] !== '' ? $filters['area'] : 'All' }}</span>
                @endif
                @if ($config['show']['supplier'])
                    <span class="lbl">Supplier</span>
                    <span class="val" title="{{ $supplierLabel }}">{{ $supplierLabel }}</span>
                @endif
                @if ($config['show']['company'])
                    <span class="lbl">Company</span>
                    <span class="val">{{ $filters['company'] !== '' ? $filters['company'] : 'All' }}</span>
                @endif
                @if ($config['show']['product'])
                    <span class="lbl">Product</span>
                    <span class="val" title="{{ $productLabel }}">{{ $productLabel }}</span>
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
                <th style="width:12%;">Date</th>
                <th style="width:14%;">Invoice No</th>
                <th style="width:28%;">Supplier</th>
                <th style="width:28%;">Product</th>
                <th style="width:8%;">Quantity</th>
                <th style="width:10%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row->report_date)->format('d-M-Y') }}</td>
                    <td class="text-center">{{ $row->invoice_no }}</td>
                    <td class="cell-desc" title="{{ $row->supplier_name }}">{{ $row->supplier_name }}</td>
                    <td class="cell-desc" title="{{ $row->product_name }}">{{ $row->product_name }}</td>
                    <td class="text-end">{{ $fmt($row->quantity) }}</td>
                    <td class="text-end amt">{{ $fmt($row->amount) }}</td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="6">No report data found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end">TOTAL</td>
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
