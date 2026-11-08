@extends('layout.app')

@section('title', 'Edit Sale Invoice')

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
            <i class="bi bi-pencil-square text-warning me-2"></i>Edit Sale Invoice
          </h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ url('/saleSummary') }}">Sale Summary</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Invoice</li>
          </ol>
        </div>
      </div>
      <hr class="mb-3" style="border-top: 2px solid #0d6efd;">
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <!-- Invoice Header Card -->
      <div class="card shadow-sm mb-3">
        <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
          <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Invoice Information</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <!-- Customer -->
            <div class="col-md-3">
              <label class="form-label fw-semibold">
                <i class="bi bi-person-badge text-primary me-1"></i>Customer
              </label>
              <select class="form-select form-select-sm select2" id="customerCode">
                <option value="">Select Customer</option>
                @foreach($customers as $customer)
                  @php
                    $biz = trim((string) ($customer->bussnessName ?: $customer->custName));
                    $city = trim((string) ($customer->custCity ?? ''));
                    $label = $city !== '' ? $biz.' — '.$city : $biz;
                  @endphp
                  <option value="{{ $customer->custCode }}"
                          data-city="{{ $customer->custCity }}"
                          data-name="{{ $customer->custName }}"
                          data-business="{{ $customer->bussnessName ?? '' }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <!-- City -->
            <div class="col-md-2">
              <label class="form-label fw-semibold">City</label>
              <input type="text" class="form-control form-control-sm" id="city" readonly>
            </div>

            <!-- Balance -->
            <div class="col-md-2">
              <label class="form-label fw-semibold">Balance</label>
              <input type="text" class="form-control form-control-sm" id="balance" readonly>
            </div>

            <!-- Invoice No -->
            <div class="col-md-2">
              <label class="form-label fw-semibold">Invoice No</label>
              <input type="text" class="form-control form-control-sm" id="invoiceNo" value="{{ $invoice->invoiceNo }}" readonly>
            </div>

            <!-- Invoice Date -->
            <div class="col-md-1">
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
            <i class="bi bi-cart-plus me-2 text-success"></i>Products
          </h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0" id="productTable">
              <thead class="table-primary">
                <tr>
                  <th style="width: 35%">Product</th>
                  <th style="width: 12%" class="text-center">Qty</th>
                  <th style="width: 15%" class="text-end">Rate</th>
                  <th style="width: 12%" class="text-end">Discount %</th>
                  <th style="width: 16%" class="text-end">Amount</th>
                  <th style="width: 10%" class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <select class="form-select form-select-sm select2-product productCode">
                      <option value="">Select Product</option>
                      @foreach($products as $product)
                        <option value="{{ $product->productCode }}" data-rate="{{ $product->saleRate }}">{{ $product->productName }} - {{ $product->companyName }}</option>
                      @endforeach
                    </select>
                  </td>
                  <td><input type="number" class="form-control form-control-sm text-center qty" value="1" min="1"></td>
                  <td><input type="number" class="form-control form-control-sm text-end rate" value="0" step="0.01"></td>
                  <td><input type="number" class="form-control form-control-sm text-end discount" value="0" step="0.01" min="0" max="100"></td>
                  <td><input type="text" class="form-control form-control-sm text-end amount" readonly value="0.00"></td>
                  <td class="text-center">
                    <button type="button" class="btn btn-sm btn-success p-1 px-2 me-1" onclick="addRow()" title="Add Row">
                      <i class="bi bi-plus-lg"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger p-1 px-2 btnDeleteRow" title="Delete Row">
                      <i class="bi bi-trash3"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Bottom Section: Additional Info & Totals -->
      <div class="row">
        <!-- Left: Additional Information -->
        <div class="col-md-6">
          <div class="card shadow-sm">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Additional Information</h6>
            </div>
            <div class="card-body">
              <div class="row g-2">
                <div class="col-md-8">
                  <label class="form-label fw-semibold">Guds Name</label>
                  <div class="input-group input-group-sm">
                    <select class="form-select form-select-sm" id="gudsName">
                      <option value="">Select Guds</option>
                      @foreach($guds as $gud)
                        <option value="{{ $gud->gudsName }}" data-expanse="{{ $gud->gudsExpanse }}" data-loader="{{ $gud->gudsLoader }}">{{ $gud->gudsName }}</option>
                      @endforeach
                    </select>
                    <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#gudsModal" title="Add New Guds">
                      <i class="bi bi-plus-lg"></i>
                    </button>
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold">No. of Corten</label>
                  <input type="number" class="form-control form-control-sm" id="corten" value="0">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Bilty Expense</label>
                  <input type="number" class="form-control form-control-sm" id="biltyExpanse" value="0" step="0.01">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Loader Expense</label>
                  <input type="number" class="form-control form-control-sm" id="loaderExpanse" value="0" step="0.01">
                </div>
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Bilty Balance</label>
                  <input type="text" class="form-control form-control-sm" id="biltyBalance" readonly value="0.00">
                </div>
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Comments</label>
                  <textarea class="form-control form-control-sm" id="comments" rows="2"></textarea>
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
                  <label class="form-label fw-semibold">Invoice discount <span class="text-muted fw-normal">(amount Rs.)</span></label>
                  <input type="number" class="form-control" id="invoiceDiscount" value="0" step="0.01" min="0">
                </div>
                <div class="col-md-12">
                  <label class="form-label fw-semibold text-success">Grand Total</label>
                  <input type="text" class="form-control fw-bold text-success fs-5" id="grandTotal" readonly value="0.00">
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

