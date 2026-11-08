<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Closing History Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @include('reports.profit-loss.partials.navy-styles')
    <style>
        #plHistoryLive.is-loading { opacity: 0.45; pointer-events: none; }
        .stat-box.warn { background: #fdf0f1; border-color: #f1b0b7; }
        .ledger-container { max-width: 1280px; }
        .btn-pdf { display: none; }
    </style>
</head>
<body>
    <div class="ledger-container">
        @include('reports.profit-loss.partials.navy-header', ['title' => 'Closing History Report'])

        <div class="filter-card no-print">
            <form method="GET" action="{{ route('reports.profit-loss.closing-history') }}" id="plHistoryFilter" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small mb-1 fw-semibold">From</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}" required>
        </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1 fw-semibold">To</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}" required>
                </div>
                <div class="col-md-auto">
                    <a href="{{ route('reports.profit-loss.closing-history') }}" id="plHistoryReset" class="btn btn-outline-secondary btn-sm">Reset</a>
                    <a href="{{ route('reports.profit-loss.closings', ['from_date' => $fromDate, 'to_date' => $toDate]) }}" class="btn btn-sm text-white" style="background:#0f2747;border-color:#0f2747;" target="_blank" rel="noopener" id="plNewClosingLink">
                        New Closing
                    </a>
                </div>
            </form>
        </div>

        @include('reports.profit-loss.closing-history-live')
    </div>

    <button type="button" class="btn btn-secondary btn-back no-print" onclick="window.close()">
        <i class="bi bi-x-lg"></i> Close
    </button>
    <button type="button" class="btn btn-primary btn-print no-print" onclick="window.print()">
        <i class="bi bi-printer"></i> Print
    </button>

    <script>
    (function () {
        var form = document.getElementById('plHistoryFilter');
        if (!form) return;
        var requestId = 0;
        var closingBase = @json(route('reports.profit-loss.closings'));

        function loadFromParams(params) {
            var id = ++requestId;
            params.delete('fragment');
            var clean = params.toString();
            var fetchParams = new URLSearchParams(clean);
            fetchParams.set('fragment', '1');
            var live = document.getElementById('plHistoryLive');
            if (live) live.classList.add('is-loading');

            fetch(form.action + '?' + fetchParams.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            }).then(function (res) {
                if (!res.ok) throw new Error('load failed');
                return res.text();
            }).then(function (html) {
                if (id !== requestId) return;
                var current = document.getElementById('plHistoryLive');
                if (!current) return;
                current.outerHTML = html.trim();
                history.replaceState(null, '', form.action + (clean ? '?' + clean : ''));
                var link = document.getElementById('plNewClosingLink');
                if (link) link.href = closingBase + '?' + clean;
            }).catch(function () {
                var current = document.getElementById('plHistoryLive');
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

        document.getElementById('plHistoryReset')?.addEventListener('click', function (event) {
            event.preventDefault();
            var today = new Date();
            var y = today.getFullYear();
            var m = String(today.getMonth() + 1).padStart(2, '0');
            var d = String(today.getDate()).padStart(2, '0');
            form.from_date.value = y + '-' + m + '-01';
            form.to_date.value = y + '-' + m + '-' + d;
            loadFromParams(new URLSearchParams(new FormData(form)));
        });
    })();
    </script>
</body>
</html>
