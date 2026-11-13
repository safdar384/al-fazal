<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DownloadsDomPdf;
use App\Support\ProfitLossReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ProfitLossReportController extends Controller
{
    use DownloadsDomPdf;

    public function __construct(
        protected ProfitLossReport $profitLoss
    ) {}

    public function statement(Request $request): View|Response
    {
        $range = $this->profitLoss->resolveDateRange($request);
        $figures = $this->profitLoss->computeForPeriod($range['from'], $range['to']);

        $viewData = [
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
            'figures' => $figures,
            'isGenerated' => true,
        ];

        if ($request->boolean('download_pdf')) {
            return $this->downloadDomPdf(
                'reports.profit-loss.pdf',
                $viewData,
                'profit-loss-'.$range['from'].'-'.$range['to'].'.pdf'
            );
        }

        if ($request->boolean('fragment')) {
            return view('reports.profit-loss.statement-live', $viewData);
        }

        return view('reports.profit-loss.statement', $viewData);
    }

    public function closings(Request $request): View
    {
        $range = $this->profitLoss->resolveDateRange($request);

        $viewData = [
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
            'closings' => $this->profitLoss->closingHistory(100),
            'preview' => $this->profitLoss->fullClosingPreview($range['from'], $range['to']),
        ];

        if ($request->boolean('fragment')) {
            return view('reports.profit-loss.closings-live', $viewData);
        }

        return view('reports.profit-loss.closings', $viewData);
    }

    public function storeClosing(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $this->profitLoss->storeClosing(
            $validated['from_date'],
            $validated['to_date'],
            $validated['notes'] ?? null
        );

        return redirect()
            ->route('reports.profit-loss.closings', [
                'from_date' => $validated['from_date'],
                'to_date' => $validated['to_date'],
            ])
            ->with('success', 'ERP balance closing saved successfully.');
    }

    public function closingHistoryReport(Request $request): View
    {
        $range = $this->profitLoss->resolveDateRange($request);
        $closings = $this->profitLoss->closingHistory(0, $range['from'], $range['to']);

        $latest = $closings->first();

        $viewData = [
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
            'closings' => $closings,
            'totals' => $this->summarizeClosings($closings),
            'reconciliation' => $latest
                ? $this->profitLoss->closingReconciliation($latest)
                : null,
        ];

        if ($request->boolean('fragment')) {
            return view('reports.profit-loss.closing-history-live', $viewData);
        }

        return view('reports.profit-loss.closing-history-report', $viewData);
    }

    public function closingHistoryShow(int $closing): View|Response
    {
        $record = $this->profitLoss->findClosing($closing);
        if (! $record) {
            abort(404, 'Closing record not found');
        }

        return view('reports.profit-loss.closing-history-detail', [
            'closing' => $record,
            'reconciliation' => $this->profitLoss->closingReconciliation($record),
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\ProfitLossClosing>  $closings
     * @return array<string, mixed>
     */
    protected function summarizeClosings($closings): array
    {
        $emptyBalances = [
            'customer_receivable' => 0.0,
            'customer_payable' => 0.0,
            'bank_balance' => 0.0,
            'labour_balance' => 0.0,
            'expense_balance' => 0.0,
            'stock_balance' => 0.0,
        ];

        if ($closings->isEmpty()) {
            return array_merge([
                'sale_amount' => 0.0,
                'cost_amount' => 0.0,
                'gross_profit' => 0.0,
                'net_profit' => 0.0,
                'all_total_balance' => 0.0,
                'latest_closed_at' => null,
            ], $emptyBalances);
        }

        $latest = $closings->first();
        $balances = [
            'customer_receivable' => (float) ($latest->customer_receivable ?? 0),
            'customer_payable' => (float) ($latest->customer_payable ?? 0),
            'bank_balance' => (float) ($latest->bank_balance ?? 0),
            'labour_balance' => (float) ($latest->labour_balance ?? 0),
            'expense_balance' => (float) ($latest->expense_balance ?? 0),
            'stock_balance' => (float) ($latest->stock_balance ?? 0),
        ];

        return array_merge([
            'sale_amount' => (float) $closings->sum('sale_amount'),
            'cost_amount' => (float) $closings->sum('cost_amount'),
            'gross_profit' => (float) $closings->sum(fn ($c) => (float) ($c->gross_profit ?? 0)),
            'net_profit' => (float) $closings->sum('net_profit'),
            'all_total_balance' => $this->profitLoss->erpAllTotalBalanceFromClosing($latest),
            'latest_closed_at' => $latest->closed_at,
        ], $balances);
    }
}
