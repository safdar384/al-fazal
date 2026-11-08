<?php

namespace App\Http\Controllers;

use App\Models\NewCustomer;
use App\Models\Order;
use App\Services\OrderReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderReportController extends Controller
{
    public function __construct(
        protected OrderReportService $reports
    ) {}

    public function summary(Request $request): Response|View
    {
        return $this->render($request, 'summary', 'Order Summary Report');
    }

    public function pending(Request $request): Response|View
    {
        $request->merge(['status' => $request->query('status', 'Pending')]);

        return $this->render($request, 'pending', 'Pending Orders Report');
    }

    public function customerHistory(Request $request): Response|View
    {
        return $this->render($request, 'customer', 'Customer Order History');
    }

    protected function render(Request $request, string $type, string $title): Response|View
    {
        $range = $this->reports->resolveDateRange($request);
        $filters = $this->reports->resolveFilters($request);
        $overall = $this->reports->overallSummary($range, $filters, $type);
        $rows = $this->reports->buildRows($range, $filters, $type);

        $viewData = [
            'reportType' => $type,
            'title' => $title,
            'overall' => $overall,
            'rows' => $rows,
            'filters' => $filters,
            'filterOptions' => $this->reports->filterOptions(),
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
        ];

        if ($request->boolean('download_pdf')) {
            if (! class_exists(Pdf::class)) {
                return back()->with('error', 'PDF package not installed.');
            }

            $pdf = Pdf::loadView('reports.orders.pdf', $viewData)->setPaper('a4', 'portrait');

            return $pdf->download('order-report-'.$type.'-'.now()->format('YmdHis').'.pdf');
        }

        if ($request->boolean('fragment')) {
            return view('reports.orders.live', $viewData);
        }

        return view('reports.orders.report', $viewData);
    }
}
