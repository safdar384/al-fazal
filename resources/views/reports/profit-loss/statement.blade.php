<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profit &amp; Loss Statement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @include('reports.profit-loss.partials.navy-styles')
    <style>
        #plStatementLive.is-loading { opacity: 0.45; pointer-events: none; }
        .stat-box.warn { background: #fdf0f1; border-color: #f1b0b7; }
        .stat-box .amt-pos { color: #198754; }
        .stat-box .amt-neg { color: #dc3545; }
        .btn-pdf { display: none; }
    </style>
</head>
<body>
    <div class="ledger-container">
        @include('reports.profit-loss.partials.navy-header', ['title' => 'Profit & Loss Statement'])

        <div class="filter-card no-print">
            <form method="GET" action="{{ route('reports.profit-loss.statement') }}" id="plStatementFilter" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small mb-1 fw-semibold">From</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1 fw-semibold">To</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}" required>
                </div>
                <div class="col-md-auto">
                    <a href="{{ route('reports.profit-loss.statement') }}" id="plStatementReset" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>

        @include('reports.profit-loss.statement-live')
    </div>

    <button type="button" class="btn btn-secondary btn-back no-print" onclick="window.close()">
        <i class="bi bi-x-lg"></i> Close
    </button>
    <button type="button" class="btn btn-primary btn-print no-print" onclick="window.print()">
        <i class="bi bi-printer"></i> Print
    </button>

    <script>
    (function () {
        var form = document.getElementById('plStatementFilter');
        if (!form) return;
        var requestId = 0;

        function loadFromParams(params) {
            var id = ++requestId;
            params.delete('fragment');
            var clean = params.toString();
            var fetchParams = new URLSearchParams(clean);
            fetchParams.set('fragment', '1');
            var live = document.getElementById('plStatementLive');
            if (live) live.classList.add('is-loading');

            fetch(form.action + '?' + fetchParams.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            }).then(function (res) {
                if (!res.ok) throw new Error('load failed');
                return res.text();
            }).then(function (html) {
                if (id !== requestId) return;
                var current = document.getElementById('plStatementLive');
                if (!current) return;
                current.outerHTML = html.trim();
                history.replaceState(null, '', form.action + (clean ? '?' + clean : ''));
            }).catch(function () {
                var current = document.getElementById('plStatementLive');
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

        document.getElementById('plStatementReset')?.addEventListener('click', function (event) {
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
