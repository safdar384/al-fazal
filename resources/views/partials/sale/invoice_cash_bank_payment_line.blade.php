{{-- Single bank line for cash invoice print / preview ($payment = SaleInvoiceBankPayment) --}}
@php
    $bank = $payment->bankAccount;
    $suffix = $bank ? $bank->invoicePrintAccountSuffix() : null;
@endphp
<p><strong>{{ $bank ? $bank->bankName : 'Bank' }}:</strong> Rs. {{ number_format($payment->amount, 2) }}@if(! empty($suffix))<span class="text-muted">({{ $suffix }})</span>@endif</p>
