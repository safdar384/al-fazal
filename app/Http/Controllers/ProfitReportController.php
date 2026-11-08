<?php

namespace App\Http\Controllers;

use App\Models\CustomerArea;
use App\Models\NewCustomer;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ProfitReportController extends Controller
{
    public function all(Request $request): Response|View
    {
        return $this->renderReport($request, 'all');
    }

    public function byArea(Request $request): Response|View
    {
        return $this->renderReport($request, 'area');
    }

    public function byCustomer(Request $request): Response|View
    {
        return $this->renderReport($request, 'customer');
    }

    public function byAjent(Request $request): Response|View
    {
        return $this->renderReport($request, 'ajent');
    }

    /** @deprecated Use sale invoice profit routes */
    public function index(Request $request): Response|View
    {
        return $this->all($request);
    }

    /** @deprecated Use sale invoice profit routes */
    public function generate(Request $request): Response|View
    {
        return $this->all($request);
    }

    protected function renderReport(Request $request, string $reportType): Response|View
    {
        $config = $this->reportConfig($reportType);
        $range = $this->resolveDateRange($request);
        $filters = $this->resolveFilters($request);
        $rows = $this->buildInvoiceRows($filters, $range['from'], $range['to']);

        $summary = [
            'total_invoices' => $rows->count(),
            'total_qty' => (float) $rows->sum('qty_sold'),
            'total_sale' => (float) $rows->sum('sale_amount'),
            'total_cost' => (float) $rows->sum('cost_amount'),
            'total_profit' => (float) $rows->sum('profit_amount'),
        ];

        $viewData = [
            'reportType' => $reportType,
            'config' => $config,
            'rows' => $rows,
            'summary' => $summary,
            'filters' => $filters,
            'filterOptions' => $this->filterOptions($range['from'], $range['to']),
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
        ];

        if ($request->boolean('download_pdf')) {
            return Pdf::loadView('reports.sale-profit.pdf', $viewData)
                ->setPaper('a4', 'landscape')
                ->download('sale-invoice-profit-'.$reportType.'-'.now()->format('YmdHis').'.pdf');
        }

        if ($request->boolean('fragment')) {
            return view('reports.sale-profit.live', $viewData);
        }

        return response()->view('reports.sale-profit.report', $viewData);
    }

    /**
     * One row per sale invoice with sale / cost / profit.
     */
    protected function buildInvoiceRows(array $filters, string $fromDate, string $toDate): Collection
    {
        $query = DB::table('sale_invoices as si')
            ->join('sale_invoice_items as sii', 'sii.sale_invoice_id', '=', 'si.id')
            ->leftJoin('newcustomers as nc', 'nc.custCode', '=', 'si.customerCode')
            ->leftJoin('customerareas as ca', 'ca.areaName', '=', 'nc.custArea')
            ->whereBetween('si.invoiceDate', [$fromDate, $toDate]);

        if ($filters['area'] !== '') {
            $query->where('nc.custArea', $filters['area']);
        }

        if ($filters['customer_code'] !== '') {
            $query->where('si.customerCode', $filters['customer_code']);
        }

        if ($filters['ajent'] !== '') {
            $query->where('ca.ajentName', $filters['ajent']);
        }

        if ($filters['sale_type'] !== '') {
            $query->where('si.sale_type', $filters['sale_type']);
        }

        if ($filters['invoice_no'] !== '') {
            $query->where('si.invoiceNo', $filters['invoice_no']);
        }

        return $query
            ->groupBy('si.id')
            ->orderBy(DB::raw('MAX(si.invoiceDate)'))
            ->orderBy(DB::raw('MAX(si.invoiceNo)'))
            ->select([
                'si.id as invoice_id',
                DB::raw('MAX(si.invoiceNo) as invoice_no'),
                DB::raw('MAX(si.invoiceDate) as invoice_date'),
                DB::raw('MAX(si.customerCode) as customer_code'),
                DB::raw('MAX(si.customerName) as customer_name'),
                DB::raw('MAX(si.sale_type) as sale_type'),
                DB::raw('MAX(COALESCE(si.grandTotal, 0)) as grand_total'),
                DB::raw('MAX(COALESCE(si.biltyExpanse, 0)) + MAX(COALESCE(si.loaderExpanse, 0)) as bilty_amount'),
                DB::raw("MAX(COALESCE(nc.custArea, '')) as area"),
                DB::raw("MAX(COALESCE(nc.bussnessName, '')) as business_name"),
                DB::raw("MAX(COALESCE(ca.ajentName, '')) as ajent_name"),
                DB::raw('SUM(COALESCE(sii.qty, 0)) as qty_sold'),
                DB::raw('SUM(COALESCE(sii.amount, 0)) as item_sale_amount'),
                DB::raw('SUM(COALESCE(sii.qty, 0) * COALESCE(sii.cost_rate, 0)) as cost_amount'),
            ])
            ->get()
            ->map(function ($row) {
                $saleAmount = (float) ($row->item_sale_amount ?? 0);
                $costAmount = (float) ($row->cost_amount ?? 0);
                $bilty = (float) ($row->bilty_amount ?? 0);
                $profit = $saleAmount - $costAmount + $bilty;

                $displayName = trim((string) ($row->business_name ?? ''));
                if ($displayName === '') {
                    $displayName = (string) ($row->customer_name ?? '');
                }

                return (object) [
                    'invoice_id' => $row->invoice_id,
                    'invoice_no' => $row->invoice_no,
                    'invoice_date' => $row->invoice_date,
                    'customer_code' => $row->customer_code,
                    'customer_name' => $displayName,
                    'area' => (string) ($row->area ?: '—'),
                    'ajent_name' => (string) ($row->ajent_name ?: '—'),
                    'sale_type' => $row->sale_type ?: 'credit',
                    'qty_sold' => (float) ($row->qty_sold ?? 0),
                    'sale_amount' => $saleAmount,
                    'cost_amount' => $costAmount,
                    'bilty_amount' => $bilty,
                    'profit_amount' => $profit,
                    'grand_total' => (float) ($row->grand_total ?? 0),
                ];
            });
    }

    protected function resolveFilters(Request $request): array
    {
        return [
            'area' => trim((string) $request->input('area', '')),
            'customer_code' => trim((string) $request->input('customer_code', '')),
            'ajent' => trim((string) $request->input('ajent', '')),
            'sale_type' => trim((string) $request->input('sale_type', '')),
            'invoice_no' => trim((string) $request->input('invoice_no', '')),
        ];
    }

    protected function filterOptions(string $fromDate, string $toDate): array
    {
        return [
            'areas' => NewCustomer::query()
                ->whereNotNull('custArea')
                ->where('custArea', '!=', '')
                ->select('custArea')
                ->distinct()
                ->orderBy('custArea')
                ->pluck('custArea'),
            'customers' => NewCustomer::query()
                ->excludeProductSupplier()
                ->orderBy('custName')
                ->get(['custCode', 'custName', 'bussnessName', 'custArea']),
            'ajents' => CustomerArea::query()
                ->whereNotNull('ajentName')
                ->where('ajentName', '!=', '')
                ->select('ajentName')
                ->distinct()
                ->orderBy('ajentName')
                ->pluck('ajentName'),
            'ajent_areas' => CustomerArea::query()
                ->whereNotNull('ajentName')
                ->where('ajentName', '!=', '')
                ->whereNotNull('areaName')
                ->where('areaName', '!=', '')
                ->orderBy('areaName')
                ->get(['areaName', 'ajentName']),
            'invoices' => DB::table('sale_invoices')
                ->whereBetween('invoiceDate', [$fromDate, $toDate])
                ->orderByDesc('invoiceDate')
                ->orderByDesc('invoiceNo')
                ->get(['invoiceNo', 'invoiceDate', 'customerName']),
        ];
    }

    protected function reportConfig(string $reportType): array
    {
        return match ($reportType) {
            'area' => [
                'title' => 'Sale Invoice Profit by Area',
                'route' => 'reports.sale-profit.by-area',
                'show' => ['area' => true, 'customer' => false, 'ajent' => false, 'invoice' => false],
            ],
            'customer' => [
                'title' => 'Sale Invoice Profit by Customer',
                'route' => 'reports.sale-profit.by-customer',
                'show' => ['area' => true, 'customer' => true, 'ajent' => false, 'invoice' => false],
            ],
            'ajent' => [
                'title' => 'Sale Invoice Profit by Ajent',
                'route' => 'reports.sale-profit.by-ajent',
                'show' => ['area' => false, 'customer' => false, 'ajent' => true, 'invoice' => false],
            ],
            default => [
                'title' => 'Sale Invoice Profit (All)',
                'route' => 'reports.sale-profit.all',
                'show' => ['area' => false, 'customer' => false, 'ajent' => false, 'invoice' => true],
            ],
        };
    }

    protected function resolveDateRange(Request $request): array
    {
        $defaultFrom = now()->startOfMonth()->format('Y-m-d');
        $defaultTo = now()->format('Y-m-d');

        try {
            $from = Carbon::parse($request->input('from_date', $defaultFrom))->format('Y-m-d');
            $to = Carbon::parse($request->input('to_date', $defaultTo))->format('Y-m-d');
        } catch (\Throwable) {
            $from = $defaultFrom;
            $to = $defaultTo;
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return ['from' => $from, 'to' => $to];
    }
}
