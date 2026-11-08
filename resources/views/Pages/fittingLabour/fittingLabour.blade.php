@extends('layout.app')

@section('title', 'Fitting Labour')

@section('content')

@include('components.toast-notification')

<style>
  .position-relative .dropdown-menu {
    max-height: 300px; overflow-y: auto; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: 1px solid #e0e0e0; border-radius: 8px; margin-top: 4px;
  }
  .position-relative .dropdown-menu .dropdown-item {
    padding: 10px 15px; cursor: pointer; transition: all 0.2s ease; border-left: 3px solid transparent;
  }
  .position-relative .dropdown-menu .dropdown-item:hover,
  .position-relative .dropdown-menu .dropdown-item.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white; border-left-color: #ffc107; transform: translateX(3px);
  }
  .position-relative .dropdown-menu .dropdown-item strong { font-size: 0.95rem; display: block; margin-bottom: 3px; }
  .position-relative .dropdown-menu .dropdown-item .text-muted { font-size: 0.8rem; opacity: 0.8; }
  .position-relative .dropdown-menu .dropdown-item:hover .text-muted,
  .position-relative .dropdown-menu .dropdown-item.active .text-muted { color: rgba(255,255,255,0.9) !important; }
  .add-work-link { font-size: 0.8rem; font-weight: 600; text-decoration: none; }
  .add-work-link:hover { text-decoration: underline; }
</style>

