<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>{{ $title }}</title>
<style>
body{font-family:DejaVu Sans,sans-serif;font-size:10px;}
table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ddd;padding:3px;}
</style></head><body>
@include('components.invoiceHeader')
<h2>{{ $title }}</h2>
<p>{{ $fromDate }} to {{ $toDate }}</p>
<p>Orders: {{ $overall['order_count'] }} | Value: Rs. {{ number_format($overall['total_amount'], 2) }}</p>
<table>
<thead><tr><th>Order</th><th>Date</th><th>Customer</th><th>Items</th><th>Amount</th><th>Status</th></tr></thead>
<tbody>
@foreach($rows as $row)
@if($reportType === 'customer')
<tr><td>{{ $row->customer_name }}</td><td>{{ $row->order_count }}</td><td>{{ $row->total_items }}</td><td>{{ number_format($row->total_amount,2) }}</td><td></td><td></td></tr>
@else
<tr><td>{{ $row->orderNo }}</td><td>{{ $row->orderDate }}</td><td>{{ $row->customerName }}</td><td>{{ $row->noOfItems }}</td><td>{{ number_format($row->grandTotal,2) }}</td><td>{{ $row->status }}</td></tr>
@endif
@endforeach
</tbody>
</table>
</body></html>
