@extends('layout.app')

@section('title', 'Customer Account Transaction')

@section('content')

@include('components.toast-notification')

<style>
  .acct-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
  }
  #customerDropdown.dropdown-menu,
  #bankDropdown.dropdown-menu,
  #toCustomerDropdown.dropdown-menu {
    max-height: 300px;
    overflow-y: auto;
    width: 100%;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-top: 4px;
  }
  #customerDropdown .dropdown-item.active,
  #customerDropdown .dropdown-item:hover,
  #bankDropdown .dropdown-item.active,
  #bankDropdown .dropdown-item:hover,
  #toCustomerDropdown .dropdown-item.active,
  #toCustomerDropdown .dropdown-item:hover {
    background: linear-gradient(135deg, #1a4a8a 0%, #0f2747 100%);
    color: #fff;
  }
  #customerDropdown .dropdown-item:hover .text-muted,
  #customerDropdown .dropdown-item.active .text-muted,
  #bankDropdown .dropdown-item:hover .text-muted,
  #bankDropdown .dropdown-item.active .text-muted,
  #toCustomerDropdown .dropdown-item:hover .text-muted,
  #toCustomerDropdown .dropdown-item.active .text-muted {
    color: rgba(255,255,255,0.9) !important;
  }
  #ledgerTable {
    font-size: 0.875rem;
  }
  #ledgerTable th,
  #ledgerTable td {
    white-space: nowrap;
    vertical-align: middle;
    padding-top: 0.35rem;
    padding-bottom: 0.35rem;
  }
  #ledgerTable td.ledger-desc {
    max-width: 28rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  #ledgerTable tbody tr {
    cursor: pointer;
  }
  #ledgerTable tbody tr.table-active {
    --bs-table-bg-state: #dbeafe;
    background-color: #dbeafe !important;
  }
</style>

