<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Invoice - {{ $invoice->invoiceNo }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            
            .invoice-container {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0;
                margin: 0;
                padding: 0;
                width: 100% !important;
                max-width: none !important;
                font-size: 8.5pt;
            }

            .meta-strip,
            .meta-after {
                display: grid !important;
                grid-template-columns: minmax(0, 1.35fr) minmax(0, 0.9fr) !important;
                gap: 2mm !important;
                width: 100% !important;
            }

            .meta-cell,
            .sale-doc-row,
            .sale-doc-v {
                min-width: 0;
            }

            .sale-doc-v {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                font-weight: 400 !important;
            }

            .meta-customer .sale-doc-title,
            .meta-invoice .sale-doc-title {
                font-size: 11pt;
                font-weight: 600;
            }

            .meta-customer .sale-doc-row,
            .meta-invoice .sale-doc-row {
                font-size: 9.5pt;
                font-weight: 400;
            }

            .company-header {
                display: block !important;
            }

            .items-table th,
            .items-table td {
                padding: 2px 4px !important;
            }

            .signature-section {
                margin-top: 8mm;
            }

            .signature-line {
                margin-top: 10mm;
            }
            
            @page {
                size: A4;
                margin: 8mm;
            }
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', 'Helvetica Neue', Tahoma, Geneva, Verdana, sans-serif;
            padding: clamp(0.75rem, 2vw, 2.5rem);
            min-height: 100vh;
        }
        
        .invoice-container {
            width: min(96vw, 76rem);
            margin: 0 auto;
            background: #ffffff;
            box-shadow: 0 8px 32px rgba(15, 39, 71, 0.12);
            border-radius: 10px;
            padding: clamp(1rem, 2.2vw, 2.25rem);
            font-size: clamp(0.95rem, 0.72rem + 0.55vw, 1.2rem);
            line-height: 1.45;
        }

        .invoice-container .company-header {
            display: block;
            padding-bottom: 0.35rem;
            margin-bottom: 0.5rem;
        }

        .invoice-container .company-header-banner {
            width: 100%;
            height: auto;
            display: block;
        }

        .invoice-container .company-monogram {
            flex: 0 0 auto;
        }

        .invoice-container .monogram-logo {
            font-size: clamp(1.55rem, 1.2rem + 0.7vw, 2.05rem);
            margin-bottom: 0;
        }

        .invoice-container .monogram-text {
            font-size: 0.62rem;
        }

        .meta-invoice .sale-doc-title,
        .meta-customer .sale-doc-title {
            text-align: center;
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 1.05em;
            font-weight: 600;
            letter-spacing: 0.01em;
            color: #1a252f;
            margin: 0 0 0.3rem;
            line-height: 1.2;
        }

        .sale-doc-row {
            display: grid;
            grid-template-columns: 8.5rem minmax(0, 1fr);
            column-gap: 0.75rem;
            align-items: baseline;
            line-height: 1.45;
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 0.92em;
            font-weight: 400;
        }

        .meta-invoice .sale-doc-row {
            grid-template-columns: 4.6rem minmax(0, 1fr);
        }

        .meta-customer .sale-doc-row {
            grid-template-columns: 5.4rem minmax(0, 1fr);
        }

        .sale-doc-k {
            font-weight: 500;
            color: #34495e;
        }
        
        .sale-doc-v {
            font-weight: 400;
            color: #1c2833;
        }

        .sale-doc-spread {
            display: flex;
            justify-content: space-between;
            gap: 0.6rem;
            margin-top: 0.2rem;
            font-weight: 700;
            font-size: 0.92em;
            color: #1c2833;
        }

        .sale-doc-spread span:nth-child(2) {
            text-align: center;
            flex: 1;
        }

        .sale-doc-spread span:last-child {
            text-align: right;
            min-width: 4.5rem;
        }
        
        .meta-strip,
        .meta-after {
            display: grid;
            gap: clamp(0.35rem, 0.8vw, 0.65rem);
            align-items: stretch;
        }

        .meta-strip {
            grid-template-columns: minmax(0, 1.6fr) minmax(12rem, 0.9fr);
            margin-bottom: clamp(0.45rem, 1vw, 0.75rem);
        }

        .meta-after {
            grid-template-columns: minmax(0, 1.4fr) minmax(14rem, 0.85fr);
            margin-top: 0.45rem;
            margin-bottom: 0.35rem;
        }

        .meta-cell {
            border: 1px solid #d7dee7;
            border-top: 3px solid #2c3e50;
            border-radius: 4px;
            padding: 0.28rem 0.5rem 0.35rem;
            background: #f8fafc;
            min-width: 0;
            line-height: 1.25;
        }

        .meta-cell.meta-invoice { border-top-color: #1a5276; background: #f4f7fb; }
        .meta-cell.meta-delivery { border-top-color: #1e8449; }
        .meta-cell.meta-totals { border-top-color: #1a252f; background: #fff; padding: 0; overflow: hidden; }

        .meta-label {
            font-size: 0.68em;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #5d6d7e;
            margin-bottom: 0.1rem;
        }

        .meta-cell p {
            margin: 0;
            font-size: 0.86em;
            color: #1c2833;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .meta-cell .meta-strong {
            font-weight: 700;
            font-size: 0.92em;
        }

        .meta-pay-ok { color: #0e6655; font-weight: 700; }
        .meta-pay-due { color: #b9770e; font-weight: 700; }
        .meta-status { color: #1e8449; font-weight: 700; }
        
        .items-table {
            width: 100%;
            margin-bottom: 0.45rem;
            border-collapse: collapse;
            font-size: 0.9em;
            box-shadow: none;
            table-layout: auto;
        }
        
        .items-table thead {
            background: #34495e;
            color: white;
        }
        
        .items-table th,
        .items-table td {
            white-space: nowrap;
            vertical-align: middle;
        }
        
        .items-table th {
            padding: 0.28rem 0.4rem;
            text-align: left;
            font-size: 0.78em;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        
        .items-table td {
            padding: 0.22rem 0.4rem;
            border-bottom: 1px solid #e8eef3;
            font-size: 0.9em;
            line-height: 1.2;
        }

        .items-table td.product-name {
            white-space: nowrap;
        }

        .product-name-line {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 0.75rem;
            white-space: nowrap;
        }

        .pn-name {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 500;
        }

        .pn-company {
            flex: 0 0 auto;
            margin-left: auto;
            font-weight: 400;
            text-align: right;
        }
        
        .items-table tbody tr {
            transition: background 0.2s ease;
        }
        
        .items-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .items-table tbody tr:nth-child(even) {
            background: #fafbfc;
        }
        
        .text-right {
            text-align: right !important;
        }
        
        .totals-box {
            width: 100%;
            background: white;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.22rem 0.5rem;
            border-bottom: 1px solid #ecf0f1;
            font-size: 0.84em;
            color: #2c3e50;
        }
        
        .total-row span {
            font-weight: 500;
        }
        
        .total-row strong {
            text-align: right;
            font-weight: 700;
        }
        
        .total-row.grand-total {
            background: #1a252f;
            color: white;
            padding: 0.32rem 0.5rem;
            font-size: 0.9em;
            font-weight: 700;
            border-bottom: none;
            margin-top: 0;
        }
        
        .footer-notes {
            border-top: 1px solid #ecf0f1;
            margin-top: clamp(0.6rem, 1.2vw, 1rem);
            font-size: 0.9em;
            background: #f8f9fa;
            padding: clamp(0.5rem, 1vw, 0.85rem);
            border-radius: 6px;
        }
        
        .footer-notes h6 {
            margin-bottom: 0.25rem;
            font-size: 0.95em;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .footer-notes p {
            color: #7f8c8d;
            font-style: italic;
            margin: 0;
        }
        
        .signature-section {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 0.85rem;
            padding-top: 0.45rem;
            border-top: 1px solid #ecf0f1;
        }
        
        .signature-box {
            text-align: center;
            flex: 1 1 10rem;
            max-width: 16rem;
        }
        
        .signature-line {
            border-top: 1.5px solid #2c3e50;
            margin-top: 1.35rem;
            padding-top: 0.25rem;
            font-weight: 600;
            font-size: 0.82em;
            color: #2c3e50;
        }
        
        .footer-text {
            text-align: center;
            margin-top: clamp(0.75rem, 1.5vw, 1.25rem);
            padding-top: 0.5rem;
            border-top: 1px solid #ecf0f1;
            color: #95a5a6;
            font-size: 0.85em;
            font-style: italic;
        }
        
        .btn-print {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 10px 18px;
            font-size: 0.85rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 1000;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.25);
        }

        .btn-whatsapp {
            position: fixed;
            bottom: 30px;
            right: 130px;
            padding: 10px 18px;
            font-size: 0.85rem;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.35);
            z-index: 1000;
            border-radius: 4px;
            transition: all 0.3s ease;
            background: #25D366;
            border-color: #25D366;
            color: #fff;
        }

        .btn-whatsapp:hover {
            background: #1ebe57;
            border-color: #1ebe57;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 211, 102, 0.45);
        }
        
        .btn-back {
            position: fixed;
            bottom: 30px;
            left: 30px;
            padding: 10px 18px;
            font-size: 0.85rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 1000;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.25);
        }

        @media screen and (max-width: 520px) {
            .btn-whatsapp { right: 30px; bottom: 84px; }
        }
        
        .invoice-type-badge {
            text-align: center;
            font-size: 0.78em;
            font-weight: 700;
            color: #1e8449;
            letter-spacing: 0.08em;
            margin: 0 0 0.4rem;
        }

        @media screen and (max-width: 720px) {
            .meta-strip,
            .meta-after {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Include Professional Company Header Component -->
        @include('Components.invoiceHeader')
        
        <div class="meta-strip">
            <div class="meta-cell meta-customer">
                <div class="sale-doc-title">Customer Info</div>
                @php
                    $buyerName = $invoice->customerName;
                    if (($invoice->sale_type ?? 'credit') !== 'cash' && $invoice->customerCode && ! str_contains((string) $buyerName, (string) $invoice->customerCode)) {
                        $buyerName .= ' ('.$invoice->customerCode.')';
                    }
                    $buyerPhone = optional($customer)->custContect ?? '';
                    $buyerAddress = optional($customer)->custAddress ?? '';
                    $buyerCity = $invoice->city ?: optional($customer)->custCity;
                    $addressLine = trim($buyerAddress);
                    if ($buyerCity) {
                        $addressLine = trim($addressLine.' ('.$buyerCity.')');
                    }
                @endphp
                <div class="sale-doc-row">
                    <span class="sale-doc-k">Name : -</span>
                    <span class="sale-doc-v">{{ $buyerName }}@if($buyerPhone) {{ $buyerPhone }}@endif</span>
                </div>
                <div class="sale-doc-row">
                    <span class="sale-doc-k">Address :</span>
                    <span class="sale-doc-v">{{ $addressLine }}</span>
                </div>
            </div>
            
            <div class="meta-cell meta-invoice">
                <div class="sale-doc-title">Sale Invoice</div>
                <div class="sale-doc-row">
                    <span class="sale-doc-k">Invoice</span>
                    <span class="sale-doc-v">{{ $invoice->invoiceNo }}</span>
                </div>
                <div class="sale-doc-row">
                    <span class="sale-doc-k">Date</span>
                    <span class="sale-doc-v">{{ date('d.m.Y', strtotime($invoice->invoiceDate)) }}</span>
                </div>
                @if(!empty($invoice->orderNo))
                <div class="sale-doc-row">
                    <span class="sale-doc-k">Order</span>
                    <span class="sale-doc-v">{{ $invoice->orderNo }}</span>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th>
                        <div class="product-name-line">
                            <span>Product Name</span>
                            <span>Company</span>
                        </div>
                    </th>
                    <th style="width: 14%;">Packing</th>
                    <th class="text-right" style="width: 8%;">Qty</th>
                    <th class="text-right" style="width: 12%;">Rate</th>
                    <th class="text-right" style="width: 12%;">Discount</th>
                    <th class="text-right" style="width: 14%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                @php
                    $product = \App\Models\newProduct::where('productCode', $item->productCode)->first();
                    $packing = $product ? $product->pakingName : 'N/A';
                    $discountAmount = ($item->qty * $item->rate) * ($item->discount / 100);
                    $company = trim((string) optional($product)->companyName);
                    $productLabel = trim((string) $item->productName);
                    if ($company !== '' && str_ends_with($productLabel, ' - '.$company)) {
                        $productLabel = trim(substr($productLabel, 0, -strlen(' - '.$company)));
                    }
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="product-name" title="{{ $productLabel }}{{ $company !== '' ? ' — '.$company : '' }}">
                        <div class="product-name-line">
                            <span class="pn-name">{{ $productLabel }}</span>
                            <span class="pn-company">{{ $company }}</span>
                        </div>
                    </td>
                    <td>{{ $packing }}</td>
                    <td class="text-right">{{ number_format($item->qty) }}</td>
                    <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                    <td class="text-right">{{ number_format($discountAmount, 0) }}</td>
                    <td class="text-right"><strong>{{ number_format($item->amount, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="meta-after">
            <div class="meta-cell meta-delivery">
            @if(($invoice->sale_type ?? 'credit') === 'cash' && $invoice->bankPayments->count() > 0)
                <div class="meta-label">Bank</div>
                @foreach($invoice->bankPayments as $bp)
                    @include('partials.sale.invoice_cash_bank_payment_line', ['payment' => $bp])
                @endforeach
                @else
                <div class="meta-label">Delivery</div>
                <p>Items: {{ $invoice->noOfItems }}</p>
                @if($invoice->gudsName)
                <p title="{{ $invoice->gudsName }}">Transport: {{ $invoice->gudsName }}</p>
                @endif
                @if($invoice->corten > 0)
                <p>Corten: {{ $invoice->corten }}</p>
                @endif
                @endif
            </div>
            
            <div class="meta-cell meta-totals">
                <div class="totals-box">
                    <div class="total-row">
                        <span>Subtotal</span>
                        <strong>{{ number_format($invoice->invoiceTotal, 2) }}</strong>
                    </div>
                    @if($invoice->invoiceDiscount > 0)
                    <div class="total-row">
                        <span>Discount</span>
                        <strong style="color: #c0392b;">- {{ number_format($invoice->invoiceDiscount, 2) }}</strong>
                    </div>
                    @endif
                    <div class="total-row grand-total">
                        <span>Total</span>
                        <strong>Rs. {{ number_format($invoice->grandTotal, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Comments -->
        @if($invoice->comments)
        <div class="footer-notes">
            <h6>📝 NOTES</h6>
            <p>{{ $invoice->comments }}</p>
        </div>
        @endif
                
        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Customer Signature</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Authorized By</div>
            </div>
        </div>
    </div>
    
    <!-- WhatsApp + Print -->
    <button type="button" id="btnWhatsAppInvoice" class="btn btn-whatsapp no-print" title="Send invoice JPG on WhatsApp">
        WhatsApp JPG
    </button>
    <button type="button" onclick="window.print()" class="btn btn-primary btn-print no-print">
        🖨️ Print
    </button>
    
    <!-- Back Button -->
    <a href="{{ ($invoice->sale_type ?? 'credit') === 'cash' ? url('/cash-sale') : route('sale.invoice') }}" class="btn btn-secondary btn-back no-print">
        ← Back
    </a>

    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script>
    (function () {
        const customerPhone = @json((string) ($buyerPhone ?? ''));
        const invoiceNo = @json((string) $invoice->invoiceNo);
        const invoiceDate = @json($invoice->invoiceDate ? \Carbon\Carbon::parse($invoice->invoiceDate)->format('d-M-Y') : '');
        const customerName = @json((string) ($invoice->customerName ?? ''));
        const grandTotal = @json(number_format((float) ($invoice->grandTotal ?? 0), 2));
        const fileName = String(invoiceNo || 'invoice').replace(/[^\w.-]+/g, '_') + '.jpg';

        function normalizeWhatsAppPhone(raw) {
            let digits = String(raw || '').replace(/\D/g, '');
            if (!digits) return '';
            if (digits.length === 11 && digits.startsWith('0')) {
                digits = '92' + digits.slice(1);
            } else if (digits.length === 10 && digits.startsWith('3')) {
                digits = '92' + digits;
            }
            return digits;
        }

        function buildMessage() {
            return [
                '*Trade Gate — Sale Invoice*',
                'Invoice: ' + invoiceNo,
                'Date: ' + invoiceDate,
                'Customer: ' + customerName,
                'Amount: Rs. ' + grandTotal,
                '',
                'Invoice image: ' + fileName,
            ].join('\n');
        }

        function resolvePhone() {
            let phone = normalizeWhatsAppPhone(customerPhone);
            if (phone) return phone;
            const entered = window.prompt('Customer WhatsApp number (e.g. 0300xxxxxxx):', '');
            if (entered === null) return null;
            phone = normalizeWhatsAppPhone(entered);
            if (!phone) {
                alert('Please enter a valid WhatsApp number.');
                return null;
            }
            return phone;
        }

        function canvasToJpegBlob(canvas) {
            return new Promise(function (resolve) {
                canvas.toBlob(function (blob) {
                    resolve(blob);
                }, 'image/jpeg', 0.92);
            });
        }

        function downloadBlob(blob, name) {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = name;
            document.body.appendChild(a);
            a.click();
            a.remove();
            setTimeout(function () { URL.revokeObjectURL(url); }, 2000);
        }

        function openWhatsApp(phone, text) {
            const url = 'https://api.whatsapp.com/send?phone=' + encodeURIComponent(phone)
                + '&text=' + encodeURIComponent(text);
            window.open(url, '_blank');
        }

        async function captureInvoiceJpg() {
            const el = document.querySelector('.invoice-container');
            if (!el || typeof html2canvas !== 'function') {
                throw new Error('Invoice capture not available');
            }
            const canvas = await html2canvas(el, {
                scale: 2,
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff',
                logging: false,
                scrollX: 0,
                scrollY: -window.scrollY,
            });
            const blob = await canvasToJpegBlob(canvas);
            if (!blob) throw new Error('Could not create JPG');
            return blob;
        }

        document.getElementById('btnWhatsAppInvoice').addEventListener('click', async function () {
            const btn = this;
            const phone = resolvePhone();
            if (!phone) return;

            const prevLabel = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Preparing JPG…';

            try {
                const blob = await captureInvoiceJpg();
                const file = new File([blob], fileName, { type: 'image/jpeg' });
                const text = buildMessage();
                const shareData = {
                    files: [file],
                    title: 'Invoice ' + invoiceNo,
                    text: text,
                };

                // Mobile / supporting browsers: share JPG directly (pick WhatsApp)
                if (navigator.canShare && navigator.canShare(shareData)) {
                    try {
                        await navigator.share(shareData);
                        return;
                    } catch (err) {
                        if (err && err.name === 'AbortError') return;
                    }
                }

                // Desktop fallback: download JPG, then open WhatsApp chat
                downloadBlob(blob, fileName);
                openWhatsApp(phone, text);
                alert('Invoice JPG downloaded (' + fileName + ').\n\nWhatsApp me Attach (📎) se ye image bhej dein.');
            } catch (e) {
                console.error(e);
                alert('Could not create invoice JPG. Please try again.');
            } finally {
                btn.disabled = false;
                btn.textContent = prevLabel;
            }
        });
    })();
    </script>
</body>
</html>
