@extends('layout.app')

@section('title', 'Sale report')

@section('content')
<div class="container-fluid py-3">
    @include('reports.partials.report-header', [
        'label' => 'Sale report',
        'subtitle' => 'Invoices dated in the selected range.',
        'icon' => 'bi-bag-check-fill',
    ])

    @include('reports.partials.date-filter', [
        'action' => route('reports.sale'),
        'fromDate' => $fromDate,
        'toDate' => $toDate,
        'title' => 'Filter by invoice date',
    ])

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Invoices</div>
                    <div class="fs-4 fw-bold">{{ number_format($totals['invoices']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Line items (header)</div>
                    <div class="fs-4 fw-bold">{{ number_format($totals['items']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Subtotal (before discount)</div>
                    <div class="fs-5 fw-bold text-body">Rs. {{ number_format($totals['invoice_total'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="text-muted small">Grand total</div>
                    <div class="fs-5 fw-bold text-success">Rs. {{ number_format($totals['grand_total'], 2) }}</div>
                    <div class="small text-muted">Discount: Rs. {{ number_format($totals['discount'], 2) }}</div>
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
                            <th>Date</th>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th class="text-end">Items</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Grand total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td>{{ $row->invoiceDate?->format('Y-m-d') }}</td>
                                <td class="font-monospace">{{ $row->invoiceNo }}</td>
                                <td>{{ $row->customerName }} <span class="text-muted small">({{ $row->customerCode }})</span></td>
                                <td class="text-end">{{ $row->noOfItems }}</td>
                                <td class="text-end">Rs. {{ number_format((float) $row->invoiceTotal, 2) }}</td>
                                <td class="text-end">Rs. {{ number_format((float) $row->invoiceDiscount, 2) }}</td>
                                <td class="text-end fw-semibold">Rs. {{ number_format((float) $row->grandTotal, 2) }}</td>
                                <td><span class="badge rounded-pill text-bg-secondary">{{ $row->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No sale invoices in this range.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($rows->isNotEmpty())
                        <tfoot class="table-light fw-semibold">
                            <tr>
                                <td colspan="4" class="text-end">Totals</td>
                                <td class="text-end">Rs. {{ number_format($totals['invoice_total'], 2) }}</td>
                                <td class="text-end">Rs. {{ number_format($totals['discount'], 2) }}</td>
                                <td class="text-end">Rs. {{ number_format($totals['grand_total'], 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
