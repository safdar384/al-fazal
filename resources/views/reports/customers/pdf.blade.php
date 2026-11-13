<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $config['title'] }}</title>
    <style>
        @page { size: A4; margin: 9mm 8mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #000; font-size: 10.5px; line-height: 1.2; }
        .header { margin-bottom: 8px; border-bottom: 1px solid #d1d5db; padding-bottom: 4px; }
        .title { font-size: 14px; font-weight: 700; margin: 0; }
        .meta { margin-top: 2px; font-size: 10px; color: #000; }
        .filters { margin: 6px 0 8px; font-size: 10px; }
        .filters span { display: inline-block; margin-right: 8px; }
        .summary { margin: 8px 0; font-size: 10px; }
        .summary span { display: inline-block; margin-right: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; table-layout: fixed; }
        th, td { border: 1px solid #e5e7eb; padding: 3px 4px; vertical-align: middle; word-break: break-word; }
        th { background: #f3f4f6; text-align: left; }
        .text-right { text-align: right; }
        tfoot th, tfoot td { background: #f9fafb; font-weight: 700; }
        .net-row th, .net-row td { background: #dbeafe; }
    </style>
</head>
<body>
    @include('components.invoiceHeader')

    <div class="header">
        <p class="title">{{ $config['title'] }}</p>
        <div class="meta">Date Range: {{ $fromDate }} to {{ $toDate }}</div>
    </div>

    <div class="filters">
        <strong>Applied Filters:</strong>
        <span>Area: {{ $filters['area'] ?: 'All' }}</span>
        <span>Customer: {{ $filters['customer_code'] ?: 'All' }}</span>
    </div>

    @if ($rows->isNotEmpty())
        <div class="summary">
            <span><strong>Total Receivable:</strong> Rs. {{ number_format($summary['total_receivable'], 2) }}</span>
            <span><strong>Total Payable:</strong> Rs. {{ number_format($summary['total_payable'], 2) }}</span>
            <span><strong>Balance (Payable − Receivable):</strong> Rs. {{ number_format($summary['net_balance'], 2) }}</span>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Customer / Party Name</th>
                <th>Area</th>
                <th>Type</th>
                <th class="text-right">Receivable Balance</th>
                <th class="text-right">Payable Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->customer_name }}</td>
                    <td>{{ $row->area ?: '—' }}</td>
                    <td>{{ $row->party_type ?? 'Customer' }}</td>
                    <td class="text-right">{{ (float) $row->receivable_balance > 0 ? number_format((float) $row->receivable_balance, 2) : '—' }}</td>
                    <td class="text-right">{{ (float) $row->payable_balance > 0 ? number_format((float) $row->payable_balance, 2) : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No records found.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <th colspan="3" class="text-right">Total</th>
                    <th class="text-right">{{ number_format($summary['total_receivable'], 2) }}</th>
                    <th class="text-right">{{ number_format($summary['total_payable'], 2) }}</th>
                </tr>
                <tr class="net-row">
                    <th colspan="3" class="text-right">Balance (Payable − Receivable)</th>
                    <th colspan="2" class="text-right">Rs. {{ number_format($summary['net_balance'], 2) }}</th>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
