<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Balance Closing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @include('reports.profit-loss.partials.navy-styles')
    <style>
        #plClosingsLive.is-loading { opacity: 0.45; pointer-events: none; }
        .stat-box.warn { background: #fdf0f1; border-color: #f1b0b7; }
        .meta-grid .amt-pos { color: #198754; }
        .meta-grid .amt-neg { color: #dc3545; }
        .btn-pdf { display: none; }
        .close-actions {
            display: flex; gap: 8px; flex-wrap: wrap; align-items: flex-end;
        }
        .close-actions .notes-field { flex: 1; min-width: 180px; }
    </style>
</head>
<body>
    <div class="ledger-container">
        @include('reports.profit-loss.partials.navy-header', ['title' => 'ERP Balance Closing'])

    @if(session('success'))
            <div class="flash-ok no-print">{{ session('success') }}</div>
    @endif

        <div class="filter-card no-print">
            <form method="POST" action="{{ route('reports.profit-loss.closings.store') }}" id="plClosingSaveForm">
                        @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small mb-1 fw-semibold">From</label>
                        <input type="date" name="from_date" id="closingFromDate" class="form-control form-control-sm" value="{{ $fromDate }}" required>
                            </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-1 fw-semibold">To</label>
                        <input type="date" name="to_date" id="closingToDate" class="form-control form-control-sm" value="{{ $toDate }}" required>
                            </div>
                    <div class="col-md-4 notes-field">
                        <label class="form-label small mb-1 fw-semibold">Notes</label>
                                <input type="text" name="notes" class="form-control form-control-sm" maxlength="255" placeholder="Optional">
                            </div>
                    <div class="col-md-auto close-actions">
                        <button type="submit" class="btn btn-sm text-white" style="background:#0f2747;border-color:#0f2747;">
                            <i class="bi bi-check2-circle"></i> Save Closing
                        </button>
                        <a href="{{ route('reports.profit-loss.closing-history', ['from_date' => $fromDate, 'to_date' => $toDate]) }}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener" id="closingHistoryLink">
                            History
                        </a>
                    </div>
                </div>
            </form>
        </div>

        @include('reports.profit-loss.closings-live')
    </div>

    <button type="button" class="btn btn-secondary btn-back no-print" onclick="window.close()">
        <i class="bi bi-x-lg"></i> Close
    </button>
    <button type="button" class="btn btn-primary btn-print no-print" onclick="window.print()">
        <i class="bi bi-printer"></i> Print
    </button>

    <script>
    (function () {
        var fromInput = document.getElementById('closingFromDate');
        var toInput = document.getElementById('closingToDate');
        var historyLink = document.getElementById('closingHistoryLink');
        var previewUrl = @json(route('reports.profit-loss.closings'));
        var historyBase = @json(route('reports.profit-loss.closing-history'));
        var requestId = 0;

        function loadPreview() {
            if (!fromInput || !toInput) return;
            var id = ++requestId;
            var params = new URLSearchParams({
                from_date: fromInput.value,
                to_date: toInput.value,
                fragment: '1'
            });
            var live = document.getElementById('plClosingsLive');
            if (live) live.classList.add('is-loading');

            fetch(previewUrl + '?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            }).then(function (res) {
                if (!res.ok) throw new Error('load failed');
                return res.text();
            }).then(function (html) {
                if (id !== requestId) return;
                var current = document.getElementById('plClosingsLive');
                if (!current) return;
                current.outerHTML = html.trim();
                var clean = new URLSearchParams({
                    from_date: fromInput.value,
                    to_date: toInput.value
                });
                history.replaceState(null, '', previewUrl + '?' + clean.toString());
                if (historyLink) {
                    historyLink.href = historyBase + '?' + clean.toString();
                }
            }).catch(function () {
                var current = document.getElementById('plClosingsLive');
                if (current) current.classList.remove('is-loading');
            });
        }

        if (fromInput) fromInput.addEventListener('change', loadPreview);
        if (toInput) toInput.addEventListener('change', loadPreview);
    })();
    </script>
</body>
</html>