<main class="app-main">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-6">
          <h1 class="mb-0"><i class="bi bi-tools me-2"></i>Fitting Labour</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Fitting Labour</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <div class="card mb-4 shadow-sm">
        <div class="card-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
          <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Record Work / Cash</h5>
        </div>
        <div class="card-body">
          <form id="fittingLabourForm">
            @csrf
            <input type="hidden" id="editTxnId" value="">

            <div class="row g-3 align-items-end mb-2">
              <div class="col-md-5">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <label class="form-label fw-semibold mb-0">
                    <i class="bi bi-person-badge text-primary me-1"></i>Fitter
                  </label>
                  <small id="labourBalanceDisplay" class="text-muted" style="display:none;">
                    Balance: <strong id="labourBalanceValue" class="text-danger">0.00</strong>
                  </small>
                </div>
                <div class="position-relative">
                  <input type="text" class="form-control form-control-sm" id="labourSearch"
                         placeholder="Type to search fitter..." autocomplete="off" required>
                  <input type="hidden" id="labourCodeSubmit" value="">
                  <select class="form-select form-select-sm d-none" id="labourCode">
                    <option value="">Select Fitter</option>
                    @foreach($fitters as $f)
                      <option value="{{ $f->labourCode }}"
                              data-name="{{ $f->labourName }}"
                              data-address="{{ $f->labourAddress ?? '' }}"
                              data-category="{{ $f->labourCatagory ?? '' }}"
                              data-balance="{{ $f->balance ?? 0 }}">
                        {{ $f->labourName }} — {{ $f->labourCode }}
                      </option>
                    @endforeach
                  </select>
                  <div id="labourDropdown" class="dropdown-menu w-100" style="display:none;"></div>
                </div>
              </div>
              <div class="col-md-7">
                <label class="form-label fw-semibold mb-2">
                  <i class="bi bi-geo-alt text-danger me-1"></i>Address
                </label>
                <div id="labourAddressDisplay" class="form-control form-control-sm bg-light text-muted" style="min-height:31px;">
                  Select a fitter to view address
                </div>
              </div>
            </div>

            <div class="row g-3 align-items-end">
              <div class="col-md-2">
                <label class="form-label fw-semibold"><i class="bi bi-arrow-left-right text-warning me-1"></i>Type</label>
                <select class="form-select form-select-sm" id="txnType" required>
                  <option value="Work" selected>Work</option>
                  <option value="Cash">Cash</option>
                </select>
              </div>

              <div class="col-md-3" id="productFieldWrap">
                <label class="form-label fw-semibold">
                  <i class="bi bi-box-seam text-success me-1"></i>Product
                </label>
                <div class="position-relative">
                  <input type="text" class="form-control form-control-sm" id="productSearch"
                         placeholder="Type to search product..." autocomplete="off">
                  <input type="hidden" id="productCodeSubmit" value="">
                  <input type="hidden" id="productNameHidden" value="">
                  <div id="productDropdown" class="dropdown-menu w-100" style="display:none;"></div>
                </div>
                <div id="variantWrap" class="mt-1 d-none">
                  <select class="form-select form-select-sm" id="variantSelect">
                    <option value="">Select Variant</option>
                  </select>
                </div>
              </div>

              <div class="col-md-2" id="workFieldWrap">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="form-label fw-semibold mb-0">
                    <i class="bi bi-hammer text-info me-1"></i>Work
                  </label>
                  <a href="javascript:void(0);" class="add-work-link text-primary" id="addWorkLink">
                    <i class="bi bi-plus-circle me-1"></i>Add Work
                  </a>
                </div>
                <select class="form-select form-select-sm" id="workSelect">
                  <option value="">Select Work</option>
                  @foreach(($allWorkNames ?? []) as $wn)
                    <option value="{{ $wn }}">{{ $wn }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-2 d-none" id="entryTypeFieldWrap">
                <label class="form-label fw-semibold"><i class="bi bi-arrow-down-up text-warning me-1"></i>Entry Type</label>
                <select class="form-select form-select-sm" id="cashEntryType">
                  <option value="pay" selected>Pay</option>
                  <option value="receive">Receive</option>
                </select>
              </div>

              <div class="col-md-3 d-none" id="descriptionFieldWrap">
                <label class="form-label fw-semibold"><i class="bi bi-chat-left-text text-secondary me-1"></i>Description</label>
                <input type="text" class="form-control form-control-sm" id="cashDescription"
                       placeholder="Cash payment details..." maxlength="255">
              </div>

              <div class="col-md-2" id="rateFieldWrap">
                <label class="form-label fw-semibold"><i class="bi bi-currency-dollar text-success me-1"></i>Rate</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text">Rs.</span>
                  <input type="number" step="0.01" min="0" class="form-control" id="rate" placeholder="0.00">
                </div>
              </div>
              <div class="col-md-1" id="qtyFieldWrap">
                <label class="form-label fw-semibold">
                  <i class="bi bi-123 text-info me-1"></i>Qty
                  <small class="text-muted fw-normal" id="qtyUnitLabel"></small>
                </label>
                <input type="number" step="0.01" min="0.01" class="form-control form-control-sm" id="qty" placeholder="0">
              </div>
              <div class="col-md-2" id="amountFieldWrap">
                <label class="form-label fw-semibold"><i class="bi bi-cash-coin text-primary me-1"></i>Amount</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text">Rs.</span>
                  <input type="number" step="0.01" min="0" class="form-control bg-light" id="amount" placeholder="0.00" readonly>
                </div>
              </div>
              <div class="col-md-2">
                <div class="d-flex gap-1">
                  <button type="submit" class="btn btn-primary flex-grow-1" style="font-weight:600;" id="btnSave">
                    <i class="bi bi-check2-circle me-1"></i><span id="btnSaveLabel">Save</span>
                  </button>
                  <button type="button" class="btn btn-outline-secondary d-none" id="btnCancelEdit" title="Cancel edit">
                    <i class="bi bi-x-lg"></i>
                  </button>
                </div>
                <small class="text-warning d-none" id="editModeHint"></small>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="bi bi-table text-primary me-2"></i>Transaction Entries</h5>
          <small class="text-muted" id="entriesHint">Select a fitter to view entries</small>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover table-bordered align-middle mb-0" id="entriesTable">
              <thead class="table-secondary">
                <tr>
                  <th style="width:4%;" class="text-center">#</th>
                  <th style="width:9%;">Date</th>
                  <th style="width:10%;">Txn No</th>
                  <th style="width:8%;">Type</th>
                  <th>Product / Description</th>
                  <th class="text-end" style="width:7%;">Qty</th>
                  <th class="text-end" style="width:8%;">Rate (Rs.)</th>
                  <th class="text-end" style="width:10%;">Credit</th>
                  <th class="text-end" style="width:10%;">Debit</th>
                  <th class="text-end" style="width:11%;">Balance</th>
                  <th class="text-center" style="width:10%;">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr><td colspan="11" class="text-center text-muted py-4">Select a fitter to view entries</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

{{-- Add Work Modal --}}
<div class="modal fade" id="addWorkModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <h5 class="modal-title"><i class="bi bi-hammer me-2"></i>Add Work</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addWorkForm">
          <input type="hidden" id="awEditId" value="">
          <div class="mb-2">
            <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
            <select class="form-select form-select-sm" id="awProductCode" required>
              <option value="">Select Product</option>
              @foreach($products as $p)
                <option value="{{ $p->productCode }}">
                  {{ $p->productName }}@if($p->companyName) - {{ $p->companyName }}@endif
                </option>
              @endforeach
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label fw-semibold">Work <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm" id="awWorkName" list="awWorkNameList"
                   placeholder="e.g. Pin Fitting" required maxlength="100">
            <datalist id="awWorkNameList">
              @foreach(($workNameSuggestions ?? []) as $wn)
                <option value="{{ $wn }}"></option>
              @endforeach
            </datalist>
          </div>
          <div class="mb-2">
            <label class="form-label fw-semibold">Rate <span class="text-danger">*</span></label>
            <div class="input-group input-group-sm">
              <span class="input-group-text">Rs.</span>
              <input type="number" step="0.01" min="0" class="form-control" id="awRate" required>
            </div>
          </div>
          <div class="d-flex gap-2 justify-content-end mb-3">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="awFormResetBtn">Reset</button>
            <button type="submit" class="btn btn-success btn-sm" id="awSaveBtn">
              <i class="bi bi-save me-1"></i>Save Work
            </button>
          </div>
        </form>

        <hr class="my-2">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0"><i class="bi bi-list-check me-1"></i>All Works</h6>
          <small class="text-muted" id="awWorksHint">—</small>
        </div>
        <div class="table-responsive">
          <table class="table table-sm table-hover table-bordered align-middle mb-0" id="awWorksTable">
            <thead class="table-secondary">
              <tr>
                <th style="width:5%;" class="text-center">#</th>
                <th>Product</th>
                <th>Work</th>
                <th class="text-end" style="width:14%;">Rate (Rs.)</th>
                <th class="text-center" style="width:16%;">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr><td colspan="5" class="text-center text-muted py-3">No works saved</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const productVariants = @json($productVariants ?? []);
  let productList = @json($productListJson ?? []);
  let workRatesCache = @json($workRatesJson ?? []);
  let allWorkNames = @json($allWorkNames ?? []);
  let entriesCache = [];
  let editingId = null;
  let selectContextXhr = null;

  const fmt = n => Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  function calcAmount() {
    const rate = parseFloat($('#rate').val()) || 0;
    const qty = parseFloat($('#qty').val()) || 0;
    $('#amount').val((rate * qty).toFixed(2));
  }

  function showAddress($opt) {
    const addr = ($opt.data('address') || '').toString().trim();
    const $box = $('#labourAddressDisplay');
    if (!addr) {
      $box.removeClass('text-dark').addClass('text-muted').text('No address on file');
      return;
    }
    $box.removeClass('text-muted').addClass('text-dark').text(addr);
  }

  function filterLabours(term) {
    const t = (term || '').toLowerCase().trim();
    const list = [];
    $('#labourCode option').each(function () {
      const code = $(this).val();
      if (!code) return;
      const name = ($(this).data('name') || '').toString();
      const hay = (name + ' ' + code).toLowerCase();
      if (!t || hay.includes(t)) list.push({ code, name });
    });
    return list;
  }

  function renderLabourDropdown(list) {
    const $dd = $('#labourDropdown').empty();
    if (!list.length) {
      $dd.html('<div class="dropdown-item text-muted">No fitters found</div>').show();
      return;
    }
    list.slice(0, 15).forEach((c, idx) => {
      $dd.append(`
        <button type="button" class="dropdown-item ${idx === 0 ? 'active' : ''}" data-code="${c.code}">
          <strong>${c.name}</strong>
          <div class="text-muted">Code: ${c.code}</div>
        </button>
      `);
    });
    $dd.show();
  }

  function selectLabour(code) {
    const $opt = $('#labourCode option[value="' + code + '"]');
    if (!$opt.length) return;
    $('#labourCode').val(code);
    $('#labourCodeSubmit').val(code);
    $('#labourSearch').val($opt.data('name') || code);
    showAddress($opt);
    $('#labourDropdown').hide();
    clearProduct();
    loadContext();
  }

  function applyBalance(res) {
    if (res == null || res.balance === undefined) {
      $('#labourBalanceDisplay').hide();
      return;
    }
    $('#labourBalanceValue').text(fmt(res.balance));
    $('#labourBalanceDisplay').show();
  }

  function loadContext() {
    const labourCode = $('#labourCodeSubmit').val();
    const $tb = $('#entriesTable tbody');
    if (!labourCode) {
      $('#entriesHint').text('Select a fitter to view entries');
      $tb.html('<tr><td colspan="11" class="text-center text-muted py-4">Select a fitter to view entries</td></tr>');
      $('#labourBalanceDisplay').hide();
      return;
    }
    $('#entriesHint').text('Loading...');
    if (selectContextXhr && selectContextXhr.readyState !== 4) selectContextXhr.abort();
    selectContextXhr = $.get(`{{ route('fitting-labour.selectContext') }}`, { labourCode })
      .done(function (res) {
        applyBalance(res);
        renderEntries(res.entries || [], res);
        const n = (res.entries || []).length;
        $('#entriesHint').text(n + ' entr' + (n === 1 ? 'y' : 'ies') + ' | Balance: ' + fmt(res.closing_balance || 0));
      })
      .fail(function (xhr) {
        if (xhr.statusText === 'abort') return;
        $tb.html('<tr><td colspan="11" class="text-center text-danger py-4">Failed to load</td></tr>');
        $('#entriesHint').text('Failed to load');
      });
  }

  function setEditMode(on, txnNo) {
    if (on) {
      $('#btnSaveLabel').text('Update');
      $('#btnCancelEdit').removeClass('d-none');
      $('#editModeHint').removeClass('d-none').text('Editing ' + (txnNo || ''));
    } else {
      editingId = null;
      $('#editTxnId').val('');
      $('#btnSaveLabel').text('Save');
      $('#btnCancelEdit').addClass('d-none');
      $('#editModeHint').addClass('d-none').text('');
    }
  }

  function cancelEdit() {
    setEditMode(false);
    clearProduct();
    clearWorkSelection();
    $('#cashDescription').val('');
    $('#cashEntryType').val('pay');
    $('#rate, #qty, #amount').val('');
    applyTypeUi($('#txnType').val(), { reset: false });
  }

  function isCashType(type) {
    return type === 'Cash';
  }

  function applyTypeUi(type, { reset = true } = {}) {
    const isCash = isCashType(type);
    $('#productFieldWrap, #workFieldWrap').toggleClass('d-none', isCash);
    $('#descriptionFieldWrap, #entryTypeFieldWrap').toggleClass('d-none', !isCash);
    $('#rateFieldWrap, #qtyFieldWrap').toggleClass('d-none', isCash);
    if (isCash) {
      $('#amount').prop('readonly', false).removeClass('bg-light');
      if (reset) {
        clearProduct();
        clearWorkSelection();
        $('#cashDescription').val('');
        $('#cashEntryType').val('pay');
        $('#amount').val('');
      }
    } else {
      $('#amount').prop('readonly', true).addClass('bg-light');
      if (reset) {
        $('#cashDescription').val('');
        calcAmount();
      }
    }
  }

  function rateForWork(workName, productCode) {
    const name = (workName || '').trim().toLowerCase();
    if (!name) return null;
    const rows = (workRatesCache || []).filter(w => String(w.workName || '').toLowerCase() === name);
    if (!rows.length) return null;
    if (productCode) {
      const matched = rows.find(w => String(w.productCode) === String(productCode));
      if (matched) return Number(matched.rate || 0);
    }
    return Number(rows[0].rate || 0);
  }

  function fillWorkSelect(selectedName) {
    const current = selectedName || $('#workSelect').val() || '';
    const productCode = $('#productCodeSubmit').val();
    const $w = $('#workSelect').empty().append('<option value="">Select Work</option>');
    (allWorkNames || []).forEach(name => {
      if (!name) return;
      const rate = rateForWork(name, productCode);
      const label = rate != null ? `${name} (Rs. ${fmt(rate)})` : name;
      const sel = current && String(current).toLowerCase() === String(name).toLowerCase() ? ' selected' : '';
      $w.append(`<option value="${name}" data-rate="${rate != null ? rate : ''}"${sel}>${label}</option>`);
    });
  }

  function tryApplyMatchedRate() {
    const productCode = $('#productCodeSubmit').val();
    const workName = ($('#workSelect').val() || '').trim();
    if (!workName) return;
    const rate = rateForWork(workName, productCode);
    if (rate == null) return;
    $('#rate').val(Number(rate).toFixed(2));
    if (!$('#qty').val()) $('#qty').val('1');
    calcAmount();
  }

  function unitForProduct(productCode) {
    const p = productList.find(x => String(x.productCode) === String(productCode));
    return (p && p.unitName) ? p.unitName : 'Pieces';
  }

  function updateQtyUnitLabel(productCode) {
    const unit = productCode ? unitForProduct(productCode) : '';
    $('#qtyUnitLabel').text(unit ? `(${unit})` : '');
  }

  function refreshWorksFromServer(selectedName) {
    return $.get(`{{ route('fitting-labour.works') }}`)
      .done(function (res) {
        workRatesCache = res.data || [];
        const names = [];
        const seen = {};
        workRatesCache.forEach(w => {
          const n = w.workName || '';
          if (n && !seen[n.toLowerCase()]) {
            seen[n.toLowerCase()] = true;
            names.push(n);
          }
        });
        names.sort((a, b) => a.localeCompare(b));
        allWorkNames = names;
        fillWorkSelect(selectedName || '');
        tryApplyMatchedRate();
      });
  }

  function clearProduct() {
    $('#productSearch').val('');
    $('#productCodeSubmit').val('');
    $('#productNameHidden').val('');
    $('#productDropdown').hide().empty();
    $('#variantWrap').addClass('d-none');
    $('#variantSelect').html('<option value="">Select Variant</option>');
    updateQtyUnitLabel('');
  }

  function clearWorkSelection() {
    $('#workSelect').val('');
  }

  function fillVariants(code) {
    const variants = productVariants[code] || [];
    const $v = $('#variantSelect').empty().append('<option value="">Select Variant</option>');
    if (!variants.length) {
      $('#variantWrap').addClass('d-none');
      return;
    }
    variants.forEach(v => {
      $v.append(`<option value="${v.id}" data-name="${v.name || ''}" data-value="${v.value || ''}">${v.name || ''} ${v.value || ''}</option>`);
    });
    $('#variantWrap').removeClass('d-none');
  }

  function filterProducts(term) {
    const t = (term || '').toLowerCase().trim();
    if (!t) return productList.slice(0, 15);
    return productList.filter(p => {
      const hay = ((p.productName || '') + ' ' + (p.companyName || '') + ' ' + (p.productCode || '')).toLowerCase();
      return hay.includes(t);
    }).slice(0, 15);
  }

  function renderProductDropdown(list) {
    const $dd = $('#productDropdown').empty();
    if (!list.length) {
      $dd.html('<div class="dropdown-item text-muted">No products</div>').show();
      return;
    }
    list.forEach((p, idx) => {
      $dd.append(`
        <button type="button" class="dropdown-item ${idx === 0 ? 'active' : ''}" data-code="${p.productCode}">
          <strong>${p.productName}</strong>
          <div class="text-muted">${p.companyName || ''} | ${p.productCode || ''}</div>
        </button>
      `);
    });
    $dd.show();
  }

  function selectProduct(code) {
    const p = productList.find(x => x.productCode === code);
    if (!p) return;
    const keepWork = $('#workSelect').val() || '';
    $('#productCodeSubmit').val(code);
    $('#productNameHidden').val(p.productName || '');
    $('#productSearch').val(p.productName + (p.companyName ? ' - ' + p.companyName : ''));
    $('#productDropdown').hide();
    fillVariants(code);
    fillWorkSelect(keepWork);
    updateQtyUnitLabel(code);
    if (!$('#qty').val()) $('#qty').val('1');
    tryApplyMatchedRate();
    calcAmount();
  }

  function beginEdit(e) {
    editingId = e.id;
    $('#editTxnId').val(e.id);
    setEditMode(true, e.txnNo);
    let type = e.type || 'Work';
    if (type === 'Sale' || type === 'Purchase') type = 'Work';
    $('#txnType').val(type);
    applyTypeUi(type, { reset: false });

    if (type === 'Cash') {
      clearProduct();
      const et = (e.entryType || 'pay').toLowerCase();
      $('#cashEntryType').val(et === 'receive' ? 'receive' : 'pay');
      $('#cashDescription').val(e.productName || e.remarks || '');
      $('#amount').val(Number(e.amount || 0).toFixed(2));
    } else {
      $('#cashDescription').val('');
      const code = e.productCode || '';
      const p = productList.find(x => x.productCode === code);
      $('#productCodeSubmit').val(code);
      $('#productNameHidden').val(e.productName || '');
      $('#productSearch').val((e.productName || code) + (p && p.companyName ? ' - ' + p.companyName : ''));
      $('#rate').val(Number(e.rate || 0).toFixed(2));
      $('#qty').val(e.qty || '');
      calcAmount();
      fillVariants(code);
      fillWorkSelect(e.workName || '');
      updateQtyUnitLabel(code);
      if (e.variantId) {
        setTimeout(() => {
          $('#variantSelect').val(String(e.variantId));
          if (!$('#variantSelect').val()) {
            $('#variantSelect').append(
              `<option value="${e.variantId}" data-name="${e.variantName || ''}" data-value="${e.variantValue || ''}" selected>${e.variantName || ''} ${e.variantValue || ''}</option>`
            );
          }
        }, 30);
      }
    }
    $('html, body').animate({ scrollTop: Math.max(0, ($('#fittingLabourForm').offset()?.top || 0) - 80) }, 200);
  }

  function renderEntries(entries, meta) {
    const $tb = $('#entriesTable tbody').empty();
    entriesCache = entries || [];
    if (!entries.length) {
      $tb.html('<tr><td colspan="11" class="text-center text-muted py-4">No entries for this fitter</td></tr>');
      return;
    }
    let totalDebit = 0, totalCredit = 0;
    entries.forEach((e, idx) => {
      let typeBadge = '<span class="badge text-bg-secondary">' + (e.type || '') + '</span>';
      if (e.type === 'Work' || e.type === 'Sale' || e.type === 'Purchase') {
        typeBadge = '<span class="badge text-bg-success">Work</span>';
      } else if (e.type === 'Cash') {
        const et = (e.entryType || 'pay').toLowerCase();
        typeBadge = '<span class="badge text-bg-info">Cash</span> '
          + (et === 'receive'
            ? '<span class="badge text-bg-success">Receive</span>'
            : '<span class="badge text-bg-danger">Pay</span>');
      }
      const productLabel = e.type === 'Cash'
        ? (e.productName || e.remarks || '-')
        : ((e.productName || e.productCode || '-')
          + (e.workName ? ` <small class="text-info">/ ${e.workName}</small>` : '')
          + (e.variant ? ` <small class="text-muted">(${e.variant})</small>` : ''));
      const debit = parseFloat(e.debit) || 0;
      const credit = parseFloat(e.credit) || 0;
      totalDebit += debit;
      totalCredit += credit;
      $tb.append(`
        <tr>
          <td class="text-center">${idx + 1}</td>
          <td>${e.date || ''}</td>
          <td><code>${e.txnNo || ''}</code></td>
          <td>${typeBadge}</td>
          <td>${productLabel}</td>
          <td class="text-end">${e.type === 'Cash' ? '-' : fmt(e.qty)}</td>
          <td class="text-end">${e.type === 'Cash' ? '-' : ('Rs. ' + fmt(e.rate))}</td>
          <td class="text-end text-success">${credit > 0 ? fmt(credit) : '-'}</td>
          <td class="text-end text-danger">${debit > 0 ? fmt(debit) : '-'}</td>
          <td class="text-end fw-semibold text-danger">${fmt(e.balance)}</td>
          <td class="text-center text-nowrap">
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-outline-secondary btn-edit-entry" data-id="${e.id}" title="Edit"><i class="bi bi-pencil"></i></button>
              <button type="button" class="btn btn-outline-danger btn-delete-entry" data-id="${e.id}" data-txn="${e.txnNo}" title="Delete"><i class="bi bi-trash"></i></button>
            </div>
          </td>
        </tr>
      `);
    });
    const closeBal = meta && typeof meta.closing_balance === 'number'
      ? meta.closing_balance
      : (parseFloat(entries[entries.length - 1]?.balance) || 0);
    if (meta && typeof meta.total_debit === 'number') totalDebit = meta.total_debit;
    if (meta && typeof meta.total_credit === 'number') totalCredit = meta.total_credit;
    $tb.append(`
      <tr class="table-light fw-semibold">
        <td colspan="7" class="text-end">Total</td>
        <td class="text-end text-success">${fmt(totalCredit)}</td>
        <td class="text-end text-danger">${fmt(totalDebit)}</td>
        <td class="text-end text-danger">${fmt(closeBal)}</td>
        <td></td>
      </tr>
    `);
  }

  function focusNextFittingField(current) {
    const fields = Array.from(document.querySelectorAll('#fittingLabourForm input, #fittingLabourForm select, #fittingLabourForm textarea, #btnSave')).filter(function (el) {
      if (el.disabled || el.readOnly) return false;
      if ((el.type || '') === 'hidden') return false;
      if (el.closest('.d-none, .modal')) return false;
      return el.offsetParent !== null || el === document.activeElement;
    });
    const index = fields.indexOf(current);
    const next = index >= 0 ? fields[index + 1] : null;
    const target = next || document.getElementById('btnSave');
    if (!target) return;
    target.focus();
    if (target.tagName === 'INPUT' && typeof target.select === 'function') target.select();
  }

  function moveDropdownActive($dropdown, direction) {
    const $items = $dropdown.find('.dropdown-item[data-code]');
    if (!$items.length) return;
    let index = $items.index($items.filter('.active'));
    if (direction > 0) index = index < 0 ? 0 : Math.min(index + 1, $items.length - 1);
    else index = index <= 0 ? 0 : index - 1;
    $items.removeClass('active').eq(index).addClass('active');
  }

  $('#labourSearch').on('focus input', function () {
    renderLabourDropdown(filterLabours($(this).val()));
  });
  $('#labourSearch').on('keydown', function (e) {
    const $dd = $('#labourDropdown');
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      if (!$dd.is(':visible')) renderLabourDropdown(filterLabours($(this).val()));
      moveDropdownActive($dd, e.key === 'ArrowDown' ? 1 : -1);
      return;
    }
    if (e.key === 'Escape') {
      $dd.hide();
      return;
    }
    if (e.key !== 'Enter') return;
    e.preventDefault();
    e.stopPropagation();
    const $selected = $dd.is(':visible') ? $dd.find('.dropdown-item.active[data-code]') : $();
    if ($selected.length) {
      selectLabour($selected.data('code'));
    }
    if ($('#labourCodeSubmit').val()) {
      $dd.hide();
      focusNextFittingField(this);
    }
  });
  $(document).on('click', '#labourDropdown .dropdown-item[data-code]', function () {
    selectLabour($(this).data('code'));
  });

  $('#productSearch').on('focus input', function () {
    renderProductDropdown(filterProducts($(this).val()));
  });
  $('#productSearch').on('keydown', function (e) {
    const $dd = $('#productDropdown');
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      if (!$dd.is(':visible')) renderProductDropdown(filterProducts($(this).val()));
      moveDropdownActive($dd, e.key === 'ArrowDown' ? 1 : -1);
      return;
    }
    if (e.key === 'Escape') {
      $dd.hide();
      return;
    }
    if (e.key !== 'Enter') return;
    e.preventDefault();
    e.stopPropagation();
    const $selected = $dd.is(':visible') ? $dd.find('.dropdown-item.active[data-code]') : $();
    if ($selected.length) {
      selectProduct($selected.data('code'));
    }
    if ($('#productCodeSubmit').val()) {
      $dd.hide();
      focusNextFittingField(this);
    }
  });
  $(document).on('click', '#productDropdown .dropdown-item[data-code]', function () {
    selectProduct($(this).data('code'));
  });

  $('#fittingLabourForm').on('keydown', function (e) {
    if (e.key !== 'Enter') return;
    const el = e.target;
    if (!el || el.id === 'labourSearch' || el.id === 'productSearch') return;
    const tag = el.tagName;
    if (tag !== 'INPUT' && tag !== 'SELECT' && tag !== 'TEXTAREA') return;
    if (el.id === 'btnSave') return;
    e.preventDefault();
    focusNextFittingField(el);
  });

  $('#txnType').on('change', function () {
    applyTypeUi($(this).val(), { reset: !editingId });
  });
  applyTypeUi($('#txnType').val(), { reset: false });
  $('#rate, #qty').on('input change', calcAmount);

  $('#workSelect').on('change', function () {
    if ($(this).val()) {
      tryApplyMatchedRate();
      if (!$('#qty').val()) $('#qty').val('1');
      calcAmount();
    } else {
      $('#rate, #amount').val('');
    }
  });

  $(document).on('click', function (e) {
    if (!$(e.target).closest('#labourSearch, #labourDropdown').length) $('#labourDropdown').hide();
    if (!$(e.target).closest('#productSearch, #productDropdown').length) $('#productDropdown').hide();
  });

  $(document).on('click', '.btn-edit-entry', function () {
    const entry = entriesCache.find(x => String(x.id) === String($(this).data('id')));
    if (!entry) return;
    beginEdit(entry);
  });
  $('#btnCancelEdit').on('click', cancelEdit);

  $(document).on('click', '.btn-delete-entry', function () {
    const id = $(this).data('id');
    const txnNo = $(this).data('txn');
    Swal.fire({
      title: 'Delete Entry?',
      text: `Transaction ${txnNo} will be deleted.`,
      icon: 'warning', showCancelButton: true,
      confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (!result.isConfirmed) return;
      $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
      $.ajax({ url: `{{ url('/fitting-labour/delete') }}/${encodeURIComponent(id)}`, method: 'DELETE' })
        .done(function (res) {
          if (String(editingId) === String(id)) cancelEdit();
          Swal.fire({ icon: 'success', title: 'Deleted', text: res.message || 'Deleted' });
          loadContext();
        })
        .fail(function (xhr) {
          Swal.fire({ icon: 'error', title: 'Delete Failed', text: xhr.responseJSON?.message || 'Failed' });
        });
    });
  });

  // ---- Add Work Modal ----
  function resetAwForm() {
    $('#awEditId').val('');
    $('#awProductCode').val('');
    $('#awWorkName').val('');
    $('#awRate').val('');
    $('#awSaveBtn').html('<i class="bi bi-save me-1"></i>Save Work');
  }

  function renderAwWorksTable(rows) {
    const $tb = $('#awWorksTable tbody').empty();
    if (!rows.length) {
      $tb.html('<tr><td colspan="5" class="text-center text-muted py-3">No works saved</td></tr>');
      $('#awWorksHint').text('0 works');
      return;
    }
    rows.forEach((w, idx) => {
      $tb.append(`
        <tr>
          <td class="text-center">${idx + 1}</td>
          <td>${w.productName || w.productCode || '-'}</td>
          <td>${w.workName || '-'}</td>
          <td class="text-end">Rs. ${fmt(w.rate)}</td>
          <td class="text-center text-nowrap">
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-outline-secondary btn-aw-edit"
                data-id="${w.id}" data-product="${w.productCode}" data-work="${w.workName}" data-rate="${w.rate}" title="Edit">
                <i class="bi bi-pencil"></i>
              </button>
              <button type="button" class="btn btn-outline-danger btn-aw-delete" data-id="${w.id}" title="Delete">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      `);
    });
    $('#awWorksHint').text(rows.length + ' work' + (rows.length === 1 ? '' : 's'));
  }

  function loadAwWorksTable(preferProduct) {
    const q = preferProduct ? { productCode: preferProduct } : {};
    $('#awWorksHint').text('Loading...');
    return $.get(`{{ route('fitting-labour.works') }}`, q)
      .done(function (res) {
        renderAwWorksTable(res.data || []);
      })
      .fail(function () {
        renderAwWorksTable([]);
        $('#awWorksHint').text('Failed to load');
      });
  }

  function openAddWorkModal() {
    resetAwForm();
    const selected = $('#productCodeSubmit').val();
    if (selected) $('#awProductCode').val(selected);
    const modalEl = document.getElementById('addWorkModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
    loadAwWorksTable();
  }

  $('#addWorkLink').on('click', function (e) {
    e.preventDefault();
    openAddWorkModal();
  });

  $('#awFormResetBtn').on('click', resetAwForm);

  $('#addWorkForm').on('submit', function (e) {
    e.preventDefault();
    const productCode = $('#awProductCode').val();
    const workName = ($('#awWorkName').val() || '').trim();
    const rate = parseFloat($('#awRate').val());
    const id = $('#awEditId').val() || null;
    if (!productCode || !workName || !(rate >= 0)) {
      Swal.fire({ icon: 'warning', title: 'Required', text: 'Select product, enter work name and rate' });
      return;
    }
    const $btn = $('#awSaveBtn').prop('disabled', true);
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    $.post(`{{ route('fitting-labour.works.store') }}`, { productCode, workName, rate, id })
      .done(function (res) {
        Swal.fire({ icon: 'success', title: 'Saved', text: res.message || 'Work saved', timer: 1400, showConfirmButton: false });
        resetAwForm();
        if (productCode) $('#awProductCode').val(productCode);
        loadAwWorksTable();
        refreshWorksFromServer(workName);
        if (workName && !$('#awWorkNameList option').filter(function () {
          return $(this).attr('value') === workName;
        }).length) {
          $('#awWorkNameList').append(`<option value="${workName}"></option>`);
        }
      })
      .fail(function (xhr) {
        Swal.fire({ icon: 'error', title: 'Save Failed', text: xhr.responseJSON?.message || 'Could not save work' });
      })
      .always(() => $btn.prop('disabled', false));
  });

  $(document).on('click', '.btn-aw-edit', function () {
    $('#awEditId').val($(this).data('id'));
    $('#awProductCode').val($(this).data('product'));
    $('#awWorkName').val($(this).data('work'));
    $('#awRate').val(Number($(this).data('rate') || 0).toFixed(2));
    $('#awSaveBtn').html('<i class="bi bi-check2 me-1"></i>Update Work');
  });

  $(document).on('click', '.btn-aw-delete', function () {
    const id = $(this).data('id');
    Swal.fire({
      title: 'Delete Work?',
      text: 'This work rate will be removed.',
      icon: 'warning', showCancelButton: true,
      confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete'
    }).then((result) => {
      if (!result.isConfirmed) return;
      $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
      $.ajax({ url: `{{ url('/fitting-labour/works') }}/${encodeURIComponent(id)}`, method: 'DELETE' })
        .done(function (res) {
          Swal.fire({ icon: 'success', title: 'Deleted', text: res.message || 'Deleted', timer: 1200, showConfirmButton: false });
          if (String($('#awEditId').val()) === String(id)) resetAwForm();
          loadAwWorksTable();
          refreshWorksFromServer($('#workSelect').val() || '');
        })
        .fail(function (xhr) {
          Swal.fire({ icon: 'error', title: 'Delete Failed', text: xhr.responseJSON?.message || 'Failed' });
        });
    });
  });

  $('#fittingLabourForm').on('submit', function (e) {
    e.preventDefault();
    const labourCode = $('#labourCodeSubmit').val();
    const productCode = $('#productCodeSubmit').val();
    const workName = ($('#workSelect').val() || '').trim();
    const type = $('#txnType').val();
    const isCash = type === 'Cash';
    const rate = parseFloat($('#rate').val()) || 0;
    const qty = parseFloat($('#qty').val()) || 0;
    if (!isCash) calcAmount();
    const amount = parseFloat($('#amount').val()) || 0;
    const description = ($('#cashDescription').val() || '').trim();
    const entryType = $('#cashEntryType').val() || 'pay';

    if (!labourCode) {
      Swal.fire({ icon: 'warning', title: 'Required', text: 'Please select a fitter' });
      return;
    }
    if (isCash) {
      if (!description || amount <= 0) {
        Swal.fire({ icon: 'warning', title: 'Required', text: 'Enter description and amount' });
        return;
      }
    } else if (!productCode || !workName || rate < 0 || qty <= 0) {
      Swal.fire({ icon: 'warning', title: 'Required', text: 'Select product, work and enter rate/qty' });
      return;
    }

    const $v = $('#variantSelect option:selected');
    const editId = $('#editTxnId').val() || editingId;
    const payload = isCash
      ? { labourCode, type, entryType, description, amount, rate: amount, qty: 1 }
      : {
          labourCode, type, productCode, workName, rate, qty, amount,
          variant_id: $v.val() || null,
          variant_name: $v.data('name') || null,
          variant_value: $v.data('value') || null,
        };

    const $btn = $('#btnSave').prop('disabled', true);
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    const req = editId
      ? $.ajax({ url: `{{ url('/fitting-labour/update') }}/${encodeURIComponent(editId)}`, method: 'PUT', data: payload })
      : $.post(`{{ route('fitting-labour.store') }}`, payload);

    req.done(function (res) {
      Swal.fire({
        icon: 'success',
        title: editId ? 'Updated' : 'Saved',
        html: `${res.message || 'Saved'}<br><strong>${res.txnNo || ''}</strong>`
      });
      cancelEdit();
      $('#rate, #qty, #amount, #cashDescription').val('');
      clearProduct();
      clearWorkSelection();
      loadContext();
    }).fail(function (xhr) {
      Swal.fire({
        icon: 'error',
        title: editId ? 'Update Failed' : 'Save Failed',
        text: xhr.responseJSON?.message || 'Could not save'
      });
    }).always(() => $btn.prop('disabled', false));
  });

  fillWorkSelect();
})();
</script>

@endsection
