<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $config['title'] }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @include('reports.profit-loss.partials.navy-styles')
    <style>
        #saleProfitLive.is-loading { opacity: 0.45; pointer-events: none; }
        .stat-box.warn { background: #fdf0f1; border-color: #f1b0b7; }
        .ledger-container { max-width: 1200px; }
    </style>
</head>
<body>
    <div class="ledger-container">
        @include('reports.profit-loss.partials.navy-header', ['title' => $config['title']])

        <div class="filter-card no-print">
            <form method="GET" action="{{ route($config['route']) }}" id="saleProfitFilter" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small mb-1 fw-semibold">From</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1 fw-semibold">To</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1 fw-semibold">Sale Type</label>
                    <select name="sale_type" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="credit" @selected(($filters['sale_type'] ?? '') === 'credit')>Credit</option>
                        <option value="cash" @selected(($filters['sale_type'] ?? '') === 'cash')>Cash</option>
                    </select>
                </div>
                @if ($config['show']['invoice'] ?? false)
                    <div class="col-md-3">
                        <label class="form-label small mb-1 fw-semibold">Invoice</label>
                        <select name="invoice_no" id="filterInvoice" class="form-select form-select-sm">
                            <option value="">All Invoices</option>
                            @foreach ($filterOptions['invoices'] as $invoice)
                                <option value="{{ $invoice->invoiceNo }}" @selected(($filters['invoice_no'] ?? '') === $invoice->invoiceNo)>
                                    {{ $invoice->invoiceNo }} — {{ \Carbon\Carbon::parse($invoice->invoiceDate)->format('d-m-Y') }} ({{ $invoice->customerName }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if ($config['show']['ajent'] ?? false)
                    <div class="col-md-2">
                        <label class="form-label small mb-1 fw-semibold">Ajent</label>
                        <select name="ajent" id="filterAjent" class="form-select form-select-sm">
                            <option value="">All Ajents</option>
                            @foreach ($filterOptions['ajents'] as $ajent)
                                <option value="{{ $ajent }}" @selected(($filters['ajent'] ?? '') === $ajent)>{{ $ajent }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if ($config['show']['area'] ?? false)
                    <div class="col-md-2">
                        <label class="form-label small mb-1 fw-semibold">Area</label>
                        <select name="area" id="filterArea" class="form-select form-select-sm">
                            <option value="">All Areas</option>
                            @foreach ($filterOptions['areas'] as $area)
                                <option value="{{ $area }}" @selected(($filters['area'] ?? '') === $area)>{{ $area }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if ($config['show']['customer'] ?? false)
                    <div class="col-md-3">
                        <label class="form-label small mb-1 fw-semibold">Customer</label>
                        <select name="customer_code" id="filterCustomer" class="form-select form-select-sm">
                            <option value="">All Customers</option>
                            @foreach ($filterOptions['customers'] as $customer)
                                <option value="{{ $customer->custCode }}"
                                    data-area="{{ $customer->custArea ?? '' }}"
                                    @selected(($filters['customer_code'] ?? '') === $customer->custCode)>
                                    {{ $customer->bussnessName ?: $customer->custName }} ({{ $customer->custCode }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-auto">
                    <a href="{{ route($config['route']) }}" id="saleProfitReset" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>

        @include('reports.sale-profit.live')
    </div>

    <button type="button" class="btn btn-secondary btn-back no-print" onclick="window.close()">
        <i class="bi bi-x-lg"></i> Close
    </button>
    <a href="{{ route($config['route'], array_merge(request()->except(['fragment']), ['download_pdf' => 1])) }}" id="saleProfitPdf" class="btn btn-danger btn-pdf no-print">
        <i class="bi bi-file-earmark-pdf"></i> PDF
    </a>
    <button type="button" class="btn btn-primary btn-print no-print" onclick="window.print()">
        <i class="bi bi-printer"></i> Print
    </button>

    <script>
    (function () {
        var form = document.getElementById('saleProfitFilter');
        if (!form) return;
        var requestId = 0;
        var rebuildFns = [];

        function cascadeCustomer() {
            var area = document.getElementById('filterArea');
            var child = document.getElementById('filterCustomer');
            if (!area || !child) return;

            var allOptions = Array.prototype.slice.call(child.querySelectorAll('option')).map(function (opt) {
                return {
                    value: opt.value,
                    text: opt.textContent,
                    area: (opt.getAttribute('data-area') || '').trim()
                };
            });

            function rebuild(keepValue) {
                var areaVal = area.value.trim();
                var current = keepValue !== undefined ? keepValue : child.value;
                child.innerHTML = '';
                allOptions.forEach(function (item) {
                    if (item.value === '') {
                        var allOpt = document.createElement('option');
                        allOpt.value = '';
                        allOpt.textContent = 'All Customers';
                        child.appendChild(allOpt);
                        return;
                    }
                    if (areaVal && item.area.toUpperCase() !== areaVal.toUpperCase()) return;
                    var opt = document.createElement('option');
                    opt.value = item.value;
                    opt.textContent = item.text;
                    opt.setAttribute('data-area', item.area);
                    child.appendChild(opt);
                });
                var stillValid = Array.prototype.some.call(child.options, function (o) {
                    return o.value === current;
                });
                child.value = stillValid ? current : '';
            }

            area.addEventListener('change', function () { rebuild(''); });
            rebuildFns.push(function () { rebuild(''); });
            if (area.value) rebuild(child.value);
        }

        @if (($config['show']['area'] ?? false) && ($config['show']['customer'] ?? false))
        cascadeCustomer();
        @endif

        function syncPdf(params) {
            var pdf = document.getElementById('saleProfitPdf');
            if (!pdf) return;
            var p = new URLSearchParams(params.toString());
            p.delete('fragment');
            p.set('download_pdf', '1');
            pdf.href = form.action + '?' + p.toString();
        }

        function loadFromParams(params) {
            var id = ++requestId;
            params.delete('fragment');
            params.delete('download_pdf');
            var clean = params.toString();
            var fetchParams = new URLSearchParams(clean);
            fetchParams.set('fragment', '1');
            var live = document.getElementById('saleProfitLive');
            if (live) live.classList.add('is-loading');

            fetch(form.action + '?' + fetchParams.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            }).then(function (res) {
                if (!res.ok) throw new Error('load failed');
                return res.text();
            }).then(function (html) {
                if (id !== requestId) return;
                var current = document.getElementById('saleProfitLive');
                if (!current) return;
                current.outerHTML = html.trim();
                history.replaceState(null, '', form.action + (clean ? '?' + clean : ''));
                syncPdf(params);
            }).catch(function () {
                var current = document.getElementById('saleProfitLive');
                if (current) current.classList.remove('is-loading');
            });
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            loadFromParams(new URLSearchParams(new FormData(form)));
        });

        form.addEventListener('change', function () {
            loadFromParams(new URLSearchParams(new FormData(form)));
        });

        document.getElementById('saleProfitReset')?.addEventListener('click', function (event) {
            event.preventDefault();
            var today = new Date();
            var y = today.getFullYear();
            var m = String(today.getMonth() + 1).padStart(2, '0');
            var d = String(today.getDate()).padStart(2, '0');
            form.from_date.value = y + '-' + m + '-01';
            form.to_date.value = y + '-' + m + '-' + d;
            if (form.sale_type) form.sale_type.value = '';
            if (form.area) form.area.value = '';
            if (form.customer_code) form.customer_code.value = '';
            if (form.ajent) form.ajent.value = '';
            if (form.invoice_no) form.invoice_no.value = '';
            rebuildFns.forEach(function (fn) { fn(); });
            loadFromParams(new URLSearchParams(new FormData(form)));
        });
    })();
    </script>
</body>
</html>