<!-- Guds Modal -->
<div class="modal fade" id="gudsModal" tabindex="-1" aria-labelledby="gudsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="gudsModalLabel"><i class="bi bi-truck me-2"></i>Add New Guds</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="gudsForm">
          <div class="mb-3">
            <label for="modalGudsName" class="form-label fw-semibold">Guds Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="modalGudsName" required>
          </div>
          <div class="mb-3">
            <label for="modalGudsExpanse" class="form-label fw-semibold">Guds Expense <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="modalGudsExpanse" value="0" step="0.01" min="0" required>
          </div>
          <div class="mb-3">
            <label for="modalGudsLoader" class="form-label fw-semibold">Guds Loader <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="modalGudsLoader" value="0" step="0.01" min="0" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="btnSaveGuds">
          <i class="bi bi-save me-1"></i>Save Guds
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Invoice data from server
let invoiceData = {};
try {
  // Use htmlspecialchars to safely encode the JSON
  const jsonString = '<?php echo json_encode($invoiceData ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES); ?>';
  invoiceData = JSON.parse(jsonString || '{}');
} catch(e) {
  console.error('Error parsing invoice data:', e);
  console.error('Raw data:', '<?php echo json_encode($invoiceData ?? []); ?>');
  invoiceData = {};
}

// Immediate debugging before document ready
console.log('================================');
console.log('RAW Invoice Data:', invoiceData);
console.log('Type of invoiceData:', typeof invoiceData);
console.log('invoiceData.id:', invoiceData?.id);
console.log('invoiceData.invoiceNo:', invoiceData?.invoiceNo);
console.log('invoiceData.customerCode:', invoiceData?.customerCode);
console.log('invoiceData.items property exists:', 'items' in invoiceData);
console.log('invoiceData.items value:', invoiceData?.items);
console.log('invoiceData keys:', Object.keys(invoiceData || {}));
console.log('================================');