<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-6">
          <h3 class="mb-0"><i class="bi bi-journal-text text-primary me-2"></i>Customer Account Transaction</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('accountTransaction.index') }}">Accounts</a></li>
            <li class="breadcrumb-item active" aria-current="page">Transaction</li>
          </ol>
        </div>
      </div>
      <hr class="mb-0" style="border-top: 2px solid #0d6efd;">
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <div class="card mb-4 shadow-sm">
        <div class="card-header acct-card-head py-2">
          <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Record Debit / Credit</h5>
        </div>
        <div class="card-body">
          <form id="accountTranForm">
            @csrf
            <div class="row g-2 align-items-end mb-3">
              <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Entry Category</label>
                <select class="form-select form-select-sm" name="entryCategory" id="entryCategory" required>
                  <option value="single" selected>Single Entry</option>
                  <option value="bank">Bank Entry</option>
                  <option value="customer">Customer Entry</option>
                </select>
              </div>
              <div class="col-md-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="form-label small fw-semibold mb-0">
                    <i class="bi bi-person-badge text-primary me-1"></i>Customer
                  </label>
                  <small id="customerBalanceDisplay" class="text-muted" style="display:none;">
                    <strong id="customerBalanceValue" class="text-primary">0.00</strong>
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
                         placeholder="Type to search customer..."
                         autocomplete="off"
                         required>
                  <input type="hidden" name="customerCode" id="customerCodeSubmit" value="">
                  <select class="form-select form-select-sm d-none" id="customerCode">
                    <option value="">Select Customer</option>
                    @foreach($customers as $c)
                      <option value="{{ $c->custCode }}"
                              data-name="{{ $c->custName }}"
                              data-business="{{ $c->bussnessName ?? '' }}"
                              data-address="{{ $c->custAddress ?? '' }}"
                              data-area="{{ $c->custArea }}"
                              data-city="{{ $c->custCity }}">
                        {{ $c->custName }}@if(!empty($c->bussnessName)) | {{ $c->bussnessName }}@endif - {{ $c->custArea }}
                      </option>
                    @endforeach
                  </select>
                  <div id="customerDropdown" class="dropdown-menu w-100" style="display:none;"></div>
                </div>
              </div>

              <div class="col-md-3" id="counterpartWrap" style="display:none;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="form-label small fw-semibold mb-0" id="counterpartLabel">
                    <i class="bi bi-bank text-success me-1"></i>Bank
                  </label>
                  <small id="counterpartBalanceDisplay" class="text-muted" style="display:none;">
                    <strong id="counterpartBalanceValue" class="text-primary">0.00</strong>
                    <span id="counterpartAfterWrap" class="ms-1" style="display:none;">
                      <i class="bi bi-arrow-right-short text-muted"></i>
                      <strong id="counterpartAfterValue" class="text-info">0.00</strong>
                    </span>
                  </small>
                </div>
                <div class="position-relative" id="bankSelectWrap" style="display:none;">
                  <input type="text"
                         class="form-control form-control-sm"
                         id="bankSearch"
                         placeholder="Type to search bank..."
                         autocomplete="off">
                  <input type="hidden" name="bankAccount" id="bankAccountSubmit" value="">
                  <select class="form-select form-select-sm d-none" id="bankAccount">
                    <option value="">Select Bank</option>
                    @foreach(($bankAccounts ?? []) as $bank)
                      <option value="{{ $bank->bankAccount }}"
                              data-name="{{ $bank->bankName }}"
                              data-branch="{{ $bank->branchCode ?? '' }}">
                        {{ $bank->bankName }} - {{ $bank->bankAccount }}@if($bank->branchCode) ({{ $bank->branchCode }})@endif
                      </option>
                    @endforeach
                  </select>
                  <div id="bankDropdown" class="dropdown-menu w-100" style="display:none;"></div>
                </div>
                <div class="position-relative" id="toCustomerSelectWrap" style="display:none;">
                  <input type="text"
                         class="form-control form-control-sm"
                         id="toCustomerSearch"
                         placeholder="Type to search customer..."
                         autocomplete="off">
                  <input type="hidden" name="toCustomerCode" id="toCustomerCodeSubmit" value="">
                  <select class="form-select form-select-sm d-none" id="toCustomerCode">
                    <option value="">Select Customer</option>
                    @foreach($customers as $c)
                      <option value="{{ $c->custCode }}"
                              data-name="{{ $c->custName }}"
                              data-business="{{ $c->bussnessName ?? '' }}"
                              data-area="{{ $c->custArea }}"
                              data-city="{{ $c->custCity }}">
                        {{ $c->custName }}@if(!empty($c->bussnessName)) | {{ $c->bussnessName }}@endif - {{ $c->custArea }}
                      </option>
                    @endforeach
                  </select>
                  <div id="toCustomerDropdown" class="dropdown-menu w-100" style="display:none;"></div>
                </div>
              </div>

              <div class="col-md-3" id="addressWrap">
                <label class="form-label small fw-semibold mb-1">
                  <i class="bi bi-geo-alt text-danger me-1"></i>Address
                </label>
                <div id="customerAddressDisplay" class="form-control form-control-sm bg-light text-muted" style="min-height:31px;">
                  Select a customer to view address
                </div>
              </div>
            </div>

            <div class="row g-2 align-items-end">
              <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Date</label>
                <input type="date" class="form-control form-control-sm" name="customerDate" id="customerDate" value="{{ now()->format('Y-m-d') }}">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">Description</label>
                <input type="text" class="form-control form-control-sm" name="description" id="description" placeholder="Transaction details..." required>
              </div>
              <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Amount (PKR)</label>
                <input type="number" step="0.01" min="0.01" class="form-control form-control-sm" name="amount" id="amount" placeholder="0.00" required>
              </div>
              <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Type</label>
                <select class="form-select form-select-sm" name="type" id="type" required>
                  <option value="credit">Credit</option>
                  <option value="debit">Debit</option>
                </select>
              </div>
              <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm" id="btnAcctTxnSave">
                  <i class="bi bi-save me-1"></i>Save
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-header acct-card-head d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
          <h5 class="mb-0"><i class="bi bi-table me-2"></i><span id="acctTxnTableTitle">All Account Transactions</span></h5>
          <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
            <div class="d-flex align-items-center gap-2 mb-0">
              <label class="small mb-0 text-white-50" for="ledgerPerPage">Rows</label>
              <select id="ledgerPerPage" class="form-select form-select-sm" style="width: 5rem;" aria-label="Rows per page">
                @foreach ([10, 25, 50, 100] as $size)
                  <option value="{{ $size }}" @selected($size === 25)>{{ $size }}</option>
                @endforeach
              </select>
            </div>
            <a href="{{ route('customerLedger.view') }}" class="btn btn-light btn-sm">
              <i class="bi bi-journal-text me-1"></i>Customer Ledger
            </a>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0" id="ledgerTable">
              <thead class="table-primary">
                <tr>
                  <th style="width: 110px;">Date</th>
                  <th style="min-width: 140px;">Customer</th>
                  <th style="width: 100px;">Area Code</th>
                  <th>Description</th>
                  <th class="text-end" style="width: 120px;">Credit</th>
                  <th class="text-end" style="width: 120px;">Debit</th>
                  <th class="text-center" style="width: 110px;">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="7" class="text-muted py-4 px-3">Loading transactions...</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div id="ledgerPager"></div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Edit Entry Modal -->
