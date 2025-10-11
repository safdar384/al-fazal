@extends('layout.app')

@section('title', 'Product Management')

@section('content')

<main class="app-main" id="main" tabindex="-1">
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0 fw-bold">
                        <i class="bi bi-box-seam text-primary me-2"></i>
                        Product Management
                    </h3>
                    <p class="text-muted small mb-0">Manage your product inventory</p>
                </div>
              <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Products</li>
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
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                                <i class="bi bi-box-seam text-primary fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1 small">Total Products</h6>
                                            <h3 class="mb-0 fw-bold">{{ count($products) }}</h3>
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
                                                <i class="bi bi-boxes text-success fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1 small">Total Stock</h6>
                                            <h3 class="mb-0 fw-bold">{{ number_format($products->sum('stockQty')) }}</h3>
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
                                                <i class="bi bi-cash-stack text-warning fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1 small">Stock Value</h6>
                                            <h3 class="mb-0 fw-bold">Rs. {{ number_format($products->sum(function($p) { return $p->costRate * $p->stockQty; })) }}</h3>
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
                                            <div class="bg-danger bg-opacity-10 p-3 rounded">
                                                <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="text-muted mb-1 small">Low Stock Items</h6>
                                            <h3 class="mb-0 fw-bold">{{ $products->filter(function($p) { return $p->stockQty <= $p->lessQty; })->count() }}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Product Table Card -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h5 class="mb-0 fw-semibold">
                                        <i class="bi bi-table me-2"></i>
                                        Product List
                                    </h5>
                                </div>
                                <div class="col-md-6 text-end">
                                    <a href="{{ route('product.create') }}" 
                                       class="btn btn-primary btn-sm shadow-sm">
                                        <i class="bi bi-plus-circle me-1"></i>
                                        Add New Product
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="productTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 60px;"><small class="text-muted fw-semibold">#</small></th>
                                            <th><small class="text-muted fw-semibold">CODE</small></th>
                                            <th><small class="text-muted fw-semibold">PRODUCT NAME</small></th>
                                            <th><small class="text-muted fw-semibold">COMPANY</small></th>
                                            <th><small class="text-muted fw-semibold">CATEGORY</small></th>
                                            <th class="text-end"><small class="text-muted fw-semibold">SALE RATE</small></th>
                                            <th class="text-center"><small class="text-muted fw-semibold">STOCK</small></th>
                                            <th class="text-center" style="width: 150px;"><small class="text-muted fw-semibold">ACTIONS</small></th>
                        </tr>
                        </thead>
                        <tbody>
                                        @forelse($products as $index => $product)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">
                                                    {{ $product->productCode }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold">{{ $product->productName }}</div>
                                                    <small class="text-muted">
                                                        <i class="bi bi-palette"></i> {{ $product->colourName }} | 
                                                        <i class="bi bi-box"></i> {{ $product->pakingName }}
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $product->companyName }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info bg-opacity-10 text-info">
                                                    {{ $product->catagoryName }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold text-success">Rs. {{ number_format($product->saleRate, 2) }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($product->stockQty <= $product->lessQty)
                                                    <span class="badge bg-danger">{{ $product->stockQty }}</span>
                                                @elseif($product->stockQty <= ($product->lessQty * 2))
                                                    <span class="badge bg-warning">{{ $product->stockQty }}</span>
                                                @else
                                                    <span class="badge bg-success">{{ $product->stockQty }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('product.edit', $product->id) }}" 
                                                       class="btn btn-outline-warning"
                                                       title="Edit Product">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger" 
                                                            onclick="confirmDelete('deleteForm{{ $product->id }}', 'Delete Product', 'Are you sure you want to delete {{ $product->productName }}?')"
                                                            title="Delete Product">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </div>

                                                <form id="deleteForm{{ $product->id }}" 
                                                      action="{{ route('product.destroy', $product->id) }}" 
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
                                                    <h5>No Products Found</h5>
                                                    <p>Start by adding your first product.</p>
                                                    <a href="{{ route('product.create') }}" 
                                                       class="btn btn-primary btn-sm mt-2">
                                                        <i class="bi bi-plus-circle me-1"></i>
                                                        Add New Product
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

/* Responsive adjustments */
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
    @if($products->count() > 0)
    // Initialize DataTable
    const table = $('#productTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        order: [[1, 'asc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search products...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ products",
            infoEmpty: "No products available",
            infoFiltered: "(filtered from _MAX_ total products)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            },
            emptyTable: `
                <div class="text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                    <h5>No Products Found</h5>
                    <p>Start by adding your first product.</p>
                </div>
            `
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        columnDefs: [
            { orderable: false, targets: [0, 7] }, // Disable sorting on # and Actions columns
            { className: "text-center", targets: [0, 6, 7] },
            { className: "text-end", targets: [5] }
        ],
        drawCallback: function() {
            // Add animation to rows
            $('#productTable tbody tr').each(function(index) {
                $(this).css({
                    'animation': 'fadeIn 0.3s ease-in',
                    'animation-delay': (index * 0.05) + 's'
                });
            });
        }
    });

    // Custom search styling
    $('.dataTables_filter input').addClass('form-control form-control-sm').attr('placeholder', 'Search products...');
    $('.dataTables_length select').addClass('form-select form-select-sm');
    
    // Add icons to pagination
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
