<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Invoice - {{ $invoice->invoiceNo }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.invoice-doc-styles')
</head>
<body>
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
        $saleTypeLabel = (($invoice->sale_type ?? 'credit') === 'cash') ? 'Cash Sale' : 'Credit Sale';
        $isCashBanks = (($invoice->sale_type ?? 'credit') === 'cash') && $invoice->bankPayments->count() > 0;
        $hasDeliveryInfo = $invoice->gudsName || ($invoice->corten > 0);
    @endphp

    <div class="invoice-container">
        @include('components.invoiceHeader')

        <div class="bill-row">
            <div>
                <div class="sec-label">Bill To</div>
                <div class="bill-name">{{ $buyerName }}</div>
                @if($buyerPhone)
                <div class="bill-line">{{ $buyerPhone }}</div>
                @endif
                @if($addressLine !== '')
                <div class="bill-line">{{ $addressLine }}</div>
                @endif
            </div>
            <div class="inv-side">
                <h1 class="inv-title">INVOICE</h1>
                <div class="inv-type">{{ $saleTypeLabel }}</div>
                <div class="inv-meta">
                    <div><span class="k">No.</span><span class="v">{{ $invoice->invoiceNo }}</span></div>
                    <div><span class="k">Date</span><span class="v">{{ date('d.m.Y', strtotime($invoice->invoiceDate)) }}</span></div>
                    @if(!empty($invoice->orderNo))
                    <div><span class="k">Order</span><span class="v">{{ $invoice->orderNo }}</span></div>
                    @endif
                    <div><span class="k">Items</span><span class="v">{{ $invoice->noOfItems }}</span></div>
                </div>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th>
                        <div class="product-name-line">
                            <span>Product</span>
                            <span>Company</span>
                        </div>
                    </th>
                    <th style="width: 13%;">Packing</th>
                    <th class="text-right" style="width: 8%;">Qty</th>
                    <th class="text-right" style="width: 12%;">Rate</th>
                    <th class="text-right" style="width: 11%;">Disc</th>
                    <th class="text-right" style="width: 13%;">Amount</th>
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

        <div class="bottom-row">
            <div class="notes-box">
                @if($isCashBanks)
                    <h6>Payment</h6>
                    @foreach($invoice->bankPayments as $bp)
                        @include('partials.sale.invoice_cash_bank_payment_line', ['payment' => $bp])
                    @endforeach
                @else
                    <h6>Delivery</h6>
                    @if($invoice->gudsName)
                    <p title="{{ $invoice->gudsName }}">Transport: {{ $invoice->gudsName }}</p>
                    @endif
                    @if($invoice->corten > 0)
                    <p>Corten: {{ $invoice->corten }}</p>
                    @endif
                    @unless($hasDeliveryInfo)
                    <p class="muted">—</p>
                    @endunless
                @endif
                @if($invoice->comments)
                    <h6 style="margin-top:0.55rem;">Notes</h6>
                    <p>{{ $invoice->comments }}</p>
                @endif
            </div>
            <div class="totals-box">
                <div class="total-row">
                    <span>Subtotal</span>
                    <strong>{{ number_format($invoice->invoiceTotal, 2) }}</strong>
                </div>
                @if($invoice->invoiceDiscount > 0)
                <div class="total-row">
                    <span>Discount</span>
                    <strong>- {{ number_format($invoice->invoiceDiscount, 2) }}</strong>
                </div>
                @endif
                <div class="total-row grand-total">
                    <span>Total</span>
                    <strong>Rs. {{ number_format($invoice->grandTotal, 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Customer Signature</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Authorized Signature</div>
            </div>
        </div>

        <div class="thanks-line">Thank you for your business</div>
    </div>

    <button type="button" id="btnWhatsAppInvoice" class="btn btn-whatsapp no-print" title="Send invoice JPG on WhatsApp">
        WhatsApp JPG
    </button>
    <a href="{{ route('sale.invoice.pdf', $invoice->invoiceNo) }}" class="btn btn-pdf no-print">
        PDF
    </a>
    <button type="button" onclick="window.print()" class="btn btn-primary btn-print no-print">
        🖨️ Print
    </button>
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
                '*Al Fazal Traders — Sale Invoice*',
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

                if (navigator.canShare && navigator.canShare(shareData)) {
                    try {
                        await navigator.share(shareData);
                        return;
                    } catch (err) {
                        if (err && err.name === 'AbortError') return;
                    }
                }

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