<div class="modal fade" id="editEntryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Ledger Entry <small class="text-muted">(<span id="editCategory"></span>)</small></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editEntryForm">
        @csrf
        <input type="hidden" id="editId">
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">Description</label>
            <input type="text" id="editDescription" class="form-control form-control-sm" required>
          </div>
          <div class="row g-2">
            <div class="col-6">
              <label class="form-label">Amount</label>
              <input type="number" id="editAmount" class="form-control form-control-sm" step="0.01" min="0.01" required>
            </div>
            <div class="col-6">
              <label class="form-label">Date</label>
              <input type="date" id="editDate" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
            </div>
          </div>
          <div class="mt-2" id="editTypeWrap" style="display:none;">
            <label class="form-label">Type</label>
            <select id="editType" class="form-select form-select-sm">
              <option value="credit">Credit</option>
              <option value="debit">Debit</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(function () {
  const fmt = (n) => (parseFloat(n || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  let primaryBalance = 0;
  let counterpartBalance = 0;
  let counterpartKind = null; // 'bank' | 'customer' | null
  let allLedgerEntries = @json($initialAccountTransactions ?? []);
  let ledgerPage = 1;
  const accountTxnListUrl = @json(route('accountTransaction.list', [], false));

  function projectPrimaryAfter(current, amount, type) {
    const amt = parseFloat(amount) || 0;
    if (amt <= 0) return null;
    // Customer receivable: debit increases, credit decreases
    return type === 'debit' ? current + amt : current - amt;
  }

  function projectCounterpartAfter(current, amount, type, kind) {
    const amt = parseFloat(amount) || 0;
    if (amt <= 0 || !kind) return null;
    if (kind === 'bank') {
      // Customer credit → bank debit (in); customer debit → bank credit (out)
      return type === 'credit' ? current + amt : current - amt;
    }
    // Other customer gets opposite of primary
    return type === 'credit' ? current + amt : current - amt;
  }

  function updateProjectedBalances() {
    const amount = $('#amount').val();
    const type = $('#type').val() || 'credit';

    if ($('#customerBalanceDisplay').is(':visible')) {
      const after = projectPrimaryAfter(primaryBalance, amount, type);
      if (after === null) {
        $('#customerAfterWrap').hide();
      } else {
        $('#customerAfterValue')
          .text(fmt(after))
          .removeClass('text-info text-danger text-primary')
          .addClass(after < 0 ? 'text-danger' : 'text-info');
        $('#customerAfterWrap').show();
      }
    } else {
      $('#customerAfterWrap').hide();
    }

    if ($('#counterpartBalanceDisplay').is(':visible') && counterpartKind) {
      const after = projectCounterpartAfter(counterpartBalance, amount, type, counterpartKind);
      if (after === null) {
        $('#counterpartAfterWrap').hide();
      } else {
        $('#counterpartAfterValue')
          .text(fmt(after))
          .removeClass('text-info text-danger text-primary')
          .addClass(after < 0 ? 'text-danger' : 'text-info');
        $('#counterpartAfterWrap').show();
      }
    } else {
      $('#counterpartAfterWrap').hide();
    }
  }

  $('#amount, #type').on('input change', updateProjectedBalances);

  function filterCustomers(term, excludeCode) {
    const q = String(term || '').toLowerCase().trim();
    const items = [];
    $('#customerCode option').each(function () {
      const val = $(this).val();
      if (!val || (excludeCode && val === excludeCode)) return;
      const name = String($(this).data('name') || '');
      const business = String($(this).data('business') || '');
      const area = String($(this).data('area') || '');
      const city = String($(this).data('city') || '');
      const hay = (name + ' ' + business + ' ' + area + ' ' + city + ' ' + val).toLowerCase();
      if (!q || hay.includes(q)) {
        items.push({ val, name, business, area, city });
      }
    });
    return items.slice(0, 30);
  }

  function renderCustomerDropdown(items, $dd, dataAttr) {
    $dd.empty();
    if (!items.length) {
      $dd.append('<div class="dropdown-item text-muted">No customers found</div>').show();
      return;
    }
    items.forEach((c, idx) => {
      const label = c.business ? `${c.name} | ${c.business}` : c.name;
      $dd.append(`
        <button type="button" class="dropdown-item ${idx === 0 ? 'active' : ''}" ${dataAttr}="${c.val}">
          <strong>${label}</strong>
          <span class="text-muted">${c.val} · ${c.area || '-'} ${c.city ? '· ' + c.city : ''}</span>
        </button>
      `);
    });
    $dd.show();
  }

  function filterBanks(term) {
    const q = String(term || '').toLowerCase().trim();
    const items = [];
    $('#bankAccount option').each(function () {
      const val = $(this).val();
      if (!val) return;
      const name = String($(this).data('name') || '');
      const branch = String($(this).data('branch') || '');
      const hay = (name + ' ' + branch + ' ' + val).toLowerCase();
      if (!q || hay.includes(q)) {
        items.push({ val, name, branch });
      }
    });
    return items.slice(0, 30);
  }

  function renderBankDropdown(items) {
    const $dd = $('#bankDropdown').empty();
    if (!items.length) {
      $dd.append('<div class="dropdown-item text-muted">No bank found</div>').show();
      return;
    }
    items.forEach((b, idx) => {
      $dd.append(`
        <button type="button" class="dropdown-item ${idx === 0 ? 'active' : ''}" data-bank="${b.val}">
          <strong>${b.name}</strong>
          <span class="text-muted">${b.val}${b.branch ? ' · ' + b.branch : ''}</span>
        </button>
      `);
    });
    $dd.show();
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

  function clearCounterpartBalance() {
    counterpartBalance = 0;
    counterpartKind = null;
    $('#counterpartBalanceDisplay, #counterpartAfterWrap').hide();
  }

  function clearBankSelection() {
    $('#bankAccount').val('');
    $('#bankAccountSubmit').val('');
    $('#bankSearch').val('');
    clearCounterpartBalance();
  }

  function clearToCustomerSelection() {
    $('#toCustomerCode').val('');
    $('#toCustomerCodeSubmit').val('');
    $('#toCustomerSearch').val('');
    clearCounterpartBalance();
  }

  function clearCustomerSelection() {
    $('#customerCode').val('');
    $('#customerCodeSubmit').val('');
    $('#customerSearch').val('');
    $('#customerDropdown').hide();
    primaryBalance = 0;
    $('#customerBalanceDisplay, #customerAfterWrap').hide();
    $('#customerAddressDisplay').removeClass('text-dark').addClass('text-muted').text('Select a customer to view address');
  }

  function updateAcctTxnTableTitle(customerCode) {
    if (!customerCode) {
      $('#acctTxnTableTitle').text('All Account Transactions');
      return;
    }
    const $opt = $('#customerCode option[value="' + customerCode + '"]');
    const name = $opt.data('name') || customerCode;
    $('#acctTxnTableTitle').text('Transactions — ' + name);
  }

  function renderLedger(entries) {
    allLedgerEntries = Array.isArray(entries) ? entries.slice() : [];
    showLedgerPage(1);
  }

  function loadAccountTransactions(customerCode) {
    const code = customerCode || '';
    updateAcctTxnTableTitle(code);
    return $.ajax({
      url: accountTxnListUrl,
      method: 'GET',
      data: code ? { customerCode: code } : {},
      dataType: 'json',
      cache: false,
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    }).done(function (res) {
      renderLedger((res && Array.isArray(res.data)) ? res.data : []);
    }).fail(function (xhr) {
      console.error('account-transaction/list failed', xhr.status, xhr.responseText);
      // Keep currently shown rows if any; only show error when empty
      if (!allLedgerEntries.length) {
        $('#ledgerTable tbody').html('<tr><td colspan="7" class="text-muted py-4 px-3">Failed to load transactions</td></tr>');
        $('#ledgerPager').empty();
      }
    });
  }

  function focusCustomerSearch() {
    const el = document.getElementById('customerSearch');
    if (!el) return;
    el.focus();
    if (typeof el.select === 'function') el.select();
  }

  window.focusCustomerSearch = focusCustomerSearch;
  window.loadAccountTransactions = loadAccountTransactions;

  function syncEntryCategoryUI() {
    const cat = $('#entryCategory').val();
    clearBankSelection();
    clearToCustomerSelection();
    $('#bankDropdown, #toCustomerDropdown').hide();

    if (cat === 'single') {
      $('#counterpartWrap').hide();
      $('#bankSelectWrap, #toCustomerSelectWrap').hide();
      updateProjectedBalances();
      return;
    }

    $('#counterpartWrap').show();
    if (cat === 'bank') {
      $('#counterpartLabel').html('<i class="bi bi-bank text-success me-1"></i>Bank');
      $('#bankSelectWrap').show();
      $('#toCustomerSelectWrap').hide();
    } else {
      $('#counterpartLabel').html('<i class="bi bi-people text-success me-1"></i>Other Customer');
      $('#bankSelectWrap').hide();
      $('#toCustomerSelectWrap').show();
    }
    updateProjectedBalances();
  }

  $('#entryCategory').on('change', syncEntryCategoryUI);
  syncEntryCategoryUI();

  function selectCustomer(code) {
    const $opt = $('#customerCode option[value="' + code + '"]');
    if (!$opt.length) return;
    $('#customerCode').val(code).trigger('change');
    $('#customerCodeSubmit').val(code);
    const name = $opt.data('name') || '';
    const business = $opt.data('business') || '';
    $('#customerSearch').val(business ? `${name} | ${business}` : name);
    showCustomerAddress($opt);
    $('#customerDropdown').hide();
    if ($('#toCustomerCodeSubmit').val() === code) {
      clearToCustomerSelection();
    }
  }

  function selectBank(code) {
    const $opt = $('#bankAccount option[value="' + code + '"]');
    if (!$opt.length) return;
    $('#bankAccount').val(code);
    $('#bankAccountSubmit').val(code);
    $('#bankSearch').val($opt.data('name') || code);
    $('#bankDropdown').hide();
    counterpartKind = 'bank';
    $.get(`{{ url('/api/bank-balance') }}/${encodeURIComponent(code)}`)
      .done(function (res) {
        counterpartBalance = parseFloat(res.balance ?? res.current_balance ?? 0) || 0;
        $('#counterpartBalanceValue')
          .text(fmt(counterpartBalance))
          .removeClass('text-danger')
          .addClass('text-primary');
        $('#counterpartBalanceDisplay').show();
        updateProjectedBalances();
      })
      .fail(function () {
        clearCounterpartBalance();
      });
  }

  function selectToCustomer(code) {
    const primary = $('#customerCodeSubmit').val();
    if (primary && code === primary) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'warning', title: 'Same Customer', text: 'Select a different customer.' });
      }
      return;
    }
    const $opt = $('#toCustomerCode option[value="' + code + '"]');
    if (!$opt.length) return;
    $('#toCustomerCode').val(code);
    $('#toCustomerCodeSubmit').val(code);
    const name = $opt.data('name') || '';
    const business = $opt.data('business') || '';
    $('#toCustomerSearch').val(business ? `${name} | ${business}` : name);
    $('#toCustomerDropdown').hide();
    counterpartKind = 'customer';
    $.get(`{{ url('/customer/balance') }}/${encodeURIComponent(code)}`)
      .done(function (res) {
        counterpartBalance = parseFloat(res.balance ?? 0) || 0;
        $('#counterpartBalanceValue')
          .text(fmt(counterpartBalance))
          .toggleClass('text-danger', res.balance_type === 'payable')
          .toggleClass('text-primary', res.balance_type !== 'payable');
        $('#counterpartBalanceDisplay').show();
        updateProjectedBalances();
      })
      .fail(function () {
        clearCounterpartBalance();
      });
  }

  $('#customerSearch').on('focus input', function () {
    renderCustomerDropdown(filterCustomers($(this).val()), $('#customerDropdown'), 'data-code');
  });

  $('#bankSearch').on('focus input', function () {
    renderBankDropdown(filterBanks($(this).val()));
  });

  $('#toCustomerSearch').on('focus input', function () {
    renderCustomerDropdown(
      filterCustomers($(this).val(), $('#customerCodeSubmit').val()),
      $('#toCustomerDropdown'),
      'data-to-code'
    );
  });

  $(document).on('click', '#customerDropdown .dropdown-item[data-code]', function () {
    selectCustomer($(this).data('code'));
  });
  $(document).on('click', '#bankDropdown .dropdown-item[data-bank]', function () {
    selectBank($(this).data('bank'));
  });
  $(document).on('click', '#toCustomerDropdown .dropdown-item[data-to-code]', function () {
    selectToCustomer($(this).data('to-code'));
  });

  $(document).on('click', function (e) {
    if (!$(e.target).closest('#customerSearch, #customerDropdown').length) {
      $('#customerDropdown').hide();
    }
    if (!$(e.target).closest('#bankSearch, #bankDropdown').length) {
      $('#bankDropdown').hide();
    }
    if (!$(e.target).closest('#toCustomerSearch, #toCustomerDropdown').length) {
      $('#toCustomerDropdown').hide();
    }
  });

  function moneyCell(value) {
    const amount = parseFloat(value) || 0;
    return amount > 0 ? fmt(amount) : '-';
  }

  function renderLedgerPager(meta) {
    const $pager = $('#ledgerPager');
    if (!meta || !meta.total) {
      $pager.empty();
      return;
    }
    let links = '';
    if (meta.last_page > 1) {
      links = '<nav><ul class="pagination pagination-sm mb-0">';
      if (meta.current_page > 1) {
        links += '<li class="page-item"><a class="page-link" href="#" data-page="' + (meta.current_page - 1) + '">Prev</a></li>';
      }
      links += '<li class="page-item disabled"><span class="page-link">Page ' + meta.current_page + ' / ' + meta.last_page + '</span></li>';
      if (meta.current_page < meta.last_page) {
        links += '<li class="page-item"><a class="page-link" href="#" data-page="' + (meta.current_page + 1) + '">Next</a></li>';
      }
      links += '</ul></nav>';
    }
    $pager.html(
      '<div class="card-footer bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">' +
      '<small class="text-muted">Showing ' + meta.from + '–' + meta.to + ' of ' + meta.total + '</small>' +
      '<div class="ms-auto">' + links + '</div></div>'
    );
  }

  function showLedgerPage(page) {
    const perPage = parseInt($('#ledgerPerPage').val(), 10) || 25;
    const total = allLedgerEntries.length;
    const lastPage = Math.max(1, Math.ceil(total / perPage) || 1);
    ledgerPage = Math.min(Math.max(1, page || 1), lastPage);
    const $tb = $('#ledgerTable tbody').empty();

    if (!total) {
      $tb.append('<tr><td colspan="7" class="text-muted py-4 px-3">No account transactions yet</td></tr>');
      renderLedgerPager(null);
      return;
    }

    const start = (ledgerPage - 1) * perPage;
    const slice = allLedgerEntries.slice(start, start + perPage);
    slice.forEach(e => {
      const cat = String(e.category || '').replace(/"/g, '&quot;');
      const desc = String(e.description ?? '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      const customer = String(e.customer || e.customerCode || '-').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      const area = String(e.areaCode || '-').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      const descLine = e.category
        ? desc + ' <span class="text-muted">— ' + String(e.category).replace(/</g, '&lt;') + '</span>'
        : desc;
      $tb.append(`
        <tr data-id="${e.id}" data-category="${cat}">
          <td>${e.date || ''}</td>
          <td>${customer}</td>
          <td>${area}</td>
          <td class="ledger-desc" title="${desc.replace(/"/g, '&quot;')}">${descLine}</td>
          <td class="text-end">${moneyCell(e.credit)}</td>
          <td class="text-end">${moneyCell(e.debit)}</td>
          <td class="text-center text-nowrap">
            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 btn-edit-ledger-row" data-id="${e.id}" title="Edit"><i class="bi bi-pencil"></i></button>
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 btn-delete-ledger-row" data-id="${e.id}" title="Delete"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
      `);
    });

    const from = start + 1;
    const to = start + slice.length;
    renderLedgerPager({
      current_page: ledgerPage,
      last_page: lastPage,
      from: from,
      to: to,
      total: total
    });
  }

  $('#ledgerPerPage').on('change', function () {
    showLedgerPage(1);
  });

  $(document).on('click', '#ledgerPager a[data-page]', function (e) {
    e.preventDefault();
    showLedgerPage(parseInt($(this).data('page'), 10) || 1);
  });

  $(document).on('click', '#ledgerTable tbody tr[data-id]', function (e) {
    if ($(e.target).closest('button, a, input, select, label').length) return;
    $('#ledgerTable tbody tr').removeClass('table-active');
    $(this).addClass('table-active');
  });

  $(document).on('click', '.btn-edit-ledger-row', function (e) {
    e.preventDefault();
    e.stopPropagation();
    const id = $(this).data('id');
    if (id) openEdit(id);
  });

  $(document).on('click', '.btn-delete-ledger-row', function (e) {
    e.preventDefault();
    e.stopPropagation();
    const id = $(this).data('id');
    if (id) deleteEntry(id);
  });

  $('#customerCode').on('change', function () {
    const code = $(this).val();
    if (!code) {
      primaryBalance = 0;
      $('#customerBalanceDisplay, #customerAfterWrap').hide();
      $('#customerAddressDisplay').removeClass('text-dark').addClass('text-muted').text('Select a customer to view address');
      loadAccountTransactions();
      updateProjectedBalances();
      return;
    }
    showCustomerAddress($(this).find('option:selected'));
    loadAccountTransactions(code);
    $.get(`{{ url('/customer/balance') }}/${encodeURIComponent(code)}`)
      .done(res => {
        primaryBalance = parseFloat(res.balance ?? 0) || 0;
        $('#customerBalanceValue')
          .text(fmt(primaryBalance))
          .toggleClass('text-danger', res.balance_type === 'payable')
          .toggleClass('text-primary', res.balance_type !== 'payable');
        $('#customerBalanceDisplay').show();
        updateProjectedBalances();
      })
      .fail(() => {
        primaryBalance = 0;
        $('#customerBalanceDisplay, #customerAfterWrap').hide();
      });
  });

  function focusNextAcctField(current) {
    const fields = Array.from(document.querySelectorAll(
      '#accountTranForm input:not([type="hidden"]), #accountTranForm select, #btnAcctTxnSave'
    )).filter(function (el) {
      if (el.disabled || el.readOnly) return false;
      if (el.closest('.d-none, .modal')) return false;
      const wrap = el.closest('#counterpartWrap, #bankSelectWrap, #toCustomerSelectWrap');
      if (wrap && getComputedStyle(wrap).display === 'none') return false;
      return el.offsetParent !== null || el === document.activeElement;
    });
    const index = fields.indexOf(current);
    const next = index >= 0 ? fields[index + 1] : null;
    const target = next || document.getElementById('btnAcctTxnSave');
    if (!target) return;
    target.focus();
    if (target.tagName === 'INPUT' && typeof target.select === 'function') target.select();
  }

  function moveDropdownActive($dd, direction, itemSelector) {
    const $items = $dd.find(itemSelector);
    if (!$items.length) return;
    let index = $items.index($items.filter('.active'));
    if (direction > 0) index = index < 0 ? 0 : Math.min(index + 1, $items.length - 1);
    else index = index <= 0 ? 0 : index - 1;
    $items.removeClass('active').eq(index).addClass('active');
  }

  $('#customerSearch').on('keydown', function (e) {
    const $dd = $('#customerDropdown');
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      if (!$dd.is(':visible')) renderCustomerDropdown(filterCustomers($(this).val()), $dd, 'data-code');
      moveDropdownActive($dd, e.key === 'ArrowDown' ? 1 : -1, '.dropdown-item[data-code]');
      return;
    }
    if (e.key === 'Escape') { $dd.hide(); return; }
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const $selected = $dd.is(':visible') ? $dd.find('.dropdown-item.active[data-code]') : $();
    if ($selected.length) selectCustomer($selected.data('code'));
    if ($('#customerCodeSubmit').val()) {
      $dd.hide();
      focusNextAcctField(this);
    }
  });

  $('#bankSearch').on('keydown', function (e) {
    const $dd = $('#bankDropdown');
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      if (!$dd.is(':visible')) renderBankDropdown(filterBanks($(this).val()));
      moveDropdownActive($dd, e.key === 'ArrowDown' ? 1 : -1, '.dropdown-item[data-bank]');
      return;
    }
    if (e.key === 'Escape') { $dd.hide(); return; }
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const $selected = $dd.is(':visible') ? $dd.find('.dropdown-item.active[data-bank]') : $();
    if ($selected.length) selectBank($selected.data('bank'));
    if ($('#bankAccountSubmit').val()) {
      $dd.hide();
      focusNextAcctField(this);
    }
  });

  $('#toCustomerSearch').on('keydown', function (e) {
    const $dd = $('#toCustomerDropdown');
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      if (!$dd.is(':visible')) {
        renderCustomerDropdown(
          filterCustomers($(this).val(), $('#customerCodeSubmit').val()),
          $dd,
          'data-to-code'
        );
      }
      moveDropdownActive($dd, e.key === 'ArrowDown' ? 1 : -1, '.dropdown-item[data-to-code]');
      return;
    }
    if (e.key === 'Escape') { $dd.hide(); return; }
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const $selected = $dd.is(':visible') ? $dd.find('.dropdown-item.active[data-to-code]') : $();
    if ($selected.length) selectToCustomer($selected.data('to-code'));
    if ($('#toCustomerCodeSubmit').val()) {
      $dd.hide();
      focusNextAcctField(this);
    }
  });

  $('#accountTranForm').on('keydown', function (e) {
    if (e.key !== 'Enter') return;
    const el = e.target;
    if (!el || el.id === 'btnAcctTxnSave') return;
    if (el.id === 'customerSearch' || el.id === 'bankSearch' || el.id === 'toCustomerSearch') return;
    if (el.tagName !== 'INPUT' && el.tagName !== 'SELECT') return;
    e.preventDefault();
    focusNextAcctField(el);
  });

  $('#accountTranForm').on('submit', function (e) {
    e.preventDefault();
    const customerCode = $('#customerCodeSubmit').val();
    const entryCategory = $('#entryCategory').val() || 'single';
    if (!customerCode) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'warning', title: 'Customer Required', text: 'Please select a customer.' });
      }
      return;
    }
    if (entryCategory === 'bank' && !$('#bankAccountSubmit').val()) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'warning', title: 'Bank Required', text: 'Please select a bank.' });
      }
      return;
    }
    if (entryCategory === 'customer' && !$('#toCustomerCodeSubmit').val()) {
      if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'warning', title: 'Customer Required', text: 'Please select the other customer.' });
      }
      return;
    }

    const payload = {
      _token: $('meta[name="csrf-token"]').attr('content'),
      customerCode: customerCode,
      description: $('#description').val(),
      amount: $('#amount').val(),
      type: $('#type').val(),
      customerDate: $('#customerDate').val(),
      paymentMode: 'None',
      entryCategory: entryCategory,
      bankAccount: $('#bankAccountSubmit').val() || '',
      toCustomerCode: $('#toCustomerCodeSubmit').val() || ''
    };

    $.post({
      url: '{{ route('accountTransaction.store') }}',
      data: payload,
      dataType: 'json'
    })
      .done(function (res) {
        // Clear entry fields; keep Entry Category selected
        $('#description').val('');
        $('#amount').val('');
        clearCustomerSelection();
        if (entryCategory === 'bank') clearBankSelection();
        if (entryCategory === 'customer') clearToCustomerSelection();
        updateProjectedBalances();
        loadAccountTransactions();

        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'success',
            title: 'Saved',
            text: res.message || 'Transaction saved',
            timer: 1600,
            showConfirmButton: false,
            allowOutsideClick: false,
            didClose: function () {
              setTimeout(focusCustomerSearch, 30);
            }
          });
        } else {
          focusCustomerSearch();
        }
      })
      .fail(function (xhr) {
        const msg = xhr.responseJSON?.message
          || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join(' ') : null)
          || 'Failed to save transaction';
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'error', title: 'Save Failed', text: msg });
        }
      });
  });

  // Page load: show server-provided rows immediately (no AJAX needed)
  showLedgerPage(1);
  setTimeout(focusCustomerSearch, 100);
});

