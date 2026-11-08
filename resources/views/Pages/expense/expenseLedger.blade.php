@extends('layout.app')

@section('title', 'Expense Ledger')

@section('content')

<style>
  .ledger-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
  }
  .summary-pill {
    background: #fff;
    border: 1px solid #d5dde8;
    border-left: 4px solid #6c757d;
    border-radius: 4px;
    padding: 0.65rem 0.85rem;
    height: 100%;
  }
  .summary-pill.debit { border-left-color: #dc3545; background: #fdf0f1; }
  .summary-pill.credit { border-left-color: #198754; background: #eefaf2; }
  .summary-pill.balance { border-left-color: #0f2747; background: #f4f7fb; }
  .summary-pill.txns { border-left-color: #1a4a8a; background: #f4f7fb; }
  .summary-pill .lbl {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: #6b7c8d;
    margin-bottom: 0.15rem;
  }
  .summary-pill .val {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f2747;
    margin: 0;
  }
  .summary-pill.debit .val { color: #dc3545; }
  .summary-pill.credit .val { color: #198754; }
  .chart-container {
    position: relative;
    height: 260px;
  }
  #filterCard .select2-container { width: 100% !important; }
  #filterCard .select2-container .select2-selection--single {
    height: 31px !important;
    border: 1px solid #ced4da;
  }
  #filterCard .select2-dropdown {
    z-index: 1060;
  }
  .filter-badge {
    display: inline-block;
    padding: 4px 10px;
    background: #f4f7fb;
    border: 1px solid #d5dde8;
    border-radius: 4px;
    font-size: 0.75rem;
    margin-right: 6px;
    margin-bottom: 4px;
    color: #0f2747;
    font-weight: 600;
  }
  @media print {
    .app-sidebar, .app-header, .app-content-header, #filterCard, .btn, button, .no-print,
    .summary-row, .chart-row, #activeFilters { display: none !important; }
    body, .app-main { margin: 0; padding: 0; }
    .app-content { padding: 0 !important; }
    .card { box-shadow: none !important; border: none !important; }
    .card-header { display: none !important; }
    @page { margin: 5mm; size: A4 landscape; }
  }
</style>

<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-6">
          <h3 class="mb-0">
            <i class="bi bi-journal-text text-primary me-2"></i>Expense Ledger
          </h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('expense.transactionPage') }}">Expense Transaction</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ledger</li>
          </ol>
        </div>
      </div>
      <hr class="mb-0" style="border-top: 2px solid #0d6efd;">
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      {{-- Filters --}}
      <div class="card shadow-sm mb-3" id="filterCard">
        <div class="card-header ledger-card-head py-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
          <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filters</h5>
          <a href="{{ route('expense.transactionPage') }}" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left-right me-1"></i>Transaction
          </a>
        </div>
        <div class="card-body">
          <div class="row g-2 align-items-end">
            <div class="col-md-3">
              <label class="form-label small fw-semibold mb-1">
                <i class="bi bi-wallet2 text-primary me-1"></i>Expense Code
              </label>
              <select class="form-select form-select-sm select2" id="filterExpenseCode" style="width:100%;">
                <option value="">All Expenses</option>
                @foreach($expenses as $expense)
                  <option value="{{ $expense->expenseCode }}">{{ $expense->expenseCode }} - {{ $expense->expenseName }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-semibold mb-1">From Date</label>
              <input type="date" class="form-control form-control-sm" id="fromDate">
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-semibold mb-1">To Date</label>
              <input type="date" class="form-control form-control-sm" id="toDate">
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-semibold mb-1">Category</label>
              <select class="form-select form-select-sm" id="filterCategory">
                <option value="">All Categories</option>
                <option value="Debit">Debit</option>
                <option value="Credit">Credit</option>
              </select>
            </div>
            <div class="col-md-3 d-flex flex-wrap gap-1 justify-content-md-end">
              <button type="button" class="btn btn-secondary btn-sm" id="btnClearFilter" title="Clear Filters">
                <i class="bi bi-arrow-clockwise me-1"></i>Clear
              </button>
              <button type="button" class="btn btn-outline-primary btn-sm" id="btnPrintReport" title="Print Report">
                <i class="bi bi-printer me-1"></i>Print
              </button>
            </div>
          </div>
        </div>
      </div>

      {{-- Summary (after filters) --}}
      <div class="row g-2 mb-2 summary-row">
        <div class="col-6 col-md-3">
          <div class="summary-pill debit">
            <div class="lbl">Total Debits</div>
            <p class="val" id="totalDebits">0</p>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="summary-pill credit">
            <div class="lbl">Total Credits</div>
            <p class="val" id="totalCredits">0</p>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="summary-pill balance">
            <div class="lbl">Net Balance</div>
            <p class="val" id="netBalance">0</p>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="summary-pill txns">
            <div class="lbl">Total Transactions</div>
            <p class="val" id="totalTransactions">0</p>
          </div>
        </div>
      </div>

      {{-- Active filters (after summary boxes) --}}
      <div id="activeFilters" class="mb-3"></div>

      {{-- Ledger Table --}}
      <div class="card shadow-sm mb-4">
        <div class="card-header ledger-card-head d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
          <h5 class="mb-0">
            <i class="bi bi-table me-2"></i>Expense Ledger Transactions
          </h5>
          <div class="d-flex flex-wrap align-items-center gap-2">
            <label class="mb-0 small text-white-50 d-flex align-items-center gap-1">
              Rows
              <select id="pageSizeSelect" class="form-select form-select-sm" style="width: auto; min-width: 4.5rem;">
                <option value="10">10</option>
                <option value="15" selected>15</option>
                <option value="25">25</option>
                <option value="50">50</option>
              </select>
            </label>
            <span class="badge bg-light text-dark" id="recordCount">0 records</span>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0 table-sm" id="expenseLedgerTable" style="font-size: 0.875rem;">
              <thead class="table-light">
                <tr>
                  <th class="text-center" style="width: 50px;">#</th>
                  <th style="width: 100px;">Date</th>
                  <th style="width: 160px;">Expense Name</th>
                  <th>Description</th>
                  <th class="text-center" style="width: 90px;">Category</th>
                  <th class="text-end" style="width: 100px;">Amount</th>
                  <th class="text-end" style="width: 100px;">Debit</th>
                  <th class="text-end" style="width: 100px;">Credit</th>
                  <th class="text-end" style="width: 110px;">Balance</th>
                  <th class="text-center no-print" style="width: 100px;">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="10" class="text-center text-muted py-5">
                    <i class="bi bi-inbox display-4 d-block mb-2 text-secondary"></i>
                    <p class="mb-0">Select filters to view transactions</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer bg-light py-2" id="paginationFooter" style="display:none;">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <small class="text-muted" id="pageInfo">Showing 0–0 of 0</small>
            <nav>
              <ul class="pagination pagination-sm mb-0" id="paginationControls"></ul>
            </nav>
          </div>
        </div>
      </div>

      {{-- Charts --}}
      <div class="row g-3 chart-row">
        <div class="col-md-6">
          <div class="card shadow-sm h-100">
            <div class="card-header ledger-card-head py-2">
              <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Debit vs Credit</h5>
            </div>
            <div class="card-body">
              <div class="chart-container">
                <canvas id="debitCreditChart"></canvas>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card shadow-sm h-100">
            <div class="card-header ledger-card-head py-2">
              <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Category Distribution</h5>
            </div>
            <div class="card-body">
              <div class="chart-container">
                <canvas id="categoryChart"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

{{-- Edit Transaction Modal --}}
<div class="modal fade" id="editTransactionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header ledger-card-head py-2">
        <h5 class="modal-title">
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function() {
  $('#filterExpenseCode').select2({
    placeholder: 'Choose Expense',
    allowClear: true,
    width: '100%',
    dropdownParent: $('#filterCard')
  });

  // Default: all history up to today (fromDate empty so older entries stay visible)
  const today = new Date().toISOString().split('T')[0];
  $('#toDate').val(today);
  $('#fromDate').val('');

  function fmt(n) {
    return parseFloat(n || 0).toLocaleString('en-PK', {minimumFractionDigits: 0, maximumFractionDigits: 0});
  }

  let debitCreditChart = null;
  let categoryChart = null;
  let suppressFilterChange = false;
  let ledgerRows = [];
  let currentPage = 1;

  function refreshLedger(updateBadges = true) {
    if (updateBadges) updateActiveFiltersDisplay();
    currentPage = 1;
    loadLedgerData();
  }

  function getPageSize() {
    return parseInt($('#pageSizeSelect').val(), 10) || 15;
  }

  function loadLedgerData() {
    const expenseCode = $('#filterExpenseCode').val();
    const fromDate = $('#fromDate').val();
    const toDate = $('#toDate').val();
    const category = $('#filterCategory').val();

    const params = new URLSearchParams();
    if(expenseCode) params.append('expenseCode', expenseCode);
    if(fromDate) params.append('fromDate', fromDate);
    if(toDate) params.append('toDate', toDate);
    if(category) params.append('category', category);

    $.get(`{{ url('/expense/ledger/report') }}?${params}`)
      .done(res => {
        prepareLedgerData(res.data || []);
        updateCharts(res.data || []);
        renderLedgerPage();
      })
      .fail(err => {
        console.error('Error:', err);
        showError('Failed to load ledger data');
      });
  }

  // Any filter change → refresh data (no page reload)
  $('#filterExpenseCode, #filterCategory').on('change', function () {
    if (suppressFilterChange) return;
    refreshLedger();
  });
  $('#fromDate, #toDate').on('change', function () {
    if (suppressFilterChange) return;
    refreshLedger();
  });

  $('#pageSizeSelect').on('change', function () {
    currentPage = 1;
    renderLedgerPage();
  });

  function prepareLedgerData(data) {
    if (!data || !data.length) {
      ledgerRows = [];
      $('#totalDebits').text('0');
      $('#totalCredits').text('0');
      $('#netBalance').text('0');
      $('#totalTransactions').text('0');
      $('#recordCount').text('0 records');
      return;
    }

    data.sort((a, b) => new Date(b.entryDate) - new Date(a.entryDate));

    let totalDebit = 0;
    let totalCredit = 0;
    let runningBalance = 0;

    data.forEach(row => {
      totalDebit += parseFloat(row.debit || 0);
      totalCredit += parseFloat(row.credit || 0);
    });

    const reversedData = [...data].reverse();
    reversedData.forEach(row => {
      runningBalance += parseFloat(row.debit || 0) - parseFloat(row.credit || 0);
      row.runningBalance = runningBalance;
    });

    ledgerRows = data;
    $('#totalDebits').text(fmt(totalDebit));
    $('#totalCredits').text(fmt(totalCredit));
    $('#netBalance').text(fmt(totalDebit - totalCredit));
    $('#totalTransactions').text(data.length);
    $('#recordCount').text(`${data.length} ${data.length === 1 ? 'record' : 'records'}`);
  }

  function renderLedgerPage() {
    const $tb = $('#expenseLedgerTable tbody').empty();
    const total = ledgerRows.length;
    const pageSize = getPageSize();
    const totalPages = Math.max(1, Math.ceil(total / pageSize));

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    if (!total) {
      $tb.append('<tr><td colspan="10" class="text-center text-muted py-5"><i class="bi bi-inbox display-4 d-block mb-2 text-secondary"></i><p class="mb-0">No transactions found</p></td></tr>');
      $('#paginationFooter').hide();
      $('#paginationControls').empty();
      $('#pageInfo').text('Showing 0–0 of 0');
      return;
    }

    const start = (currentPage - 1) * pageSize;
    const end = Math.min(start + pageSize, total);
    const pageRows = ledgerRows.slice(start, end);

    pageRows.forEach((row, idx) => {
      const date = row.entryDate ? new Date(row.entryDate).toLocaleDateString() : '';
      const balanceClass = row.runningBalance >= 0 ? 'text-danger fw-semibold' : 'text-success fw-semibold';
      const categoryBadge = row.category === 'Debit'
        ? '<span class="badge bg-danger">Debit</span>'
        : '<span class="badge bg-success">Credit</span>';
      const expenseName = row.expenseName || row.expenseCode || '—';

      $tb.append(`
        <tr>
          <td class="text-center">${start + idx + 1}</td>
          <td>${date}</td>
          <td title="${row.expenseCode || ''}">${expenseName}</td>
          <td>${row.description || ''}</td>
          <td class="text-center">${categoryBadge}</td>
          <td class="text-end">${fmt(row.amount)}</td>
          <td class="text-end text-danger fw-semibold">${fmt(row.debit)}</td>
          <td class="text-end text-success fw-semibold">${fmt(row.credit)}</td>
          <td class="text-end ${balanceClass}">${fmt(row.runningBalance)}</td>
          <td class="text-center text-nowrap no-print">
            <button type="button" class="btn btn-sm btn-outline-primary btnEditLedger py-0 px-2 me-1" title="Edit" data-id="${row.id}">
              <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger btnDeleteLedger py-0 px-2" title="Delete" data-id="${row.id}">
              <i class="bi bi-trash"></i>
            </button>
          </td>
        </tr>
      `);
    });

    $('#pageInfo').text(`Showing ${start + 1}–${end} of ${total}`);
    renderPaginationControls(totalPages);
    $('#paginationFooter').show();
  }

  function renderPaginationControls(totalPages) {
    const $ul = $('#paginationControls').empty();
    if (totalPages <= 1) return;

    const addItem = (label, page, disabled, active) => {
      const cls = `page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}`;
      $ul.append(`<li class="${cls}"><a class="page-link" href="#" data-page="${page}">${label}</a></li>`);
    };

    addItem('«', currentPage - 1, currentPage <= 1, false);

    const windowSize = 5;
    let from = Math.max(1, currentPage - Math.floor(windowSize / 2));
    let to = Math.min(totalPages, from + windowSize - 1);
    from = Math.max(1, to - windowSize + 1);

    if (from > 1) {
      addItem('1', 1, false, currentPage === 1);
      if (from > 2) $ul.append('<li class="page-item disabled"><span class="page-link">…</span></li>');
    }

    for (let p = from; p <= to; p++) {
      addItem(String(p), p, false, p === currentPage);
    }

    if (to < totalPages) {
      if (to < totalPages - 1) $ul.append('<li class="page-item disabled"><span class="page-link">…</span></li>');
      addItem(String(totalPages), totalPages, false, currentPage === totalPages);
    }

    addItem('»', currentPage + 1, currentPage >= totalPages, false);
  }

  $(document).on('click', '#paginationControls a.page-link', function (e) {
    e.preventDefault();
    const $li = $(this).closest('.page-item');
    if ($li.hasClass('disabled') || $li.hasClass('active')) return;
    const page = parseInt($(this).data('page'), 10);
    if (!page || page === currentPage) return;
    currentPage = page;
    renderLedgerPage();
  });

  function updateCharts(data) {
    if(!data || !data.length) {
      if(debitCreditChart) debitCreditChart.destroy();
      if(categoryChart) categoryChart.destroy();
      return;
    }

    let totalDebit = 0;
    let totalCredit = 0;
    let debitCount = 0;
    let creditCount = 0;

    data.forEach(row => {
      const debit = parseFloat(row.debit || 0);
      const credit = parseFloat(row.credit || 0);
      totalDebit += debit;
      totalCredit += credit;
      if(debit > 0) debitCount++;
      if(credit > 0) creditCount++;
    });

    const debitCreditCtx = document.getElementById('debitCreditChart').getContext('2d');
    if(debitCreditChart) debitCreditChart.destroy();
    debitCreditChart = new Chart(debitCreditCtx, {
      type: 'bar',
      data: {
        labels: ['Debit', 'Credit'],
        datasets: [{
          label: 'Amount',
          data: [totalDebit, totalCredit],
          backgroundColor: ['#dc3545', '#198754'],
          borderColor: ['#bd2130', '#146c43'],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
      }
    });

    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    if(categoryChart) categoryChart.destroy();
    categoryChart = new Chart(categoryCtx, {
      type: 'doughnut',
      data: {
        labels: ['Debit', 'Credit'],
        datasets: [{
          data: [debitCount, creditCount],
          backgroundColor: ['#dc3545', '#198754'],
          borderColor: ['#fff', '#fff'],
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
      }
    });
  }

  $('#btnClearFilter').on('click', function(){
    suppressFilterChange = true;
    $('#filterExpenseCode').val(null).trigger('change');
    $('#filterCategory').val('');
    $('#fromDate').val('');
    $('#toDate').val(new Date().toISOString().split('T')[0]);
    suppressFilterChange = false;
    refreshLedger();
  });

  function updateActiveFiltersDisplay() {
    const expenseCode = $('#filterExpenseCode').val();
    const category = $('#filterCategory').val();
    const fromDate = $('#fromDate').val();
    const toDate = $('#toDate').val();

    let html = '';
    if(expenseCode) {
      const label = $('#filterExpenseCode option:selected').text();
      html += `<span class="filter-badge">Expense: ${label}</span>`;
    }
    if(category) {
      html += `<span class="filter-badge">Category: ${category}</span>`;
    }
    if(fromDate) {
      html += `<span class="filter-badge">From: ${new Date(fromDate).toLocaleDateString()}</span>`;
    }
    if(toDate) {
      html += `<span class="filter-badge">To: ${new Date(toDate).toLocaleDateString()}</span>`;
    }

    $('#activeFilters').html(html || '<small class="text-muted">No filters applied</small>');
  }

  $('#btnPrintReport').on('click', function() {
    const expenseCode = $('#filterExpenseCode').val();
    const fromDate = $('#fromDate').val();
    const toDate = $('#toDate').val();
    const category = $('#filterCategory').val();

    let url = '{{ route("expense.report.print") }}?';
    if(expenseCode) url += `expenseCode=${expenseCode}&`;
    if(fromDate) url += `fromDate=${fromDate}&`;
    if(toDate) url += `toDate=${toDate}&`;
    if(category) url += `category=${category}&`;

    window.open(url, '_blank');
  });

  $(document).on('click', '.btnEditLedger', function () {
    const id = $(this).data('id');
    const row = ledgerRows.find(r => String(r.id) === String(id));
    if (!row) {
      showError('Transaction not found');
      return;
    }
    $('#editTransactionId').val(row.id);
    $('#editDescription').val(row.description || '');
    $('#editAmount').val(row.amount);
    $('#editBankAccount').val(row.accountCode || '');
    $('#editTransactionType').val(row.category || '');
    $('#editTransactionModal').modal('show');
  });

  $('#btnUpdateTransaction').on('click', function () {
    const id = $('#editTransactionId').val();
    const transactionType = $('#editTransactionType').val();
    const amount = $('#editAmount').val();

    if (!transactionType) {
      showWarning('Please select transaction type');
      return;
    }
    if (!amount || amount <= 0) {
      showWarning('Please enter a valid amount');
      return;
    }

    $.ajax({
      url: `{{ url('/expense/ledger') }}/${id}`,
      method: 'PUT',
      data: {
        _token: '{{ csrf_token() }}',
        description: $('#editDescription').val(),
        amount: amount,
        paymentMethod: 'Bank',
        accountCode: $('#editBankAccount').val() || '',
        category: transactionType
      },
      success: function (res) {
        if (res.success) {
          showSuccess('Transaction updated successfully!');
          $('#editTransactionModal').modal('hide');
          refreshLedger(false);
        } else {
          showError('Failed to update', res.message || '');
        }
      },
      error: function (xhr) {
        showError('Failed to update', xhr.responseJSON?.message || '');
      }
    });
  });

  $(document).on('click', '.btnDeleteLedger', function () {
    const id = $(this).data('id');

    Swal.fire({
      title: 'Delete Transaction?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (!result.isConfirmed) return;

      $.ajax({
        url: `{{ url('/expense/ledger') }}/${id}`,
        method: 'DELETE',
        data: { _token: '{{ csrf_token() }}' },
        success: function (res) {
          if (res.success) {
            showSuccess('Transaction deleted successfully!');
            refreshLedger(false);
          } else {
            showError('Failed to delete', res.message || '');
          }
        },
        error: function (xhr) {
          showError('Failed to delete', xhr.responseJSON?.message || '');
        }
      });
    });
  });

  refreshLedger();
});
</script>

@endsection
