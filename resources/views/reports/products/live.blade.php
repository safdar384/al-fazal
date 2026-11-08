@php
    $fmt = $fmt ?? static fn ($n) => number_format((float) $n, 2);
    $companyLabel = ($filters['company'] ?? '') !== '' ? $filters['company'] : 'All';
    $categoryLabel = ($filters['category'] ?? '') !== '' ? $filters['category'] : 'All';
    $productLabel = 'All';
    if (! empty($filters['product_code'])) {
        $match = collect($filterOptions['products'] ?? [])->firstWhere('productCode', $filters['product_code']);
        $productLabel = $match->productName ?? $filters['product_code'];
    }
@endphp

<div id="productReportLive">
    <div class="meta-bar">
        <div class="meta-box">
            <h6>Filters</h6>
            <div class="meta-grid">
                @if ($config['show']['company'] ?? false)
                    <span class="lbl">Company</span>
                    <span class="val">{{ $companyLabel }}</span>
                @endif
                @if ($config['show']['category'] ?? false)
                    <span class="lbl">Category</span>
                    <span class="val">{{ $categoryLabel }}</span>
                @endif
                @if ($config['show']['product'] ?? false)
                    <span class="lbl">Product</span>
                    <span class="val" title="{{ $productLabel }}">{{ $productLabel }}</span>
                @endif
                @if (!($config['show']['company'] ?? false) && !($config['show']['category'] ?? false) && !($config['show']['product'] ?? false))
                    <span class="lbl">Scope</span>
                    <span class="val">All Products</span>
                @endif
            </div>
        </div>
        <div class="meta-box">
            <h6>Report</h6>
            <div class="meta-grid">
                <span class="lbl">Generated</span>
                <span class="val">{{ now()->format('d-M-Y h:i A') }}</span>
                <span class="lbl">Products</span>
                <span class="val">{{ $summary['total_products'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    <div class="summary-stats">
        <div class="stat-box">
            <div class="stat-label">Products</div>
            <div class="stat-value">{{ $summary['total_products'] ?? 0 }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Total Stock</div>
            <div class="stat-value">{{ $fmt($summary['total_stock'] ?? 0) }}</div>
        </div>
        <div class="stat-box {{ ($summary['short_products'] ?? 0) > 0 ? 'warn' : 'value' }}">
            <div class="stat-label">Short Stock</div>
            <div class="stat-value">{{ $summary['short_products'] ?? 0 }}</div>
        </div>
    </div>

    <table class="ledger-table">
        <thead>
            <tr>
                <th style="width:12%;">Code</th>
                <th style="width:28%;">Product Name</th>
                <th style="width:18%;">Company</th>
                <th style="width:14%;">Category</th>
                <th style="width:10%;">Stock</th>
                <th style="width:10%;">Less Qty</th>
                <th style="width:8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="text-center">{{ $row->productCode }}</td>
                    <td class="cell-desc" title="{{ $row->productName }}">{{ $row->productName }}</td>
                    <td class="cell-desc" title="{{ $row->companyName }}">{{ $row->companyName ?: '—' }}</td>
                    <td class="text-center">{{ $row->catagoryName ?: '—' }}</td>
                    <td class="text-end amt">{{ $fmt($row->stockQty) }}</td>
                    <td class="text-end">{{ $fmt($row->lessQty) }}</td>
                    <td class="text-center">
                        @if ($row->stock_status === 'Short Stock')
                            <span class="status-pill st-short">Short</span>
                        @else
                            <span class="status-pill st-ok">OK</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="7">No product records found for selected filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end">TOTAL</td>
                    <td class="text-end">{{ $fmt($summary['total_stock']) }}</td>
                    <td class="text-end">{{ $fmt($summary['total_threshold']) }}</td>
                    <td class="text-center">{{ $summary['short_products'] }} Short</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="report-footer">
        This is a computer-generated report. &copy; {{ date('Y') }} Trade Gate
    </div>
</div>
