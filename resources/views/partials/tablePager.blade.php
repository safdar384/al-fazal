@if (isset($pager) && $pager instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $pager->total() > 0)
    <div class="card-footer bg-light no-print d-flex flex-wrap justify-content-between align-items-center gap-2">
        <small class="text-muted">Showing {{ $pager->firstItem() }}–{{ $pager->lastItem() }} of {{ $pager->total() }}</small>
        @if ($pager->hasPages())
            {{ $pager->links('pagination::bootstrap-5') }}
        @endif
    </div>
@endif
