@extends('layout.app')

@section('title', 'Edit Purchase Invoice')

@section('content')

<style>
  /* Select2 Styling */
  .select2-container--bootstrap-5 .select2-selection {
    border: 1px solid #ced4da !important;
    border-radius: 0.375rem;
    min-height: 31px !important;
  }
  .select2-container--bootstrap-5 .select2-selection__rendered {
    color: #212529;
    line-height: 1.5;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
  }
  
  /* Select2 Dropdown Styling */
  .select2-dropdown {
    border: 1px solid #ced4da;
    max-width: 500px !important;
  }
  .select2-results__option {
    font-size: 14px;
    padding: 6px 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .select2-results {
    max-height: 300px;
    overflow-y: auto;
  }
  
  /* Product Dropdown Styling */
  .productDropdown,
  #supplierDropdown {
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
    z-index: 1050 !important;
    border: 1px solid #ced4da;
    background: white;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    margin-top: 4px;
    min-width: 100%;
    width: auto;
    max-width: 500px;
  }
  
  .dropdown-item {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    font-size: 0.875rem;
    border-bottom: 1px solid #f0f0f0;
  }
  
  .dropdown-item:hover,
  .dropdown-item.active {
    background-color: #f8f9fa;
    color: #d9534f;
  }
  
  .dropdown-item:last-child {
    border-bottom: none;
  }
  
  .table-responsive {
    position: relative;
    overflow: visible !important;
  }
  
  @media print {
    .app-sidebar, .app-header, .app-content-header .breadcrumb, .btn, button, .no-print { display: none !important; }
    body, .app-main { margin: 0; padding: 0; }
    .card { box-shadow: none !important; border: none !important; }
  }
</style>

