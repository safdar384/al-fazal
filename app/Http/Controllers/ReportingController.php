<?php

namespace App\Http\Controllers;

use App\Models\ExpenseLedger;
use App\Models\PurchaseInvoice;
use App\Models\SaleInvoice;
use App\Support\CustomerBalanceReport;
use App\Support\PartyBalance;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportingController extends Controller
{
    /**
     * Reporting hub — links to each detailed report.
     */
    public function hub(): View
    {
        $range = $this->resolveDateRange(request());

        return view('reports.hub', [
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
            'portfolio' => $this->portfolioSnapshot(),
        ]);
    }

    /**
     * AJAX: refresh portfolio KPIs without full page reload.
     */
    public function hubPortfolio(): JsonResponse
    {
        $portfolio = $this->portfolioSnapshot();

        return response()->json([
            'success' => true,
            'portfolio' => $portfolio,
            'formatted' => [
                'total_receivable' => number_format($portfolio['total_receivable'], 2),
                'total_payable' => number_format($portfolio['total_payable'], 2),
                'net_balance' => number_format($portfolio['net_balance'], 2),
            ],
        ]);
    }

    /**
     * @return array{total_receivable: float, total_payable: float, net_balance: float}
     */
    protected function portfolioSnapshot(): array
    {
        $portfolio = PartyBalance::portfolioTotals();
        $portfolio['net_balance'] = round($portfolio['total_payable'] - $portfolio['total_receivable'], 2);

        return $portfolio;
    }

    public function sale(Request $request): View
    {
        $range = $this->resolveDateRange($request);

        $rows = SaleInvoice::query()
            ->whereBetween('invoiceDate', [$range['from'], $range['to']])
            ->orderBy('invoiceDate')
            ->orderBy('id')
            ->get();

        $totals = [
            'invoices' => $rows->count(),
            'items' => (int) $rows->sum('noOfItems'),
            'invoice_total' => (float) $rows->sum('invoiceTotal'),
            'discount' => (float) $rows->sum('invoiceDiscount'),
            'grand_total' => (float) $rows->sum('grandTotal'),
        ];

        return view('reports.sale', [
            'rows' => $rows,
            'totals' => $totals,
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
        ]);
    }

    public function purchase(Request $request): View
    {
        $range = $this->resolveDateRange($request);

        $rows = PurchaseInvoice::query()
            ->whereBetween('invoiceDate', [$range['from'], $range['to']])
            ->orderBy('invoiceDate')
            ->orderBy('id')
            ->get();

        $totals = [
            'invoices' => $rows->count(),
            'items' => (int) $rows->sum('noOfItems'),
            'invoice_total' => (float) $rows->sum('invoiceTotal'),
            'discount' => (float) $rows->sum('invoiceDiscount'),
            'grand_total' => (float) $rows->sum('grandTotal'),
        ];

        return view('reports.purchase', [
            'rows' => $rows,
            'totals' => $totals,
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
        ]);
    }

    public function customer(Request $request): View
    {
        $range = $this->resolveDateRange($request);
        $rows = CustomerBalanceReport::buildRows($range['from'], $range['to']);
        $summary = CustomerBalanceReport::summarize($rows);

        return view('reports.customer', [
            'rows' => $rows,
            'summary' => $summary,
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
        ]);
    }

    public function labor(Request $request): View
    {
        $range = $this->resolveDateRange($request);

        $fromDt = $range['from'].' 00:00:00';
        $toDt = $range['to'].' 23:59:59';

        $rows = DB::table('bom_labors as bl')
            ->join('boms as b', 'b.id', '=', 'bl.bom_id')
            ->leftJoin('product_info as p', 'p.productCode', '=', 'b.productCode')
            ->whereBetween('bl.created_at', [$fromDt, $toDt])
            ->orderBy('bl.created_at')
            ->select([
                'bl.id',
                'bl.operation_name',
                'bl.time_minutes',
                'bl.rate_per_minute',
                'bl.created_at',
                'b.productCode',
                'b.status as bom_status',
                DB::raw('COALESCE(p.productName, b.productCode) as product_name'),
            ])
            ->get()
            ->map(function ($row) {
                $minutes = (float) $row->time_minutes;
                $rate = (float) $row->rate_per_minute;
                $row->line_cost = round($minutes * $rate, 2);

                return $row;
            });

        $totals = [
            'lines' => $rows->count(),
            'minutes' => (float) $rows->sum(fn ($r) => (float) $r->time_minutes),
            'estimated_cost' => (float) $rows->sum('line_cost'),
        ];

        return view('reports.labor', [
            'rows' => $rows,
            'totals' => $totals,
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
        ]);
    }

    public function expense(Request $request): View
    {
        $range = $this->resolveDateRange($request);

        $rows = ExpenseLedger::query()
            ->whereBetween('entryDate', [$range['from'], $range['to']])
            ->orderBy('entryDate')
            ->orderBy('id')
            ->get();

        $totals = [
            'entries' => $rows->count(),
            'debit' => (float) $rows->sum('debit'),
            'credit' => (float) $rows->sum('credit'),
            'amount' => (float) $rows->sum('amount'),
        ];

        return view('reports.expense', [
            'rows' => $rows,
            'totals' => $totals,
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
        ]);
    }

    public function product(Request $request): View
    {
        $range = $this->resolveDateRange($request);

        $rows = DB::table('sale_invoice_items as si')
            ->join('sale_invoices as inv', 'inv.id', '=', 'si.sale_invoice_id')
            ->leftJoin('product_info as p', 'p.productCode', '=', 'si.productCode')
            ->whereBetween('inv.invoiceDate', [$range['from'], $range['to']])
            ->groupBy('si.productCode', 'si.productName')
            ->orderByDesc(DB::raw('SUM(si.amount)'))
            ->select([
                'si.productCode',
                'si.productName',
                DB::raw('MAX(p.catagoryName) as category_name'),
                DB::raw('MAX(p.stockQty) as stock_qty'),
                DB::raw('SUM(si.qty) as total_qty'),
                DB::raw('SUM(si.amount) as total_amount'),
                DB::raw('COUNT(*) as line_count'),
            ])
            ->get();

        $totals = [
            'products' => $rows->count(),
            'qty' => (float) $rows->sum('total_qty'),
            'amount' => (float) $rows->sum('total_amount'),
            'lines' => (int) $rows->sum('line_count'),
        ];

        return view('reports.product', [
            'rows' => $rows,
            'totals' => $totals,
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
        ]);
    }

    /**
     * @return array{from: string, to: string}
     */
    protected function resolveDateRange(Request $request): array
    {
        $defaultFrom = now()->startOfMonth()->format('Y-m-d');
        $defaultTo = now()->format('Y-m-d');

        $fromInput = $request->input('from_date', $defaultFrom);
        $toInput = $request->input('to_date', $defaultTo);

        try {
            $from = Carbon::parse($fromInput)->format('Y-m-d');
            $to = Carbon::parse($toInput)->format('Y-m-d');
        } catch (\Throwable) {
            $from = $defaultFrom;
            $to = $defaultTo;
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [
            'from' => $from,
            'to' => $to,
        ];
    }
}
