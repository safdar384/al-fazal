@php
    $fmt = $fmt ?? static fn ($v) => number_format((float) ($v ?? 0), 2);
    $pre = $reconciliation['pre_closing'] ?? null;
@endphp
<div class="card shadow-sm mb-3 border-success">
    <div class="card-header bg-success text-white py-2">
        <h6 class="mb-0"><i class="bi bi-calculator me-1"></i>Balance reconciliation</h6>
    </div>
    <div class="card-body py-3">
        @if($pre)
            <p class="small text-muted mb-3">
                Pre-closing from saved closing
                <a href="{{ route('reports.profit-loss.closing-history.show', $pre->id) }}" class="text-decoration-none" target="_blank">#{{ $pre->id }}</a>
                ({{ $pre->closed_at?->format('d M Y H:i') }})
            </p>
        @else
            <p class="small text-muted mb-3">No earlier closing found — pre-closing balance is treated as zero.</p>
        @endif

        <div class="row g-3 align-items-stretch">
            <div class="col-lg-7">
                <div class="border rounded p-3 h-100 bg-light">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span>Pre Closing Balance</span>
                        <strong class="fs-5">{{ $fmt($reconciliation['pre_closing_balance']) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-success">+ Now Profit (this closing period)</span>
                        <strong class="fs-5 text-success">{{ $fmt($reconciliation['now_profit']) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="fw-semibold">= Total Balance (expected)</span>
                        <strong class="fs-4 text-dark">{{ $fmt($reconciliation['expected_total_balance']) }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="border rounded p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span>All Total Balance (current closing)</span>
                        <strong class="fs-5 text-primary">{{ $fmt($reconciliation['current_all_total_balance']) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="fw-semibold">Difference<br><small class="text-muted fw-normal">Total Balance − Current closing</small></span>
                        @php $diff = (float) ($reconciliation['balance_difference'] ?? 0); @endphp
                        <strong class="fs-4 {{ ($reconciliation['is_balanced'] ?? false) ? 'text-success' : ($diff >= 0 ? 'text-warning' : 'text-danger') }}">
                            {{ $fmt($diff) }}
                        </strong>
                    </div>
                    @if($reconciliation['is_balanced'] ?? false)
                        <div class="alert alert-success py-1 px-2 small mb-0 mt-2">
                            <i class="bi bi-check-circle me-1"></i>Expected total matches current closing balance.
                        </div>
                    @else
                        <div class="alert alert-warning py-1 px-2 small mb-0 mt-2">
                            <i class="bi bi-exclamation-triangle me-1"></i>Variance — check purchases, payments, stock, or period dates between closings.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
