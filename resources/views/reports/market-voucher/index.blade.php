@extends('layout.app')

@section('title', 'Market Voucher')

@section('content')

<style>
  .mv-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
  }
  .market-voucher-report .mv-report-table {
    font-size: 0.82rem;
  }
  .market-voucher-report .mv-report-table thead th {
    background: #0f2747;
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
    border-color: #0a1c33;
    vertical-align: middle;
  }
  .market-voucher-report .mv-report-table th,
  .market-voucher-report .mv-report-table td {
    padding: 0.35rem 0.45rem;
    vertical-align: middle;
  }
  .market-voucher-report .mv-report-table tbody tr:nth-child(even) {
    background: #f4f7fb;
  }
  .market-voucher-report .mv-report-table tfoot td {
    background: #e8eef5;
    font-weight: 700;
    color: #0f2747;
  }
  .market-voucher-report .mv-manual-cell {
    background: #fafafa;
    min-width: 3.5rem;
    border-bottom: 1px dotted #cfd8e3 !important;
  }
  .mv-meta-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: #f4f7fb;
    border: 1px solid #d5dde8;
    border-radius: 4px;
    padding: 0.3rem 0.65rem;
    font-size: 0.78rem;
    white-space: nowrap;
  }
  .mv-meta-pill .lbl { color: #6b7c8d; font-weight: 600; }
  .mv-meta-pill .val { color: #0f2747; font-weight: 700; }
  .mv-hint {
    font-size: 0.75rem;
    color: #6b7c8d;
  }
</style>

<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-6">
          <h3 class="mb-0">
            <i class="bi bi-receipt-cutoff text-primary me-2"></i>Market Voucher
          </h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('reports.hub') }}">Reports</a></li>
            <li class="breadcrumb-item active" aria-current="page">Market Voucher</li>
          </ol>
        </div>
      </div>
      <hr class="mb-0" style="border-top: 2px solid #0d6efd;">
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid market-voucher-report">
      <p class="mv-hint mb-3">
        Balance = customer ledger balance through end date. Delivery = credit sales in range. Pre Balance = Balance − Delivery.
      </p>

      {{-- Filters --}}
      <div class="card shadow-sm mb-3" id="filterCard">
        <div class="card-header mv-card-head py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
          <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filters</h5>
          <a href="{{ route('reports.hub') }}" class="btn btn-light btn-sm">
            <i class="bi bi-grid me-1"></i>Reports Hub
          </a>
        </div>
        <div class="card-body">
          <form id="mvFilterForm" class="row g-2 align-items-end">
            <div class="col-md-3">
              <label class="form-label small fw-semibold mb-1">
                <i class="bi bi-geo-alt text-primary me-1"></i>Area Name <span class="text-danger">*</span>
              </label>
              <select name="area" id="mvArea" class="form-select form-select-sm" required>
                <option value="">-- Select Area --</option>
                @foreach ($areas as $area)
                  <option value="{{ $area->areaCode }}"
                    data-agent="{{ $area->ajentName }}"
                    @selected($areaCode === $area->areaCode)>
                    {{ $area->areaCode }} — {{ $area->areaName }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-3">
              <label class="form-label small fw-semibold mb-1">
                <i class="bi bi-person-badge text-success me-1"></i>Agent Name
              </label>
              <input type="text"
                     id="mvAgentDisplay"
                     class="form-control form-control-sm bg-light"
                     value="{{ $selectedAgentName }}"
                     placeholder="Select area to show agent"
                     readonly>
            </div>

            <div class="col-md-2">
              <label class="form-label small fw-semibold mb-1">Date From</label>
              <input type="date" name="from_date" id="mvFromDate" class="form-control form-control-sm" value="{{ $fromDate }}" required>
            </div>

            <div class="col-md-2">
              <label class="form-label small fw-semibold mb-1">Date To</label>
              <input type="date" name="to_date" id="mvToDate" class="form-control form-control-sm" value="{{ $toDate }}" required>
            </div>

            <div class="col-md-2">
              <label class="form-label small fw-semibold mb-1 d-none d-md-block">&nbsp;</label>
              <button type="submit" class="btn btn-primary btn-sm w-100" id="btnMvGenerate">
                <i class="bi bi-search me-1"></i>Generate
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- Report panel (AJAX refreshed) --}}
      <div class="card shadow-sm mb-3" id="mvReportPanel">
        @if ($generated && $report)
          @include('reports.market-voucher.partials.report-panel', ['report' => $report])
        @else
          <div class="card-header mv-card-head py-2">
            <h5 class="mb-0"><i class="bi bi-table me-2"></i>Market Voucher</h5>
          </div>
          <div class="card-body text-center text-muted py-5" id="mvEmptyState">
            <i class="bi bi-receipt display-4 d-block mb-2 text-secondary"></i>
            <p class="mb-0">Select area and date range, then click Generate.</p>
          </div>
        @endif
      </div>
    </div>
  </div>
</main>
@endsection

@push('scripts')
<script>
(function () {
  const areaSelect = document.getElementById('mvArea');
  const agentDisplay = document.getElementById('mvAgentDisplay');
  const form = document.getElementById('mvFilterForm');
  const panel = document.getElementById('mvReportPanel');
  const btnGenerate = document.getElementById('btnMvGenerate');
  const dataUrl = @json(route('reports.market-voucher.data'));
  const indexUrl = @json(route('reports.market-voucher'));

  function updateAgentFromArea() {
    if (!areaSelect || !agentDisplay) return;
    const opt = areaSelect.options[areaSelect.selectedIndex];
    const agent = opt?.dataset?.agent || '';
    agentDisplay.value = agent.trim() || '—';
  }

  if (areaSelect) {
    areaSelect.addEventListener('change', updateAgentFromArea);
    updateAgentFromArea();
  }

  if (!form || !panel) return;

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const area = areaSelect?.value || '';
    const fromDate = document.getElementById('mvFromDate')?.value || '';
    const toDate = document.getElementById('mvToDate')?.value || '';

    if (!area) {
      if (typeof showWarning === 'function') showWarning('Please select an area');
      else alert('Please select an area');
      return;
    }
    if (!fromDate || !toDate) {
      if (typeof showWarning === 'function') showWarning('Please select date range');
      else alert('Please select date range');
      return;
    }

    const params = new URLSearchParams({
      area: area,
      from_date: fromDate,
      to_date: toDate
    });

    const originalHtml = btnGenerate.innerHTML;
    btnGenerate.disabled = true;
    btnGenerate.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Loading...';

    fetch(dataUrl + '?' + params.toString(), {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
      .then(function (res) {
        if (!res.ok) throw res;
        return res.json();
      })
      .then(function (data) {
        if (!data.success) {
          throw new Error(data.message || 'Failed to generate');
        }
        panel.innerHTML = data.html;
        // Update URL without reload
        const qs = new URLSearchParams({
          generate: '1',
          area: data.area,
          from_date: data.from_date,
          to_date: data.to_date
        });
        history.replaceState(null, '', indexUrl + '?' + qs.toString());
      })
      .catch(function (err) {
        console.error(err);
        if (typeof showError === 'function') showError('Failed to generate market voucher');
        else alert('Failed to generate market voucher');
      })
      .finally(function () {
        btnGenerate.disabled = false;
        btnGenerate.innerHTML = originalHtml;
      });
  });
})();
</script>
@endpush
