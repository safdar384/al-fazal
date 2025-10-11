@extends('layout.app')

@section('title', 'Customer Management')

@section('content')

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold">
                        <i class="bi bi-shop text-primary me-2"></i>
                        Customer Management
                    </h3>
                    <p class="text-muted small mb-0">Manage your customers and business partners</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Customers</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            
            <!-- Success/Error Messages -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                                <i class="bi bi-shop text-primary fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1 small">Total Customers</h6>
                                            <h3 class="mb-0 fw-bold">{{ count($customers) }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                                <i class="bi bi-cash-stack text-success fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1 small">Total Receivable</h6>
                                            <h3 class="mb-0 fw-bold">Rs. {{ number_format($customers->sum('custBalance')) }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                                <i class="bi bi-credit-card text-warning fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1 small">Total Credit Limit</h6>
                                            <h3 class="mb-0 fw-bold">Rs. {{ number_format($customers->sum('custCreditLimit')) }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                                <i class="bi bi-percent text-info fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1 small">Avg Discount</h6>
                                            <h3 class="mb-0 fw-bold">{{ number_format($customers->avg('custdiscount'), 2) }}%</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Customer Table Card -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" 
                                               id="globalSearch" 
                                               class="form-control border-start-0 ps-0" 
                                               placeholder="Search customers...">
                                    </div>
                                </div>
                                <div class="col-md-9 text-end mt-2 mt-md-0">
                                    <a href="{{ route('Customer.create') }}" 
                                       class="btn btn-primary btn-sm shadow-sm">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        Add New Customer
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-secondary btn-sm" 
                                            onclick="window.print()">
                                        <i class="bi bi-printer me-1"></i>
                                        Print
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="customerTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 60px;"><small class="text-muted fw-semibold">#</small></th>
                                            <th><small class="text-muted fw-semibold">AREA</small></th>
                                            <th><small class="text-muted fw-semibold">CODE</small></th>
                                            <th><small class="text-muted fw-semibold">BUSINESS NAME</small></th>
                                            <th><small class="text-muted fw-semibold">CITY</small></th>
                                            <th><small class="text-muted fw-semibold">CONTACT</small></th>
                                            <th class="text-end"><small class="text-muted fw-semibold">BALANCE</small></th>
                                            <th class="text-center" style="width: 150px;"><small class="text-muted fw-semibold">ACTIONS</small></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($customers as $index => $customer)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary bg-opacity-75 text-white">
                                                    {{ $customer->custArea }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">
                                                    {{ $customer->custCode }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold">{{ $customer->bussnessName }}</div>
                                                    <small class="text-muted">
                                                        <i class="bi bi-person"></i>
                                                        {{ $customer->custName }}
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    <i class="bi bi-geo-alt me-1"></i>
                                                    {{ $customer->custCity }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">
                                                    <i class="bi bi-telephone me-1"></i>
                                                    {{ $customer->custContect }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold {{ $customer->custBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                                    Rs. {{ number_format($customer->custBalance, 2) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('Customer.edit', $customer->id) }}" 
                                                       class="btn btn-outline-warning"
                                                       title="Edit Customer">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger" 
                                                            onclick="confirmDelete('deleteForm{{ $customer->id }}', 'Delete Customer', 'Are you sure you want to delete {{ $customer->bussnessName }}? This action cannot be undone.')"
                                                            title="Delete Customer">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </div>

                                                <form id="deleteForm{{ $customer->id }}" 
                                                      action="{{ route('Customer.destroy', $customer->id) }}" 
                                                      method="POST" 
                                                      class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                                    <h5>No Customers Found</h5>
                                                    <p>Start by adding your first customer.</p>
                                                    <a href="{{ route('Customer.create') }}" 
                                                       class="btn btn-primary btn-sm mt-2">
                                                        <i class="bi bi-plus-circle me-1"></i>
                                                        Add New Customer
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if($customers->count() > 0)
                        <div class="card-footer bg-white border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    Showing {{ $customers->count() }} customer(s)
                                </div>
                                <div class="text-muted small">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Last updated: {{ now()->format('d M Y, h:i A') }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.table-hover tbody tr {
    transition: all 0.2s ease;
}

.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.card {
    transition: all 0.3s ease;
}

#globalSearch:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

.input-group-text {
    background-color: transparent;
}

@media print {
    .btn, .breadcrumb, .card-header {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}

.table tbody tr {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
$(document).ready(function() {
    // Check if table has data rows before initializing DataTable
    const tableBody = $('#customerTable tbody tr');
    const hasData = tableBody.length > 0 && tableBody.first().find('td').length > 1;
    
    if (hasData) {
        // Initialize DataTable
        var table = $('#customerTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [5, 10, 25, 50, 100],
            "paging": true,
            "info": true,
            "searching": true,
            "ordering": true,
            "order": [[3, 'asc']], // Sort by business name
            "language": {
                "search": "",
                "searchPlaceholder": "Search in table...",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ customers",
                "infoEmpty": "No customers available",
                "infoFiltered": "(filtered from _MAX_ total customers)",
                "zeroRecords": "No matching customers found"
            },
            "columnDefs": [
                { "orderable": false, "targets": [0, 7] } // Disable sorting on # and Actions
            ]
        });

        // Global search
        $('#globalSearch').on('keyup', function() {
            table.search(this.value).draw();
        });
    } else {
        // Simple search for empty table
        $('#globalSearch').on('keyup', function() {
            const value = this.value.toLowerCase();
            $('#customerTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    }

    // Auto-dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

@endsection
