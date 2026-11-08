<?php

namespace App\Http\Controllers;

use App\Models\NewCustomer;
use App\Support\CustomerBalanceReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerReportController extends Controller
{
    public function balance(Request $request): Response|View
    {
        return $this->renderReport($request, 'balance');
    }

    public function balanceByArea(Request $request): Response|View
    {
        return $this->renderReport($request, 'balance_area');
    }

    public function balanceByName(Request $request): Response|View
    {
        return $this->renderReport($request, 'balance_name');
    }

    protected function renderReport(Request $request, string $reportType): Response|View
    {
        $config = $this->reportConfig($reportType);
        $range = $this->resolveDateRange($request);
        $filters = CustomerBalanceReport::resolveFilters($request);
        $rows = CustomerBalanceReport::buildRows($range['from'], $range['to'], $filters);
        $summary = CustomerBalanceReport::summarize($rows);

        $viewData = [
            'reportType' => $reportType,
            'config' => $config,
            'rows' => $rows,
            'summary' => $summary,
            'filters' => $filters,
            'filterOptions' => $this->filterOptions(),
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
            'companyName' => config('app.name', 'TradeGate'),
        ];

        if ($request->boolean('download_pdf')) {
            if (! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                return back()->with('error', 'PDF package is not installed. Please run composer install.');
            }

            $pdf = Pdf::loadView('reports.customers.pdf', $viewData)
                ->setPaper('a4', 'portrait');

            return $pdf->download('customer-report-'.$reportType.'-'.now()->format('YmdHis').'.pdf');
        }

        if ($request->boolean('fragment')) {
            return view('reports.customers.live', $viewData);
        }

        return response()->view('reports.customers.report', $viewData);
    }

    protected function filterOptions(): array
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
        ];
    }

    protected function reportConfig(string $reportType): array
    {
        return match ($reportType) {
            'balance' => [
                'title' => 'Customer Balance',
                'route' => 'reports.customers.balance',
                'show' => ['area' => false, 'customer' => false],
            ],
            'balance_area' => [
                'title' => 'Customer Balance by Area',
                'route' => 'reports.customers.balance-area',
                'show' => ['area' => true, 'customer' => false],
            ],
            'balance_name' => [
                'title' => 'Customer Balance by Name',
                'route' => 'reports.customers.balance-name',
                'show' => ['area' => true, 'customer' => true],
            ],
            default => [
                'title' => 'Customer Balance',
                'route' => 'reports.customers.balance',
                'show' => ['area' => false, 'customer' => false],
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
