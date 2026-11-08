<?php

namespace App\Http\Controllers;

use App\Services\BiltyGudsReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BiltyGudsReportController extends Controller
{
    public function __construct(
        protected BiltyGudsReportService $reports
    ) {}

    public function index(): View
    {
        $range = $this->reports->resolveDateRange(request());

        return view('reports.bilty-guds.index', [
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
            'gudsOptions' => $this->reports->gudsOptions(),
            'companyName' => config('app.name', 'TradeGate'),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $range = $this->reports->resolveDateRange($request);
        $filters = $this->reports->resolveFilters($request);
        $report = $this->reports->build($range, $filters);

        $html = view('reports.bilty-guds.partials.data-table', [
            'report' => $report,
            'filters' => $filters,
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
        ])->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'counts' => $report['counts'],
            'grand' => $report['grand'],
            'meta' => [
                'from_date' => $range['from'],
                'to_date' => $range['to'],
                'guds_name' => $filters['guds_name'],
                'delivery_status' => $filters['delivery_status'],
                'only_with_bilty' => $filters['only_with_bilty'],
            ],
        ]);
    }

    public function print(Request $request): View
    {
        $range = $this->reports->resolveDateRange($request);
        $filters = $this->reports->resolveFilters($request);
        $report = $this->reports->build($range, $filters);

        return view('reports.bilty-guds.print', [
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
            'filters' => $filters,
            'report' => $report,
            'companyName' => config('app.name', 'TradeGate'),
        ]);
    }

    public function bulkDelivered(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_nos' => 'required|array|min:1',
            'invoice_nos.*' => 'string|max:100',
            'delivered' => 'required|boolean',
        ]);

        try {
            $updated = $this->reports->bulkUpdateDelivered(
                $validated['invoice_nos'],
                (bool) $validated['delivered']
            );

            return response()->json([
                'success' => true,
                'updated' => $updated,
                'delivered' => (bool) $validated['delivered'],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Could not update delivered status.',
            ], 422);
        }
    }
}
