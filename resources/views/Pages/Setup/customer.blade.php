@extends('layout.app')

@section('title', 'Customer Management')

@section('content')

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center gy-2">
                <div class="col-12 col-md-6">
                    <h3 class="mb-0 fw-bold" style="color:#0f2747;">
                        <i class="bi bi-people me-2"></i>Customer Management
                    </h3>
                    <p class="text-muted small mb-0">Accounts · Customers / Suppliers</p>
                </div>
                <div class="col-12 col-md-6">
                    <ol class="breadcrumb float-md-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><span class="text-muted">Accounts</span></li>
                        <li class="breadcrumb-item active" aria-current="page">Customers</li>
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
                    <div class="cm-kpi">
                        <div class="cm-kpi-icon"><i class="bi bi-people"></i></div>
                        <div>
                            <div class="cm-kpi-lbl">Total Customers</div>
                            <div class="cm-kpi-val">{{ number_format(count($customers)) }}</div>
                                        </div>
                                    </div>
                                </div>
                <div class="col-6 col-xl-3">
                    <div class="cm-kpi recv">
                        <div class="cm-kpi-icon recv"><i class="bi bi-cash-stack"></i></div>
                        <div>
                            <div class="cm-kpi-lbl">Total Receivable</div>
                            <div class="cm-kpi-val text-success">Rs. {{ number_format($portfolio['total_receivable'] ?? 0, 2) }}</div>
                        </div>
                                            </div>
                                        </div>
                <div class="col-6 col-xl-3">
                    <div class="cm-kpi pay">
                        <div class="cm-kpi-icon pay"><i class="bi bi-wallet2"></i></div>
                        <div>
                            <div class="cm-kpi-lbl">Total Payable</div>
                            <div class="cm-kpi-val text-danger">Rs. {{ number_format($portfolio['total_payable'] ?? 0, 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                <div class="col-6 col-xl-3">
                    <div class="cm-kpi net">
                        <div class="cm-kpi-icon net"><i class="bi bi-calculator"></i></div>
                        <div>
                            <div class="cm-kpi-lbl">Net (Pay − Recv)</div>
                            <div class="cm-kpi-val {{ ($portfolio['net_balance'] ?? 0) >= 0 ? 'text-danger' : 'text-success' }}">
                                Rs. {{ number_format($portfolio['net_balance'] ?? 0, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

            <div class="card cm-card border-0">
                <div class="card-header cm-card-head">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h6 class="mb-0 text-white fw-semibold">
                            <i class="bi bi-table me-2"></i>Customer List
                        </h6>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="input-group input-group-sm cm-search">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" id="globalSearch" class="form-control" placeholder="Search customers...">
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-light" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i>Print
                            </button>
                            <a href="{{ route('Customer.create') }}" class="btn btn-sm btn-light fw-semibold">
                                <i class="bi bi-plus-circle me-1"></i>Add Customer
                            </a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 cm-table" id="customerTable">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:50px;">#</th>
                                    <th>Area</th>
                                    <th>Code</th>
                                    <th>Business Name</th>
                                    <th>City</th>
                                    <th>Contact</th>
                                    <th class="text-end">Receivable</th>
                                    <th class="text-end">Payable</th>
                                    <th class="text-center" style="width:120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($customers as $index => $customer)
                                        <tr>
                                        <td class="text-center text-muted small">{{ $index + 1 }}</td>
                                        <td>
                                            @if($customer->custArea)
                                                <span class="cm-badge">{{ $customer->custArea }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                            </td>
                                        <td><span class="cm-code">{{ $customer->custCode }}</span></td>
                                            <td>
                                            <div class="fw-semibold" style="color:#0f2747;">{{ $customer->bussnessName }}</div>
                                            <small class="text-muted"><i class="bi bi-person"></i> {{ $customer->custName }}</small>
                                            </td>
                                        <td class="small">{{ $customer->custCity ?: '—' }}</td>
                                        <td class="small">{{ $customer->custContect ?: '—' }}</td>
                                            <td class="text-end">
                                                @if ((float) ($customer->receivable_balance ?? 0) > 0)
                                                    <span class="fw-bold text-success">Rs. {{ number_format((float) $customer->receivable_balance, 2) }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if ((float) ($customer->payable_balance ?? 0) > 0)
                                                    <span class="fw-bold text-danger">Rs. {{ number_format((float) $customer->payable_balance, 2) }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('Customer.edit', $customer->id) }}" class="btn btn-outline-secondary" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                <button type="button" class="btn btn-outline-danger"
                                                        onclick="confirmDelete('deleteForm{{ $customer->id }}', 'Delete Customer', 'Are you sure you want to delete {{ addslashes($customer->bussnessName) }}?')"
                                                        title="Delete">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </div>
                                            <form id="deleteForm{{ $customer->id }}" action="{{ route('Customer.destroy', $customer->id) }}" method="POST" class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            <div class="fw-semibold mb-1">No Customers Found</div>
                                            <p class="small mb-3">Start by adding your first customer.</p>
                                            <a href="{{ route('Customer.create') }}" class="btn btn-sm text-white" style="background:#0f2747;">
                                                <i class="bi bi-plus-circle me-1"></i>Add New Customer
                                            </a>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                    @if($customers->count() > 0)
                                <tfoot>
                                        <tr>
                                        <td colspan="6" class="text-end">TOTAL</td>
                                            <td class="text-end text-success">Rs. {{ number_format($portfolio['total_receivable'] ?? 0, 2) }}</td>
                                            <td class="text-end text-danger">Rs. {{ number_format($portfolio['total_payable'] ?? 0, 2) }}</td>
                                            <td></td>
                                        </tr>
                                    <tr>
                                        <td colspan="6" class="text-end">BALANCE (Payable − Receivable)</td>
                                            <td colspan="2" class="text-end {{ ($portfolio['net_balance'] ?? 0) >= 0 ? 'text-danger' : 'text-success' }}">
                                                Rs. {{ number_format($portfolio['net_balance'] ?? 0, 2) }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                    @endif
                                </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@push('styles')
<style>
    .cm-kpi {
        display: flex; align-items: center; gap: 0.75rem;
        background: #fff; border: 1px solid #d5dde8; border-left: 4px solid #0f2747;
        border-radius: 4px; padding: 0.85rem 1rem; height: 100%;
        box-shadow: 0 2px 8px rgba(15, 39, 71, 0.05);
    }
    .cm-kpi.recv { border-left-color: #198754; background: #eefaf2; }
    .cm-kpi.pay { border-left-color: #dc3545; background: #fdf0f1; }
    .cm-kpi.net { border-left-color: #1a4a8a; background: #f4f7fb; }
    .cm-kpi-icon {
        width: 42px; height: 42px; border-radius: 6px; background: #e8eef5; color: #0f2747;
        display: flex; align-items: center; justify-content: center; font-size: 1.15rem; flex-shrink: 0;
    }
    .cm-kpi-icon.recv { background: #e8f5e9; color: #198754; }
    .cm-kpi-icon.pay { background: #fde8ea; color: #dc3545; }
    .cm-kpi-icon.net { background: #e8eef5; color: #1a4a8a; }
    .cm-kpi-lbl {
        font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.3px; color: #6b7c8d; margin-bottom: 0.15rem;
    }
    .cm-kpi-val { font-size: 1.05rem; font-weight: 700; color: #0f2747; line-height: 1.2; }
    .cm-card {
        box-shadow: 0 2px 12px rgba(15, 39, 71, 0.08);
        border-radius: 4px; overflow: hidden; border: 1px solid #d5dde8;
    }
    .cm-card-head {
        background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
        border: none; padding: 0.85rem 1rem;
    }
    .cm-search { max-width: 240px; }
    .cm-search .input-group-text,
    .cm-search .form-control { background: rgba(255,255,255,0.95); border-color: transparent; }
    .cm-table thead th {
        background: #0f2747; color: #fff; font-size: 0.68rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.3px; border-color: #0a1c33;
        white-space: nowrap; vertical-align: middle; padding: 0.55rem 0.65rem;
    }
    .cm-table tbody td {
        font-size: 0.82rem; border-color: #e8eef5; vertical-align: middle; padding: 0.55rem 0.65rem;
    }
    .cm-table tbody tr:nth-child(even) td { background: #f4f7fb; }
    .cm-table tbody tr:hover td { background: #e8eef5; }
    .cm-table tfoot td {
        background: #e8eef5; font-weight: 700; color: #0f2747;
        border-color: #cfd8e3; font-size: 0.82rem; padding: 0.55rem 0.65rem;
    }
    .cm-code {
        display: inline-block; padding: 0.15rem 0.45rem; border-radius: 3px;
        background: #e8eef5; color: #0f2747; font-weight: 700; font-size: 0.75rem;
    }
    .cm-badge {
        display: inline-block; padding: 0.15rem 0.45rem; border-radius: 3px;
        background: #e8eef5; color: #1a4a8a; font-size: 0.72rem; font-weight: 600;
    }
    .dataTables_wrapper .dataTables_filter { display: none; }
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate { padding: 0.65rem 1rem; font-size: 0.8rem; }
    .dataTables_wrapper .page-item.active .page-link {
        background: #0f2747; border-color: #0f2747;
    }
    .dataTables_wrapper .page-link { color: #0f2747; }
    @media print {
        .btn, .breadcrumb, .cm-card-head, .cm-search, .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_paginate, .dataTables_wrapper .dataTables_info { display: none !important; }
        .cm-card { box-shadow: none !important; border: none !important; }
}
</style>
@endpush

<script>
$(document).ready(function () {
    const tableBody = $('#customerTable tbody tr');
    const hasData = tableBody.length > 0 && tableBody.first().find('td').length > 1;
    
    if (hasData) {
        const table = $('#customerTable').DataTable({
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            paging: true,
            info: true,
            searching: true,
            ordering: true,
            order: [[3, 'asc']],
            language: {
                lengthMenu: 'Show _MENU_',
                info: 'Showing _START_–_END_ of _TOTAL_ customers',
                infoEmpty: 'No customers',
                infoFiltered: '(filtered from _MAX_)',
                zeroRecords: 'No matching customers found',
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
            $('#customerTable tbody tr').filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    }

    setTimeout(function () {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

@endsection