const ACCOUNT_TXN_LOCKED_MESSAGE = 'Only entries created on the Account Transaction page can be edited or deleted.';

function isPageManagedCategory(cat) {
  const c = String(cat || '').trim().toLowerCase();
  return c === 'accounttransaction' || c === 'transfer';
}

function showLockedEntryMessage(action) {
  if (typeof Swal !== 'undefined') {
    Swal.fire({
      icon: 'warning',
      title: action === 'delete' ? 'Cannot Delete' : 'Cannot Edit',
      text: ACCOUNT_TXN_LOCKED_MESSAGE,
      confirmButtonText: 'OK'
    });
  } else {
    alert(ACCOUNT_TXN_LOCKED_MESSAGE);
  }
}

function deleteEntry(id) {
  const category = $(`tr[data-id="${id}"]`).data('category') || '';
  if (!isPageManagedCategory(category)) {
    showLockedEntryMessage('delete');
    return;
  }

  const run = function () {
    $.ajax({
      url: `{{ url('/ledger-transaction/delete') }}/${id}?source=account_transaction`,
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json'
      }
    })
      .done(function () {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'success',
            title: 'Deleted',
            timer: 1400,
            showConfirmButton: false,
            didClose: function () {
              setTimeout(function () {
                if (typeof window.focusCustomerSearch === 'function') window.focusCustomerSearch();
              }, 30);
            }
          });
        }
        const code = $('#customerCodeSubmit').val() || '';
        if (typeof window.loadAccountTransactions === 'function') {
          window.loadAccountTransactions(code);
        } else {
          $('#customerCode').trigger('change');
        }
      })
      .fail(function (xhr) {
        const msg = xhr.responseJSON?.message || 'Failed to delete';
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: xhr.status === 403 ? 'warning' : 'error',
            title: xhr.status === 403 ? 'Cannot Delete' : 'Delete Failed',
            text: msg
          });
        }
      });
  };

  if (typeof Swal !== 'undefined') {
    Swal.fire({
      icon: 'warning',
      title: 'Confirm Delete?',
      text: 'This will remove the customer account entry.',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      confirmButtonText: 'Yes, delete'
    }).then(r => { if (r.isConfirmed) run(); });
  } else if (confirm('Delete this entry?')) {
    run();
  }
}

