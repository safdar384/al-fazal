@extends('layout.app')

@section('title', 'Expense report')

@section('content')
<div class="container-fluid py-3">
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            @include('components.invoiceHeader')
        </div>
    </div>

    @include('reports.partials.report-header', [
        'label' => 'Expense report',
        'subtitle' => 'Expense ledger entries in the selected range.',
        'icon' => 'bi-cash-stack',
    ])

    @include('reports.partials.date-filter', [
        'action' => route('reports.expense'),
        'fromDate' => $fromDate,
        'toDate' => $toDate,
        'title' => 'Filter by entry date',
    ])

    <div class="card shadow-sm">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Detail</span>
            <span class="badge text-bg-light">{{ $fromDate }} → {{ $toDate }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm align-middle mb-0">
                    <colgroup>
                        <col style="width: 10%;">
                        <col style="width: 10%;">
                        <col style="width: 12%;">
                        <col style="width: 22%;">
                        <col style="width: 10%;">
                        <col style="width: 12%;">
                        <col style="width: 8%;">
                        <col style="width: 8%;">
                        <col style="width: 8%;">
                    </colgroup>
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Code</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Payment</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                            <th>Ref</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td>{{ $row->entryDate?->format('Y-m-d') }}</td>
                                <td class="font-monospace">{{ $row->expenseCode }}</td>
                                <td>{{ $row->expenseCategory ?? '—' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($row->description ?? '', 40) }}</td>
                                <td>{{ $row->paymentMethod ?? '—' }}</td>
                                <td class="text-end">Rs. {{ number_format((float) $row->amount, 2) }}</td>
                                <td class="text-end">Rs. {{ number_format((float) $row->debit, 2) }}</td>
                                <td class="text-end">Rs. {{ number_format((float) $row->credit, 2) }}</td>
                                <td class="small">{{ $row->referenceNo ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No expense entries in this range.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($rows->isNotEmpty())
                        <tfoot class="table-light fw-semibold">
                            <tr>
                                <td colspan="5" class="text-end">Totals</td>
                                <td class="text-end">Rs. {{ number_format($totals['amount'], 2) }}</td>
                                <td class="text-end">Rs. {{ number_format($totals['debit'], 2) }}</td>
                                <td class="text-end">Rs. {{ number_format($totals['credit'], 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    @page {
        size: A4;
        margin: 8mm;
    }

    body {
        font-size: 11px !important;
        line-height: 1.2 !important;
    }

    .container-fluid {
        padding: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 auto !important;
    }

    .app-main,
    .app-content,
    .content-wrapper,
    .app-wrapper {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    .no-print,
    .app-header,
    .app-sidebar,
    .app-footer {
        display: none !important;
    }

    .card {
        box-shadow: none !important;
        border: 1px solid #d9d9d9 !important;
        margin-bottom: 6px !important;
    }

    .card-body {
        padding: 6px !important;
    }

    .card-header {
        padding: 4px 6px !important;
    }

    .table {
        margin-bottom: 0 !important;
        font-size: 10.5px !important;
        table-layout: fixed !important;
        width: 100% !important;
    }

    .table th,
    .table td {
        padding: 3px 5px !important;
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: anywhere !important;
    }

    .company-header {
        gap: 8px !important;
        margin-bottom: 6px !important;
        padding-bottom: 4px !important;
    }

    .company-name {
        font-size: 16px !important;
        line-height: 1.1 !important;
    }

    .company-tagline,
    .company-contact {
        font-size: 10px !important;
        line-height: 1.2 !important;
    }

    .company-monogram {
        flex: 0 0 70px !important;
    }

    .monogram-logo {
        font-size: 28px !important;
        margin-bottom: 0 !important;
    }

    .monogram-text {
        font-size: 9px !important;
    }
}
</style>
@endsection
