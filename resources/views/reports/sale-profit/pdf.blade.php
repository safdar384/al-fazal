<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $config['title'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #000; }
        h2 { margin: 0 0 6px; font-size: 14px; color: #0f2747; text-align: center; }
        .meta { margin-bottom: 8px; font-size: 9px; color: #000; text-align: center; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #cfd8e3; padding: 3px 4px; }
        th { background: #fff; color: #000; border-bottom: 2px solid #000; font-size: 8px; text-transform: uppercase; }
        td { font-size: 8.5px; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        tfoot td { background: #e8eef5; font-weight: bold; }
        .pos { color: #198754; font-weight: bold; }
        .neg { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
@php $fmt = static fn ($n) => number_format((float) $n, 2); @endphp
    <h2>{{ $config['title'] }}</h2>
    <div class="meta">
        {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} – {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}
        · Invoices {{ $summary['total_invoices'] ?? 0 }}
        · Profit {{ $fmt($summary['total_profit'] ?? 0) }}
    </div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Area</th>
                <th>Ajent</th>
                <th>Sale</th>
                <th>Cost</th>
                <th>Profit</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row->invoice_date)->format('d-m-Y') }}</td>
                    <td class="text-center">{{ $row->invoice_no }}</td>
                    <td>{{ $row->customer_name }}</td>
                    <td class="text-center">{{ $row->area }}</td>
                    <td class="text-center">{{ $row->ajent_name }}</td>
                    <td class="text-end">{{ $fmt($row->sale_amount) }}</td>
                    <td class="text-end">{{ $fmt($row->cost_amount) }}</td>
                    <td class="text-end {{ $row->profit_amount < 0 ? 'neg' : 'pos' }}">{{ $fmt($row->profit_amount) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">No sale invoices found.</td></tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="5" class="text-end">TOTAL</td>
                    <td class="text-end">{{ $fmt($summary['total_sale']) }}</td>
                    <td class="text-end">{{ $fmt($summary['total_cost']) }}</td>
                    <td class="text-end {{ ($summary['total_profit'] ?? 0) < 0 ? 'neg' : 'pos' }}">{{ $fmt($summary['total_profit']) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
