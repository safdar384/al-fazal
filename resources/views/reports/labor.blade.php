@extends('layout.app')

@section('title', 'Labor report')

@section('content')
<div class="container-fluid py-3">
    @include('reports.partials.report-header', [
        'label' => 'Labor report',
        'subtitle' => 'BOM labor operations created in the selected range (by line created date).',
        'icon' => 'bi-tools',
    ])

    @include('reports.partials.date-filter', [
        'action' => route('reports.labor'),
        'fromDate' => $fromDate,
        'toDate' => $toDate,
        'title' => 'Filter by labor line created date',
    ])

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Labor lines</div>
                    <div class="fs-4 fw-bold">{{ number_format($totals['lines']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Total time (minutes)</div>
                    <div class="fs-5 fw-bold">{{ number_format($totals['minutes'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100 bg-warning bg-opacity-10">
                <div class="card-body">
                    <div class="text-muted small">Estimated labor cost (minutes × rate)</div>
                    <div class="fs-5 fw-bold text-dark">Rs. {{ number_format($totals['estimated_cost'], 2) }}</div>
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
                            <th>Created</th>
                            <th>Product</th>
                            <th>Code</th>
                            <th>Operation</th>
                            <th class="text-end">Minutes</th>
                            <th class="text-end">Rate / min</th>
                            <th class="text-end">Line cost</th>
                            <th>BOM</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($row->created_at)->format('Y-m-d H:i') }}</td>
                                <td>{{ $row->product_name }}</td>
                                <td class="font-monospace">{{ $row->productCode }}</td>
                                <td>{{ $row->operation_name }}</td>
                                <td class="text-end">{{ number_format((float) $row->time_minutes, 2) }}</td>
                                <td class="text-end">{{ number_format((float) $row->rate_per_minute, 4) }}</td>
                                <td class="text-end fw-semibold">Rs. {{ number_format($row->line_cost, 2) }}</td>
                                <td><span class="badge text-bg-light">{{ $row->bom_status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No BOM labor lines in this range.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($rows->isNotEmpty())
                        <tfoot class="table-light fw-semibold">
                            <tr>
                                <td colspan="4" class="text-end">Totals</td>
                                <td class="text-end">{{ number_format($totals['minutes'], 2) }}</td>
                                <td class="text-end">—</td>
                                <td class="text-end">Rs. {{ number_format($totals['estimated_cost'], 2) }}</td>
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
