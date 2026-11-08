@php
    $count = $report ? $report['rows']->count() : 0;
    $printHref = ($report && $count > 0)
        ? route('reports.market-voucher.print', [
            'area' => $report['meta']['area_code'],
            'from_date' => $report['meta']['from'],
            'to_date' => $report['meta']['to'],
        ])
        : '#';
@endphp

<div class="card-header mv-card-head py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
  <h5 class="mb-0"><i class="bi bi-table me-2"></i>Market Voucher</h5>
  <div class="d-flex flex-wrap align-items-center gap-2">
    <span class="badge bg-light text-dark" id="mvCustomerCount">{{ $count }} customers</span>
    <a id="btnMvPrint"
       target="_blank"
       rel="noopener"
       class="btn btn-sm btn-outline-light fw-semibold {{ $count > 0 ? '' : 'd-none' }}"
       title="Print Report"
       href="{{ $printHref }}">
      <i class="bi bi-printer me-1"></i>Print Report
    </a>
  </div>
</div>

@if ($report)
  <div class="card-body border-bottom py-2">
    <div class="d-flex flex-wrap gap-2">
      <div class="mv-meta-pill">
        <span class="lbl">Area</span>
        <span class="val">{{ $report['meta']['area_code'] }} — {{ $report['meta']['area_name'] }}</span>
      </div>
      @if ($report['meta']['agent_name'])
        <div class="mv-meta-pill">
          <span class="lbl">Agent</span>
          <span class="val">{{ $report['meta']['agent_name'] }}</span>
        </div>
      @endif
      <div class="mv-meta-pill">
        <span class="lbl">Period</span>
        <span class="val">
          {{ \Carbon\Carbon::parse($report['meta']['from'])->format('d-M-Y') }}
          to
          {{ \Carbon\Carbon::parse($report['meta']['to'])->format('d-M-Y') }}
        </span>
      </div>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      @include('reports.market-voucher.partials.table', ['report' => $report])
    </div>
  </div>
@else
  <div class="card-body">
    <p class="text-muted text-center py-4 mb-0">Select an area and click Generate.</p>
  </div>
@endif
