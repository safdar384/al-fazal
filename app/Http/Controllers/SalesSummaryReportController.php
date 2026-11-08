<?php

namespace App\Http\Controllers;

use App\Services\SalesSummaryReportService;
use App\Support\ListPage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesSummaryReportController extends Controller
{
    public function __construct(
        protected SalesSummaryReportService $reports
    ) {}

    public function index(Request $request): Response|View|StreamedResponse
    {
        $range = $this->reports->resolveDateRange($request);
        $filters = $this->reports->resolveFilters($request);
        $tab = $request->query('tab', 'invoices');
        if (! in_array($tab, ['invoices', 'products', 'customers'], true)) {
            $tab = 'invoices';
        }

        $overall = $this->reports->overallSummary($range, $filters);
        $filterOptions = $this->reports->filterOptions();

        if ($request->boolean('print')) {
            return view('reports.sales-summary.print', [
                'fromDate' => $range['from'],
                'toDate' => $range['to'],
                'filters' => $filters,
                'filterOptions' => $filterOptions,
                'overall' => $overall,
                'invoices' => $this->reports->invoicesForExport($range, $filters),
                'products' => $this->reports->productSummary($range, $filters),
                'customers' => $this->reports->customerSummary($range, $filters),
            ]);
        }

        $isFragment = $request->boolean('fragment');
        if ($isFragment) {
            $request->query->remove('fragment');
        }

        $invoices = null;
        $products = null;
        $customers = null;

        $productTotals = null;
        $customerTotals = null;

        if ($tab === 'invoices') {
            $invoices = $this->reports->paginateInvoices($request, $range, $filters);
        } elseif ($tab === 'products') {
            $allProducts = $this->reports->productSummary($range, $filters);
            $productTotals = [
                'qty' => $allProducts->sum('total_qty'),
                'gross' => $allProducts->sum('gross_amount'),
                'discount' => $allProducts->sum('total_discount'),
                'sales' => $allProducts->sum('total_sales_value'),
            ];
            $products = ListPage::paginate($request, $allProducts);
        } else {
            $allCustomers = $this->reports->customerSummary($range, $filters);
            $customerTotals = [
                'invoices' => $allCustomers->sum('invoice_count'),
                'sales' => $allCustomers->sum('total_sales'),
                'discount' => $allCustomers->sum('total_discount'),
                'paid' => $allCustomers->sum('total_paid'),
                'outstanding' => $allCustomers->sum('total_outstanding'),
            ];
            $customers = ListPage::paginate($request, $allCustomers);
        }

        $viewData = [
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'overall' => $overall,
            'tab' => $tab,
            'invoices' => $invoices,
            'products' => $products,
            'customers' => $customers,
            'productTotals' => $productTotals,
            'customerTotals' => $customerTotals,
            'companyName' => config('app.name', 'TradeGate'),
        ];

        if ($request->boolean('download_pdf')) {
            return $this->downloadPdf($viewData, $range, $filters);
        }

        if ($request->boolean('export_excel')) {
            return $this->exportCsv($range, $filters);
        }

        if ($isFragment) {
            return view('reports.sales-summary.live', $viewData);
        }

        return view('reports.sales-summary.index', $viewData);
    }

    /**
     * @param  array<string, mixed>  $viewData
     */
    protected function downloadPdf(array $viewData, array $range, array $filters): Response|View
    {
        if (! class_exists(Pdf::class)) {
            return back()->with('error', 'PDF package is not installed. Run: composer require barryvdh/laravel-dompdf');
        }

        $viewData['invoices'] = $this->reports->invoicesForExport($range, $filters);
        $viewData['products'] = $this->reports->productSummary($range, $filters);
        $viewData['customers'] = $this->reports->customerSummary($range, $filters);

        $pdf = Pdf::loadView('reports.sales-summary.pdf', $viewData)
            ->setPaper('a4', 'landscape');

        return $pdf->download('sales-summary-'.now()->format('YmdHis').'.pdf');
    }

    /**
     * @param  array{from: string, to: string}  $range
     * @param  array<string, string>  $filters
     */
    protected function exportCsv(array $range, array $filters): StreamedResponse
    {
        $rows = $this->reports->invoicesForExport($range, $filters);
        $filename = 'sales-summary-'.$range['from'].'-'.$range['to'].'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Date', 'Invoice No', 'Customer', 'Type', 'Qty', 'Gross', 'Discount', 'Net', 'Paid', 'Balance',
            ]);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->invoiceDate,
                    $row->invoiceNo,
                    $row->customerName,
                    $row->sale_type_label,
                    $row->total_qty,
                    $row->gross_amount,
                    $row->total_discount,
                    $row->net_amount,
                    $row->paid_amount,
                    $row->balance,
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
