<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Summary</title>
    @include('partials.doc-pdf-styles')
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        .cards { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .cards td { border: 1px solid #000; padding: 5px; text-align: center; color: #000; }
        .cards .lbl { font-size: 8px; }
        .cards .val { font-size: 11px; font-weight: 700; }
    </style>
</head>
<body>
@include('components.invoiceHeaderPdf')

<div class="report-h1">Purchase Summary Report</div>
<div class="report-meta">Period: {{ $fromDate }} to {{ $toDate }}</div>

<table class="cards">
    <tr>
        <td><div class="lbl">Purchases</div><div class="val">Rs. {{ number_format((float) ($overall['total_purchases'] ?? 0), 2) }}</div></td>
        <td><div class="lbl">Paid</div><div class="val">Rs. {{ number_format((float) ($overall['total_paid'] ?? 0), 2) }}</div></td>
        <td><div class="lbl">Outstanding</div><div class="val">Rs. {{ number_format((float) ($overall['total_outstanding'] ?? 0), 2) }}</div></td>
        <td><div class="lbl">Invoices</div><div class="val">{{ $overall['invoice_count'] ?? 0 }}</div></td>
    </tr>
</table>

@if (!empty($invoices) && count($invoices))
<table class="data">
    <thead>
        <tr>
            <th>Date</th>
            <th>Invoice No</th>
            <th>Supplier</th>
            <th class="text-right">Amount</th>
            <th class="text-right">Paid</th>
            <th class="text-right">Due</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($invoices as $row)
            @php
                $paid = (float) ($row->paid_amount ?? 0);
                $total = (float) ($row->grandTotal ?? 0);
            @endphp
            <tr>
                <td>{{ $row->invoiceDate ?? '' }}</td>
                <td>{{ $row->invoiceNo ?? '' }}</td>
                <td>{{ $row->supplierName ?? '' }}</td>
                <td class="text-right">{{ number_format($total, 2) }}</td>
                <td class="text-right">{{ number_format($paid, 2) }}</td>
                <td class="text-right">{{ number_format(max(0, $total - $paid), 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif
</body>
</html>
