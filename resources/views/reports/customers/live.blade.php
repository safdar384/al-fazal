@php
    $fmt = $fmt ?? static fn ($n) => number_format((float) $n, 2);
    $fmtRs = $fmtRs ?? static fn ($n) => 'Rs. ' . number_format((float) $n, 2);
    $customerLabel = 'All';
    if (! empty($filters['customer_code'])) {
        $match = collect($filterOptions['customers'] ?? [])->firstWhere('custCode', $filters['customer_code']);
        $customerLabel = $match
            ? (($match->bussnessName ?: $match->custName) ?: $filters['customer_code'])
            : $filters['customer_code'];
    }
@endphp

<div id="customerReportLive">
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
                    <span class="val">{{ ($filters['area'] ?? '') !== '' ? $filters['area'] : 'All' }}</span>
                @endif
                @if ($config['show']['customer'])
                    <span class="lbl">Customer</span>
                    <span class="val" title="{{ $customerLabel }}">{{ $customerLabel }}</span>
                @endif
            </div>
        </div>
        <div class="meta-box">
            <h6>Report</h6>
            <div class="meta-grid">
                <span class="lbl">Generated</span>
                <span class="val">{{ now()->format('d-M-Y h:i A') }}</span>
                <span class="lbl">Parties</span>
                <span class="val">{{ $rows->count() }}</span>
            </div>
        </div>
    </div>

    <div class="summary-stats">
        <div class="stat-box recv">
            <div class="stat-label">Total Receivable</div>
            <div class="stat-value">{{ $fmtRs($summary['total_receivable'] ?? 0) }}</div>
        </div>
        <div class="stat-box pay">
            <div class="stat-label">Total Payable</div>
            <div class="stat-value">{{ $fmtRs($summary['total_payable'] ?? 0) }}</div>
        </div>
        <div class="stat-box {{ ($summary['net_balance'] ?? 0) >= 0 ? 'pay' : 'recv' }}">
            <div class="stat-label">Balance (Pay − Rec)</div>
            <div class="stat-value">{{ $fmtRs($summary['net_balance'] ?? 0) }}</div>
        </div>
    </div>

    <table class="ledger-table">
        <thead>
            <tr>
                <th style="width:32%;">Customer / Party</th>
                <th style="width:14%;">Area</th>
                <th style="width:12%;">Type</th>
                <th style="width:21%;">Receivable</th>
                <th style="width:21%;">Payable</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="cell-desc" title="{{ $row->customer_name }}">{{ $row->customer_name }}</td>
                    <td class="text-center">{{ $row->area ?: '—' }}</td>
                    <td class="text-center">
                        @if (($row->party_type ?? '') === 'Supplier')
                            <span class="status-pill st-pay">Supplier</span>
                        @else
                            <span class="status-pill st-recv">Customer</span>
                        @endif
                    </td>
                    <td class="text-end {{ (float) $row->receivable_balance > 0 ? 'amt-recv' : '' }}">
                        {{ (float) $row->receivable_balance > 0 ? $fmt($row->receivable_balance) : '—' }}
                    </td>
                    <td class="text-end {{ (float) $row->payable_balance > 0 ? 'amt-pay' : '' }}">
                        {{ (float) $row->payable_balance > 0 ? $fmt($row->payable_balance) : '—' }}
                    </td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="5">No balance data found for selected filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end">TOTAL</td>
                    <td class="text-end amt-recv">{{ $fmt($summary['total_receivable']) }}</td>
                    <td class="text-end amt-pay">{{ $fmt($summary['total_payable']) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end">BALANCE (PAYABLE − RECEIVABLE)</td>
                    <td colspan="2" class="text-end {{ ($summary['net_balance'] ?? 0) >= 0 ? 'amt-pay' : 'amt-recv' }}">
                        {{ $fmt($summary['net_balance'] ?? 0) }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="report-footer">
        This is a computer-generated report. &copy; {{ date('Y') }} Trade Gate
    </div>
</div>
