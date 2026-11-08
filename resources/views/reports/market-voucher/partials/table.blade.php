@php
    $fmt = fn ($n) => number_format((float) $n, 2);
    $rows = $report['rows'];
    $totals = $report['totals'];
    $cellAmt = static function ($n) use ($fmt) {
        $v = (float) $n;
        return $v > 0 ? $fmt($v) : '—';
    };
@endphp

<table class="table table-bordered table-sm mv-report-table mb-0 align-middle">
    <thead>
        <tr>
            <th class="text-center" style="width:4%">Page</th>
            <th style="width:16%">Customer Name</th>
            <th class="text-center" style="width:8%">City</th>
            <th class="text-end" style="width:9%">Pre Balance</th>
            <th class="text-end" style="width:8%">Delivery</th>
            <th class="text-end" style="width:9%">Balance</th>
            <th class="text-end" style="width:8%">Recovery</th>
            <th class="text-end" style="width:7%">Discount</th>
            <th class="text-end" style="width:7%">Bilty</th>
            <th class="text-end" style="width:7%">Other</th>
            <th class="text-end" style="width:9%">Last Balance</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td class="text-center">{{ $row->page_no }}</td>
                <td>{{ $row->customer_name }}</td>
                <td class="text-center">{{ $row->city }}</td>
                <td class="text-end">{{ $fmt($row->pre_balance) }}</td>
                <td class="text-end">{{ $fmt($row->delivery) }}</td>
                <td class="text-end fw-semibold">{{ $fmt($row->balance) }}</td>
                <td class="text-end">{{ $cellAmt($row->recovery) }}</td>
                <td class="text-end">{{ $cellAmt($row->discount) }}</td>
                <td class="text-end">{{ $cellAmt($row->bilty) }}</td>
                <td class="text-end">{{ $cellAmt($row->other) }}</td>
                <td class="text-end fw-semibold">{{ $fmt($row->last_balance) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="text-center text-muted py-4">No customers found for this area.</td>
            </tr>
        @endforelse
    </tbody>
    @if ($rows->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="3" class="text-end">TOTALS</td>
                <td class="text-end">{{ $fmt($totals['pre_balance']) }}</td>
                <td class="text-end">{{ $fmt($totals['delivery']) }}</td>
                <td class="text-end">{{ $fmt($totals['balance']) }}</td>
                <td class="text-end">{{ $fmt($totals['recovery'] ?? 0) }}</td>
                <td class="text-end">{{ $fmt($totals['discount'] ?? 0) }}</td>
                <td class="text-end">{{ $fmt($totals['bilty'] ?? 0) }}</td>
                <td class="text-end">{{ $fmt($totals['other'] ?? 0) }}</td>
                <td class="text-end">{{ $fmt($totals['last_balance'] ?? 0) }}</td>
            </tr>
        </tfoot>
    @endif
</table>
