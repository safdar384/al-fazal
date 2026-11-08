@extends('layout.app')

@section('title', 'Labour Account Transaction')

@section('content')
<style>
    .labour-card-head {
        background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
        color: #fff;
    }
    /* Second row: one line, equal control heights (with or without agent fields) */
    .labour-txn-second-row {
        display: flex;
        flex-wrap: nowrap;
        align-items: flex-end;
        gap: 0.5rem;
        overflow-x: auto;
        overflow-y: visible;
        padding-bottom: 4px;
        -webkit-overflow-scrolling: touch;
    }
    .labour-txn-second-row .labour-txn-field {
        flex: 0 0 auto;
        min-width: 7rem;
    }
    .labour-txn-second-row .labour-txn-field--wide {
        min-width: 10rem;
    }
    .labour-txn-second-row .labour-txn-field--btn {
        min-width: 8.5rem;
    }
    .labour-txn-second-row .labour-txn-label-row {
        min-height: 1.375rem;
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        width: 100%;
    }
    .labour-txn-second-row .form-control-sm,
    .labour-txn-second-row .form-select-sm {
        min-height: calc(1.5em + 0.5rem + 2px);
    }
    .labour-txn-ajent-inner {
        display: flex;
        flex-wrap: nowrap;
        align-items: flex-end;
        gap: 0.35rem;
    }
    .labour-txn-ajent-inner .ajent-col {
        flex: 0 0 auto;
    }
    .labour-txn-ajent-inner .ajent-col--comm {
        width: 5rem;
        min-width: 5rem;
        max-width: 5rem;
    }
    .labour-txn-ajent-inner .ajent-col--comm .form-control {
        padding-left: 0.375rem;
        padding-right: 0.375rem;
        text-align: center;
    }
    #bankDropdown.dropdown-menu,
    #labourDropdown.dropdown-menu {
        max-height: 300px;
        overflow-y: auto;
        width: 100%;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-top: 4px;
    }
    #bankDropdown .dropdown-item.active,
    #bankDropdown .dropdown-item:hover,
    #labourDropdown .dropdown-item.active,
    #labourDropdown .dropdown-item:hover {
        background: linear-gradient(135deg, #1a4a8a 0%, #0f2747 100%);
        color: #fff;
    }
    #bankDropdown .dropdown-item:hover .text-muted,
    #labourDropdown .dropdown-item:hover .text-muted,
    #bankDropdown .dropdown-item.active .text-muted,
    #labourDropdown .dropdown-item.active .text-muted {
        color: rgba(255, 255, 255, 0.9) !important;
    }
