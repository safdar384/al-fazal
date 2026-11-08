@php
    $fmt = fn ($n) => 'Rs. '.number_format((float) $n, 2);
@endphp
<div class="table-responsive">
    <table class="table table-striped table-hover table-sm align-middle mb-0">
        <colgroup>
            <col style="width: 32%;">
            <col style="width: 14%;">
            <col style="width: 12%;">
            <col style="width: 21%;">
            <col style="width: 21%;">
        </colgroup>
        <thead class="table-light">
            <tr>
                <th>Customer / Party Name</th>
                <th>Area</th>
                <th>Type</th>
                <th class="text-end text-success">Receivable Balance</th>
                <th class="text-end text-danger">Payable Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->customer_name }}</td>
                    <td>{{ $row->area ?: '—' }}</td>
                    <td>
                        <span class="badge {{ ($row->party_type ?? '') === 'Supplier' ? 'text-bg-danger' : 'text-bg-success' }} bg-opacity-75">
                            {{ $row->party_type ?? 'Customer' }}
                        </span>
                    </td>
                    <td class="text-end {{ (float) $row->receivable_balance > 0 ? 'text-success fw-semibold' : 'text-muted' }}">
                        {{ (float) $row->receivable_balance > 0 ? $fmt($row->receivable_balance) : '—' }}
                    </td>
                    <td class="text-end {{ (float) $row->payable_balance > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">
                        {{ (float) $row->payable_balance > 0 ? $fmt($row->payable_balance) : '—' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No balance data found for selected filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot class="table-light fw-semibold border-top border-2">
                <tr>
                    <td colspan="3" class="text-end">Total</td>
                    <td class="text-end text-success">{{ $fmt($summary['total_receivable']) }}</td>
                    <td class="text-end text-danger">{{ $fmt($summary['total_payable']) }}</td>
                </tr>
                <tr class="table-primary">
                    <td colspan="3" class="text-end">Balance (Payable − Receivable)</td>
                    <td colspan="2" class="text-end {{ ($summary['net_balance'] ?? 0) >= 0 ? 'text-danger' : 'text-success' }}">
                        {{ $fmt($summary['net_balance'] ?? 0) }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
