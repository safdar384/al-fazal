{{-- Expects: $action, $fromDate, $toDate; optional $title --}}

@php
    $showPrintButton = $showPrintButton ?? true;
@endphp

<div class="card shadow-sm mb-3 no-print">
    <div class="card-header bg-primary text-white py-2">
        <h6 class="mb-0"><i class="bi bi-calendar-range me-1"></i> {{ $title ?? 'Date range' }}</h6>
    </div>
    <div class="card-body py-3">
        <form method="GET" action="{{ $action }}" class="row g-2 align-items-end">
            <div class="col-lg-3 col-md-4">
                <label for="from_date" class="form-label small fw-semibold mb-1">From</label>
                <input type="date"
                       name="from_date"
                       id="from_date"
                       class="form-control form-control-sm"
                       value="{{ $fromDate }}"
                       max="{{ date('Y-m-d') }}"
                       required>
            </div>
            <div class="col-lg-3 col-md-4">
                <label for="to_date" class="form-label small fw-semibold mb-1">To</label>
                <input type="date"
                       name="to_date"
                       id="to_date"
                       class="form-control form-control-sm"
                       value="{{ $toDate }}"
                       max="{{ date('Y-m-d') }}"
                       required>
            </div>
            <div class="col-lg-6 col-md-4 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-funnel me-1"></i> Apply
                </button>
                <a href="{{ $action }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> This month
                </a>
                @if ($showPrintButton)
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>