<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h3 class="mb-0">
            <i class="bi bi-pencil-square text-warning me-2"></i>Edit Purchase Invoice
          </h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ url('/purchaseSummary') }}">Purchase Summary</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Invoice</li>
          </ol>
        </div>
      </div>
      <hr class="mb-3" style="border-top: 2px solid #d9534f;">
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <!-- Invoice Header Card -->
      <div class="card shadow-sm mb-3">
        <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #d9534f 0%, #c0392b 100%);">
          <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Invoice Information</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <!-- Supplier -->
            <div class="col-md-4">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-semibold mb-0">
                  <i class="bi bi-shop text-danger me-1"></i>Supplier
                </label>
                <a href="javascript:void(0);" class="net-rate-link text-primary" id="addNetRateLink" title="Add purchase net rate for this supplier">
                  <i class="bi bi-plus-circle me-1"></i>Net Rate
                </a>
              </div>
              <div class="position-relative">
                <input type="text" 
                       class="form-control form-control-sm" 
                       id="supplierSearch" 
                       placeholder="Type to search supplier..."
                       autocomplete="off">
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
                            data-balance="{{ $supplier->custBalance }}">
                      {{ $label }}
                    </option>
                  @endforeach
                </select>
                <div id="supplierDropdown" class="dropdown-menu w-100" style="max-height: 300px; overflow-y: auto; display: none;"></div>
              </div>
            </div>

            <!-- Invoice No -->
            <div class="col-md-2">
              <label class="form-label fw-semibold">Invoice No</label>
              <input type="text" class="form-control form-control-sm" id="invoiceNo" value="{{ $invoice->invoiceNo }}" readonly>
            </div>

            <!-- Invoice Date -->
            <div class="col-md-2">
              <label class="form-label fw-semibold">Date</label>
              <input type="date" class="form-control form-control-sm" id="invoiceDate" value="{{ date('Y-m-d') }}">
            </div>
          </div>
        </div>
      </div>

      <!-- Products Grid Card -->
      <div class="card shadow-sm mb-3">
        <div class="card-header bg-light border-bottom">
          <h5 class="mb-0 text-dark">
            <i class="bi bi-cart-plus me-2 text-danger"></i>Products
          </h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0" id="productTable">
              <thead class="table-danger">
                <tr>
                  <th style="width: 42%">Product / Variant</th>
                  <th style="width: 12%" class="text-center">Qty</th>
                  <th style="width: 15%" class="text-end">Rate</th>
                  <th style="width: 12%" class="text-end">Discount %</th>
                  <th style="width: 16%" class="text-end">Amount</th>
                  <th style="width: 10%" class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td style="position: relative; vertical-align: top;">
                    <div style="position: relative; width: 100%;">
                      <div class="d-flex gap-2 align-items-start product-variant-line">
                        <div class="flex-grow-1" style="position: relative;">
                          <input type="text" 
                                 class="form-control form-control-sm productSearch" 
                                 placeholder="Type to search product..."
                                 autocomplete="off">
                          <select class="form-select form-select-sm d-none productCode">
                            <option value="">Select Product</option>
                            @php
                              $uniqueProducts = $products->unique('productCode');
                            @endphp
                            @foreach($uniqueProducts as $product)
                              <option value="{{ $product->productCode }}" 
                                      data-rate="{{ $product->purchaseRate ?? 0 }}"
                                      data-cost="{{ $product->costRate }}"
                                      data-name="{{ $product->productName }}"
                                      data-company="{{ $product->companyName }}">
                                {{ $product->productName }} - {{ $product->companyName }}
                              </option>
                            @endforeach
                          </select>
                          <div class="productDropdown dropdown-menu w-100" style="max-height: 300px; overflow-y: auto; display: none;"></div>
                        </div>
                        <div class="variantWrapper d-none" style="min-width:150px;">
                          <select class="form-select form-select-sm variantSelect">
                            <option value="">Variant</option>
                          </select>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td><input type="number" class="form-control form-control-sm text-center qty" value="1" min="1" placeholder="Qty"></td>
                  <td><input type="number" class="form-control form-control-sm text-end rate" value="" step="0.01" placeholder="Rate"></td>
                  <td><input type="number" class="form-control form-control-sm text-end discount" value="" step="0.01" min="0" max="100" placeholder="Disc %"></td>
                  <td><input type="text" class="form-control form-control-sm text-end amount" readonly value="" placeholder="Amount"></td>
                  <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group">
                      <button type="button" class="btn btn-success" onclick="addRow()" title="Add Row">
                        <i class="bi bi-plus-lg"></i>
                      </button>
                      <button type="button" class="btn btn-danger btnDeleteRow" title="Delete Row">
                        <i class="bi bi-trash3"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Bottom Section: Transport & Totals -->
      <div class="row">
        <div class="col-md-6">
          <div class="card shadow-sm">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-truck me-2"></i>Transport</h6>
            </div>
            <div class="card-body">
              <div class="row g-2">
                <div class="col-md-12">
                  <label class="form-label fw-semibold" for="transportCode">Transport</label>
                  <select class="form-select form-select-sm" id="transportCode">
                    <option value="">Select transport</option>
                    @foreach($transports as $transport)
                      @php
                        $transportLabel = trim((string) ($transport->bussnessName ?: $transport->custName));
                      @endphp
                      <option value="{{ $transport->custCode }}"
                              data-name="{{ $transportLabel }}"
                              data-business="{{ $transport->bussnessName ?? '' }}">
                        {{ $transportLabel }}
                      </option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold" for="vehicleNumber">Vehicle Number</label>
                  <input type="text" class="form-control form-control-sm" id="vehicleNumber" autocomplete="off">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold" for="dispatchNo">Dispatch No</label>
                  <input type="text" class="form-control form-control-sm" id="dispatchNo" autocomplete="off">
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold" for="transportWeight">Weight (KG)</label>
                  <input type="number" class="form-control form-control-sm" id="transportWeight" step="0.01" min="0">
                  <small class="text-muted" id="transportWeightHint">Auto from KG items. You can change it.</small>
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold" for="transportRate">Rate</label>
                  <input type="number" class="form-control form-control-sm" id="transportRate" step="0.01" min="0" placeholder="0">
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold" for="transportAmount">Amount</label>
                  <input type="text" class="form-control form-control-sm" id="transportAmount" readonly value="">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Totals -->
        <div class="col-md-6">
          <div class="card shadow-sm">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-calculator me-2"></i>Invoice Summary</h6>
            </div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-12">
                  <label class="form-label fw-semibold">No. of Items</label>
                  <input type="number" class="form-control" id="noOfItems" readonly value="1">
                </div>
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Invoice Total</label>
                  <input type="text" class="form-control fw-bold" id="invoiceTotal" readonly value="0.00">
                </div>
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Invoice Discount %</label>
                  <input type="number" class="form-control" id="invoiceDiscount" value="0" step="0.01" min="0" max="100">
                </div>
                <div class="col-md-12">
                  <label class="form-label fw-semibold text-danger">Grand Total</label>
                  <input type="text" class="form-control fw-bold text-danger fs-5" id="grandTotal" readonly value="0.00">
                </div>
                <div class="col-md-12 d-grid">
                  <button class="btn btn-warning btn-lg" id="btnSaveInvoice">
                    <i class="bi bi-arrow-repeat me-2"></i>Update Invoice
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
// Invoice data from server
const invoiceData = @json($invoice);
const invoiceItems = @json($invoice->items);