</style>

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0"><i class="bi bi-person-workspace text-primary me-2"></i>Labour Account Transaction</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item">Labour Accounts</li>
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
                <div class="card-header labour-card-head py-2">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Record Labour Transaction</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('labour.account.store') }}" method="POST" id="labourAccountForm">
                        @csrf
                        <div class="row g-2 align-items-end mb-3">
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-semibold mb-0">
                                        <i class="bi bi-person-badge text-primary me-1"></i>Labour
                                    </label>
                                    <small id="labourBalanceDisplay" class="text-muted" style="display: none;">
                                        <strong id="labourBalanceValue" class="text-primary">0.00</strong>
                                        <span id="labourAfterTxnWrap" class="ms-2" style="display: none;">
                                            <i class="bi bi-arrow-right-short text-muted"></i>
                                            <strong id="labourAfterTxnValue" class="text-info">0.00</strong>
                                        </span>
                                    </small>
                                </div>
                                <div class="position-relative">
                                    <input type="text" class="form-control form-control-sm" id="labourSearch" placeholder="Type to search labour..." autocomplete="off">
                                    <select name="labour_id" id="labour_id" class="form-select form-select-sm d-none" required>
                                        <option value="">Select Labour</option>
                                        @foreach($labours as $labour)
                                            <option value="{{ $labour->id }}"
                                                data-code="{{ $labour->labourCode }}"
                                                data-name="{{ $labour->labourName }}"
                                                data-category="{{ $labour->labourCatagory }}">
                                                {{ $labour->labourCode }} - {{ $labour->labourName }} ({{ $labour->labourCatagory }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="labourDropdown" class="dropdown-menu w-100" style="display: none;"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-semibold mb-0">
                                        <i class="bi bi-bank text-success me-1"></i>Bank
                                    </label>
                                    <small id="bankBalanceDisplay" class="text-muted" style="display: none;">
                                        <strong id="bankBalanceValue" class="text-primary">0.00</strong>
                                        <span id="bankAfterTxnWrap" class="ms-2" style="display: none;">
                                            <i class="bi bi-arrow-right-short text-muted"></i>
                                            <strong id="bankAfterTxnValue" class="text-info">0.00</strong>
                                        </span>
                                    </small>
                                </div>
                                <div class="position-relative">
                                    <input type="text" class="form-control form-control-sm" id="bankSearch" placeholder="Type to search bank..." autocomplete="off">
                                    <select name="bankAccount" id="bankAccount" class="form-select form-select-sm d-none">
                                        <option value="">Select Bank</option>
                                        @foreach($bankAccounts as $bank)
                                            <option value="{{ $bank->bankAccount }}"
                                                data-name="{{ $bank->bankName }}"
                                                data-branch="{{ $bank->branchCode ?? '' }}">
                                                {{ $bank->bankName }} - {{ $bank->bankAccount }}@if($bank->branchCode) ({{ $bank->branchCode }})@endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="bankDropdown" class="dropdown-menu w-100" style="display: none;"></div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold mb-1">Date</label>
                                <input type="date" name="transactionDate" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                        </div>

                        {{-- Single line: common fields always visible; only agent block toggles --}}
                        <div class="labour-txn-second-row">
                            <div class="labour-txn-field labour-txn-field--wide">
                                <label class="form-label fw-semibold small labour-txn-label-row">Discription</label>
                                <input type="text" name="description" class="form-control form-control-sm" placeholder="Enter description" required>
                            </div>

                            <div id="ajentOnlyGroup" class="labour-txn-field labour-txn-field--wide d-none">
                                <div class="labour-txn-ajent-inner">
                                    <div class="ajent-col" style="min-width: 5.5rem;">
                                        <label class="form-label fw-semibold small labour-txn-label-row">Market Bal.</label>
                                        <input type="number" step="0.01" min="0" name="marketBalance" id="marketBalance" class="form-control form-control-sm" placeholder="0">
                                    </div>
                                    <div class="ajent-col" style="min-width: 5.5rem;">
                                        <label class="form-label fw-semibold small labour-txn-label-row">Recovery</label>
                                        <input type="number" step="0.01" min="0" name="recovery" id="recovery" class="form-control form-control-sm" placeholder="0">
                                    </div>
                                    <div class="ajent-col ajent-col--comm">
                                        <label class="form-label fw-semibold small labour-txn-label-row text-center d-block">Comm%</label>
                                        <input type="number" step="0.01" min="0" name="commissionPercent" id="commissionPercent" class="form-control form-control-sm" readonly>
                                    </div>
                                </div>
                                <input type="hidden" name="recoveryCommPercent" id="recoveryCommPercent" value="">
                            </div>

                            <div class="labour-txn-field" style="min-width: 8rem;">
                                <div class="labour-txn-label-row justify-content-between gap-2 w-100">
                                    <label class="form-label fw-semibold small mb-0" for="amount">Amount</label>
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" value="1" name="isAdvance" id="isAdvance">
                                        <label class="form-check-label small mb-0" for="isAdvance">Advance</label>
                                    </div>
                                </div>
                                <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control form-control-sm" required>
                            </div>
                            <div class="labour-txn-field" style="min-width: 6.5rem;">
                                <label class="form-label fw-semibold small labour-txn-label-row">Catagory</label>
                                <select name="entryType" id="entryType" class="form-select form-select-sm" required>
                                    <option value="debit">Debit</option>
                                    <option value="credit">Credit</option>
                                </select>
                            </div>
                            <div class="labour-txn-field labour-txn-field--btn">
                                <div class="labour-txn-label-row mb-0" aria-hidden="true">&nbsp;</div>
                                <button type="submit" class="btn btn-primary btn-sm w-100 text-nowrap" id="btnLabourTxnSave">
                                    <i class="bi bi-save me-1"></i>Save
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header labour-card-head d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
                    <h5 class="mb-0"><i class="bi bi-table me-2"></i><span id="labourTxnTableTitle">All Labour Transactions</span></h5>
                    <a href="{{ route('labour.ledger') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-journal-text me-1"></i>Labour Ledger
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="labourAccountTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Labour</th>
                                    <th>Discription</th>
                                    <th>Bank</th>
                                    <th>Catagory</th>
                                    <th>Amount</th>
                                    <th>Advance</th>
                                    <th class="text-center" style="width: 1%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $entry)
                                    <tr>
                                        <td>{{ $entry->transactionDate?->format('Y-m-d') }}</td>
                                        <td>{{ $entry->labourCode }} - {{ $entry->labourName }}</td>
                                        <td>{{ $entry->description }}</td>
                                        <td>{{ $entry->bankAccount ?: '-' }}</td>
                                        <td><span class="badge {{ $entry->entryType === 'debit' ? 'bg-danger' : 'bg-success' }}">{{ ucfirst($entry->entryType) }}</span></td>
                                        <td>Rs. {{ number_format($entry->amount, 2) }}</td>
                                        <td>{{ $entry->isAdvance ? 'Yes' : 'No' }}</td>
                                        <td class="text-center text-nowrap">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit-labour-txn py-0 px-2" data-id="{{ $entry->id }}" title="Edit">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-labour-txn py-0 px-2" data-id="{{ $entry->id }}" title="Delete">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

{{-- Edit transaction modal --}}
<div class="modal fade" id="editLabourTxnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="editLabourTxnForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header py-2">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-1"></i>Edit Labour Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small mb-0 fw-semibold">Labour</label>
                            <input type="text" class="form-control form-control-sm bg-light" id="edit_labourLabel" readonly tabindex="-1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-0 fw-semibold">Date</label>
                            <input type="date" name="transactionDate" id="edit_transactionDate" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small mb-0 fw-semibold">Bank</label>
                            <select name="bankAccount" id="edit_bankAccount" class="form-select form-select-sm">
                                <option value="">Select Bank</option>
                                @foreach($bankAccounts as $bank)
                                    <option value="{{ $bank->bankAccount }}">{{ $bank->bankName }} - {{ $bank->bankAccount }}@if($bank->branchCode) ({{ $bank->branchCode }})@endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small mb-0 fw-semibold">Description</label>
                            <input type="text" name="description" id="edit_description" class="form-control form-control-sm" required maxlength="255">
                        </div>
                    </div>
                    <div id="editAjentFields" class="row g-2 mt-1 d-none">
                        <div class="col-4 col-md-3">
                            <label class="form-label small mb-0 fw-semibold">Market Bal.</label>
                            <input type="number" step="0.01" min="0" name="marketBalance" id="edit_marketBalance" class="form-control form-control-sm">
                        </div>
                        <div class="col-4 col-md-3">
                            <label class="form-label small mb-0 fw-semibold">Recovery</label>
                            <input type="number" step="0.01" min="0" name="recovery" id="edit_recovery" class="form-control form-control-sm">
                        </div>
                        <div class="col-4 col-md-2">
                            <label class="form-label small mb-0 fw-semibold">Comm%</label>
                            <input type="number" step="0.01" min="0" name="commissionPercent" id="edit_commissionPercent" class="form-control form-control-sm" readonly>
                        </div>
                        <input type="hidden" name="recoveryCommPercent" id="edit_recoveryCommPercent" value="">
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-0">
                                <label class="form-label small mb-0 fw-semibold" for="edit_amount">Amount</label>
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="isAdvance" value="1" id="edit_isAdvance">
                                    <label class="form-check-label small mb-0" for="edit_isAdvance">Advance</label>
                                </div>
                            </div>
                            <input type="number" step="0.01" min="0.01" name="amount" id="edit_amount" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
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
        title: 'Labour Transaction',
        text: @json(session('success')),
        confirmButtonText: 'OK',
        confirmButtonColor: '#0d6efd',
        allowOutsideClick: false
    });
    @endif

    function labourTxnPopup(icon, text, title) {
        if (typeof Swal === 'undefined') {
            alert(text);
            return;
        }
        const opts = {
            icon: icon,
            title: title || (icon === 'success' ? 'Saved' : (icon === 'error' ? 'Error' : 'Notice')),
            text: text,
            confirmButtonColor: '#0d6efd'
        };
        if (icon === 'success') {
            opts.timer = 1600;
            opts.showConfirmButton = false;
        }
        Swal.fire(opts);
    }

    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, function (ch) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
        });
    }

    const recentUrl = @json(route('labour.account.recent'));
    let labourDt = null;

    function initLabourAccountDataTable() {
        labourDt = $('#labourAccountTable').DataTable({
            pageLength: 10,
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: -1 }],
            language: {
                emptyTable: 'No labour transactions yet.',
                zeroRecords: 'No matching labour transactions found.'
            }
        });
    }

    function renderLabourTransactions(rows) {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#labourAccountTable')) {
            $('#labourAccountTable').DataTable().clear().destroy();
            labourDt = null;
        }

        const $tb = $('#labourAccountTable tbody');
        $tb.empty();

        if (rows && rows.length) {
            let html = '';
            rows.forEach(function (e) {
                const badge = e.entryType === 'debit' ? 'bg-danger' : 'bg-success';
                const typeRaw = String(e.entryType || '');
                const typeLabel = typeRaw ? typeRaw.charAt(0).toUpperCase() + typeRaw.slice(1) : '';
                const amount = 'Rs. ' + (Number(e.amount) || 0).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                html += '<tr>'
                    + '<td>' + escapeHtml(e.transactionDate || '') + '</td>'
                    + '<td>' + escapeHtml(e.labour || '') + '</td>'
                    + '<td>' + escapeHtml(e.description || '') + '</td>'
                    + '<td>' + escapeHtml(e.bankAccount || '-') + '</td>'
                    + '<td><span class="badge ' + badge + '">' + escapeHtml(typeLabel) + '</span></td>'
                    + '<td>' + amount + '</td>'
                    + '<td>' + (e.isAdvance ? 'Yes' : 'No') + '</td>'
                    + '<td class="text-center text-nowrap">'
                    + '<button type="button" class="btn btn-sm btn-outline-primary btn-edit-labour-txn py-0 px-2" data-id="' + e.id + '" title="Edit"><i class="bi bi-pencil"></i> Edit</button> '
                    + '<button type="button" class="btn btn-sm btn-outline-danger btn-delete-labour-txn py-0 px-2" data-id="' + e.id + '" title="Delete"><i class="bi bi-trash"></i> Delete</button>'
                    + '</td></tr>';
            });
            $tb.html(html);
        }

        initLabourAccountDataTable();
    }

    function updateLabourTxnTableTitle() {
        const labourId = $labourSelect.val();
        const $title = $('#labourTxnTableTitle');
        if (!labourId) {
            $title.text('All Labour Transactions');
            return;
        }
        const labour = allLabours.find(function (l) { return l.id === String(labourId); });
        $title.text(labour
            ? ('Transactions — ' + labour.code + ' - ' + labour.name)
            : 'Selected Labour Transactions');
    }

    function fetchLabourTransactions(params) {
        return $.ajax({
            url: recentUrl,
            method: 'GET',
            data: params || {},
            dataType: 'json',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
    }

    function loadLabourTransactions() {
        const labourId = $labourSelect.val();
        const params = labourId ? { labour_id: labourId } : {};
        updateLabourTxnTableTitle();
        return fetchLabourTransactions(params).done(function (res) {
            const rows = res.data || [];
            // Selected labour has no rows → show full list again
            if (labourId && !rows.length) {
                $('#labourTxnTableTitle').text('All Labour Transactions');
                return fetchLabourTransactions({}).done(function (allRes) {
                    renderLabourTransactions(allRes.data || []);
                });
            }
            renderLabourTransactions(rows);
        });
    }

    // ----- Labour search (same pattern as customer transaction: custLedgerTransaction.blade.php) -----
    const $labourSearchInput = $('#labourSearch');
    const $labourSelect = $('#labour_id');
    const $labourDropdown = $('#labourDropdown');
    let allLabours = [];

    $labourSelect.find('option').each(function () {
        if ($(this).val()) {
            allLabours.push({
                id: String($(this).val()),
                code: $(this).data('code'),
                name: $(this).data('name'),
                category: $(this).data('category') || '',
                display: $(this).text()
            });
        }
    });

    function filterLabours(searchTerm) {
        const term = (searchTerm || '').toLowerCase().trim();
        let filtered = allLabours;
        if (term) {
            filtered = filtered.filter(l =>
                (l.name && l.name.toLowerCase().includes(term)) ||
                (l.code && String(l.code).toLowerCase().includes(term)) ||
                (l.category && String(l.category).toLowerCase().includes(term))
            );
        }
        if (!term) {
            $labourDropdown.hide().empty();
            return;
        }
        if (filtered.length === 0) {
            $labourDropdown.html('<div class="dropdown-item text-muted">No labour found</div>').show();
            return;
        }
        let html = '';
        filtered.slice(0, 15).forEach((l, idx) => {
            html += `<a class="dropdown-item ${idx === 0 ? 'active' : ''}" href="javascript:void(0);" data-labour-id="${l.id}">
                <strong>${l.code} - ${l.name}</strong>
                <div class="text-muted small">${l.category}</div>
            </a>`;
        });
        $labourDropdown.html(html).show();
    }

    $labourSearchInput.on('input', function () {
        filterLabours($(this).val());
    });
    $labourSearchInput.on('focus', function () {
        if ($(this).val()) {
            filterLabours($(this).val());
        }
    });

    $labourDropdown.on('click', '.dropdown-item', function (e) {
        e.preventDefault();
        const id = String($(this).data('labour-id'));
        const labour = allLabours.find(l => l.id === id);
        if (!labour) return;
        $labourSelect.val(id).trigger('change');
        $labourSearchInput.val(labour.code + ' - ' + labour.name);
        $labourDropdown.hide();
        focusNextLabourField($labourSearchInput[0]);
    });

    $labourSearchInput.on('keydown', function (e) {
        const $items = $labourDropdown.find('.dropdown-item');
        const $active = $items.filter('.active');
        let $next;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            $next = $active.nextAll('.dropdown-item').eq(0);
            if ($next.length) {
                $items.removeClass('active');
                $next.addClass('active');
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            $next = $active.prevAll('.dropdown-item').eq(0);
            if ($next.length) {
                $items.removeClass('active');
                $next.addClass('active');
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if ($active.length) {
                $active.trigger('click');
            } else if ($labourSelect.val()) {
                $labourDropdown.hide();
                focusNextLabourField(this);
            }
        }
    });

    // ----- Bank search (same pattern as customer transaction bank dropdown) -----
    const $bankSearchInput = $('#bankSearch');
    const $bankSelect = $('#bankAccount');
    const $bankDropdown = $('#bankDropdown');
    let allBanks = [];

    $bankSelect.find('option').each(function () {
        if ($(this).val()) {
            allBanks.push({
                code: $(this).val(),
                name: $(this).data('name'),
                branch: $(this).data('branch') || '',
                display: $(this).text()
            });
        }
    });

    function filterBanks(searchTerm) {
        const term = (searchTerm || '').toLowerCase().trim();
        let filtered = allBanks;
        if (term) {
            filtered = filtered.filter(b =>
                (b.name && b.name.toLowerCase().includes(term)) ||
                (b.branch && String(b.branch).toLowerCase().includes(term)) ||
                (b.code && String(b.code).toLowerCase().includes(term))
            );
        }
        if (!term) {
            $bankDropdown.hide().empty();
            return;
        }
        if (filtered.length === 0) {
            $bankDropdown.html('<div class="dropdown-item text-muted">No banks found</div>').show();
            return;
        }
        let html = '';
        filtered.slice(0, 15).forEach((b, idx) => {
            html += `<a class="dropdown-item ${idx === 0 ? 'active' : ''}" href="javascript:void(0);" data-code="${b.code}">
                <strong>${b.name}</strong>
                <div class="text-muted small">Code: ${b.code}${b.branch ? ' | Branch: ' + b.branch : ''}</div>
            </a>`;
        });
        $bankDropdown.html(html).show();
    }

    $bankSearchInput.on('input', function () {
        filterBanks($(this).val());
    });
    $bankSearchInput.on('focus', function () {
        if ($(this).val()) {
            filterBanks($(this).val());
        }
    });

    $bankDropdown.on('click', '.dropdown-item', function (e) {
        e.preventDefault();
        const code = $(this).data('code');
        const bank = allBanks.find(b => b.code === code);
        if (!bank) return;
        $bankSelect.val(code).trigger('change');
        $bankSearchInput.val(bank.name);
        $bankDropdown.hide();
        focusNextLabourField($bankSearchInput[0]);
    });

    $bankSearchInput.on('keydown', function (e) {
        const $items = $bankDropdown.find('.dropdown-item');
        const $active = $items.filter('.active');
        let $next;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            $next = $active.nextAll('.dropdown-item').eq(0);
            if ($next.length) {
                $items.removeClass('active');
                $next.addClass('active');
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            $next = $active.prevAll('.dropdown-item').eq(0);
            if ($next.length) {
                $items.removeClass('active');
                $next.addClass('active');
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if ($active.length) {
                $active.trigger('click');
            } else if ($bankSelect.val()) {
                $bankDropdown.hide();
                focusNextLabourField(this);
            }
        }
    });

    function focusNextLabourField(current) {
        const fields = Array.from(document.querySelectorAll(
            '#labourAccountForm input, #labourAccountForm select, #btnLabourTxnSave'
        )).filter(function (el) {
            if (el.disabled || el.readOnly) return false;
            const type = (el.type || '').toLowerCase();
            if (type === 'hidden' || type === 'checkbox') return false;
            if (el.id === 'labour_id' || el.id === 'bankAccount') return false;
            if (el.closest('.d-none, .modal')) return false;
            return el.offsetParent !== null || el === document.activeElement;
        });
        const index = fields.indexOf(current);
        const next = index >= 0 ? fields[index + 1] : null;
        const target = next || document.getElementById('btnLabourTxnSave');
        if (!target) return;
        target.focus();
        if (target.tagName === 'INPUT' && typeof target.select === 'function') target.select();
    }

    $('#labourAccountForm').on('keydown', function (e) {
        if (e.key !== 'Enter') return;
        const el = e.target;
        if (!el || el.id === 'labourSearch' || el.id === 'bankSearch' || el.id === 'btnLabourTxnSave') return;
        if (el.tagName !== 'INPUT' && el.tagName !== 'SELECT') return;
        if ((el.type || '').toLowerCase() === 'checkbox') return;
        e.preventDefault();
        focusNextLabourField(el);
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#labourSearch, #labourDropdown').length) {
            $labourDropdown.hide();
        }
        if (!$(e.target).closest('#bankSearch, #bankDropdown').length) {
            $bankDropdown.hide();
        }
    });

    // ----- Balance preview (labour + bank) -----
    const bankBalanceUrl = @json(url('/api/bank-balance'));
    let selectedLabourBalance = 0;
    let selectedBankBalance = 0;

    function fmt(n) {
        return (Number(n || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function showAfterBalance($wrap, $valueEl, afterVal) {
        if (afterVal === null || afterVal === undefined) {
            $wrap.hide();
            return;
        }
        $valueEl
            .text(fmt(afterVal))
            .removeClass('text-info text-danger text-success text-primary')
            .addClass(afterVal < 0 ? 'text-danger' : 'text-info');
        $wrap.show();
    }

    /** Labour ledger: debit increases running balance, credit decreases */
    function projectLabourBalance(current, amount, entryType) {
        const amt = parseFloat(amount) || 0;
        if (amt <= 0) return null;
        return entryType === 'debit' ? current + amt : current - amt;
    }

    /** Bank ledger: debit increases, credit decreases */
    function projectBankBalance(current, amount, entryType) {
        const amt = parseFloat(amount) || 0;
        if (amt <= 0) return null;
        return entryType === 'debit' ? current + amt : current - amt;
    }

    function updateProjectedBalances() {
        const amountVal = $('#amount').val();
        const entryType = $('#entryType').val() || 'debit';
        const bankCode = $('#bankAccount').val();

        if ($('#labourBalanceDisplay').is(':visible')) {
            showAfterBalance($('#labourAfterTxnWrap'), $('#labourAfterTxnValue'),
                projectLabourBalance(selectedLabourBalance, amountVal, entryType));
        } else {
            $('#labourAfterTxnWrap').hide();
        }

        if ($('#bankBalanceDisplay').is(':visible') && bankCode) {
            showAfterBalance($('#bankAfterTxnWrap'), $('#bankAfterTxnValue'),
                projectBankBalance(selectedBankBalance, amountVal, entryType));
        } else {
            $('#bankAfterTxnWrap').hide();
        }
    }

    function loadBankBalance(code) {
        if (!code) {
            selectedBankBalance = 0;
            $('#bankBalanceDisplay, #bankAfterTxnWrap').hide();
            updateProjectedBalances();
            return;
        }
        $.get(bankBalanceUrl + '/' + encodeURIComponent(code))
            .done(function (res) {
                if (res.success) {
                    selectedBankBalance = parseFloat(res.balance ?? res.current_balance ?? 0) || 0;
                    $('#bankBalanceValue').text(fmt(selectedBankBalance));
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

    $bankSelect.on('change', function () {
        loadBankBalance($(this).val());
    });

    $bankSearchInput.on('input', function () {
        if (!$(this).val().trim()) {
            $bankSelect.val('');
            selectedBankBalance = 0;
            $('#bankBalanceDisplay, #bankAfterTxnWrap').hide();
            updateProjectedBalances();
        }
    });

    $('#amount, #entryType').on('input change', updateProjectedBalances);

    // ----- Agent-only fields + labour change -----
    const ajentOnlyGroup = document.getElementById('ajentOnlyGroup');
    const marketBalance = document.getElementById('marketBalance');
    const recovery = document.getElementById('recovery');
    const recoveryCommPercent = document.getElementById('recoveryCommPercent');
    const commissionPercent = document.getElementById('commissionPercent');
    const amount = document.getElementById('amount');

    function recalculateAjentFields() {
        if (!marketBalance || !recovery || !commissionPercent || !amount) return;
        const m = parseFloat(marketBalance.value || 0);
        const r = parseFloat(recovery.value || 0);
        const c = parseFloat(commissionPercent.value || 0);
        if (recoveryCommPercent) {
            recoveryCommPercent.value = m > 0 ? ((r / m) * 100).toFixed(2) : '0.00';
        }
        amount.value = ((r * c) / 100).toFixed(2);
        updateProjectedBalances();
    }

    function setAjentVisible(show) {
        if (!ajentOnlyGroup) return;
        ajentOnlyGroup.querySelectorAll('input').forEach((inp) => {
            if (show) {
                inp.removeAttribute('disabled');
            } else {
                inp.setAttribute('disabled', 'disabled');
                inp.value = '';
            }
        });
        ajentOnlyGroup.classList.toggle('d-none', !show);
    }

    $labourSelect.on('change', async function () {
        const labourId = $(this).val();
        if (!labourId) {
            setAjentVisible(false);
            selectedLabourBalance = 0;
            $('#labourBalanceDisplay, #labourAfterTxnWrap').hide();
            updateProjectedBalances();
            loadLabourTransactions();
            return;
        }
        try {
            const res = await fetch(`{{ url('/labour-account/labour') }}/${labourId}`);
            const data = await res.json();
            if (!data.success) return;

            selectedLabourBalance = parseFloat(data.labour.ledgerBalance ?? data.labour.balance ?? 0) || 0;
            $('#labourBalanceValue').text(fmt(selectedLabourBalance));
            $('#labourBalanceDisplay').show();
            updateProjectedBalances();

            const cat = (data.labour.labourCatagory || '').toLowerCase().trim();
            if (cat === 'ajent' || cat === 'agent') {
                setAjentVisible(true);
                if (commissionPercent) {
                    commissionPercent.value = parseFloat(data.labour.commissionPercentage || 0).toFixed(2);
                }
                recalculateAjentFields();
            } else {
                setAjentVisible(false);
            }
            loadLabourTransactions();
        } catch (e) {
            console.error(e);
            selectedLabourBalance = 0;
            $('#labourBalanceDisplay, #labourAfterTxnWrap').hide();
            loadLabourTransactions();
        }
    });

    $labourSearchInput.on('input', function () {
        if (!$(this).val().trim()) {
            $labourSelect.val('');
            selectedLabourBalance = 0;
            $('#labourBalanceDisplay, #labourAfterTxnWrap').hide();
            setAjentVisible(false);
            updateProjectedBalances();
            loadLabourTransactions();
        }
    });

    if (marketBalance) marketBalance.addEventListener('input', recalculateAjentFields);
    if (recovery) recovery.addEventListener('input', recalculateAjentFields);

    if (window.jQuery && $('#labourAccountTable').length) {
        initLabourAccountDataTable();
    }

    $('#labourAccountForm').on('submit', function (e) {
        e.preventDefault();
        if (!$labourSelect.val()) {
            labourTxnPopup('warning', 'Please select a labour from the search list.', 'Labour Required');
            $labourSearchInput.focus();
            return;
        }
        const $btn = $('#btnLabourTxnSave').prop('disabled', true);
        $.ajax({
            url: this.action,
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).done(function (res) {
            labourTxnPopup('success', res.message || 'Labour account transaction saved successfully.');
            $('#labourAccountForm [name="description"]').val('');
            $('#amount').val('');
            $('#isAdvance').prop('checked', false);
            if (marketBalance) marketBalance.value = '';
            if (recovery) recovery.value = '';
            if (recoveryCommPercent) recoveryCommPercent.value = '';
            if (res.ledgerBalance !== undefined && $('#labourBalanceDisplay').is(':visible')) {
                selectedLabourBalance = parseFloat(res.ledgerBalance) || 0;
                $('#labourBalanceValue').text(fmt(selectedLabourBalance));
            }
            if (res.bankBalance !== undefined && $('#bankBalanceDisplay').is(':visible')) {
                selectedBankBalance = parseFloat(res.bankBalance) || 0;
                $('#bankBalanceValue').text(fmt(selectedBankBalance));
            }
            updateProjectedBalances();
            loadLabourTransactions();
            $('#labourAccountForm [name="description"]').focus();
        }).fail(function (xhr) {
            const errors = xhr.responseJSON && xhr.responseJSON.errors;
            const first = errors ? Object.values(errors)[0][0] : (xhr.responseJSON && xhr.responseJSON.message);
            labourTxnPopup('error', first || 'Could not save the transaction.', 'Save Failed');
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });

    const txnBaseUrl = @json(url('/labour-account/transaction'));

    function recalcEditAjent() {
        const m = parseFloat($('#edit_marketBalance').val() || 0);
        const r = parseFloat($('#edit_recovery').val() || 0);
        const c = parseFloat($('#edit_commissionPercent').val() || 0);
        $('#edit_recoveryCommPercent').val(m > 0 ? ((r / m) * 100).toFixed(2) : '0.00');
        $('#edit_amount').val(((r * c) / 100).toFixed(2));
    }

    $('#edit_marketBalance, #edit_recovery').on('input', recalcEditAjent);

    const editLabourTxnModalEl = document.getElementById('editLabourTxnModal');

    $('#labourAccountTable').on('click', '.btn-edit-labour-txn', function () {
        const id = $(this).data('id');
        $('#editLabourTxnForm').attr('action', txnBaseUrl + '/' + id);
        fetch(txnBaseUrl + '/' + id, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    alert('Could not load transaction');
                    return;
                }
                const t = data.transaction;
                const showAjentFields = !!t.isAjent || (function (c) {
                    c = String(c || '').toLowerCase().trim();
                    return c === 'ajent' || c === 'agent';
                })(t.labourCatagory);

                $('#edit_labourLabel').val(t.labourLabel);
                $('#edit_transactionDate').val(t.transactionDate);
                $('#edit_bankAccount').val(t.bankAccount || '');
                $('#edit_description').val(t.description);
                $('#edit_amount').val(t.amount);
                $('#edit_entryType').val(t.entryType);
                $('#edit_isAdvance').prop('checked', !!t.isAdvance);

                if (showAjentFields) {
                    $('#editAjentFields').removeClass('d-none');
                    $('#editAjentFields').find('input').prop('disabled', false);
                    $('#edit_commissionPercent').prop('readonly', true);
                    $('#edit_marketBalance').val(t.marketBalance === '' || t.marketBalance === null ? '' : t.marketBalance);
                    $('#edit_recovery').val(t.recovery === '' || t.recovery === null ? '' : t.recovery);
                    $('#edit_commissionPercent').val(
                        t.commissionPercent !== '' && t.commissionPercent != null ? t.commissionPercent : ''
                    );
                    $('#edit_recoveryCommPercent').val(
                        t.recoveryCommPercent !== '' && t.recoveryCommPercent != null ? t.recoveryCommPercent : ''
                    );
                } else {
                    $('#editAjentFields').addClass('d-none');
                    $('#editAjentFields').find('input').prop('disabled', true);
                }

                const modal = bootstrap.Modal.getOrCreateInstance(editLabourTxnModalEl);
                modal.show();
            })
            .catch(function () {
                alert('Failed to load transaction');
            });
    });

    // Delete — same SweetAlert2 flow as customer ledger (custLedgerTransaction deleteEntry)
    $('#labourAccountTable').on('click', '.btn-delete-labour-txn', function () {
        const id = $(this).data('id');
        if (!id) {
            return;
        }
        if (typeof Swal === 'undefined') {
            if (!confirm('Delete this labour account transaction?')) {
                return;
            }
            $.ajax({
                url: txnBaseUrl + '/' + id,
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).done(function () {
                loadLabourTransactions();
            }).fail(function () {
                alert('Delete failed.');
            });
            return;
        }

        Swal.fire({
            title: 'Confirm Delete?',
            text: 'This labour account transaction will be deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: txnBaseUrl + '/' + id,
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .done(function () {
                        labourTxnPopup('success', 'Transaction has been removed successfully', 'Deleted!');
                        loadLabourTransactions();
                    })
                    .fail(function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Delete Failed',
                            text: (xhr.responseJSON && xhr.responseJSON.message)
                                ? xhr.responseJSON.message
                                : 'Failed to delete transaction',
                            confirmButtonColor: '#d33'
                        });
                    });
            }
        });
    });

    $('#editLabourTxnForm').on('submit', function (e) {
        e.preventDefault();
        if ($('#editAjentFields').hasClass('d-none')) {
            $('#editAjentFields').find('input').prop('disabled', true);
        }
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]').prop('disabled', true);
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).done(function (res) {
            const modal = bootstrap.Modal.getInstance(editLabourTxnModalEl);
            if (modal) modal.hide();
            labourTxnPopup('success', res.message || 'Labour account transaction updated successfully.');
            loadLabourTransactions();
        }).fail(function (xhr) {
            const errors = xhr.responseJSON && xhr.responseJSON.errors;
            const first = errors ? Object.values(errors)[0][0] : (xhr.responseJSON && xhr.responseJSON.message);
            labourTxnPopup('error', first || 'Could not update the transaction.', 'Update Failed');
        }).always(function () {
            $btn.prop('disabled', false);
            $('#editAjentFields').find('input').prop('disabled', false);
            $('#edit_commissionPercent').prop('readonly', true);
        });
    });

    setAjentVisible(false);
});
</script>
@endsection

