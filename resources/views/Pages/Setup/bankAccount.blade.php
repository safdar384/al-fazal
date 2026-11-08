@extends('layout.app')

@section('title', 'Bank Accounts')

@section('content')
@php
    $totalBanks = $bankAC->count();
    $totalBalance = (float) $bankAC->sum('balance');
@endphp

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center gy-2">
                <div class="col-12 col-md-6">
                    <h3 class="mb-0 fw-bold" style="color:#0f2747;">
                        <i class="bi bi-bank me-2"></i>Bank Accounts
                    </h3>
                    <p class="text-muted small mb-0">Bank · Account master list</p>
                </div>
                <div class="col-12 col-md-6">
                    <ol class="breadcrumb float-md-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><span class="text-muted">Bank</span></li>
                        <li class="breadcrumb-item active">Accounts</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-3">
                    <div class="ba-kpi">
                        <div class="ba-kpi-icon"><i class="bi bi-wallet2"></i></div>
                        <div>
                            <div class="ba-kpi-lbl">Total Accounts</div>
                            <div class="ba-kpi-val" id="baTotalCount">{{ number_format($totalBanks) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="ba-kpi {{ $totalBalance >= 0 ? 'ok' : 'warn' }}" id="baBalanceKpi">
                        <div class="ba-kpi-icon {{ $totalBalance >= 0 ? 'ok' : 'warn' }}" id="baBalanceIcon"><i class="bi bi-cash-stack"></i></div>
                        <div>
                            <div class="ba-kpi-lbl">Total Balance</div>
                            <div class="ba-kpi-val" id="baTotalBalance">Rs. {{ number_format($totalBalance, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card ba-card border-0">
                <div class="card-header ba-card-head">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h6 class="mb-0 text-white fw-semibold">
                            <i class="bi bi-table me-2"></i>Bank Account List
                        </h6>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="input-group input-group-sm ba-search">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" id="bankSearch" class="form-control" placeholder="Search bank accounts...">
                            </div>
                            <button type="button" class="btn btn-sm btn-light fw-semibold" onclick="openbankModal()">
                                <i class="bi bi-plus-circle me-1"></i>Add New Bank
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 ba-table" id="bankAccountTable">
                            <thead>
                                <tr>
                                    <th style="width:60px;" class="text-center">#</th>
                                    <th>Bank Account</th>
                                    <th>Bank Name</th>
                                    <th>Address</th>
                                    <th>Branch Code</th>
                                    <th class="text-end">Balance</th>
                                    <th class="text-center" style="width:140px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bankAC as $index => $bank)
                                    <tr data-id="{{ $bank->id }}">
                                        <td class="text-center text-muted small">{{ $index + 1 }}</td>
                                        <td><span class="ba-code">{{ $bank->bankAccount }}</span></td>
                                        <td class="fw-semibold" style="color:#0f2747;">{{ $bank->bankName }}</td>
                                        <td class="small">{{ $bank->bankAddress ?: '—' }}</td>
                                        <td>{{ $bank->branchCode ?: '—' }}</td>
                                        <td class="text-end fw-semibold {{ (float) $bank->balance >= 0 ? 'text-success' : 'text-danger' }}">
                                            Rs. {{ number_format((float) $bank->balance, 2) }}
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-secondary btn-edit-bank"
                                                        data-id="{{ $bank->id }}"
                                                        data-account="{{ $bank->bankAccount }}"
                                                        data-name="{{ $bank->bankName }}"
                                                        data-address="{{ $bank->bankAddress }}"
                                                        data-branch="{{ $bank->branchCode }}"
                                                        title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-delete-bank"
                                                        data-id="{{ $bank->id }}"
                                                        data-name="{{ $bank->bankName }}"
                                                        title="Delete">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="ba-empty-row">
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No bank accounts found.
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-sm text-white" style="background:#0f2747;" onclick="openbankModal()">
                                                    <i class="bi bi-plus-circle me-1"></i>Add New Bank
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

{{-- Add modal --}}
<div class="modal fade" id="bankModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('bankAccount.store') }}" method="POST" id="bankForm">
                @csrf
                <div class="modal-header ba-modal-head">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Bank</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label fw-semibold ba-label">Bank Account <span class="text-danger">*</span></label>
                        <input type="text" name="bankAccount" class="form-control form-control-sm" required autocomplete="off">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold ba-label">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="bankName" class="form-control form-control-sm" required autocomplete="off">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold ba-label">Bank Address <span class="text-danger">*</span></label>
                        <input type="text" name="bankAddress" class="form-control form-control-sm" required autocomplete="off">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold ba-label">Branch Code <span class="text-danger">*</span></label>
                        <input type="text" name="branchCode" class="form-control form-control-sm" required autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm text-white" id="btnBankSave" style="background:#0f2747;border-color:#0f2747;">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit modal --}}
<div class="modal fade" id="editBankModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="" method="POST" id="editBankForm">
                @csrf
                @method('PUT')
                <div class="modal-header ba-modal-head">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Bank</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label fw-semibold ba-label">Bank Account <span class="text-danger">*</span></label>
                        <input type="text" name="bankAccount" id="edit_bankAccount" class="form-control form-control-sm" required autocomplete="off">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold ba-label">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="bankName" id="edit_bankName" class="form-control form-control-sm" required autocomplete="off">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold ba-label">Bank Address <span class="text-danger">*</span></label>
                        <input type="text" name="bankAddress" id="edit_bankAddress" class="form-control form-control-sm" required autocomplete="off">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold ba-label">Branch Code <span class="text-danger">*</span></label>
                        <input type="text" name="branchCode" id="edit_branchCode" class="form-control form-control-sm" required autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm text-white" id="btnBankUpdate" style="background:#0f2747;border-color:#0f2747;">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .ba-kpi {
        display: flex; align-items: center; gap: 0.75rem;
        background: #fff; border: 1px solid #d5dde8; border-left: 4px solid #0f2747;
        border-radius: 4px; padding: 0.85rem 1rem; height: 100%;
        box-shadow: 0 2px 8px rgba(15, 39, 71, 0.05);
    }
    .ba-kpi.ok { border-left-color: #198754; }
    .ba-kpi.warn { border-left-color: #dc3545; background: #fdf0f1; }
    .ba-kpi-icon {
        width: 42px; height: 42px; border-radius: 6px; background: #e8eef5; color: #0f2747;
        display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;
    }
    .ba-kpi-icon.ok { background: #e8f5e9; color: #198754; }
    .ba-kpi-icon.warn { background: #fde8ea; color: #dc3545; }
    .ba-kpi-lbl {
        font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.3px; color: #6b7c8d; margin-bottom: 0.15rem;
    }
    .ba-kpi-val { font-size: 1.1rem; font-weight: 700; color: #0f2747; line-height: 1.2; }
    .ba-card {
        box-shadow: 0 2px 12px rgba(15, 39, 71, 0.08);
        border-radius: 4px; overflow: hidden; border: 1px solid #d5dde8;
    }
    .ba-card-head {
        background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
        border: none; padding: 0.85rem 1rem;
    }
    .ba-search { max-width: 260px; }
    .ba-search .input-group-text,
    .ba-search .form-control { background: rgba(255,255,255,0.95); border-color: transparent; }
    .ba-table thead th {
        background: #0f2747; color: #fff; font-size: 0.68rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.3px; border-color: #0a1c33;
        white-space: nowrap; vertical-align: middle; padding: 0.55rem 0.65rem;
    }
    .ba-table tbody td {
        font-size: 0.85rem; border-color: #e8eef5; vertical-align: middle; padding: 0.55rem 0.65rem;
    }
    .ba-table tbody tr:nth-child(even) td { background: #f4f7fb; }
    .ba-table tbody tr:hover td { background: #e8eef5; }
    .ba-code {
        display: inline-block; padding: 0.15rem 0.45rem; border-radius: 3px;
        background: #e8eef5; color: #0f2747; font-weight: 700; font-size: 0.75rem;
    }
    .ba-modal-head {
        background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
        color: #fff;
    }
    .ba-label { font-size: 0.78rem; color: #0f2747; margin-bottom: 0.2rem; }
    .dataTables_wrapper .dataTables_filter { display: none; }
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate { padding: 0.65rem 1rem; font-size: 0.8rem; }
    .dataTables_wrapper .page-item.active .page-link {
        background: #0f2747; border-color: #0f2747;
    }
    .dataTables_wrapper .page-link { color: #0f2747; }
</style>
@endpush

<script>
function openbankModal() {
    const form = document.getElementById('bankForm');
    if (form) form.reset();
    new bootstrap.Modal(document.getElementById('bankModal'), {
        backdrop: 'static',
        keyboard: false
    }).show();
}

$(document).ready(function () {
    const listUrl = @json(route('bankAccount.list'));
    const storeUrl = @json(route('bankAccount.store'));
    const updateBase = @json(url('/bank-accounts'));
    const csrf = $('meta[name="csrf-token"]').attr('content');
    let bankDt = null;

    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, function (ch) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
        });
    }

    function fmtMoney(n) {
        return (Number(n) || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function baPopup(icon, text, title) {
        if (typeof Swal === 'undefined') {
            alert(text);
            return;
        }
        Swal.fire({
            icon: icon,
            title: title || (icon === 'success' ? 'Saved' : 'Notice'),
            text: text,
            confirmButtonText: 'OK',
            confirmButtonColor: '#0f2747'
        });
    }

    function updateKpis(total, totalBalance) {
        $('#baTotalCount').text(Number(total || 0).toLocaleString());
        $('#baTotalBalance').text('Rs. ' + fmtMoney(totalBalance));
        const ok = (Number(totalBalance) || 0) >= 0;
        $('#baBalanceKpi').toggleClass('ok', ok).toggleClass('warn', !ok);
        $('#baBalanceIcon').toggleClass('ok', ok).toggleClass('warn', !ok);
    }

    function destroyBankTable() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#bankAccountTable')) {
            $('#bankAccountTable').DataTable().clear().destroy();
            bankDt = null;
        }
    }

    function initBankTable() {
        const hasRows = $('#bankAccountTable tbody tr').length > 0
            && !$('#bankAccountTable tbody tr').first().hasClass('ba-empty-row')
            && $('#bankAccountTable tbody tr').first().find('td').length > 1;

        if (!hasRows) return;

        bankDt = $('#bankAccountTable').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            paging: true,
            info: true,
            searching: true,
            ordering: true,
            order: [[1, 'asc']],
            columnDefs: [{ orderable: false, targets: [6] }],
            language: {
                lengthMenu: 'Show _MENU_',
                info: 'Showing _START_–_END_ of _TOTAL_ accounts',
                emptyTable: 'No bank accounts found.',
                paginate: { previous: '‹', next: '›' }
            },
            dom: '<"row px-2 pt-2"<"col-sm-12 col-md-6"l>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
        });
        $('.dataTables_length select').addClass('form-select form-select-sm');
    }

    function renderBanks(rows) {
        destroyBankTable();
        const $tb = $('#bankAccountTable tbody');
        $tb.empty();

        if (!rows || !rows.length) {
            $tb.html(
                '<tr class="ba-empty-row"><td colspan="7" class="text-center text-muted py-5">'
                + '<i class="bi bi-inbox fs-1 d-block mb-2"></i>No bank accounts found.'
                + '<div class="mt-2"><button type="button" class="btn btn-sm text-white" style="background:#0f2747;" onclick="openbankModal()">'
                + '<i class="bi bi-plus-circle me-1"></i>Add New Bank</button></div></td></tr>'
            );
            return;
        }

        let html = '';
        rows.forEach(function (b, index) {
            const bal = Number(b.balance) || 0;
            const balClass = bal >= 0 ? 'text-success' : 'text-danger';
            html += '<tr data-id="' + b.id + '">'
                + '<td class="text-center text-muted small">' + (index + 1) + '</td>'
                + '<td><span class="ba-code">' + escapeHtml(b.bankAccount) + '</span></td>'
                + '<td class="fw-semibold" style="color:#0f2747;">' + escapeHtml(b.bankName) + '</td>'
                + '<td class="small">' + escapeHtml(b.bankAddress || '—') + '</td>'
                + '<td>' + escapeHtml(b.branchCode || '—') + '</td>'
                + '<td class="text-end fw-semibold ' + balClass + '">Rs. ' + fmtMoney(bal) + '</td>'
                + '<td class="text-center"><div class="btn-group btn-group-sm">'
                + '<button type="button" class="btn btn-outline-secondary btn-edit-bank" title="Edit"'
                + ' data-id="' + b.id + '"'
                + ' data-account="' + escapeHtml(b.bankAccount) + '"'
                + ' data-name="' + escapeHtml(b.bankName) + '"'
                + ' data-address="' + escapeHtml(b.bankAddress || '') + '"'
                + ' data-branch="' + escapeHtml(b.branchCode || '') + '">'
                + '<i class="bi bi-pencil-square"></i></button>'
                + '<button type="button" class="btn btn-outline-danger btn-delete-bank" title="Delete"'
                + ' data-id="' + b.id + '" data-name="' + escapeHtml(b.bankName) + '">'
                + '<i class="bi bi-trash3"></i></button>'
                + '</div></td></tr>';
        });
        $tb.html(html);
        initBankTable();
    }

    function loadBanks() {
        return $.ajax({
            url: listUrl,
            method: 'GET',
            dataType: 'json',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).done(function (res) {
            renderBanks(res.data || []);
            updateKpis(res.total, res.totalBalance);
        });
    }

    $('#bankSearch').on('keyup', function () {
        if (bankDt) {
            bankDt.search(this.value).draw();
            return;
        }
        const value = this.value.toLowerCase();
        $('#bankAccountTable tbody tr').filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    initBankTable();

    $('#bankForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#btnBankSave').prop('disabled', true);
        $.ajax({
            url: storeUrl,
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).done(function (res) {
            bootstrap.Modal.getInstance(document.getElementById('bankModal'))?.hide();
            document.getElementById('bankForm').reset();
            baPopup('success', res.message || 'Bank Account created successfully!', 'Saved');
            loadBanks();
        }).fail(function (xhr) {
            const errors = xhr.responseJSON && xhr.responseJSON.errors;
            const first = errors ? Object.values(errors)[0][0] : (xhr.responseJSON && xhr.responseJSON.message);
            baPopup('error', first || 'Could not save bank account.', 'Save Failed');
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });

    $(document).on('click', '.btn-edit-bank', function () {
        const $btn = $(this);
        $('#editBankForm').attr('action', updateBase + '/' + $btn.data('id'));
        $('#edit_bankAccount').val($btn.data('account') || '');
        $('#edit_bankName').val($btn.data('name') || '');
        $('#edit_bankAddress').val($btn.data('address') || '');
        $('#edit_branchCode').val($btn.data('branch') || '');
        new bootstrap.Modal(document.getElementById('editBankModal'), {
            backdrop: 'static',
            keyboard: false
        }).show();
    });

    $('#editBankForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#btnBankUpdate').prop('disabled', true);
        $.ajax({
            url: this.action,
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).done(function (res) {
            bootstrap.Modal.getInstance(document.getElementById('editBankModal'))?.hide();
            baPopup('success', res.message || 'Bank Account updated successfully!', 'Updated');
            loadBanks();
        }).fail(function (xhr) {
            const errors = xhr.responseJSON && xhr.responseJSON.errors;
            const first = errors ? Object.values(errors)[0][0] : (xhr.responseJSON && xhr.responseJSON.message);
            baPopup('error', first || 'Could not update bank account.', 'Update Failed');
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });

    $(document).on('click', '.btn-delete-bank', function () {
        const id = $(this).data('id');
        const name = $(this).data('name') || 'this bank';
        Swal.fire({
            title: 'Delete Bank Account',
            text: 'Do you really want to delete ' + name + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then(function (result) {
            if (!result.isConfirmed) return;
            $.ajax({
                url: updateBase + '/' + id,
                method: 'POST',
                data: { _method: 'DELETE', _token: csrf },
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).done(function (res) {
                baPopup('success', res.message || 'Bank Account deleted.', 'Deleted');
                loadBanks();
            }).fail(function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Could not delete bank account.';
                baPopup('error', msg, 'Delete Failed');
            });
        });
    });
});
</script>

@endsection