// Format function - globally accessible
function fmt(n) { return parseFloat(n || 0).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }

// Map of productCode => variants - globally accessible
window.productVariants = {
  @php
    $uniqueProducts = $products->unique('productCode');
  @endphp
  @foreach($uniqueProducts as $product)
    '{{ $product->productCode }}': [
      @foreach($product->variants as $variant)
      {
        id: {{ $variant->id }},
        name: '{{ $variant->variantName }}',
        value: '{{ $variant->variantValue }}',
        additionalPrice: {{ $variant->additionalPrice ?? 0 }},
        additionalCost: {{ $variant->additionalCost ?? 0 }},
        stockQty: {{ $variant->stockQty ?? 0 }}
      },
      @endforeach
    ],
  @endforeach
};
const productVariants = window.productVariants;

// Global variables for product search
let allProducts = [];
window.allProducts = allProducts;
let supplierProducts = [];
let selectedSupplierCode = '';

// Helper functions - globally accessible
function setupProductSearch($row) {
  const $searchInput = $row.find('.productSearch');
  const $productSelect = $row.find('.productCode');
  const $dropdown = $row.find('.productDropdown');
  const $variantWrapper = $row.find('.variantWrapper');
  const $variantSelect = $row.find('.variantSelect');
  
  function filterProducts(searchTerm) {
    const term = searchTerm.toLowerCase().trim();
    if(!term) {
      $dropdown.hide().empty();
      return;
    }
    
    let productsToFilter = [];
    if (selectedSupplierCode && supplierProducts.length > 0) {
      productsToFilter = supplierProducts;
    } else if (selectedSupplierCode && supplierProducts.length === 0) {
      $dropdown.html('<div class="dropdown-empty text-muted px-3 py-2">No products available for this supplier</div>').show();
      return;
    } else {
      productsToFilter = allProducts;
    }
    
    const filtered = productsToFilter.filter(p => 
      p.name.toLowerCase().includes(term) ||
      p.company.toLowerCase().includes(term) ||
      p.code.toLowerCase().includes(term)
    );
    
    if(filtered.length === 0) {
      $dropdown.html('<div class="dropdown-empty text-muted px-3 py-2">No products found</div>').show();
      return;
    }
    
    let html = '';
    filtered.slice(0, 10).forEach((p, idx) => {
      const rateDisplay = supplierProducts.length > 0 ? `Rs.${parseFloat(p.rate).toFixed(2)}` : '';
      html += `<a class="dropdown-item ${idx===0?'active':''}" href="#" data-code="${p.code}" data-rate="${p.rate}" data-name="${p.name}" data-company="${p.company}">
        <strong>${p.name}</strong> <span class="text-muted">- ${p.company}</span>
        ${rateDisplay ? `<span class="badge bg-info ms-2">${rateDisplay}</span>` : ''}
      </a>`;
    });
    $dropdown.html(html).show();
  }

  function hideProductDropdown() {
    $dropdown.hide();
  }
  
  $searchInput.on('input', function() {
    if ($productSelect.val()) {
      $productSelect.val('');
    }
    filterProducts($(this).val());
  });

  $searchInput.on('focus', function() {
    if($(this).val().trim()) filterProducts($(this).val());
  });

  $searchInput.on('keydown', function(e) {
    const $items = $dropdown.find('.dropdown-item[data-code]');
    const $active = $items.filter('.active');
    let newIndex = -1;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      if (!$items.length) return;
      if (!$active.length) newIndex = 0;
      else newIndex = Math.min($items.index($active) + 1, $items.length - 1);
      $items.removeClass('active').eq(newIndex).addClass('active');
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      if (!$active.length) return;
      newIndex = Math.max($items.index($active) - 1, 0);
      $items.removeClass('active').eq(newIndex).addClass('active');
    } else if (e.key === 'Enter') {
      e.preventDefault();
      e.stopPropagation();
      if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
      const $selected = $dropdown.is(':visible') ? $items.filter('.active') : $();
      const typed = String($(this).val() || '').trim();
      if ($selected.length > 0 && $selected.data('code')) {
        $selected.trigger('click');
      } else if ($productSelect.val()) {
        $row.find('.qty').focus().select();
      } else if (!typed) {
        hideProductDropdown();
        const transport = document.getElementById('transportCode');
        if (transport) transport.focus();
      } else {
        showWarning('Product not found', 'No product matches your search. Clear the field or try another name/code.');
        hideProductDropdown();
        $(this).focus();
      }
    } else if (e.key === 'Escape') {
      hideProductDropdown();
    }
  });

  // Direct click handler for dropdown items in this row
  $dropdown.on('click', 'a.dropdown-item', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const code = $(this).data('code');
    const rate = $(this).data('rate');
    const name = $(this).data('name');
    const company = $(this).data('company');
    
    $productSelect.val(code);
    $searchInput.val(name + ' - ' + company);
    $dropdown.hide().empty();
    
    $row.find('.rate').val(rate);
    $row.data('kgEach', kgEachFor(code));
    
    // Store base rate
    $row.data('baseRate', rate);
    
    // Find product in supplier products to get variants with rates
    const supplierProduct = supplierProducts.find(p => p.code === code);
    let variantsToUse = productVariants[code] || [];
    
    // If we have supplier-specific variants, filter to show only those
    if (supplierProduct && supplierProduct.variants && supplierProduct.variants.length > 0) {
      const supplierVariantIds = supplierProduct.variants.map(v => v.id);
      variantsToUse = variantsToUse.filter(v => supplierVariantIds.includes(v.id));
    }
    
    const $variantWrapper = $row.find('.variantWrapper');
    const $variantSelect = $row.find('.variantSelect');
    $variantSelect.empty().append('<option value="">Select Variant</option>');
    
    if(variantsToUse.length > 0){
      variantsToUse.forEach(v => {
        // Check if this variant has a special rate in supplier products
        let variantRate = v.additionalCost > 0 ? v.additionalCost : null;
        if (supplierProduct && supplierProduct.variants) {
          const supplierVariant = supplierProduct.variants.find(sv => sv.id === v.id);
          if (supplierVariant) {
            variantRate = supplierVariant.specialRate;
          }
        }
        
        const text = variantRate ? `${v.name}: ${v.value} (Rs.${variantRate})` : `${v.name}: ${v.value}`;
        $variantSelect.append(`<option value="${v.id}" data-variant-rate="${variantRate || 0}">${text}</option>`);
      });
      $variantWrapper.removeClass('d-none');
    } else {
      $variantWrapper.addClass('d-none');
    }
    
    calculateRowAmount($row);
    calculateTotals();
  });

  $(document).on('click', function(e) {
    if(!$(e.target).closest($searchInput).length && !$(e.target).closest($dropdown).length) {
      $dropdown.hide();
    }
  });
}

