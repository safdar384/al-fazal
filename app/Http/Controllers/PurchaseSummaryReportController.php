<?php

namespace App\Http\Controllers;

use App\Services\PurchaseSummaryReportService;
use App\Support\ListPage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PurchaseSummaryReportController extends Controller
{
    public function __construct(
        protected PurchaseSummaryReportService $reports
    ) {}

    public function index(Request $request): Response|View
    {
        $range = $this->reports->resolveDateRange($request);
        $filters = $this->reports->resolveFilters($request);
        $tab = $request->query('tab', 'invoices');

        $viewData = [
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
            'filters' => $filters,
            'filterOptions' => $this->reports->filterOptions(),
            'overall' => $this->reports->overallSummary($range, $filters),
            'tab' => $tab,
            'invoices' => $tab === 'invoices' ? $this->reports->invoiceRows($range, $filters) : collect(),
            'suppliers' => $tab === 'suppliers' ? $this->reports->supplierRows($range, $filters) : collect(),
            'pendingSuppliers' => $tab === 'pending' ? $this->reports->pendingSupplierBalances($range) : collect(),
        ];

        if ($request->boolean('print')) {
            return view('reports.purchases-summary.print', $viewData);
        }

        if ($request->boolean('download_pdf')) {
            $viewData['invoices'] = $this->reports->invoiceRows($range, $filters);
            $viewData['suppliers'] = $this->reports->supplierRows($range, $filters);

            $pdf = Pdf::loadView('reports.purchases-summary.pdf', $viewData)->setPaper('a4', 'landscape');

            return $pdf->download('purchase-summary-'.now()->format('YmdHis').'.pdf');
        }

        if ($tab === 'invoices') {
            $viewData['invoices'] = ListPage::paginate($request, $viewData['invoices']);
        } elseif ($tab === 'suppliers') {
            $viewData['suppliers'] = ListPage::paginate($request, $viewData['suppliers']);
        } elseif ($tab === 'pending') {
            $viewData['pendingSuppliers'] = ListPage::paginate($request, $viewData['pendingSuppliers']);
        }

        if ($request->boolean('fragment')) {
            return view('reports.purchases-summary.live', $viewData);
        }

        return view('reports.purchases-summary.index', $viewData);
    }
}
