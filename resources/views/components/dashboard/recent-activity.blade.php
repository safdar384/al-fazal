@php
    $currency = fn ($amount) => 'PKR ' . number_format((float) $amount, 2);
@endphp

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0 erp-dash-activity-table">
        <thead style="background: linear-gradient(180deg, #f8fbff, #eef6ff);">
            <tr>
                <th class="ps-3">Type</th>
                <th>Reference</th>
                <th class="d-none d-md-table-cell">Party</th>
                <th class="text-end">Amount</th>
                <th class="d-none d-sm-table-cell">Status</th>
                <th class="pe-3">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($recentActivity as $item)
                @php
                    $typeClass = match ($item['type']) {
                        'Sale' => 'text-bg-primary',
                        'Purchase' => 'text-bg-secondary',
                        'Order' => 'text-bg-warning',
                        'Recovery' => 'text-bg-success',
                        'Expense' => 'text-bg-danger',
                        default => 'text-bg-light text-dark',
                    };
                @endphp
                <tr>
                    <td><span class="badge erp-dash-badge {{ $typeClass }}">{{ $item['type'] }}</span></td>
                    <td class="fw-semibold text-truncate" style="max-width:8rem;">{{ $item['reference'] ?: '—' }}</td>
                    <td class="d-none d-md-table-cell text-truncate" style="max-width:10rem;">{{ $item['party'] ?: '—' }}</td>
                    <td class="text-end fw-semibold text-nowrap">{{ $currency($item['amount']) }}</td>
                    <td class="d-none d-sm-table-cell"><span class="badge text-bg-light text-dark">{{ $item['status'] }}</span></td>
                    <td class="text-nowrap text-muted small">
                        {{ $item['date'] ? $item['date']->format('d M Y') : '—' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No recent activity found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
