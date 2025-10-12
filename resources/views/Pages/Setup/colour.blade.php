@extends('layout.app')

@section('title', 'Colour Management')

@section('content')

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold">
                        <i class="bi bi-palette-fill text-primary me-2"></i>
                        Colour Management
                    </h3>
                    <p class="text-muted small mb-0">Manage product colours</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item">Setup</li>
                        <li class="breadcrumb-item active" aria-current="page">Colours</li>
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
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                                <i class="bi bi-palette-fill text-primary fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1 small">Total Colours</h6>
                                            <h3 class="mb-0 fw-bold">{{ $colours->count() }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                                <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1 small">Active Colours</h6>
                                            <h3 class="mb-0 fw-bold">{{ $colours->count() }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                                <i class="bi bi-paint-bucket text-info fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1 small">Recently Added</h6>
                                            <h3 class="mb-0 fw-bold">{{ $colours->take(5)->count() }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Colour Table Card -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h5 class="mb-0 fw-semibold">
                                        <i class="bi bi-table me-2"></i>
                                        Colour List
                                    </h5>
                                </div>
                                <div class="col-md-6 text-end">
                                    <a href="{{ route('colours.create') }}" 
                                       class="btn btn-primary btn-sm shadow-sm">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        Add New Colour
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="colourTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 60px;"><small class="text-muted fw-semibold">#</small></th>
                                            <th><small class="text-muted fw-semibold">COLOUR NAME</small></th>
                                            <th class="text-center" style="width: 150px;"><small class="text-muted fw-semibold">ACTIONS</small></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($colours as $index => $colour)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle me-3" 
                                                         style="width: 30px; height: 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: 2px solid #f8f9fa;">
                                                    </div>
                                                    <div>
                                                        <span class="fw-semibold">{{ $colour->colourName }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('colours.edit', $colour->id) }}" 
                                                       class="btn btn-outline-warning"
                                                       title="Edit Colour">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger" 
                                                            onclick="confirmDelete('deleteForm{{ $colour->id }}', 'Delete Colour', 'Are you sure you want to delete {{ $colour->colourName }}?')"
                                                            title="Delete Colour">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </div>

                                                <form id="deleteForm{{ $colour->id }}" 
                                                      action="{{ route('colours.destroy', $colour->id) }}" 
                                                      method="POST" 
                                                      class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                                    <h5>No Colours Found</h5>
                                                    <p>Start by adding your first colour.</p>
                                                    <a href="{{ route('colours.create') }}" 
                                                       class="btn btn-primary btn-sm mt-2">
                                                        <i class="bi bi-plus-circle me-1"></i>
                                                        Add New Colour
                                                    </a>
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
        </div>
    </div>
</main>

@push('styles')
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

/* DataTables Custom Styling */
.dataTables_wrapper .dataTables_filter {
    float: right;
}

.dataTables_wrapper .dataTables_filter input {
    margin-left: 0.5em;
    border-radius: 6px;
}

.dataTables_wrapper .dataTables_length select {
    margin: 0 0.5em;
    border-radius: 6px;
}

.dataTables_wrapper .dataTables_info {
    padding-top: 0.85em;
    color: #6c757d;
    font-size: 0.875rem;
}

.dataTables_wrapper .dataTables_paginate {
    padding-top: 0.5em;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.25rem 0.5rem;
    margin: 0 2px;
    border-radius: 4px;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(180deg, #0d6efd 0%, #0a58ca 100%);
    color: white !important;
    border: 1px solid #0d6efd;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: linear-gradient(180deg, #0d6efd 0%, #0a58ca 100%);
    color: white !important;
    border: 1px solid #0d6efd;
}

.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        float: none;
        text-align: center;
        margin-bottom: 0.5rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    @if($colours->count() > 0)
    // Initialize DataTable
    const table = $('#colourTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        order: [[1, 'asc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search colours...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ colours",
            infoEmpty: "No colours available",
            infoFiltered: "(filtered from _MAX_ total colours)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            },
            emptyTable: `
                <div class="text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                    <h5>No Colours Found</h5>
                    <p>Start by adding your first colour.</p>
                </div>
            `
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        columnDefs: [
            { orderable: false, targets: [0, 2] },
            { className: "text-center", targets: [0, 2] }
        ],
        drawCallback: function() {
            $('#colourTable tbody tr').each(function(index) {
                $(this).css({
                    'animation': 'fadeIn 0.3s ease-in',
                    'animation-delay': (index * 0.05) + 's'
                });
            });
        }
    });

    // Custom search styling
    $('.dataTables_filter input').addClass('form-control form-control-sm').attr('placeholder', 'Search colours...');
    $('.dataTables_length select').addClass('form-select form-select-sm');
    $('.dataTables_wrapper .pagination').addClass('pagination-sm');
    
    @endif

    // Auto-dismiss alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>
@endpush

@endsection