// Global variant change handler for all rows
$(document).on('change', '.variantSelect', function() {
  const $row = $(this).closest('tr');
  const baseRate = parseFloat($row.data('baseRate')) || 0;
  const selectedOption = $(this).find('option:selected');
  const variantRate = parseFloat(selectedOption.data('variant-rate')) || 0;
  const rateCell = $row.find('.rate');
  
  if($(this).val() && variantRate > 0) {
    rateCell.val(variantRate.toFixed(2));
  } else {
    rateCell.val(baseRate.toFixed(2));
  }
  
  calculateRowAmount($row);
  calculateTotals();
});

function calculateRowAmount($row) {
  const qty = parseFloat($row.find('.qty').val()) || 0;
  const rate = parseFloat($row.find('.rate').val()) || 0;
  const discount = parseFloat($row.find('.discount').val()) || 0;
  const subtotal = qty * rate;
  const discountAmount = subtotal * (discount / 100);
  const amount = subtotal - discountAmount;
  $row.find('.amount').val(amount.toFixed(2));
}

function calculateTotals() {
  let total = 0, itemCount = 0;
  $('#productTable tbody tr').each(function() {
    const amount = parseFloat($(this).find('.amount').val()) || 0;
    total += amount;
    if ($(this).find('.productCode').val()) itemCount++;
  });

  const discount = parseFloat($('#invoiceDiscount').val()) || 0;
  const discountAmount = total * (discount / 100);
  const grandTotal = total - discountAmount;

  $('#invoiceTotal').val(total.toFixed(2));
  $('#noOfItems').val(itemCount);
  $('#grandTotal').val(grandTotal.toFixed(2));
  applyTransportWeight();
}

