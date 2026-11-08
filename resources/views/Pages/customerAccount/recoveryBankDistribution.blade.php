@extends('layout.app')

@section('title', 'Market Recovery')

@section('content')

<style>
  .recovery-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
  }
  #areaDropdown.dropdown-menu,
  #customerDropdown.dropdown-menu {
    max-height: 300px;
    overflow-y: auto;
    width: 100%;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-top: 4px;
    z-index: 10000;
  }
  #areaDropdown .dropdown-item.active,
  #areaDropdown .dropdown-item:hover,
  #customerDropdown .dropdown-item.active,
  #customerDropdown .dropdown-item:hover {
    background: linear-gradient(135deg, #1a4a8a 0%, #0f2747 100%);
    color: #fff;
  }
  #customerDropdown .dropdown-item:hover .text-muted,
  #customerDropdown .dropdown-item.active .text-muted {
    color: rgba(255,255,255,0.9) !important;
  }
  .position-relative {
    position: relative !important;
    overflow: visible !important;
  }
</style>

<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-6">
          <h3 class="mb-0"><i class="bi bi-cash-coin text-primary me-2"></i>Market Recovery</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('accountTransaction.index') }}">Accounts</a></li>
            <li class="breadcrumb-item active" aria-current="page">Market Recovery</li>
          </ol>
        </div>
      </div>
      <hr class="mb-0" style="border-top: 2px solid #0d6efd;">
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
          <div class="card mb-4 shadow-sm">
        <div class="card-header recovery-card-head py-2 d-flex justify-content-between align-items-center">
          <h5 class="mb-0" id="recoveryFormTitle"><i class="bi bi-plus-circle me-2"></i>Record Recovery Payment</h5>
          <button type="button" class="btn btn-sm btn-outline-light" id="btnCancelEdit" style="display:none;">
            Cancel Edit
          </button>
            </div>
            <div class="card-body">
          <form id="accountTranForm" action="javascript:void(0)" method="post" onsubmit="return false;">
                @csrf
                <input type="hidden" name="type" value="credit">
            <input type="hidden" id="editingLedgerId" value="">
                
            <div class="row g-2 align-items-end mb-3">
                  <div class="col-md-2">
                <label for="areaSearch" class="form-label small fw-semibold mb-1">
                      <i class="bi bi-geo-alt text-info me-1"></i>Customer Area
                    </label>
                    <div class="position-relative">
                      <input type="text" 
                             class="form-control form-control-sm" 
                             id="areaSearch" 
                             placeholder="Type to search area..."
                             autocomplete="off">
                      <select class="form-select form-select-sm d-none" id="customerArea" name="customerArea" required>
                        <option value="">Select Area</option>
                        @isset($areas)
                          @foreach($areas as $area)
                            <option value="{{ $area->areaCode }}" data-name="{{ $area->areaName }}">
                              {{ $area->areaName }}
                            </option>
                          @endforeach
                        @endisset
                      </select>
                  <div id="areaDropdown" class="dropdown-menu" style="display: none;"></div>
                    </div>
                  </div>
                  
                  <div class="col-md-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label for="customerSearch" class="form-label small fw-semibold mb-0">
                        <i class="bi bi-person-badge text-primary me-1"></i>Customer
                      </label>
                      <small id="customerBalanceDisplay" class="text-muted" style="display: none;">
                        <strong id="customerBalanceValue" class="text-primary">0.00</strong>
                        <span id="customerAfterRecoveryWrap" class="ms-2" style="display: none;">
                          <i class="bi bi-arrow-right-short text-muted"></i>
                          <strong id="customerAfterRecoveryValue" class="text-info">0.00</strong>
                        </span>
                      </small>
                    </div>
                    <div class="position-relative">
                      <input type="text" 
                             class="form-control form-control-sm" 
                             id="customerSearch" 
                             placeholder="Type to search customer..."
                             autocomplete="off">
                      <select class="form-select form-select-sm d-none" id="customerCode" name="customerCode" required>
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                          <option value="{{ $customer->custCode }}" 
                                  data-name="{{ $customer->custName }}"
                                  data-business="{{ $customer->bussnessName ?? '' }}"
                                  data-address="{{ $customer->custAddress ?? '' }}"
                                  data-area="{{ $customer->custArea }}"
                                  data-city="{{ $customer->custCity }}">
                            {{ $customer->custName }}@if($customer->bussnessName) | {{ $customer->bussnessName }}@endif - {{ $customer->custArea }}
                          </option>
                        @endforeach
                      </select>
                  <div id="customerDropdown" class="dropdown-menu" style="display: none;"></div>
                    </div>
                  </div>

                  <div class="col-md-6">
                <label class="form-label small fw-semibold mb-1">
                      <i class="bi bi-house-door text-danger me-1"></i>Address
                    </label>
                    <div id="customerAddressDisplay" class="form-control form-control-sm bg-light text-muted" style="min-height:31px;">
                      Select a customer to view address
                    </div>
                  </div>
                </div>
                
            <div class="row g-2 align-items-end">
                  <div class="col-md-2">
                <label for="customerDate" class="form-label small fw-semibold mb-1">Date</label>
                <input type="date"
                       class="form-control form-control-sm"
                       name="customerDate"
                       id="customerDate"
                       value="{{ now()->format('Y-m-d') }}"
                       required>
              </div>
              <div class="col-md-3">
                <label for="description" class="form-label small fw-semibold mb-1">Description</label>
                    <input type="text" 
                           class="form-control form-control-sm" 
                           name="description" 
                           id="description" 
                           placeholder="Recovery..."
                           maxlength="255"
                           required>
                  </div>
                  <div class="col-md-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label for="paymentMode" class="form-label small fw-semibold mb-0">Category</label>
                  <a href="javascript:void(0)" id="btnChequeLink" class="small text-decoration-none fw-semibold text-primary">
                    <i class="bi bi-bank2 me-1"></i>Cheque
                  </a>
                </div>
                    <select class="form-select form-select-sm" name="paymentMode" id="paymentMode" required>
                      <option value="">Select Category</option>
                      <option value="Recovery" selected>Recovery</option>
                      <option value="Discount">Discount</option>
                      <option value="Bilty">Bilty</option>
                      <option value="Other">Other</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                <label for="amount" class="form-label small fw-semibold mb-1">Amount (PKR)</label>
                    <input type="number" 
                           step="0.01" 
                           min="0.01" 
                           class="form-control form-control-sm" 
                           name="amount" 
                           id="amount" 
                           placeholder="0.00"
                           required>
                  </div>
                  <div class="col-md-2">
                <button type="button" class="btn btn-primary btn-sm" id="btnRecoverySave">
                  <i class="bi bi-save me-1"></i><span id="btnRecoverySaveLabel">Save Recovery</span>
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>

      <div class="card shadow-sm">
        <div class="card-header recovery-card-head py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
          <h5 class="mb-0"><i class="bi bi-table me-2"></i>Area Entries <span class="fs-6 fw-normal opacity-75" id="areaLedgerTitle"></span></h5>
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <small class="text-white-50" id="areaLedgerCount"></small>
            <button type="button" class="btn btn-sm btn-outline-light fw-semibold" id="btnPrintAreaLedger" disabled title="Print area recovery report">
              <i class="bi bi-printer me-1"></i>Print Report
            </button>
            <button type="button" class="btn btn-sm btn-light fw-semibold" id="btnApproveRecoveryEntry" disabled title="Approve selected recovery entries">
              <i class="bi bi-check2-square me-1"></i>Approve Recovery Entry
            </button>
          </div>
        </div>
        <div class="card-body p-0">
                <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0" id="customerLedgerTable">
              <thead class="table-primary">
                <tr>
                  <th style="width:3%;" class="text-center">#</th>
                  <th style="width:6%;" class="text-center">Page No</th>
                  <th style="width:8%">Date</th>
                  <th>Description</th>
                  <th style="width:10%;" class="text-end">Opening Balance</th>
                  <th style="width:8%;" class="text-end">Recovery</th>
                  <th style="width:8%;" class="text-end">Cheque</th>
                  <th style="width:7%;" class="text-end">Discount</th>
                  <th style="width:7%;" class="text-end">Bilty</th>
                  <th style="width:7%;" class="text-end">Other</th>
                  <th style="width:10%;" class="text-end">Last Balance</th>
                  <th style="width:6%;" class="text-center">
                    <div class="form-check d-inline-flex justify-content-center m-0">
                      <input class="form-check-input" type="checkbox" id="approveAllCheck" title="Select all">
                    </div>
                    <div class="small">Select</div>
                  </th>
                  <th style="width:8%;" class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr><td colspan="13" class="text-center text-muted py-4">Select an area to view entries</td></tr>
              </tbody>
              <tfoot class="table-secondary fw-semibold d-none" id="customerLedgerTfoot">
                <tr>
                  <td colspan="4" class="text-end">TOTAL</td>
                  <td class="text-end" id="totOpening">0.00</td>
                  <td class="text-end text-success" id="totRecovery">0.00</td>
                  <td class="text-end text-info" id="totCheque">0.00</td>
                  <td class="text-end text-primary" id="totDiscount">0.00</td>
                  <td class="text-end text-warning" id="totBilty">0.00</td>
                  <td class="text-end text-secondary" id="totOther">0.00</td>
                  <td class="text-end" id="totLast">0.00</td>
                  <td colspan="2"></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
  function fmt(n){ return (Number(n||0)).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}); }

  let selectedCustomerBalance = 0;

  function updateAfterRecoveryBalance() {
    const code = $('#customerCode').val();
    if (!code || !$('#customerBalanceDisplay').is(':visible')) {
      $('#customerAfterRecoveryWrap').hide();
      return;
    }
    const amount = parseFloat($('#amount').val()) || 0;
    if (amount > 0) {
      const after = selectedCustomerBalance - amount;
      $('#customerAfterRecoveryValue')
        .text(fmt(after))
        .removeClass('text-info text-danger text-success')
        .addClass(after < 0 ? 'text-danger' : 'text-info');
      $('#customerAfterRecoveryWrap').show();
    } else {
      $('#customerAfterRecoveryWrap').hide();
    }
  }

  function loadCustomerBalance(custCode) {
    if (!custCode) {
      selectedCustomerBalance = 0;
      $('#customerBalanceDisplay, #customerAfterRecoveryWrap').hide();
      return;
    }
    $.get(`{{ url('/customer/balance') }}/${encodeURIComponent(custCode)}`)
      .done(function (res) {
        selectedCustomerBalance = parseFloat(res.balance) || 0;
        $('#customerBalanceValue')
          .text(fmt(selectedCustomerBalance))
          .removeClass('text-danger text-success text-primary')
          .addClass(res.balance_type === 'payable' ? 'text-danger' : 'text-primary');
        $('#customerBalanceDisplay').show();
        updateAfterRecoveryBalance();
      })
      .fail(function () {
        selectedCustomerBalance = 0;
        $('#customerBalanceDisplay, #customerAfterRecoveryWrap').hide();
      });
  }

  $('#amount').on('input change', updateAfterRecoveryBalance);

  // Delete ledger entry and refresh area ledger
  function deleteLedger(id) {
    deleteLedgerGroup([id]);
  }

  function deleteLedgerGroup(ids) {
    const list = (ids || []).map(function (v) { return parseInt(v, 10); }).filter(Boolean);
    if (!list.length) return;

    Swal.fire({
      title: 'Confirm Delete?',
      text: list.length > 1
        ? (list.length + ' linked entries (same customer) delete hongi.')
        : 'This ledger transaction will be deleted.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (!result.isConfirmed) return;

      $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
      const reqs = list.map(function (id) {
        return $.ajax({
          url: `{{ url('/ledger-transaction/delete') }}/${id}`,
          method: 'DELETE'
        });
      });

      $.when.apply($, reqs)
        .done(function () {
          Swal.fire('Deleted', list.length > 1 ? 'Linked entries removed.' : 'Entry has been removed successfully.', 'success');
          loadAreaLedger($('#customerArea').val());
        })
        .fail(function (xhr) {
          Swal.fire('Delete Failed', (xhr && xhr.responseJSON && xhr.responseJSON.message) || 'Delete failed', 'error');
          loadAreaLedger($('#customerArea').val());
        });
    });
  }

  function setEditMode(on, isCheque) {
    const editing = !!on;
    $('#editingLedgerId').val(editing ? ($('#editingLedgerId').val() || '') : '');
    if (!editing) {
      $('#editingLedgerId').val('');
    }
    $('#btnCancelEdit').toggle(editing);
    $('#recoveryFormTitle').html(
      editing
        ? '<i class="bi bi-pencil-square me-2"></i>Edit Recovery Payment'
        : '<i class="bi bi-plus-circle me-2"></i>Record Recovery Payment'
    );
    $('#btnRecoverySaveLabel').text(editing ? 'Update Recovery' : 'Save Recovery');
    if (editing && isCheque) {
      setChequeEntryMode(true);
    } else if (!editing) {
      setChequeEntryMode(false);
    }
  }

  function fillAreaAndCustomer(areaCode, customerCode) {
    if (areaCode) {
      const $areaOpt = $('#customerArea option[value="' + areaCode + '"]');
      $('#customerArea').val(areaCode);
      $('#areaSearch').val(($areaOpt.data('name') || $areaOpt.text() || '').toString().trim());
      if (typeof loadAreaLedger === 'function') {
        loadAreaLedger(areaCode);
      }
    }
    if (customerCode) {
      const $custOpt = $('#customerCode option[value="' + customerCode + '"]');
      $('#customerCode').val(customerCode);
      const name = ($custOpt.data('business') || $custOpt.data('name') || $custOpt.text() || '').toString().trim();
      $('#customerSearch').val(name);
      const parts = [
        $custOpt.data('address'),
        $custOpt.data('area'),
        $custOpt.data('city')
      ].map(function (v) { return (v || '').toString().trim(); }).filter(Boolean);
      const $box = $('#customerAddressDisplay');
      if (parts.length) {
        $box.removeClass('text-muted').addClass('text-dark').text(parts.join(', '));
      } else {
        $box.removeClass('text-dark').addClass('text-muted').text('No address on file');
      }
      loadCustomerBalance(customerCode);
    }
  }

  function clearEditFormFields() {
    $('#description').val('');
    $('#amount').val('');
    $('#paymentMode').val('Recovery');
    clearChequeDetails();
    setChequeEntryMode(false);
    setEditMode(false);
  }

  function applyChequeDetailsToForm(chq) {
    if (!chq) return false;
    const bank = (chq.bank_name || '').toString().trim();
    const no = (chq.cheque_no || '').toString().trim();
    const cDate = (chq.cheque_date || '').toString().substring(0, 10);
    const dDate = (chq.due_date || '').toString().substring(0, 10);
    if (!bank && !no && !cDate && !dDate) return false;
    $('#chequeBankName').val(bank);
    $('#chequeNo').val(no);
    if (cDate) $('#chequeDate').val(cDate);
    if (dDate) $('#chequeDueDate').val(dDate);
    return true;
  }

  function loadEntryForEdit(id) {
    $.get(`{{ url('/customer-ledger/entry') }}/${id}`)
      .done(function (res) {
        const amount = (parseFloat(res.credit) || 0) > 0
          ? parseFloat(res.credit)
          : (parseFloat(res.debit) || 0);
        const hasChequePayload = !!(res.cheque && (res.cheque.bank_name || res.cheque.cheque_no));
        const isCheque = String(res.category || '') === 'Cheque' || hasChequePayload;

        $('#editingLedgerId').val(res.id);
        fillAreaAndCustomer(res.customerArea, res.customerCode);
        $('#customerDate').val((res.customerDate || '').toString().substring(0, 10));
        $('#amount').val(amount > 0 ? amount : '');

        if (isCheque) {
          let baseDesc = res.description || '';
          baseDesc = baseDesc.replace(/\s*\(Chq\s+.+?\)$/i, '').trim();
          if (/^Cheque\s+/i.test(baseDesc) && hasChequePayload) {
            baseDesc = '';
          }
          $('#description').val(baseDesc);
          $('#paymentMode').val('Recovery');
          // Fill cheque fields BEFORE mode UI so Cheque link / modal already have values
          clearChequeDetails();
          applyChequeDetailsToForm(res.cheque);
          setEditMode(true, true);
          setChequeEntryMode(true);
          const formCard = document.querySelector('.card.mb-4');
          if (formCard) formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
          // Open Cheque Detail so saved bank / chq no / dates are visible on edit
          setTimeout(function () {
            openChequeModal(false);
          }, 120);
      } else {
          $('#description').val(res.description || '');
          const cat = ['Recovery', 'Discount', 'Bilty', 'Other'].includes(res.category)
            ? res.category
            : 'Recovery';
          $('#paymentMode').val(cat);
          clearChequeDetails();
          setEditMode(true, false);
          setTimeout(function () { $('#description').focus(); }, 80);
          const formCard = document.querySelector('.card.mb-4');
          if (formCard) formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      })
      .fail(function (xhr) {
      Swal.fire({
        icon: 'error',
          title: 'Load Failed',
          text: xhr.responseJSON?.message || 'Could not load entry for edit',
        confirmButtonColor: '#d33'
      });
    });
  }

  $('#btnCancelEdit').on('click', function () {
    clearEditFormFields();
    resetCustomerForNext();
    $('#customerSearch').focus();
  });

  // Delegated click handlers (safer when rows are rendered dynamically)
  $(document).on('click', '.btn-edit-ledger', function(){
    const id = $(this).data('id');
    if (id) loadEntryForEdit(id);
    return false;
  });

  $(document).on('click', '.btn-delete-ledger', function(){
    const idsCsv = String($(this).attr('data-ids') || $(this).data('id') || '');
    const ids = idsCsv.split(',').map(function (v) { return parseInt(v, 10); }).filter(Boolean);
    if (ids.length > 1) {
      deleteLedgerGroup(ids);
    } else if (ids[0]) {
      deleteLedger(ids[0]);
    }
    return false;
  });

  // Area Search with Dropdown
  $(document).ready(function() {
    const $areaSearchInput = $('#areaSearch');
    const $areaSelect = $('#customerArea');
    const $areaDropdown = $('#areaDropdown');
    let allAreas = [];
    
    // Build areas array from select options
    $areaSelect.find('option').each(function() {
      if($(this).val()) {
        allAreas.push({
          code: $(this).val(),
          name: $(this).data('name')
        });
      }
    });
    
    // Filter and display areas
    function filterAreas(searchTerm) {
      const term = searchTerm.toLowerCase().trim();
      
      let filtered = allAreas;
      
      if(term) {
        filtered = filtered.filter(a => 
          a.name.toLowerCase().includes(term) ||
          a.code.toLowerCase().includes(term)
        );
      }
      
      if(!term) {
        $areaDropdown.hide().empty();
        return;
      }
      
      if(filtered.length === 0) {
        $areaDropdown.html('<div class="dropdown-item text-muted">No areas found</div>').show();
        return;
      }
      
      let html = '';
      filtered.forEach((a, idx) => {
        html += `<a class="dropdown-item ${idx===0?'active':''}" href="javascript:void(0);" data-code="${a.code}">
          <strong>${a.name}</strong>
        </a>`;
      });
      
      $areaDropdown.html(html).show();
    }
    
    // Area search input events
    $areaSearchInput.on('input', function() {
      filterAreas($(this).val());
    });
    
    $areaSearchInput.on('focus', function() {
      if($(this).val()) {
        filterAreas($(this).val());
      }
    });
    
    // Area dropdown item selection
    $areaDropdown.on('click', '.dropdown-item', function(e) {
      e.preventDefault();
      const code = $(this).data('code');
      const area = allAreas.find(a => a.code === code);
      
      $areaSelect.val(code);
      $areaSearchInput.val(area.name);
      $areaDropdown.hide();
      
      // Reset customer when area changes
      $('#customerSearch').val('');
      $('#customerCode').val('');
      $('#customerDropdown').hide();
      $('#customerAddressDisplay').removeClass('text-dark').addClass('text-muted').text('Select a customer to view address');
      // Trigger area ledger load
      $areaSelect.trigger('change');
      setTimeout(function () { $('#customerSearch').focus(); }, 50);
    });
    
    // Keyboard navigation for area dropdown
    $areaSearchInput.on('keydown', function(e) {
      const $items = $areaDropdown.find('.dropdown-item');
      const $active = $items.filter('.active');
      let $next;
      
      if(e.key === 'ArrowDown') {
        e.preventDefault();
        if (!$areaDropdown.is(':visible')) filterAreas($(this).val());
        $next = $active.nextAll('.dropdown-item').eq(0);
        if($next.length) {
          $items.removeClass('active');
          $next.addClass('active');
        }
      } else if(e.key === 'ArrowUp') {
        e.preventDefault();
        $next = $active.prevAll('.dropdown-item').eq(0);
        if($next.length) {
          $items.removeClass('active');
          $next.addClass('active');
        }
      } else if(e.key === 'Escape') {
        $areaDropdown.hide();
      } else if(e.key === 'Enter') {
        e.preventDefault();
        if($active.length && $areaDropdown.is(':visible')) {
          $active.click();
        } else if ($areaSelect.val()) {
          $areaDropdown.hide();
          $('#customerSearch').focus();
        }
      }
    });
  });

  // Customer Search with Dropdown - Like Sale Invoice
  $(document).ready(function() {
    const $searchInput = $('#customerSearch');
    const $customerSelect = $('#customerCode');
    const $dropdown = $('#customerDropdown');
    const $areaSelect = $('#customerArea');
    let allCustomers = [];
    
    // Build customer array from select options
    $customerSelect.find('option').each(function() {
      if($(this).val()) {
        allCustomers.push({
          code: $(this).val(),
          name: $(this).data('name'),
          business: $(this).data('business') || '',
          address: $(this).data('address') || '',
          area: $(this).data('area'),
          city: $(this).data('city'),
          display: $(this).text()
        });
      }
    });

    function showCustomerAddress(customer) {
      const $box = $('#customerAddressDisplay');
      if (!customer) {
        $box.removeClass('text-dark').addClass('text-muted').text('Select a customer to view address');
        return;
      }
      const parts = [customer.address, customer.area, customer.city]
        .map(v => (v || '').toString().trim())
        .filter(Boolean);
      if (!parts.length) {
        $box.removeClass('text-dark').addClass('text-muted').text('No address on file');
        return;
      }
      $box.removeClass('text-muted').addClass('text-dark').text(parts.join(', '));
    }
    
    // Filter and display customers
    function filterCustomers(searchTerm) {
      const selectedArea = $areaSelect.val();
      const term = searchTerm.toLowerCase().trim();
      
      let filtered = allCustomers;
      
      // Filter by selected area
      if(selectedArea) {
        filtered = filtered.filter(c => c.area === selectedArea);
      }
      
      // Filter by search term
      if(term) {
        filtered = filtered.filter(c => 
          c.name.toLowerCase().includes(term) ||
          c.business.toLowerCase().includes(term) ||
          c.area.toLowerCase().includes(term) ||
          c.code.toLowerCase().includes(term)
        );
      }
      
      if(!term && !selectedArea) {
        $dropdown.hide().empty();
        return;
      }
      
      if(filtered.length === 0) {
        $dropdown.html('<div class="dropdown-item text-muted">No customers found</div>').show();
        return;
      }
      
      let html = '';
      filtered.slice(0, 15).forEach((c, idx) => {
        html += `<a class="dropdown-item ${idx===0?'active':''}" href="javascript:void(0);" data-code="${c.code}">
          <strong>${c.business || c.name}</strong>
          <div class="text-muted">Area: ${c.area} | City: ${c.city} | Code: ${c.code}</div>
        </a>`;
      });
      
      $dropdown.html(html).show();
    }
    
    // Area change - reset customer and filter
    $areaSelect.on('change', function() {
      $searchInput.val('');
      $customerSelect.val('');
      loadCustomerBalance('');
      showCustomerAddress(null);
      filterCustomers('');
      loadAreaLedger($(this).val());
    });
    
    // Search input events
    $searchInput.on('input', function() {
      filterCustomers($(this).val());
    });
    
    $searchInput.on('focus', function() {
      if($(this).val() || $areaSelect.val()) {
        filterCustomers($(this).val());
      }
    });
    
    // Dropdown item selection
    $dropdown.on('click', '.dropdown-item', function(e) {
      e.preventDefault();
      const code = $(this).data('code');
      const customer = allCustomers.find(c => c.code === code);
      
      $customerSelect.val(code).trigger('change');
      $searchInput.val(customer.business || customer.name);
      showCustomerAddress(customer);
      $dropdown.hide();
      setTimeout(function () { $('#customerDate').focus(); }, 50);
    });

    $customerSelect.on('change', function () {
      const code = $(this).val();
      if (code) {
        loadCustomerBalance(code);
        showCustomerAddress(allCustomers.find(c => c.code === code));
      } else {
        loadCustomerBalance('');
        showCustomerAddress(null);
      }
    });
    
    // Keyboard navigation
    $searchInput.on('keydown', function(e) {
      const $items = $dropdown.find('.dropdown-item');
      const $active = $items.filter('.active');
      let $next;
      
      if(e.key === 'ArrowDown') {
        e.preventDefault();
        if (!$dropdown.is(':visible')) filterCustomers($(this).val());
        $next = $active.nextAll('.dropdown-item').eq(0);
        if($next.length) {
          $items.removeClass('active');
          $next.addClass('active');
        }
      } else if(e.key === 'ArrowUp') {
        e.preventDefault();
        $next = $active.prevAll('.dropdown-item').eq(0);
        if($next.length) {
          $items.removeClass('active');
          $next.addClass('active');
        }
      } else if(e.key === 'Escape') {
        $dropdown.hide();
      } else if(e.key === 'Enter') {
        e.preventDefault();
        if($active.length && $dropdown.is(':visible')) {
          $active.click();
        } else if ($customerSelect.val()) {
          $dropdown.hide();
          $('#customerDate').focus();
        }
      }
    });
    
    // Click outside to close dropdowns
    $(document).on('click', function(e) {
      if(!$(e.target).closest('#areaSearch, #areaDropdown, #customerSearch, #customerDropdown').length) {
        $('#areaDropdown').hide();
        $dropdown.hide();
      }
    });
  });

  function focusNextRecoveryField(current) {
    const fields = Array.from(document.querySelectorAll(
      '#accountTranForm input:not([type="hidden"]), #accountTranForm select, #btnRecoverySave'
    )).filter(function (el) {
      if (el.disabled || el.readOnly) return false;
      if (el.classList.contains('d-none') || el.closest('.d-none, .modal')) return false;
      return el.offsetParent !== null || el === document.activeElement;
    });
    const index = fields.indexOf(current);
    const next = index >= 0 ? fields[index + 1] : null;
    const target = next || document.getElementById('btnRecoverySave');
    if (!target) return;
    target.focus();
    if (target.tagName === 'INPUT' && typeof target.select === 'function') target.select();
  }

  let ignoreRecoveryEnterUntil = 0;

  function resetCustomerForNext() {
    $('#customerSearch').val('');
    $('#customerCode').val('');
    $('#customerDropdown').hide();
    $('#customerAddressDisplay').removeClass('text-dark').addClass('text-muted').text('Select a customer to view address');
    selectedCustomerBalance = 0;
    $('#customerBalanceDisplay, #customerAfterRecoveryWrap').hide();
  }

  function focusCustomerAfterOk() {
    ignoreRecoveryEnterUntil = Date.now() + 400;
    const apply = function () {
      const el = document.getElementById('customerSearch');
      if (!el) return;
      el.focus({ preventScroll: true });
      if (typeof el.select === 'function') el.select();
    };
    apply();
    setTimeout(apply, 0);
    setTimeout(apply, 50);
    setTimeout(apply, 150);
  }

  let chequeEntryMode = false;

  function setChequeEntryMode(on) {
    chequeEntryMode = !!on;
    const $link = $('#btnChequeLink');
    if (chequeEntryMode) {
      $link.addClass('text-success').removeClass('text-primary');
    } else {
      $link.removeClass('text-success').addClass('text-primary');
    }
  }

  function refreshAfterSave() {
    $('#description').val('');
    $('#amount').val('');
    clearChequeDetails();
    setChequeEntryMode(false);
    $('#paymentMode').val('Recovery');
    setEditMode(false);
    resetCustomerForNext();
    loadAreaLedger($('#customerArea').val());
  }

  function showSaveSuccess(message) {
    refreshAfterSave();
    if (document.activeElement && typeof document.activeElement.blur === 'function') {
      document.activeElement.blur();
    }
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: message,
      confirmButtonColor: '#28a745',
      confirmButtonText: 'OK',
      allowOutsideClick: false,
      allowEnterKey: true,
      returnFocus: false,
      didClose: function () {
        focusCustomerAfterOk();
      }
    });
  }

    function clearFieldError($el){
      $el.removeClass('is-invalid');
      $el.next('.invalid-feedback').remove();
    }

    function showFieldError($el, msg){
      if(!$el || !$el.length) return;
      clearFieldError($el);
      $el.addClass('is-invalid');
      $el.after(`<div class="invalid-feedback">${msg}</div>`);
      $el.focus();
    }

  function saveRecoveryEntry() {
    if (Date.now() < ignoreRecoveryEnterUntil) return;
    if (typeof Swal !== 'undefined' && Swal.isVisible && Swal.isVisible()) return;

    const form = document.getElementById('accountTranForm');
    const code = $('#customerCode').val();
    const description = $('#description').val().trim();
    const amount = $('#amount').val();
    const pm = $('#paymentMode').val();
    const area = $('#customerArea').val();

    [$('#customerArea'), $('#customerCode'), $('#description'), $('#amount'), $('#paymentMode')].forEach(el => clearFieldError(el));

    if (!area){ 
      Swal.fire({
        icon: 'warning',
        title: 'Validation Error',
        text: 'Please select a customer area',
        confirmButtonColor: '#ffc107'
      });
      showFieldError($('#customerArea'), 'Required'); 
      return; 
    }
    if (!code){ 
      Swal.fire({
        icon: 'warning',
        title: 'Validation Error',
        text: 'Please select a customer',
        confirmButtonColor: '#ffc107'
      });
      showFieldError($('#customerCode'), 'Required'); 
      return; 
    }
    if (!amount || amount <= 0){
      Swal.fire({
        icon: 'warning',
        title: 'Validation Error',
        text: 'Please enter a valid amount greater than 0',
        confirmButtonColor: '#ffc107'
      });
      showFieldError($('#amount'), 'Invalid amount');
      return; 
    }

    const editingId = $('#editingLedgerId').val();

    // Cheque link mode → save/update cheque + ledger
    if (chequeEntryMode) {
      const bankName = $('#chequeBankName').val().trim();
      const chequeNo = $('#chequeNo').val().trim();
      const chequeDate = $('#chequeDate').val();
      const dueDate = $('#chequeDueDate').val();

      if (!bankName || !chequeNo || !chequeDate || !dueDate) {
        Swal.fire({
          icon: 'warning',
          title: 'Cheque Details Required',
          text: 'Please fill cheque details (Cheque link → modal).',
          confirmButtonColor: '#ffc107'
        }).then(function () {
          openChequeModal(false);
        });
        return;
      }

      const $btn = $('#btnRecoverySave').prop('disabled', true);
      $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
      });

      if (editingId) {
        $.ajax({
          url: `{{ url('/ledger-transaction/update') }}/${editingId}`,
          method: 'PUT',
          data: {
            description: description,
            amount: amount,
            type: 'credit',
            customerDate: $('#customerDate').val() || '',
            paymentMode: 'Cheque',
            bank_name: bankName,
            cheque_no: chequeNo,
            cheque_date: chequeDate,
            due_date: dueDate
          }
        })
          .done(function (res) {
            showSaveSuccess(res.message || 'Cheque entry updated successfully');
          })
          .fail(function (xhr) {
            const msg = xhr.responseJSON?.message
              || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join(' ') : null)
              || 'Failed to update cheque';
            Swal.fire({ icon: 'error', title: 'Update Failed', text: msg, confirmButtonColor: '#d33' });
          })
          .always(function () { $btn.prop('disabled', false); });
        return;
      }

      $.post({
        url: @json(route('recovery.cheque.store')),
        data: {
          customerCode: code,
          customerArea: area || '',
          bank_name: bankName,
          cheque_no: chequeNo,
          amount: amount,
          cheque_date: chequeDate,
          due_date: dueDate,
          customerDate: $('#customerDate').val() || '',
          description: description
        },
        dataType: 'json'
      })
        .done(function (res) {
          showSaveSuccess(res.message || 'Cheque and ledger entry saved successfully');
        })
        .fail(function (xhr) {
          const msg = xhr.responseJSON?.message
            || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join(' ') : null)
            || 'Failed to save cheque';
          Swal.fire({
            icon: 'error',
            title: 'Save Failed',
            text: msg,
            confirmButtonColor: '#d33'
          });
        })
        .always(function () {
          $btn.prop('disabled', false);
        });
      return;
    }

    if (!description){
      Swal.fire({
        icon: 'warning',
        title: 'Validation Error',
        text: 'Please enter a description',
        confirmButtonColor: '#ffc107'
      });
      showFieldError($('#description'), 'Required');
      return; 
    }
    if (!pm){ 
      Swal.fire({
        icon: 'warning',
        title: 'Validation Error',
        text: 'Please select a category',
        confirmButtonColor: '#ffc107'
      });
      showFieldError($('#paymentMode'), 'Required'); 
      return; 
    }
    
    $.ajaxSetup({
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    const $btn = $('#btnRecoverySave').prop('disabled', true);

    if (editingId) {
      $.ajax({
        url: `{{ url('/ledger-transaction/update') }}/${editingId}`,
        method: 'PUT',
        data: {
          description: description,
          amount: amount,
          type: 'credit',
          customerDate: $('#customerDate').val() || '',
          paymentMode: pm
        }
      })
        .done(function (res) {
          showSaveSuccess(res.message || 'Entry updated successfully');
        })
        .fail(function (xhr) {
          Swal.fire({
            icon: 'error',
            title: 'Update Failed',
            text: xhr.responseJSON?.message || 'Failed to update entry',
            confirmButtonColor: '#d33'
          });
        })
        .always(function () { $btn.prop('disabled', false); });
      return;
    }

    const formData = new FormData(form);
    formData.set('type', 'credit');

    $.ajax({
      url: `{{ route('accountTransaction.store') }}`,
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false
    })
    .done(() => {
      showSaveSuccess('Transaction saved successfully');
    })
    .fail(xhr => {
      Swal.fire({
        icon: 'error',
        title: 'Save Failed',
        text: xhr.responseJSON?.message || 'Failed to save transaction',
        confirmButtonColor: '#d33'
      });
    })
    .always(function () {
      $btn.prop('disabled', false);
    });
  }

  $('#accountTranForm').on('keydown', function (e) {
    if (e.key !== 'Enter') return;
    if (Date.now() < ignoreRecoveryEnterUntil) {
      e.preventDefault();
      e.stopPropagation();
      return;
    }
    const el = e.target;
    if (!el || el.id === 'btnRecoverySave') return;
    if (el.id === 'areaSearch' || el.id === 'customerSearch') return;
    if (el.tagName !== 'INPUT' && el.tagName !== 'SELECT') return;
    e.preventDefault();
    focusNextRecoveryField(el);
  });

  // Never allow native form submit / page reload
  $('#accountTranForm').on('submit', function(ev){
    ev.preventDefault();
    ev.stopPropagation();
    return false;
  });

  $('#btnRecoverySave').on('click', function (e) {
    e.preventDefault();
    saveRecoveryEntry();
  });

  $('#btnRecoverySave').on('keydown', function (e) {
    if (e.key !== 'Enter' && e.key !== ' ') return;
    e.preventDefault();
    saveRecoveryEntry();
  });

  function clearChequeDetails() {
    $('#chequeBankName').val('');
    $('#chequeNo').val('');
    $('#chequeDate').val('');
    $('#chequeDueDate').val('');
  }

  function openChequeModal(resetDates) {
    const today = $('#customerDate').val() || '{{ now()->format('Y-m-d') }}';
    if (resetDates || !$('#chequeDate').val()) {
      $('#chequeDate').val(today);
    }
    if (resetDates || !$('#chequeDueDate').val()) {
      $('#chequeDueDate').val(today);
    }
    const modalEl = document.getElementById('chequeDetailModal');
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  }

  $('#btnChequeLink').on('click', function (e) {
    e.preventDefault();
    e.stopPropagation();
    setChequeEntryMode(true);
    // Keep existing details when editing; set dates for new entry
    const hasDetails = !!($('#chequeBankName').val() || $('#chequeNo').val());
    openChequeModal(!hasDetails);
  });

  $('#paymentMode').on('change', function () {
    // While editing a cheque, keep cheque mode
    if ($('#editingLedgerId').val() && chequeEntryMode) return;
    setChequeEntryMode(false);
  });

  function focusNextChequeField(current) {
    const fields = Array.from(document.querySelectorAll(
      '#chequeDetailForm input, #btnChequeDone'
    )).filter(function (el) {
      if (el.disabled) return false;
      return el.offsetParent !== null || el === document.activeElement;
    });
    const index = fields.indexOf(current);
    const next = index >= 0 ? fields[index + 1] : null;
    const target = next || document.getElementById('btnChequeDone');
    if (!target) return;
    target.focus();
    if (target.tagName === 'INPUT' && typeof target.select === 'function') target.select();
  }

  $(document).on('keydown', '#chequeDetailForm', function (e) {
    if (e.key !== 'Enter') return;
    const el = e.target;
    if (!el || el.id === 'btnChequeDone') return;
    if (el.tagName !== 'INPUT' && el.tagName !== 'SELECT') return;
    e.preventDefault();
    focusNextChequeField(el);
  });

  $(document).on('submit', '#chequeDetailForm', function (e) {
    e.preventDefault();
    e.stopPropagation();
    closeChequeModal();
  });

  $(document).on('click', '#btnChequeDone', function (e) {
    e.preventDefault();
    e.stopPropagation();
    closeChequeModal();
  });

  function closeChequeModal() {
    const bankName = $('#chequeBankName').val().trim();
    const chequeNo = $('#chequeNo').val().trim();
    const chequeDate = $('#chequeDate').val();
    const dueDate = $('#chequeDueDate').val();
    if (!bankName || !chequeNo || !chequeDate || !dueDate) {
      Swal.fire({ icon: 'warning', title: 'Required', text: 'Please fill all cheque details.' });
      return;
    }
    const $modal = $('#chequeDetailModal');
    $modal.data('focusAmount', true);
    bootstrap.Modal.getInstance($modal[0])?.hide();
  }

  $(document).on('shown.bs.modal', '#chequeDetailModal', function () {
    const bank = document.getElementById('chequeBankName');
    if (!bank) return;
    bank.focus();
    if (typeof bank.select === 'function') bank.select();
  });

  $(document).on('hidden.bs.modal', '#chequeDetailModal', function () {
    const $modal = $('#chequeDetailModal');
    if (! $modal.data('focusAmount')) return;
    $modal.data('focusAmount', false);
    const amount = document.getElementById('amount');
    if (!amount) return;
    amount.focus();
    if (typeof amount.select === 'function') amount.select();
  });

  let currentAreaCode = '';

  function approveLedgerIds(ids, approved) {
    if (!ids.length) return $.Deferred().resolve().promise();
    return $.ajax({
      url: @json(route('recovery.ledger.approve')),
      method: 'POST',
      dataType: 'json',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      data: { ids: ids, approved: approved ? 1 : 0 }
    });
  }

  function loadAreaLedger(areaCode) {
    const code = (areaCode || '').toString().trim();
    currentAreaCode = code;
    const $tb = $('#customerLedgerTable tbody');
    if (!code) {
      $tb.html('<tr><td colspan="13" class="text-center text-muted py-4">Select an area to view entries</td></tr>');
      $('#areaLedgerTitle').text('');
      $('#areaLedgerCount').text('');
      $('#approveAllCheck').prop({ checked: false, indeterminate: false });
      $('#btnApproveRecoveryEntry').prop('disabled', true);
      resetAreaLedgerTotals();
      return;
    }
    $tb.html('<tr><td colspan="13" class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-2"></span>Loading area entries...</td></tr>');
    $('#areaLedgerTitle').text('— ' + code);
    $('#areaLedgerCount').text('');
    resetAreaLedgerTotals();
    return $.ajax({
      url: `{{ url('/recovery/area-ledger') }}/${encodeURIComponent(code)}`,
      method: 'GET',
      dataType: 'json',
      cache: false,
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    }).done(function (res) {
      displayAreaLedger((res && res.entries) ? res.entries : []);
    }).fail(function (xhr) {
      console.warn('area-ledger failed', xhr.status, xhr.responseText);
      $tb.html('<tr><td colspan="13" class="text-center text-danger py-4">Failed to load area entries</td></tr>');
    });
  }

  window.loadAreaLedger = loadAreaLedger;

  function resetAreaLedgerTotals() {
    $('#totOpening, #totRecovery, #totCheque, #totDiscount, #totBilty, #totOther, #totLast').text('0.00');
    $('#customerLedgerTfoot').addClass('d-none');
    $('#btnPrintAreaLedger').prop('disabled', true);
  }

  function setAreaLedgerTotals(totals) {
    $('#totOpening').text(fmt(totals.opening));
    $('#totRecovery').text(fmt(totals.recovery));
    $('#totCheque').text(fmt(totals.cheque));
    $('#totDiscount').text(fmt(totals.discount));
    $('#totBilty').text(fmt(totals.bilty));
    $('#totOther').text(fmt(totals.other));
    $('#totLast').text(fmt(totals.last));
    $('#customerLedgerTfoot').removeClass('d-none');
    $('#btnPrintAreaLedger').prop('disabled', false);
  }

  // Display area ledger entries — same customer+date already grouped (Recovery/Discount/Bilty/Other cols)
  function displayAreaLedger(entries) {
    const $tb = $('#customerLedgerTable tbody');
    $tb.empty();

    const areaEntries = (entries || []).map(function (e) {
      const ids = Array.isArray(e.ids) && e.ids.length
        ? e.ids.map(function (id) { return parseInt(id, 10); }).filter(Boolean)
        : (e.id ? [parseInt(e.id, 10)] : []);
      return Object.assign({}, e, {
        entryDate: (e.entryDate || e.date || e.customerDate || '').toString().substring(0, 10),
        ids: ids
      });
    });

    $('#areaLedgerCount').text(areaEntries.length ? (areaEntries.length + ' entr' + (areaEntries.length === 1 ? 'y' : 'ies')) : '');

    if (areaEntries.length === 0) {
      $tb.html('<tr><td colspan="13" class="text-center text-muted py-4">No pending Recovery / Cheque entries for this area</td></tr>');
      $('#approveAllCheck').prop({ checked: false, indeterminate: false });
      $('#btnApproveRecoveryEntry').prop('disabled', true);
      resetAreaLedgerTotals();
      return;
    }

    areaEntries.sort(function (a, b) {
      const d = String(a.entryDate).localeCompare(String(b.entryDate));
      return d !== 0 ? d : String(a.code || '').localeCompare(String(b.code || ''));
    });

    const totals = { opening: 0, recovery: 0, cheque: 0, discount: 0, bilty: 0, other: 0, last: 0 };

    areaEntries.forEach(function (row, idx) {
      const date = row.entryDate || '';
      const desc = ((row.code ? (row.code + ' — ') : '') + (row.description || ''))
        .replace(/</g, '&lt;').replace(/>/g, '&gt;');
      const opening = parseFloat(row.opening_balance);
      const lastBal = parseFloat(row.last_balance);

      // Prefer pre-grouped column amounts from API; fall back to single-category credit.
      let recoveryAmount = parseFloat(row.recovery);
      let chequeAmount = parseFloat(row.cheque);
      let discountAmount = parseFloat(row.discount);
      let biltyAmount = parseFloat(row.bilty);
      let otherAmount = parseFloat(row.other);
      if (![recoveryAmount, chequeAmount, discountAmount, biltyAmount, otherAmount].some(Number.isFinite)) {
        const category = String(row.category || '');
        const amount = parseFloat(row.credit) || 0;
        recoveryAmount = chequeAmount = discountAmount = biltyAmount = otherAmount = 0;
        if (category === 'Cheque') chequeAmount = amount;
        else if (category === 'Recovery') recoveryAmount = amount;
        else if (category === 'Discount') discountAmount = amount;
        else if (category === 'Bilty') biltyAmount = amount;
        else otherAmount = amount;
      }
      recoveryAmount = Number.isFinite(recoveryAmount) ? recoveryAmount : 0;
      chequeAmount = Number.isFinite(chequeAmount) ? chequeAmount : 0;
      discountAmount = Number.isFinite(discountAmount) ? discountAmount : 0;
      biltyAmount = Number.isFinite(biltyAmount) ? biltyAmount : 0;
      otherAmount = Number.isFinite(otherAmount) ? otherAmount : 0;

      if (Number.isFinite(opening)) totals.opening += opening;
      totals.recovery += recoveryAmount;
      totals.cheque += chequeAmount;
      totals.discount += discountAmount;
      totals.bilty += biltyAmount;
      totals.other += otherAmount;
      if (Number.isFinite(lastBal)) totals.last += lastBal;

      const idsCsv = (row.ids && row.ids.length ? row.ids : [row.id]).join(',');
      const primaryId = row.id || (row.ids && row.ids[0]) || '';
      const pageNo = (row.page_no || row.custPage || '').toString().trim();

      $tb.append(`
        <tr data-id="${primaryId}" data-ids="${idsCsv}">
          <td class="text-center">${idx + 1}</td>
          <td class="text-center">${pageNo !== '' ? pageNo : '—'}</td>
          <td>${date}</td>
          <td>${desc}</td>
          <td class="text-end fw-semibold">${Number.isFinite(opening) ? fmt(opening) : '-'}</td>
          <td class="text-end text-success">${recoveryAmount > 0 ? fmt(recoveryAmount) : '-'}</td>
          <td class="text-end text-info">${chequeAmount > 0 ? fmt(chequeAmount) : '-'}</td>
          <td class="text-end text-primary">${discountAmount > 0 ? fmt(discountAmount) : '-'}</td>
          <td class="text-end text-warning">${biltyAmount > 0 ? fmt(biltyAmount) : '-'}</td>
          <td class="text-end text-secondary">${otherAmount > 0 ? fmt(otherAmount) : '-'}</td>
          <td class="text-end fw-semibold">${Number.isFinite(lastBal) ? fmt(lastBal) : '-'}</td>
          <td class="text-center">
            <input type="checkbox" class="form-check-input approve-row-check" value="${idsCsv}" title="Select for approve">
          </td>
          <td class="text-center text-nowrap">
            <button class="btn btn-sm btn-warning me-1 btn-edit-ledger" type="button"
              data-id="${primaryId}"
              title="Edit Entry">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-danger btn-delete-ledger" type="button" data-id="${primaryId}" data-ids="${idsCsv}" title="Delete Entry">
              <i class="bi bi-trash"></i>
            </button>
          </td>
        </tr>
      `);
    });
    $('#approveAllCheck').prop({ checked: false, indeterminate: false });
    $('#btnApproveRecoveryEntry').prop('disabled', true);
    setAreaLedgerTotals(totals);
  }

  $('#btnPrintAreaLedger').on('click', function () {
    if (!currentAreaCode) {
      Swal.fire({ icon: 'warning', title: 'Select area', text: 'Pehle area select karein.' });
      return;
    }
    const $rows = $('#customerLedgerTable tbody tr[data-id]');
    if (!$rows.length) {
      Swal.fire({ icon: 'warning', title: 'No data', text: 'Print ke liye entries nahi hain.' });
      return;
    }

    const printUrl = `{{ route('recovery.area-ledger.print') }}?area=${encodeURIComponent(currentAreaCode)}`;
    const w = window.open(printUrl, '_blank');
    if (!w) {
      Swal.fire({ icon: 'warning', title: 'Popup blocked', text: 'Please allow popups to print.' });
    }
  });

  function syncApproveSelection() {
    const total = $('#customerLedgerTable tbody .approve-row-check').length;
    const checked = $('#customerLedgerTable tbody .approve-row-check:checked').length;
    $('#approveAllCheck').prop('checked', total > 0 && checked === total);
    $('#approveAllCheck').prop('indeterminate', checked > 0 && checked < total);
    $('#btnApproveRecoveryEntry').prop('disabled', checked === 0);
  }

  $(document).on('change', '#approveAllCheck', function () {
    const on = $(this).is(':checked');
    $('#customerLedgerTable tbody .approve-row-check').prop('checked', on);
    syncApproveSelection();
  });

  $(document).on('change', '#customerLedgerTable tbody .approve-row-check', function () {
    syncApproveSelection();
  });

  $('#btnApproveRecoveryEntry').on('click', function () {
    const ids = [];
    $('#customerLedgerTable tbody .approve-row-check:checked').each(function () {
      String($(this).val() || '').split(',').forEach(function (part) {
        const n = parseInt(part, 10);
        if (n && ids.indexOf(n) === -1) ids.push(n);
      });
    });
    if (!ids.length) {
      Swal.fire({ icon: 'warning', title: 'Select entries', text: 'Pehle table se entry select karein.' });
      return;
    }

    Swal.fire({
      icon: 'question',
      title: 'Approve Recovery Entry?',
      text: ids.length + ' entr' + (ids.length === 1 ? 'y' : 'ies') + ' approve hogi (statuse = 1).',
      showCancelButton: true,
      confirmButtonText: 'Approve Recovery Entry',
      confirmButtonColor: '#0f2747'
    }).then(function (result) {
      if (!result.isConfirmed) return;
      const $btn = $('#btnApproveRecoveryEntry').prop('disabled', true);
      approveLedgerIds(ids, true)
        .done(function (res) {
          if (typeof showSuccess === 'function') showSuccess(res.message || 'Approved');
          loadAreaLedger(currentAreaCode);
        })
        .fail(function (xhr) {
          $btn.prop('disabled', false);
          Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Could not approve entries' });
        });
    });
  });
</script>

<!-- Cheque Details Modal (entry only — Save Recovery on main page) -->
<div class="modal fade" id="chequeDetailModal" tabindex="-1" aria-labelledby="chequeDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="chequeDetailForm" action="javascript:void(0)" onsubmit="return false;">
        <div class="modal-header recovery-card-head">
          <h5 class="modal-title" id="chequeDetailModalLabel"><i class="bi bi-bank2 me-2"></i>Cheque Details</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label small fw-semibold mb-1">Bank Name</label>
            <input type="text" id="chequeBankName" class="form-control form-control-sm" placeholder="Bank name" maxlength="150" required>
          </div>
          <div class="mb-2">
            <label class="form-label small fw-semibold mb-1">Cheque Number</label>
            <input type="text" id="chequeNo" class="form-control form-control-sm" placeholder="Cheque number" maxlength="100" required>
          </div>
          <div class="row g-2">
            <div class="col-6">
              <label class="form-label small fw-semibold mb-1">Cheque Date</label>
              <input type="date" id="chequeDate" class="form-control form-control-sm" required>
            </div>
            <div class="col-6">
              <label class="form-label small fw-semibold mb-1">Due Date (Clear)</label>
              <input type="date" id="chequeDueDate" class="form-control form-control-sm" required>
            </div>
          </div>
          <p class="small text-muted mb-0 mt-2">Enter cheque details here, then click Done and save with Save Recovery.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary btn-sm" id="btnChequeDone">
            <i class="bi bi-check2 me-1"></i>Done
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
