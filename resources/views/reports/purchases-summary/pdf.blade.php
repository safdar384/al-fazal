<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Purchase Summary</title></head><body>
@include('components.invoiceHeader')
<h2>Purchase Summary</h2>
<p>{{ $fromDate }} to {{ $toDate }}</p>
<p>Total: Rs. {{ number_format($overall['total_purchases'], 2) }} | Paid: {{ number_format($overall['total_paid'], 2) }} | Due: {{ number_format($overall['total_outstanding'], 2) }}</p>
</body></html>