$(document).ready(function() {
  // Initialize Select2
  $('#customerCode').select2({
    theme: 'bootstrap-5',
    placeholder: 'Select Customer',
    width: 'resolve'
  });

  // Initialize first product row Select2
  $('#productTable tbody tr:first .productCode').select2({
    theme: 'bootstrap-5',
    placeholder: 'Select Product',
    width: 'resolve'
  });

  // Load invoice data
  loadInvoiceData();

  // Number formatter
  function fmt(n) {
    return parseFloat(n || 0).toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2});
  }

  // Load customer details
  $('#customerCode').on('change', function(){
    const customerCode = $(this).val();
    if(!customerCode) {
      $('#city').val('');
      $('#balance').val('');
      return;
    }

    const city = $(this).find('option:selected').data('city');
    $('#city').val(city || '');

    // Load balance from server
    $.get(`{{ url('/sale/customer') }}/${customerCode}`)
      .done(res => {
        $('#balance').val(fmt(res.balance || 0));
      })
      .fail(err => {
        console.error('Error:', err);
        $('#balance').val('0.00');
      });
  });

  // Load product details when selected
  $(document).on('change', '.productCode', function(){
    const productCode = $(this).val();
    const row = $(this).closest('tr');

    if (productCode) {
      let duplicate = false;
      $('#productTable tbody tr').each(function () {
        if (this === row[0]) return;
        if (String($(this).find('.productCode').val() || '') === String(productCode)) duplicate = true;
      });
      if (duplicate) {
        showWarning('Duplicate product', 'This product is already added.');
        $(this).val('').trigger('change');
        return;
      }
    }
    
    if(!productCode) {
      row.find('.rate').val('0');
      calculateRowAmount(row);
      return;
    }

    const rate = $(this).find('option:selected').data('rate') || 0;
    row.find('.rate').val(rate);
    calculateRowAmount(row);
  });

  // Calculate row amount
  function calculateRowAmount(row) {
    const qty = parseFloat(row.find('.qty').val()) || 0;
    const rate = parseFloat(row.find('.rate').val()) || 0;
    const discount = parseFloat(row.find('.discount').val()) || 0;
    
    const amount = (qty * rate) * (1 - discount / 100);
    row.find('.amount').val(amount.toFixed(2));
    updateTotals();
  }

  // Update totals
  function updateTotals() {
    let total = 0;
    let itemCount = 0;
    
    $('#productTable tbody tr').each(function(){
      const amount = parseFloat($(this).find('.amount').val()) || 0;
      const productCode = $(this).find('.productCode').val();
      if(productCode) itemCount++;
      total += amount;
    });
    
    $('#noOfItems').val(itemCount);
    $('#invoiceTotal').val(total.toFixed(2));
    updateGrandTotal();
  }

  // Update grand total
  function updateGrandTotal() {
    const invoiceTotal = parseFloat($('#invoiceTotal').val()) || 0;
    let d = parseFloat($('#invoiceDiscount').val()) || 0;
    if (d < 0) d = 0;
    if (d > invoiceTotal) {
      d = invoiceTotal;
      $('#invoiceDiscount').val(d.toFixed(2));
    }
    $('#grandTotal').val((invoiceTotal - d).toFixed(2));
  }

  // Calculate bilty balance
  function calculateBiltyBalance() {
    const corten = parseFloat($('#corten').val()) || 0;
    const biltyExpanse = parseFloat($('#biltyExpanse').val()) || 0;
    const loaderExpanse = parseFloat($('#loaderExpanse').val()) || 0;
    
    const biltyBalance = (corten * biltyExpanse) - loaderExpanse;
    $('#biltyBalance').val(biltyBalance.toFixed(2));
  }

  // Event listeners
  $(document).on('input', '.qty, .rate, .discount', function(){
    calculateRowAmount($(this).closest('tr'));
  });

  $('#invoiceDiscount').on('input', updateGrandTotal);
  $('#corten, #biltyExpanse, #loaderExpanse').on('input', calculateBiltyBalance);

  // Store guds loader rate
  let gudsLoaderRate = 0;

  // Load guds expenses when selected
  $('#gudsName').on('change', function(){
    const selectedOption = $(this).find('option:selected');
    const gudsExpanse = selectedOption.data('expanse') || 0;
    gudsLoaderRate = selectedOption.data('loader') || 0;
    
    $('#biltyExpanse').val(gudsExpanse);
    
    // Calculate loader expense based on corten quantity
    const corten = parseFloat($('#corten').val()) || 0;
    const loaderExpanse = corten * gudsLoaderRate;
    $('#loaderExpanse').val(loaderExpanse.toFixed(2));
    
    calculateBiltyBalance();
  });

  // Update loader expense when corten changes
  $('#corten').on('input', function(){
    const corten = parseFloat($(this).val()) || 0;
    const loaderExpanse = corten * gudsLoaderRate;
    $('#loaderExpanse').val(loaderExpanse.toFixed(2));
    calculateBiltyBalance();
  });

  // Delete row
  $(document).on('click', '.btnDeleteRow', function(){
    const tbody = $('#productTable tbody');
    if(tbody.find('tr').length > 1) {
      $(this).closest('tr').remove();
      updateTotals();
    } else {
      showWarning('At least one product row is required');
    }
  });

  // Save invoice
  $('#btnSaveInvoice').on('click', function(){
    const customerCode = $('#customerCode').val();
    if(!customerCode){
      showWarning('Please select a customer');
      return;
    }

    // Collect items
    const items = [];
    let hasError = false;

    $('#productTable tbody tr').each(function(){
      const productCode = $(this).find('.productCode').val();
      if(!productCode) return;

      const qty = parseInt($(this).find('.qty').val());
      if(qty <= 0){
        showWarning('Quantity must be greater than 0');
        hasError = true;
        return false;
      }

      const productName = $(this).find('.productCode option:selected').text();

      items.push({
        productCode: productCode,
        productName: productName,
        qty: qty,
        rate: parseFloat($(this).find('.rate').val()),
        discount: parseFloat($(this).find('.discount').val()) || 0,
        amount: parseFloat($(this).find('.amount').val())
      });
    });

    if(hasError) return;

    if(items.length === 0){
      showWarning('Please add at least one product');
      return;
    }

    const $custOpt = $('#customerCode option:selected');
    const customerName = ($custOpt.data('business') || $custOpt.data('name') || $custOpt.text() || '').toString().trim();

    const invoiceTotal = parseFloat($('#invoiceTotal').val()) || 0;
    let discountAmount = parseFloat($('#invoiceDiscount').val()) || 0;
    if (discountAmount < 0) discountAmount = 0;
    if (discountAmount > invoiceTotal) discountAmount = invoiceTotal;

    const data = {
      _token: '{{ csrf_token() }}',
      invoiceNo: $('#invoiceNo').val(),
      invoiceDate: $('#invoiceDate').val(),
      customerCode: customerCode,
      customerName: customerName,
      city: $('#city').val(),
      balance: parseFloat($('#balance').val().replace(/,/g, '')) || 0,
      gudsName: $('#gudsName').val(),
      corten: parseInt($('#corten').val()) || 0,
      biltyExpanse: parseFloat($('#biltyExpanse').val()) || 0,
      loaderExpanse: parseFloat($('#loaderExpanse').val()) || 0,
      biltyBalance: parseFloat($('#biltyBalance').val()) || 0,
      comments: $('#comments').val(),
      noOfItems: parseInt($('#noOfItems').val()),
      invoiceTotal: invoiceTotal,
      invoiceDiscount: discountAmount,
      grandTotal: parseFloat($('#grandTotal').val()),
      items: items
    };

    // Add _method for Laravel PUT route handling
    data._method = 'PUT';

    $.ajax({
      url: '{{ url("/sale/invoice/update") }}/' + invoiceData.invoiceNo,
      method: 'POST',
      data: data,
      dataType: 'json',
      success: function(res){
        if(res.success){
          showSuccess('Invoice updated successfully!', `Invoice No: ${res.invoiceNo}`);
          setTimeout(() => window.location.href = '{{ url("/saleSummary") }}', 2000);
        } else {
          showError('Failed to update', res.message || '');
        }
      },
      error: function(xhr){
        console.error('Error:', xhr);
        const errorMsg = xhr.responseJSON?.message || xhr.responseText || 'Unknown error occurred';
        showError('Failed to update', errorMsg);
      }
    });
  });

  // Function to load invoice data into form
  function loadInvoiceData() {
    console.log('=== LOADING INVOICE DATA ===');
    console.log('Full Invoice Data:', invoiceData); 
    console.log('Invoice ID:', invoiceData.id);
    console.log('Invoice No:', invoiceData.invoiceNo);
    console.log('Customer Code:', invoiceData.customerCode);
    console.log('Customer Name:', invoiceData.customerName);
    console.log('Items:', invoiceData.items);
    console.log('Items Type:', typeof invoiceData.items);
    console.log('Items Length:', invoiceData.items ? invoiceData.items.length : 'null/undefined');
    
    // Set invoice details first (these don't depend on AJAX)
    $('#invoiceNo').val(invoiceData.invoiceNo);
    $('#invoiceDate').val(invoiceData.invoiceDate);
    
    // Set guds and additional info
    $('#gudsName').val(invoiceData.gudsName || '');
    $('#corten').val(invoiceData.corten || 0);
    $('#biltyExpanse').val(parseFloat(invoiceData.biltyExpanse) || 0);
    $('#loaderExpanse').val(parseFloat(invoiceData.loaderExpanse) || 0);
    $('#biltyBalance').val(parseFloat(invoiceData.biltyBalance) || 0);
    $('#comments').val(invoiceData.comments || '');
    
    // Set customer FIRST - this is critical
    console.log('Setting customer code:', invoiceData.customerCode);
    $('#customerCode').val(invoiceData.customerCode);
    
    // Trigger change to load customer details via AJAX if needed
    setTimeout(() => {
      $('#customerCode').trigger('change');
    }, 100);
    
    $('#city').val(invoiceData.city || '');
    $('#balance').val(fmt(parseFloat(invoiceData.balance) || 0));
    
    // Clear product table rows
    $('#productTable tbody').empty();
    
    // Add invoice items - handle both array and object format
    let items = [];
    if (invoiceData.items) {
      if (Array.isArray(invoiceData.items)) {
        items = invoiceData.items;
      } else if (typeof invoiceData.items === 'object') {
        // In case items is an object, convert to array
        items = Object.values(invoiceData.items);
      }
    }
    
    console.log('Processed Items:', items);
    console.log('Items count after processing:', items.length); 
    
    if(items.length > 0) {
      // Add all items sequentially
      items.forEach((item, index) => {
        console.log(`Processing item ${index}:`, item);
        
        // Create row HTML
        const rowHTML = `
          <tr>
            <td>
              <select class="form-select form-select-sm productCode">
                <option value="">Select Product</option>
                @foreach($products as $product)
                  <option value="{{ $product->productCode }}" data-rate="{{ $product->saleRate }}">{{ $product->productName }} - {{ $product->companyName }}</option>
                @endforeach
              </select>
            </td>
            <td><input type="number" class="form-control form-control-sm text-center qty" value="1" min="1"></td>
            <td><input type="number" class="form-control form-control-sm text-end rate" value="0" step="0.01"></td>
            <td><input type="number" class="form-control form-control-sm text-end discount" value="0" step="0.01" min="0" max="100"></td>
            <td><input type="text" class="form-control form-control-sm text-end amount" readonly value="0.00"></td>
            <td class="text-center">
              <button type="button" class="btn btn-sm btn-success p-1 px-2 me-1" onclick="addRow()" title="Add Row">
                <i class="bi bi-plus-lg"></i>
              </button>
              <button type="button" class="btn btn-sm btn-danger p-1 px-2 btnDeleteRow" title="Delete Row">
                <i class="bi bi-trash3"></i>
              </button>
            </td>
          </tr>
        `;
        
        $('#productTable tbody').append(rowHTML);
        const row = $('#productTable tbody tr:last');
        
        // Initialize Select2 for this row
        row.find('.productCode').select2({
          theme: 'bootstrap-5',
          placeholder: 'Select Product',
          width: 'resolve'
        });
        
        // Set item values
        console.log('Setting item', index, 'values - Product:', item.productCode, 'Qty:', item.qty, 'Rate:', item.rate, 'Amount:', item.amount);
        row.find('.productCode').val(item.productCode).trigger('change');
        row.find('.qty').val(item.qty);
        row.find('.rate').val(parseFloat(item.rate) || 0);
        row.find('.discount').val(parseFloat(item.discount) || 0);
        row.find('.amount').val(parseFloat(item.amount) || 0);
      });
      
      // Update totals after all items are added
      console.log('All items loaded, updating totals');
      setTimeout(() => {
        updateTotals();
        const dAmt = parseFloat(invoiceData.invoiceDiscount) || 0;
        $('#invoiceDiscount').val(dAmt.toFixed(2));
        updateGrandTotal();
      }, 200);
    } else {
      console.warn('No items found, adding empty row');
      // Add at least one empty row if no items
      const emptyRow = `
        <tr>
          <td>
            <select class="form-select form-select-sm productCode">
              <option value="">Select Product</option>
              @foreach($products as $product)
                <option value="{{ $product->productCode }}" data-rate="{{ $product->saleRate }}">{{ $product->productName }} - {{ $product->companyName }}</option>
              @endforeach
            </select>
          </td>
          <td><input type="number" class="form-control form-control-sm text-center qty" value="1" min="1"></td>
          <td><input type="number" class="form-control form-control-sm text-end rate" value="0" step="0.01"></td>
          <td><input type="number" class="form-control form-control-sm text-end discount" value="0" step="0.01" min="0" max="100"></td>
          <td><input type="text" class="form-control form-control-sm text-end amount" readonly value="0.00"></td>
          <td class="text-center">
            <button type="button" class="btn btn-sm btn-success p-1 px-2 me-1" onclick="addRow()" title="Add Row">
              <i class="bi bi-plus-lg"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger p-1 px-2 btnDeleteRow" title="Delete Row">
              <i class="bi bi-trash3"></i>
            </button>
          </td>
        </tr>
      `;
      $('#productTable tbody').append(emptyRow);
      const row = $('#productTable tbody tr:first');
      row.find('.productCode').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select Product',
        width: 'resolve'
      });
      
      updateTotals();
      const dAmtEmpty = parseFloat(invoiceData.invoiceDiscount) || 0;
      $('#invoiceDiscount').val(dAmtEmpty.toFixed(2));
      updateGrandTotal();
    }
    
    console.log('=== INVOICE DATA LOADING COMPLETE ===');
  }

  // Save Guds
  $('#btnSaveGuds').on('click', function(){
    const gudsName = $('#modalGudsName').val().trim();
    const gudsExpanse = $('#modalGudsExpanse').val();
    const gudsLoader = $('#modalGudsLoader').val();

    if(!gudsName){
      showWarning('Please enter guds name');
      return;
    }

    $.ajax({
      url: '{{ url("/sale/guds/store") }}',
      method: 'POST',
      data: {
        _token: '{{ csrf_token() }}',
        gudsName: gudsName,
        gudsExpanse: gudsExpanse,
        gudsLoader: gudsLoader
      },
      dataType: 'json',
      success: function(res){
        if(res.success){
          showSuccess('Guds saved successfully!');
          
          // Add to dropdown
          const newOption = new Option(res.data.gudsName, res.data.gudsName, true, true);
          $(newOption).attr('data-expanse', res.data.gudsExpanse);
          $(newOption).attr('data-loader', res.data.gudsLoader);
          $('#gudsName').append(newOption).trigger('change');
          
          // Close modal and reset form
          $('#gudsModal').modal('hide');
          $('#gudsForm')[0].reset();
        } else {
          showError('Failed to save', res.message || '');
        }
      },
      error: function(xhr){
        console.error('Error:', xhr);
        showError('Failed to save', xhr.responseJSON?.message || '');
      }
    });
  });

  // Reset guds form when modal closes
  $('#gudsModal').on('hidden.bs.modal', function(){
    $('#gudsForm')[0].reset();
  });
});

