@php
    $fmt = $fmt ?? fn ($n) => 'Rs. ' . number_format((float) $n, 2);
    $fmtNum = fn ($n) => number_format((float) $n, 2);
    $queryBase = $queryBase ?? array_filter([
        'from_date' => $fromDate,
        'to_date' => $toDate,
        'customer_code' => $filters['customer_code'],
        'product_code' => $filters['product_code'],
        'area' => $filters['area'],
        'sale_type' => $filters['sale_type'],
        'per_page' => request('per_page'),
    ], fn ($v) => $v !== null && $v !== '');
    $tabUrl = $tabUrl ?? fn (string $t) => route('reports.sales-summary', array_merge($queryBase, ['tab' => $t]));
    $periodLabel = \Carbon\Carbon::parse($fromDate)->format('d M Y').' — '.\Carbon\Carbon::parse($toDate)->format('d M Y');
    $tabTitle = match ($tab) {
        'products' => 'Product Summary',
        'customers' => 'Customer Summary',
        default => 'Invoice Detail',
    };
@endphp
<div id="ssrLive" data-period="{{ $periodLabel }}">
    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-2">
            <div class="ss-kpi amt">
                <div class="ss-kpi-icon amt"><i class="bi bi-currency-rupee"></i></div>
                <div>
                    <div class="ss-kpi-lbl">Net Sales</div>
                    <div class="ss-kpi-val text-success">{{ $fmt($overall['net_sales'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="ss-kpi cash">
                <div class="ss-kpi-icon cash"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="ss-kpi-lbl">Cash Sales</div>
                    <div class="ss-kpi-val">{{ $fmt($overall['cash_sales'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="ss-kpi credit">
                <div class="ss-kpi-icon credit"><i class="bi bi-journal-text"></i></div>
                <div>
                    <div class="ss-kpi-lbl">Credit Sales</div>
                    <div class="ss-kpi-val">{{ $fmt($overall['credit_sales'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="ss-kpi discount">
                <div class="ss-kpi-icon discount"><i class="bi bi-percent"></i></div>
                <div>
                    <div class="ss-kpi-lbl">Discount</div>
                    <div class="ss-kpi-val">{{ $fmt($overall['total_discount'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="ss-kpi paid">
                <div class="ss-kpi-icon paid"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="ss-kpi-lbl">Paid</div>
                    <div class="ss-kpi-val text-success">{{ $fmt($overall['total_paid'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-2">
            <div class="ss-kpi due">
                <div class="ss-kpi-icon due"><i class="bi bi-exclamation-circle"></i></div>
                <div>
                    <div class="ss-kpi-lbl">Outstanding</div>
                    <div class="ss-kpi-val text-danger">{{ $fmt($overall['total_outstanding'] ?? 0) }}</div>
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
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a id="ssrPdf" href="{{ route('reports.sales-summary', array_merge($queryBase, ['download_pdf' => 1])) }}" class="btn btn-sm btn-danger">
                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                    </a>
                    <a id="ssrPrint" href="{{ route('reports.sales-summary', array_merge($queryBase, ['print' => 1])) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-light">
                        <i class="bi bi-printer me-1"></i>Print Report
                    </a>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs ssr-nav-tabs no-print" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'invoices' ? 'active' : '' }}" href="{{ $tabUrl('invoices') }}">Invoice Detail</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'products' ? 'active' : '' }}" href="{{ $tabUrl('products') }}">Product Summary</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'customers' ? 'active' : '' }}" href="{{ $tabUrl('customers') }}">Customer Summary</a>
            </li>
        </ul>

        <div class="card-body p-0">
            @if ($tab === 'invoices' && $invoices)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 ss-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Invoice No</th>
                                <th>Customer</th>
                                <th>Address</th>
                                <th class="text-center">Type</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Gross</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Net</th>
                                <th>Bilty No.</th>
                                <th class="text-end">Invoice Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invoices as $inv)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($inv->invoiceDate)->format('d-M-Y') }}</td>
                                    <td class="font-monospace fw-semibold">{{ $inv->invoiceNo }}</td>
                                    <td>{{ $inv->customerName }}</td>
                                    <td class="ss-address">{{ $inv->customer_address }}</td>
                                    <td class="text-center">
                                        @if ($inv->sale_type_label === 'Cash')
                                            <span class="badge badge-cash">Cash</span>
                                        @else
                                            <span class="badge badge-credit">Credit</span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ $fmtNum($inv->total_qty) }}</td>
                                    <td class="text-end">{{ $fmt($inv->gross_amount) }}</td>
                                    <td class="text-end">{{ $fmt($inv->total_discount) }}</td>
                                    <td class="text-end fw-semibold">{{ $fmt($inv->net_amount) }}</td>
                                    <td>{{ $inv->biltyNo ?: '—' }}</td>
                                    <td class="text-end fw-semibold">{{ $fmt($inv->invoice_amount ?? $inv->net_amount) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">No invoices in this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('partials.tablePager', ['pager' => $invoices])
            @elseif ($tab === 'products' && $products)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 ss-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Code</th>
                                <th class="text-end">Qty Sold</th>
                                <th class="text-end">Gross</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Sales Value</th>
                                <th class="text-end">Avg Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $p)
                                <tr>
                                    <td>{{ $p->product_name }}</td>
                                    <td class="font-monospace small">{{ $p->product_code }}</td>
                                    <td class="text-end">{{ $fmtNum($p->total_qty) }}</td>
                                    <td class="text-end">{{ $fmt($p->gross_amount) }}</td>
                                    <td class="text-end">{{ $fmt($p->total_discount) }}</td>
                                    <td class="text-end fw-semibold">{{ $fmt($p->total_sales_value) }}</td>
                                    <td class="text-end">{{ $fmt($p->avg_rate) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No product sales found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if (($productTotals['qty'] ?? null) !== null || $products->isNotEmpty())
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="text-end">Total</td>
                                    <td class="text-end">{{ $fmtNum($productTotals['qty'] ?? $products->sum('total_qty')) }}</td>
                                    <td class="text-end">{{ $fmt($productTotals['gross'] ?? $products->sum('gross_amount')) }}</td>
                                    <td class="text-end">{{ $fmt($productTotals['discount'] ?? $products->sum('total_discount')) }}</td>
                                    <td class="text-end">{{ $fmt($productTotals['sales'] ?? $products->sum('total_sales_value')) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
                @include('partials.tablePager', ['pager' => $products])
            @elseif ($tab === 'customers' && $customers)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 ss-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Code</th>
                                <th class="text-end">Invoices</th>
                                <th class="text-end">Total Sales</th>
                                <th class="text-end">Discount</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Outstanding</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customers as $c)
                                <tr>
                                    <td>{{ $c->customer_name }}</td>
                                    <td class="font-monospace small">{{ $c->customer_code }}</td>
                                    <td class="text-end">{{ $c->invoice_count }}</td>
                                    <td class="text-end fw-semibold">{{ $fmt($c->total_sales) }}</td>
                                    <td class="text-end">{{ $fmt($c->total_discount) }}</td>
                                    <td class="text-end text-success">{{ $fmt($c->total_paid) }}</td>
                                    <td class="text-end {{ $c->total_outstanding > 0 ? 'text-danger fw-semibold' : '' }}">{{ $fmt($c->total_outstanding) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No customer sales found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if (($customerTotals['invoices'] ?? null) !== null || $customers->isNotEmpty())
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="text-end">Total</td>
                                    <td class="text-end">{{ $customerTotals['invoices'] ?? $customers->sum('invoice_count') }}</td>
                                    <td class="text-end">{{ $fmt($customerTotals['sales'] ?? $customers->sum('total_sales')) }}</td>
                                    <td class="text-end">{{ $fmt($customerTotals['discount'] ?? $customers->sum('total_discount')) }}</td>
                                    <td class="text-end">{{ $fmt($customerTotals['paid'] ?? $customers->sum('total_paid')) }}</td>
                                    <td class="text-end">{{ $fmt($customerTotals['outstanding'] ?? $customers->sum('total_outstanding')) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
                @include('partials.tablePager', ['pager' => $customers])
            @endif
        </div>
    </div>
</div>
