@extends('layout.app')

@section('title', 'Product sales report')

@section('content')
<div class="container-fluid py-3">
    @include('reports.partials.report-header', [
        'label' => 'Product sales report',
        'subtitle' => 'Aggregated sale invoice lines by product for invoices in the selected range.',
        'icon' => 'bi-box-seam',
    ])

    @include('reports.partials.date-filter', [
        'action' => route('reports.product'),
        'fromDate' => $fromDate,
        'toDate' => $toDate,
        'title' => 'Filter by invoice date',
    ])

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Products sold</div>
                    <div class="fs-4 fw-bold">{{ number_format($totals['products']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Invoice lines</div>
                    <div class="fs-4 fw-bold">{{ number_format($totals['lines']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Quantity sold</div>
                    <div class="fs-5 fw-bold">{{ number_format($totals['qty'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-secondary bg-opacity-10">
                <div class="card-body">
                    <div class="text-muted small">Revenue (line amounts)</div>
                    <div class="fs-5 fw-bold">Rs. {{ number_format($totals['amount'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Detail</span>
            <span class="badge text-bg-light">{{ $fromDate }} → {{ $toDate }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th class="text-end">Stock (master)</th>
                            <th class="text-end">Qty sold</th>
                            <th class="text-end">Lines</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td class="font-monospace">{{ $row->productCode }}</td>
                                <td>{{ $row->productName }}</td>
                                <td>{{ $row->category_name ?? '—' }}</td>
                                <td class="text-end">{{ $row->stock_qty !== null ? number_format((float) $row->stock_qty, 2) : '—' }}</td>
                                <td class="text-end">{{ number_format((float) $row->total_qty, 2) }}</td>
                                <td class="text-end">{{ number_format($row->line_count) }}</td>
                                <td class="text-end fw-semibold">Rs. {{ number_format((float) $row->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No product sales in this range.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($rows->isNotEmpty())
                        <tfoot class="table-light fw-semibold">
                            <tr>
                                <td colspan="4" class="text-end">Totals</td>
                                <td class="text-end">{{ number_format($totals['qty'], 2) }}</td>
                                <td class="text-end">{{ number_format($totals['lines']) }}</td>
                                <td class="text-end">Rs. {{ number_format($totals['amount'], 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
