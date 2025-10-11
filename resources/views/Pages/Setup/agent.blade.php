@extends('layout.app')

@section('title', 'Agent Management')

@section('content')

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold">
                        <i class="bi bi-people-fill text-primary me-2"></i>
                        Agent Management
                    </h3>
                    <p class="text-muted small mb-0">Manage your agents and their information</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Agents</li>
                    </ol>
                </div>
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content Header-->

    <!--begin::App Content-->
    <div class="app-content">
        <!--begin::Container-->
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

            <!--begin::Row-->
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
                                                <i class="bi bi-people-fill text-primary fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1 small">Total Agents</h6>
                                            <h3 class="mb-0 fw-bold">{{ count($agents) }}</h3>
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
                                            <h6 class="text-muted mb-1 small">Total Balance</h6>
                                            <h3 class="mb-0 fw-bold">Rs. {{ number_format($agents->sum('ajentBalance')) }}</h3>
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
                                                <i class="bi bi-wallet2 text-warning fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1 small">Total Advance</h6>
                                            <h3 class="mb-0 fw-bold">Rs. {{ number_format($agents->sum('AjentAdvance')) }}</h3>
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
                                            <h6 class="text-muted mb-1 small">Avg Commission</h6>
                                            <h3 class="mb-0 fw-bold">{{ number_format($agents->avg('ajentCommission'), 2) }}%</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Agent Table Card -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" 
                                               id="agentSearch" 
                                               class="form-control border-start-0 ps-0" 
                                               placeholder="Search agents by name, code or contact...">
                                    </div>
                                </div>
                                <div class="col-md-8 text-end mt-2 mt-md-0">
                                    <a href="{{ route('NewAgent.create') }}" 
                                       class="btn btn-primary btn-sm shadow-sm">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        Add New Agent
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
                                <table class="table table-hover align-middle mb-0" id="agentTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 60px;">
                                                <small class="text-muted fw-semibold">#</small>
                                            </th>
                                            <th>
                                                <small class="text-muted fw-semibold">AGENT CODE</small>
                                            </th>
                                            <th>
                                                <small class="text-muted fw-semibold">AGENT NAME</small>
                                            </th>
                                            <th>
                                                <small class="text-muted fw-semibold">CONTACT</small>
                                            </th>
                                            <th class="text-end">
                                                <small class="text-muted fw-semibold">ADVANCE</small>
                                            </th>
                                            <th class="text-center">
                                                <small class="text-muted fw-semibold">COMMISSION</small>
                                            </th>
                                            <th class="text-end">
                                                <small class="text-muted fw-semibold">BALANCE</small>
                                            </th>
                                            <th class="text-center" style="width: 150px;">
                                                <small class="text-muted fw-semibold">ACTIONS</small>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($agents as $index => $agent)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">
                                                    {{ $agent->ajentCode }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <span class="text-primary fw-bold">{{ substr($agent->ajentName, 0, 1) }}</span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ $agent->ajentName }}</div>
                                                        @if($agent->ajentAdderss)
                                                        <small class="text-muted">
                                                            <i class="bi bi-geo-alt"></i>
                                                            {{ Str::limit($agent->ajentAdderss, 30) }}
                                                        </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($agent->ajentContact)
                                                <span class="text-muted">
                                                    <i class="bi bi-telephone me-1"></i>
                                                    {{ $agent->ajentContact }}
                                                </span>
                                                @else
                                                <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-semibold">Rs. {{ number_format($agent->AjentAdvance, 2) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info bg-opacity-10 text-info">
                                                    {{ $agent->ajentCommission }}%
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold {{ $agent->ajentBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                                    Rs. {{ number_format($agent->ajentBalance, 2) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('NewAgent.edit', $agent->id) }}" 
                                                       class="btn btn-outline-warning"
                                                       title="Edit Agent">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger" 
                                                            onclick="confirmDelete('deleteForm{{ $agent->id }}', 'Delete Agent', 'Are you sure you want to delete {{ $agent->ajentName }}? This action cannot be undone.')"
                                                            title="Delete Agent">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </div>

                                                <form id="deleteForm{{ $agent->id }}" 
                                                      action="{{ route('NewAgent.destroy', $agent->id) }}" 
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
                                                    <h5>No Agents Found</h5>
                                                    <p>Start by adding your first agent.</p>
                                                    <a href="{{ route('NewAgent.create') }}" 
                                                       class="btn btn-primary btn-sm mt-2">
                                                        <i class="bi bi-plus-circle me-1"></i>
                                                        Add New Agent
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if($agents->count() > 0)
                        <div class="card-footer bg-white border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    Showing {{ $agents->count() }} agent(s)
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
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content-->
</main>

<style>
/* Custom Styles for Elegant UI */
.avatar-sm {
    width: 36px;
    height: 36px;
}

.table-hover tbody tr {
    transition: all 0.2s ease;
}

.table-hover tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
}

.card {
    transition: all 0.3s ease;
}

#agentSearch:focus {
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

/* Loading Animation */
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
    const searchInput = document.getElementById("agentSearch");
    const table = document.getElementById("agentTable");
    const tbody = table.getElementsByTagName("tbody")[0];

    // Search functionality
    function filterTable() {
        const searchValue = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        Array.from(tbody.rows).forEach(row => {
            // Skip empty state row
            if (row.cells.length === 1) return;

            const agentCode = row.cells[1].textContent.toLowerCase();
            const agentName = row.cells[2].textContent.toLowerCase();
            const contact = row.cells[3].textContent.toLowerCase();

            const match = agentCode.includes(searchValue) || 
                         agentName.includes(searchValue) || 
                         contact.includes(searchValue);

            row.style.display = match ? "" : "none";
            if (match) visibleCount++;
        });

        // Show "no results" message if needed
        if (visibleCount === 0 && searchValue !== '') {
            if (!document.getElementById('noResultsRow')) {
                const noResultsRow = tbody.insertRow(0);
                noResultsRow.id = 'noResultsRow';
                noResultsRow.innerHTML = `
                    <td colspan="8" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bi bi-search fs-1 d-block mb-3"></i>
                            <h5>No Results Found</h5>
                            <p>No agents match your search criteria.</p>
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

    // Auto-dismiss alerts after 5 seconds
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
