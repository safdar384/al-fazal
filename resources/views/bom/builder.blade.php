@extends('layout.app')
@section('title', 'Edit Product Cost — '.$product->productName)

@push('styles')
<style>
  .pb-card {
    box-shadow: 0 2px 12px rgba(15, 39, 71, 0.08);
    border: 1px solid #d5dde8;
    border-radius: 4px;
    overflow: visible;
  }
  .pb-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
    border: none;
    border-radius: 4px 4px 0 0;
    padding: 0.7rem 1rem;
    font-size: 0.9rem;
    font-weight: 600;
  }
  .pb-summary {
    background: #f0f5fb;
    border: 1px solid #b8c9de;
    border-left: 4px solid #0f2747;
  }
  .pb-summary .pb-card-head { border-radius: 0; }
  .pb-table thead th {
    background: #0f2747;
    color: #fff;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border-color: #0a1c33;
    white-space: nowrap;
    vertical-align: middle;
  }
  .pb-table tbody td {
    font-size: 0.82rem;
    vertical-align: middle;
    border-color: #e8eef5;
  }
  .pb-table tbody tr:nth-child(even) td { background: #f4f7fb; }
  .pb-product-chip {
    display: inline-block;
    background: #e8eef5;
    color: #0f2747;
    font-weight: 700;
    font-size: 0.75rem;
    padding: 0.15rem 0.5rem;
    border-radius: 3px;
  }
  .pb-modal-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
  }
  .bom-material-search, .bom-labour-search { position: relative; min-width: 220px; }
  .bom-material-dropdown, .bom-labour-dropdown {
    display: none; position: absolute; left: 0; right: 0; top: 100%; z-index: 1060;
    max-height: 220px; overflow-y: auto; background: #fff; border: 1px solid #d5dde8;
    border-radius: 4px; box-shadow: 0 6px 18px rgba(15, 39, 71, 0.18);
  }
  .bom-material-dropdown.show, .bom-labour-dropdown.show { display: block; }
  .bom-material-option, .bom-labour-option {
    padding: .45rem .65rem; cursor: pointer; font-size: .85rem; border-bottom: 1px solid #f1f3f5;
  }
  .bom-material-option:hover, .bom-material-option.active,
  .bom-labour-option:hover, .bom-labour-option.active {
    background: #0f2747; color: #fff;
  }
  .bom-material-option:hover .meta, .bom-material-option.active .meta,
  .bom-labour-option:hover .meta, .bom-labour-option.active .meta {
    color: rgba(255,255,255,0.85) !important;
  }
  .bom-material-option .meta, .bom-labour-option .meta { font-size: .75rem; color: #6c757d; }
  .btn-check:checked + .btn-outline-secondary {
    background: #0f2747;
    border-color: #0f2747;
    color: #fff;
  }
  .form-control:focus, .form-select:focus {
    border-color: #1a4a8a;
    box-shadow: 0 0 0 0.2rem rgba(15, 39, 71, 0.12);
  }
</style>
@endpush

@section('content')
@php
    $batchUom = old('batch_uom', $bom->batch_uom ?? 'piece');
    $fmtMoney = fn ($v) => number_format((float) ($v ?? 0), 2, '.', '');
    $fmtQty = fn ($v) => number_format((float) ($v ?? 0), 2, '.', '');
    $materialsForJs = $materials->map(function ($m) {
        return [
            'id' => $m->id,
            'code' => $m->material_code,
            'name' => $m->material_name,
            'packing' => $m->packing,
            'rate' => (float) $m->rate,
        ];
    })->values();
    $labourWorksForJs = $labourWorks->map(function ($w) {
        return [
            'id' => $w->id,
            'name' => $w->work_name,
            'rate' => (float) $w->rate,
        ];
    })->values();
@endphp
<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center gy-2">
        <div class="col-12 col-md-8">
          <h3 class="mb-0 fw-bold" style="color:#0f2747;">
            <i class="bi bi-calculator me-2"></i>Edit Product Cost
          </h3>
          <p class="text-muted small mb-0 mt-1">
            {{ $product->productName }}
            <span class="pb-product-chip ms-1">{{ $product->productCode }}</span>
          </p>
        </div>
        <div class="col-12 col-md-4 text-md-end">
          <ol class="breadcrumb float-md-end mb-2 mb-md-0 justify-content-md-end">
            <li class="breadcrumb-item"><a href="{{ route('bom.index') }}">Product Cost</a></li>
            <li class="breadcrumb-item active">{{ $product->productCode }}</li>
          </ol>
          <a href="{{ route('bom.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
<div class="container-fluid" id="bomBuilderApp">
  <div class="row g-3">
    <div class="col-md-8">
      <div class="card pb-card border-0 mb-3">
        <div class="card-header pb-card-head">Batch Settings</div>
        <div class="card-body">
          <div class="d-flex flex-wrap align-items-center gap-3">
            <input type="hidden" id="batch_size" value="{{ $bom->batch_size ?? 1 }}">
            <input type="hidden" id="wastage_percent" value="{{ $bom->wastage_percent ?? 0 }}">
            <div class="btn-group btn-group-sm" role="group" id="batch_uom_group">
              <input type="radio" class="btn-check" name="batch_uom_radio" id="batch_uom_dozen" value="dozen"
                     @checked($batchUom !== 'gurs')>
              <label class="btn btn-outline-secondary" for="batch_uom_dozen">in dozen</label>
              <input type="radio" class="btn-check" name="batch_uom_radio" id="batch_uom_gurs" value="gurs"
                     @checked($batchUom === 'gurs')>
              <label class="btn btn-outline-secondary" for="batch_uom_gurs">in Gurs</label>
            </div>
            <input type="hidden" id="batch_uom" value="{{ $batchUom === 'gurs' ? 'gurs' : 'dozen' }}">
          </div>
        </div>
      </div>

      <div class="card pb-card border-0 mb-3">
        <div class="card-header pb-card-head d-flex justify-content-between align-items-center">
          <span>Materials</span>
          <button type="button" class="btn btn-sm btn-light fw-semibold" data-bs-toggle="modal" data-bs-target="#newMaterialModal">
            <i class="bi bi-plus-lg me-1"></i>New Material
          </button>
        </div>
        <div class="card-body p-0">
          <table class="table table-sm mb-0 pb-table" id="materialsTable">
            <thead>
              <tr>
                <th style="min-width:220px">Search Material</th>
                <th style="width:100px">Rate</th>
                <th style="width:100px">Per Dozen</th>
                <th style="width:100px">Use Qty</th>
                <th style="width:100px" class="text-end">Total</th>
                <th style="width:80px" class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              @if($bom && $bom->items && $bom->items->count())
                @foreach($bom->items as $it)
                  @php
                    $bomMat = $bomMaterialsByCode[$it->materialCode] ?? null;
                    if ($bomMat) {
                        $rate = $it->unit_rate ?? $bomMat->rate;
                        $label = $bomMat->material_name.' ('.$bomMat->material_code.')';
                    } else {
                        $mat = $it->material;
                        $rate = $it->unit_rate ?? ($mat->costRate ?? 0);
                        $label = $mat ? $mat->productName.' ('.$it->materialCode.')' : $it->materialCode;
                    }
                    $perDozen = $it->per_dozen ?? $it->qty_per_batch ?? 0;
                    $useQty = $it->use_qty ?? 1;
                  @endphp
                  <tr data-material-code="{{ $it->materialCode }}">
                    <td>
                      <div class="bom-material-search">
                        <input type="text" class="form-control form-control-sm mat-search" value="{{ $label }}" placeholder="Search metrial..." autocomplete="off">
                        <input type="hidden" class="mat-code" value="{{ $it->materialCode }}">
                        <div class="bom-material-dropdown"></div>
                      </div>
                    </td>
                    <td><input type="number" step="0.01" min="0" class="form-control form-control-sm mat-rate" value="{{ $fmtMoney($rate) }}"></td>
                    <td><input type="number" step="0.01" min="0" class="form-control form-control-sm mat-per-dozen" value="{{ $fmtQty($perDozen) }}"></td>
                    <td><input type="number" step="0.01" min="0" class="form-control form-control-sm mat-use-qty" value="{{ $fmtQty($useQty) }}"></td>
                    <td class="line-total text-end fw-semibold">0.00</td>
                    <td class="text-center">
                      <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-success btn-add-material-row" title="Add row"><i class="bi bi-plus-lg"></i></button>
                        <button type="button" class="btn btn-danger remove-row" title="Remove row"><i class="bi bi-trash3"></i></button>
                      </div>
                    </td>
                  </tr>
                @endforeach
              @endif
            </tbody>
          </table>
        </div>
      </div>

      <div class="card pb-card border-0 mb-3">
        <div class="card-header pb-card-head d-flex justify-content-between align-items-center">
          <span>Labour</span>
          <button type="button" class="btn btn-sm btn-light fw-semibold" data-bs-toggle="modal" data-bs-target="#newLabourWorkModal">
            <i class="bi bi-plus-lg me-1"></i>New Work
          </button>
        </div>
        <div class="card-body p-0">
          <table class="table table-sm mb-0 pb-table" id="labourTable">
            <thead>
              <tr>
                <th style="min-width:240px">Work</th>
                <th style="width:100px">Qty</th>
                <th style="width:110px">Rate</th>
                <th style="width:110px" class="text-end">Total</th>
                <th style="width:90px" class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              @if($bom && $bom->labors && $bom->labors->count())
                @foreach($bom->labors as $lb)
                  <tr>
                    <td>
                      <div class="bom-labour-search">
                        <input type="text" class="form-control form-control-sm labour-search" value="{{ $lb->operation_name }}" placeholder="Search work..." autocomplete="off">
                        <input type="hidden" class="labour-work-name" value="{{ $lb->operation_name }}">
                        <div class="bom-labour-dropdown"></div>
                      </div>
                    </td>
                    <td><input type="number" step="0.01" min="0" class="form-control form-control-sm labour-qty" value="{{ $fmtQty($lb->time_minutes) }}"></td>
                    <td><input type="number" step="0.01" min="0" class="form-control form-control-sm labour-rate" value="{{ $fmtMoney($lb->rate_per_minute) }}"></td>
                    <td class="labour-total text-end fw-semibold">0.00</td>
                    <td class="text-center">
                      <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-success btn-add-labour-row" title="Add row"><i class="bi bi-plus-lg"></i></button>
                        <button type="button" class="btn btn-danger remove-row" title="Remove row"><i class="bi bi-trash3"></i></button>
                      </div>
                    </td>
                  </tr>
                @endforeach
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card pb-card pb-summary border-0 mb-3 sticky-top" style="top:1rem;">
        <div class="card-header pb-card-head">Cost Summary</div>
        <div class="card-body" id="costSummary">
          <div class="d-flex justify-content-between mb-1 small">
            <span class="text-muted">Material Amount</span>
            <strong style="color:#0f2747;" id="material_cost">0.00</strong>
          </div>
          <div class="d-flex justify-content-between mb-2 small">
            <span class="text-muted">Labour Work Amount</span>
            <strong style="color:#0f2747;" id="labour_cost">0.00</strong>
          </div>
          <div class="border-top pt-2">
            <div class="fw-bold mb-2" style="color:#0f2747;"><span id="cost_per_batch_label">Cost Per Dozen</span></div>
            <div class="ps-1 small">
              <div class="mb-1 d-flex justify-content-between">
                <span class="text-muted">Material Amount</span>
                <span id="cpd_material">0.00</span>
              </div>
              <div class="mb-1 d-flex justify-content-between">
                <span class="text-muted">Labour Work Amount</span>
                <span id="cpd_labour">0.00</span>
              </div>
              <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-1" style="color:#0f2747;">
                <span>Total</span>
                <span id="cpd_total">0.00</span>
              </div>
            </div>
          </div>
          <div class="d-grid mt-3">
            <button type="button" class="btn btn-sm text-white" id="saveCost" style="background:#0f2747;border-color:#0f2747;">
              <i class="bi bi-save me-1"></i>Save Cost
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
  </div>
</main>

<div class="modal fade" id="newMaterialModal" tabindex="-1" aria-labelledby="newMaterialModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header pb-modal-head">
        <h5 class="modal-title" id="newMaterialModalLabel">
          <i class="bi bi-plus-circle me-2"></i>New Material
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="newMaterialForm">
        <div class="modal-body">
          <div class="row g-2 mb-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold" for="newMaterialName">Metrial Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" id="newMaterialName" name="materialName" placeholder="Enter metrial name" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold" for="newMaterialPacking">Packing <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" id="newMaterialPacking" name="packing" placeholder="e.g. Box, Kg" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold" for="newMaterialRate">Rate <span class="text-danger">*</span></label>
              <input type="number" class="form-control form-control-sm" id="newMaterialRate" name="rate" placeholder="0.00" step="0.01" min="0" required>
            </div>
          </div>
          <div id="newMaterialError" class="alert alert-danger d-none mb-2"></div>
          <h6 class="text-muted small mb-2"><i class="bi bi-list-ul me-1"></i>Saved Metrials</h6>
          <div class="table-responsive" style="max-height:280px;">
            <table class="table table-sm table-hover table-bordered mb-0">
              <thead class="table-light sticky-top">
                <tr>
                  <th>Metrial Name</th>
                  <th>Packing</th>
                  <th style="width:110px">Rate</th>
                  <th style="width:70px" class="text-center">Delete</th>
                </tr>
              </thead>
              <tbody id="bomMaterialListBody"></tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn text-white" id="saveNewMaterialBtn" style="background:#0f2747;border-color:#0f2747;">
            <i class="bi bi-save me-1"></i>Save Material
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="newLabourWorkModal" tabindex="-1" aria-labelledby="newLabourWorkModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header pb-modal-head">
        <h5 class="modal-title" id="newLabourWorkModalLabel">
          <i class="bi bi-plus-circle me-2"></i>New Work
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="newLabourWorkForm">
        <div class="modal-body">
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="newLabourWorkName">Work Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control form-control-sm" id="newLabourWorkName" name="workName" placeholder="Enter work name" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="newLabourWorkRate">Rate <span class="text-danger">*</span></label>
              <input type="number" class="form-control form-control-sm" id="newLabourWorkRate" name="rate" placeholder="0.00" step="0.01" min="0" required>
            </div>
          </div>
          <div id="newLabourWorkError" class="alert alert-danger d-none mb-2"></div>
          <h6 class="text-muted small mb-2"><i class="bi bi-list-ul me-1"></i>Saved Labour Works</h6>
          <div class="table-responsive" style="max-height:280px;">
            <table class="table table-sm table-hover table-bordered mb-0">
              <thead class="table-light sticky-top">
                <tr>
                  <th>Work Name</th>
                  <th style="width:110px">Rate</th>
                  <th style="width:70px" class="text-center">Delete</th>
                </tr>
              </thead>
              <tbody id="bomLabourWorkListBody"></tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn text-white" id="saveNewLabourWorkBtn" style="background:#0f2747;border-color:#0f2747;">
            <i class="bi bi-save me-1"></i>Save Work
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const BOM_MATERIALS = @json($materialsForJs);
const BOM_LABOUR_WORKS = @json($labourWorksForJs);
const BOM_PRODUCT_CODE = @json($product->productCode);
const BOM_STORE_URL = @json(route('bom.store'));
const BOM_INDEX_URL = @json(route('bom.index'));
const BOM_MATERIAL_STORE_URL = @json(route('bom.material.store'));
const BOM_MATERIAL_UPDATE_URL = @json(url('/bom/material'));
const BOM_LABOUR_WORK_STORE_URL = @json(route('bom.labour.work.store'));
const BOM_LABOUR_WORK_UPDATE_URL = @json(url('/bom/labour-work'));

function fmt(n) { return (parseFloat(n) || 0).toFixed(2); }

function fmtInput(n, dec) {
  dec = dec === undefined ? 2 : dec;
  const v = parseFloat(n);
  return isNaN(v) ? '' : v.toFixed(dec);
}

function normalizeBuilderNumbers() {
  $('#materialsTable .mat-rate, #labourTable .labour-rate').each(function () {
    const v = parseFloat($(this).val());
    if (!isNaN(v)) $(this).val(v.toFixed(2));
  });
  $('#materialsTable .mat-per-dozen, #materialsTable .mat-use-qty, #labourTable .labour-qty').each(function () {
    const v = parseFloat($(this).val());
    if (!isNaN(v)) $(this).val(v.toFixed(2));
  });
}

function bomCsrfToken() {
  return $('meta[name="csrf-token"]').attr('content') || '';
}

function bomAjaxHeaders() {
  const token = bomCsrfToken();
  return token ? { 'X-CSRF-TOKEN': token } : {};
}

function bomPost(url, data) {
  const payload = Object.assign({ _token: bomCsrfToken() }, data || {});
  return $.ajax({
    url: url,
    method: 'POST',
    headers: bomAjaxHeaders(),
    data: payload,
  });
}

function confirmApplyRateToAll(name, typeLabel) {
  return Swal.fire({
    title: 'Apply Rate?',
    html: 'Do you want to apply this ' + typeLabel + ' rate to all product costs where <strong>' + escapeHtml(name) + '</strong> is used?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#198754',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Yes, apply to all',
    cancelButtonText: '<i class="bi bi-x-circle me-1"></i> No, master only',
    reverseButtons: true,
  });
}

function confirmDeleteBomItem(title, message) {
  return Swal.fire({
    title: title,
    text: message,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Yes, delete',
    cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancel',
    reverseButtons: true,
  });
}

function escapeHtml(text) {
  return String(text ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function renderMaterialModalTable() {
  const $body = $('#bomMaterialListBody');
  if (!BOM_MATERIALS.length) {
    $body.html('<tr><td colspan="4" class="text-muted text-center py-3">No metrial saved yet</td></tr>');
    return;
  }
  let html = '';
  BOM_MATERIALS.forEach(function (m) {
    html += '<tr data-id="' + m.id + '">'
      + '<td>' + escapeHtml(m.name) + '</td>'
      + '<td>' + escapeHtml(m.packing || '—') + '</td>'
      + '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm bom-mat-list-rate" data-id="' + m.id + '" value="' + fmtInput(m.rate) + '"></td>'
      + '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger bom-mat-list-delete" data-id="' + m.id + '" title="Delete"><i class="bi bi-trash3"></i></button></td>'
      + '</tr>';
  });
  $body.html(html);
}

function renderLabourWorkModalTable() {
  const $body = $('#bomLabourWorkListBody');
  if (!BOM_LABOUR_WORKS.length) {
    $body.html('<tr><td colspan="3" class="text-muted text-center py-3">No work saved yet</td></tr>');
    return;
  }
  let html = '';
  BOM_LABOUR_WORKS.forEach(function (w) {
    html += '<tr data-id="' + w.id + '">'
      + '<td>' + escapeHtml(w.name) + '</td>'
      + '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm bom-labour-list-rate" data-id="' + w.id + '" value="' + fmtInput(w.rate) + '"></td>'
      + '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger bom-labour-list-delete" data-id="' + w.id + '" title="Delete"><i class="bi bi-trash3"></i></button></td>'
      + '</tr>';
  });
  $body.html(html);
}

function syncMaterialInGrid(code, rate) {
  $('#materialsTable tbody tr').each(function () {
    if ($('.mat-code', this).val() === code) {
      $('.mat-rate', this).val(rate);
      calcMaterialRow($(this));
    }
  });
  recalc();
}

function removeMaterialFromGrid(code) {
  $('#materialsTable tbody tr').each(function () {
    if ($('.mat-code', this).val() === code) {
      $(this).remove();
    }
  });
  if ($('#materialsTable tbody tr').length === 0) {
    addMaterialRow();
  }
  recalc();
}

function syncLabourInGrid(name, rate) {
  $('#labourTable tbody tr').each(function () {
    const wn = ($('.labour-work-name', this).val() || $('.labour-search', this).val() || '').trim();
    if (wn === name) {
      $('.labour-rate', this).val(rate);
      calcLabourRow($(this));
    }
  });
  recalc();
}

function removeLabourFromGrid(name) {
  $('#labourTable tbody tr').each(function () {
    const wn = ($('.labour-work-name', this).val() || $('.labour-search', this).val() || '').trim();
    if (wn === name) {
      $(this).remove();
    }
  });
  if ($('#labourTable tbody tr').length === 0) {
    addLabourRow();
  }
  recalc();
}

function filterMaterials(term) {
  term = (term || '').toLowerCase().trim();
  if (!term) return BOM_MATERIALS.slice(0, 40);
  return BOM_MATERIALS.filter(function (m) {
    return m.code.toLowerCase().indexOf(term) >= 0
      || m.name.toLowerCase().indexOf(term) >= 0
      || (m.packing && m.packing.toLowerCase().indexOf(term) >= 0);
  }).slice(0, 40);
}

function renderMaterialDropdown($wrap, term) {
  const list = filterMaterials(term);
  const $dd = $wrap.find('.bom-material-dropdown');
  if (!list.length) {
    $dd.html('<div class="bom-material-option text-muted">No material found</div>').addClass('show');
    return;
  }
  let html = '';
  list.forEach(function (m) {
    const label = m.name + ' (' + m.code + ')';
    html += '<div class="bom-material-option" data-code="' + escapeHtml(m.code) + '" data-rate="' + m.rate + '" data-label="' + escapeHtml(label) + '">'
      + '<div>' + escapeHtml(m.name) + ' <span class="text-muted">(' + escapeHtml(m.code) + ')</span></div>'
      + '<div class="meta">Packing: ' + escapeHtml(m.packing || '—') + ' | Rate: ' + fmt(m.rate) + '</div></div>';
  });
  $dd.html(html).addClass('show');
}

function bindMaterialSearch($wrap) {
  const $search = $wrap.find('.mat-search');
  const $code = $wrap.find('.mat-code');
  const $dd = $wrap.find('.bom-material-dropdown');
  const $tr = $wrap.closest('tr');

  $search.on('focus input', function () {
    renderMaterialDropdown($wrap, $search.val());
  });

  $search.on('input', function () {
    if (!$search.val().trim()) {
      $code.val('');
    }
  });

  $dd.on('click', '.bom-material-option[data-code]', function () {
    const code = $(this).attr('data-code') || '';
    const rate = parseFloat($(this).attr('data-rate')) || 0;
    const label = $(this).attr('data-label') || '';
    $code.val(code);
    $search.val(label);
    $tr.find('.mat-rate').val(fmtInput(rate));
    const $perDozen = $tr.find('.mat-per-dozen');
    if (!parseFloat($perDozen.val())) {
      $perDozen.val('12.00');
    }
    $dd.removeClass('show');
    recalc();
  });

  $wrap.on('click.bomMatSearch', function (e) {
    e.stopPropagation();
  });
}

$(document).on('click.bomMatSearchClose', function () {
  $('.bom-material-dropdown.show').removeClass('show');
});

function filterLabourWorks(term) {
  term = (term || '').toLowerCase().trim();
  if (!term) return BOM_LABOUR_WORKS.slice(0, 40);
  return BOM_LABOUR_WORKS.filter(function (w) {
    return w.name.toLowerCase().indexOf(term) >= 0;
  }).slice(0, 40);
}

function renderLabourDropdown($wrap, term) {
  const list = filterLabourWorks(term);
  const $dd = $wrap.find('.bom-labour-dropdown');
  if (!list.length) {
    $dd.html('<div class="bom-labour-option text-muted">No work found</div>').addClass('show');
    return;
  }
  let html = '';
  list.forEach(function (w) {
    html += '<div class="bom-labour-option" data-id="' + w.id + '" data-rate="' + w.rate + '" data-name="' + escapeHtml(w.name) + '">'
      + '<div>' + escapeHtml(w.name) + '</div>'
      + '<div class="meta">Rate: ' + fmt(w.rate) + '</div></div>';
  });
  $dd.html(html).addClass('show');
}

function bindLabourSearch($wrap) {
  const $search = $wrap.find('.labour-search');
  const $name = $wrap.find('.labour-work-name');
  const $dd = $wrap.find('.bom-labour-dropdown');

  $search.on('focus input', function () {
    renderLabourDropdown($wrap, $search.val());
  });

  $search.on('input', function () {
    if (!$search.val().trim()) {
      $name.val('');
    } else {
      $name.val($search.val().trim());
    }
  });

  $dd.on('click', '.bom-labour-option[data-id]', function () {
    const workName = $(this).attr('data-name') || '';
    const rate = parseFloat($(this).attr('data-rate')) || 0;
    $name.val(workName);
    $search.val(workName);
    $wrap.closest('tr').find('.labour-rate').val(fmtInput(rate));
    $dd.removeClass('show');
    recalc();
  });

  $wrap.on('click.bomLabourSearch', function (e) {
    e.stopPropagation();
  });
}

$(document).on('click.bomLabourSearchClose', function () {
  $('.bom-labour-dropdown.show').removeClass('show');
});

function calcMaterialRow($tr) {
  const perDozen = parseFloat($tr.find('.mat-per-dozen').val()) || 0;
  const useQty = parseFloat($tr.find('.mat-use-qty').val()) || 0;
  const rate = parseFloat($tr.find('.mat-rate').val()) || 0;
  const line = perDozen > 0 ? (rate / perDozen) * useQty : 0;
  $tr.find('.line-total').text(fmt(line));
  return line;
}

function calcLabourRow($tr) {
  const qty = parseFloat($tr.find('.labour-qty').val()) || 0;
  const rate = parseFloat($tr.find('.labour-rate').val()) || 0;
  const total = qty * rate;
  $tr.find('.labour-total').text(fmt(total));
  return total;
}

function gatherData() {
  const items = [];
  $('#materialsTable tbody tr').each(function () {
    const code = $('.mat-code', this).val();
    if (!code) return;
    const perDozen = parseFloat($('.mat-per-dozen', this).val()) || 0;
    const useQty = parseFloat($('.mat-use-qty', this).val()) || 0;
    const rate = parseFloat($('.mat-rate', this).val()) || 0;
    items.push({
      materialCode: code,
      per_dozen: perDozen,
      use_qty: useQty,
      qty_per_batch: useQty,
      wastage_percent: 0,
      unit_rate: rate,
    });
  });
  const labors = [];
  $('#labourTable tbody tr').each(function () {
    const op = ($('.labour-work-name', this).val() || $('.labour-search', this).val() || '').trim();
    if (!op) return;
    labors.push({
      operation_name: op,
      time_minutes: parseFloat($('.labour-qty', this).val()) || 0,
      rate_per_minute: parseFloat($('.labour-rate', this).val()) || 0,
    });
  });
  return {
    productCode: BOM_PRODUCT_CODE,
    batch_size: parseFloat($('#batch_size').val()) || 1,
    batch_uom: $('#batch_uom').val() || 'dozen',
    wastage_percent: parseFloat($('#wastage_percent').val()) || 0,
    items: items,
    labors: labors,
    overheads: [],
  };
}

function updateCostSummary(materialAmt, labourAmt) {
  materialAmt = parseFloat(materialAmt) || 0;
  labourAmt = parseFloat(labourAmt) || 0;
  const total = materialAmt + labourAmt;
  $('#material_cost').text(fmt(materialAmt));
  $('#labour_cost').text(fmt(labourAmt));
  $('#cpd_material').text(fmt(materialAmt));
  $('#cpd_labour').text(fmt(labourAmt));
  $('#cpd_total').text(fmt(total));
}

function updateCostPerBatchLabel() {
  const uom = $('#batch_uom').val();
  $('#cost_per_batch_label').text(uom === 'gurs' ? 'Cost Per Gurs' : 'Cost Per Dozen');
}

function recalc() {
  updateCostPerBatchLabel();

  let materialSum = 0;
  $('#materialsTable tbody tr').each(function () { materialSum += calcMaterialRow($(this)); });
  let labourSum = 0;
  $('#labourTable tbody tr').each(function () { labourSum += calcLabourRow($(this)); });
  updateCostSummary(materialSum, labourSum);
}

function addMaterialRow(preset) {
  preset = preset || {};
  const $tr = $('<tr></tr>');
  if (preset.code) $tr.attr('data-material-code', preset.code);
  $tr.html(
    '<td><div class="bom-material-search">'
    + '<input type="text" class="form-control form-control-sm mat-search" placeholder="Search metrial..." autocomplete="off" value="' + (preset.label || '') + '">'
    + '<input type="hidden" class="mat-code" value="' + (preset.code || '') + '">'
    + '<div class="bom-material-dropdown"></div></div></td>'
    + '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm mat-rate" value="' + fmtInput(preset.rate != null ? preset.rate : 0) + '"></td>'
    + '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm mat-per-dozen" value="' + (preset.perDozen != null ? fmtInput(preset.perDozen) : '') + '"></td>'
    + '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm mat-use-qty" value="' + fmtInput(preset.useQty != null ? preset.useQty : 1) + '"></td>'
    + '<td class="line-total text-end fw-semibold">0.00</td>'
    + '<td class="text-center"><div class="btn-group btn-group-sm" role="group">'
    + '<button type="button" class="btn btn-success btn-add-material-row" title="Add row"><i class="bi bi-plus-lg"></i></button>'
    + '<button type="button" class="btn btn-danger remove-row" title="Remove row"><i class="bi bi-trash3"></i></button>'
    + '</div></td>'
  );
  $('#materialsTable tbody').append($tr);
  bindMaterialSearch($tr.find('.bom-material-search'));
  calcMaterialRow($tr);
}

$(document).on('click', '.btn-add-material-row', function () { addMaterialRow(); });

$('input[name="batch_uom_radio"]').on('change', function () {
  $('#batch_uom').val($(this).val());
  updateCostPerBatchLabel();
  recalc();
});
function addLabourRow(preset) {
  preset = preset || {};
  const $tr = $('<tr></tr>');
  $tr.html(
    '<td><div class="bom-labour-search">'
    + '<input type="text" class="form-control form-control-sm labour-search" placeholder="Search work..." autocomplete="off" value="' + (preset.name || '') + '">'
    + '<input type="hidden" class="labour-work-name" value="' + (preset.name || '') + '">'
    + '<div class="bom-labour-dropdown"></div></div></td>'
    + '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm labour-qty" value="' + (preset.qty != null ? fmtInput(preset.qty) : '') + '"></td>'
    + '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm labour-rate" value="' + fmtInput(preset.rate != null ? preset.rate : 0) + '"></td>'
    + '<td class="labour-total text-end fw-semibold">0.00</td>'
    + '<td class="text-center"><div class="btn-group btn-group-sm" role="group">'
    + '<button type="button" class="btn btn-success btn-add-labour-row" title="Add row"><i class="bi bi-plus-lg"></i></button>'
    + '<button type="button" class="btn btn-danger remove-row" title="Remove row"><i class="bi bi-trash3"></i></button>'
    + '</div></td>'
  );
  $('#labourTable tbody').append($tr);
  bindLabourSearch($tr.find('.bom-labour-search'));
  calcLabourRow($tr);
}

$(document).on('click', '.btn-add-labour-row', function () { addLabourRow(); });

$(document).on('click', '.remove-row', function () { $(this).closest('tr').remove(); recalc(); });
$(document).on('input', '#bomBuilderApp input, #bomBuilderApp select', function () { recalc(); });

$('#saveCost').on('click', function () {
  const $btn = $(this).prop('disabled', true);
  bomPost(BOM_STORE_URL, gatherData())
    .done(function () { window.location.href = BOM_INDEX_URL; })
    .fail(function (xhr) { showError('Failed to save cost', xhr.responseJSON?.message || ''); })
    .always(function () { $btn.prop('disabled', false); });
});

$('#newMaterialModal').on('show.bs.modal', renderMaterialModalTable);
$('#newLabourWorkModal').on('show.bs.modal', renderLabourWorkModalTable);

$(document).on('change', '.bom-mat-list-rate', function () {
  const $input = $(this);
  const id = $input.data('id');
  const rate = parseFloat($input.val());
  if (isNaN(rate) || rate < 0) return;
  const item = BOM_MATERIALS.find(function (x) { return x.id === id; });
  if (!item) return;

  confirmApplyRateToAll(item.name, 'metrial').then(function (result) {
    $input.prop('disabled', true);
    $.ajax({
      url: BOM_MATERIAL_UPDATE_URL + '/' + id,
      method: 'PUT',
      headers: bomAjaxHeaders(),
      data: { rate: rate, apply_all: result.isConfirmed ? 1 : 0, _token: bomCsrfToken() },
      success: function (res) {
        const m = res.material;
        const idx = BOM_MATERIALS.findIndex(function (x) { return x.id === m.id; });
        if (idx >= 0) BOM_MATERIALS[idx] = m;
        syncMaterialInGrid(m.code, m.rate);
        if (res.apply_all) {
          showSuccess('Rate updated', 'Applied to ' + (res.affected_products || 0) + ' product cost(s).');
        } else {
          showInfo('Rate saved', 'Master metrial rate updated only.');
        }
      },
      error: function (xhr) {
        showError('Failed to update rate', xhr.responseJSON?.message || '');
        renderMaterialModalTable();
      },
      complete: function () { $input.prop('disabled', false); },
    });
  });
});

$(document).on('click', '.bom-mat-list-delete', function () {
  const id = $(this).data('id');
  const item = BOM_MATERIALS.find(function (x) { return x.id === id; });
  if (!item) return;
  confirmDeleteBomItem('Delete Metrial?', 'Delete metrial "' + item.name + '"?').then(function (result) {
    if (!result.isConfirmed) return;
    $.ajax({
      url: BOM_MATERIAL_UPDATE_URL + '/' + id,
      method: 'DELETE',
      headers: bomAjaxHeaders(),
      data: { _token: bomCsrfToken() },
      success: function () {
        BOM_MATERIALS = BOM_MATERIALS.filter(function (x) { return x.id !== id; });
        removeMaterialFromGrid(item.code);
        renderMaterialModalTable();
        showSuccess('Deleted', 'Metrial removed successfully.');
      },
      error: function (xhr) { showError('Failed to delete', xhr.responseJSON?.message || ''); },
    });
  });
});

$(document).on('change', '.bom-labour-list-rate', function () {
  const $input = $(this);
  const id = $input.data('id');
  const rate = parseFloat($input.val());
  if (isNaN(rate) || rate < 0) return;
  const item = BOM_LABOUR_WORKS.find(function (x) { return x.id === id; });
  if (!item) return;

  confirmApplyRateToAll(item.name, 'work').then(function (result) {
    $input.prop('disabled', true);
    $.ajax({
      url: BOM_LABOUR_WORK_UPDATE_URL + '/' + id,
      method: 'PUT',
      headers: bomAjaxHeaders(),
      data: { rate: rate, apply_all: result.isConfirmed ? 1 : 0, _token: bomCsrfToken() },
      success: function (res) {
        const w = res.work;
        const idx = BOM_LABOUR_WORKS.findIndex(function (x) { return x.id === w.id; });
        if (idx >= 0) BOM_LABOUR_WORKS[idx] = w;
        syncLabourInGrid(w.name, w.rate);
        if (res.apply_all) {
          showSuccess('Rate updated', 'Applied to ' + (res.affected_products || 0) + ' product cost(s).');
        } else {
          showInfo('Rate saved', 'Master work rate updated only.');
        }
      },
      error: function (xhr) {
        showError('Failed to update rate', xhr.responseJSON?.message || '');
        renderLabourWorkModalTable();
      },
      complete: function () { $input.prop('disabled', false); },
    });
  });
});

$(document).on('click', '.bom-labour-list-delete', function () {
  const id = $(this).data('id');
  const item = BOM_LABOUR_WORKS.find(function (x) { return x.id === id; });
  if (!item) return;
  confirmDeleteBomItem('Delete Work?', 'Delete work "' + item.name + '"?').then(function (result) {
    if (!result.isConfirmed) return;
    $.ajax({
      url: BOM_LABOUR_WORK_UPDATE_URL + '/' + id,
      method: 'DELETE',
      headers: bomAjaxHeaders(),
      data: { _token: bomCsrfToken() },
      success: function () {
        BOM_LABOUR_WORKS = BOM_LABOUR_WORKS.filter(function (x) { return x.id !== id; });
        removeLabourFromGrid(item.name);
        renderLabourWorkModalTable();
        showSuccess('Deleted', 'Work removed successfully.');
      },
      error: function (xhr) { showError('Failed to delete', xhr.responseJSON?.message || ''); },
    });
  });
});

$('#newMaterialModal').on('hidden.bs.modal', function () {
  $('#newMaterialForm')[0].reset();
  $('#newMaterialError').addClass('d-none').text('');
});

$('#newMaterialForm').on('submit', function (e) {
  e.preventDefault();
  const name = $('#newMaterialName').val().trim();
  const packing = $('#newMaterialPacking').val().trim();
  const rate = parseFloat($('#newMaterialRate').val());
  if (!name || !packing) return;

  const $btn = $('#saveNewMaterialBtn').prop('disabled', true);
  $.ajax({
    url: BOM_MATERIAL_STORE_URL,
    method: 'POST',
    headers: bomAjaxHeaders(),
    data: { _token: bomCsrfToken(), materialName: name, packing: packing, rate: isNaN(rate) ? 0 : rate },
    success: function (res) {
      BOM_MATERIALS.push(res.material);
      $('#newMaterialForm')[0].reset();
      renderMaterialModalTable();
    },
    error: function (xhr) {
      let msg = xhr.responseJSON?.message || 'Failed to save metrial';
      if (xhr.responseJSON?.errors) {
        msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
      }
      $('#newMaterialError').removeClass('d-none').text(msg);
    },
    complete: function () {
      $btn.prop('disabled', false);
    },
  });
});

$('#newLabourWorkModal').on('hidden.bs.modal', function () {
  $('#newLabourWorkForm')[0].reset();
  $('#newLabourWorkError').addClass('d-none').text('');
});

$('#newLabourWorkForm').on('submit', function (e) {
  e.preventDefault();
  const name = $('#newLabourWorkName').val().trim();
  const rate = parseFloat($('#newLabourWorkRate').val());
  if (!name) return;

  const $btn = $('#saveNewLabourWorkBtn').prop('disabled', true);
  $.ajax({
    url: BOM_LABOUR_WORK_STORE_URL,
    method: 'POST',
    headers: bomAjaxHeaders(),
    data: { _token: bomCsrfToken(), workName: name, rate: isNaN(rate) ? 0 : rate },
    success: function (res) {
      BOM_LABOUR_WORKS.push(res.work);
      $('#newLabourWorkForm')[0].reset();
      renderLabourWorkModalTable();
    },
    error: function (xhr) {
      let msg = xhr.responseJSON?.message || 'Failed to save work';
      if (xhr.responseJSON?.errors) {
        msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
      }
      $('#newLabourWorkError').removeClass('d-none').text(msg);
    },
    complete: function () {
      $btn.prop('disabled', false);
    },
  });
});

$(function () {
  $('#materialsTable tbody .bom-material-search').each(function () { bindMaterialSearch($(this)); });
  $('#labourTable tbody .bom-labour-search').each(function () { bindLabourSearch($(this)); });
  if ($('#materialsTable tbody tr').length === 0) {
    addMaterialRow();
  } else {
    $('#materialsTable tbody tr').each(function () { calcMaterialRow($(this)); });
  }
  if ($('#labourTable tbody tr').length === 0) {
    addLabourRow();
  } else {
    $('#labourTable tbody tr').each(function () { calcLabourRow($(this)); });
  }
  renderMaterialModalTable();
  renderLabourWorkModalTable();
  normalizeBuilderNumbers();
  recalc();
});
</script>
@endpush
