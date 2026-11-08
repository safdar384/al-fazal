<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $config['title'] }}</title>
    <style>
        @page { size: A4; margin: 9mm 8mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 10.5px; line-height: 1.2; }
        .header { margin-bottom: 8px; border-bottom: 1px solid #d1d5db; padding-bottom: 4px; }
        .title { font-size: 14px; font-weight: 700; margin: 0; }
        .meta { margin-top: 2px; font-size: 10px; color: #4b5563; }
        .filters { margin: 6px 0 8px; font-size: 10px; }
        .filters span { display: inline-block; margin-right: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th, td { border: 1px solid #e5e7eb; padding: 3px 4px; vertical-align: middle; }
        th { background: #f3f4f6; text-align: left; }
        .text-right { text-align: right; }
        .company-header { gap: 8px; margin-bottom: 6px; padding-bottom: 4px; }
        .company-name { font-size: 15px; line-height: 1.1; margin-bottom: 0; }
        .company-tagline, .company-contact { font-size: 9px; line-height: 1.1; margin-bottom: 1px; }
        .company-monogram { flex: 0 0 65px; }
        .monogram-logo { font-size: 24px; margin-bottom: 0; }
        .monogram-text { font-size: 8px; }
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
        <span>Supplier: {{ $filters['supplier_code'] ?: 'All' }}</span>
        <span>Product: {{ $filters['product_code'] ?: 'All' }}</span>
        <span>Area: {{ $filters['area'] ?: 'All' }}</span>
        <span>Company: {{ $filters['company'] ?: 'All' }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice No</th>
                <th>Supplier</th>
                <th>Product</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row->report_date)->format('Y-m-d') }}</td>
                    <td>{{ $row->invoice_no }}</td>
                    <td>{{ $row->supplier_name }}</td>
                    <td>{{ $row->product_name }}</td>
                    <td class="text-right">{{ number_format((float) $row->quantity, 2) }}</td>
                    <td class="text-right">{{ number_format((float) $row->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No records found.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">Total</th>
                    <th class="text-right">{{ number_format($summary['total_quantity'], 2) }}</th>
                    <th class="text-right">Rs. {{ number_format($summary['total_amount'], 2) }}</th>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
