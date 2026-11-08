@extends('layout.app')

@section('title', 'Expense Transaction')

@section('content')

<style>
  .expense-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
  }
  .expense-txn-row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 0.5rem;
  }
  .expense-txn-row .expense-field {
    flex: 1 1 7rem;
    min-width: 6.5rem;
  }
  .expense-txn-row .expense-field--wide {
    flex: 2 1 10rem;
    min-width: 9rem;
  }
  .expense-txn-row .expense-field--btn {
    flex: 0 0 auto;
    min-width: 7.5rem;
  }
  .expense-txn-row .expense-label-row {
    min-height: 1.375rem;
    margin-bottom: 0.25rem;
    display: flex;
    align-items: center;
    width: 100%;
  }
  .expense-txn-row .form-control-sm,
  .expense-txn-row .form-select-sm {
    min-height: calc(1.5em + 0.5rem + 2px);
  }
  #filterCard .select2-container { width: 100% !important; }
  #filterCard .select2-container .select2-selection--single {
    height: 31px !important;
    border: 1px solid #ced4da;
  }
  #filterCard .select2-dropdown {
    z-index: 1060;
  }
  #filterCard .select2-results__option--highlighted {
    background-color: #0f2747 !important;
    color: #fff !important;
  }
  .expense-info-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    background: #f4f7fb;
    border: 1px solid #d5dde8;
    border-radius: 4px;
    padding: 0.35rem 0.65rem;
    font-size: 0.8rem;
    white-space: nowrap;
  }
  .expense-info-pill .lbl { color: #6b7c8d; font-weight: 600; }
  .expense-info-pill .val { color: #0f2747; font-weight: 700; }

  @media print {
    .app-sidebar, .app-header, .app-content-header, #filterCard, .btn, button, .no-print { display: none !important; }
    body, .app-main { margin: 0; padding: 0; }
    .app-content { padding: 0 !important; }
    .container-fluid { padding: 10mm !important; }
    .card { box-shadow: none !important; border: none !important; margin: 0 !important; padding: 0 !important; }
    .card-header { display: none !important; }
    .card-body { padding: 0 !important; }
    #expenseTable { font-size: 9pt; width: 100%; }
    #expenseTable thead { display: table-header-group; }
    #expenseTable th {
      border: 1px solid #000 !important;
      padding: 6px !important;
      background-color: #f0f0f0 !important;
      font-weight: bold;
    }
    #expenseTable td {
      border: 1px solid #000 !important;
      padding: 5px !important;
    }
    @page { margin: 5mm; size: A4 landscape; }
  }
</style>