// Add row function
function addRow() {
  const tbody = $('#productTable tbody');
  
  // Create new row HTML from scratch
  const newRowHTML = `
    <tr>
      <td>
        <select class="form-select form-select-sm productCode">
          <option value="">Select Product</option>
          @foreach($products as $product)
            <option value="{{ $product->productCode }}" data-rate="{{ $product->saleRate }}">{{ $product->productName }} - {{ $product->companyName }}</option>
          @endforeach
        </select>
      </td>
      <td><input type="number" class="form-control form-control-sm text-center qty" value="1" min="1"></td>
      <td><input type="number" class="form-control form-control-sm text-end rate" value="0" step="0.01"></td>
      <td><input type="number" class="form-control form-control-sm text-end discount" value="0" step="0.01" min="0" max="100"></td>
      <td><input type="text" class="form-control form-control-sm text-end amount" readonly value="0.00"></td>
      <td class="text-center">
        <button type="button" class="btn btn-sm btn-success p-1 px-2 me-1" onclick="addRow()" title="Add Row">
          <i class="bi bi-plus-lg"></i>
        </button>
        <button type="button" class="btn btn-sm btn-danger p-1 px-2 btnDeleteRow" title="Delete Row">
          <i class="bi bi-trash3"></i>
        </button>
      </td>
    </tr>
  `;
  
  // Append new row
  const newRow = $(newRowHTML);
  tbody.append(newRow);
  
  // Initialize Select2 on the new row's select element
  newRow.find('.productCode').select2({
    theme: 'bootstrap-5',
    placeholder: 'Select Product',
    width: 'resolve'
  });
  
  // Update totals
  updateTotals();
}
</script>

@endsection
