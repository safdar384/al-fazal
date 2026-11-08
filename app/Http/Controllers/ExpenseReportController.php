<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseLedger;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class ExpenseReportController extends Controller
{
    public function summary(Request $request): Response|View
    {
        return $this->renderReport($request, 'summary');
    }

    public function byCategory(Request $request): Response|View
    {
        return $this->renderReport($request, 'category');
    }

    protected function renderReport(Request $request, string $reportType): Response|View
    {
        $config = $this->reportConfig($reportType);
        $range = $this->resolveDateRange($request);
        $filters = $this->resolveFilters($request);

        if ($reportType === 'category') {
            $rows = $this->buildCategoryRows($range['from'], $range['to'], $filters);
            $summary = [
                'total_rows' => $rows->count(),
                'total_entries' => (int) $rows->sum('entry_count'),
                'total_amount' => (float) $rows->sum('total_amount'),
            ];
        } else {
            $rows = $this->buildDetailRows($range['from'], $range['to'], $filters);
            $summary = [
                'total_rows' => $rows->count(),
                'total_entries' => $rows->count(),
                'total_amount' => (float) $rows->sum(fn ($r) => (float) $r->amount),
            ];
        }

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
            if (! class_exists(Pdf::class)) {
                return back()->with('error', 'PDF package is not installed.');
            }

            $pdf = Pdf::loadView('reports.expense.pdf', $viewData)->setPaper('a4', 'landscape');

            return $pdf->download('expense-report-'.$reportType.'-'.now()->format('YmdHis').'.pdf');
        }

        if ($request->boolean('fragment')) {
            return view('reports.expense.live', $viewData);
        }

        return response()->view('reports.expense.report', $viewData);
    }

    /**
     * @param  array{category: string, expense_code: string}  $filters
     */
    protected function buildDetailRows(string $fromDate, string $toDate, array $filters): Collection
    {
        $query = ExpenseLedger::query()
            ->whereBetween('entryDate', [$fromDate, $toDate])
            ->orderBy('entryDate')
            ->orderBy('id');

        if ($filters['category'] !== '') {
            $query->where(function ($q) use ($filters) {
                $q->where('expenseCategory', $filters['category'])
                    ->orWhere('category', $filters['category']);
            });
        }

        if ($filters['expense_code'] !== '') {
            $query->where('expenseCode', $filters['expense_code']);
        }

        return $query->get()->map(function (ExpenseLedger $row) {
            $category = trim((string) ($row->expenseCategory ?: $row->category ?: ''));

            return (object) [
                'report_date' => $row->entryDate?->format('Y-m-d'),
                'expense_code' => $row->expenseCode,
                'category' => $category !== '' ? $category : '—',
                'description' => $row->description ?: '—',
                'payment_method' => $row->paymentMethod ?: '—',
                'reference_no' => $row->referenceNo ?: '—',
                'amount' => (float) $row->amount,
            ];
        });
    }

    /**
     * @param  array{category: string, expense_code: string}  $filters
     */
    protected function buildCategoryRows(string $fromDate, string $toDate, array $filters): Collection
    {
        return $this->buildDetailRows($fromDate, $toDate, $filters)
            ->groupBy(fn ($row) => $row->category)
            ->map(function (Collection $group, $category) {
                return (object) [
                    'category' => $category,
                    'entry_count' => $group->count(),
                    'total_amount' => (float) $group->sum(fn ($r) => (float) $r->amount),
                ];
            })
            ->sortBy('category', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    protected function resolveFilters(Request $request): array
    {
        return [
            'category' => $request->string('category')->toString(),
            'expense_code' => $request->string('expense_code')->toString(),
        ];
    }

    protected function filterOptions(): array
    {
        $fromLedger = ExpenseLedger::query()
            ->whereNotNull('expenseCategory')
            ->where('expenseCategory', '!=', '')
            ->distinct()
            ->pluck('expenseCategory');

        $fromLedgerAlt = ExpenseLedger::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        $fromMaster = Expense::query()
            ->whereNotNull('expenseCategory')
            ->where('expenseCategory', '!=', '')
            ->distinct()
            ->pluck('expenseCategory');

        $categories = $fromLedger
            ->merge($fromLedgerAlt)
            ->merge($fromMaster)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $expenses = Expense::query()
            ->orderBy('expenseName')
            ->get(['expenseCode', 'expenseName', 'expenseCategory']);

        if ($expenses->isEmpty()) {
            $expenses = ExpenseLedger::query()
                ->whereNotNull('expenseCode')
                ->where('expenseCode', '!=', '')
                ->select('expenseCode')
                ->selectRaw('MAX(expenseCategory) as expenseCategory')
                ->selectRaw('MAX(description) as expenseName')
                ->groupBy('expenseCode')
                ->orderBy('expenseCode')
                ->get()
                ->map(fn ($row) => (object) [
                    'expenseCode' => $row->expenseCode,
                    'expenseName' => $row->expenseName ?: $row->expenseCode,
                    'expenseCategory' => $row->expenseCategory,
                ]);
        }

        return [
            'categories' => $categories,
            'expenses' => $expenses,
        ];
    }

    protected function reportConfig(string $reportType): array
    {
        return match ($reportType) {
            'category' => [
                'title' => 'Expenses by Category',
                'route' => 'reports.expense.by-category',
                'show' => ['category' => true, 'expense' => false],
                'mode' => 'category',
            ],
            default => [
                'title' => 'Expense Report Summary',
                'route' => 'reports.expense.summary',
                'show' => ['category' => true, 'expense' => true],
                'mode' => 'detail',
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
