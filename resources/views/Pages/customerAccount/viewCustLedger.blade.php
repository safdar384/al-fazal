@extends('layout.app')

@section('title', 'Customer Ledger Report')

@section('content')

@include('components.toast-notification')

<style>
  /* Customer list dropdown — do not change */
  .position-relative .dropdown-menu {
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-top: 4px;
  }
  
  .position-relative .dropdown-menu .dropdown-item {
    padding: 10px 15px;
    cursor: pointer;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
  }
  
  .position-relative .dropdown-menu .dropdown-item:hover,
  .position-relative .dropdown-menu .dropdown-item.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-left-color: #ffc107;
    transform: translateX(3px);
  }
  
  .position-relative .dropdown-menu .dropdown-item strong {
    font-size: 0.95rem;
    display: block;
    margin-bottom: 3px;
  }
  
  .position-relative .dropdown-menu .dropdown-item .text-muted {
    font-size: 0.8rem;
    opacity: 0.8;
  }
  
  .position-relative .dropdown-menu .dropdown-item:hover .text-muted,
  .position-relative .dropdown-menu .dropdown-item.active .text-muted {
    color: rgba(255, 255, 255, 0.9) !important;
  }
  
  .position-relative input[type="text"].form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
  }
  
  .position-relative .dropdown-menu::-webkit-scrollbar {
    width: 6px;
  }
  
  .position-relative .dropdown-menu::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }
  
  .position-relative .dropdown-menu::-webkit-scrollbar-thumb {
    background: #667eea;
    border-radius: 10px;
  }
  
  .position-relative .dropdown-menu::-webkit-scrollbar-thumb:hover {
    background: #764ba2;
  }

  /* Page layout */
  .ledger-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
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
  @media print {
    .app-sidebar, .app-header, .app-content-header, #filterCard, .no-print { display: none !important; }
    #ledgerTable td.ledger-desc { max-width: none; overflow: visible; text-overflow: clip; }
  }
</style>

<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-6">
          <h3 class="mb-0"><i class="bi bi-journal-text text-primary me-2"></i>Customer Ledger Report</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('accountTransaction.index') }}">Accounts</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ledger Report</li>
          </ol>
        </div>
      </div>
      <hr class="mb-0" style="border-top: 2px solid #0d6efd;">
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <div class="card shadow-sm mb-4" id="filterCard">
        <div class="card-header ledger-card-head py-2">
          <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filters</h5>
        </div>
        <div class="card-body">
          <div class="row g-2 mb-3">
            <div class="col-md-6 col-lg-4" style="max-width: 24rem;">
              <label class="form-label small fw-semibold mb-1">
                <i class="bi bi-person-circle text-primary me-1"></i>Select Customer
              </label>
              <div class="position-relative">
                <input type="text"
                       class="form-control form-control-sm"
                       id="customerSearch"
                       placeholder="Type to search customer..."
                       autocomplete="off">
                <select class="form-select d-none" id="customerCode">
                  <option value="">Select Customer</option>
                  @isset($customers)
                    @foreach($customers as $c)
                      <option value="{{ $c->custCode }}"
                              data-name="{{ $c->custName }}"
                              data-business="{{ $c->bussnessName ?? '' }}"
                              data-area="{{ $c->custArea }}"
                              data-city="{{ $c->custCity }}">
                        {{ $c->custName }}@if($c->bussnessName) | {{ $c->bussnessName }}@endif - {{ $c->custArea }}
                      </option>
                    @endforeach
                  @endisset
                </select>
                <div id="customerDropdown" class="dropdown-menu w-100" style="display: none;"></div>
              </div>
            </div>
          </div>

          <div class="row g-2 align-items-end">
            <div class="col-md-3 col-lg-3">
              <label class="form-label small fw-semibold mb-1">Customer Name</label>
              <input class="form-control form-control-sm" id="customerName" type="text" placeholder="Auto-filled" disabled>
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-semibold mb-1">From Date</label>
              <input class="form-control form-control-sm" type="date" id="fromDate">
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-semibold mb-1">To Date</label>
              <input class="form-control form-control-sm" type="date" id="toDate" value="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="col-md-3 col-lg-2">
              <label class="form-label small fw-semibold mb-1">Current Balance</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text bg-light">₨</span>
                <input class="form-control fw-bold" id="preBalance" type="text" value="0.00" disabled>
              </div>
            </div>
            <div class="col-md-auto d-flex gap-2">
              <button type="button" class="btn btn-secondary btn-sm" id="btnPrint">
                <i class="bi bi-printer me-1"></i>Print
              </button>
              <a href="{{ route('accountTransaction.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-left-right me-1"></i>Transaction
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm" id="ledgerCard">
        <div class="card-header ledger-card-head d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 no-print">
          <h5 class="mb-0"><i class="bi bi-table me-2"></i>Ledger Entries</h5>
          <span class="badge bg-light text-dark" id="entryCount">0 entries</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0" id="ledgerTable">
              <thead class="table-primary">
                <tr>
                  <th style="width: 110px;">Date</th>
                  <th>Description</th>
                  <th class="text-end" style="width: 120px;">Credit</th>
                  <th class="text-end" style="width: 120px;">Debit</th>
                  <th class="text-end" style="width: 120px;">Balance</th>
                  <th class="text-center" style="width: 120px;">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="6" class="text-muted py-4 px-3">Select a customer to view ledger entries</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer bg-light py-2" id="summaryFooter" style="display:none;">
          <div class="row text-center g-0">
            <div class="col-md-4 py-2">
              <small class="text-muted d-block">Total Credit</small>
              <strong class="text-success" id="totalCredit">0.00</strong>
            </div>
            <div class="col-md-4 py-2 border-start border-end">
              <small class="text-muted d-block">Total Debit</small>
              <strong class="text-danger" id="totalDebit">0.00</strong>
            </div>
            <div class="col-md-4 py-2">
              <small class="text-muted d-block">Net Balance</small>
              <strong class="text-primary" id="netBalance">0.00</strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
