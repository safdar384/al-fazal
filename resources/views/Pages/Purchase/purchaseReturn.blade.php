@extends('layout.app')

@section('title', !empty($editReturn) ? 'Edit Purchase Return' : 'Purchase Return')

@section('content')
@include('partials.sale.erp-styles')
@include('components.toast-notification')
@php $isEditReturn = !empty($editReturn); @endphp

<style>
  #supplierDropdown, .productDropdown {
    position: absolute; top: 100%; left: 0; z-index: 10650;
    border: 1px solid #dee2e6; border-radius: 0.375rem; background: #fff;
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.2); margin-top: 4px;
    max-height: 300px; overflow-y: auto;
    width: 100%; min-width: 220px;
  }
  #supplierDropdown .dropdown-item, .productDropdown .dropdown-item {
    padding: 10px 12px; cursor: pointer; border-bottom: 1px solid #f1f3f5;
  }
  #supplierDropdown .dropdown-item:hover, #supplierDropdown .dropdown-item.active,
  .productDropdown .dropdown-item:hover, .productDropdown .dropdown-item.active {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%); color: #fff;
  }
  .si-card {
    box-shadow: 0 2px 12px rgba(15,39,71,0.08); border-radius: 4px; border: 1px solid #d5dde8;
    overflow: visible;
  }
  .si-card .card-body { overflow: visible; }
  .si-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    border: none; color: #fff; padding: 0.75rem 1rem;
  }
  .si-label { font-size: 0.78rem; color: #0f2747; margin-bottom: 0.25rem; }
  .si-table thead th {
    background: #0f2747; color: #fff; font-size: 0.72rem; font-weight: 700;
    text-transform: uppercase; border-color: #0a1c33; white-space: nowrap;
  }
  .si-table tbody tr:nth-child(even) td { background: #f4f7fb; }
  #productTable td { position: relative; overflow: visible; }
  .sr-product-wrap {
    overflow: visible !important;
  }
  .position-relative, .product-search-wrap {
    position: relative !important;
    overflow: visible !important;
  }
  .condition-select.condition-good { border-color: #198754; color: #198754; font-weight: 600; }
  .condition-select.condition-damage { border-color: #dc3545; color: #dc3545; font-weight: 600; }
  .row-damage td { background: #fff5f5 !important; }
</style>

<main class="app-main erp-invoice-page" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid px-2 px-md-3">
      <div class="row align-items-center gy-2">
        <div class="col-12 col-md-6">
          <h3 class="mb-0 fw-bold" style="color:#0f2747;">
            <i class="bi bi-arrow-return-left me-2"></i>{{ $isEditReturn ? 'Edit Purchase Return' : 'Purchase Return' }}
          </h3>
          <p class="text-muted small mb-0">Good items reduce stock · Damage does not · Full amount debits supplier</p>
        </div>
        <div class="col-12 col-md-6">
          <ol class="breadcrumb float-md-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Sale</span></li>
            <li class="breadcrumb-item active">{{ $isEditReturn ? 'Edit Return' : 'Return' }}</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <div class="card si-card border-0 mb-3">
        <div class="card-header si-card-head">
          <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-file-earmark-text me-2"></i>Return Information</h6>
        </div>
        <div class="card-body p-2 p-md-3">
          <div class="row g-2">
            <div class="col-md-4">
              <label class="form-label fw-semibold si-label">Supplier</label>
              <div class="position-relative">
                <input type="text" class="form-control form-control-sm" id="supplierSearch"
                       placeholder="Type to search supplier..." autocomplete="off">
                <select class="form-select form-select-sm d-none" id="supplierCode">
                  <option value="">Select Supplier</option>
                  @foreach($suppliers as $supplier)
                    @php
                      $biz = trim((string) ($supplier->bussnessName ?: $supplier->custName));
                      $city = trim((string) ($supplier->custCity ?? ''));
                      $label = $city !== '' ? $biz.' — '.$city : $biz;
                    @endphp
                    <option value="{{ $supplier->custCode }}"
                            data-city="{{ $supplier->custCity }}"
                            data-name="{{ $supplier->custName }}"
                            data-business="{{ $supplier->bussnessName ?? '' }}"
                            data-label="{{ $label }}">
                      {{ $label }}
                    </option>
                  @endforeach
                </select>
                <div id="supplierDropdown" class="dropdown-menu w-100" style="display:none;"></div>
              </div>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold si-label">City</label>
              <input type="text" class="form-control form-control-sm" id="city" readonly>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold si-label" id="balanceFieldLabel">Payable Balance</label>
              <input type="text" class="form-control form-control-sm" id="balance" readonly>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold si-label">Return No</label>
              <input type="text" class="form-control form-control-sm" id="returnNo" value="{{ $nextReturnNo }}" readonly>
            </div>
            <div class="col-md-2">
              <label class="form-label fw-semibold si-label">Date</label>
              <input type="date" class="form-control form-control-sm" id="returnDate" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold si-label">Original Invoice (optional)</label>
              <input type="text" class="form-control form-control-sm" id="originalInvoiceNo" placeholder="PINV-xxxxx" maxlength="50">
            </div>
            <div class="col-md-9">
              <label class="form-label fw-semibold si-label">Comments</label>
              <input type="text" class="form-control form-control-sm" id="comments" placeholder="Remarks..." maxlength="1000">
            </div>
          </div>
        </div>
      </div>

      <div class="card si-card border-0 mb-2">
        <div class="card-header si-card-head py-2 d-flex justify-content-between align-items-center">
          <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-cart-plus me-2"></i>Return Products</h6>
          <small class="text-white-50">Default condition: <strong class="text-white">Good</strong></small>
        </div>
        <div class="card-body p-0" style="overflow:visible;">
          <div class="sr-product-wrap table-responsive" style="overflow:visible !important;">
            <table class="table table-hover mb-0 si-table" id="productTable">
              <thead>
                <tr>
                  <th style="width:34%">Product / Variant</th>
                  <th style="width:10%" class="text-center">Condition</th>
                  <th style="width:10%" class="text-center">Qty</th>
                  <th style="width:12%" class="text-end">Rate</th>
                  <th style="width:10%" class="text-end">Disc %</th>
                  <th style="width:12%" class="text-end">Amount</th>
                  <th style="width:12%" class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <div class="d-flex gap-2 align-items-start">
                      <div class="flex-grow-1 product-search-wrap">
                        <input type="text" class="form-control form-control-sm productSearch"
                               placeholder="Type to search product..." autocomplete="off">
                        <select class="form-select form-select-sm d-none productCode">
                          <option value="">Select Product</option>
                          @foreach($products->unique('productCode') as $product)
                            <option value="{{ $product->productCode }}"
                                    data-rate="{{ $product->purchaseRate ?? $product->costRate }}"
                                    data-cost="{{ $product->costRate }}"
                                    data-name="{{ $product->productName }}"
                                    data-company="{{ $product->companyName }}">
                              {{ $product->productName }} - {{ $product->companyName }}
                            </option>
                          @endforeach
                        </select>
                        <div class="productDropdown dropdown-menu w-100" style="display:none;"></div>
                      </div>
                      <div class="variantWrapper d-none" style="min-width:120px;">
                        <select class="form-select form-select-sm variantSelect">
                          <option value="">Variant</option>
                        </select>
                      </div>
                    </div>
                  </td>
                  <td>
                    <select class="form-select form-select-sm condition-select condition-good" title="Good restores stock">
                      <option value="good" selected>Good</option>
                      <option value="damage">Damage</option>
                    </select>
                  </td>
                  <td><input type="number" class="form-control form-control-sm text-center qty" value="1" min="0.01" step="0.01"></td>
                  <td><input type="number" class="form-control form-control-sm text-end rate" value="" step="0.01"></td>
                  <td><input type="number" class="form-control form-control-sm text-end discount" value="" step="0.01" min="0" max="100"></td>
                  <td><input type="text" class="form-control form-control-sm text-end amount" readonly value=""></td>
                  <td class="text-center">
                    <div class="btn-group btn-group-sm">
                      <button type="button" class="btn btn-success btn-add-row" title="Add Row"><i class="bi bi-plus-lg"></i></button>
                      <button type="button" class="btn btn-danger btn-delete-row" title="Delete"><i class="bi bi-trash3"></i></button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="row g-2 mb-4">
        <div class="col-md-6">
          <div class="card si-card border-0 h-100">
            <div class="card-header si-card-head py-2">
              <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-info-circle me-2"></i>Stock Summary</h6>
            </div>
            <div class="card-body">
              <div class="d-flex justify-content-between mb-2">
                <span>Good qty (stock −)</span>
                <strong class="text-success" id="goodQtyTotal">0</strong>
              </div>
              <div class="d-flex justify-content-between">
                <span>Damage qty (no stock)</span>
                <strong class="text-danger" id="damageQtyTotal">0</strong>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card si-card border-0 h-100">
            <div class="card-header si-card-head py-2">
              <h6 class="mb-0 text-white fw-semibold"><i class="bi bi-calculator me-2"></i>Totals</h6>
            </div>
            <div class="card-body">
              <div class="d-flex justify-content-between mb-1">
                <span>Invoice Total</span>
                <strong id="invoiceTotal">0.00</strong>
              </div>
              <div class="mb-2">
                <label class="form-label small mb-1">Invoice Discount (Rs.)</label>
                <input type="number" class="form-control form-control-sm text-end" id="invoiceDiscount" value="0" min="0" step="0.01">
              </div>
              <div class="d-flex justify-content-between p-2 rounded" style="background:#eefaf2;">
                <span class="fw-semibold">Grand Total (Supplier Debit)</span>
                <strong class="text-success fs-5" id="grandTotal">0.00</strong>
              </div>
              <button type="button" class="btn btn-primary w-100 mt-3" id="btnSaveReturn" style="background:#0f2747;border-color:#0f2747;">
                <i class="bi bi-save me-1"></i>{{ $isEditReturn ? 'Update Purchase Return' : 'Save Purchase Return' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
$(function () {
  const fmt = (n) => (parseFloat(n) || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const productUrl = @json(url('/purchase/return/product'));
  const storeUrl = @json(route('purchase.return.store'));
  const updateUrl = @json($isEditReturn ? route('purchase.return.update', $editReturn['returnNo']) : '');
  const isEditReturn = @json($isEditReturn);
  const editReturnData = @json($editReturn ?? null);
  const customerUrl = @json(url('/purchase/invoice/supplier'));
  const csrf = $('meta[name="csrf-token"]').attr('content');
  // Cache once — blankRowHtml must not read options after tbody is emptied (edit fill).
  const productOptionsHtml = $('#productTable tbody tr:first .productCode').html() || '<option value="">Select Product</option>';

  function lineAmount(qty, rate, disc) {
    qty = Math.max(0, parseFloat(qty) || 0);
    rate = Math.max(0, parseFloat(rate) || 0);
    disc = Math.max(0, Math.min(100, parseFloat(disc) || 0));
    return Math.round(qty * rate * (1 - disc / 100) * 100) / 100;
  }

  function syncConditionStyle($sel) {
    const v = ($sel.val() || 'good').toLowerCase();
    $sel.toggleClass('condition-good', v === 'good').toggleClass('condition-damage', v === 'damage');
    $sel.closest('tr').toggleClass('row-damage', v === 'damage');
  }

  function calcRow($tr) {
    const amount = lineAmount($tr.find('.qty').val(), $tr.find('.rate').val(), $tr.find('.discount').val());
    $tr.find('.amount').val(amount ? amount.toFixed(2) : '');
    calcTotals();
  }

  function calcTotals() {
    let total = 0, goodQty = 0, damageQty = 0;
    $('#productTable tbody tr').each(function () {
      const $tr = $(this);
      if (!$tr.find('.productCode').val()) return;
      total += parseFloat($tr.find('.amount').val()) || 0;
      const qty = parseFloat($tr.find('.qty').val()) || 0;
      if (($tr.find('.condition-select').val() || 'good') === 'damage') damageQty += qty;
      else goodQty += qty;
    });
    const disc = Math.max(0, parseFloat($('#invoiceDiscount').val()) || 0);
    const grand = Math.max(0, total - Math.min(disc, total));
    $('#invoiceTotal').text(fmt(total));
    $('#grandTotal').text(fmt(grand));
    $('#goodQtyTotal').text(goodQty.toLocaleString(undefined, { maximumFractionDigits: 2 }));
    $('#damageQtyTotal').text(damageQty.toLocaleString(undefined, { maximumFractionDigits: 2 }));
  }

  function filterSuppliers(term) {
    const t = (term || '').toLowerCase().trim();
    const list = [];
    $('#supplierCode option').each(function () {
      const code = $(this).val();
      if (!code) return;
      const label = ($(this).data('label') || $(this).text() || '').toString();
      if (!t || label.toLowerCase().includes(t) || code.toLowerCase().includes(t)) {
        list.push({ code, label, city: $(this).data('city') || '', name: $(this).data('name') || '' });
      }
    });
    return list;
  }

  function renderSupplierDropdown(list) {
    const $dd = $('#supplierDropdown').empty();
    if (!list.length) {
      $dd.html('<div class="dropdown-item text-muted">No supplier found</div>').show();
      return;
    }
    list.slice(0, 20).forEach((c, i) => {
      $dd.append(`<button type="button" class="dropdown-item ${i === 0 ? 'active' : ''}" data-code="${c.code}"><strong>${c.label}</strong></button>`);
    });
    $dd.show();
  }

  function focusEl(el) {
    if (!el) return;
    const $el = $(el);
    if (!$el.length) return;
    $el.trigger('focus');
    if ($el.is('input:not([type=date]):not([type=button]):not([type=submit])') && $el[0].select) {
      try { $el[0].select(); } catch (err) {}
    }
  }

  /** Enter focus chain: Supplier → Original Inv → Comment → Product → … → Save */
  function focusNextReturnField(current) {
    const fields = [];
    const pushIf = function (el) {
      if (!el) return;
      const $el = $(el);
      if (!$el.length || $el.prop('disabled') || $el.prop('readonly')) return;
      if ($el.is(':hidden') && !$el.is(':focus')) return;
      if ($el.closest('.d-none, .productDropdown, #supplierDropdown, .modal').length) return;
      fields.push($el[0]);
    };

    pushIf(document.getElementById('supplierSearch'));
    pushIf(document.getElementById('returnDate'));
    pushIf(document.getElementById('originalInvoiceNo'));
    pushIf(document.getElementById('comments'));

    $('#productTable tbody tr').each(function () {
      const $tr = $(this);
      pushIf($tr.find('.productSearch')[0]);
      const $vw = $tr.find('.variantWrapper');
      if ($vw.length && !$vw.hasClass('d-none')) pushIf($tr.find('.variantSelect')[0]);
      pushIf($tr.find('.condition-select')[0]);
      pushIf($tr.find('.qty')[0]);
      pushIf($tr.find('.rate')[0]);
      pushIf($tr.find('.discount')[0]);
      pushIf($tr.find('.btn-add-row')[0]);
    });

    pushIf(document.getElementById('invoiceDiscount'));
    pushIf(document.getElementById('btnSaveReturn'));

    const index = fields.indexOf(current);
    const next = index >= 0 ? fields[index + 1] : fields[0];
    focusEl(next || document.getElementById('btnSaveReturn'));
  }

  function selectSupplier(code) {
    code = String(code || '').trim();
    const $opt = $('#supplierCode option').filter(function () { return String($(this).val()) === code; }).first();
    if (!$opt.length) return;
    $('#supplierCode').val(code);
    $('#supplierSearch').val($opt.data('label') || $opt.text());
    $('#city').val($opt.data('city') || '');
    $('#supplierDropdown').hide();
    $.get(`${customerUrl}/${encodeURIComponent(code)}`)
      .done(function (res) {
        $('#balance').val(fmt(res.balance || 0));
        if (res.balance_label) $('#balanceFieldLabel').text(res.balance_label);
      })
      .fail(function () { $('#balance').val('0.00'); });
    focusEl(document.getElementById('originalInvoiceNo'));
  }

  $('#supplierSearch').on('focus input', function () {
    renderSupplierDropdown(filterSuppliers($(this).val()));
  });
  $(document).on('mousedown', '#supplierDropdown .dropdown-item[data-code]', function (e) {
    e.preventDefault();
    selectSupplier($(this).attr('data-code'));
  });
  $('#supplierSearch').on('keydown', function (e) {
    const $dd = $('#supplierDropdown');
    const $items = $dd.find('.dropdown-item[data-code]');
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      if (!$dd.is(':visible')) renderSupplierDropdown(filterSuppliers($(this).val()));
      let i = $items.index($items.filter('.active'));
      i = e.key === 'ArrowDown' ? Math.min(i + 1, $items.length - 1) : Math.max(i - 1, 0);
      if (i < 0) i = 0;
      $items.removeClass('active').eq(i).addClass('active');
      return;
    }
    if (e.key === 'Enter') {
      e.preventDefault();
      e.stopPropagation();
      const $sel = $dd.is(':visible') ? $dd.find('.dropdown-item.active[data-code]') : $();
      if ($sel.length) {
        selectSupplier($sel.attr('data-code'));
      } else if ($('#supplierCode').val()) {
        focusEl(document.getElementById('originalInvoiceNo'));
      } else {
        const matches = filterSuppliers($(this).val());
        if (matches.length === 1) selectSupplier(matches[0].code);
      }
      return;
    }
    if (e.key === 'Escape') $dd.hide();
  });

  function filterProducts($row, term) {
    const t = (term || '').toLowerCase().trim();
    const list = [];
    $row.find('.productCode option').each(function () {
      const code = $(this).val();
      if (!code) return;
      const name = ($(this).data('name') || '').toString();
      const company = ($(this).data('company') || '').toString();
      const hay = (name + ' ' + company + ' ' + code).toLowerCase();
      if (!t || hay.includes(t)) list.push({ code, name, company, rate: $(this).data('rate'), cost: $(this).data('cost') });
    });
    return list;
  }

  function renderProductDropdown($row, list) {
    const $dd = $row.find('.productDropdown').empty();
    if (!list.length) {
      $dd.html('<div class="dropdown-item text-muted">No product</div>').show();
      return;
    }
    list.slice(0, 20).forEach((p, i) => {
      $dd.append(`<button type="button" class="dropdown-item ${i === 0 ? 'active' : ''}" data-code="${p.code}"><strong>${p.name}</strong><div class="small">${p.company || p.code}</div></button>`);
    });
    $dd.show();
  }

  function loadProduct($row, code) {
    const supplierCode = $('#supplierCode').val() || '';
    $.get(`${productUrl}/${encodeURIComponent(code)}`, { supplierCode })
      .done(function (res) {
        $row.find('.productCode').val(code);
        $row.find('.productSearch').val(res.productName || code);
        $row.find('.rate').val(res.purchaseRate != null ? res.purchaseRate : '');
        $row.data('productName', res.productName || '');
        $row.data('costRate', res.costRate || 0);
        const $vw = $row.find('.variantWrapper');
        const $vs = $row.find('.variantSelect').empty().append('<option value="">Variant</option>');
        if (res.variants && res.variants.length) {
          res.variants.forEach(function (v) {
            $vs.append(`<option value="${v.id}" data-name="${v.name || ''}" data-value="${v.value || ''}" data-add="${v.additionalPrice || 0}" data-rate="${v.hasSpecialRate ? v.specialRate : ''}" data-cost="${v.costRate || 0}">${v.name}: ${v.value}</option>`);
          });
          $vw.removeClass('d-none');
          $row.find('.productDropdown').hide();
          calcRow($row);
          focusEl($vs[0]);
        } else {
          $vw.addClass('d-none');
          $row.find('.productDropdown').hide();
          calcRow($row);
          focusEl($row.find('.condition-select')[0]);
        }
      })
      .fail(function () {
        Swal.fire({ icon: 'warning', title: 'Product', text: 'Product details not found' });
      });
  }

  $(document).on('focus input', '.productSearch', function () {
    const $row = $(this).closest('tr');
    renderProductDropdown($row, filterProducts($row, $(this).val()));
  });

  $(document).on('mousedown', '.productDropdown .dropdown-item[data-code]', function (e) {
    e.preventDefault();
    loadProduct($(this).closest('tr'), $(this).attr('data-code'));
  });

  $(document).on('keydown', '.productSearch', function (e) {
    const $row = $(this).closest('tr');
    const $dd = $row.find('.productDropdown');
    const $items = $dd.find('.dropdown-item[data-code]');
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      if (!$dd.is(':visible')) renderProductDropdown($row, filterProducts($row, $(this).val()));
      let i = $items.index($items.filter('.active'));
      i = e.key === 'ArrowDown' ? Math.min(i + 1, $items.length - 1) : Math.max(i - 1, 0);
      if (i < 0) i = 0;
      $items.removeClass('active').eq(i).addClass('active');
      return;
    }
    if (e.key === 'Enter') {
      e.preventDefault();
      e.stopPropagation();
      const $sel = $dd.is(':visible') ? $dd.find('.dropdown-item.active[data-code]') : $();
      if ($sel.length) {
        loadProduct($row, $sel.attr('data-code'));
      } else if ($row.find('.productCode').val()) {
        const $vw = $row.find('.variantWrapper');
        if ($vw.length && !$vw.hasClass('d-none')) focusEl($row.find('.variantSelect')[0]);
        else focusEl($row.find('.condition-select')[0]);
      } else {
        const matches = filterProducts($row, $(this).val());
        if (matches.length === 1) loadProduct($row, matches[0].code);
      }
      return;
    }
    if (e.key === 'Escape') $dd.hide();
  });

  $(document).on('change', '.variantSelect', function () {
    const $row = $(this).closest('tr');
    const $opt = $(this).find('option:selected');
    const special = $opt.data('rate');
    if (special !== '' && special != null && !isNaN(parseFloat(special))) {
      $row.find('.rate').val(parseFloat(special));
    } else {
      const base = parseFloat($row.find('.productCode option:selected').data('rate')) || 0;
      const add = parseFloat($opt.data('add')) || 0;
      $row.find('.rate').val(base + add);
    }
    if ($opt.data('cost') != null) $row.data('costRate', $opt.data('cost'));
    calcRow($row);
  });

  $(document).on('input change', '.qty, .rate, .discount', function () {
    calcRow($(this).closest('tr'));
  });

  $(document).on('change', '.condition-select', function () {
    syncConditionStyle($(this));
    calcTotals();
  });

  $('#invoiceDiscount').on('input change', calcTotals);

  function blankRowHtml() {
    return `<tr>
      <td>
        <div class="d-flex gap-2 align-items-start">
          <div class="flex-grow-1 product-search-wrap">
            <input type="text" class="form-control form-control-sm productSearch" placeholder="Type to search product..." autocomplete="off">
            <select class="form-select form-select-sm d-none productCode">${productOptionsHtml}</select>
            <div class="productDropdown dropdown-menu w-100" style="display:none;"></div>
          </div>
          <div class="variantWrapper d-none" style="min-width:120px;">
            <select class="form-select form-select-sm variantSelect"><option value="">Variant</option></select>
          </div>
        </div>
      </td>
      <td>
        <select class="form-select form-select-sm condition-select condition-good">
          <option value="good" selected>Good</option>
          <option value="damage">Damage</option>
        </select>
      </td>
      <td><input type="number" class="form-control form-control-sm text-center qty" value="1" min="0.01" step="0.01"></td>
      <td><input type="number" class="form-control form-control-sm text-end rate" value="" step="0.01"></td>
      <td><input type="number" class="form-control form-control-sm text-end discount" value="" step="0.01" min="0" max="100"></td>
      <td><input type="text" class="form-control form-control-sm text-end amount" readonly value=""></td>
      <td class="text-center">
        <div class="btn-group btn-group-sm">
          <button type="button" class="btn btn-success btn-add-row"><i class="bi bi-plus-lg"></i></button>
          <button type="button" class="btn btn-danger btn-delete-row"><i class="bi bi-trash3"></i></button>
        </div>
      </td>
    </tr>`;
  }

  $(document).on('click', '.btn-add-row', function () {
    const $tr = $(blankRowHtml());
    $('#productTable tbody').append($tr);
    syncConditionStyle($tr.find('.condition-select'));
    $tr.find('.productSearch').focus();
  });

  $(document).on('click', '.btn-delete-row', function () {
    const $rows = $('#productTable tbody tr');
    if ($rows.length <= 1) {
      const $tr = $(this).closest('tr');
      $tr.find('.productSearch').val('');
      $tr.find('.productCode').val('');
      $tr.find('.rate,.discount,.amount').val('');
      $tr.find('.qty').val(1);
      $tr.find('.condition-select').val('good');
      syncConditionStyle($tr.find('.condition-select'));
      $tr.find('.variantWrapper').addClass('d-none');
      calcTotals();
      return;
    }
    $(this).closest('tr').remove();
    calcTotals();
  });

  $(document).on('click', function (e) {
    if (!$(e.target).closest('#supplierSearch, #supplierDropdown').length) $('#supplierDropdown').hide();
    if (!$(e.target).closest('.productSearch, .productDropdown').length) $('.productDropdown').hide();
  });

  $('#btnSaveReturn').on('click', function () {
    const supplierCode = $('#supplierCode').val();
    if (!supplierCode) {
      Swal.fire({ icon: 'warning', title: 'Required', text: 'Please select a supplier.' });
      return;
    }
    const items = [];
    $('#productTable tbody tr').each(function () {
      const $tr = $(this);
      const productCode = $tr.find('.productCode').val();
      if (!productCode) return;
      const $v = $tr.find('.variantSelect option:selected');
      items.push({
        productCode,
        productName: $tr.data('productName') || $tr.find('.productCode option:selected').data('name') || '',
        variant_id: $tr.find('.variantSelect').val() || null,
        variant_name: $v.data('name') || null,
        variant_value: $v.data('value') || null,
        variant_added_price: parseFloat($v.data('add')) || 0,
        qty: parseFloat($tr.find('.qty').val()) || 0,
        rate: parseFloat($tr.find('.rate').val()) || 0,
        cost_rate: parseFloat($tr.data('costRate')) || 0,
        discount: parseFloat($tr.find('.discount').val()) || 0,
        condition: ($tr.find('.condition-select').val() || 'good')
      });
    });
    if (!items.length) {
      Swal.fire({ icon: 'warning', title: 'Required', text: 'Add at least one product.' });
      return;
    }

    const $btn = $(this).prop('disabled', true);
    const payload = {
      supplierCode,
      supplierName: $('#supplierCode option:selected').data('name') || '',
      city: $('#city').val() || '',
      returnDate: $('#returnDate').val(),
      originalInvoiceNo: $('#originalInvoiceNo').val() || '',
      comments: $('#comments').val() || '',
      invoiceDiscount: $('#invoiceDiscount').val() || 0,
      items
    };
    if (isEditReturn) {
      payload._method = 'PUT';
    }
    $.ajax({
      url: isEditReturn ? updateUrl : storeUrl,
      method: 'POST',
      dataType: 'json',
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
      data: payload
    }).done(function (res) {
      if (typeof showSuccess === 'function') {
        showSuccess(isEditReturn ? 'Purchase return updated!' : 'Purchase return saved!', 'Return No: ' + (res.returnNo || ''));
      } else {
        Swal.fire({
          icon: 'success',
          title: isEditReturn ? 'Updated' : 'Saved',
          text: res.message || ('Return No: ' + (res.returnNo || '')),
          timer: 1500,
          showConfirmButton: false
        });
      }
      if (res.previewUrl) {
        setTimeout(function () {
          window.open(res.previewUrl, '_blank');
          if (isEditReturn) {
            window.location.href = @json(route('purchaseSummary'));
          } else {
            window.location.reload();
          }
        }, 1200);
      } else {
        setTimeout(function () {
          if (isEditReturn) {
            window.location.href = @json(route('purchaseSummary'));
          } else {
            window.location.reload();
          }
        }, 1500);
      }
    }).fail(function (xhr) {
      $btn.prop('disabled', false);
      Swal.fire({
        icon: 'error',
        title: 'Failed',
        text: xhr.responseJSON?.message || (isEditReturn ? 'Could not update purchase return' : 'Could not save purchase return')
      });
    });
  });

  function fillEditReturn(data) {
    if (!data) return;
    $('#returnNo').val(data.returnNo || '');
    $('#returnDate').val(data.returnDate || '');
    $('#originalInvoiceNo').val(data.originalInvoiceNo || '');
    $('#comments').val(data.comments || '');
    $('#invoiceDiscount').val(data.invoiceDiscount || 0);
    if (data.supplierCode) {
      selectSupplier(data.supplierCode);
    }
    const items = Array.isArray(data.items) ? data.items : [];
    if (!items.length) {
      calcTotals();
      return;
    }
    $('#productTable tbody').empty();
    items.forEach(function (item) {
      const $tr = $(blankRowHtml());
      $('#productTable tbody').append($tr);
      const code = String(item.productCode || '').trim();
      const $pc = $tr.find('.productCode');
      // Ensure option exists so .val() sticks (hidden select needs matching <option>)
      if (code && !$pc.find('option').filter(function () { return String($(this).val()) === code; }).length) {
        $pc.append(
          `<option value="${code}" data-name="${(item.productName || code).replace(/"/g, '&quot;')}" data-rate="${item.rate || 0}" data-cost="${item.cost_rate || 0}">${item.productName || code}</option>`
        );
      }
      $pc.val(code);
      $tr.find('.productSearch').val(item.productName || code);
      $tr.data('productName', item.productName || '');
      $tr.data('costRate', item.cost_rate || 0);
      $tr.find('.qty').val(item.qty || 1);
      $tr.find('.rate').val(item.rate != null ? item.rate : '');
      $tr.find('.discount').val(item.discount || '');
      $tr.find('.amount').val(item.amount != null ? Number(item.amount).toFixed(2) : '');
      const cond = (item.condition || 'good').toLowerCase() === 'damage' ? 'damage' : 'good';
      $tr.find('.condition-select').val(cond);
      syncConditionStyle($tr.find('.condition-select'));
      if (item.variant_id) {
        $tr.find('.variantWrapper').removeClass('d-none');
        $tr.find('.variantSelect').empty().append(
          `<option value="${item.variant_id}" selected data-name="${item.variant_name || ''}" data-value="${item.variant_value || ''}" data-add="${item.variant_added_price || 0}">${item.variant_name || 'Variant'}: ${item.variant_value || ''}</option>`
        );
      }
      if (code) {
        $.get(`${productUrl}/${encodeURIComponent(code)}`, { supplierCode: data.supplierCode || '' })
          .done(function (res) {
            const $vw = $tr.find('.variantWrapper');
            const $vs = $tr.find('.variantSelect').empty().append('<option value="">Variant</option>');
            if (res.variants && res.variants.length) {
              res.variants.forEach(function (v) {
                const sel = String(item.variant_id || '') === String(v.id) ? ' selected' : '';
                $vs.append(`<option value="${v.id}"${sel} data-name="${v.name || ''}" data-value="${v.value || ''}" data-add="${v.additionalPrice || 0}" data-rate="${v.hasSpecialRate ? v.specialRate : ''}" data-cost="${v.costRate || 0}">${v.name}: ${v.value}</option>`);
              });
              $vw.removeClass('d-none');
            } else {
              $vw.addClass('d-none');
            }
          });
      }
    });
    calcTotals();
  }

  if (isEditReturn && editReturnData) {
    fillEditReturn(editReturnData);
  }

  // Global Enter navigation through the form to Save
  $(document).on('keydown', '#main', function (e) {
    if (e.key !== 'Enter') return;
    const el = e.target;
    if (!el || !el.closest) return;

    // Supplier / product search handle their own Enter (dropdown select)
    if (el.id === 'supplierSearch' || (el.classList && el.classList.contains('productSearch'))) return;

    if (el.id === 'btnSaveReturn') {
      e.preventDefault();
      $(el).trigger('click');
      return;
    }

    const $addBtn = $(el).closest('.btn-add-row');
    if ($addBtn.length) {
      e.preventDefault();
      $addBtn.trigger('click');
      return;
    }

    const row = el.closest('#productTable tr');
    if (row && el.classList && el.classList.contains('discount')) {
      e.preventDefault();
      const isLast = row === document.querySelector('#productTable tbody tr:last-child');
      if (isLast) {
        $(row).find('.btn-add-row').trigger('click');
      } else {
        focusNextReturnField(el);
      }
      return;
    }

    if (row && el.classList && el.classList.contains('qty')) {
      if (!row.querySelector('.productCode') || !row.querySelector('.productCode').value) {
        e.preventDefault();
        focusEl(row.querySelector('.productSearch'));
        return;
      }
      const variantWrap = row.querySelector('.variantWrapper');
      const variant = row.querySelector('.variantSelect');
      if (variantWrap && !variantWrap.classList.contains('d-none') && variant && !variant.value) {
        e.preventDefault();
        focusEl(variant);
        return;
      }
    }

    if (row && el.classList && el.classList.contains('variantSelect') && !el.value) {
      e.preventDefault();
      return;
    }

    const tag = el.tagName;
    if (tag !== 'INPUT' && tag !== 'SELECT' && tag !== 'TEXTAREA' && el.id !== 'btnSaveReturn') return;

    e.preventDefault();
    focusNextReturnField(el);
  });

  syncConditionStyle($('.condition-select').first());
  calcTotals();
  focusEl(document.getElementById('supplierSearch'));
});
</script>
@endsection