let transportWeightEdited = false;

function kgEachFor(code) {
  const master = (allProducts || []).find(function (p) { return String(p.code) === String(code); });
  return master ? (parseFloat(master.kgEach) || 0) : 0;
}

function transportKgTotal() {
  let kg = 0;
  $('#productTable tbody tr').each(function () {
    if (!$(this).find('.productCode').val()) return;
    const qty = parseFloat($(this).find('.qty').val()) || 0;
    const each = parseFloat($(this).data('kgEach')) || 0;
    kg += qty * each;
  });
  return Math.round(kg * 100) / 100;
}

function applyTransportWeight() {
  const kg = transportKgTotal();
  const hint = document.getElementById('transportWeightHint');
  if (hint) {
    hint.textContent = kg > 0
      ? ('Auto from KG items: ' + kg + ' kg. You can change it.')
      : 'Auto from KG items. You can change it.';
  }
  if (!transportWeightEdited) {
    $('#transportWeight').val(kg > 0 ? kg : '');
  }
  calcTransportAmount();
}

function calcTransportAmount() {
  const rateRaw = $('#transportRate').val();
  if (rateRaw === '' || rateRaw === null) {
    $('#transportAmount').val('');
    return;
  }
  const weight = parseFloat($('#transportWeight').val()) || 0;
  const rate = parseFloat(rateRaw) || 0;
  $('#transportAmount').val((Math.round(weight * rate * 100) / 100).toFixed(2));
}

$(document).on('input', '#transportWeight', function () {
  transportWeightEdited = $(this).val().trim() !== '';
  calcTransportAmount();
});
$(document).on('input', '#transportRate', calcTransportAmount);

function addRow() {
  const newIndex = $('#productTable tbody tr').length;
  const newRow = `
    <tr>
      <td style="position: relative; vertical-align: top;">
        <div style="position: relative; width: 100%;">
          <div class="d-flex gap-2 align-items-start product-variant-line">
            <div class="flex-grow-1" style="position: relative;">
              <input type="text" 
                     class="form-control form-control-sm productSearch" 
                     placeholder="Type to search product..."
                     autocomplete="off">
              <select class="form-select form-select-sm d-none productCode">
                <option value="">Select Product</option>
                @php
                  $uniqueProducts = $products->unique('productCode');
                @endphp
                @foreach($uniqueProducts as $product)
                  <option value="{{ $product->productCode }}" 
                          data-rate="{{ $product->purchaseRate ?? 0 }}"
                          data-cost="{{ $product->costRate }}"
                          data-name="{{ $product->productName }}"
                          data-company="{{ $product->companyName }}">
                    {{ $product->productName }} - {{ $product->companyName }}
                  </option>
                @endforeach
              </select>
              <div class="productDropdown dropdown-menu w-100" style="max-height: 300px; overflow-y: auto; display: none;"></div>
            </div>
            <div class="variantWrapper d-none" style="min-width:150px;">
              <select class="form-select form-select-sm variantSelect">
                <option value="">Variant</option>
              </select>
            </div>
          </div>
        </div>
      </td>
      <td><input type="number" class="form-control form-control-sm text-center qty" value="1" min="1" placeholder="Qty"></td>
      <td><input type="number" class="form-control form-control-sm text-end rate" value="" step="0.01" placeholder="Rate"></td>
      <td><input type="number" class="form-control form-control-sm text-end discount" value="" step="0.01" min="0" max="100" placeholder="Disc %"></td>
      <td><input type="text" class="form-control form-control-sm text-end amount" readonly value="" placeholder="Amount"></td>
      <td class="text-center">
        <div class="btn-group btn-group-sm" role="group">
          <button type="button" class="btn btn-success" onclick="addRow()" title="Add Row">
            <i class="bi bi-plus-lg"></i>
          </button>
          <button type="button" class="btn btn-danger btnDeleteRow" title="Delete Row">
            <i class="bi bi-trash3"></i>
          </button>
        </div>
      </td>
    </tr>
  `;
  $('#productTable tbody').append(newRow);
  const $newRow = $('#productTable tbody tr:last');
  
  // Small delay to ensure DOM is ready
  setTimeout(() => {
    setupProductSearch($newRow);
    initializeRowEvents($newRow);
    calculateTotals();
  }, 50);
}

