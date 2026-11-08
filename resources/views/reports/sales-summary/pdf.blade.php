<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Summary Report</title>
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1f2937; }
        h1 { font-size: 14px; margin: 0 0 4px; }
        .meta { font-size: 9px; color: #4b5563; margin-bottom: 8px; }
        .cards { width: 100%; margin-bottom: 8px; border-collapse: collapse; }
        .cards td { border: 1px solid #e5e7eb; padding: 4px 6px; text-align: center; }
        .cards .label { font-size: 8px; color: #6b7280; }
        .cards .val { font-size: 10px; font-weight: 700; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.data th, table.data td { border: 1px solid #e5e7eb; padding: 2px 4px; vertical-align: top; }
        table.data th { background: #f3f4f6; }
        table.data td.addr { white-space: normal; word-wrap: break-word; max-width: 180px; }
        .text-right { text-align: right; }
        h2 { font-size: 11px; margin: 10px 0 4px; }
        .company-header { margin-bottom: 6px; }
    </style>
</head>
<body>
    @include('components.invoiceHeader')

    <h1>Sales Summary Report</h1>
    <div class="meta">
        Period: {{ $fromDate }} to {{ $toDate }}<br>
        Customer: {{ $filters['customer_code'] ?: 'All' }} |
        Product: {{ $filters['product_code'] ?: 'All' }} |
        Area: {{ $filters['area'] ?: 'All' }} |
        Type: {{ $filters['sale_type'] ?: 'All' }}
    </div>

    <table class="cards">
        <tr>
            <td><div class="label">Net Sales</div><div class="val">Rs. {{ number_format($overall['net_sales'], 2) }}</div></td>
            <td><div class="label">Cash Sales</div><div class="val">Rs. {{ number_format($overall['cash_sales'], 2) }}</div></td>
            <td><div class="label">Credit Sales</div><div class="val">Rs. {{ number_format($overall['credit_sales'], 2) }}</div></td>
            <td><div class="label">Discount</div><div class="val">Rs. {{ number_format($overall['total_discount'], 2) }}</div></td>
            <td><div class="label">Paid</div><div class="val">Rs. {{ number_format($overall['total_paid'], 2) }}</div></td>
            <td><div class="label">Outstanding</div><div class="val">Rs. {{ number_format($overall['total_outstanding'], 2) }}</div></td>
        </tr>
    </table>

    @php $fmt = fn ($n) => number_format((float) $n, 2); @endphp

    <h2>Invoice Detail</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Address</th>
                <th>Type</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Net</th>
                <th>Bilty No.</th>
                <th class="text-right">Invoice Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoices as $inv)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($inv->invoiceDate)->format('Y-m-d') }}</td>
                    <td>{{ $inv->invoiceNo }}</td>
                    <td>{{ $inv->customerName }}</td>
                    <td class="addr">{{ $inv->customer_address }}</td>
                    <td>{{ $inv->sale_type_label }}</td>
                    <td class="text-right">{{ $fmt($inv->total_qty) }}</td>
                    <td class="text-right">{{ $fmt($inv->total_discount) }}</td>
                    <td class="text-right">{{ $fmt($inv->net_amount) }}</td>
                    <td>{{ $inv->biltyNo ?: '—' }}</td>
                    <td class="text-right">{{ $fmt($inv->invoice_amount ?? $inv->net_amount) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Product Summary</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Product</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Sales Value</th>
                <th class="text-right">Avg Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $p)
                <tr>
                    <td>{{ $p->product_name }}</td>
                    <td class="text-right">{{ $fmt($p->total_qty) }}</td>
                    <td class="text-right">{{ $fmt($p->total_sales_value) }}</td>
                    <td class="text-right">{{ $fmt($p->avg_rate) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Customer Summary</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Customer</th>
                <th class="text-right">Invoices</th>
                <th class="text-right">Sales</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Outstanding</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $c)
                <tr>
                    <td>{{ $c->customer_name }}</td>
                    <td class="text-right">{{ $c->invoice_count }}</td>
                    <td class="text-right">{{ $fmt($c->total_sales) }}</td>
                    <td class="text-right">{{ $fmt($c->total_paid) }}</td>
                    <td class="text-right">{{ $fmt($c->total_outstanding) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
