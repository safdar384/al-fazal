@extends('layout.app')

@section('title', 'Product Management')

@section('content')
@php
    $totalProducts = $products->count();
    $totalStock = (float) $products->sum('stockQty');
    $stockValue = (float) $products->sum(fn ($p) => (float) $p->costRate * (float) $p->stockQty);
    $lowStock = $products->filter(fn ($p) => (float) $p->stockQty <= (float) $p->lessQty)->count();
@endphp

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center gy-2">
                <div class="col-12 col-md-6">
                    <h3 class="mb-0 fw-bold" style="color:#0f2747;">
                        <i class="bi bi-box-seam me-2"></i>Product Management
                    </h3>
                    <p class="text-muted small mb-0">Stock Management · Products inventory</p>
                </div>
                <div class="col-12 col-md-6">
                    <ol class="breadcrumb float-md-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><span class="text-muted">Stock Management</span></li>
                        <li class="breadcrumb-item active" aria-current="page">Products</li>
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
                    <div class="pm-kpi">
                        <div class="pm-kpi-icon"><i class="bi bi-box-seam"></i></div>
                        <div>
                            <div class="pm-kpi-lbl">Total Products</div>
                            <div class="pm-kpi-val">{{ number_format($totalProducts) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="pm-kpi">
                        <div class="pm-kpi-icon ok"><i class="bi bi-boxes"></i></div>
                        <div>
                            <div class="pm-kpi-lbl">Total Stock</div>
                            <div class="pm-kpi-val">{{ number_format($totalStock, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="pm-kpi">
                        <div class="pm-kpi-icon warn"><i class="bi bi-cash-stack"></i></div>
                        <div>
                            <div class="pm-kpi-lbl">Stock Value</div>
                            <div class="pm-kpi-val">Rs. {{ number_format($stockValue, 0) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="pm-kpi {{ $lowStock > 0 ? 'danger' : '' }}">
                        <div class="pm-kpi-icon danger"><i class="bi bi-exclamation-triangle"></i></div>
                        <div>
                            <div class="pm-kpi-lbl">Low Stock</div>
                            <div class="pm-kpi-val">{{ number_format($lowStock) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card pm-card border-0">
                <div class="card-header pm-card-head">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <h6 class="mb-0 fw-semibold text-white">
                            <i class="bi bi-table me-2"></i>Product List
                        </h6>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="input-group input-group-sm pm-search">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" id="productSearch" class="form-control" placeholder="Search products...">
                            </div>
                            <a href="{{ route('product.create') }}" class="btn btn-sm btn-light fw-semibold">
                                <i class="bi bi-plus-circle me-1"></i>Add Product
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 pm-table" id="productTable">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:48px;">#</th>
                                    <th class="text-center" style="width:64px;">Image</th>
                                    <th>Code</th>
                                    <th>Product</th>
                                    <th>Company</th>
                                    <th>Category</th>
                                    <th>Unit / Pack</th>
                                    <th class="text-end">Sale Rate</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-center" style="width:110px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $index => $product)
                                    <tr>
                                        <td class="text-center text-muted small">{{ $index + 1 }}</td>
                                        <td class="text-center">
                                            @if($product->image_url)
                                                <img src="{{ $product->image_url }}"
                                                     alt="{{ $product->productName }}"
                                                     class="pm-thumb">
                                            @else
                                                <span class="pm-thumb pm-thumb-empty"><i class="bi bi-image"></i></span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="pm-code">{{ $product->productCode }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold" style="color:#0f2747;">{{ $product->productName }}</div>
                                            <small class="text-muted">
                                                @if($product->colourName)
                                                    <i class="bi bi-palette"></i> {{ $product->colourName }}
                                                @endif
                                                @if($product->colourName && $product->pakingName) · @endif
                                                @if($product->pakingName)
                                                    <i class="bi bi-box"></i> {{ $product->pakingName }}
                                                @endif
                                            </small>
                                        </td>
                                        <td class="small">{{ $product->companyName ?: '—' }}</td>
                                        <td>
                                            @if($product->catagoryName)
                                                <span class="pm-badge">{{ $product->catagoryName }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="small">
                                            @if($product->unitPerPackName)
                                                {{ $product->unitPerPackName }}
                                                @if($product->piecesPerUnit)
                                                    <span class="text-muted">({{ $product->piecesPerUnit }} pcs)</span>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-semibold text-success">
                                            Rs. {{ number_format((float) $product->saleRate, 2) }}
                                        </td>
                                        <td class="text-center">
                                            @if((float) $product->stockQty <= (float) $product->lessQty)
                                                <span class="badge bg-danger">{{ number_format((float) $product->stockQty, 2) }}</span>
                                            @elseif((float) $product->stockQty <= ((float) $product->lessQty * 2))
                                                <span class="badge bg-warning text-dark">{{ number_format((float) $product->stockQty, 2) }}</span>
                                            @else
                                                <span class="badge bg-success">{{ number_format((float) $product->stockQty, 2) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('product.edit', $product->id) }}"
                                                   class="btn btn-outline-secondary"
                                                   title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-outline-danger"
                                                        title="Delete"
                                                        onclick="confirmDelete('deleteForm{{ $product->id }}', 'Delete Product', 'Are you sure you want to delete {{ addslashes($product->productName) }}?')">
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
                                        <td colspan="10" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            <div class="fw-semibold mb-1">No Products Found</div>
                                            <p class="small mb-3">Start by adding your first product.</p>
                                            <a href="{{ route('product.create') }}" class="btn btn-sm text-white" style="background:#0f2747;">
                                                <i class="bi bi-plus-circle me-1"></i>Add New Product
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
    .pm-kpi {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: #fff;
        border: 1px solid #d5dde8;
        border-left: 4px solid #0f2747;
        border-radius: 4px;
        padding: 0.85rem 1rem;
        height: 100%;
        box-shadow: 0 2px 8px rgba(15, 39, 71, 0.05);
    }
    .pm-kpi.danger { border-left-color: #dc3545; background: #fdf0f1; }
    .pm-kpi-icon {
        width: 42px;
        height: 42px;
        border-radius: 6px;
        background: #e8eef5;
        color: #0f2747;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .pm-kpi-icon.ok { background: #e8f5e9; color: #198754; }
    .pm-kpi-icon.warn { background: #fff8e6; color: #c79100; }
    .pm-kpi-icon.danger { background: #fde8ea; color: #dc3545; }
    .pm-kpi-lbl {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #6b7c8d;
        margin-bottom: 0.15rem;
    }
    .pm-kpi-val {
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f2747;
        line-height: 1.2;
    }
    .pm-card {
        box-shadow: 0 2px 12px rgba(15, 39, 71, 0.08);
        border-radius: 4px;
        overflow: hidden;
    }
    .pm-card-head {
        background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
        border: none;
        padding: 0.85rem 1rem;
    }
    .pm-search {
        max-width: 240px;
    }
    .pm-search .input-group-text,
    .pm-search .form-control {
        background: rgba(255,255,255,0.95);
        border-color: transparent;
    }
    .pm-table thead th {
        background: #0f2747;
        color: #fff;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
        border-color: #0a1c33;
        padding: 0.55rem 0.65rem;
        vertical-align: middle;
    }
    .pm-table tbody td {
        font-size: 0.82rem;
        padding: 0.55rem 0.65rem;
        border-color: #e8eef5;
        vertical-align: middle;
    }
    .pm-table tbody tr:nth-child(even) td { background: #f4f7fb; }
    .pm-table tbody tr:hover td { background: #e8eef5; }
    .pm-thumb {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #d5dde8;
    }
    .pm-thumb-empty {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f4f7fb;
        color: #8a97a6;
    }
    .pm-code {
        display: inline-block;
        padding: 0.15rem 0.45rem;
        border-radius: 3px;
        background: #e8eef5;
        color: #0f2747;
        font-weight: 700;
        font-size: 0.75rem;
    }
    .pm-badge {
        display: inline-block;
        padding: 0.15rem 0.45rem;
        border-radius: 3px;
        background: #e8eef5;
        color: #1a4a8a;
        font-size: 0.72rem;
        font-weight: 600;
    }
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 0.65rem 1rem;
        font-size: 0.8rem;
    }
    .dataTables_wrapper .dataTables_filter { display: none; }
    .dataTables_wrapper .page-item.active .page-link {
        background: #0f2747;
        border-color: #0f2747;
    }
    .dataTables_wrapper .page-link { color: #0f2747; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function () {
    @if($products->count() > 0)
    const table = $('#productTable').DataTable({
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        order: [[2, 'asc']],
        language: {
            lengthMenu: 'Show _MENU_',
            info: 'Showing _START_–_END_ of _TOTAL_ products',
            infoEmpty: 'No products',
            infoFiltered: '(filtered from _MAX_)',
            paginate: { previous: '‹', next: '›' }
        },
        dom: '<"row px-2 pt-2"<"col-sm-12 col-md-6"l>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        columnDefs: [
            { orderable: false, targets: [0, 1, 9] }
        ]
    });

    $('#productSearch').on('keyup', function () {
        table.search(this.value).draw();
    });

    $('.dataTables_length select').addClass('form-select form-select-sm');
    @endif

    setTimeout(function () {
        document.querySelectorAll('.alert').forEach(function (alert) {
            try { new bootstrap.Alert(alert).close(); } catch (e) {}
        });
    }, 5000);
});
</script>
@endpush
@endsection