function openEdit(id) {
  const category = $(`tr[data-id="${id}"]`).data('category') || '';
  if (!isPageManagedCategory(category)) {
    showLockedEntryMessage('edit');
    return;
  }

  $('#editEntryForm')[0].reset();
  $('#editTypeWrap').hide();
  $('#editCategory').text('');
  $('#editId').val(id);

  $.get(`{{ url('/customer-ledger/entry') }}/${id}`)
    .done(function (e) {
      if (!isPageManagedCategory(e.category)) {
        showLockedEntryMessage('edit');
        return;
      }
      $('#editCategory').text(e.category || '');
      $('#editDescription').val(e.description || '');
      $('#editDate').val(e.customerDate || '{{ now()->format('Y-m-d') }}');
      $('#editAmount').val(e.credit > 0 ? e.credit : e.debit);
      if (e.category === 'AccountTransaction') {
        $('#editTypeWrap').show();
        $('#editType').val(e.credit > 0 ? 'credit' : 'debit');
      }
      new bootstrap.Modal(document.getElementById('editEntryModal')).show();
    })
    .fail(function () {
      if (typeof Swal !== 'undefined') {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load entry' });
      }
    });
}

$(document).on('submit', '#editEntryForm', function (ev) {
  ev.preventDefault();
  const id = $('#editId').val();
  $.ajax({
    url: `{{ url('/ledger-transaction/update') }}/${id}`,
    method: 'POST',
    data: {
      description: $('#editDescription').val(),
      amount: $('#editAmount').val(),
      customerDate: $('#editDate').val(),
      type: $('#editTypeWrap').is(':visible') ? $('#editType').val() : undefined,
      source: 'account_transaction',
      _method: 'PUT',
      _token: $('meta[name="csrf-token"]').attr('content')
    }
  })
    .done(function () {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'success',
          title: 'Updated',
          timer: 1400,
          showConfirmButton: false,
          didClose: function () {
            setTimeout(function () {
              if (typeof window.focusCustomerSearch === 'function') window.focusCustomerSearch();
            }, 30);
          }
        });
      }
      bootstrap.Modal.getInstance(document.getElementById('editEntryModal'))?.hide();
      const code = $('#customerCodeSubmit').val() || '';
      if (typeof window.loadAccountTransactions === 'function') {
        window.loadAccountTransactions(code);
      } else {
        $('#customerCode').trigger('change');
      }
    })
    .fail(function (xhr) {
      const msg = xhr.responseJSON?.message || 'Failed to update';
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: xhr.status === 403 ? 'warning' : 'error',
          title: xhr.status === 403 ? 'Cannot Edit' : 'Update Failed',
          text: msg
        });
      }
    });
  return false;
});
</script>

@endsection
