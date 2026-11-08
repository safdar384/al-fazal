@extends('layout.app')

@section('title', 'Supplier Transaction')

@section('content')

@include('components.toast-notification')

<style>
  .st-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
  }
  #entriesTable {
    font-size: 0.875rem;
  }
  #entriesTable th,
  #entriesTable td {
    vertical-align: middle;
    white-space: nowrap;
  }
  #entriesTable td.st-product {
    max-width: 18rem;
    white-space: normal;
  }

  /* Customer dropdown */
  #customerDropdown.dropdown-menu {
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-top: 4px;
  }
  #customerDropdown .dropdown-item {
    padding: 10px 15px;
    cursor: pointer;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
  }
  #customerDropdown .dropdown-item:hover,
  #customerDropdown .dropdown-item.active {
    background: linear-gradient(135deg, #1a4a8a 0%, #0f2747 100%);
    color: white;
    border-left-color: #c9a227;
    transform: translateX(3px);
  }
  #customerDropdown .dropdown-item strong {
    font-size: 0.95rem;
    display: block;
    margin-bottom: 3px;
  }
  #customerDropdown .dropdown-item .text-muted { font-size: 0.8rem; opacity: 0.8; }
  #customerDropdown .dropdown-item:hover .text-muted,
  #customerDropdown .dropdown-item.active .text-muted {
    color: rgba(255, 255, 255, 0.9) !important;
  }

  /* Transaction product list — same as Sale Invoice */
  #productFieldWrap .product-search-wrap { position: relative; }
  #productSearch.has-product { padding-right: 1.85rem; }
  #productFieldWrap .btn-clear-product {
    position: absolute; right: 4px; top: 50%; transform: translateY(-50%);
    z-index: 5; width: 22px; height: 22px; padding: 0; border: none; border-radius: 50%;
    background: transparent; color: #6b7c8d; display: none; align-items: center; justify-content: center;
    line-height: 1; font-size: 0.75rem;
  }
  #productFieldWrap .btn-clear-product:hover { background: #fde8ea; color: #dc3545; }
  #productDropdown.dropdown-menu {
    max-height: 350px; overflow-y: auto;
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.2);
    border: 1px solid #dee2e6; border-radius: 0.375rem; margin-top: 4px;
  }
  #productDropdown .dropdown-item {
    padding: 10px 12px; cursor: pointer; font-size: 0.875rem;
    border-bottom: 1px solid #f1f3f5;
    display: flex; justify-content: space-between; align-items: center;
  }
  #productDropdown .dropdown-item:last-child { border-bottom: none; }
  #productDropdown .dropdown-item:hover,
  #productDropdown .dropdown-item.active {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    color: #fff;
  }
  #productDropdown .dropdown-item:hover .text-muted,
  #productDropdown .dropdown-item.active .text-muted {
    color: rgba(255,255,255,0.9) !important;
  }
  #productDropdown .product-search-thumb {
    width: 36px; height: 36px; object-fit: cover; flex-shrink: 0;
  }
  #productDropdown .product-search-thumb-empty {
    width: 36px; height: 36px; font-size: 0.85rem;
  }
  .net-rate-link {
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
  }
  .net-rate-link:hover { text-decoration: underline; }

  /* Net Rate modal product search — same look as Sale Invoice */
  #netRateModal .nr-product-search-wrap { position: relative; }
  #netRateModal .nrProductSearch.has-product { padding-right: 1.85rem; }
  #netRateModal .btn-clear-nr-product {
    position: absolute; right: 4px; top: 50%; transform: translateY(-50%);
    z-index: 5; width: 22px; height: 22px; padding: 0; border: none; border-radius: 50%;
    background: transparent; color: #6b7c8d; display: none; align-items: center; justify-content: center;
    line-height: 1; font-size: 0.75rem;
  }
  #netRateModal .btn-clear-nr-product:hover { background: #fde8ea; color: #dc3545; }
  #netRateModal .nrProductDropdown {
    position: absolute; top: 100%; left: 0; z-index: 10650;
    border: 1px solid #dee2e6; border-radius: 0.375rem; background: #fff;
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.2); margin-top: 4px;
    max-height: 320px; overflow-y: auto; min-width: 100%; display: none;
  }
  #netRateModal .nrProductDropdown .dropdown-item {
    padding: 10px 12px; cursor: pointer; font-size: 0.875rem;
    border-bottom: 1px solid #f1f3f5; display: flex; justify-content: space-between; align-items: center;
  }
  #netRateModal .nrProductDropdown .dropdown-item:last-child { border-bottom: none; }
  #netRateModal .nrProductDropdown .dropdown-item:hover,
  #netRateModal .nrProductDropdown .dropdown-item.active {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: #fff;
  }
  #netRateModal .nrProductDropdown .dropdown-item:hover .text-muted,
  #netRateModal .nrProductDropdown .dropdown-item.active .text-muted {
    color: rgba(255,255,255,0.9) !important;
  }
  #netRateModal .product-search-thumb {
    width: 36px; height: 36px; object-fit: cover; flex-shrink: 0;
  }
  #netRateModal .product-search-thumb-empty {
    width: 36px; height: 36px; font-size: 0.85rem;
  }
</style>

<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-6">
          <h3 class="mb-0">
            <i class="bi bi-truck text-primary me-2"></i>Supplier Transaction
          </h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Accounts</a></li>
            <li class="breadcrumb-item active" aria-current="page">Supplier Transaction</li>
          </ol>
        </div>
      </div>
      <hr class="mb-0" style="border-top: 2px solid #0f2747;">
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <div class="card mb-4 shadow-sm">
        <div class="card-header st-card-head py-2">
          <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Record Sale / Purchase</h5>
        </div>
        <div class="card-body">
          <form id="supplierTranForm">
            @csrf

            {{-- Line 1: Customer + Address --}}
            <div class="row g-3 align-items-end mb-2">
              <div class="col-md-5">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <label class="form-label fw-semibold mb-0">
                    <i class="bi bi-person-badge text-primary me-1"></i>Supplier
                  </label>
                  <small id="customerBalanceDisplay" class="text-muted" style="display:none;">
                    Balance: <strong id="customerBalanceValue" class="text-primary">0.00</strong>
                    <span id="customerAfterWrap" class="ms-1" style="display:none;">
                      <i class="bi bi-arrow-right-short text-muted"></i>
                      <strong id="customerAfterValue" class="text-info">0.00</strong>
                    </span>
                  </small>
                </div>
                <div class="position-relative">
                  <input type="text"
                         class="form-control form-control-sm"
                         id="customerSearch"
                         placeholder="Type to search supplier..."
                         autocomplete="off"
                         required>
                  <input type="hidden" name="customerCode" id="customerCodeSubmit" value="">
                  <select class="form-select form-select-sm d-none" id="customerCode">
                    <option value="">Select Supplier</option>
                    @foreach($customers as $c)
                      <option value="{{ $c->custCode }}"
                              data-name="{{ $c->custName }}"
                              data-business="{{ $c->bussnessName ?? '' }}"
                              data-address="{{ $c->custAddress ?? '' }}"
                              data-area="{{ $c->custArea }}"
                              data-city="{{ $c->custCity }}"
                              data-category="{{ $c->custCatagory ?? '' }}">
                        {{ $c->custName }}@if(!empty($c->bussnessName)) | {{ $c->bussnessName }}@endif - {{ $c->custArea }}
                      </option>
                    @endforeach
                  </select>
                  <div id="customerDropdown" class="dropdown-menu w-100" style="display:none;"></div>
                </div>
              </div>
              <div class="col-md-7">
                <label class="form-label fw-semibold mb-2">
                  <i class="bi bi-geo-alt text-danger me-1"></i>Address
                </label>
                <div id="customerAddressDisplay" class="form-control form-control-sm bg-light text-muted" style="min-height:31px;">
                  Select a supplier to view address
                </div>
              </div>
            </div>

            {{-- Line 2: Type, Product, Rate, Qty, Amount, Save --}}
            <div class="row g-3 align-items-end">
              <div class="col-md-2">
                <label class="form-label fw-semibold">
                  <i class="bi bi-arrow-left-right text-warning me-1"></i>Type
                </label>
                <select class="form-select form-select-sm" name="type" id="txnType" required>
                  <option value="Sale">Sale</option>
                  <option value="Purchase">Purchase</option>
                  <option value="Cash">Cash</option>
                </select>
              </div>

              <div class="col-md-3" id="productFieldWrap">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="form-label fw-semibold mb-0">
                    <i class="bi bi-box-seam text-success me-1"></i>Product
                  </label>
                  <a href="javascript:void(0);" class="net-rate-link text-primary" id="addNetRateLink">
                    <i class="bi bi-plus-circle me-1"></i>Add Net Rate
                  </a>
                </div>
                <div class="position-relative product-search-wrap">
                  <input type="text"
                         class="form-control form-control-sm"
                         id="productSearch"
                         placeholder="Type to search product..."
                         autocomplete="off">
                  <button type="button" class="btn-clear-product" id="btnClearProduct" title="Clear product">
                    <i class="bi bi-x"></i>
                  </button>
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

              <div class="col-md-2 d-none" id="workFieldWrap">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="form-label fw-semibold mb-0">
                    <i class="bi bi-hammer text-info me-1"></i>Work
                  </label>
                  <small class="text-success fw-semibold d-none" id="workStockHint"></small>
                </div>
                <select class="form-select form-select-sm" id="workSelect">
                  <option value="">Select Work</option>
                </select>
              </div>

              <div class="col-md-2 d-none" id="entryTypeFieldWrap">
                <label class="form-label fw-semibold">
                  <i class="bi bi-arrow-down-up text-warning me-1"></i>Entry Type
                </label>
                <select class="form-select form-select-sm" id="cashEntryType" name="entryType">
                  <option value="pay" selected>Pay</option>
                  <option value="receive">Receive</option>
                </select>
              </div>

              <div class="col-md-3 d-none" id="bankAccountFieldWrap">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="form-label fw-semibold mb-0">
                    <i class="bi bi-bank text-success me-1"></i>Bank Account
                  </label>
                  <small id="bankBalanceDisplay" class="text-muted" style="display:none;">
                    <strong id="bankBalanceValue" class="text-primary">0.00</strong>
                    <span id="bankAfterWrap" class="ms-1" style="display:none;">
                      <i class="bi bi-arrow-right-short text-muted"></i>
                      <strong id="bankAfterValue" class="text-info">0.00</strong>
                    </span>
                  </small>
                </div>
                <select class="form-select form-select-sm" id="cashBankAccount" name="bankAccount">
                  <option value="">Select Bank</option>
                  @foreach(($bankAccounts ?? []) as $bank)
                    <option value="{{ $bank->bankAccount }}"
                      @selected(strtoupper((string) $bank->bankAccount) === 'CASH' || strtoupper((string) $bank->bankName) === 'CASH BOOK')">
                      {{ $bank->bankName }} — {{ $bank->bankAccount }}@if($bank->branchCode) ({{ $bank->branchCode }})@endif
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-3 d-none" id="descriptionFieldWrap">
                <label class="form-label fw-semibold">
                  <i class="bi bi-chat-left-text text-secondary me-1"></i>Description
                </label>
                <input type="text"
                       class="form-control form-control-sm"
                       id="cashDescription"
                       name="description"
                       placeholder="Cash payment details..."
                       maxlength="255">
              </div>

              <div class="col-md-2" id="rateFieldWrap">
                <label class="form-label fw-semibold" id="rateFieldLabel">
                  <i class="bi bi-currency-dollar text-success me-1"></i><span id="rateLabelText">Sale Rate</span>
                </label>
                <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="rate" id="rate" placeholder="0.00">
              </div>

              <div class="col-md-1" id="qtyFieldWrap">
                <label class="form-label fw-semibold">
                  <i class="bi bi-123 text-info me-1"></i>Qty
                </label>
                <input type="number" step="0.01" min="0.01" class="form-control form-control-sm" name="qty" id="qty" placeholder="0">
              </div>

              <div class="col-md-2" id="amountFieldWrap">
                <label class="form-label fw-semibold">
                  <i class="bi bi-cash-coin text-primary me-1"></i>Amount
                </label>
                <input type="number" step="0.01" min="0" class="form-control form-control-sm bg-light" name="amount" id="amount" placeholder="0.00" readonly>
              </div>

              <div class="col-md-2">
                <input type="hidden" id="editTxnId" value="">
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
        <div class="card-header st-card-head py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
          <h5 class="mb-0"><i class="bi bi-table me-2"></i>Transaction Entries</h5>
          <small class="text-white-50" id="entriesHint">Select a supplier to view entries</small>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover table-bordered align-middle mb-0" id="entriesTable">
              <thead class="table-light">
                <tr>
                  <th style="width:4%;" class="text-center">#</th>
                  <th style="width:9%;">Date</th>
                  <th style="width:10%;">Txn No</th>
                  <th style="width:8%;">Type</th>
                  <th>Product / Description</th>
                  <th class="text-end" style="width:7%;">Qty</th>
                  <th class="text-end" style="width:8%;">Rate</th>
                  <th class="text-end" style="width:10%;">Credit</th>
                  <th class="text-end" style="width:10%;">Debit</th>
                  <th class="text-end" style="width:11%;">Balance</th>
                  <th class="text-center" style="width:10%;">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="11" class="text-center text-muted py-4">Select a supplier to view entries</td>
                </tr>
              </tbody>
              <tfoot id="entriesTableFoot" class="d-none">
                <tr class="table-light fw-semibold">
                  <td colspan="7" class="text-end">Total</td>
                  <td class="text-end text-success" id="entriesTotalCredit">0.00</td>
                  <td class="text-end text-danger" id="entriesTotalDebit">0.00</td>
                  <td class="text-end text-danger" id="entriesClosingBal">0.00</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
        <div class="card-footer bg-light py-2">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <small class="text-muted" id="entriesMeta">—</small>
            <div class="d-flex align-items-center gap-2">
              <label class="small text-muted mb-0" for="entriesPerPage">Rows</label>
              <select id="entriesPerPage" class="form-select form-select-sm" style="width:auto;">
                <option value="10">10</option>
                <option value="15" selected>15</option>
                <option value="25">25</option>
                <option value="50">50</option>
              </select>
              <nav id="entriesPager" aria-label="Entries pagination"></nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

