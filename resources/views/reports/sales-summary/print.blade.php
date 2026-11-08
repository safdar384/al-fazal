<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Summary Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    @include('reports.profit-loss.partials.navy-styles')
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        .ledger-container { max-width: 1400px; }
        .type-pill {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 0.58rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .type-cash { background: #e8f5e9; color: #198754; }
        .type-credit { background: #e8eef8; color: #0f2747; }
        .stat-box.cash .stat-value { color: #198754; }
        .stat-box.credit .stat-value { color: #1a4a8a; }
        .stat-box.discount .stat-value { color: #c77c00; }
        .stat-box.paid .stat-value { color: #198754; }
        .stat-box.due .stat-value { color: #dc3545; }
        .section-title {
            margin: 12px 0 6px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #0f2747;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #d5dde8;
            padding-bottom: 3px;
        }
        /* Full address — override navy nowrap/ellipsis */
        .ledger-table tbody td.cell-address {
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: unset !important;
            word-break: break-word;
            line-height: 1.25;
            height: auto !important;
            text-align: left !important;
            font-weight: 500;
            vertical-align: top;
        }
        .ledger-table tbody td.cell-customer {
            white-space: normal !important;
            overflow: visible !important;
            text-overflow: unset !important;
            word-break: break-word;
            height: auto !important;
            text-align: left !important;
            vertical-align: top;
        }
        @media print {
            html, body {
                width: 297mm;
                margin: 0;
                padding: 0;
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .ledger-container {
                max-width: 100% !important;
                width: 100% !important;
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
            }
            .type-pill, .stat-box {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .section-title { break-after: avoid; }
            .ledger-table { font-size: 0.62rem; }
            .ledger-table thead th { font-size: 0.55rem; padding: 4px 3px; }
            .ledger-table tbody td { padding: 3px 4px; }
            .ledger-table tbody td.cell-address,
            .ledger-table tbody td.cell-customer {
                white-space: normal !important;
                overflow: visible !important;
            }
            .ledger-table tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
@php
    $fmt = static fn ($n) => number_format((float) $n, 2);
    $customerLabel = 'All';
    if (($filters['customer_code'] ?? '') !== '') {
        $match = collect($filterOptions['customers'] ?? [])->firstWhere('custCode', $filters['customer_code']);
        $customerLabel = $match->custName ?? $filters['customer_code'];
    }
    $productLabel = 'All';
    if (($filters['product_code'] ?? '') !== '') {
        $match = collect($filterOptions['products'] ?? [])->firstWhere('productCode', $filters['product_code']);
        $productLabel = $match->productName ?? $filters['product_code'];
    }
    $areaLabel = ($filters['area'] ?? '') !== '' ? $filters['area'] : 'All';
    $typeLabel = ($filters['sale_type'] ?? '') !== '' ? ucfirst($filters['sale_type']) : 'All';
@endphp

    <div class="ledger-container">
        @include('reports.profit-loss.partials.navy-header', ['title' => 'Sales Summary Report'])

        <div class="meta-bar">
            <div class="meta-box">
                <h6>Filters</h6>
                <div class="meta-grid">
                    <span class="lbl">Period</span>
                    <span class="val">
                        {{ \Carbon\Carbon::parse($fromDate)->format('d-M-Y') }}
                        to
                        {{ \Carbon\Carbon::parse($toDate)->format('d-M-Y') }}
                    </span>
                    <span class="lbl">Customer</span>
                    <span class="val" title="{{ $customerLabel }}">{{ $customerLabel }}</span>
                    <span class="lbl">Product</span>
                    <span class="val" title="{{ $productLabel }}">{{ $productLabel }}</span>
                    <span class="lbl">Area</span>
                    <span class="val">{{ $areaLabel }}</span>
                    <span class="lbl">Type</span>
                    <span class="val">{{ $typeLabel }}</span>
                </div>
            </div>
            <div class="meta-box">
                <h6>Report</h6>
                <div class="meta-grid">
                    <span class="lbl">Generated</span>
                    <span class="val">{{ now()->format('d-M-Y h:i A') }}</span>
                    <span class="lbl">Invoices</span>
                    <span class="val">{{ $invoices->count() }}</span>
                    <span class="lbl">Net Sales</span>
                    <span class="val">{{ $fmt($overall['net_sales'] ?? 0) }}</span>
                </div>
            </div>
        </div>

        <div class="summary-stats">
            <div class="stat-box">
                <div class="stat-label">Net Sales</div>
                <div class="stat-value">{{ $fmt($overall['net_sales'] ?? 0) }}</div>
            </div>
            <div class="stat-box cash">
                <div class="stat-label">Cash Sales</div>
                <div class="stat-value">{{ $fmt($overall['cash_sales'] ?? 0) }}</div>
            </div>
            <div class="stat-box credit">
                <div class="stat-label">Credit Sales</div>
                <div class="stat-value">{{ $fmt($overall['credit_sales'] ?? 0) }}</div>
            </div>
            <div class="stat-box discount">
                <div class="stat-label">Discount</div>
                <div class="stat-value">{{ $fmt($overall['total_discount'] ?? 0) }}</div>
            </div>
            <div class="stat-box paid">
                <div class="stat-label">Paid</div>
                <div class="stat-value">{{ $fmt($overall['total_paid'] ?? 0) }}</div>
            </div>
            <div class="stat-box due">
                <div class="stat-label">Outstanding</div>
                <div class="stat-value">{{ $fmt($overall['total_outstanding'] ?? 0) }}</div>
            </div>
        </div>

        <div class="section-title">Invoice Detail</div>
        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width:6%;">Date</th>
                    <th style="width:8%;">Invoice</th>
                    <th style="width:12%;">Customer</th>
                    <th style="width:22%;">Address</th>
                    <th style="width:5%;">Type</th>
                    <th style="width:5%;">Qty</th>
                    <th style="width:7%;">Gross</th>
                    <th style="width:7%;">Discount</th>
                    <th style="width:7%;">Net</th>
                    <th style="width:8%;">Bilty No.</th>
                    <th style="width:9%;">Invoice Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $inv)
                    <tr>
                        <td class="text-center">{{ \Carbon\Carbon::parse($inv->invoiceDate)->format('d-m-Y') }}</td>
                        <td class="text-center">{{ $inv->invoiceNo }}</td>
                        <td class="cell-customer">{{ $inv->customerName }}</td>
                        <td class="cell-address">{{ $inv->customer_address }}</td>
                        <td class="text-center">
                            @if ($inv->sale_type_label === 'Cash')
                                <span class="type-pill type-cash">Cash</span>
                            @else
                                <span class="type-pill type-credit">Credit</span>
                            @endif
                        </td>
                        <td class="text-end amt">{{ $fmt($inv->total_qty) }}</td>
                        <td class="text-end">{{ $fmt($inv->gross_amount) }}</td>
                        <td class="text-end">{{ $fmt($inv->total_discount) }}</td>
                        <td class="text-end amt">{{ $fmt($inv->net_amount) }}</td>
                        <td class="text-center">{{ $inv->biltyNo ?: '—' }}</td>
                        <td class="text-end amt">{{ $fmt($inv->invoice_amount ?? $inv->net_amount) }}</td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="11">No invoices in this period.</td></tr>
                @endforelse
            </tbody>
            @if ($invoices->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end">TOTAL</td>
                        <td class="text-end">{{ $fmt($invoices->sum('total_qty')) }}</td>
                        <td class="text-end">{{ $fmt($invoices->sum('gross_amount')) }}</td>
                        <td class="text-end">{{ $fmt($invoices->sum('total_discount')) }}</td>
                        <td class="text-end">{{ $fmt($invoices->sum('net_amount')) }}</td>
                        <td></td>
                        <td class="text-end">{{ $fmt($invoices->sum(fn ($i) => $i->invoice_amount ?? $i->net_amount)) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <div class="section-title">Product Summary</div>
        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width:28%;">Product</th>
                    <th style="width:12%;">Code</th>
                    <th style="width:12%;">Qty Sold</th>
                    <th style="width:12%;">Gross</th>
                    <th style="width:12%;">Discount</th>
                    <th style="width:12%;">Sales Value</th>
                    <th style="width:12%;">Avg Rate</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $p)
                    <tr>
                        <td class="cell-desc" title="{{ $p->product_name }}">{{ $p->product_name }}</td>
                        <td class="text-center">{{ $p->product_code }}</td>
                        <td class="text-end amt">{{ $fmt($p->total_qty) }}</td>
                        <td class="text-end">{{ $fmt($p->gross_amount) }}</td>
                        <td class="text-end">{{ $fmt($p->total_discount) }}</td>
                        <td class="text-end amt">{{ $fmt($p->total_sales_value) }}</td>
                        <td class="text-end">{{ $fmt($p->avg_rate) }}</td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="7">No product sales found.</td></tr>
                @endforelse
            </tbody>
            @if ($products->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-end">TOTAL</td>
                        <td class="text-end">{{ $fmt($products->sum('total_qty')) }}</td>
                        <td class="text-end">{{ $fmt($products->sum('gross_amount')) }}</td>
                        <td class="text-end">{{ $fmt($products->sum('total_discount')) }}</td>
                        <td class="text-end">{{ $fmt($products->sum('total_sales_value')) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <div class="section-title">Customer Summary</div>
        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width:28%;">Customer</th>
                    <th style="width:12%;">Code</th>
                    <th style="width:10%;">Invoices</th>
                    <th style="width:13%;">Total Sales</th>
                    <th style="width:12%;">Discount</th>
                    <th style="width:12%;">Paid</th>
                    <th style="width:13%;">Outstanding</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $c)
                    <tr>
                        <td class="cell-desc" title="{{ $c->customer_name }}">{{ $c->customer_name }}</td>
                        <td class="text-center">{{ $c->customer_code }}</td>
                        <td class="text-center">{{ $c->invoice_count }}</td>
                        <td class="text-end amt">{{ $fmt($c->total_sales) }}</td>
                        <td class="text-end">{{ $fmt($c->total_discount) }}</td>
                        <td class="text-end amt-pos">{{ $fmt($c->total_paid) }}</td>
                        <td class="text-end {{ $c->total_outstanding > 0 ? 'amt-neg' : 'amt' }}">{{ $fmt($c->total_outstanding) }}</td>
                    </tr>
                @empty
                    <tr class="empty-row"><td colspan="7">No customer sales found.</td></tr>
                @endforelse
            </tbody>
            @if ($customers->isNotEmpty())
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-end">TOTAL</td>
                        <td class="text-center">{{ $customers->sum('invoice_count') }}</td>
                        <td class="text-end">{{ $fmt($customers->sum('total_sales')) }}</td>
                        <td class="text-end">{{ $fmt($customers->sum('total_discount')) }}</td>
                        <td class="text-end">{{ $fmt($customers->sum('total_paid')) }}</td>
                        <td class="text-end">{{ $fmt($customers->sum('total_outstanding')) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <div class="report-footer">
            This is a computer-generated report. &copy; {{ date('Y') }} Trade Gate
        </div>
    </div>

    <button type="button" class="btn btn-secondary btn-back no-print" onclick="window.close()">
        <i class="bi bi-x-lg"></i> Close
    </button>
    <button type="button" onclick="window.print()" class="btn btn-primary btn-print no-print">
        <i class="bi bi-printer"></i> Print Report
    </button>
</body>
</html>
