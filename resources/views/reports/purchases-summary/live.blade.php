@php
    $fmt = fn ($n) => 'Rs. ' . number_format((float) $n, 2);
    $tabQuery = request()->except(['tab', 'fragment', 'download_pdf', 'print', 'page']);
    $tabTitle = $tab === 'suppliers' ? 'By Supplier' : ($tab === 'pending' ? 'Pending Payable' : 'Invoices');
    $periodFrom = \Carbon\Carbon::parse($fromDate)->format('d M Y');
    $periodTo = \Carbon\Carbon::parse($toDate)->format('d M Y');
@endphp
<div id="purchaseReportLive" data-from="{{ $periodFrom }}" data-to="{{ $periodTo }}">
    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="ss-kpi amt">
                <div class="ss-kpi-icon amt"><i class="bi bi-cart4"></i></div>
                <div>
                    <div class="ss-kpi-lbl">Purchases</div>
                    <div class="ss-kpi-val text-danger">{{ $fmt($overall['total_purchases'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="ss-kpi paid">
                <div class="ss-kpi-icon paid"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="ss-kpi-lbl">Paid</div>
                    <div class="ss-kpi-val text-success">{{ $fmt($overall['total_paid'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="ss-kpi due">
                <div class="ss-kpi-icon due"><i class="bi bi-exclamation-circle"></i></div>
                <div>
                    <div class="ss-kpi-lbl">Outstanding</div>
                    <div class="ss-kpi-val">{{ $fmt($overall['total_outstanding'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="ss-kpi count">
                <div class="ss-kpi-icon count"><i class="bi bi-receipt"></i></div>
                <div>
                    <div class="ss-kpi-lbl">Invoices</div>
                    <div class="ss-kpi-val">{{ $overall['invoice_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card ss-card border-0">
        <div class="card-header ss-card-head">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h6 class="mb-0 text-white fw-semibold">
                    <i class="bi bi-table me-2"></i>{{ $tabTitle }}
                </h6>
                <div class="d-flex flex-wrap align-items-center gap-2 no-print">
                    <a id="psrPdf" class="btn btn-sm btn-danger"
                       href="{{ route('reports.purchases-summary', array_merge($tabQuery, ['tab' => $tab, 'download_pdf' => 1])) }}">
                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                    </a>
                    <a id="psrPrint" class="btn btn-sm btn-outline-light" target="_blank" rel="noopener"
                       href="{{ route('reports.purchases-summary', array_merge($tabQuery, ['tab' => $tab, 'print' => 1])) }}">
                        <i class="bi bi-printer me-1"></i>Print Report
                    </a>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs psr-nav-tabs no-print" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'invoices' ? 'active' : '' }}" data-tab="invoices"
                   href="{{ route('reports.purchases-summary', array_merge($tabQuery, ['tab' => 'invoices'])) }}">Invoices</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'suppliers' ? 'active' : '' }}" data-tab="suppliers"
                   href="{{ route('reports.purchases-summary', array_merge($tabQuery, ['tab' => 'suppliers'])) }}">By Supplier</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'pending' ? 'active' : '' }}" data-tab="pending"
                   href="{{ route('reports.purchases-summary', array_merge($tabQuery, ['tab' => 'pending'])) }}">Pending Payable</a>
            </li>
        </ul>

        <div class="card-body p-0">
            <div class="table-responsive">
                @if($tab === 'suppliers')
                    <table class="table table-hover align-middle mb-0 ss-table">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th class="text-end">Invoices</th>
                                <th class="text-end">Purchases</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Due</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suppliers as $row)
                                <tr>
                                    <td>{{ $row->supplierName }}</td>
                                    <td class="text-end">{{ $row->invoice_count }}</td>
                                    <td class="text-end fw-semibold">{{ $fmt($row->total_purchases) }}</td>
                                    <td class="text-end text-success">{{ $fmt($row->total_paid) }}</td>
                                    <td class="text-end {{ $row->total_outstanding > 0 ? 'text-danger fw-semibold' : '' }}">{{ $fmt($row->total_outstanding) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No data</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @elseif($tab === 'pending')
                    <table class="table table-hover align-middle mb-0 ss-table">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th>Code</th>
                                <th class="text-end">Payable Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingSuppliers as $row)
                                <tr>
                                    <td>{{ $row->custName }}</td>
                                    <td class="font-monospace small">{{ $row->custCode }}</td>
                                    <td class="text-end fw-semibold text-danger">{{ $fmt($row->payable_balance) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">No outstanding payables</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <table class="table table-hover align-middle mb-0 ss-table">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Due</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $inv)
                                <tr>
                                    <td class="fw-semibold font-monospace">{{ $inv->invoiceNo }}</td>
                                    <td>{{ $inv->invoiceDate?->format('d-M-Y') }}</td>
                                    <td>{{ $inv->supplierName }}</td>
                                    <td class="text-end fw-semibold">{{ $fmt($inv->grandTotal) }}</td>
                                    <td class="text-end text-success">{{ $fmt($inv->paid_amount) }}</td>
                                    <td class="text-end {{ $inv->balanceDue() > 0 ? 'text-danger fw-semibold' : '' }}">{{ $fmt($inv->balanceDue()) }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $inv->paymentStatusBadgeClass() }}">{{ $inv->payment_status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No invoices</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>
            @php
                $pager = $tab === 'suppliers' ? $suppliers : ($tab === 'pending' ? $pendingSuppliers : $invoices);
            @endphp
            @include('partials.tablePager', ['pager' => $pager])
        </div>
    </div>
</div>
