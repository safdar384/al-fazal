@extends('layout.app')

@section('title', 'Labour Management')

@php
    $totalLabour = $labours->count();
    $ajentCount = $labours->filter(fn ($l) => strtolower((string) $l->labourCatagory) === 'ajent')->count();
    $workerCount = $labours->filter(fn ($l) => strtolower((string) $l->labourCatagory) === 'worker')->count();
    $fitterCount = $labours->filter(fn ($l) => strtolower((string) $l->labourCatagory) === 'fitter')->count();
    $totalSalary = $labours->sum(fn ($l) => (float) $l->salary);
    $totalAdvance = $labours->sum(fn ($l) => (float) $l->advance);
    $totalBalance = $labours->sum(fn ($l) => (float) $l->balance);
@endphp

@section('content')

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center gy-2">
                <div class="col-12 col-md-6">
                    <h3 class="mb-0 fw-bold" style="color:#0f2747;">
                        <i class="bi bi-person-badge me-2"></i>Labour Management
                    </h3>
                    <p class="text-muted small mb-0">Labour · Workers, agents & fitters</p>
                </div>
                <div class="col-12 col-md-6">
                    <ol class="breadcrumb float-md-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><span class="text-muted">Labour</span></li>
                        <li class="breadcrumb-item active" aria-current="page">Setup</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row g-3 mb-3">
                <div class="col-6 col-xl-3">
                    <div class="lm-kpi">
                        <div class="lm-kpi-icon"><i class="bi bi-people"></i></div>
                        <div>
                            <div class="lm-kpi-lbl">Total Labour</div>
                            <div class="lm-kpi-val">{{ number_format($totalLabour) }}</div>
                            <div class="small text-muted">W {{ $workerCount }} · A {{ $ajentCount }} · F {{ $fitterCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="lm-kpi salary">
                        <div class="lm-kpi-icon salary"><i class="bi bi-cash-stack"></i></div>
                        <div>
                            <div class="lm-kpi-lbl">Total Salary</div>
                            <div class="lm-kpi-val">Rs. {{ number_format($totalSalary, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="lm-kpi adv">
                        <div class="lm-kpi-icon adv"><i class="bi bi-wallet2"></i></div>
                        <div>
                            <div class="lm-kpi-lbl">Total Advance</div>
                            <div class="lm-kpi-val">Rs. {{ number_format($totalAdvance, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="lm-kpi bal">
                        <div class="lm-kpi-icon bal"><i class="bi bi-calculator"></i></div>
                        <div>
                            <div class="lm-kpi-lbl">Total Balance</div>
                            <div class="lm-kpi-val {{ $totalBalance >= 0 ? 'text-danger' : 'text-success' }}">
                                Rs. {{ number_format($totalBalance, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card lm-card border-0">
                <div class="card-header lm-card-head">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h6 class="mb-0 text-white fw-semibold">
                            <i class="bi bi-table me-2"></i>Labour List
                        </h6>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="input-group input-group-sm lm-search">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" id="globalSearch" class="form-control" placeholder="Search labour...">
                            </div>
                            <a href="{{ route('Labour.create') }}" class="btn btn-sm btn-light fw-semibold">
                                <i class="bi bi-plus-circle me-1"></i>Add New Labour
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 lm-table" id="labourTable">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:50px;">#</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th class="text-end">Salary</th>
                                    <th class="text-end">Advance</th>
                                    <th class="text-end">Balance</th>
                                    <th class="text-end">Commission</th>
                                    <th class="text-center" style="width:110px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($labours as $index => $labour)
                                    @php $cat = strtolower((string) $labour->labourCatagory); @endphp
                                    <tr>
                                        <td class="text-center text-muted small">{{ $index + 1 }}</td>
                                        <td><span class="lm-code">{{ $labour->labourCode }}</span></td>
                                        <td>
                                            <div class="fw-semibold" style="color:#0f2747;">{{ $labour->labourName }}</div>
                                            <small class="text-muted">{{ $labour->labourAddress ?: '—' }}</small>
                                        </td>
                                        <td>
                                            <span class="lm-badge {{ $cat }}">{{ ucfirst($labour->labourCatagory) }}</span>
                                        </td>
                                        <td class="text-end small">Rs. {{ number_format((float) $labour->salary, 2) }}</td>
                                        <td class="text-end small">Rs. {{ number_format((float) $labour->advance, 2) }}</td>
                                        <td class="text-end small fw-semibold {{ (float) $labour->balance >= 0 ? 'text-danger' : 'text-success' }}">
                                            Rs. {{ number_format((float) $labour->balance, 2) }}
                                        </td>
                                        <td class="text-end small">
                                            @if($labour->commissionPercentage !== null)
                                                {{ number_format((float) $labour->commissionPercentage, 2) }}%
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('Labour.edit', $labour->id) }}" class="btn btn-outline-secondary" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger"
                                                        onclick="confirmDelete('deleteLabourForm{{ $labour->id }}', 'Delete Labour', 'Are you sure you want to delete {{ addslashes($labour->labourName) }}?')"
                                                        title="Delete">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </div>
                                            <form id="deleteLabourForm{{ $labour->id }}" action="{{ route('Labour.destroy', $labour->id) }}" method="POST" class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            <div class="fw-semibold mb-1">No Labour Found</div>
                                            <p class="small mb-3">Start by adding your first labour record.</p>
                                            <a href="{{ route('Labour.create') }}" class="btn btn-sm text-white" style="background:#0f2747;">
                                                <i class="bi bi-plus-circle me-1"></i>Add New Labour
                                            </a>
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

@push('styles')
<style>
    .lm-kpi {
        display: flex; align-items: center; gap: 0.75rem;
        background: #fff; border: 1px solid #d5dde8; border-left: 4px solid #0f2747;
        border-radius: 4px; padding: 0.85rem 1rem; height: 100%;
        box-shadow: 0 2px 8px rgba(15, 39, 71, 0.05);
    }
    .lm-kpi.salary { border-left-color: #198754; background: #eefaf2; }
    .lm-kpi.adv { border-left-color: #c47a12; background: #fff8ee; }
    .lm-kpi.bal { border-left-color: #1a4a8a; background: #f4f7fb; }
    .lm-kpi-icon {
        width: 42px; height: 42px; border-radius: 6px; background: #e8eef5; color: #0f2747;
        display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;
    }
    .lm-kpi-icon.salary { background: #e8f5e9; color: #198754; }
    .lm-kpi-icon.adv { background: #fff0db; color: #c47a12; }
    .lm-kpi-icon.bal { background: #e8eef5; color: #1a4a8a; }
    .lm-kpi-lbl {
        font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.3px; color: #6b7c8d; margin-bottom: 0.15rem;
    }
    .lm-kpi-val { font-size: 1.05rem; font-weight: 700; color: #0f2747; line-height: 1.2; }
    .lm-card {
        box-shadow: 0 2px 12px rgba(15, 39, 71, 0.08);
        border-radius: 4px; overflow: hidden; border: 1px solid #d5dde8;
    }
    .lm-card-head {
        background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
        border: none; padding: 0.85rem 1rem;
    }
    .lm-search { max-width: 240px; }
    .lm-search .input-group-text,
    .lm-search .form-control { background: rgba(255,255,255,0.95); border-color: transparent; }
    .lm-table thead th {
        background: #0f2747; color: #fff; font-size: 0.68rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.3px; border-color: #0a1c33;
        white-space: nowrap; vertical-align: middle; padding: 0.55rem 0.65rem;
    }
    .lm-table tbody td {
        font-size: 0.82rem; border-color: #e8eef5; vertical-align: middle; padding: 0.55rem 0.65rem;
    }
    .lm-table tbody tr:nth-child(even) td { background: #f4f7fb; }
    .lm-table tbody tr:hover td { background: #e8eef5; }
    .lm-code {
        display: inline-block; padding: 0.15rem 0.45rem; border-radius: 3px;
        background: #e8eef5; color: #0f2747; font-weight: 700; font-size: 0.75rem;
    }
    .lm-badge {
        display: inline-block; padding: 0.15rem 0.5rem; border-radius: 3px;
        background: #e8eef5; color: #1a4a8a; font-size: 0.72rem; font-weight: 600;
        text-transform: capitalize;
    }
    .lm-badge.ajent { background: #e8f0fe; color: #1a4a8a; }
    .lm-badge.worker { background: #e8f5e9; color: #198754; }
    .lm-badge.fitter { background: #fff0db; color: #c47a12; }
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
$(document).ready(function () {
    const tableBody = $('#labourTable tbody tr');
    const hasData = tableBody.length > 0 && tableBody.first().find('td').length > 1;

    if (hasData) {
        const table = $('#labourTable').DataTable({
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            paging: true,
            info: true,
            searching: true,
            ordering: true,
            order: [[2, 'asc']],
            language: {
                lengthMenu: 'Show _MENU_',
                info: 'Showing _START_–_END_ of _TOTAL_ labour',
                infoEmpty: 'No labour',
                infoFiltered: '(filtered from _MAX_)',
                zeroRecords: 'No matching labour found',
                paginate: { previous: '‹', next: '›' }
            },
            columnDefs: [
                { orderable: false, targets: [0, 8] }
            ],
            dom: '<"row px-2 pt-2"<"col-sm-12 col-md-6"l>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
        });

        $('#globalSearch').on('keyup', function () {
            table.search(this.value).draw();
        });

        $('.dataTables_length select').addClass('form-select form-select-sm');
    } else {
        $('#globalSearch').on('keyup', function () {
            const value = this.value.toLowerCase();
            $('#labourTable tbody tr').filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    }

    setTimeout(function () {
        $('.alert').fadeOut('slow');
    }, 5000);

    @if (session('error'))
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'warning',
            title: 'Cannot Delete',
            text: @json(session('error')),
            confirmButtonText: 'OK',
            confirmButtonColor: '#0f2747'
                });
            }
        @endif
    });
</script>

@endsection
