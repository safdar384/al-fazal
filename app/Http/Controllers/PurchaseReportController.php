<?php

namespace App\Http\Controllers;

use App\Models\PurchaseInvoiceItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PurchaseReportController extends Controller
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

    public function bySupplier(Request $request): Response|View
    {
        return $this->renderReport($request, 'supplier');
    }

    public function bySupplierProduct(Request $request): Response|View
    {
        return $this->renderReport($request, 'supplier_product');
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
                return back()->with('error', 'PDF package is not installed. Please run composer install.');
            }

            $pdf = Pdf::loadView('reports.purchases.pdf', $viewData)
                ->setPaper('a4', 'portrait');

            return $pdf->download('purchase-report-'.$reportType.'-'.now()->format('YmdHis').'.pdf');
        }

        if ($request->boolean('fragment')) {
            return view('reports.purchases.live', $viewData);
        }

        return response()->view('reports.purchases.report', $viewData);
    }

    protected function buildRows(Request $request, string $reportType, string $fromDate, string $toDate): Collection
    {
        $query = PurchaseInvoiceItem::query()
            ->from('purchase_invoice_items as pii')
            ->join('purchase_invoices as pi', 'pi.id', '=', 'pii.purchase_invoice_id')
            ->leftJoin('product_info as p', 'p.productCode', '=', 'pii.productCode')
            ->whereBetween('pi.invoiceDate', [$fromDate, $toDate]);

        $filters = $this->resolveFilters($request);

        if ($filters['supplier_code']) {
            $query->where('pi.supplierCode', $filters['supplier_code']);
        }

        if ($filters['product_code']) {
            $query->where('pii.productCode', $filters['product_code']);
        }

        if ($filters['area']) {
            $query->where('pi.city', $filters['area']);
        }

        if ($filters['company']) {
            $query->where('p.companyName', $filters['company']);
        }

        return $query
            ->orderBy('pi.invoiceDate')
            ->orderBy('pi.invoiceNo')
            ->select([
                'pi.invoiceDate as report_date',
                'pi.invoiceNo as invoice_no',
                'pi.supplierCode as supplier_code',
                'pi.supplierName as supplier_name',
                DB::raw("COALESCE(pi.city, '') as area"),
                'pii.productCode as product_code',
                'pii.productName as product_name',
                DB::raw("COALESCE(p.companyName, '') as company_name"),
                DB::raw('COALESCE(pii.qty, 0) as quantity'),
                DB::raw('COALESCE(pii.amount, 0) as amount'),
            ])
            ->get()
            ->map(function ($row) use ($reportType) {
                if ($reportType === 'summary') {
                    $row->product_name = 'All Products';
                }
                if ($reportType === 'company' && $row->company_name !== '') {
                    $row->product_name = $row->product_name.' ('.$row->company_name.')';
                }
                if ($reportType === 'area' && $row->area !== '') {
                    $row->supplier_name = $row->supplier_name.' ['.$row->area.']';
                }

                return $row;
            });
    }

    protected function resolveFilters(Request $request): array
    {
        return [
            'supplier_code' => $request->string('supplier_code')->toString(),
            'product_code' => $request->string('product_code')->toString(),
            'area' => $request->string('area')->toString(),
            'company' => $request->string('company')->toString(),
        ];
    }

    protected function filterOptions(): array
    {
        return [
            'suppliers' => DB::table('purchase_invoices')
                ->whereNotNull('supplierCode')
                ->where('supplierCode', '!=', '')
                ->select('supplierCode', 'supplierName', DB::raw("COALESCE(MAX(city), '') as city"))
                ->groupBy('supplierCode', 'supplierName')
                ->orderBy('supplierName')
                ->get(),
            'products' => DB::table('product_info')
                ->orderBy('productName')
                ->get(['productCode', 'productName', 'companyName']),
            'areas' => DB::table('purchase_invoices')
                ->whereNotNull('city')
                ->where('city', '!=', '')
                ->select('city')
                ->distinct()
                ->orderBy('city')
                ->pluck('city'),
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
                'title' => 'Purchase Report Summary',
                'route' => 'reports.purchases.summary',
                'show' => ['supplier' => false, 'product' => false, 'company' => false, 'area' => false],
            ],
            'company' => [
                'title' => 'Purchase Report by Company',
                'route' => 'reports.purchases.by-company',
                'show' => ['supplier' => false, 'product' => false, 'company' => true, 'area' => false],
            ],
            'product' => [
                'title' => 'Purchase Report by Product',
                'route' => 'reports.purchases.by-product',
                'show' => ['supplier' => false, 'product' => true, 'company' => true, 'area' => false],
            ],
            'area' => [
                'title' => 'Purchase Report by Area',
                'route' => 'reports.purchases.by-area',
                'show' => ['supplier' => false, 'product' => false, 'company' => false, 'area' => true],
            ],
            'supplier' => [
                'title' => 'Purchase Report by Supplier',
                'route' => 'reports.purchases.by-supplier',
                'show' => ['supplier' => true, 'product' => false, 'company' => false, 'area' => true],
            ],
            'supplier_product' => [
                'title' => 'Purchase Report by Supplier and Product',
                'route' => 'reports.purchases.by-supplier-product',
                'show' => ['supplier' => true, 'product' => true, 'company' => false, 'area' => false],
            ],
            default => [
                'title' => 'Purchase Report',
                'route' => 'reports.purchases.summary',
                'show' => ['supplier' => false, 'product' => false, 'company' => false, 'area' => false],
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
