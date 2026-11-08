<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $config['title'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; }
        h2 { text-align: center; margin: 0 0 8px; font-size: 14px; }
        .meta { text-align: center; margin-bottom: 10px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; }
        th { background: #0f2747; color: #fff; font-size: 10px; text-transform: uppercase; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        tfoot td { font-weight: bold; background: #eef2f7; }
    </style>
</head>
<body>
@php
    $fmt = static fn ($n) => number_format((float) $n, 2);
    $isAdvance = ($config['mode'] ?? '') === 'advance';
    $isBalances = ($config['mode'] ?? '') === 'balances';
@endphp
    <h2>{{ $config['title'] }}</h2>
    <div class="meta">
        @if ($config['show']['dates'] ?? true)
            {{ \Carbon\Carbon::parse($fromDate)->format('d-M-Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d-M-Y') }}
        @else
            As of {{ now()->format('d-M-Y') }}
        @endif
        @if ($isBalances || $isAdvance)
            &nbsp;|&nbsp; Total Balance: {{ $fmt($summary['total_balance'] ?? 0) }}
        @endif
    </div>

    @if ($isAdvance)
        <table>
            <thead>
                <tr>
                    <th>Code</th><th>Labour Name</th><th>Category</th>
                    <th class="text-end">Advance</th><th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="text-center">{{ $row->labour_code }}</td>
                        <td>{{ $row->labour_name }}</td>
                        <td class="text-center">{{ $row->category_label }}</td>
                        <td class="text-end">{{ $fmt($row->advance) }}</td>
                        <td class="text-end">{{ $fmt($row->balance) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">No data.</td></tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end">TOTAL</td>
                        <td class="text-end">{{ $fmt($summary['total_advance']) }}</td>
                        <td class="text-end">{{ $fmt($summary['total_balance']) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @elseif ($isBalances)
        <table>
            <thead>
                <tr>
                    <th>Code</th><th>Labour Name</th><th>Category</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="text-center">{{ $row->labour_code }}</td>
                        <td>{{ $row->labour_name }}</td>
                        <td class="text-center">{{ $row->category_label }}</td>
                        <td class="text-end">{{ $fmt($row->balance) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">No data.</td></tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end">TOTAL BALANCE</td>
                        <td class="text-end">{{ $fmt($summary['total_balance']) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th>Date</th><th>Labour</th><th>Category</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td class="text-center">{{ \Carbon\Carbon::parse($row->report_date)->format('d-M-Y') }}</td>
                        <td>{{ $row->labour_name }}</td>
                        <td class="text-center">{{ $row->category_label }}</td>
                        <td class="text-end">{{ $fmt($row->balance) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">No data.</td></tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end">TOTAL BALANCE</td>
                        <td class="text-end">{{ $fmt($summary['total_balance']) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @endif
</body>
</html>