{{-- Add Net Rate Modal (same store as Special Rates page) --}}
<div class="modal fade" id="netRateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header st-card-head">
        <h5 class="modal-title"><i class="bi bi-percent me-2"></i>Add Net Rate</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="netRateForm">
          <input type="hidden" id="nrEditId" value="">
          <div class="mb-2">
            <label class="form-label fw-semibold">Supplier</label>
            <input type="text" class="form-control form-control-sm bg-light" id="nrCustomerLabel" readonly>
            <input type="hidden" id="nrCustomerCode">
          </div>
          <div class="mb-2">
            <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
            <div class="nr-product-search-wrap">
              <input type="text"
                     class="form-control form-control-sm nrProductSearch"
                     id="nrProductSearch"
                     placeholder="Type to search product..."
                     autocomplete="off"
                     required>
              <button type="button" class="btn-clear-nr-product" id="btnClearNrProduct" title="Clear product">
                <i class="bi bi-x"></i>
              </button>
              <input type="hidden" id="nrProductCode" value="">
              <div class="nrProductDropdown" id="nrProductDropdown"></div>
            </div>
          </div>
          <div class="mb-2" id="nrWorkWrap">
            <label class="form-label fw-semibold">Work</label>
            <select class="form-select form-select-sm" id="nrWorkSelect">
              <option value="">Product only (no work)</option>
            </select>
            <small class="text-muted d-none" id="nrWorkStockHint"></small>
          </div>
          <div class="mb-2 d-none" id="nrVariantWrap">
            <label class="form-label fw-semibold">Variant</label>
            <select class="form-select form-select-sm" id="nrVariantSelect">
              <option value="">No Variant</option>
            </select>
          </div>
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Rate Type <span class="text-danger">*</span></label>
              <select class="form-select form-select-sm" id="nrRateType" required>
                <option value="Sale">Sale</option>
                <option value="Purchase">Purchase</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Effective From <span class="text-danger">*</span></label>
              <input type="date" class="form-control form-control-sm" id="nrEffectiveFrom" value="{{ now()->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" id="nrRateAmountLabel">Net Rate <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="nrSpecialRate" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Discount %</label>
              <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm" id="nrDiscountPercent" value="0">
            </div>
            <div class="col-md-12">
              <label class="form-label fw-semibold">Remarks</label>
              <input type="text" class="form-control form-control-sm" id="nrRemarks" maxlength="255" placeholder="Optional">
            </div>
            <div class="col-md-12">
              <div class="d-flex gap-2 justify-content-end mt-1">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="nrFormResetBtn">Reset</button>
                <button type="submit" class="btn btn-success btn-sm" id="nrSaveBtn">
                  <i class="bi bi-save me-1"></i>Save Net Rate
                </button>
              </div>
            </div>
          </div>
        </form>

        <hr class="my-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0"><i class="bi bi-list-check me-1"></i>Saved Net Rates</h6>
          <small class="text-muted" id="nrRatesHint">—</small>
        </div>
        <div class="table-responsive">
          <table class="table table-sm table-hover table-bordered align-middle mb-0" id="nrRatesTable">
            <thead class="table-secondary">
              <tr>
                <th style="width:5%;" class="text-center">#</th>
                <th>Product</th>
                <th style="width:14%;">Work</th>
                <th style="width:11%;">Type</th>
                <th class="text-end" style="width:12%;">Rate</th>
                <th style="width:12%;">Effective</th>
                <th class="text-center" style="width:14%;">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="7" class="text-center text-muted py-3">No rates saved</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="submit" form="netRateForm" class="btn btn-success btn-sm" id="nrSaveBtnFooter">
          <i class="bi bi-save me-1"></i>Save Net Rate
        </button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  let productVariants = {};
  let catalogLoaded = false;
  let catalogXhr = null;
  let productList = [];
  let productContextXhr = null;

  function fmt(n) {
    return (Number(n || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function calcAmount() {
    const rate = parseFloat($('#rate').val()) || 0;
    const qty = parseFloat($('#qty').val()) || 0;
    $('#amount').val((rate * qty).toFixed(2));
    updateProjectedBalances();
  }

  function showCustomerAddress($opt) {
    const address = ($opt.data('address') || '').toString().trim();
    const area = ($opt.data('area') || '').toString().trim();
    const city = ($opt.data('city') || '').toString().trim();
    const parts = [address, area, city].filter(Boolean);
    const $box = $('#customerAddressDisplay');
    if (!parts.length) {
      $box.removeClass('text-dark').addClass('text-muted').text('No address on file');
      return;
    }
    $box.removeClass('text-muted').addClass('text-dark').text(parts.join(', '));
  }

  // ----- Supplier search -----
  function filterCustomers(term) {
    const t = (term || '').toLowerCase().trim();
    const list = [];
    $('#customerCode option').each(function () {
      const code = $(this).val();
      if (!code) return;
      const name = ($(this).data('name') || '').toString();
      const business = ($(this).data('business') || '').toString();
      const area = ($(this).data('area') || '').toString();
      const hay = (name + ' ' + business + ' ' + area + ' ' + code).toLowerCase();
      if (!t || hay.includes(t)) {
        list.push({ code, name, business, area, city: $(this).data('city') || '' });
      }
    });
    return list;
  }

  function renderCustomerDropdown(list) {
    const $dd = $('#customerDropdown').empty();
    if (!list.length) {
      $dd.html('<div class="dropdown-item text-muted">No suppliers found</div>').show();
      return;
    }
    list.slice(0, 15).forEach((c, idx) => {
      $dd.append(`
        <button type="button" class="dropdown-item ${idx === 0 ? 'active' : ''}" data-code="${c.code}">
          <strong>${c.business || c.name}</strong>
          <div class="text-muted">Area: ${c.area} | City: ${c.city} | Code: ${c.code}</div>
        </button>
      `);
    });
    $dd.show();
  }

  let selectContextXhr = null;
  let entriesCache = [];
  let entriesMetaCache = null;
  let entriesPage = 1;
  let editingId = null;
  let supplierBalance = 0;
  let supplierBalanceType = 'payable';
  let bankBalance = 0;

  function selectCustomer(code) {
    code = String(code || '').trim();
    if (!code) return;
    const $opt = $('#customerCode option').filter(function () {
      return String($(this).val()) === code;
    }).first();
    if (!$opt.length) return;
    $('#customerCode').val(code);
    $('#customerCodeSubmit').val(code);
    const name = $opt.attr('data-name') || $opt.data('name') || '';
    const business = $opt.attr('data-business') || $opt.data('business') || '';
    $('#customerSearch').val(business ? `${name} | ${business}` : name);
    showCustomerAddress($opt);
    $('#customerDropdown').hide();
    clearProduct();
    loadSupplierContext();
  }

  function moveCustomerDropdownActive(direction) {
    const $dd = $('#customerDropdown');
    const $items = $dd.find('.dropdown-item[data-code]');
    if (!$items.length) return;
    let index = $items.index($items.filter('.active').first());
    if (direction > 0) index = index < 0 ? 0 : Math.min(index + 1, $items.length - 1);
    else index = index <= 0 ? 0 : index - 1;
    $items.removeClass('active').eq(index).addClass('active');
    const el = $items.get(index);
    if (el && el.scrollIntoView) el.scrollIntoView({ block: 'nearest' });
  }

  function applyBalance(res) {
    if (!res || res.balance === undefined || res.balance === null) {
      supplierBalance = 0;
      $('#customerBalanceDisplay, #customerAfterWrap').hide();
      return;
    }
    supplierBalance = parseFloat(res.balance) || 0;
    supplierBalanceType = res.balance_type || 'payable';
    $('#customerBalanceValue')
      .text(fmt(supplierBalance))
      .toggleClass('text-danger', supplierBalanceType === 'payable')
      .toggleClass('text-primary', supplierBalanceType !== 'payable');
    $('#customerBalanceDisplay').show();
    updateProjectedBalances();
  }

  /** Payable: Pay/Sale ↓, Receive/Purchase ↑ */
  function projectSupplierAfter(current, amount, txnType, entryType) {
    const amt = parseFloat(amount) || 0;
    if (amt <= 0) return null;
    if (txnType === 'Cash') {
      return entryType === 'receive' ? current + amt : current - amt;
    }
    if (txnType === 'Purchase') return current + amt;
    if (txnType === 'Sale') return current - amt;
    return null;
  }

  /** Cash Pay → bank out (−); Cash Receive → bank in (+) */
  function projectBankAfter(current, amount, entryType) {
    const amt = parseFloat(amount) || 0;
    if (amt <= 0) return null;
    return entryType === 'receive' ? current + amt : current - amt;
  }

  function showAfterBalance($wrap, $valEl, afterVal) {
    if (afterVal === null || afterVal === undefined) {
      $wrap.hide();
      return;
    }
    $valEl.text(fmt(afterVal))
      .removeClass('text-info text-danger text-primary')
      .addClass(afterVal < 0 ? 'text-danger' : 'text-info');
    $wrap.show();
  }

  function updateProjectedBalances() {
    const amount = $('#amount').val();
    const txnType = $('#txnType').val() || 'Purchase';
    const entryType = $('#cashEntryType').val() || 'pay';

    if ($('#customerBalanceDisplay').is(':visible')) {
      showAfterBalance(
        $('#customerAfterWrap'),
        $('#customerAfterValue'),
        projectSupplierAfter(supplierBalance, amount, txnType, entryType)
      );
    } else {
      $('#customerAfterWrap').hide();
    }

    if ($('#bankBalanceDisplay').is(':visible') && txnType === 'Cash') {
      showAfterBalance(
        $('#bankAfterWrap'),
        $('#bankAfterValue'),
        projectBankAfter(bankBalance, amount, entryType)
      );
    } else {
      $('#bankAfterWrap').hide();
    }
  }

  function loadBankBalance(bankCode) {
    if (!bankCode || $('#txnType').val() !== 'Cash') {
      bankBalance = 0;
      $('#bankBalanceDisplay, #bankAfterWrap').hide();
      updateProjectedBalances();
      return;
    }
    $.get(`{{ url('/api/bank-balance') }}/${encodeURIComponent(bankCode)}`)
      .done(function (res) {
        if (!res || res.success === false) {
          bankBalance = 0;
          $('#bankBalanceDisplay, #bankAfterWrap').hide();
          updateProjectedBalances();
          return;
        }
        bankBalance = parseFloat(res.balance ?? res.current_balance ?? 0) || 0;
        $('#bankBalanceValue').text(fmt(bankBalance));
        $('#bankBalanceDisplay').show();
        updateProjectedBalances();
      })
      .fail(function () {
        bankBalance = 0;
        $('#bankBalanceDisplay, #bankAfterWrap').hide();
        updateProjectedBalances();
      });
  }

  /** Single request: balance + entries + products (replaces 3 parallel calls). */
  function loadSupplierContext() {
    const customerCode = $('#customerCodeSubmit').val();
    const type = $('#txnType').val() || 'Purchase';
    const $tb = $('#entriesTable tbody');
    if (!customerCode) {
      entriesCache = [];
      entriesMetaCache = null;
      entriesPage = 1;
      $('#entriesHint').text('Select a supplier to view entries');
      $tb.html('<tr><td colspan="11" class="text-center text-muted py-4">Select a supplier to view entries</td></tr>');
      $('#entriesTableFoot').addClass('d-none');
      $('#entriesPager').empty();
      $('#entriesMeta').text('—');
      supplierBalance = 0;
      $('#customerBalanceDisplay, #customerAfterWrap').hide();
      productList = [];
      return;
    }
    $('#entriesHint').text('Loading...');
    if (selectContextXhr && selectContextXhr.readyState !== 4) {
      selectContextXhr.abort();
    }
    selectContextXhr = $.get(`{{ route('supplierTransaction.selectContext') }}`, { customerCode, type })
      .done(function (res) {
        applyBalance(res);
        productList = res.products || [];
        renderEntries(res.entries || [], res);
        const n = (res.entries || []).filter(function (e) {
          return !(e.isOpening || e.type === 'Opening');
        }).length;
        const bal = fmt(res.closing_balance || 0);
        const label = res.balance_label || 'Balance';
        $('#entriesHint').text(n + ' entr' + (n === 1 ? 'y' : 'ies') + ' | ' + label + ': ' + bal);
      })
      .fail(function (xhr) {
        if (xhr.statusText === 'abort') return;
        entriesCache = [];
        entriesMetaCache = null;
        entriesPage = 1;
        $tb.html('<tr><td colspan="11" class="text-center text-danger py-4">Failed to load entries</td></tr>');
        $('#entriesTableFoot').addClass('d-none');
        $('#entriesPager').empty();
        $('#entriesMeta').text('—');
        $('#entriesHint').text('Failed to load');
        productList = [];
        supplierBalance = 0;
        $('#customerBalanceDisplay, #customerAfterWrap').hide();
      });
  }

  function loadEntries() {
    loadSupplierContext();
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
    $('#cashDescription').val('');
    $('#cashEntryType').val('pay');
    $('#rate, #qty, #amount').val('');
    applyTypeUi($('#txnType').val() || 'Purchase', { resetCash: true });
  }

  function applyTypeUi(type, { resetCash = true } = {}) {
    const isCash = type === 'Cash';
    $('#productFieldWrap').toggleClass('d-none', isCash);
    if (isCash) {
      $('#workFieldWrap').addClass('d-none');
    }
    $('#descriptionFieldWrap, #entryTypeFieldWrap, #bankAccountFieldWrap').toggleClass('d-none', !isCash);
    $('#rateFieldWrap, #qtyFieldWrap').toggleClass('d-none', isCash);
    $('#productSearch').prop('required', !isCash);
    $('#cashDescription').prop('required', isCash);
    $('#cashEntryType').prop('required', isCash);
    $('#cashBankAccount').prop('required', isCash);
    $('#rate, #qty').prop('required', !isCash);
    $('#amount').prop('required', true);
    updateRateLabel();
    if (isCash) {
      $('#amount').prop('readonly', false).removeClass('bg-light');
      if (resetCash) {
        clearProduct();
        $('#cashDescription').val('');
        $('#cashEntryType').val('pay');
        const $cashOpt = $('#cashBankAccount option').filter(function () {
          const v = String($(this).val() || '').toUpperCase();
          return v === 'CASH' || v === 'CASH BOOK';
        }).first();
        $('#cashBankAccount').val($cashOpt.length ? $cashOpt.val() : '');
        $('#amount').val('');
      }
    } else {
      $('#amount').prop('readonly', true).addClass('bg-light');
      $('#cashBankAccount').prop('required', false).val('');
      bankBalance = 0;
      $('#bankBalanceDisplay, #bankAfterWrap').hide();
      if (resetCash) {
        $('#cashDescription').val('');
        calcAmount();
      }
    }
    if (isCash) {
      loadBankBalance($('#cashBankAccount').val() || '');
    } else {
      updateProjectedBalances();
    }
  }

  function beginEdit(e) {
    if (!e || !e.id) return;
    editingId = e.id;
    $('#editTxnId').val(e.id);
    setEditMode(true, e.txnNo);

    const type = e.type || 'Purchase';
    $('#txnType').val(type);
    applyTypeUi(type, { resetCash: false });

    if (type === 'Cash') {
      clearProduct();
      const et = (e.entryType || 'pay').toLowerCase();
      $('#cashEntryType').val(et === 'receive' || et === 'credit' ? 'receive' : 'pay');
      $('#cashBankAccount').val(e.bankAccount || '');
      $('#cashDescription').val(e.productName || e.remarks || '');
      $('#amount').val(Number(e.amount || e.debit || e.credit || 0).toFixed(2));
      loadBankBalance(e.bankAccount || '');
      updateProjectedBalances();
    } else {
      $('#cashDescription').val('');
      const customerCode = $('#customerCodeSubmit').val();
      $.get(`{{ url('/supplier-transaction/products') }}`, { customerCode, type })
        .done(function (res) {
          productList = res.products || [];
          const code = e.productCode || '';
          const p = productList.find(x => x.productCode === code);
          $('#productCodeSubmit').val(code);
          $('#productNameHidden').val(e.productName || (p ? p.productName : '') || '');
          $('#productSearch').val(
            (e.productName || (p ? p.productName : code) || '')
            + (p && p.companyName ? ' - ' + p.companyName : '')
          );
          setProductClearVisible(!!code);
          $('#rate').val(Number(e.rate || 0).toFixed(2));
          $('#qty').val(e.qty || '');
          calcAmount();
          fillVariants(code, null);
          const vid = e.variantId || '';
          if (vid) {
            setTimeout(function () {
              $('#variantSelect').val(String(vid));
              if (!$('#variantSelect').val() && e.variantName) {
                // ensure option exists
                $('#variantSelect').append(
                  `<option value="${vid}" data-name="${e.variantName || ''}" data-value="${e.variantValue || ''}" selected>${e.variantName || ''} ${e.variantValue || ''}</option>`
                );
              }
            }, 50);
          }
          loadProductWorks(code, e.workName || '');
        })
        .fail(function () {
          $('#productCodeSubmit').val(e.productCode || '');
          $('#productSearch').val(e.productName || '');
          setProductClearVisible(!!e.productCode);
          $('#rate').val(Number(e.rate || 0).toFixed(2));
          $('#qty').val(e.qty || '');
          calcAmount();
          loadProductWorks(e.productCode || '', e.workName || '');
        });
    }

    const $form = $('#supplierTranForm');
    if ($form.length) {
      $('html, body').animate({ scrollTop: Math.max(0, ($form.offset()?.top || 0) - 80) }, 200);
    }
  }

  function entriesPerPage() {
    return parseInt($('#entriesPerPage').val(), 10) || 15;
  }

  function renderEntriesPager() {
    const $pager = $('#entriesPager').empty();
    const total = entriesCache.length;
    const per = entriesPerPage();
    const last = Math.max(1, Math.ceil(total / per) || 1);
    if (entriesPage > last) entriesPage = last;
    if (total <= 0) {
      $('#entriesMeta').text('—');
      return;
    }
    const from = (entriesPage - 1) * per + 1;
    const to = Math.min(entriesPage * per, total);
    $('#entriesMeta').text('Showing ' + from + '–' + to + ' of ' + total);

    if (last <= 1) return;

    let html = '<ul class="pagination pagination-sm mb-0">';
    html += '<li class="page-item' + (entriesPage <= 1 ? ' disabled' : '') + '">' +
      '<a class="page-link" href="#" data-page="' + (entriesPage - 1) + '">Prev</a></li>';

    const windowSize = 5;
    let start = Math.max(1, entriesPage - Math.floor(windowSize / 2));
    let end = Math.min(last, start + windowSize - 1);
    start = Math.max(1, end - windowSize + 1);
    for (let p = start; p <= end; p++) {
      html += '<li class="page-item' + (p === entriesPage ? ' active' : '') + '">' +
        '<a class="page-link" href="#" data-page="' + p + '">' + p + '</a></li>';
    }

    html += '<li class="page-item' + (entriesPage >= last ? ' disabled' : '') + '">' +
      '<a class="page-link" href="#" data-page="' + (entriesPage + 1) + '">Next</a></li>';
    html += '</ul>';
    $pager.html(html);
  }

  function entryTypeBadge(e) {
    if (e.isOpening || e.type === 'Opening') {
      return '<span class="badge text-bg-warning text-dark">Pre Balance</span>';
    }
    let typeBadge = '<span class="badge text-bg-secondary">' + (e.type || '') + '</span>';
    if (e.type === 'Sale') typeBadge = '<span class="badge text-bg-success">Sale</span>';
    else if (e.type === 'Purchase') typeBadge = '<span class="badge text-bg-warning text-dark">Purchase</span>';
    else if (e.type === 'Cash') {
      const et = (e.entryType || 'pay').toLowerCase();
      const isReceive = et === 'receive' || et === 'credit';
      typeBadge = '<span class="badge text-bg-info">Cash</span> '
        + (isReceive
          ? '<span class="badge text-bg-success">Receive</span>'
          : '<span class="badge text-bg-danger">Pay</span>');
    }
    return typeBadge;
  }

  function appendEntryRow($tb, e, rowNo, opts) {
    opts = opts || {};
    const isOpening = !!(e.isOpening || e.type === 'Opening' || opts.isBroughtForward);
    const productLabel = isOpening
      ? (opts.isBroughtForward ? 'Pre Balance (B/F)' : (e.productName || 'Pre Balance'))
      : (e.type === 'Cash'
        ? ((e.productName || e.remarks || '-')
          + (e.bankName || e.bankAccount
            ? ' <small class="text-success"><i class="bi bi-bank"></i> ' + (e.bankName || e.bankAccount) + '</small>'
            : ''))
        : ((e.productName || e.productCode || '-')
          + (e.workName ? ' <small class="text-info">/ ' + e.workName + '</small>' : '')
          + (e.variant ? ' <small class="text-muted">(' + e.variant + ')</small>' : '')));
    const debit = parseFloat(e.debit) || 0;
    const credit = parseFloat(e.credit) || 0;
    const bal = parseFloat(e.balance) || 0;
    const actions = isOpening
      ? '<span class="text-muted">—</span>'
      : `<div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-secondary btn-edit-entry" data-id="${e.id}" title="Edit"><i class="bi bi-pencil"></i></button>
            <button type="button" class="btn btn-outline-danger btn-delete-entry" data-id="${e.id}" data-txn="${e.txnNo}" title="Delete"><i class="bi bi-trash"></i></button>
          </div>`;
    $tb.append(`
      <tr class="${isOpening ? 'table-warning fw-semibold' : ''}">
        <td class="text-center">${rowNo}</td>
        <td>${e.date || ''}</td>
        <td>${isOpening ? '—' : '<code>' + (e.txnNo || '') + '</code>'}</td>
        <td>${opts.isBroughtForward ? '<span class="badge text-bg-warning text-dark">Pre Balance</span>' : entryTypeBadge(e)}</td>
        <td class="st-product">${productLabel}</td>
        <td class="text-end">${isOpening || e.type === 'Cash' ? '—' : fmt(e.qty)}</td>
        <td class="text-end">${isOpening || e.type === 'Cash' ? '—' : fmt(e.rate)}</td>
        <td class="text-end text-success">${!isOpening && credit > 0 ? fmt(credit) : '-'}</td>
        <td class="text-end text-danger">${!isOpening && debit > 0 ? fmt(debit) : '-'}</td>
        <td class="text-end fw-semibold text-danger">${fmt(bal)}</td>
        <td class="text-center text-nowrap">${actions}</td>
      </tr>
    `);
  }

  function renderEntriesPage() {
    const $tb = $('#entriesTable tbody').empty();
    const entries = entriesCache || [];
    if (!entries.length) {
      $tb.html('<tr><td colspan="11" class="text-center text-muted py-4">No entries for this supplier</td></tr>');
      $('#entriesTableFoot').addClass('d-none');
      $('#entriesPager').empty();
      $('#entriesMeta').text('—');
      return;
    }

    const per = entriesPerPage();
    const last = Math.max(1, Math.ceil(entries.length / per));
    if (entriesPage > last) entriesPage = last;
    if (entriesPage < 1) entriesPage = 1;
    const start = (entriesPage - 1) * per;
    const pageRows = entries.slice(start, start + per);

    pageRows.forEach(function (e, idx) {
      appendEntryRow($tb, e, start + idx + 1);
    });

    renderEntriesPager();
  }

  function renderEntries(entries, meta) {
    entriesCache = entries || [];
    entriesMetaCache = meta || null;
    entriesPage = 1;

    let totalDebit = 0;
    let totalCredit = 0;
    entriesCache.forEach(function (e) {
      if (e.isOpening || e.type === 'Opening') return;
      totalDebit += parseFloat(e.debit) || 0;
      totalCredit += parseFloat(e.credit) || 0;
    });
    // Descending list: newest first — closing balance is on the first non-opening row
    const closeBal = meta && typeof meta.closing_balance === 'number'
      ? meta.closing_balance
      : (function () {
          const firstTxn = entriesCache.find(function (e) {
            return !(e.isOpening || e.type === 'Opening');
          });
          if (firstTxn) return parseFloat(firstTxn.balance) || 0;
          const openRow = entriesCache.find(function (e) {
            return e.isOpening || e.type === 'Opening';
          });
          return openRow ? (parseFloat(openRow.balance) || 0) : 0;
        })();
    if (meta && typeof meta.total_debit === 'number') totalDebit = meta.total_debit;
    if (meta && typeof meta.total_credit === 'number') totalCredit = meta.total_credit;

    if (!entriesCache.length) {
      $('#entriesTable tbody').html('<tr><td colspan="11" class="text-center text-muted py-4">No entries for this supplier</td></tr>');
      $('#entriesTableFoot').addClass('d-none');
      $('#entriesPager').empty();
      $('#entriesMeta').text('—');
      return;
    }

    $('#entriesTotalCredit').text(fmt(totalCredit));
    $('#entriesTotalDebit').text(fmt(totalDebit));
    $('#entriesClosingBal').text(fmt(closeBal));
    $('#entriesTableFoot').removeClass('d-none');
    renderEntriesPage();
  }

  $(document).on('click', '#entriesPager a.page-link', function (e) {
    e.preventDefault();
    const page = parseInt($(this).data('page'), 10);
    if (!page || $(this).parent().hasClass('disabled') || $(this).parent().hasClass('active')) return;
    entriesPage = page;
    renderEntriesPage();
  });

  $('#entriesPerPage').on('change', function () {
    entriesPage = 1;
    if (entriesCache && entriesCache.length) {
      renderEntriesPage();
    }
  });

  $(document).on('click', '.btn-edit-entry', function () {
    const id = $(this).data('id');
    if (id === undefined || id === null || id === '') return;
    const entry = entriesCache.find(x => String(x.id) === String(id));
    if (!entry || entry.isOpening || entry.type === 'Opening') {
      Swal.fire({ icon: 'warning', title: 'Not found', text: 'Entry data not loaded. Select supplier again.' });
      return;
    }
    beginEdit(entry);
  });

  $('#btnCancelEdit').on('click', cancelEdit);

  $(document).on('click', '.btn-delete-entry', function () {
    const id = $(this).data('id');
    const txnNo = $(this).data('txn');
    Swal.fire({
      title: 'Delete Entry?',
      text: `Transaction ${txnNo} will be deleted.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (!result.isConfirmed) return;
      $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
      $.ajax({
        url: `{{ url('/supplier-transaction/delete') }}/${encodeURIComponent(id)}`,
        method: 'DELETE'
      })
        .done(function (res) {
          if (String(editingId) === String(id)) cancelEdit();
          Swal.fire({ icon: 'success', title: 'Deleted', text: res.message || 'Entry deleted' });
          loadSupplierContext();
        })
        .fail(function (xhr) {
          Swal.fire({
            icon: 'error',
            title: 'Delete Failed',
            text: xhr.responseJSON?.message || 'Could not delete entry'
          });
        });
    });
  });

  function loadBalance(code) {
    if (!code) {
      supplierBalance = 0;
      $('#customerBalanceDisplay, #customerAfterWrap').hide();
      return;
    }
    $.get(`{{ url('/customer/balance') }}/${encodeURIComponent(code)}`)
      .done(res => {
        applyBalance(res);
      })
      .fail(function () {
        supplierBalance = 0;
        $('#customerBalanceDisplay, #customerAfterWrap').hide();
      });
  }

  $('#customerSearch').on('focus input', function () {
    renderCustomerDropdown(filterCustomers($(this).val()));
  });

  $('#customerSearch').on('keydown', function (e) {
    const $dd = $('#customerDropdown');
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      if (!$dd.is(':visible')) renderCustomerDropdown(filterCustomers($(this).val()));
      moveCustomerDropdownActive(e.key === 'ArrowDown' ? 1 : -1);
      return;
    }
    if (e.key === 'Escape') {
      $dd.hide();
      return;
    }
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const $selected = $dd.is(':visible') ? $dd.find('.dropdown-item.active[data-code]').first() : $();
    if ($selected.length) {
      selectCustomer($selected.attr('data-code'));
      return;
    }
    const matches = filterCustomers($(this).val());
    if (matches.length === 1) {
      selectCustomer(matches[0].code);
    }
  });

  // mousedown so selection happens before blur/outside-click hides dropdown
  $(document).on('mousedown', '#customerDropdown .dropdown-item[data-code]', function (e) {
    e.preventDefault();
    selectCustomer($(this).attr('data-code'));
  });

  // ----- Products -----
  let productWorksCache = [];

  function hideWorkField() {
    productWorksCache = [];
    $('#workSelect').html('<option value="">Select Work</option>');
    $('#workStockHint').addClass('d-none').text('');
    $('#workFieldWrap').addClass('d-none');
  }

  function showWorkField() {
    if ($('#txnType').val() === 'Cash') {
      $('#workFieldWrap').addClass('d-none');
      return;
    }
    $('#workFieldWrap').removeClass('d-none');
  }

  function updateWorkStockHint() {
    const workName = ($('#workSelect').val() || '').trim();
    const $hint = $('#workStockHint');
    if (!workName) {
      $hint.addClass('d-none').text('');
      return;
    }
    const w = productWorksCache.find(x => String(x.workName).toLowerCase() === workName.toLowerCase());
    if (!w) {
      $hint.addClass('d-none').text('');
      return;
    }
    const unit = w.unitName || 'Pieces';
    const qty = Number(w.qty || 0).toLocaleString(undefined, { maximumFractionDigits: 2 });
    // Stock beside Work label (top of list)
    $hint.removeClass('d-none').text(`Stock: ${qty} ${unit}`);
  }

  function fillWorkSelect(works, selectedName) {
    // Only works that already have supplier net/sale rate
    productWorksCache = (works || []).filter(w => w.specialRate != null && Number(w.specialRate) > 0);
    if (!productWorksCache.length) {
      hideWorkField();
      return;
    }

    showWorkField();
    const current = selectedName || '';
    const $w = $('#workSelect').empty().append('<option value="">Select Work</option>');
    productWorksCache.forEach(w => {
      const name = w.workName || '';
      if (!name) return;
      const special = Number(w.specialRate || 0);
      const sel = current && String(current).toLowerCase() === String(name).toLowerCase() ? ' selected' : '';
      $w.append(
        `<option value="${name}" data-qty="${Number(w.qty || 0)}" data-unit="${w.unitName || 'Pieces'}" data-special="${special}"${sel}>${name} (Rs. ${fmt(special)})</option>`
      );
    });
    updateWorkStockHint();
  }

  function loadProductWorks(productCode, selectedName) {
    hideWorkField();
    if (!productCode || $('#txnType').val() === 'Cash') return;
    const customerCode = $('#customerCodeSubmit').val() || '';
    const type = $('#txnType').val() || '';
    if (!customerCode) return;
    $.get(`{{ route('supplierTransaction.productWorks') }}`, { productCode, customerCode, type })
      .done(function (res) {
        fillWorkSelect(res.data || [], selectedName || '');
        if (selectedName && $('#workSelect').val()) {
          const special = parseFloat($('#workSelect option:selected').data('special'));
          if (!isNaN(special) && special > 0) {
            $('#rate').val(special.toFixed(2));
            calcAmount();
          } else {
            loadProductRate();
          }
        }
      })
      .fail(function () {
        hideWorkField();
      });
  }

  /** Rate + works in one HTTP call (faster on php artisan serve). */
  function loadProductContext(productCode, selectedWork) {
    const customerCode = $('#customerCodeSubmit').val();
    const type = $('#txnType').val();
    const variantId = $('#variantSelect').val() || '';
    if (!customerCode || !productCode || type === 'Cash') return;
    if (productContextXhr && productContextXhr.readyState !== 4) {
      productContextXhr.abort();
    }
    productContextXhr = $.get(`{{ route('supplierTransaction.productContext') }}`, {
      customerCode,
      productCode,
      type,
      variant_id: variantId || null,
      workName: selectedWork || null
    }).done(function (res) {
      if (res.productName) $('#productNameHidden').val(res.productName);
      if (res.variants) fillVariants(productCode, res.variants);
      fillWorkSelect(res.works || [], selectedWork || '');
      const workVal = ($('#workSelect').val() || '').trim();
      if (workVal) {
        const special = parseFloat($('#workSelect option:selected').data('special'));
        if (!isNaN(special) && special > 0) {
          $('#rate').val(special.toFixed(2));
        } else {
          $('#rate').val(Number(res.rate || 0).toFixed(2));
        }
      } else if (res.hasNetRate) {
        $('#rate').val(Number(res.rate || 0).toFixed(2));
      } else {
        $('#rate').val('');
        if (res.message && typeof Swal !== 'undefined' && !(res.works || []).length) {
          Swal.fire({ icon: 'warning', title: 'Rate Missing', text: res.message });
        }
      }
      calcAmount();
    }).fail(function (xhr) {
      if (xhr.statusText === 'abort') return;
      hideWorkField();
      $('#rate').val('');
      calcAmount();
      const msg = xhr.responseJSON?.message || 'Unable to load product rate';
      if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'warning', title: 'Rate Missing', text: msg });
      }
    });
  }

  let nrCatalogProducts = [];

  function ensureProductCatalog() {
    if (catalogLoaded) {
      return $.Deferred().resolve().promise();
    }
    if (catalogXhr && catalogXhr.readyState !== 4) {
      return catalogXhr;
    }
    catalogXhr = $.get(`{{ route('supplierTransaction.catalog') }}`)
      .done(function (res) {
        productVariants = res.productVariants || {};
        nrCatalogProducts = (res.products || []).map(function (p) {
          return {
            code: p.productCode,
            name: p.productName || '',
            company: p.companyName || '',
            sale: Number(p.saleRate || 0),
            cost: Number(p.costRate || 0),
            stockQty: Number(p.stockQty || 0),
            hasVariants: !!p.hasVariants,
            image: p.image || ''
          };
        });
        catalogLoaded = true;
      });
    return catalogXhr;
  }

  function nrProductByCode(code) {
    return nrCatalogProducts.find(function (p) { return String(p.code) === String(code); }) || null;
  }

  function hideNrProductDropdown() {
    const $dd = $('#nrProductDropdown');
    if (typeof erpResetFloatingDropdown === 'function') {
      erpResetFloatingDropdown($dd[0]);
    }
    $dd.hide().empty();
  }

  function syncNrProductDropdownPosition() {
    const $dd = $('#nrProductDropdown');
    if (typeof erpPositionFloatingDropdown === 'function' && $dd.is(':visible')) {
      erpPositionFloatingDropdown($('#nrProductSearch')[0], $dd[0]);
    }
  }

  function setNrProductClearVisible(show) {
    const $btn = $('#btnClearNrProduct');
    const $inp = $('#nrProductSearch');
    if (show) {
      $btn.css('display', 'inline-flex');
      $inp.addClass('has-product');
    } else {
      $btn.hide();
      $inp.removeClass('has-product');
    }
  }

  function filterNrProducts(term) {
    const t = (term || '').toLowerCase().trim();
    const $dd = $('#nrProductDropdown').empty();
    if (!t) {
      hideNrProductDropdown();
      return;
    }
    const filtered = nrCatalogProducts.filter(function (p) {
      return p.name.toLowerCase().includes(t)
        || p.company.toLowerCase().includes(t)
        || String(p.code).toLowerCase().includes(t);
    }).slice(0, 10);

    if (!filtered.length) {
      $dd.html('<div class="dropdown-item text-muted">No products found</div>').show();
      syncNrProductDropdownPosition();
      return;
    }

    const type = $('#nrRateType').val() || 'Sale';
    let html = '';
    filtered.forEach(function (p, idx) {
      let stock = Number(p.stockQty || 0);
      if (p.hasVariants && productVariants[p.code] && productVariants[p.code].length) {
        stock = productVariants[p.code].reduce(function (sum, v) {
          return sum + (parseFloat(v.stockQty) || 0);
        }, 0);
      }
      const rate = type === 'Sale' ? p.sale : p.cost;
      const stockClass = stock <= 0 ? 'bg-danger' : (stock <= 5 ? 'bg-warning text-dark' : 'bg-success');
      const thumb = p.image
        ? `<img src="${p.image}" alt="" class="product-search-thumb rounded border">`
        : `<span class="product-search-thumb product-search-thumb-empty rounded border d-inline-flex align-items-center justify-content-center bg-light text-muted"><i class="bi bi-image"></i></span>`;
      html += `<a class="dropdown-item d-flex align-items-center gap-2 ${idx === 0 ? 'active' : ''}" href="#" data-code="${p.code}">
        ${thumb}
        <div class="flex-grow-1 min-w-0">
          <strong class="d-block text-truncate">${p.name}</strong>
          <div class="text-muted small text-truncate">${p.company || p.code}</div>
        </div>
        <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
          <span class="badge ${stockClass}">Stock: ${Number.isInteger(stock) ? stock : stock.toFixed(2)}</span>
          <span class="badge bg-info">Rs.${Number(rate || 0).toFixed(2)}</span>
        </div>
      </a>`;
    });
    $dd.html(html).show();
    syncNrProductDropdownPosition();
  }

  function selectNrProduct(code) {
    const p = nrProductByCode(code);
    if (!p) return;
    const label = p.name + (p.company ? ' - ' + p.company : '');
    $('#nrProductCode').val(p.code);
    $('#nrProductSearch').val(label);
    setNrProductClearVisible(true);
    hideNrProductDropdown();
    $('#nrProductCode').trigger('change');
  }

  function clearNrProductSelection() {
    $('#nrProductCode').val('');
    $('#nrProductSearch').val('');
    setNrProductClearVisible(false);
    hideNrProductDropdown();
    clearNrWorkSelect();
    $('#nrVariantWrap').addClass('d-none');
    $('#nrVariantSelect').html('<option value="">No Variant</option>');
  }

  function setProductClearVisible(show) {
    const $btn = $('#btnClearProduct');
    const $inp = $('#productSearch');
    if (show) {
      $btn.css('display', 'inline-flex');
      $inp.addClass('has-product');
    } else {
      $btn.hide();
      $inp.removeClass('has-product');
    }
  }

  function clearProduct() {
    $('#productSearch').val('');
    $('#productCodeSubmit').val('');
    $('#productNameHidden').val('');
    $('#productDropdown').hide().empty();
    setProductClearVisible(false);
    $('#variantWrap').addClass('d-none');
    $('#variantSelect').html('<option value="">Select Variant</option>');
    hideWorkField();
    $('#rate, #qty, #amount').val('');
  }

  function loadProducts() {
    const customerCode = $('#customerCodeSubmit').val();
    const type = $('#txnType').val();
    if (!customerCode) {
      productList = [];
      return;
    }
    $.get(`{{ url('/supplier-transaction/products') }}`, { customerCode, type })
      .done(res => {
        productList = res.products || [];
      })
      .fail(() => {
        productList = [];
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'error', title: 'Failed', text: 'Unable to load products' });
        }
      });
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
    if (!$('#customerCodeSubmit').val()) {
      $dd.html('<div class="dropdown-item text-muted">Select supplier first</div>').show();
      return;
    }
    const type = $('#txnType').val();
    const emptyMsg = type === 'Sale'
      ? 'No products with sale rate for this supplier'
      : 'No products with purchase net rate for this supplier';
    if (!list.length) {
      $dd.html(`<div class="dropdown-item text-muted">${emptyMsg}</div>`).show();
      return;
    }
    let html = '';
    list.forEach(function (p, idx) {
      const rate = Number(p.displayRate != null ? p.displayRate : (p.netRate || 0));
      let stock = Number(p.stockQty || 0);
      if (p.hasVariants && productVariants[p.productCode] && productVariants[p.productCode].length) {
        stock = productVariants[p.productCode].reduce(function (sum, v) {
          return sum + (parseFloat(v.stockQty) || 0);
        }, 0);
      }
      const stockClass = stock <= 0 ? 'bg-danger' : (stock <= 5 ? 'bg-warning text-dark' : 'bg-success');
      const thumb = p.image
        ? `<img src="${p.image}" alt="" class="product-search-thumb rounded border">`
        : `<span class="product-search-thumb product-search-thumb-empty rounded border d-inline-flex align-items-center justify-content-center bg-light text-muted"><i class="bi bi-image"></i></span>`;
      html += `<a class="dropdown-item d-flex align-items-center gap-2 ${idx === 0 ? 'active' : ''}" href="#" data-code="${p.productCode}">
        ${thumb}
        <div class="flex-grow-1 min-w-0">
          <strong class="d-block text-truncate">${p.productName || ''}</strong>
          <div class="text-muted small text-truncate">${p.companyName || p.productCode || ''}</div>
        </div>
        <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
          <span class="badge ${stockClass}">Stock: ${Number.isInteger(stock) ? stock : stock.toFixed(2)}</span>
          <span class="badge bg-info">Rs.${rate.toFixed(2)}</span>
        </div>
      </a>`;
    });
    $dd.html(html).show();
  }

  function fillVariants(productCode, variantsFromApi) {
    const local = productVariants[productCode] || [];
    const variants = (variantsFromApi && variantsFromApi.length) ? variantsFromApi : local;
    const $wrap = $('#variantWrap');
    const $sel = $('#variantSelect').empty().append('<option value="">Select Variant</option>');
    if (!variants.length) {
      $wrap.addClass('d-none');
      return;
    }
    variants.forEach(v => {
      $sel.append(`<option value="${v.id}" data-name="${v.name || ''}" data-value="${v.value || ''}">${v.name || ''} ${v.value || ''}</option>`);
    });
    $wrap.removeClass('d-none');
  }

  function loadProductRate() {
    const customerCode = $('#customerCodeSubmit').val();
    const productCode = $('#productCodeSubmit').val();
    const type = $('#txnType').val();
    const variantId = $('#variantSelect').val() || '';
    const workName = ($('#workSelect').val() || '').trim();
    if (!customerCode || !productCode) return;

    $.get(`{{ url('/supplier-transaction/product-rate') }}`, {
      customerCode,
      productCode,
      type,
      variant_id: variantId || null,
      workName: workName || null
    }).done(res => {
      $('#rate').val(Number(res.rate || 0).toFixed(2));
      if (res.productName) $('#productNameHidden').val(res.productName);
      if (!$('#variantSelect').val() && res.variants) {
        fillVariants(productCode, res.variants);
      }
      calcAmount();
    }).fail(function (xhr) {
      $('#rate').val('');
      calcAmount();
      const msg = xhr.responseJSON?.message || (type === 'Sale'
        ? 'No sale rate set for this product'
        : 'No net rate set for this product');
      if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'warning', title: 'Rate Missing', text: msg });
      }
    });
  }

  function selectProduct(code) {
    const p = productList.find(x => x.productCode === code);
    if (!p) return;
    $('#productCodeSubmit').val(code);
    $('#productNameHidden').val(p.productName || '');
    $('#productSearch').val(p.productName + (p.companyName ? ' - ' + p.companyName : ''));
    setProductClearVisible(true);
    $('#productDropdown').hide();
    $('#variantWrap').addClass('d-none');
    $('#variantSelect').html('<option value="">Select Variant</option>');
    hideWorkField();
    // One request: product rate + rated works + stock
    loadProductContext(code);
  }

  $('#workSelect').on('change', function () {
    updateWorkStockHint();
    // Work selected → use option specialRate (no extra HTTP); empty → product-only rate API
    const special = parseFloat($(this).find('option:selected').data('special'));
    if ($(this).val() && !isNaN(special) && special > 0) {
      $('#rate').val(special.toFixed(2));
      calcAmount();
      return;
    }
    loadProductRate();
  });

  $('#productSearch').on('focus input', function () {
    if (!$('#productCodeSubmit').val()) {
      setProductClearVisible(false);
    }
    if (!productList.length && $('#customerCodeSubmit').val()) {
      loadProducts();
    }
    renderProductDropdown(filterProducts($(this).val()));
  });

  $('#productSearch').on('keydown', function (e) {
    const $dd = $('#productDropdown');
    const $items = $dd.find('.dropdown-item[data-code]');
    const $active = $items.filter('.active');
    let newIndex = -1;
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      if (!$items.length) return;
      newIndex = $active.length ? Math.min($items.index($active) + 1, $items.length - 1) : 0;
      $items.removeClass('active').eq(newIndex).addClass('active');
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      if (!$active.length) return;
      newIndex = Math.max($items.index($active) - 1, 0);
      $items.removeClass('active').eq(newIndex).addClass('active');
    } else if (e.key === 'Enter') {
      e.preventDefault();
      const $selected = $dd.is(':visible') ? $items.filter('.active') : $();
      if ($selected.length && $selected.data('code')) {
        selectProduct($selected.data('code'));
      }
    } else if (e.key === 'Escape') {
      $dd.hide();
    }
  });

  $(document).on('click', '#productDropdown .dropdown-item[data-code]', function (e) {
    e.preventDefault();
    selectProduct($(this).data('code'));
  });

  $('#btnClearProduct').on('click', function () {
    clearProduct();
    $('#productSearch').focus();
  });

  $('#variantSelect').on('change', loadProductRate);
  function updateRateLabel() {
    const type = $('#txnType').val();
    $('#rateLabelText').text(type === 'Sale' ? 'Sale Rate' : 'Net Rate');
  }

  function toggleCashFields() {
    applyTypeUi($('#txnType').val(), { resetCash: !editingId });
    if ($('#txnType').val() !== 'Cash' && $('#customerCodeSubmit').val() && !editingId) {
      loadProducts();
    }
  }

  $('#txnType').on('change', function () {
    if (editingId) {
      applyTypeUi($(this).val(), { resetCash: true });
      if ($(this).val() !== 'Cash' && $('#customerCodeSubmit').val()) {
        loadProducts();
      }
      return;
    }
    updateRateLabel();
    clearProduct();
    toggleCashFields();
  });
  updateRateLabel();
  toggleCashFields();
  $('#rate, #qty').on('input change', calcAmount);
  $('#amount, #cashEntryType').on('input change', updateProjectedBalances);
  $('#cashBankAccount').on('change', function () {
    loadBankBalance($(this).val() || '');
  });
  $('#txnType').on('change.updateProj', updateProjectedBalances);

  $(document).on('click', function (e) {
    if (!$(e.target).closest('#customerSearch, #customerDropdown').length) {
      $('#customerDropdown').hide();
    }
    if (!$(e.target).closest('#productSearch, #productDropdown').length) {
      $('#productDropdown').hide();
    }
  });

  // ----- Net Rate Modal -----
  let nrRatesCache = [];

  function clearNrWorkSelect() {
    $('#nrWorkSelect').html('<option value="">Product only (no work)</option>');
    $('#nrWorkStockHint').addClass('d-none').text('');
  }

  function updateNrWorkStockHint() {
    const $opt = $('#nrWorkSelect option:selected');
    const workName = ($opt.val() || '').trim();
    const $hint = $('#nrWorkStockHint');
    if (!workName) {
      $hint.addClass('d-none').text('');
      return;
    }
    const qty = Number($opt.data('qty') || 0);
    const unit = $opt.data('unit') || 'Pieces';
    $hint.removeClass('d-none').html(
      `Stock: <strong class="text-success">${qty.toLocaleString(undefined, { maximumFractionDigits: 2 })} ${unit}</strong>`
    );
  }

  function loadNrProductWorks(productCode, selectedWork) {
    clearNrWorkSelect();
    if (!productCode) return $.Deferred().resolve([]).promise();
    return $.get(`{{ route('supplierTransaction.productWorks') }}`, { productCode })
      .done(function (res) {
        const rows = res.data || [];
        const $w = $('#nrWorkSelect');
        rows.forEach(w => {
          const name = w.workName || '';
          if (!name) return;
          const unit = w.unitName || 'Pieces';
          const qty = Number(w.qty || 0);
          const sel = selectedWork && String(selectedWork).toLowerCase() === String(name).toLowerCase() ? ' selected' : '';
          $w.append(
            `<option value="${name}" data-qty="${qty}" data-unit="${unit}" data-rate="${w.rate != null ? w.rate : ''}"${sel}>${name} (Stock: ${qty} ${unit})</option>`
          );
        });
        updateNrWorkStockHint();
      })
      .fail(function () {
        clearNrWorkSelect();
      });
  }

  function resetNrForm(keepType) {
    const type = keepType ? ($('#nrRateType').val() || $('#txnType').val() || 'Sale') : ($('#txnType').val() || 'Sale');
    $('#nrEditId').val('');
    clearNrProductSelection();
    $('#nrRateType').val(type);
    updateNrRateAmountLabel();
    $('#nrSpecialRate').val('');
    $('#nrEffectiveFrom').val(new Date().toISOString().slice(0, 10));
    $('#nrDiscountPercent').val('0');
    $('#nrRemarks').val('');
    $('#nrSaveBtn, #nrSaveBtnFooter').html('<i class="bi bi-save me-1"></i>Save Net Rate');
  }

  function openNetRateModal() {
    const code = $('#customerCodeSubmit').val();
    if (!code) {
      Swal.fire({ icon: 'warning', title: 'Select Supplier', text: 'Please select a supplier first' });
      return;
    }
    const $opt = $('#customerCode option[value="' + code + '"]');
    const label = ($opt.data('name') || '') + ' (' + code + ')';
    $('#nrCustomerCode').val(code);
    $('#nrCustomerLabel').val(label);

    resetNrForm(false);
    loadNrRates(code);

    const selectedProduct = $('#productCodeSubmit').val();
    const selectedWork = $('#workSelect').val() || '';
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('netRateModal'));
    modal.show();

    ensureProductCatalog().always(function () {
      if (selectedProduct) {
        selectNrProduct(selectedProduct);
        if (selectedWork) {
          setTimeout(function () {
            loadNrProductWorks(selectedProduct, selectedWork);
          }, 30);
        }
      }
    });
  }

  function updateNrRateAmountLabel() {
    const type = $('#nrRateType').val();
    $('#nrRateAmountLabel').html(
      (type === 'Sale' ? 'Sale Rate' : 'Net Rate') + ' <span class="text-danger">*</span>'
    );
  }

  function loadNrRates(customerCode) {
    const code = customerCode || $('#nrCustomerCode').val();
    const $tb = $('#nrRatesTable tbody');
    if (!code) {
      $tb.html('<tr><td colspan="7" class="text-center text-muted py-3">Select supplier first</td></tr>');
      $('#nrRatesHint').text('—');
      return;
    }
    $('#nrRatesHint').text('Loading...');
    $.get(`{{ url('/customer-discount/rates') }}/${encodeURIComponent(code)}`)
      .done(function (res) {
        nrRatesCache = res.data || [];
        renderNrRates(nrRatesCache);
        $('#nrRatesHint').text(nrRatesCache.length + ' rate' + (nrRatesCache.length === 1 ? '' : 's'));
      })
      .fail(function () {
        nrRatesCache = [];
        $tb.html('<tr><td colspan="7" class="text-center text-danger py-3">Failed to load rates</td></tr>');
        $('#nrRatesHint').text('Failed');
      });
  }

  function renderNrRates(rates) {
    const $tb = $('#nrRatesTable tbody').empty();
    if (!rates.length) {
      $tb.html('<tr><td colspan="7" class="text-center text-muted py-3">No rates saved for this supplier</td></tr>');
      return;
    }
    rates.forEach((r, idx) => {
      const typeBadge = r.rateType === 'Purchase'
        ? '<span class="badge text-bg-warning text-dark">Purchase</span>'
        : '<span class="badge text-bg-success">Sale</span>';
      $tb.append(`
        <tr>
          <td class="text-center">${idx + 1}</td>
          <td>
            <div class="fw-semibold">${r.productName || r.productCode}</div>
            <small class="text-muted">${r.productCode}${r.companyName ? ' | ' + r.companyName : ''}</small>
          </td>
          <td>${r.workName ? `<span class="text-info">${r.workName}</span>` : '<span class="text-muted">—</span>'}</td>
          <td>${typeBadge}</td>
          <td class="text-end fw-semibold">${fmt(r.specialRate)}</td>
          <td>${r.effective_from || '-'}</td>
          <td class="text-center">
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-outline-secondary btn-nr-edit" data-id="${r.id}" title="Edit"><i class="bi bi-pencil"></i></button>
              <button type="button" class="btn btn-outline-danger btn-nr-remove" data-id="${r.id}" title="Remove"><i class="bi bi-trash"></i></button>
            </div>
          </td>
        </tr>
      `);
    });
  }

  function editNrRate(id) {
    const rate = nrRatesCache.find(r => String(r.id) === String(id));
    if (!rate) return;

    $('#nrEditId').val(rate.id);
    $('#nrRateType').val(rate.rateType || 'Sale');
    updateNrRateAmountLabel();
    ensureProductCatalog().always(function () {
      const p = nrProductByCode(rate.productCode);
      const label = p
        ? (p.name + (p.company ? ' - ' + p.company : ''))
        : ((rate.productName || rate.productCode) + (rate.companyName ? ' - ' + rate.companyName : ''));
      $('#nrProductCode').val(rate.productCode);
      $('#nrProductSearch').val(label);
      setNrProductClearVisible(true);
    });

    loadNrProductWorks(rate.productCode, rate.workName || '').always(function () {
      // Apply variant after options render
      const variants = productVariants[rate.productCode] || [];
      const $wrap = $('#nrVariantWrap');
      const $sel = $('#nrVariantSelect').empty().append('<option value="">No Variant</option>');
      if (variants.length) {
        variants.forEach(v => {
          $sel.append(`<option value="${v.id}" data-name="${v.name || ''}" data-value="${v.value || ''}" data-add-price="${v.additionalPrice || 0}" data-add-cost="${v.additionalCost || 0}">${v.name || ''} ${v.value || ''}</option>`);
        });
        $wrap.removeClass('d-none');
      } else {
        $wrap.addClass('d-none');
      }
      if (rate.variant_id) {
        $('#nrVariantSelect').val(String(rate.variant_id));
        if (!$('#nrVariantSelect').val()) {
          $('#nrVariantSelect').append(
            `<option value="${rate.variant_id}" data-name="${rate.variant_name || ''}" data-value="${rate.variant_value || ''}" selected>${rate.variant_name || ''} ${rate.variant_value || ''}</option>`
          );
        }
      }
      $('#nrSpecialRate').val(Number(rate.specialRate || 0).toFixed(2));
    });

    $('#nrEffectiveFrom').val(rate.effective_from_iso || new Date().toISOString().slice(0, 10));
    $('#nrDiscountPercent').val(rate.discountPercent || 0);
    $('#nrRemarks').val(rate.remarks || '');
    $('#nrSaveBtn, #nrSaveBtnFooter').html('<i class="bi bi-check2-circle me-1"></i>Update Net Rate');

    const modalBody = document.querySelector('#netRateModal .modal-body');
    if (modalBody) modalBody.scrollTop = 0;
  }

  function removeNrRate(id) {
    Swal.fire({
      title: 'Remove Rate?',
      text: 'This net rate will be deactivated.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, remove it!'
    }).then((result) => {
      if (!result.isConfirmed) return;
      $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
      $.ajax({
        url: `{{ url('/customer-discount') }}/${id}`,
        method: 'DELETE'
      })
        .done(function (res) {
          Swal.fire({ icon: 'success', title: 'Removed', text: res.message || 'Rate removed' });
          loadNrRates();
          loadProducts();
          if (String($('#nrEditId').val()) === String(id)) {
            resetNrForm(true);
          }
        })
        .fail(function (xhr) {
          Swal.fire({
            icon: 'error',
            title: 'Remove Failed',
            text: xhr.responseJSON?.message || 'Could not remove rate'
          });
        });
    });
  }

  $('#addNetRateLink').on('click', openNetRateModal);
  $('#nrFormResetBtn').on('click', function () { resetNrForm(true); });
  $(document).on('click', '.btn-nr-edit', function () { editNrRate($(this).data('id')); });
  $(document).on('click', '.btn-nr-remove', function () { removeNrRate($(this).data('id')); });
  $('#nrRateType').on('change', function () {
    updateNrRateAmountLabel();
    const code = $('#nrProductCode').val();
    if (code) $('#nrProductCode').trigger('change');
  });

  $('#nrProductSearch').on('input', function () {
    if (!$('#nrProductCode').val()) {
      setNrProductClearVisible(false);
    }
    filterNrProducts($(this).val());
  });

  $('#nrProductSearch').on('focus', function () {
    if ($(this).val()) filterNrProducts($(this).val());
  });

  $('#nrProductSearch').on('keydown', function (e) {
    const $dd = $('#nrProductDropdown');
    const $items = $dd.find('.dropdown-item[data-code]');
    const $active = $items.filter('.active');
    let newIndex = -1;
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      if (!$items.length) return;
      newIndex = $active.length ? Math.min($items.index($active) + 1, $items.length - 1) : 0;
      $items.removeClass('active').eq(newIndex).addClass('active');
      syncNrProductDropdownPosition();
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      if (!$active.length) return;
      newIndex = Math.max($items.index($active) - 1, 0);
      $items.removeClass('active').eq(newIndex).addClass('active');
      syncNrProductDropdownPosition();
    } else if (e.key === 'Enter') {
      e.preventDefault();
      const $selected = $dd.is(':visible') ? $items.filter('.active') : $();
      if ($selected.length && $selected.data('code')) {
        selectNrProduct($selected.data('code'));
      }
    } else if (e.key === 'Escape') {
      hideNrProductDropdown();
    }
  });

  $('#nrProductDropdown').on('click', '.dropdown-item[data-code]', function (e) {
    e.preventDefault();
    e.stopPropagation();
    selectNrProduct($(this).data('code'));
  });

  $('#btnClearNrProduct').on('click', function () {
    clearNrProductSelection();
    $('#nrSpecialRate').val('');
    $('#nrProductSearch').focus();
  });

  $(document).on('click', function (e) {
    if (!$(e.target).closest('#nrProductSearch, #nrProductDropdown, #btnClearNrProduct').length) {
      hideNrProductDropdown();
    }
  });

  $('#nrProductCode').on('change', function () {
    const code = $(this).val();
    const p = nrProductByCode(code);
    const type = $('#nrRateType').val() || $('#txnType').val();
    const base = p ? (type === 'Sale' ? p.sale : p.cost) : 0;
    $('#nrSpecialRate').val(base ? Number(base).toFixed(2) : '');

    loadNrProductWorks(code);

    const variants = productVariants[code] || [];
    const $wrap = $('#nrVariantWrap');
    const $sel = $('#nrVariantSelect').empty().append('<option value="">No Variant</option>');
    if (!variants.length) {
      $wrap.addClass('d-none');
      return;
    }
    variants.forEach(function (v) {
      $sel.append(`<option value="${v.id}" data-name="${v.name || ''}" data-value="${v.value || ''}" data-add-price="${v.additionalPrice || 0}" data-add-cost="${v.additionalCost || 0}">${v.name || ''} ${v.value || ''}</option>`);
    });
    $wrap.removeClass('d-none');
  });

  $('#nrWorkSelect').on('change', function () {
    updateNrWorkStockHint();
    const workRate = parseFloat($(this).find('option:selected').data('rate'));
    if ($(this).val() && !isNaN(workRate) && workRate > 0) {
      $('#nrSpecialRate').val(workRate.toFixed(2));
    }
  });

  $('#nrVariantSelect').on('change', function () {
    const code = $('#nrProductCode').val();
    const p = nrProductByCode(code);
    const type = $('#nrRateType').val() || $('#txnType').val();
    let base = p ? (type === 'Sale' ? p.sale : p.cost) : 0;
    const $v = $(this).find('option:selected');
    if ($v.val()) {
      const add = type === 'Sale' ? parseFloat($v.data('add-price')) || 0 : parseFloat($v.data('add-cost')) || 0;
      base += add;
    }
    $('#nrSpecialRate').val(Number(base).toFixed(2));
  });

  $('#netRateForm').on('submit', function (e) {
    e.preventDefault();
    const customerCode = $('#nrCustomerCode').val();
    const productCode = $('#nrProductCode').val();
    const specialRate = $('#nrSpecialRate').val();
    const rateType = $('#nrRateType').val() || 'Sale';
    if (!customerCode || !productCode || !specialRate) {
      Swal.fire({ icon: 'warning', title: 'Required', text: 'Supplier, product and rate are required' });
      return;
    }
    const $v = $('#nrVariantSelect option:selected');
    const workName = ($('#nrWorkSelect').val() || '').trim();
    const payload = {
      customerCode,
      productCode,
      workName: workName || null,
      specialRate,
      rateType,
      discountPercent: $('#nrDiscountPercent').val() || 0,
      remarks: $('#nrRemarks').val() || '',
      effective_from: $('#nrEffectiveFrom').val(),
      variant_id: $v.val() || null,
      variant_name: $v.data('name') || null,
      variant_value: $v.data('value') || null,
    };

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    $.post(`{{ route('customerDiscount.store') }}`, payload)
      .done(function (res) {
        if (typeof showSuccess === 'function') {
          showSuccess(res.message || 'Net rate saved');
        } else {
          Swal.fire({ icon: 'success', title: 'Saved', text: res.message || 'Net rate saved' });
        }

        resetNrForm(true);
        loadNrRates();
        loadProducts();

        // If main form type matches saved rate type, refresh selected product rate
        if ($('#txnType').val() === rateType && $('#productCodeSubmit').val() === productCode) {
          loadProductRate();
        } else if ($('#txnType').val() === rateType && !$('#productCodeSubmit').val()) {
          setTimeout(() => {
            $.get(`{{ url('/supplier-transaction/products') }}`, {
              customerCode: $('#customerCodeSubmit').val(),
              type: rateType
            }).done(r => {
              productList = r.products || [];
              if (productList.find(p => p.productCode === productCode)) {
                selectProduct(productCode);
              }
            });
          }, 200);
        }
      })
      .fail(function (xhr) {
        Swal.fire({
          icon: 'error',
          title: 'Failed',
          text: xhr.responseJSON?.message || 'Could not save net rate'
        });
      });
  });

  // ----- Save transaction -----
  $('#supplierTranForm').on('submit', function (e) {
    e.preventDefault();
    const customerCode = $('#customerCodeSubmit').val();
    const productCode = $('#productCodeSubmit').val();
    const type = $('#txnType').val();
    const isCash = type === 'Cash';
    const rate = parseFloat($('#rate').val()) || 0;
    const qty = parseFloat($('#qty').val()) || 0;
    if (!isCash) calcAmount();
    const amount = parseFloat($('#amount').val()) || 0;
    const description = ($('#cashDescription').val() || '').trim();

    if (!customerCode) {
      Swal.fire({ icon: 'warning', title: 'Required', text: 'Please select a supplier' });
      return;
    }
    const entryType = $('#cashEntryType').val() || 'pay';
    if (isCash) {
      if (!description) {
        Swal.fire({ icon: 'warning', title: 'Required', text: 'Please enter description' });
        return;
      }
      if (amount <= 0) {
        Swal.fire({ icon: 'warning', title: 'Required', text: 'Enter a valid amount' });
        return;
      }
      if (!['pay', 'receive'].includes(entryType)) {
        Swal.fire({ icon: 'warning', title: 'Required', text: 'Please select Entry Type (Pay / Receive)' });
        return;
      }
    } else {
      if (!productCode) {
        Swal.fire({ icon: 'warning', title: 'Required', text: 'Please select a product' });
        return;
      }
      if (rate < 0 || qty <= 0) {
        Swal.fire({ icon: 'warning', title: 'Required', text: 'Enter valid rate and quantity' });
        return;
      }
    }

    const $v = $('#variantSelect option:selected');
    const editId = $('#editTxnId').val() || editingId;
    const workName = ($('#workSelect').val() || '').trim();
    const bankAccount = ($('#cashBankAccount').val() || '').trim();
    if (isCash && !bankAccount) {
      Swal.fire({ icon: 'warning', title: 'Required', text: 'Please select a bank account' });
      return;
    }

    const payload = isCash
      ? { customerCode, type, entryType, bankAccount, description, amount, rate: amount, qty: 1 }
      : {
          customerCode,
          type,
          productCode,
          workName: workName || null,
          rate,
          qty,
          amount,
          variant_id: $v.val() || null,
          variant_name: $v.data('name') || null,
          variant_value: $v.data('value') || null,
        };

    const $btn = $('#btnSave').prop('disabled', true);
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    const req = editId
      ? $.ajax({
          url: `{{ url('/supplier-transaction/update') }}/${encodeURIComponent(editId)}`,
          method: 'PUT',
          data: payload
        })
      : $.post(`{{ route('supplierTransaction.store') }}`, payload);

    req
      .done(function (res) {
        Swal.fire({
          icon: 'success',
          title: editId ? 'Updated' : 'Saved',
          html: `${res.message || (editId ? 'Transaction updated' : 'Transaction saved')}<br><strong>${res.txnNo || ''}</strong>`,
          confirmButtonText: 'OK'
        });
        cancelEdit();
        $('#rate, #qty, #amount, #cashDescription').val('');
        if ($('#txnType').val() === 'Cash') {
          applyTypeUi('Cash', { resetCash: true });
        }
        clearProduct();
        loadSupplierContext();
      })
      .fail(function (xhr) {
        Swal.fire({
          icon: 'error',
          title: editId ? 'Update Failed' : 'Save Failed',
          text: xhr.responseJSON?.message || 'Could not save transaction'
        });
      })
      .always(() => $btn.prop('disabled', false));
  });
})();
</script>

@endsection
