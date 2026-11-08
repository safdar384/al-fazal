
        @page { size: A4 landscape; margin: 8mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 8px; line-height: 1.2; }
        .header { text-align: center; margin-bottom: 8px; border-bottom: 2px solid #333; padding-bottom: 6px; }
        .title { font-size: 13px; font-weight: 700; margin: 4px 0; }
        .meta { font-size: 9px; color: #4b5563; margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #333; padding: 3px 2px; vertical-align: top; word-break: break-word; }
        th { background: #e9ecef; font-weight: 700; font-size: 7.5px; text-transform: uppercase; }
        .opening { background: #fff3cd; font-weight: 700; }
        tfoot th { background: #f3f4f6; }
@php
    $fmt = static fn($value) => $value === null || $value === '' ? '—' : rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    $balanceLabel = $summary['balance_label'] ?? ($balanceLabel ?? 'Payable Balance');
    $typeLabels = ['purchase' => 'Purchase', 'sale' => 'Sale', 'payment' => 'Payment', 'discount' => 'Discount', 'other' => 'Other', 'Opening' => 'Opening'];
@endphp
@include('components.invoiceHeader')
<div class="header">
    <p class="title">Supplier Ledger Report</p>
    <div class="meta">Supplier: {{ $selectedSupplierName }} ({{ $selectedSupplierCode }})</div>
    <div class="meta">Period: {{ $fromDate }} to {{ $toDate }}</div>
    @if (($transactionType ?? 'all') !== 'all')
        <div class="meta">Filter: {{ $typeLabels[$transactionType] ?? $transactionType }}</div>
    @endif
</div>
<table>
    <thead>
        <tr>
            <th style="width:7%;">Date</th>
            <th style="width:9%;">Invoice</th>
            <th style="width:7%;">Type</th>
            <th style="width:18%;">Product</th>
            <th class="text-right" style="width:6%;">Qty</th>
            <th class="text-right" style="width:10%;">Rate</th>
            <th class="text-right" style="width:9%;">Debit</th>
            <th class="text-right" style="width:8%;">Credit</th>
            <th class="text-right" style="width:10%;">{{ $balanceLabel }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($ledgerRows as $row)
            @php $tk = $row['transaction_type'] ?? ''; @endphp
            <tr @if (!empty($row['is_opening'])) class="opening" @endif>
                <td>{{ $row['date'] ?? '—' }}</td>
                <td>{{ $row['invoice_no'] ?? '—' }}</td>
                <td>{{ $typeLabels[$tk] ?? ucfirst($tk) }}</td>
                <td>{{ $row['product_name'] ?? '—' }}</td>
                <td class="text-right">{{ $fmt($row['qty'] ?? null) }}</td>
                <td class="text-right">{{ $fmt($row['final_rate'] ?? $row['rate'] ?? null) }}</td>
                <td class="text-right debit">{{ ($row['debit'] ?? 0) > 0 ? $fmt($row['debit']) : '-' }}</td>
                <td class="text-right credit">{{ ($row['credit'] ?? 0) > 0 ? $fmt($row['credit']) : '-' }}</td>
                <td class="text-right">{{ $fmt($row['running_balance']) }}</td>
            </tr>
        @endforeach
    </tbody>
    @if ($summary)
        <tfoot>
            <tr>
                <th colspan="6" class="text-right">Period totals</th>
                <th class="text-right">{{ $fmt($summary['total_debit']) }}</th>
                <th class="text-right">{{ $fmt($summary['total_credit']) }}</th>
                <th class="text-right">{{ $fmt($summary['closing_balance']) }}</th>
            </tr>
        </tfoot>
    @endif
</table>