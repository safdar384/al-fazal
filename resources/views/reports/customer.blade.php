@extends('layout.app')

@section('title', 'Customer balance report')

@section('content')
<div class="container-fluid py-3">
    @include('reports.partials.report-header', [
        'label' => 'Customer balance report',
        'subtitle' => 'Receivable and payable balances for all parties in the selected date range.',
        'icon' => 'bi-people-fill',
    ])

    @include('reports.partials.date-filter', [
        'action' => route('reports.customer'),
        'fromDate' => $fromDate,
        'toDate' => $toDate,
        'title' => 'Filter by period',
    ])

    @if ($rows->isNotEmpty())
        <div class="row g-3 mb-3">
            <div class="col-sm-6 col-lg-4">
                <div class="card border-0 shadow-sm border-start border-success border-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total Receivable</div>
                        <div class="fs-5 fw-bold text-success">Rs. {{ number_format($summary['total_receivable'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card border-0 shadow-sm border-start border-danger border-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Total Payable</div>
                        <div class="fs-5 fw-bold text-danger">Rs. {{ number_format($summary['total_payable'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card border-0 shadow-sm border-start border-primary border-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small">Balance (Payable − Receivable)</div>
                        <div class="fs-5 fw-bold {{ $summary['net_balance'] >= 0 ? 'text-danger' : 'text-success' }}">
                            Rs. {{ number_format($summary['net_balance'], 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Party balances</span>
            <span class="badge text-bg-light">{{ $fromDate }} → {{ $toDate }}</span>
        </div>
        <div class="card-body p-0">
            @include('reports.customers._balance-table')
        </div>
    </div>
</div>
@endsection
