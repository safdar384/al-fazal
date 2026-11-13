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
@php
    $fmt = static fn ($n) => number_format((float) $n, 2);
    $isCategory = ($config['mode'] ?? '') === 'category';
@endphp
    <h2>{{ $config['title'] }}</h2>
    <div class="meta">
        {{ \Carbon\Carbon::parse($fromDate)->format('d-M-Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d-M-Y') }}
        &nbsp;|&nbsp; Total Amount: {{ $fmt($summary['total_amount'] ?? 0) }}
    </div>

    @if ($isCategory)
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-center">Entries</th>
                    <th class="text-end">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row->category }}</td>
                        <td class="text-center">{{ $row->entry_count }}</td>
                        <td class="text-end">{{ $fmt($row->total_amount) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center">No data.</td></tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
                <tfoot>
                    <tr>
                        <td class="text-end">TOTAL</td>
                        <td class="text-center">{{ $summary['total_entries'] }}</td>
                        <td class="text-end">{{ $fmt($summary['total_amount']) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th>Date</th><th>Code</th><th>Category</th><th>Description</th>
                    <th>Payment</th><th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="text-center">{{ \Carbon\Carbon::parse($row->report_date)->format('d-M-Y') }}</td>
                        <td class="text-center">{{ $row->expense_code }}</td>
                        <td>{{ $row->category }}</td>
                        <td>{{ $row->description }}</td>
                        <td class="text-center">{{ $row->payment_method }}</td>
                        <td class="text-end">{{ $fmt($row->amount) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No data.</td></tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end">TOTAL AMOUNT</td>
                        <td class="text-end">{{ $fmt($summary['total_amount']) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @endif
</body>
</html>
