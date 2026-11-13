<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $config['title'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #000; }
        h2 { text-align: center; margin: 0 0 8px; font-size: 14px; }
        .meta { text-align: center; margin-bottom: 10px; color: #000; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; }
        th { background: #fff; color: #000; border-bottom: 2px solid #000; font-size: 10px; text-transform: uppercase; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        tfoot td { font-weight: bold; background: #eef2f7; }
    </style>
</head>
<body>
@php $fmt = static fn ($n) => number_format((float) $n, 2); @endphp
    <h2>{{ $config['title'] }}</h2>
    <div class="meta">
        As of {{ now()->format('d-M-Y h:i A') }}
        &nbsp;|&nbsp; Company: {{ $filters['company'] ?: 'All' }}
        &nbsp;|&nbsp; Category: {{ $filters['category'] ?: 'All' }}
        &nbsp;|&nbsp; Products: {{ $summary['total_products'] ?? 0 }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Product Name</th>
                <th>Company</th>
                <th>Category</th>
                <th class="text-end">Stock</th>
                <th class="text-end">Less Qty</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="text-center">{{ $row->productCode }}</td>
                    <td>{{ $row->productName }}</td>
                    <td>{{ $row->companyName ?: '—' }}</td>
                    <td class="text-center">{{ $row->catagoryName ?: '—' }}</td>
                    <td class="text-end">{{ $fmt($row->stockQty) }}</td>
                    <td class="text-end">{{ $fmt($row->lessQty) }}</td>
                    <td class="text-center">{{ $row->stock_status }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">No records found.</td></tr>
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
</body>
</html>