function initializeRowEvents($row) {
  $row.find('.qty, .rate, .discount').on('input', function() {
    calculateRowAmount($row);
    calculateTotals();
  });

  $row.find('.btnDeleteRow').on('click', function() {
    $row.remove();
    calculateTotals();
  });
}

function updateProductsForSupplier(supplierCode) {
  selectedSupplierCode = supplierCode;
  
  if (!supplierCode) {
    supplierProducts = [];
    return;
  }
  
  $.get(`{{ url('/purchase/invoice/supplier-products') }}/${encodeURIComponent(supplierCode)}`)
    .done(function(data) {
      supplierProducts = (data.products || []).map(p => ({
        code: p.productCode,
        name: p.productName,
        company: p.companyName,
        rate: p.specialRate || p.purchaseRate,
        cost: p.specialRate || p.purchaseRate,
        display: p.productName + ' - ' + p.companyName,
        variants: p.variants || [],
        hasSpecialRate: p.hasSpecialRate || false
      }));
    })
    .fail(function() {
      supplierProducts = [];
    });
}
window.updateProductsForSupplier = updateProductsForSupplier;

$(document).ready(function() {

  // ---- SUPPLIER SEARCH WITH DROPDOWN ----
  const allSuppliers = [
    @foreach($suppliers as $supplier)
    @php
      $biz = trim((string) ($supplier->bussnessName ?: $supplier->custName));
      $city = trim((string) ($supplier->custCity ?? ''));
      $label = $city !== '' ? $biz.' — '.$city : $biz;
    @endphp
    {
      code: @json($supplier->custCode),
      name: @json($supplier->custName),
      business: @json($supplier->bussnessName ?? ''),
      city: @json($supplier->custCity ?? ''),
      label: @json($label),
      balance: {{ $supplier->custBalance ?? 0 }}
    },
    @endforeach
  ];

  const $supplierSearch = $('#supplierSearch');
  const $supplierSelect = $('#supplierCode');
  const $supplierDropdown = $('#supplierDropdown');

  function filterSuppliers(term) {
    term = term.toLowerCase().trim();
    if(!term) {
      $supplierDropdown.hide().empty();
      return;
    }
    
    const filtered = allSuppliers.filter(s => 
      (s.name || '').toLowerCase().includes(term) ||
      (s.business || '').toLowerCase().includes(term) ||
      (s.code || '').toLowerCase().includes(term) ||
      (s.city || '').toLowerCase().includes(term) ||
      (s.label || '').toLowerCase().includes(term)
    );
    
    if(filtered.length === 0) {
      $supplierDropdown.html('<div class="dropdown-item text-muted">No suppliers found</div>').show();
      return;
    }
    
    let html = '';
    filtered.slice(0, 10).forEach((s, idx) => {
      const title = (s.business || s.name || '').trim();
      const sub = [s.city, s.business && s.name ? s.name : ''].filter(Boolean).join(' · ');
      const label = (s.label || title).replace(/"/g, '&quot;');
      html += `<a class="dropdown-item ${idx===0?'active':''}" href="#" data-code="${s.code}" data-name="${label}">
        <strong>${title}</strong>
        <div class="text-muted" style="font-size:0.75rem;">${sub || s.code}</div>
      </a>`;
    });
    $supplierDropdown.html(html).show();
  }

  $supplierSearch.on('input', function() {
    filterSuppliers($(this).val());
  });

  $supplierSearch.on('focus', function() {
    if($(this).val().trim()) filterSuppliers($(this).val());
  });

  $supplierDropdown.on('click', 'a', function(e) {
    e.preventDefault();
    const code = $(this).data('code');
    const name = $(this).data('name');
    $supplierSelect.val(code);
    $supplierSearch.val(name);
    $supplierDropdown.hide().empty();
    updateProductsForSupplier(code);
  });

  $(document).on('click', function(e) {
    if(!$(e.target).closest('#supplierSearch, #supplierDropdown').length) {
      $supplierDropdown.hide();
    }
  });

  // Initialize allProducts array
  allProducts = [
    @php
      $uniqueProducts = $products->unique('productCode');
    @endphp
    @foreach($uniqueProducts as $product)
    {
      code: '{{ $product->productCode }}',
      name: '{{ $product->productName }}',
      company: '{{ $product->companyName }}',
      rate: {{ $product->purchaseRate ?? 0 }},
      cost: {{ $product->costRate }},
      kgEach: {{ $product->kgEach() }},
      display: '{{ $product->productName }} - {{ $product->companyName }}',
      hasVariants: {{ $product->variants->count() > 0 ? 'true' : 'false' }}
    },
    @endforeach
  ];
  window.allProducts = allProducts;

  // Load existing invoice data
  $('#supplierCode').val(invoiceData.supplierCode);
  $('#supplierSearch').val($supplierSelect.find(':selected').data('name') || '');
  $('#invoiceDate').val(invoiceData.invoiceDate);
  $('#transportCode').val(invoiceData.transportCode || '');
  $('#vehicleNumber').val(invoiceData.vehicleNumber || '');
  $('#dispatchNo').val(invoiceData.dispatchNo || '');
  if (invoiceData.transportRate !== null && invoiceData.transportRate !== '') {
    $('#transportRate').val(invoiceData.transportRate);
  }
  $('#invoiceDiscount').val(invoiceData.invoiceDiscount || 0);

  // Load existing items
  if (invoiceItems && invoiceItems.length > 0) {
    $('#productTable tbody').empty();
    invoiceItems.forEach((item, index) => {
      const newRow = `
        <tr>
          <td>
            <div class="position-relative">
              <div class="d-flex gap-2 align-items-start product-variant-line">
                <div class="flex-grow-1">
                  <input type="text" 
                         class="form-control form-control-sm productSearch" 
                         placeholder="Type to search product..."
                         autocomplete="off"
                         value="${item.productName}">
                  <select class="form-select form-select-sm d-none productCode">
                    <option value="">Select Product</option>
                    @php
                      $uniqueProducts = $products->unique('productCode');
                    @endphp
                    @foreach($uniqueProducts as $product)
                      <option value="{{ $product->productCode }}" 
                              data-rate="{{ $product->purchaseRate ?? 0 }}"
                              data-cost="{{ $product->costRate }}"
                              data-name="{{ $product->productName }}"
                              data-company="{{ $product->companyName }}">
                        {{ $product->productName }} - {{ $product->companyName }}
                      </option>
                    @endforeach
                  </select>
                  <div class="productDropdown dropdown-menu w-100" style="max-height: 300px; overflow-y: auto; display: none;"></div>
                </div>
                <div class="variantWrapper d-none" style="min-width:150px;">
                  <select class="form-select form-select-sm variantSelect">
                    <option value="">Variant</option>
                  </select>
                </div>
              </div>
            </div>
          </td>
          <td><input type="number" class="form-control form-control-sm text-center qty" value="${item.qty}" min="1" placeholder="Qty"></td>
          <td><input type="number" class="form-control form-control-sm text-end rate" value="${item.rate}" step="0.01" placeholder="Rate"></td>
          <td><input type="number" class="form-control form-control-sm text-end discount" value="${item.discount || 0}" step="0.01" min="0" max="100" placeholder="Disc %"></td>
          <td><input type="text" class="form-control form-control-sm text-end amount" readonly value="${parseFloat(item.amount || 0).toFixed(2)}" placeholder="Amount"></td>
          <td class="text-center">
            <div class="btn-group btn-group-sm" role="group">
              <button type="button" class="btn btn-success" onclick="addRow()" title="Add Row">
                <i class="bi bi-plus-lg"></i>
              </button>
              <button type="button" class="btn btn-danger btnDeleteRow" title="Delete Row">
                <i class="bi bi-trash3"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
      $('#productTable tbody').append(newRow);
    });

    // Setup all existing rows
    $('#productTable tbody tr').each(function() {
      const $row = $(this);
      const loaded = invoiceItems[$('#productTable tbody tr').index($row)];
      $row.find('.productCode').val(loaded.productCode);
      $row.data('kgEach', kgEachFor(loaded.productCode));
      setupProductSearch($row);
      initializeRowEvents($row);
    });
  }

  // Set initial supplier and load its products
  updateProductsForSupplier(invoiceData.supplierCode);

  // Supplier change handler
  $supplierSelect.on('change', function() {
    updateProductsForSupplier($(this).val());
  });

  // Invoice discount change
  $('#invoiceDiscount').on('input', calculateTotals);

  // Initialize product rows
  $('#productTable tbody tr').each(function() {
    setupProductSearch($(this));
    initializeRowEvents($(this));
  });

  (function () {
    const saved = invoiceData.weight;
    const calc = transportKgTotal();
    if (saved !== null && saved !== '' && Math.abs(parseFloat(saved) - calc) > 0.009) {
      transportWeightEdited = true;
      $('#transportWeight').val(saved);
    }
  })();
  calculateTotals();

  // Save Invoice
  $('#btnSaveInvoice').on('click', function() {
    const items = [];
    $('#productTable tbody tr').each(function() {
      const $row = $(this);
      const row = {
        productCode: $row.find('.productCode').val(),
        productName: $row.find('.productSearch').val(),
        variant_id: $row.find('.variantSelect').val() || null,
        variant_name: $row.find('.variantSelect :selected').data('name') || null,
        variant_value: $row.find('.variantSelect :selected').data('value') || null,
        variant_added_price: parseFloat($row.find('.variantSelect :selected').data('price') || 0),
        qty: parseFloat($row.find('.qty').val()) || 0,
        rate: parseFloat($row.find('.rate').val()) || 0,
        discount: parseFloat($row.find('.discount').val()) || 0,
        amount: parseFloat($row.find('.amount').val()) || 0
      };
      if (row.productCode) items.push(row);
    });

    if (items.length === 0) {
      showError('Error', 'Please add at least one product');
      return;
    }

    const data = {
      _token: '{{ csrf_token() }}',
      invoiceNo: $('#invoiceNo').val(),
      invoiceDate: $('#invoiceDate').val(),
      supplierCode: $('#supplierCode').val(),
      supplierName: $('#supplierSearch').val(),
      transportCode: $('#transportCode').val() || null,
      transportName: $('#transportCode option:selected').data('name') || null,
      vehicleNumber: $('#vehicleNumber').val() || null,
      dispatchNo: $('#dispatchNo').val() || null,
      weight: $('#transportWeight').val() === '' ? null : (parseFloat($('#transportWeight').val()) || 0),
      transportRate: $('#transportRate').val() === '' ? null : (parseFloat($('#transportRate').val()) || 0),
      transportAmount: $('#transportAmount').val() === '' ? null : (parseFloat($('#transportAmount').val()) || 0),
      noOfItems: parseInt($('#noOfItems').val()),
      invoiceTotal: parseFloat($('#invoiceTotal').val()),
      invoiceDiscount: parseFloat($('#invoiceDiscount').val()) || 0,
      grandTotal: parseFloat($('#grandTotal').val()),
      items: items
    };

    $.ajax({
      url: `{{ url('/purchase/invoice/update') }}/${invoiceData.invoiceNo}`,
      method: 'PUT',
      data: data,
      success: function(res) {
        if (res.success) {
          showSuccess('Invoice updated successfully!', `Invoice No: ${res.invoiceNo}`);
          setTimeout(() => window.location.href = `{{ url('/purchase/invoice') }}/${res.invoiceNo}/preview`, 2000);
        } else {
          showError('Failed to update', res.message || '');
        }
      },
      error: function(xhr) {
        console.error('Error:', xhr);
        showError('Failed to update', xhr.responseJSON?.message || '');
      }
    });
  });
});
</script>

@include('partials.purchase.net-rate-modal')
@endsection