<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-6">
          <h3 class="mb-0">
            <i class="bi bi-cash-coin text-primary me-2"></i>Expense Transaction
          </h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Expense</li>
            <li class="breadcrumb-item active" aria-current="page">Transaction</li>
          </ol>
        </div>
      </div>
      <hr class="mb-0" style="border-top: 2px solid #0d6efd;">
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      {{-- Select Expense + Record Transaction --}}
      <div class="card mb-4 shadow-sm" id="filterCard">
        <div class="card-header expense-card-head py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
          <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Record Expense Transaction</h5>
          <div class="d-flex gap-1">
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addExpenseModal" title="Add Expense">
              <i class="bi bi-plus-lg me-1"></i>Add Expense
            </button>
            <button type="button" class="btn btn-outline-light btn-sm" id="btnDeleteExpense" title="Delete Expense">
              <i class="bi bi-trash3 me-1"></i>Delete
            </button>
          </div>
        </div>
        <div class="card-body">
          {{-- Row 1: Expense selection + info --}}
          <div class="row g-2 align-items-end mb-3">
            <div class="col-md-4">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label small fw-semibold mb-0">
                  <i class="bi bi-wallet2 text-primary me-1"></i>Expense
                </label>
                <small id="expenseBalanceDisplay" class="text-muted" style="display: none;">
                  <strong id="expenseBalanceInline" class="text-danger">0.00</strong>
                  <span id="expenseAfterTxnWrap" class="ms-2" style="display: none;">
                    <i class="bi bi-arrow-right-short text-muted"></i>
                    <strong id="expenseAfterTxnValue" class="text-info">0.00</strong>
                  </span>
                </small>
              </div>
              <select class="form-select form-select-sm" id="selectExpense" style="width: 100%;">
                <option value=""></option>
                @foreach($expenses as $expense)
                  <option value="{{ $expense->expenseCode }}">{{ $expense->expenseCode }} - {{ $expense->expenseName }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-8">
              <div class="d-flex flex-wrap gap-2 align-items-center">
                <div class="expense-info-pill">
                  <span class="lbl">Name</span>
                  <span class="val" id="expenseNameDisplay">—</span>
                </div>
                <div class="expense-info-pill">
                  <span class="lbl">Category</span>
                  <span class="val" id="expenseCategoryDisplay">—</span>
                </div>
                <div class="expense-info-pill">
                  <span class="lbl">Balance</span>
                  <span class="val text-danger" id="expenseBalanceDisplayPill">0.00</span>
                </div>
              </div>
              {{-- Hidden fields kept for JS compatibility --}}
              <input type="hidden" id="expenseName">
              <input type="hidden" id="expenseCategory">
              <input type="hidden" id="expenseBalance">
            </div>
          </div>

          {{-- Row 2: Transaction entry (single line) --}}
          <div class="expense-txn-row">
            <div class="expense-field expense-field--wide">
              <label class="form-label fw-semibold small expense-label-row">Description</label>
              <input type="text" class="form-control form-control-sm expense-nav" id="description" placeholder="Enter description">
            </div>
            <div class="expense-field">
              <label class="form-label fw-semibold small expense-label-row">Amount</label>
              <input type="number" class="form-control form-control-sm expense-nav" id="amount" placeholder="0" step="0.01">
            </div>
            <div class="expense-field expense-field--wide">
              <div class="expense-label-row justify-content-between gap-2">
                <label class="form-label fw-semibold small mb-0">Bank Account</label>
                <small id="bankBalanceDisplay" class="text-muted" style="display: none;">
                  <strong id="bankBalanceInline" class="text-primary">0.00</strong>
                  <span id="bankAfterTxnWrap" class="ms-2" style="display: none;">
                    <i class="bi bi-arrow-right-short text-muted"></i>
                    <strong id="bankAfterTxnValue" class="text-info">0.00</strong>
                  </span>
                </small>
              </div>
              <select class="form-select form-select-sm expense-nav" id="bankAccount">
                <option value="">Select Bank</option>
                @foreach($banks as $bank)
                  <option value="{{ $bank->bankAccount }}">{{ $bank->bankName }} - {{ $bank->bankAccount }}</option>
                @endforeach
              </select>
            </div>
            <div class="expense-field">
              <label class="form-label fw-semibold small expense-label-row">Type</label>
              <select class="form-select form-select-sm expense-nav" id="transactionType">
                <option value="">Select Type</option>
                <option value="Debit">Debit</option>
                <option value="Credit">Credit</option>
              </select>
            </div>
            <div class="expense-field expense-field--btn">
              <div class="expense-label-row mb-0" aria-hidden="true">&nbsp;</div>
              <button type="button" class="btn btn-primary btn-sm w-100 text-nowrap" id="btnSaveTransaction">
                <i class="bi bi-save me-1"></i>Save
              </button>
            </div>
          </div>
        </div>
      </div>

      {{-- Transaction History --}}
      <div class="card shadow-sm">
        <div class="card-header expense-card-head d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
          <h5 class="mb-0">
            <i class="bi bi-table me-2"></i>Transaction History
            <span class="badge bg-light text-dark ms-2" id="recordCount">0 records</span>
          </h5>
          <a href="{{ route('expense.ledger') }}" class="btn btn-light btn-sm">
            <i class="bi bi-journal-text me-1"></i>Expense Ledger
          </a>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="expenseTable">
              <thead class="table-light">
                <tr>
                  <th class="text-center" style="width:4%;">#</th>
                  <th>Date</th>
                  <th>Expense Code</th>
                  <th>Description</th>
                  <th class="text-end">Amount</th>
                  <th class="text-end">Debit</th>
                  <th class="text-end">Credit</th>
                  <th class="text-end">Balance</th>
                  <th class="text-center" style="width:1%;">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="9" class="text-center text-muted py-5">
                    <i class="bi bi-inbox display-4 d-block mb-2 text-secondary"></i>
                    <p class="mb-0">Select an expense to view transactions</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

{{-- Add Expense Modal --}}
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header expense-card-head py-2">
        <h5 class="modal-title" id="addExpenseModalLabel">
          <i class="bi bi-plus-circle me-2"></i>Add New Expense
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-semibold small">Expense Code <span class="text-danger">*</span></label>
          <input type="text" class="form-control form-control-sm" id="newExpenseCode" placeholder="e.g., EXP001">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold small">Expense Name <span class="text-danger">*</span></label>
          <input type="text" class="form-control form-control-sm" id="newExpenseName" placeholder="e.g., Office Rent">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold small">Category</label>
          <select class="form-select form-select-sm" id="newExpenseCategory">
            <option value="">Select Category</option>
            <option value="Rent">Rent</option>
            <option value="Utilities">Utilities</option>
            <option value="Salaries">Salaries</option>
            <option value="Office Supplies">Office Supplies</option>
            <option value="Transportation">Transportation</option>
            <option value="Maintenance">Maintenance</option>
            <option value="Other">Other</option>
          </select>
        </div>
        <div class="mb-0">
          <label class="form-label fw-semibold small">Description</label>
          <textarea class="form-control form-control-sm" id="newExpenseDescription" rows="3" placeholder="Enter description"></textarea>
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i>Cancel
        </button>
        <button type="button" class="btn btn-primary btn-sm" id="btnSaveExpense">
          <i class="bi bi-check-circle me-1"></i>Save Expense
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Edit Transaction Modal --}}
<div class="modal fade" id="editTransactionModal" tabindex="-1" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header expense-card-head py-2">
        <h5 class="modal-title" id="editTransactionModalLabel">
          <i class="bi bi-pencil-square me-2"></i>Edit Transaction
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editTransactionId">
        <div class="mb-3">
          <label class="form-label fw-semibold small">Description</label>
          <input type="text" class="form-control form-control-sm" id="editDescription" placeholder="Enter description">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold small">Amount <span class="text-danger">*</span></label>
          <input type="number" class="form-control form-control-sm" id="editAmount" placeholder="0" step="0.01">
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold small">Bank Account</label>
          <select class="form-select form-select-sm" id="editBankAccount">
            <option value="">Select Bank</option>
            @foreach($banks as $bank)
              <option value="{{ $bank->bankAccount }}">{{ $bank->bankName }} - {{ $bank->bankAccount }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-0">
          <label class="form-label fw-semibold small">Type <span class="text-danger">*</span></label>
          <select class="form-select form-select-sm" id="editTransactionType">
            <option value="">Select Type</option>
            <option value="Debit">Debit</option>
            <option value="Credit">Credit</option>
          </select>
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i>Cancel
        </button>
        <button type="button" class="btn btn-primary btn-sm" id="btnUpdateTransaction">
          <i class="bi bi-check-circle me-1"></i>Update
        </button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  let selectedExpenseBalance = 0;
  let selectedBankBalance = 0;

  $('#selectExpense').select2({
    width: '100%',
    placeholder: 'Choose Expense',
    allowClear: true,
    dropdownParent: $('#filterCard')
  });

  function fmt(n) {
    return parseFloat(n || 0).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function setExpenseInfo(name, category, balance) {
    $('#expenseName').val(name || '');
    $('#expenseCategory').val(category || '');
    $('#expenseBalance').val(balance !== '' && balance !== null && balance !== undefined ? fmt(balance) : '');
    $('#expenseNameDisplay').text(name || '—');
    $('#expenseCategoryDisplay').text(category || '—');
    $('#expenseBalanceDisplayPill').text(balance !== '' && balance !== null && balance !== undefined ? fmt(balance) : '0.00');
  }

  /** Expense ledger: debit increases balance, credit decreases */
  function projectExpenseBalance(current, amount, entryType) {
    const amt = parseFloat(amount) || 0;
    if (amt <= 0) return null;
    return entryType === 'Debit' ? current + amt : current - amt;
  }

  /** Bank: expense debit = money out (bank credit), expense credit = money in (bank debit) */
  function projectBankFromExpenseTxn(current, amount, entryType) {
    const amt = parseFloat(amount) || 0;
    if (amt <= 0) return null;
    return entryType === 'Debit' ? current - amt : current + amt;
  }

  function showAfter($wrap, $valEl, afterVal) {
    if (afterVal === null || afterVal === undefined) {
      $wrap.hide();
      return;
    }
    $valEl.text(fmt(afterVal)).removeClass('text-info text-danger text-primary')
      .addClass(afterVal < 0 ? 'text-danger' : 'text-info');
    $wrap.show();
  }

  function updateProjectedBalances() {
    const amount = $('#amount').val();
    const entryType = $('#transactionType').val();

    if ($('#expenseBalanceDisplay').is(':visible') && entryType) {
      showAfter($('#expenseAfterTxnWrap'), $('#expenseAfterTxnValue'),
        projectExpenseBalance(selectedExpenseBalance, amount, entryType));
    } else {
      $('#expenseAfterTxnWrap').hide();
    }

    const bankCode = $('#bankAccount').val();
    if ($('#bankBalanceDisplay').is(':visible') && bankCode && entryType) {
      showAfter($('#bankAfterTxnWrap'), $('#bankAfterTxnValue'),
        projectBankFromExpenseTxn(selectedBankBalance, amount, entryType));
    } else {
      $('#bankAfterTxnWrap').hide();
    }
  }

  function loadBankBalance(bankCode) {
    if (!bankCode) {
      selectedBankBalance = 0;
      $('#bankBalanceDisplay, #bankAfterTxnWrap').hide();
      updateProjectedBalances();
      return;
    }
    $.get(`{{ url('/api/bank-balance') }}/${encodeURIComponent(bankCode)}`)
      .done(function (res) {
        if (res.success) {
          selectedBankBalance = parseFloat(res.balance ?? res.current_balance ?? 0) || 0;
          $('#bankBalanceInline').text(fmt(selectedBankBalance));
          $('#bankBalanceDisplay').show();
          updateProjectedBalances();
        } else {
          selectedBankBalance = 0;
          $('#bankBalanceDisplay, #bankAfterTxnWrap').hide();
        }
      })
      .fail(function () {
        selectedBankBalance = 0;
        $('#bankBalanceDisplay, #bankAfterTxnWrap').hide();
      });
  }

  function loadExpenseDetails(expenseCode) {
    if (!expenseCode) {
      selectedExpenseBalance = 0;
      setExpenseInfo('', '', '');
      $('#expenseBalanceDisplay, #expenseAfterTxnWrap').hide();
      $('#expenseTable tbody').html('<tr><td colspan="9" class="text-center text-muted py-5"><i class="bi bi-inbox display-4 d-block mb-2 text-secondary"></i><p class="mb-0">Select an expense to view transactions</p></td></tr>');
      $('#recordCount').text('0 records');
      return;
    }

    $.get(`{{ url('/expense') }}/${encodeURIComponent(expenseCode)}/details`)
      .done(function (res) {
        selectedExpenseBalance = parseFloat(res.balance) || 0;
        setExpenseInfo(res.expenseName || '', res.expenseCategory || '', selectedExpenseBalance);
        $('#expenseBalanceInline').text(fmt(selectedExpenseBalance));
        $('#expenseBalanceDisplay').show();
        updateProjectedBalances();
        loadExpenseLedger(expenseCode);
      })
      .fail(function () {
        showError('Failed to load expense details');
        selectedExpenseBalance = 0;
        $('#expenseBalanceDisplay, #expenseAfterTxnWrap').hide();
      });
  }

  $('#selectExpense').on('change', function () {
    loadExpenseDetails($(this).val() || '');
  });

  $('#bankAccount').on('change', function () {
    loadBankBalance($(this).val());
  });

  $('#amount, #transactionType').on('input change', updateProjectedBalances);

  function focusNextExpenseField(current) {
    const $fields = $('.expense-nav').filter(':visible');
    const idx = $fields.index(current);
    if (idx < 0) return;
    if (idx < $fields.length - 1) {
      const $next = $fields.eq(idx + 1);
      $next.focus();
      if ($next.is('select')) {
        $next[0].focus();
      }
    } else {
      $('#btnSaveTransaction').trigger('click');
    }
  }

  // Enter → next field (inputs + selects)
  $(document).on('keydown', '.expense-nav', function (e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    focusNextExpenseField(this);
  });

  // Load expense ledger with running balance
  function loadExpenseLedger(expenseCode) {
    $.get(`{{ url('/expense') }}/${expenseCode}/ledger`)
      .done(res => {
        const $tb = $('#expenseTable tbody').empty();
        if(!res.data || !res.data.length){
          $tb.append('<tr><td colspan="9" class="text-center text-muted py-5"><i class="bi bi-inbox display-4 d-block mb-2 text-secondary"></i><p class="mb-0">No transactions found</p></td></tr>');
          $('#recordCount').text('0 records');
          return;
        }

        let runningBalance = 0;
        const reversedData = [...res.data].reverse();

        reversedData.forEach(row => {
          runningBalance += parseFloat(row.debit || 0) - parseFloat(row.credit || 0);
          row.runningBalance = runningBalance;
        });

        res.data.forEach((row, idx) => {
          const date = row.entryDate ? new Date(row.entryDate).toLocaleDateString() : '';
          const balanceClass = row.runningBalance >= 0 ? 'text-danger fw-semibold' : 'text-success fw-semibold';

          $tb.append(`
            <tr>
              <td class="text-center">${idx+1}</td>
              <td>${date}</td>
              <td>${row.expenseCode}</td>
              <td>${row.description || ''}</td>
              <td class="text-end">${fmt(row.amount)}</td>
              <td class="text-end text-danger fw-semibold">${fmt(row.debit)}</td>
              <td class="text-end text-success fw-semibold">${fmt(row.credit)}</td>
              <td class="text-end ${balanceClass}">${fmt(row.runningBalance)}</td>
              <td class="text-center text-nowrap">
                <button class="btn btn-sm btn-outline-primary btnEditLedger py-0 px-2 me-1" data-id="${row.id}" data-description="${row.description || ''}" data-amount="${row.amount}" data-account="${row.accountCode || ''}" data-type="${row.category}" title="Edit">
                  <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger btnDeleteLedger py-0 px-2" data-id="${row.id}" title="Delete">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          `);
        });

        $('#recordCount').text(`${res.data.length} ${res.data.length === 1 ? 'record' : 'records'}`);
      })
      .fail(err => {
        console.error('Error:', err);
        showError('Failed to load ledger');
      });
  }

  $('#btnSaveTransaction').on('click', function(){
    const expenseCode = $('#selectExpense').val();
    if(!expenseCode){
      showWarning('Please select an expense');
      return;
    }

    const transactionType = $('#transactionType').val();
    if(!transactionType){
      showWarning('Please select transaction type');
      return;
    }

    const amount = $('#amount').val();
    if(!amount || amount <= 0){
      showWarning('Please enter a valid amount');
      return;
    }

    const bankAccount = $('#bankAccount').val();

    const data = {
      _token: '{{ csrf_token() }}',
      expenseCode: expenseCode,
      description: $('#description').val(),
      amount: amount,
      paymentMethod: 'Bank',
      accountCode: bankAccount || '',
      category: transactionType
    };

    $.ajax({
      url: '{{ url("/expense/store") }}',
      method: 'POST',
      data: data,
      success: function(res){
        if(res.success){
          showSuccess('Transaction saved successfully!');
          $('#description').val('');
          $('#amount').val('');
          $('#bankAccount').val('').trigger('change');
          $('#transactionType').val('');
          updateProjectedBalances();
          loadExpenseDetails($('#selectExpense').val());
          if ($('#bankAccount').val()) {
            loadBankBalance($('#bankAccount').val());
          }
          $('#description').focus();
        } else {
          showError('Failed to save', res.message || '');
        }
      },
      error: function(xhr){
        console.error('Error:', xhr);
        const msg = xhr.responseJSON?.message
          || (xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join(' ') : '')
          || xhr.statusText
          || 'Unknown error';
        showError('Failed to save', msg);
      }
    });
  });

  $('#btnSaveExpense').on('click', function(){
    const expenseCode = $('#newExpenseCode').val();
    const expenseName = $('#newExpenseName').val();

    if(!expenseCode){
      showWarning('Please enter expense code');
      return;
    }

    if(!expenseName){
      showWarning('Please enter expense name');
      return;
    }

    const data = {
      _token: '{{ csrf_token() }}',
      expenseCode: expenseCode,
      expenseName: expenseName,
      expenseCategory: $('#newExpenseCategory').val(),
      description: $('#newExpenseDescription').val()
    };

    $.ajax({
      url: '{{ url("/expense/store-expense") }}',
      method: 'POST',
      data: data,
      success: function(res){
        if(res.success){
          showSuccess('Expense created successfully!');
          $('#addExpenseModal').modal('hide');
          const code = res.expense?.expenseCode || expenseCode;
          const name = res.expense?.expenseName || expenseName;
          if ($('#selectExpense option[value="' + code.replace(/"/g, '\\"') + '"]').length === 0) {
            $('#selectExpense').append(new Option(code + ' - ' + name, code, false, false));
          }
          $('#selectExpense').val(code).trigger('change');
          $('#newExpenseCode').val('');
          $('#newExpenseName').val('');
          $('#newExpenseCategory').val('');
          $('#newExpenseDescription').val('');
          $('#description').focus();
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

  $(document).on('click', '.btnEditLedger', function(){
    const id = $(this).data('id');
    const description = $(this).data('description');
    const amount = $(this).data('amount');
    const accountCode = $(this).data('account');
    const transactionType = $(this).data('type');

    $('#editTransactionId').val(id);
    $('#editDescription').val(description);
    $('#editAmount').val(amount);
    $('#editBankAccount').val(accountCode);
    $('#editTransactionType').val(transactionType);

    $('#editTransactionModal').modal('show');
  });

  $('#btnUpdateTransaction').on('click', function(){
    const id = $('#editTransactionId').val();
    const transactionType = $('#editTransactionType').val();

    if(!transactionType){
      showWarning('Please select transaction type');
      return;
    }

    const amount = $('#editAmount').val();
    if(!amount || amount <= 0){
      showWarning('Please enter a valid amount');
      return;
    }

    const data = {
      _token: '{{ csrf_token() }}',
      description: $('#editDescription').val(),
      amount: amount,
      paymentMethod: 'Bank',
      accountCode: $('#editBankAccount').val() || '',
      category: transactionType
    };

    $.ajax({
      url: `{{ url('/expense/ledger') }}/${id}`,
      method: 'PUT',
      data: data,
      success: function(res){
        if(res.success){
          showSuccess('Transaction updated successfully!');
          $('#editTransactionModal').modal('hide');
          $('#selectExpense').trigger('change');
        } else {
          showError('Failed to update', res.message || '');
        }
      },
      error: function(xhr){
        console.error('Error:', xhr);
        showError('Failed to update', xhr.responseJSON?.message || '');
      }
    });
  });

  $(document).on('click', '.btnDeleteLedger', function(){
    const id = $(this).data('id');

    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: `{{ url('/expense/ledger') }}/${id}`,
          method: 'DELETE',
          data: {_token: '{{ csrf_token() }}'},
          success: function(res){
            if(res.success){
              showSuccess('Transaction deleted successfully!');
              $('#selectExpense').trigger('change');
            } else {
              showError('Failed to delete', res.message || '');
            }
          },
          error: function(xhr){
            console.error('Error:', xhr);
            showError('Failed to delete', xhr.responseJSON?.message || '');
          }
        });
      }
    });
  });

  $('#btnDeleteExpense').on('click', function(){
    const expenseCode = $('#selectExpense').val();
    if(!expenseCode){
      showWarning('Please select an expense to delete');
      return;
    }

    const expenseName = $('#expenseName').val();

    Swal.fire({
      title: 'Delete Expense?',
      html: `Are you sure you want to delete <strong>${expenseName}</strong>?<br><small class="text-danger">Note: Cannot delete if transactions exist</small>`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: `{{ url('/expense') }}/${expenseCode}`,
          method: 'DELETE',
          data: {_token: '{{ csrf_token() }}'},
          success: function(res){
            if(res.success){
              showSuccess('Expense deleted successfully!');
              $('#selectExpense option[value="' + expenseCode + '"]').remove();
              $('#selectExpense').val('').trigger('change');
              setExpenseInfo('', '', '');
              $('#expenseBalanceDisplay, #expenseAfterTxnWrap').hide();
              $('#expenseTable tbody').html('<tr><td colspan="9" class="text-center text-muted py-5"><i class="bi bi-inbox display-4 d-block mb-2 text-secondary"></i><p class="mb-0">Select an expense to view transactions</p></td></tr>');
              $('#recordCount').text('0 records');
            } else {
              showError('Failed to delete', res.message || '');
            }
          },
          error: function(xhr){
            console.error('Error:', xhr);
            const msg = xhr.responseJSON?.message || 'Failed to delete expense';
            showError('Delete Failed', msg);
          }
        });
      }
    });
  });
});
</script>

@endsection
