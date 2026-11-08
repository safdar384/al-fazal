<?php

namespace App\Http\Controllers;

use App\Models\NewCustomer;
use App\Models\SaleInvoiceItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SaleReportController extends Controller
{
    public function summary(Request $request): Response|View
    {
        return $this->renderReport($request, 'summary');
    }

    public function byCompany(Request $request): Response|View
    {
        return $this->renderReport($request, 'company');
    }

    public function byProduct(Request $request): Response|View
    {
        return $this->renderReport($request, 'product');
    }

    public function byArea(Request $request): Response|View
    {
        return $this->renderReport($request, 'area');
    }

    public function byCustomer(Request $request): Response|View
    {
        return $this->renderReport($request, 'customer');
    }

    public function byCustomerProduct(Request $request): Response|View
    {
        return $this->renderReport($request, 'customer_product');
    }

    protected function renderReport(Request $request, string $reportType): Response|View
    {
        $config = $this->reportConfig($reportType);
        $range = $this->resolveDateRange($request);
        $rows = $this->buildRows($request, $reportType, $range['from'], $range['to']);

        $summary = [
            'total_quantity' => (float) $rows->sum(fn ($row) => (float) $row->quantity),
            'total_amount' => (float) $rows->sum(fn ($row) => (float) $row->amount),
            'total_rows' => $rows->count(),
        ];

        $filters = $this->resolveFilters($request);
        $filterOptions = $this->filterOptions();

        $viewData = [
            'reportType' => $reportType,
            'config' => $config,
            'rows' => $rows,
            'summary' => $summary,
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
            'companyName' => config('app.name', 'TradeGate'),
        ];

        if ($request->boolean('download_pdf')) {
            if (! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                return back()->with('error', 'PDF package is not installed. Please run composer install after adding barryvdh/laravel-dompdf.');
            }

            $pdf = Pdf::loadView('reports.sales.pdf', $viewData)
                ->setPaper('a4', 'portrait');

            return $pdf->download('sale-report-'.$reportType.'-'.now()->format('YmdHis').'.pdf');
        }

        if ($request->boolean('fragment')) {
            return view('reports.sales.live', $viewData);
        }

        return response()->view('reports.sales.report', $viewData);
    }

    protected function buildRows(Request $request, string $reportType, string $fromDate, string $toDate): Collection
    {
        $query = SaleInvoiceItem::query()
            ->from('sale_invoice_items as sii')
            ->join('sale_invoices as si', 'si.id', '=', 'sii.sale_invoice_id')
            ->leftJoin('newcustomers as nc', 'nc.custCode', '=', 'si.customerCode')
            ->leftJoin('product_info as pi', 'pi.productCode', '=', 'sii.productCode')
            ->whereBetween('si.invoiceDate', [$fromDate, $toDate]);

        $filters = $this->resolveFilters($request);

        if ($filters['customer_code']) {
            $query->where('si.customerCode', $filters['customer_code']);
        }

        if ($filters['product_code']) {
            $query->where('sii.productCode', $filters['product_code']);
        }

        if ($filters['area']) {
            $query->where('nc.custArea', $filters['area']);
        }

        if ($filters['company']) {
            $query->where('pi.companyName', $filters['company']);
        }

        if ($filters['sale_type']) {
            $query->where('si.sale_type', $filters['sale_type']);
        }

        if ($reportType === 'customer_product') {
            if ($filters['customer_code']) {
                $query->where('si.customerCode', $filters['customer_code']);
            }
            if ($filters['product_code']) {
                $query->where('sii.productCode', $filters['product_code']);
            }
        }

        return $query
            ->orderBy('si.invoiceDate')
            ->orderBy('si.invoiceNo')
            ->select([
                'si.invoiceDate as report_date',
                'si.invoiceNo as invoice_no',
                'si.sale_type as sale_type',
                'si.payment_status as payment_status',
                'si.customerCode as customer_code',
                'si.customerName as customer_name',
                DB::raw("COALESCE(nc.bussnessName, '') as business_name"),
                DB::raw("COALESCE(nc.custArea, '') as area"),
                'sii.productCode as product_code',
                'sii.productName as product_name',
                DB::raw("COALESCE(pi.companyName, '') as company_name"),
                DB::raw('COALESCE(sii.qty, 0) as quantity'),
                DB::raw('COALESCE(sii.amount, 0) as amount'),
            ])
            ->get()
            ->map(function ($row) use ($reportType) {
                $displayBusiness = trim((string) ($row->business_name ?? ''));
                $displayArea = trim((string) ($row->area ?? ''));

                if ($displayBusiness !== '') {
                    $row->customer_name = $displayBusiness.($displayArea !== '' ? ' ('.$displayArea.')' : '');
                } elseif ($displayArea !== '') {
                    $row->customer_name = $row->customer_name.' ('.$displayArea.')';
                }

                if ($reportType === 'summary') {
                    $row->product_name = 'All Products';
                }
                if ($reportType === 'company' && $row->company_name !== '') {
                    $row->product_name = $row->product_name.' ('.$row->company_name.')';
                }

                return $row;
            });
    }

    protected function resolveFilters(Request $request): array
    {
        return [
            'customer_code' => $request->string('customer_code')->toString(),
            'product_code' => $request->string('product_code')->toString(),
            'area' => $request->string('area')->toString(),
            'company' => $request->string('company')->toString(),
            'sale_type' => $request->string('sale_type')->toString(),
        ];
    }

    protected function filterOptions(): array
    {
        return [
            'customers' => NewCustomer::query()
                ->excludeProductSupplier()
                ->orderBy('custName')
                ->get(['custCode', 'custName', 'custArea']),
            'products' => DB::table('product_info')
                ->orderBy('productName')
                ->get(['productCode', 'productName', 'companyName']),
            'areas' => NewCustomer::query()
                ->whereNotNull('custArea')
                ->where('custArea', '!=', '')
                ->select('custArea')
                ->distinct()
                ->orderBy('custArea')
                ->pluck('custArea'),
            'companies' => DB::table('product_info')
                ->whereNotNull('companyName')
                ->where('companyName', '!=', '')
                ->select('companyName')
                ->distinct()
                ->orderBy('companyName')
                ->pluck('companyName'),
        ];
    }

    protected function reportConfig(string $reportType): array
    {
        return match ($reportType) {
            'summary' => [
                'title' => 'Sale Report Summary',
                'route' => 'reports.sales.summary',
                'show' => ['customer' => false, 'product' => false, 'company' => false, 'area' => false],
            ],
            'company' => [
                'title' => 'Sale Report by Company',
                'route' => 'reports.sales.by-company',
                'show' => ['customer' => false, 'product' => false, 'company' => true, 'area' => false],
            ],
            'product' => [
                'title' => 'Sale Report by Product',
                'route' => 'reports.sales.by-product',
                'show' => ['customer' => false, 'product' => true, 'company' => true, 'area' => false],
            ],
            'area' => [
                'title' => 'Sale Report by Area',
                'route' => 'reports.sales.by-area',
                'show' => ['customer' => false, 'product' => false, 'company' => false, 'area' => true],
            ],
            'customer' => [
                'title' => 'Sale Report by Customer',
                'route' => 'reports.sales.by-customer',
                'show' => ['customer' => true, 'product' => false, 'company' => false, 'area' => true],
            ],
            'customer_product' => [
                'title' => 'Sale Report by Customer and Product',
                'route' => 'reports.sales.by-customer-product',
                'show' => ['customer' => true, 'product' => true, 'company' => false, 'area' => false],
            ],
            default => [
                'title' => 'Sale Report',
                'route' => 'reports.sales.summary',
                'show' => ['customer' => false, 'product' => false, 'company' => false, 'area' => false],
            ],
        };
    }

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

        return ['from' => $from, 'to' => $to];
    }
}
