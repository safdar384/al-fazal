@extends('layout.app')

@section('title', 'Customer Areas')

@section('content')

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold">
                        <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                        Customer Areas
                    </h3>
                    <p class="text-muted small mb-0">Manage geographical service areas</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Areas</li>
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
                                                <i class="bi bi-geo-alt-fill text-primary fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1 small">Total Areas</h6>
                                            <h3 class="mb-0 fw-bold">{{ count($areas) }}</h3>
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
                                                <i class="bi bi-people-fill text-success fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1 small">Areas with Agents</h6>
                                            <h3 class="mb-0 fw-bold">{{ $areas->where('ajentName', '!=', null)->count() }}</h3>
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
                                                <i class="bi bi-map text-info fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1 small">Coverage</h6>
                                            <h3 class="mb-0 fw-bold">Pakistan</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Areas Table Card -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" 
                                               id="areaSearch" 
                                               class="form-control border-start-0 ps-0" 
                                               placeholder="Search areas by code or name...">
                                    </div>
                                </div>
                                <div class="col-md-8 text-end mt-2 mt-md-0">
                                    <a href="{{ route('areas.create') }}" 
                                       class="btn btn-primary btn-sm shadow-sm">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        Add New Area
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="areaTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 60px;">
                                                <small class="text-muted fw-semibold">#</small>
                                            </th>
                                            <th><small class="text-muted fw-semibold">AREA CODE</small></th>
                                            <th><small class="text-muted fw-semibold">AREA NAME</small></th>
                                            <th><small class="text-muted fw-semibold">AGENT NAME</small></th>
                                            <th class="text-center" style="width: 150px;"><small class="text-muted fw-semibold">ACTIONS</small></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($areas as $index => $area)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold" style="font-size: 1rem;">
                                                    {{ $area->areaCode }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-opacity-10 p-2 rounded-circle me-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="bi bi-geo-alt-fill text-primary"></i>
                                                    </div>
                                                    <div class="fw-semibold">{{ $area->areaName }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($area->ajentName)
                                                <span class="text-muted">
                                                    <i class="bi bi-person me-1"></i>
                                                    {{ $area->ajentName }}
                                                </span>
                                                @else
                                                <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('areas.edit', $area->id) }}" 
                                                       class="btn btn-outline-warning"
                                                       title="Edit Area">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger" 
                                                            onclick="confirmDelete('deleteForm{{ $area->id }}', 'Delete Area', 'Are you sure you want to delete area {{ $area->areaName }}? This action cannot be undone.')"
                                                            title="Delete Area">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </div>

                                                <form id="deleteForm{{ $area->id }}" 
                                                      action="{{ route('areas.destroy', $area->id) }}" 
                                                      method="POST" 
                                                      class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                                    <h5>No Areas Found</h5>
                                                    <p>Start by adding your first customer area.</p>
                                                    <a href="{{ route('areas.create') }}" 
                                                       class="btn btn-primary btn-sm mt-2">
                                                        <i class="bi bi-plus-circle me-1"></i>
                                                        Add New Area
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if($areas->count() > 0)
                        <div class="card-footer bg-white border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    Showing {{ $areas->count() }} area(s)
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

#areaSearch:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
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
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("areaSearch");
    const table = document.getElementById("areaTable");
    const tbody = table.getElementsByTagName("tbody")[0];

    function filterTable() {
        const searchValue = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        Array.from(tbody.rows).forEach(row => {
            if (row.cells.length === 1) return;

            const areaCode = row.cells[1].textContent.toLowerCase();
            const areaName = row.cells[2].textContent.toLowerCase();
            const agentName = row.cells[3].textContent.toLowerCase();

            const match = areaCode.includes(searchValue) || 
                         areaName.includes(searchValue) || 
                         agentName.includes(searchValue);

            row.style.display = match ? "" : "none";
            if (match) visibleCount++;
        });

        if (visibleCount === 0 && searchValue !== '') {
            if (!document.getElementById('noResultsRow')) {
                const noResultsRow = tbody.insertRow(0);
                noResultsRow.id = 'noResultsRow';
                noResultsRow.innerHTML = `
                    <td colspan="5" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-search fs-1 d-block mb-3"></i>
                            <h5>No Results Found</h5>
                            <p>No areas match your search criteria.</p>
                        </div>
                    </td>
                `;
            }
        } else {
            const noResultsRow = document.getElementById('noResultsRow');
            if (noResultsRow) noResultsRow.remove();
        }
    }

    searchInput.addEventListener("input", filterTable);

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

@endsection

