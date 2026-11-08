@extends('layout.app')

@section('title', 'Stock Management')

@section('content')

@include('components.toast-notification')

<style>
  .stock-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
  }
  .stock-toolbar {
    display: flex;
    flex-wrap: nowrap;
    gap: 0.5rem;
    align-items: center;
  }
  .stock-toolbar .form-control { width: min(16rem, 100%); background: #fff; }
  .stock-card-head .stock-toolbar small { color: rgba(255,255,255,.9); }
  #wsTable .stock-actions,
  #psTable .stock-actions { white-space: nowrap; }
  #psTable .ps-qty-input { width: 7.5rem; margin-left: auto; }
</style>

<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-6">
          <h3 class="mb-0"><i class="bi bi-boxes text-primary me-2"></i>Stock Management</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Stock Management</li>
          </ol>
        </div>
      </div>
      <hr class="mb-0" style="border-top: 2px solid #0d6efd;">
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <ul class="nav nav-tabs mb-3" id="stockTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active fw-semibold" id="tab-work-stock" data-bs-toggle="tab"
                  data-bs-target="#pane-work-stock" type="button" role="tab">
            <i class="bi bi-hammer me-1"></i>Work Stock
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link fw-semibold" id="tab-product-stock" data-bs-toggle="tab"
                  data-bs-target="#pane-product-stock" type="button" role="tab">
            <i class="bi bi-box-seam me-1"></i>Product Stock
          </button>
        </li>
      </ul>

      <div class="tab-content">
        {{-- Work Stock --}}
        <div class="tab-pane fade show active" id="pane-work-stock" role="tabpanel">
          <div class="card shadow-sm mb-3">
            <div class="card-header stock-card-head">
              <h5 class="mb-0"><i class="bi bi-plus-circle me-1"></i>Add / Update Work Stock</h5>
            </div>
            <div class="card-body">
              <form id="workStockForm" class="row g-3 align-items-end">
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                  <select class="form-select form-select-sm" id="wsProductCode" required>
                    <option value="">Select Product</option>
                    @foreach($products as $p)
                      <option value="{{ $p->productCode }}" data-unit="{{ \App\Support\FittingWorkStockService::normalizeUnitLabel($p->unitPerPackName) }}">
                        {{ $p->productName }}@if($p->companyName) - {{ $p->companyName }}@endif
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label fw-semibold">Work <span class="text-danger">*</span></label>
                  <input type="text" class="form-control form-control-sm" id="wsWorkName" list="wsWorkNameList"
                         placeholder="e.g. Molding" required maxlength="100">
                  <datalist id="wsWorkNameList">
                    @foreach($workNames as $wn)
                      <option value="{{ $wn }}"></option>
                    @endforeach
                  </datalist>
                </div>
                <div class="col-md-2">
                  <label class="form-label fw-semibold">Qty <span class="text-muted" id="wsUnitHint"></span></label>
                  <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="wsQty" required>
                </div>
                <div class="col-md-3 d-flex gap-2">
                  <button type="submit" class="btn btn-success btn-sm flex-grow-1" id="wsSaveBtn">
                    <i class="bi bi-save me-1"></i>Save
                  </button>
                  <button type="button" class="btn btn-outline-secondary btn-sm" id="wsResetBtn">Reset</button>
                </div>
              </form>
            </div>
          </div>

          <div class="card shadow-sm">
            <div class="card-header stock-card-head d-flex flex-wrap justify-content-between align-items-center gap-2">
              <h5 class="mb-0"><i class="bi bi-list-check me-1"></i>All Work Stock</h5>
              <div class="stock-toolbar">
                <input type="text" class="form-control form-control-sm" id="wsSearch" placeholder="Search product / work...">
                <small id="wsHint">—</small>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-sm table-hover table-striped align-middle mb-0" id="wsTable">
                  <thead class="table-primary">
                    <tr>
                      <th style="width:5%;" class="text-center">#</th>
                      <th>Product</th>
                      <th>Work</th>
                      <th class="text-end" style="width:12%;">Qty</th>
                      <th style="width:10%;">Unit</th>
                      <th class="text-center" style="width:14%;">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr><td colspan="6" class="text-center text-muted py-3">Loading...</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        {{-- Product Stock --}}
        <div class="tab-pane fade" id="pane-product-stock" role="tabpanel">
          <div class="card shadow-sm">
            <div class="card-header stock-card-head d-flex flex-wrap justify-content-between align-items-center gap-2">
              <h5 class="mb-0"><i class="bi bi-box-seam me-1"></i>Product Stock</h5>
              <div class="stock-toolbar">
                <input type="text" class="form-control form-control-sm" id="psSearch" placeholder="Search product...">
                <small id="psHint">—</small>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-sm table-hover table-striped align-middle mb-0" id="psTable">
                  <thead class="table-primary">
                    <tr>
                      <th style="width:5%;" class="text-center">#</th>
                      <th>Product</th>
                      <th>Company</th>
                      <th class="text-end" style="width:14%;">Stock Qty</th>
                      <th style="width:10%;">Unit</th>
                      <th class="text-center" style="width:16%;">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr><td colspan="6" class="text-center text-muted py-3">Loading...</td></tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
(function () {
  let workStocks = @json(($workStocks ?? collect())->values());
  let productStocks = @json($productStocks ?? []);

  const fmtQty = n => Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });

  function csrf() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
  }

  function updateWsUnitHint() {
    const unit = $('#wsProductCode option:selected').data('unit') || '';
    $('#wsUnitHint').text(unit ? `(${unit})` : '');
  }

  function renderWorkStocks(filter) {
    const t = (filter || '').toLowerCase().trim();
    const rows = (workStocks || []).filter(s => {
      if (!t) return true;
      const hay = ((s.productName || '') + ' ' + (s.productCode || '') + ' ' + (s.workName || '')).toLowerCase();
      return hay.includes(t);
    });
    const $tb = $('#wsTable tbody').empty();
    if (!rows.length) {
      $tb.html('<tr><td colspan="6" class="text-center text-muted py-3">No work stock found</td></tr>');
      $('#wsHint').text('0 rows');
      return;
    }
    rows.forEach((s, idx) => {
      $tb.append(`
        <tr>
          <td class="text-center">${idx + 1}</td>
          <td>${s.productName || s.productCode || '-'}</td>
          <td>${s.workName || '-'}</td>
          <td class="text-end fw-semibold text-success">${fmtQty(s.qty)}</td>
          <td>${s.unitName || 'Pieces'}</td>
          <td class="text-center stock-actions">
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-outline-secondary btn-ws-edit"
                data-product="${s.productCode}" data-work="${s.workName}" data-qty="${s.qty}" title="Edit">
                <i class="bi bi-pencil"></i>
              </button>
              <button type="button" class="btn btn-outline-danger btn-ws-delete"
                data-product="${s.productCode}" data-work="${s.workName}" title="Delete">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      `);
    });
    $('#wsHint').text(rows.length + ' row' + (rows.length === 1 ? '' : 's'));
  }

  function renderProductStocks(filter) {
    const t = (filter || '').toLowerCase().trim();
    const rows = (productStocks || []).filter(s => {
      if (!t) return true;
      const hay = ((s.productName || '') + ' ' + (s.productCode || '') + ' ' + (s.companyName || '')).toLowerCase();
      return hay.includes(t);
    });
    const $tb = $('#psTable tbody').empty();
    if (!rows.length) {
      $tb.html('<tr><td colspan="6" class="text-center text-muted py-3">No products found</td></tr>');
      $('#psHint').text('0 rows');
      return;
    }
    rows.forEach((s, idx) => {
      $tb.append(`
        <tr data-code="${s.productCode}">
          <td class="text-center">${idx + 1}</td>
          <td>${s.productName || s.productCode || '-'}</td>
          <td>${s.companyName || '-'}</td>
          <td class="text-end">
            <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end ps-qty-input"
                   value="${Number(s.stockQty || 0).toFixed(2)}">
          </td>
          <td>${s.unitName || 'Pieces'}</td>
          <td class="text-center stock-actions">
            <button type="button" class="btn btn-sm btn-outline-primary btn-ps-save" data-code="${s.productCode}">
              <i class="bi bi-check2"></i> Update
            </button>
          </td>
        </tr>
      `);
    });
    $('#psHint').text(rows.length + ' product' + (rows.length === 1 ? '' : 's'));
  }

  function loadWorkStocks() {
    return $.get(`{{ route('stock-management.workStocks') }}`)
      .done(function (res) {
        workStocks = res.data || [];
        renderWorkStocks($('#wsSearch').val());
      });
  }

  $('#wsProductCode').on('change', updateWsUnitHint);

  $('#wsResetBtn').on('click', function () {
    $('#workStockForm')[0].reset();
    updateWsUnitHint();
  });

  $('#workStockForm').on('submit', function (e) {
    e.preventDefault();
    const productCode = $('#wsProductCode').val();
    const workName = ($('#wsWorkName').val() || '').trim();
    const qty = parseFloat($('#wsQty').val());
    if (!productCode || !workName || !(qty >= 0)) {
      Swal.fire({ icon: 'warning', title: 'Required', text: 'Select product, work and qty' });
      return;
    }
    const $btn = $('#wsSaveBtn').prop('disabled', true);
    csrf();
    $.post(`{{ route('stock-management.workStocks.save') }}`, { productCode, workName, qty })
      .done(function (res) {
        Swal.fire({ icon: 'success', title: 'Saved', text: res.message || 'Saved', timer: 1200, showConfirmButton: false });
        $('#workStockForm')[0].reset();
        updateWsUnitHint();
        loadWorkStocks();
      })
      .fail(function (xhr) {
        Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Could not save' });
      })
      .always(() => $btn.prop('disabled', false));
  });

  $(document).on('click', '.btn-ws-edit', function () {
    $('#wsProductCode').val($(this).data('product'));
    $('#wsWorkName').val($(this).data('work'));
    $('#wsQty').val(Number($(this).data('qty') || 0).toFixed(2));
    updateWsUnitHint();
    $('html, body').animate({ scrollTop: Math.max(0, ($('#workStockForm').offset()?.top || 0) - 80) }, 200);
  });

  $(document).on('click', '.btn-ws-delete', function () {
    const productCode = $(this).data('product');
    const workName = $(this).data('work');
    Swal.fire({
      title: 'Remove Work Stock?',
      text: `${workName} for this product will be removed.`,
      icon: 'warning', showCancelButton: true,
      confirmButtonColor: '#d33', confirmButtonText: 'Yes, remove'
    }).then((result) => {
      if (!result.isConfirmed) return;
      csrf();
      $.ajax({
        url: `{{ route('stock-management.workStocks.destroy') }}`,
        method: 'DELETE',
        data: { productCode, workName }
      }).done(function (res) {
        Swal.fire({ icon: 'success', title: 'Removed', text: res.message || 'Removed', timer: 1200, showConfirmButton: false });
        loadWorkStocks();
      }).fail(function (xhr) {
        Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Failed' });
      });
    });
  });

  $(document).on('click', '.btn-ps-save', function () {
    const productCode = $(this).data('code');
    const $row = $(this).closest('tr');
    const stockQty = parseFloat($row.find('.ps-qty-input').val());
    if (!(stockQty >= 0)) {
      Swal.fire({ icon: 'warning', title: 'Invalid', text: 'Enter a valid stock qty' });
      return;
    }
    const $btn = $(this).prop('disabled', true);
    csrf();
    $.ajax({
      url: `{{ route('stock-management.productStock.update') }}`,
      method: 'PUT',
      data: { productCode, stockQty }
    }).done(function (res) {
      const idx = productStocks.findIndex(p => String(p.productCode) === String(productCode));
      if (idx >= 0) productStocks[idx].stockQty = Number(res.data?.stockQty ?? stockQty);
      Swal.fire({ icon: 'success', title: 'Updated', text: res.message || 'Updated', timer: 1100, showConfirmButton: false });
    }).fail(function (xhr) {
      Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Failed' });
    }).always(() => $btn.prop('disabled', false));
  });

  $('#wsSearch').on('input', function () { renderWorkStocks($(this).val()); });
  $('#psSearch').on('input', function () { renderProductStocks($(this).val()); });

  updateWsUnitHint();
  renderWorkStocks();
  renderProductStocks();
})();
</script>

@endsection
