@extends('layout.app')

@section('title', 'Bank Transaction')

@section('content')
<style>
    .bank-card-head {
        background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
        color: #fff;
    }
    #bankTxnBankDropdown.dropdown-menu {
        max-height: 300px;
        overflow-y: auto;
        width: 100%;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-top: 4px;
    }
    #bankTxnBankDropdown .dropdown-item.active,
    #bankTxnBankDropdown .dropdown-item:hover {
        background: linear-gradient(135deg, #667eea 0%, #5568d3 100%);
        color: #fff;
    }
    #bankTxnBankDropdown .dropdown-item:hover .text-muted,
    #bankTxnBankDropdown .dropdown-item.active .text-muted {
        color: rgba(255,255,255,0.9) !important;
    }
</style>

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0"><i class="bi bi-bank text-primary me-2"></i>Bank Transaction</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('bank.ledger') }}">Bank</a></li>
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
                <div class="card-header bank-card-head py-2">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Record Bank Transaction</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('bank.account.transaction.store') }}" method="POST" id="bankTxnForm">
                        @csrf
                        <div class="row g-2 mb-3">
                            <div class="col-md-6 col-lg-4" style="max-width: 24rem;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-semibold mb-0">
                                        <i class="bi bi-bank text-primary me-1"></i>Bank
                                    </label>
                                    <small id="bankTxnBalanceDisplay" class="text-muted" style="display: none;">
                                        <strong id="bankTxnBalanceValue" class="text-primary">0.00</strong>
                                        <span id="bankTxnAfterWrap" class="ms-2" style="display: none;">
                                            <i class="bi bi-arrow-right-short text-muted"></i>
                                            <strong id="bankTxnAfterValue" class="text-info">0.00</strong>
                                        </span>
                                    </small>
                                </div>
                                <div class="position-relative">
                                    <input type="text" class="form-control form-control-sm" id="bankTxnBankSearch" placeholder="Type to search bank..." autocomplete="off">
                                    <input type="hidden" name="bankAccount" id="bankTxnBankCode" value="{{ old('bankAccount') }}">
                                    <select id="bankTxnBankSelect" class="form-select form-select-sm d-none">
                                        <option value="">Select Bank</option>
                                        @foreach($bankAccounts as $bank)
                                            <option value="{{ $bank->bankAccount }}"
                                                data-name="{{ $bank->bankName }}"
                                                data-branch="{{ $bank->branchCode ?? '' }}">
                                                {{ $bank->bankName }} - {{ $bank->bankAccount }}@if($bank->branchCode) ({{ $bank->branchCode }})@endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="bankTxnBankDropdown" class="dropdown-menu" style="display: none;"></div>
                                </div>
                                @error('bankAccount')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold mb-1">Date</label>
                                <input type="date" name="entry_date" class="form-control form-control-sm" value="{{ old('entry_date', now()->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold mb-1">Description</label>
                                <input type="text" name="description" class="form-control form-control-sm" value="{{ old('description') }}" placeholder="Enter description" required maxlength="500">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold mb-1">Amount</label>
                                <input type="number" step="0.01" min="0.01" name="amount" id="bankTxnAmount" class="form-control form-control-sm" value="{{ old('amount') }}" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold mb-1">Category</label>
                                <select name="entryType" id="bankTxnEntryType" class="form-select form-select-sm" required>
                                    <option value="debit" @selected(old('entryType') === 'debit')>Debit</option>
                                    <option value="credit" @selected(old('entryType', 'credit') === 'credit')>Credit</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm" id="btnBankTxnSave">
                                    <i class="bi bi-save me-1"></i>Save
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bank-card-head d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
                    <h5 class="mb-0"><i class="bi bi-table me-2"></i>Bank Transactions</h5>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2 mb-0">
                            <label class="small mb-0" for="bankTxnPerPage">Rows</label>
                            <select id="bankTxnPerPage" class="form-select form-select-sm" style="width: 5rem;" aria-label="Rows per page">
                                @foreach ([10, 25, 50, 100] as $size)
                                    <option value="{{ $size }}" @selected((int) request('per_page', 25) === $size)>{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>
                        <a href="{{ route('bank.ledger') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-journal-text me-1"></i>Bank Ledger
                    </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th>Date</th>
                                    <th>Bank</th>
                                    <th>Description</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Credit</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="bankTxnTableBody">
                                @forelse($transactions as $entry)
                                    <tr>
                                        <td>{{ $entry->entry_date }}</td>
                                        <td>{{ $entry->bankName }} — {{ $entry->bankCode }}</td>
                                        <td>{{ $entry->description }}</td>
                                        <td class="text-end">{{ (float) $entry->debit > 0 ? number_format($entry->debit, 2) : '-' }}</td>
                                        <td class="text-end">{{ (float) $entry->credit > 0 ? number_format($entry->credit, 2) : '-' }}</td>
                                        <td class="text-center text-nowrap">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit-bank-txn py-0 px-2" data-id="{{ $entry->id }}" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-bank-txn py-0 px-2" data-id="{{ $entry->id }}" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No bank transactions yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div id="bankTxnPager">
                        @include('partials.tablePager', ['pager' => $transactions])
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="editBankTxnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editBankTxnForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header py-2">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-1"></i>Edit Bank Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small mb-0 fw-semibold">Bank</label>
                        <input type="text" class="form-control form-control-sm bg-light" id="edit_bankLabel" readonly tabindex="-1">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-0 fw-semibold">Date</label>
                        <input type="date" name="entry_date" id="edit_entry_date" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small mb-0 fw-semibold">Description</label>
                        <input type="text" name="description" id="edit_description" class="form-control form-control-sm" required maxlength="500">
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small mb-0 fw-semibold">Amount</label>
                            <input type="number" step="0.01" min="0.01" name="amount" id="edit_amount" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-0 fw-semibold">Category</label>
                            <select name="entryType" id="edit_entryType" class="form-select form-select-sm" required>
                                <option value="debit">Debit</option>
                                <option value="credit">Credit</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check2 me-1"></i>Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Bank Transaction',
        text: @json(session('success')),
        confirmButtonText: 'OK',
        confirmButtonColor: '#0d6efd',
        allowOutsideClick: false
    });
    @endif

    const $bankSearch = $('#bankTxnBankSearch');
    const $bankSelect = $('#bankTxnBankSelect');
    const $bankDropdown = $('#bankTxnBankDropdown');
    const bankBalanceUrl = @json(url('/api/bank-balance'));
    let allBanks = [];
    let selectedBankBalance = 0;

    $bankSelect.find('option').each(function () {
        if ($(this).val()) {
            allBanks.push({
                code: $(this).val(),
                name: $(this).data('name'),
                branch: $(this).data('branch') || '',
            });
        }
    });

    function fmtRs(n) {
        return (Number(n || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function projectBankBalance(current, amount, entryType) {
        const amt = parseFloat(amount) || 0;
        if (amt <= 0) return null;
        return entryType === 'debit' ? current + amt : current - amt;
    }

    function updateProjectedBankBalance() {
        if (!$('#bankTxnBalanceDisplay').is(':visible')) {
            $('#bankTxnAfterWrap').hide();
            return;
        }
        const amount = $('#bankTxnAmount').val();
        const entryType = $('#bankTxnEntryType').val() || 'debit';
        const after = projectBankBalance(selectedBankBalance, amount, entryType);
        if (after === null) {
            $('#bankTxnAfterWrap').hide();
            return;
        }
        $('#bankTxnAfterValue')
            .text(fmtRs(after))
            .removeClass('text-info text-danger text-primary')
            .addClass(after < 0 ? 'text-danger' : 'text-info');
        $('#bankTxnAfterWrap').show();
    }

    function loadBankBalance(code) {
        if (!code) {
            selectedBankBalance = 0;
            $('#bankTxnBalanceDisplay, #bankTxnAfterWrap').hide();
            return;
        }
        $.get(bankBalanceUrl + '/' + encodeURIComponent(code))
            .done(function (res) {
                if (res.success) {
                    selectedBankBalance = parseFloat(res.balance ?? res.current_balance ?? 0) || 0;
                    $('#bankTxnBalanceValue').text(fmtRs(selectedBankBalance));
                    $('#bankTxnBalanceDisplay').show();
                    updateProjectedBankBalance();
                } else {
                    selectedBankBalance = 0;
                    $('#bankTxnBalanceDisplay, #bankTxnAfterWrap').hide();
                }
            })
            .fail(function () {
                selectedBankBalance = 0;
                $('#bankTxnBalanceDisplay, #bankTxnAfterWrap').hide();
            });
    }

    $('#bankTxnAmount, #bankTxnEntryType').on('input change', updateProjectedBankBalance);

    function filterBanks(term) {
        const t = (term || '').toLowerCase().trim();
        if (!t) {
            $bankDropdown.hide();
            return;
        }
        const filtered = allBanks.filter(b =>
            (b.name && b.name.toLowerCase().includes(t)) ||
            (b.branch && String(b.branch).toLowerCase().includes(t)) ||
            (b.code && String(b.code).toLowerCase().includes(t))
        );
        if (!filtered.length) {
            $bankDropdown.hide();
            return;
        }
        let html = '';
        filtered.slice(0, 10).forEach((b, idx) => {
            const sub = b.branch ? 'Branch: ' + b.branch : 'Account: ' + b.code;
            html += '<a class="dropdown-item ' + (idx === 0 ? 'active' : '') + '" href="javascript:void(0);" data-code="' + b.code + '">' +
                '<div><strong>' + b.name + '</strong><div class="text-muted" style="font-size:0.75rem;">' + sub + '</div></div>' +
                '<span class="badge bg-info">' + b.code + '</span></a>';
        });
        $bankDropdown.html(html).show();
    }

    function selectBank(code) {
        const $opt = $bankSelect.find('option[value="' + code + '"]');
        if ($opt.length) {
            $bankSelect.val(code);
            $('#bankTxnBankCode').val(code);
            $bankSearch.val($opt.text().trim());
        }
        $bankDropdown.hide();
        loadBankBalance(code);
        loadTransactions(1);
    }

    function bankTxnPopup(icon, text) {
        return Swal.fire({
            icon: icon,
            title: 'Bank Transaction',
            text: text,
            confirmButtonText: 'OK',
            confirmButtonColor: icon === 'error' || icon === 'warning' ? '#dc3545' : '#0d6efd',
            allowOutsideClick: false
        });
    }

    function escHtml(value) {
        return $('<div>').text(value == null ? '' : value).html();
    }

    function moneyCell(value) {
        const amount = Number(value) || 0;
        if (amount <= 0) return '-';
        return amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    let listPage = 1;

    function renderTransactions(rows, meta) {
        const $body = $('#bankTxnTableBody');
        if (!rows.length) {
            $body.html('<tr><td colspan="6" class="text-center text-muted py-4">No bank transactions yet.</td></tr>');
        } else {
            $body.html(rows.map(function (entry) {
                const date = String(entry.entry_date || '').slice(0, 10);
                return '<tr>' +
                    '<td>' + escHtml(date) + '</td>' +
                    '<td>' + escHtml(entry.bankName) + ' — ' + escHtml(entry.bankCode) + '</td>' +
                    '<td>' + escHtml(entry.description) + '</td>' +
                    '<td class="text-end">' + moneyCell(entry.debit) + '</td>' +
                    '<td class="text-end">' + moneyCell(entry.credit) + '</td>' +
                    '<td class="text-center text-nowrap">' +
                        '<button type="button" class="btn btn-sm btn-outline-primary btn-edit-bank-txn py-0 px-2" data-id="' + entry.id + '" title="Edit"><i class="bi bi-pencil"></i></button> ' +
                        '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-bank-txn py-0 px-2" data-id="' + entry.id + '" title="Delete"><i class="bi bi-trash"></i></button>' +
                    '</td></tr>';
            }).join(''));
        }

        const $pager = $('#bankTxnPager');
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
            links + '</div>'
        );
    }

    function loadTransactions(page) {
        listPage = page || 1;
        const params = new URLSearchParams({
            page: String(listPage),
            per_page: String($('#bankTxnPerPage').val() || 25)
        });
        const code = $('#bankTxnBankCode').val();
        if (code) params.set('bankAccount', code);
        return $.get(@json(route('bank.account.transaction.list')) + '?' + params.toString())
            .done(function (res) {
                const rows = res.data || [];
                const meta = res.meta || {};
                if (!rows.length && meta.current_page > 1) {
                    loadTransactions(meta.last_page || 1);
                    return;
                }
                renderTransactions(rows, meta);
            });
    }

    $('#bankTxnPerPage').on('change', function () {
        loadTransactions(1);
    });

    $(document).on('click', '#bankTxnPager a[data-page]', function (e) {
        e.preventDefault();
        loadTransactions(parseInt($(this).data('page'), 10) || 1);
    });

    $('#bankTxnForm').on('submit', function (e) {
        e.preventDefault();
        const code = $('#bankTxnBankCode').val();
        if (!code) {
            bankTxnPopup('warning', 'Please select a bank from the search list.');
            $bankSearch.focus();
            return;
        }
        const $btn = $('#btnBankTxnSave').prop('disabled', true);
        $.ajax({
            url: this.action,
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function (res) {
            bankTxnPopup('success', res.message || 'Bank transaction saved successfully.');
            $('#bankTxnForm [name="description"]').val('');
            $('#bankTxnAmount').val('');
            loadTransactions(1);
            $('#bankTxnForm [name="description"]').focus();
        }).fail(function (xhr) {
            const errors = xhr.responseJSON && xhr.responseJSON.errors;
            const first = errors ? Object.values(errors)[0][0] : (xhr.responseJSON && xhr.responseJSON.message);
            bankTxnPopup('error', first || 'Could not save the transaction.');
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });

    $bankSelect.on('change', function () {
        const code = $(this).val();
        $('#bankTxnBankCode').val(code || '');
        loadBankBalance(code);
    });

    const initialBank = $('#bankTxnBankCode').val();
    if (initialBank) {
        selectBank(initialBank);
    }

    function focusNextBankField(current) {
        const fields = Array.from(document.querySelectorAll('#bankTxnForm input, #bankTxnForm select, #btnBankTxnSave')).filter(function (el) {
            if (el.disabled || el.readOnly) return false;
            if ((el.type || '') === 'hidden') return false;
            if (el.closest('.d-none, .modal')) return false;
            return el.offsetParent !== null || el === document.activeElement;
        });
        const index = fields.indexOf(current);
        const next = index >= 0 ? fields[index + 1] : null;
        const target = next || document.getElementById('btnBankTxnSave');
        if (!target) return;
        target.focus();
        if (target.tagName === 'INPUT' && typeof target.select === 'function') target.select();
    }

    function moveBankDropdownActive(direction) {
        const $items = $bankDropdown.find('.dropdown-item[data-code]');
        if (!$items.length) return;
        let index = $items.index($items.filter('.active'));
        if (direction > 0) index = index < 0 ? 0 : Math.min(index + 1, $items.length - 1);
        else index = index <= 0 ? 0 : index - 1;
        $items.removeClass('active').eq(index).addClass('active');
    }

    $bankSearch.on('keydown', function (e) {
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            if (!$bankDropdown.is(':visible')) filterBanks($(this).val());
            moveBankDropdownActive(e.key === 'ArrowDown' ? 1 : -1);
            return;
        }
        if (e.key === 'Escape') {
            $bankDropdown.hide();
            return;
        }
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const $selected = $bankDropdown.is(':visible') ? $bankDropdown.find('.dropdown-item.active[data-code]') : $();
        if ($selected.length) selectBank($selected.data('code'));
        if ($('#bankTxnBankCode').val()) {
            $bankDropdown.hide();
            focusNextBankField(this);
        }
    });

    $('#bankTxnForm').on('keydown', function (e) {
        if (e.key !== 'Enter') return;
        const el = e.target;
        if (!el || el.id === 'bankTxnBankSearch' || el.id === 'btnBankTxnSave') return;
        if (el.tagName !== 'INPUT' && el.tagName !== 'SELECT') return;
        e.preventDefault();
        focusNextBankField(el);
    });

    $bankSearch.on('input', function () {
        filterBanks($(this).val());
        if (!$(this).val().trim()) {
            $bankSelect.val('');
            $('#bankTxnBankCode').val('');
            loadBankBalance('');
            loadTransactions(1);
        }
    });
    $bankSearch.on('focus', function () { if ($(this).val()) filterBanks($(this).val()); });

    $bankDropdown.on('click', '.dropdown-item', function (e) {
        e.preventDefault();
        if ($(this).data('code')) selectBank($(this).data('code'));
    });

    $(document).on('click', function () { $bankDropdown.hide(); });

    const editModal = new bootstrap.Modal(document.getElementById('editBankTxnModal'));
    const dataUrlTemplate = @json(route('bank.account.transaction.data', ['id' => '__ID__']));
    const updateUrlTemplate = @json(route('bank.account.transaction.update', ['id' => '__ID__']));

    $(document).on('click', '.btn-edit-bank-txn', function () {
        const id = $(this).data('id');
        $.get(dataUrlTemplate.replace('__ID__', id))
            .done(function (res) {
                if (!res.success) {
                    bankTxnPopup('warning', res.message || 'This entry cannot be edited.');
                    return;
                }
                const t = res.transaction;
                $('#edit_bankLabel').val(t.bankLabel || '');
                $('#edit_entry_date').val(String(t.entry_date || '').slice(0, 10));
                $('#edit_description').val(t.description);
                $('#edit_amount').val(t.amount);
                $('#edit_entryType').val(t.entryType);
                $('#editBankTxnForm').attr('action', updateUrlTemplate.replace('__ID__', id));
                editModal.show();
            })
            .fail(function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'This entry cannot be edited.';
                bankTxnPopup('warning', msg);
            });
    });

    $('#editBankTxnForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $(this).find('[type="submit"]').prop('disabled', true);
        $.ajax({
            url: this.action,
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function (res) {
            editModal.hide();
            bankTxnPopup('success', res.message || 'Bank transaction updated successfully.');
            loadTransactions(listPage);
            }).fail(function (xhr) {
            const errors = xhr.responseJSON && xhr.responseJSON.errors;
            const first = errors ? Object.values(errors)[0][0] : (xhr.responseJSON && xhr.responseJSON.message);
            bankTxnPopup(xhr.status === 403 ? 'warning' : 'error', first || 'Could not update the transaction.');
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });

    $(document).on('click', '.btn-delete-bank-txn', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then(function (result) {
            if (!result.isConfirmed) return;
        $.ajax({
            url: updateUrlTemplate.replace('__ID__', id),
            type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            }).done(function (res) {
                bankTxnPopup('success', res.message || 'Bank transaction deleted.');
                loadTransactions(listPage);
            }).fail(function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Could not delete the transaction.';
                bankTxnPopup(xhr.status === 403 ? 'warning' : 'error', msg);
            });
        });
    });
});
</script>
@endsection
