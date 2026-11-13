<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $config['title'] }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4 landscape; margin: 8mm; }
        @media print {
            .no-print { display: none !important; }
            html, body {
                width: 297mm; margin: 0; padding: 0; background: #fff !important;
                -webkit-print-color-adjust: exact; print-color-adjust: exact;
            }
            .ledger-container {
                box-shadow: none !important; border: none !important; border-radius: 0 !important;
                margin: 0; padding: 0; max-width: 100%;
            }
            .ledger-container, .ledger-container * { color: #000 !important; text-shadow: none !important; }
            .ledger-table thead th { background: #0f2747 !important; color: #fff !important; }
            .ledger-table tbody tr:nth-child(even) td { background: #f4f7fb !important; }
            .ledger-table tfoot td { background: #e8eef5 !important; }
            .meta-bar, .summary-stats, .stat-box, .status-pill, .filter-card {
                -webkit-print-color-adjust: exact; print-color-adjust: exact;
            }
        }
        body {
            background: #e8eef5;
            font-family: 'Segoe UI', 'Helvetica Neue', Tahoma, Geneva, Verdana, sans-serif;
            color: #1a1a1a; padding: 12px;
        }
        .ledger-container {
            width: 100%; max-width: 1120px; margin: 0 auto; background: #fff;
            box-shadow: 0 4px 24px rgba(15, 39, 71, 0.12); border-radius: 4px; padding: 14px 16px 16px;
        }
        .company-header {
            display: flex; justify-content: space-between; align-items: flex-start;
            border-bottom: 2px solid #0f2747; padding-bottom: 8px; margin-bottom: 8px;
        }
        .company-name { font-size: 1.55rem; font-weight: 700; color: #0f2747; letter-spacing: 0.4px; line-height: 1.1; }
        .company-tagline { color: #5a6a7a; font-size: 0.72rem; margin-top: 2px; }
        .company-contact { color: #6b7c8d; font-size: 0.68rem; margin-top: 1px; }
        .company-monogram {
            flex: 0 0 72px; text-align: center; border: 2px solid #0f2747; border-radius: 4px; padding: 4px 6px;
        }
        .monogram-logo { font-size: 1.75rem; font-weight: 800; color: #0f2747; line-height: 1; }
        .monogram-text { font-size: 0.52rem; color: #5a6a7a; font-weight: 700; letter-spacing: 1px; }
        .report-title { text-align: center; margin: 6px 0 8px; }
        .report-title h3 {
            font-size: 1.05rem; font-weight: 700; margin: 0; color: #0f2747;
            letter-spacing: 0.8px; text-transform: uppercase;
        }
        .meta-bar { display: grid; grid-template-columns: 1.2fr 1fr; gap: 8px; margin-bottom: 10px; }
        .meta-box {
            background: #f4f7fb; border: 1px solid #d5dde8; border-left: 3px solid #0f2747;
            border-radius: 3px; padding: 7px 10px;
        }
        .meta-box h6 {
            margin: 0 0 4px; font-size: 0.68rem; font-weight: 700; color: #0f2747;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .meta-grid { display: grid; grid-template-columns: auto 1fr; column-gap: 8px; row-gap: 2px; font-size: 0.72rem; }
        .meta-grid .lbl { color: #6b7c8d; font-weight: 600; }
        .meta-grid .val { color: #1a1a1a; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .filter-card {
            background: #f4f7fb; border: 1px solid #d5dde8; border-radius: 3px; padding: 8px 10px; margin-bottom: 10px;
        }
        .summary-stats { display: flex; gap: 8px; margin-bottom: 10px; }
        .stat-box {
            flex: 1; text-align: center; padding: 8px 6px; background: #f4f7fb;
            border: 1px solid #d5dde8; border-radius: 3px;
        }
        .stat-box.value .stat-value { color: #198754; }
        .stat-label {
            font-size: 0.62rem; color: #6b7c8d; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.4px; margin-bottom: 2px;
        }
        .stat-value { font-size: 0.92rem; font-weight: 700; color: #0f2747; }
        .ledger-table {
            width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 0.68rem; margin-bottom: 10px;
        }
        .ledger-table thead th {
            background: #0f2747; color: #fff; padding: 6px 5px; font-size: 0.62rem; font-weight: 700;
            text-align: center; border: 1px solid #0a1c33; vertical-align: middle; white-space: nowrap;
            text-transform: uppercase; letter-spacing: 0.3px;
        }
        .ledger-table tbody td {
            padding: 4px 5px; border: 1px solid #cfd8e3; vertical-align: middle; height: 26px;
            line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .ledger-table tbody tr:nth-child(even) td { background: #f4f7fb; }
        .ledger-table tfoot td {
            padding: 5px; border: 1px solid #cfd8e3; background: #e8eef5; font-weight: 700; color: #0f2747;
        }
        .ledger-table .text-end { text-align: right !important; }
        .ledger-table .text-center { text-align: center !important; }
        .ledger-table .cell-desc { text-align: left !important; font-weight: 500; }
        .ledger-table .amt { color: #0f2747; font-weight: 700; }
        .ledger-table .amt-debit { color: #dc3545; font-weight: 700; }
        .ledger-table .amt-credit { color: #198754; font-weight: 700; }
        .ledger-table .amt-adv { color: #856404; font-weight: 700; }
        .status-pill {
            display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 0.58rem;
            font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px;
        }
        .st-worker { background: #e7f1ff; color: #084298; }
        .st-ajent { background: #fff3cd; color: #856404; }
        .st-fitter { background: #e8f5e9; color: #198754; }
        .st-adv { background: #fde8ea; color: #842029; margin-left: 4px; }
        .empty-row td {
            text-align: center; color: #6b7c8d; padding: 18px 8px !important; white-space: normal !important;
        }
        .report-footer {
            margin-top: 8px; border-top: 1px solid #d5dde8; padding-top: 6px;
            color: #8a97a6; text-align: center; font-size: 0.65rem;
        }
        .btn-print, .btn-back, .btn-pdf {
            position: fixed; bottom: 24px; padding: 9px 18px; font-size: 0.85rem;
            box-shadow: 0 4px 14px rgba(0,0,0,0.18); z-index: 1000; border-radius: 4px;
        }
        .btn-back { left: 24px; }
        .btn-pdf { right: 140px; }
        .btn-print { right: 24px; background: #0f2747; border-color: #0f2747; }
        .btn-print:hover { background: #1a4a8a; border-color: #1a4a8a; }
        #labourReportLive.is-loading { opacity: 0.45; pointer-events: none; }
    </style>
</head>
<body>
    <div class="ledger-container">
        @include('components.invoiceHeader')

        <div class="report-title">
            <h3>{{ $config['title'] }}</h3>
        </div>

        <div class="filter-card no-print">
            <form method="GET" action="{{ route($config['route']) }}" id="labourReportFilter" class="row g-2 align-items-end">
                @if ($config['show']['dates'] ?? true)
                    <div class="col-md-2">
                        <label class="form-label small mb-1 fw-semibold">From</label>
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1 fw-semibold">To</label>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}" required>
                    </div>
                @endif
                @if ($config['show']['category'] ?? false)
                    <div class="col-md-2">
                        <label class="form-label small mb-1 fw-semibold">Category</label>
                        <select name="category" id="filterCategory" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach ($filterOptions['categories'] as $key => $label)
                                <option value="{{ $key }}" @selected(($filters['category'] ?? '') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if ($config['show']['labour'] ?? false)
                    <div class="col-md-3">
                        <label class="form-label small mb-1 fw-semibold">Labour</label>
                        <select name="labour_code" id="filterLabour" class="form-select form-select-sm">
                            <option value="">All Labour</option>
                            @foreach ($filterOptions['labours'] as $labour)
                                <option value="{{ $labour->labourCode }}"
                                    data-category="{{ str_contains(strtolower((string) ($labour->labourCatagory ?? '')), 'fitter') ? 'fitter' : ((in_array(strtolower(trim((string) ($labour->labourCatagory ?? ''))), ['ajent', 'agent'], true)) ? 'ajent' : 'worker') }}"
                                    @selected(($filters['labour_code'] ?? '') === $labour->labourCode)>
                                    {{ $labour->labourName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-auto">
                    <a href="{{ route($config['route']) }}" id="labourReportReset" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>

        @include('reports.labour.live')
    </div>

    <button type="button" class="btn btn-secondary btn-back no-print" onclick="window.close()">
        <i class="bi bi-x-lg"></i> Close
    </button>
    <a href="{{ route($config['route'], array_merge(request()->except(['fragment']), ['download_pdf' => 1])) }}" id="labourReportPdf" class="btn btn-danger btn-pdf no-print">
        <i class="bi bi-file-earmark-pdf"></i> PDF
    </a>
    <button type="button" class="btn btn-primary btn-print no-print" onclick="window.print()">
        <i class="bi bi-printer"></i> Print
    </button>

    <script>
    (function () {
        var form = document.getElementById('labourReportFilter');
        if (!form) return;
        var requestId = 0;
        var rebuildFns = [];

        function cascadeSelect(parentId, childId, dataAttr, allLabel) {
            var parent = document.getElementById(parentId);
            var child = document.getElementById(childId);
            if (!parent || !child) return;

            var allOptions = Array.prototype.slice.call(child.querySelectorAll('option')).map(function (opt) {
                return {
                    value: opt.value,
                    text: opt.textContent,
                    key: (opt.getAttribute(dataAttr) || '').trim()
                };
            });

            function rebuild(parentValue, keepValue) {
                var current = keepValue !== undefined ? keepValue : child.value;
                child.innerHTML = '';
                allOptions.forEach(function (item) {
                    if (item.value === '') {
                        var allOpt = document.createElement('option');
                        allOpt.value = '';
                        allOpt.textContent = allLabel;
                        child.appendChild(allOpt);
                        return;
                    }
                    if (parentValue && item.key.toUpperCase() !== parentValue.toUpperCase()) return;
                    var opt = document.createElement('option');
                    opt.value = item.value;
                    opt.textContent = item.text;
                    opt.setAttribute(dataAttr, item.key);
                    child.appendChild(opt);
                });
                var stillValid = Array.prototype.some.call(child.options, function (o) {
                    return o.value === current;
                });
                child.value = stillValid ? current : '';
            }

            parent.addEventListener('change', function () {
                rebuild(parent.value.trim(), '');
            });
            rebuildFns.push(function () { rebuild('', ''); });
            if (parent.value) rebuild(parent.value.trim(), child.value);
        }

        @if (($config['show']['category'] ?? false) && ($config['show']['labour'] ?? false))
        cascadeSelect('filterCategory', 'filterLabour', 'data-category', 'All Labour');
        @endif

        function syncPdf(params) {
            var pdf = document.getElementById('labourReportPdf');
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
            var live = document.getElementById('labourReportLive');
            if (live) live.classList.add('is-loading');

            fetch(form.action + '?' + fetchParams.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            }).then(function (res) {
                if (!res.ok) throw new Error('load failed');
                return res.text();
            }).then(function (html) {
                if (id !== requestId) return;
                var current = document.getElementById('labourReportLive');
                if (!current) return;
                current.outerHTML = html.trim();
                history.replaceState(null, '', form.action + (clean ? '?' + clean : ''));
                syncPdf(params);
            }).catch(function () {
                var current = document.getElementById('labourReportLive');
                if (current) current.classList.remove('is-loading');
            });
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            loadFromParams(new URLSearchParams(new FormData(form)));
        });

        form.addEventListener('change', function (event) {
            if (!event.target.matches('select, input[type="date"]')) return;
            loadFromParams(new URLSearchParams(new FormData(form)));
        });

        document.getElementById('labourReportReset')?.addEventListener('click', function (event) {
            event.preventDefault();
            var now = new Date();
            var y = now.getFullYear();
            var m = String(now.getMonth() + 1).padStart(2, '0');
            var d = String(now.getDate()).padStart(2, '0');
            Array.from(form.elements).forEach(function (field) {
                if (!field.name) return;
                if (field.name === 'from_date') { field.value = y + '-' + m + '-01'; return; }
                if (field.name === 'to_date') { field.value = y + '-' + m + '-' + d; return; }
                field.value = '';
            });
            rebuildFns.forEach(function (fn) { fn(); });
            loadFromParams(new URLSearchParams(new FormData(form)));
        });
    })();
    </script>
</body>
</html>