<script>
  // Professional Confirmation Modal for Delete
  function showConfirmationModal(title, message, callback) {
    const modalId = 'confirmModal_' + Date.now();
    
    const handleCancel = function() {
      $(`#${modalId}`).fadeOut(300, function() {
        $(this).remove();
        callback(false);
      });
    };
    
    const handleConfirm = function() {
      $(`#${modalId}`).fadeOut(300, function() {
        $(this).remove();
        callback(true);
      });
    };
    
    const modalHtml = `
      <div id="${modalId}" class="modal-overlay" style="
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        animation: fadeIn 0.3s ease-in-out;
      ">
        <div class="modal-content" style="
          background: white;
          border-radius: 12px;
          box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
          max-width: 420px;
          width: 90%;
          animation: slideUp 0.3s ease-in-out;
          overflow: hidden;
        ">
          <!-- Modal Header -->
          <div style="
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
          ">
            <i class="bi bi-trash-fill" style="font-size: 24px;"></i>
            <h5 style="margin: 0; font-size: 16px; font-weight: 600;">${title}</h5>
          </div>
          
          <!-- Modal Body -->
          <div style="
            padding: 24px;
            text-align: center;
          ">
            <p style="
              margin: 0;
              color: #666;
              font-size: 14px;
              line-height: 1.6;
            ">${message}</p>
          </div>
          
          <!-- Modal Footer -->
          <div style="
            display: flex;
            gap: 10px;
            padding: 16px 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            justify-content: flex-end;
          ">
            <button id="btnCancel_${modalId}" style="
              padding: 8px 20px;
              border: 1px solid #dee2e6;
              background: white;
              color: #495057;
              border-radius: 6px;
              font-size: 14px;
              font-weight: 500;
              cursor: pointer;
              transition: all 0.2s;
            ">
              Cancel
            </button>
            <button id="btnConfirm_${modalId}" style="
              padding: 8px 20px;
              border: none;
              background: #dc3545;
              color: white;
              border-radius: 6px;
              font-size: 14px;
              font-weight: 500;
              cursor: pointer;
              transition: all 0.2s;
            ">
              <i class="bi bi-trash-fill me-1"></i> Delete
            </button>
          </div>
        </div>
      </div>
    `;
    
    $('body').append(modalHtml);
    
    // Attach event handlers
    $(`#btnCancel_${modalId}`).on('click', handleCancel);
    $(`#btnConfirm_${modalId}`).on('click', handleConfirm);
    
    // Add hover effects
    $(`#btnCancel_${modalId}`).on('mouseover', function() {
      $(this).css('background', '#f8f9fa').css('borderColor', '#adb5bd');
    }).on('mouseout', function() {
      $(this).css('background', 'white').css('borderColor', '#dee2e6');
    });
    
    $(`#btnConfirm_${modalId}`).on('mouseover', function() {
      $(this).css('background', '#c82333').css('transform', 'translateY(-2px)').css('boxShadow', '0 4px 12px rgba(220, 53, 69, 0.3)');
    }).on('mouseout', function() {
      $(this).css('background', '#dc3545').css('transform', 'translateY(0)').css('boxShadow', 'none');
    });
    
    // Close modal on overlay click
    $(`#${modalId}`).on('click', function(e) {
      if(e.target === this) {
        handleCancel();
      }
    });
  }

  // Add CSS animations
  if (!document.querySelector('style[data-delete-modal-animations]')) {
    const style = document.createElement('style');
    style.setAttribute('data-delete-modal-animations', 'true');
    style.textContent = `
      @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
      }
      @keyframes slideUp {
        from { transform: translateY(30px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
      }
    `;
    document.head.appendChild(style);
  }

  $(function(){
    // Customer Search with Dropdown
    const $searchInput = $('#customerSearch');
    const $customerSelect = $('#customerCode');
    const $dropdown = $('#customerDropdown');
    let allCustomers = [];
    
    console.log('Select element found:', $customerSelect.length);
    console.log('Total options in select:', $customerSelect.find('option').length);
    
    // Build customer array from select options
    $customerSelect.find('option').each(function() {
      console.log('Processing option:', $(this).val(), $(this).text());
      if($(this).val()) {
        allCustomers.push({
          code: $(this).val(),
          name: $(this).data('name'),
          business: $(this).data('business') || '',
          area: $(this).data('area'),
          city: $(this).data('city'),
          display: $(this).text()
        });
      }
    });
    
    console.log('Total customers loaded:', allCustomers.length);
    console.log('Sample customer:', allCustomers[0]);
    
    if(allCustomers.length === 0) {
      alert('WARNING: No customers loaded! Please check if customers exist in database.');
    }
    
    // Filter and display customers
    function filterCustomers(searchTerm) {
      const term = searchTerm.toLowerCase().trim();
      
      let filtered = allCustomers;
      
      if(term) {
        filtered = filtered.filter(c => 
          c.name.toLowerCase().includes(term) ||
          c.business.toLowerCase().includes(term) ||
          c.area.toLowerCase().includes(term) ||
          c.code.toLowerCase().includes(term)
        );
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
    
    // Search input events
    $searchInput.on('input', function() {
      filterCustomers($(this).val());
    });
    
    $searchInput.on('focus', function() {
      filterCustomers($(this).val());
    });
    
    // Dropdown item selection
    $dropdown.on('click', '.dropdown-item', function(e) {
      e.preventDefault();
      const code = $(this).data('code');
      const customer = allCustomers.find(c => c.code === code);
      
      $customerSelect.val(code).trigger('change');
      $searchInput.val(customer.business || customer.name);
      $dropdown.hide();
    });
    
    // Keyboard navigation
    $searchInput.on('keydown', function(e) {
      const $items = $dropdown.find('.dropdown-item');
      const $active = $items.filter('.active');
      let $next;
      
      if(e.key === 'ArrowDown') {
        e.preventDefault();
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
      } else if(e.key === 'Enter') {
        e.preventDefault();
        if($active.length) {
          $active.click();
        }
      }
    });
    
    // Click outside to close dropdown
    $(document).on('click', function(e) {
      if(!$(e.target).closest('.position-relative').length) {
        $dropdown.hide();
      }
    });

    function fmt(n){ return (Number(n||0)).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}); }

    function renderLedger(entries){
      const $tb = $('#ledgerTable tbody').empty();
      if (!entries || !entries.length){
        $tb.append('<tr><td colspan="6" class="text-muted py-4 px-3">No ledger entries found</td></tr>');
        $('#preBalance').val('0.00');
        $('#entryCount').text('0 entries');
        $('#summaryFooter').hide();
        return;
      }

      let totalCredit = 0, totalDebit = 0;
      entries.forEach(e => {
        totalCredit += parseFloat(e.credit || 0);
        totalDebit += parseFloat(e.debit || 0);
        const cat = (e.category || '').replace(/"/g, '&quot;');
        const inv = (e.invoiceNumber || '').replace(/"/g, '&quot;');
        const hasInvoice = !!(e.invoiceNumber && String(e.invoiceNumber).trim());
        const viewBtn = hasInvoice
          ? `<button type="button" class="btn btn-outline-primary py-0 px-2" title="View Invoice"
                  onclick="viewInvoice(${e.id})">
               <i class="bi bi-eye"></i>
             </button>`
          : '';
        const desc = String(e.description ?? '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const descLine = e.category
          ? desc + ' <span class="text-muted">— ' + String(e.category).replace(/</g, '&lt;') + '</span>'
          : desc;
        $tb.append(`
          <tr data-id="${e.id}" data-category="${cat}" data-invoice="${inv}">
            <td>${e.date}</td>
            <td class="ledger-desc" title="${desc.replace(/"/g, '&quot;')}">${descLine}</td>
            <td class="text-end text-success">${fmt(e.credit)}</td>
            <td class="text-end text-danger">${fmt(e.debit)}</td>
            <td class="text-end fw-semibold">${fmt(e.balance)}</td>
            <td class="text-center text-nowrap">
              <div class="btn-group btn-group-sm" role="group">
                ${viewBtn}
                <button type="button" class="btn btn-outline-warning py-0 px-2" title="Edit"
                        onclick="openEdit(${e.id})">
                  <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="btn btn-outline-danger py-0 px-2" title="Delete"
                        onclick="deleteEntry(${e.id})">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>
        `);
      });

      const last = entries[entries.length-1]?.balance || 0;
      $('#preBalance').val(fmt(last));
      $('#printBalance').text('₨ ' + fmt(last));
      $('#entryCount').text(`${entries.length} ${entries.length === 1 ? 'entry' : 'entries'}`);
      
      // Show summary footer
      $('#totalCredit').text(fmt(totalCredit));
      $('#totalDebit').text(fmt(totalDebit));
      $('#netBalance').text(fmt(last));
      $('#summaryFooter').fadeIn();
    }

    function loadLedger(){
      const code = $('#customerCode').val();
      const name = $('#customerCode').find('option:selected').data('name') || '';
      const area = $('#customerCode').find('option:selected').data('area') || '';
      $('#customerName').val(name);
      if (!code) return;
      
      // Update print header
      $('#printCustCode').text(code);
      $('#printCustName').text(name);
      $('#printCustArea').text(area);
      
      const from = $('#fromDate').val();
      const to = $('#toDate').val();
      const periodText = from && to ? `${from} to ${to}` : (from ? `From ${from}` : (to ? `Until ${to}` : 'All Time'));
      $('#printPeriod').text(periodText);
      
      const qs = $.param({ from, to });
      $.get(`{{ url('/customer-ledger') }}/${code}?${qs}`)
        .done(res => renderLedger(res.entries))
        .fail(() => showToastError('Error', 'Failed to load ledger'));
    }

    $('#customerCode').on('change', loadLedger);
    $('#fromDate,#toDate').on('change', function(){
      // Optional auto-load on date change if customer already selected
      if ($('#customerCode').val()) loadLedger();
    });

    // Print - Open preview in new window
    $('#btnPrint').on('click', function(e){ 
      e.preventDefault(); 
      const code = $('#customerCode').val();
      if(!code) {
        showToastError('Error', 'Please select a customer first');
        return;
      }
      const from = $('#fromDate').val();
      const to = $('#toDate').val();
      const qs = $.param({ from, to });
      const url = `{{ url('/customer-ledger/preview') }}/${code}?${qs}`;
      window.open(url, '_blank');
    });
  });
</script>

<!-- Edit / Delete actions -->
<script>
  function isSaleInvoiceCategory(cat) {
    const c = String(cat || '').toLowerCase();
    return c === 'sale invoice' || c === 'saleinvoice' || c === 'cash sale' || c === 'cashsale';
  }

  function isPurchaseInvoiceCategory(cat) {
    const c = String(cat || '').toLowerCase();
    return c === 'purchase' || c === 'purchase invoice';
  }

  function viewInvoice(id) {
    const $row = $(`tr[data-id="${id}"]`);
    const category = $row.data('category') || '';
    const invoiceNo = String($row.data('invoice') || '').trim();

    if (!invoiceNo) {
      showToastError('Cannot View', 'Invoice number missing for this entry.');
      return;
    }

    let url = '';
    if (isPurchaseInvoiceCategory(category)) {
      url = `{{ url('/purchase/invoice') }}/${encodeURIComponent(invoiceNo)}/preview`;
    } else if (isSaleInvoiceCategory(category)) {
      url = `{{ url('/sale/invoice') }}/${encodeURIComponent(invoiceNo)}/preview`;
    } else {
      // Fallback: treat numbered invoice as sale preview
      url = `{{ url('/sale/invoice') }}/${encodeURIComponent(invoiceNo)}/preview`;
    }

    window.open(url, '_blank');
  }

  function isEditableLedgerCategory(cat) {
    return ['AccountTransaction', 'Recovery', 'Discount', 'Bilty', 'Other', 'Cheque', 'Transfer'].includes(cat);
  }

  function deleteEntry(id){
    const $row = $(`tr[data-id="${id}"]`);
    const category = $row.data('category') || '';
    const invoiceNo = $row.data('invoice') || '';

    const confirmAndDelete = function(message) {
      const run = function() {
        $.ajax({
          url: `{{ url('/ledger-transaction/delete') }}/${id}`,
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        })
        .done(() => {
          showToastSuccess('Deleted', 'Entry deleted. Linked bank amounts were updated where applicable.');
          $('#customerCode').val() && $('#customerCode').trigger('change');
        })
        .fail(xhr => showToastError('Delete Failed', xhr.responseJSON?.message || 'Failed to delete'));
      };

      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'warning',
          title: 'Confirm Delete?',
          text: message,
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Yes, delete'
        }).then(result => { if (result.isConfirmed) run(); });
      } else if (confirm(message)) {
        run();
      }
    };

    if (isSaleInvoiceCategory(category) || isPurchaseInvoiceCategory(category)) {
      if (!invoiceNo) {
        showToastError('Cannot Delete', 'Invoice number missing for this entry.');
        return;
      }
      confirmAndDelete('This will delete the full invoice and related ledger/stock effects. Continue?');
      return;
    }

    confirmAndDelete('This will delete the ledger entry and any linked bank posting.');
  }

  function openEdit(id){
    const $row = $(`tr[data-id="${id}"]`);
    const category = $row.data('category') || '';
    const invoiceNo = $row.data('invoice') || '';

    // Sale / Purchase invoice → open invoice edit page
    if (isSaleInvoiceCategory(category)) {
      if (!invoiceNo) {
        showToastError('Cannot Edit', 'Invoice number missing for this sale entry.');
        return;
      }
      window.location.href = `{{ url('/sale/invoice/edit') }}/${encodeURIComponent(invoiceNo)}`;
      return;
    }
    if (isPurchaseInvoiceCategory(category)) {
      if (!invoiceNo) {
        showToastError('Cannot Edit', 'Invoice number missing for this purchase entry.');
        return;
      }
      window.location.href = `{{ url('/purchase/invoice/edit') }}/${encodeURIComponent(invoiceNo)}`;
      return;
    }

    if (!isEditableLedgerCategory(category)) {
      showToastError('Cannot Edit', 'This entry type cannot be edited from the ledger report.');
      return;
    }

    $('#editEntryForm')[0]?.reset();
    $('#editTypeWrap').hide();
    $('#editChequeWrap').hide();
    $('#editDescription').prop('required', true);
    $('#editCategory').text('');
    $('#editId').val(id);
    $.get(`{{ url('/customer-ledger/entry') }}/${id}`)
      .done(e => {
        $('#editCategory').text(e.category || '');
        let baseDesc = (e.description || '')
          .replace(/\s*\((?:Transfer\s+(?:to|from)\s+[^\)]+)\)\s*/ig, ' ')
          .replace(/\s*\(Chq\s+.+?\)$/i, '')
          .trim();
        $('#editDescription').val(baseDesc);
        $('#editDate').val(e.customerDate || '{{ now()->format('Y-m-d') }}');
        const amount = e.credit > 0 ? e.credit : e.debit;
        $('#editAmount').val(amount);
        if (e.category === 'AccountTransaction') {
          $('#editTypeWrap').show();
          $('#editType').val(e.credit > 0 ? 'credit' : 'debit');
        } else {
          $('#editTypeWrap').hide();
        }

        const hasCheque = e.category === 'Cheque' || !!(e.cheque && (e.cheque.bank_name || e.cheque.cheque_no));
        if (hasCheque) {
          $('#editChequeWrap').show();
          $('#editDescription').prop('required', false);
          const chq = e.cheque || {};
          $('#editBankName').val(chq.bank_name || '');
          $('#editChequeNo').val(chq.cheque_no || '');
          $('#editChequeDate').val((chq.cheque_date || '').toString().substring(0, 10));
          $('#editChequeDueDate').val((chq.due_date || '').toString().substring(0, 10));
        } else {
          $('#editChequeWrap').hide();
          $('#editBankName, #editChequeNo, #editChequeDate, #editChequeDueDate').val('');
        }

        const modal = new bootstrap.Modal(document.getElementById('editEntryModal'));
        modal.show();
      })
      .fail(() => showToastError('Error', 'Failed to load entry'));
  }

  $(document).on('submit', '#editEntryForm', function(ev){
    ev.preventDefault();
    const id = $('#editId').val();
    const isCheque = $('#editChequeWrap').is(':visible');
    const payload = {
      description: $('#editDescription').val(),
      amount: $('#editAmount').val(),
      customerDate: $('#editDate').val(),
      type: $('#editTypeWrap').is(':visible') ? $('#editType').val() : undefined,
      _method: 'PUT'
    };

    if (isCheque) {
      const bankName = $('#editBankName').val().trim();
      const chequeNo = $('#editChequeNo').val().trim();
      const chequeDate = $('#editChequeDate').val();
      const dueDate = $('#editChequeDueDate').val();
      if (!bankName || !chequeNo || !chequeDate || !dueDate) {
        showToastError('Required', 'Please fill Bank Name, Cheque No, Cheque Date and Due Date.');
        return false;
      }
      payload.paymentMode = 'Cheque';
      payload.bank_name = bankName;
      payload.cheque_no = chequeNo;
      payload.cheque_date = chequeDate;
      payload.due_date = dueDate;
    }

    $.ajax({
      url: `{{ url('/ledger-transaction/update') }}/${id}`,
      method: 'POST',
      data: payload,
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    })
    .done(() => {
      showToastSuccess('Updated', isCheque
        ? 'Cheque and ledger entry updated successfully.'
        : 'Entry updated. Linked bank amounts synced where applicable.');
      const modalEl = document.getElementById('editEntryModal');
      const modal = bootstrap.Modal.getInstance(modalEl);
      modal.hide();
      $('#customerCode').val() && $('#customerCode').trigger('change');
    })
    .fail(xhr => { showToastError('Update Failed', xhr.responseJSON?.message || 'Failed to update'); });
    return false;
  });
</script>

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
            <input type="text" id="editDescription" class="form-control form-control-sm">
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
          <div class="mt-3 pt-2 border-top" id="editChequeWrap" style="display:none;">
            <h6 class="small fw-semibold mb-2"><i class="bi bi-bank2 me-1"></i>Cheque Details</h6>
            <div class="mb-2">
              <label class="form-label">Bank Name</label>
              <input type="text" id="editBankName" class="form-control form-control-sm" maxlength="150" placeholder="Bank name">
            </div>
            <div class="mb-2">
              <label class="form-label">Cheque Number</label>
              <input type="text" id="editChequeNo" class="form-control form-control-sm" maxlength="100" placeholder="Cheque number">
            </div>
            <div class="row g-2">
              <div class="col-6">
                <label class="form-label">Cheque Date</label>
                <input type="date" id="editChequeDate" class="form-control form-control-sm">
              </div>
              <div class="col-6">
                <label class="form-label">Due Date (Clear)</label>
                <input type="date" id="editChequeDueDate" class="form-control form-control-sm">
              </div>
            </div>
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

      @endsection