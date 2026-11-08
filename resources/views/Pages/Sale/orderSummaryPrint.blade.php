<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Summary Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page {
            size: A4 landscape;
            margin: 8mm;
        }
        @media print {
            .no-print { display: none !important; }
            html, body {
                width: 297mm;
                margin: 0;
                padding: 0;
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .ledger-container {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            .ledger-table thead th {
                background: #0f2747 !important;
                color: #fff !important;
            }
            .ledger-table tbody tr:nth-child(even) td {
                background: #f4f7fb !important;
            }
            .ledger-table tfoot td {
                background: #e8eef5 !important;
            }
            .meta-bar, .summary-stats, .stat-box, .status-pill {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        body {
            background: #e8eef5;
            font-family: 'Segoe UI', 'Helvetica Neue', Tahoma, Geneva, Verdana, sans-serif;
            color: #1a1a1a;
            padding: 12px;
        }
        .ledger-container {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 4px 24px rgba(15, 39, 71, 0.12);
            border-radius: 4px;
            padding: 14px 16px 16px;
        }
        .company-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f2747;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }
        .company-name {
            font-size: 1.55rem;
            font-weight: 700;
            color: #0f2747;
            letter-spacing: 0.4px;
            line-height: 1.1;
        }
        .company-tagline { color: #5a6a7a; font-size: 0.72rem; margin-top: 2px; }
        .company-contact { color: #6b7c8d; font-size: 0.68rem; margin-top: 1px; }
        .company-monogram {
            flex: 0 0 72px;
            text-align: center;
            border: 2px solid #0f2747;
            border-radius: 4px;
            padding: 4px 6px;
        }
        .monogram-logo { font-size: 1.75rem; font-weight: 800; color: #0f2747; line-height: 1; }
        .monogram-text { font-size: 0.52rem; color: #5a6a7a; font-weight: 700; letter-spacing: 1px; }
        .report-title { text-align: center; margin: 6px 0 8px; }
        .report-title h3 {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0;
            color: #0f2747;
            letter-spacing: 0.8px;
        }
        .meta-bar {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 8px;
            margin-bottom: 10px;
        }
        .meta-box {
            background: #f4f7fb;
            border: 1px solid #d5dde8;
            border-left: 3px solid #0f2747;
            border-radius: 3px;
            padding: 7px 10px;
        }
        .meta-box h6 {
            margin: 0 0 4px;
            font-size: 0.68rem;
            font-weight: 700;
            color: #0f2747;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            column-gap: 8px;
            row-gap: 2px;
            font-size: 0.72rem;
        }
        .meta-grid .lbl { color: #6b7c8d; font-weight: 600; }
        .meta-grid .val {
            color: #1a1a1a;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .status-stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            margin-bottom: 8px;
        }
        .summary-stats {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
            margin-bottom: 10px;
        }
        .stat-box {
            text-align: left;
            padding: 8px 10px;
            background: #fff;
            border: 1px solid #d5dde8;
            border-left: 4px solid #6c757d;
            border-radius: 3px;
            min-width: 0;
        }
        .stat-box.st-pending { border-left-color: #ffc107; background: #fffbeb; }
        .stat-box.st-confirmed { border-left-color: #0dcaf0; background: #effcff; }
        .stat-box.st-process { border-left-color: #0d6efd; background: #eef4ff; }
        .stat-box.st-completed { border-left-color: #198754; background: #eefaf2; }
        .stat-box.st-cancelled { border-left-color: #dc3545; background: #fdf0f1; }
        .stat-box.st-total { border-left-color: #0f2747; background: #f4f7fb; }
        .stat-box.st-amount { border-left-color: #198754; background: #eefaf2; }
        .stat-box.st-items { border-left-color: #1a4a8a; background: #f4f7fb; }
        .stat-line {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 6px;
            line-height: 1.35;
        }
        .stat-line + .stat-line { margin-top: 3px; }
        .stat-label {
            font-size: 0.6rem;
            color: #6b7c8d;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.25px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .stat-value {
            font-size: 0.8rem;
            font-weight: 700;
            color: #0f2747;
            white-space: nowrap;
        }
        .stat-box.st-amount .stat-value { color: #198754; }
        .stat-value.val-count { font-size: 0.95rem; }
        .stat-value.val-money { font-size: 0.72rem; color: #1a4a8a; }
        @media print {
            .status-stats, .summary-stats, .stat-box {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 0.68rem;
            margin-bottom: 10px;
        }
        .ledger-table thead th {
            background: #0f2747;
            color: #fff;
            padding: 6px 5px;
            font-size: 0.62rem;
            font-weight: 700;
            text-align: center;
            border: 1px solid #0a1c33;
            vertical-align: middle;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .ledger-table tbody td,
        .ledger-table tfoot td {
            padding: 4px 5px;
            border: 1px solid #cfd8e3;
            vertical-align: middle;
            height: 26px;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ledger-table tbody tr:nth-child(even) td { background: #f4f7fb; }
        .ledger-table tfoot td {
            background: #e8eef5;
            font-weight: 700;
            color: #0f2747;
        }
        .ledger-table .text-end { text-align: right !important; }
        .ledger-table .text-center { text-align: center !important; }
        .ledger-table .cell-desc { text-align: left !important; font-weight: 500; }
        .ledger-table .amt { color: #0f2747; font-weight: 700; }
        .status-pill {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 0.58rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .st-pending { background: #fff3cd; color: #856404; }
        .st-confirmed { background: #e7f1ff; color: #084298; }
        .st-process { background: #e7f1ff; color: #0b5ed7; }
        .st-completed { background: #e8f5e9; color: #198754; }
        .st-cancelled { background: #fde8ea; color: #842029; }
        .st-other { background: #eef1f4; color: #41464b; }
        .empty-row td {
            text-align: center;
            color: #6b7c8d;
            padding: 18px 8px !important;
            white-space: normal !important;
        }
        .report-footer {
            margin-top: 8px;
            border-top: 1px solid #d5dde8;
            padding-top: 6px;
            color: #8a97a6;
            text-align: center;
            font-size: 0.65rem;
        }
        .btn-print, .btn-back {
            position: fixed;
            bottom: 24px;
            padding: 9px 18px;
            font-size: 0.85rem;
            box-shadow: 0 4px 14px rgba(0,0,0,0.18);
            z-index: 1000;
            border-radius: 4px;
        }
        .btn-back { left: 24px; }
        .btn-print { right: 24px; background: #0f2747; border-color: #0f2747; }
        .btn-print:hover { background: #1a4a8a; border-color: #1a4a8a; }
    </style>
</head>
<body>
@php
    $fmt = static fn ($n) => number_format((float) $n, 2);
    $totalOrders = $orders->count();
    $totalAmount = (float) $orders->sum('grandTotal');
    $totalItems = (int) $orders->sum('noOfItems');
    $avg = $totalOrders > 0 ? $totalAmount / $totalOrders : 0;

    $fromLabel = request('fromDate')
        ? \Carbon\Carbon::parse(request('fromDate'))->format('d-M-Y')
        : 'Start';
    $toLabel = request('toDate')
        ? \Carbon\Carbon::parse(request('toDate'))->format('d-M-Y')
        : 'End';
    $customerLabel = request('customerCode') ?: 'All';
    $statusFilter = request('status') ?: 'All';
    $areaFilter = request('area') ?: 'All';

    $statusClass = static function ($status) {
        $s = strtolower(trim((string) $status));
        if ($s === 'pending') return 'st-pending';
        if ($s === 'confirmed') return 'st-confirmed';
        if ($s === 'in process') return 'st-process';
        if ($s === 'completed') return 'st-completed';
        if ($s === 'cancelled') return 'st-cancelled';
        return 'st-other';
    };

    $statusTotals = $statusTotals ?? [];
@endphp

    <div class="ledger-container">
        @include('components.invoiceHeader')

        <div class="report-title">
            <h3>ORDER SUMMARY REPORT</h3>
        </div>

        <div class="meta-bar">
            <div class="meta-box">
                <h6>Filters</h6>
                <div class="meta-grid">
                    <span class="lbl">Period</span>
                    <span class="val">
                        @if(request('fromDate') || request('toDate'))
                            {{ $fromLabel }} to {{ $toLabel }}
                        @else
                            All Time
                        @endif
                    </span>
                    <span class="lbl">Customer</span>
                    <span class="val" title="{{ $customerLabel }}">{{ $customerLabel }}</span>
                    <span class="lbl">Area</span>
                    <span class="val">{{ $areaFilter }}</span>
                    <span class="lbl">Status</span>
                    <span class="val">{{ $statusFilter }}</span>
                </div>
            </div>
            <div class="meta-box">
                <h6>Report</h6>
                <div class="meta-grid">
                    <span class="lbl">Generated</span>
                    <span class="val">{{ now()->format('d-M-Y h:i A') }}</span>
                    <span class="lbl">Orders</span>
                    <span class="val">{{ $totalOrders }}</span>
                </div>
            </div>
        </div>

        <div class="status-stats" style="grid-template-columns: repeat({{ max(count($statusTotals), 1) }}, 1fr);">
            @foreach($statusTotals as $box)
                <div class="stat-box {{ $statusClass($box['status']) }}">
                    <div class="stat-line">
                        <span class="stat-label">{{ $box['status'] }} Order</span>
                        <span class="stat-value val-count">{{ $box['total'] }}</span>
                    </div>
                    <div class="stat-line">
                        <span class="stat-label">Order Value</span>
                        <span class="stat-value val-money">Rs. {{ number_format($box['value'], 0) }}</span>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="summary-stats">
            <div class="stat-box st-total">
                <div class="stat-line">
                    <span class="stat-label">Total Orders</span>
                    <span class="stat-value val-count">{{ $totalOrders }}</span>
                </div>
            </div>
            <div class="stat-box st-items">
                <div class="stat-line">
                    <span class="stat-label">Total Items</span>
                    <span class="stat-value val-count">{{ $totalItems }}</span>
                </div>
            </div>
            <div class="stat-box st-amount">
                <div class="stat-line">
                    <span class="stat-label">Total Amount</span>
                    <span class="stat-value">Rs. {{ $fmt($totalAmount) }}</span>
                </div>
                <div class="stat-line">
                    <span class="stat-label">Avg. Order</span>
                    <span class="stat-value val-money">Rs. {{ $fmt($avg) }}</span>
                </div>
            </div>
        </div>

        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width:4%;">#</th>
                    <th style="width:12%;">Order No</th>
                    <th style="width:12%;">Invoice No</th>
                    <th style="width:10%;">Date</th>
                    <th style="width:22%;">Customer</th>
                    <th style="width:12%;">City</th>
                    <th style="width:7%;">Items</th>
                    <th style="width:11%;">Amount</th>
                    <th style="width:10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $index => $order)
                    @php
                        try {
                            $dateLabel = $order->orderDate
                                ? \Carbon\Carbon::parse($order->orderDate)->format('d-M-Y')
                                : '—';
                        } catch (\Throwable $e) {
                            $dateLabel = '—';
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center" title="{{ $order->orderNo }}">{{ $order->orderNo ?: '—' }}</td>
                        <td class="text-center">{{ $order->converted_invoice_no ?: '—' }}</td>
                        <td class="text-center">{{ $dateLabel }}</td>
                        <td class="cell-desc" title="{{ $order->customerName }}">{{ $order->customerName ?: '—' }}</td>
                        <td class="text-center" title="{{ $order->city ?? '' }}">{{ $order->city ?: '—' }}</td>
                        <td class="text-center">{{ $order->noOfItems }}</td>
                        <td class="text-end amt">{{ $fmt($order->grandTotal) }}</td>
                        <td class="text-center">
                            <span class="status-pill {{ $statusClass($order->status) }}">{{ $order->status ?: '—' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="9">No orders found for the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($totalOrders > 0)
            <tfoot>
                <tr>
                    <td colspan="6" class="text-end">TOTAL</td>
                    <td class="text-center">{{ $totalItems }}</td>
                    <td class="text-end">{{ $fmt($totalAmount) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>

        <div class="report-footer">
            This is a computer-generated report. &copy; {{ date('Y') }} Trade Gate
        </div>
    </div>

    <a href="{{ route('order.summary', request()->query()) }}" class="btn btn-secondary btn-back no-print">
        <i class="bi bi-arrow-left"></i> Back
    </a>
    <button type="button" onclick="window.print()" class="btn btn-primary btn-print no-print">
        <i class="bi bi-printer"></i> Print Report
    </button>
</body>
</html>
