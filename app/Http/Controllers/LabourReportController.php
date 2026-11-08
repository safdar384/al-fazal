<?php

namespace App\Http\Controllers;

use App\Models\Labour;
use App\Models\LabourAccountTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class LabourReportController extends Controller
{
    public function summary(Request $request): Response|View
    {
        return $this->renderReport($request, 'summary');
    }

    public function byWorker(Request $request): Response|View
    {
        return $this->renderReport($request, 'worker');
    }

    public function byAjent(Request $request): Response|View
    {
        return $this->renderReport($request, 'ajent');
    }

    public function byFitter(Request $request): Response|View
    {
        return $this->renderReport($request, 'fitter');
    }

    public function advance(Request $request): Response|View
    {
        return $this->renderReport($request, 'advance');
    }

    protected function renderReport(Request $request, string $reportType): Response|View
    {
        $config = $this->reportConfig($reportType);
        $range = $this->resolveDateRange($request);
        $filters = $this->resolveFilters($request);

        if ($reportType === 'advance') {
            $rows = $this->buildAdvanceRows($filters);
            $summary = [
                'total_rows' => $rows->count(),
                'total_advance' => (float) $rows->sum(fn ($r) => (float) $r->advance),
                'total_balance' => (float) $rows->sum(fn ($r) => (float) $r->balance),
                'with_advance' => $rows->filter(fn ($r) => (float) $r->advance > 0)->count(),
            ];
        } elseif ($reportType === 'summary') {
            $rows = $this->buildBalanceRows($filters);
            $summary = [
                'total_rows' => $rows->count(),
                'total_balance' => (float) $rows->sum(fn ($r) => (float) $r->balance),
                'labour_count' => $rows->count(),
            ];
        } else {
            $rows = $this->buildTransactionRows($reportType, $range['from'], $range['to'], $filters);
            $closingByLabour = $rows
                ->groupBy('labour_code')
                ->map(fn (Collection $group) => (float) $group->last()->balance);
            $summary = [
                'total_rows' => $rows->count(),
                'total_amount' => (float) $rows->sum(fn ($r) => (float) $r->amount),
                'total_balance' => (float) $closingByLabour->sum(),
                'labour_count' => $closingByLabour->count(),
            ];
        }

        $viewData = [
            'reportType' => $reportType,
            'config' => $config,
            'rows' => $rows,
            'summary' => $summary,
            'filters' => $filters,
            'filterOptions' => $this->filterOptions($reportType),
            'fromDate' => $range['from'],
            'toDate' => $range['to'],
            'companyName' => config('app.name', 'TradeGate'),
        ];

        if ($request->boolean('download_pdf')) {
            if (! class_exists(Pdf::class)) {
                return back()->with('error', 'PDF package is not installed.');
            }

            $pdf = Pdf::loadView('reports.labour.pdf', $viewData)->setPaper('a4', 'landscape');

            return $pdf->download('labour-report-'.$reportType.'-'.now()->format('YmdHis').'.pdf');
        }

        if ($request->boolean('fragment')) {
            return view('reports.labour.live', $viewData);
        }

        return response()->view('reports.labour.report', $viewData);
    }

    /**
     * @param  array{category: string, labour_code: string}  $filters
     */
    protected function buildTransactionRows(string $reportType, string $fromDate, string $toDate, array $filters): Collection
    {
        $baseQuery = LabourAccountTransaction::query();
        $this->applyReportCategory($baseQuery, $reportType, $filters);
        if ($filters['labour_code'] !== '') {
            $baseQuery->where('labourCode', $filters['labour_code']);
        }

        $openingByLabour = (clone $baseQuery)
            ->where('transactionDate', '<', $fromDate)
            ->get()
            ->groupBy('labourCode')
            ->map(function (Collection $group) {
                $bal = 0.0;
                foreach ($group->sortBy([['transactionDate', 'asc'], ['id', 'asc']]) as $row) {
                    $bal += $row->entryType === 'debit'
                        ? (float) $row->amount
                        : -(float) $row->amount;
                }

                return round($bal, 2);
            });

        $periodRows = (clone $baseQuery)
            ->whereBetween('transactionDate', [$fromDate, $toDate])
            ->orderBy('transactionDate')
            ->orderBy('id')
            ->get();

        $running = $openingByLabour->all();

        return $periodRows->map(function (LabourAccountTransaction $row) use (&$running) {
            $code = (string) $row->labourCode;
            if (! isset($running[$code])) {
                $running[$code] = 0.0;
            }

            $signed = $row->entryType === 'debit'
                ? (float) $row->amount
                : -(float) $row->amount;
            $running[$code] = round($running[$code] + $signed, 2);

            $kind = $this->categoryKind($row->labourCatagory);

            return (object) [
                'report_date' => $row->transactionDate?->format('Y-m-d'),
                'labour_code' => $row->labourCode,
                'labour_name' => $row->labourName,
                'category' => $kind,
                'category_label' => $this->categoryLabel($kind),
                'description' => $row->description,
                'bank_account' => $row->bankAccount ?: '—',
                'entry_type' => $row->entryType,
                'amount' => (float) $row->amount,
                'balance' => $running[$code],
                'is_advance' => (bool) $row->isAdvance,
            ];
        });
    }

    /**
     * @param  array{category: string, labour_code: string}  $filters
     */
    protected function buildLabourMasterRows(array $filters): Collection
    {
        $query = Labour::query()->orderBy('labourName');

        if ($filters['category'] !== '') {
            $this->applyLabourMasterCategory($query, $filters['category']);
        }

        if ($filters['labour_code'] !== '') {
            $query->where('labourCode', $filters['labour_code']);
        }

        return $query
            ->get(['labourCode', 'labourName', 'labourCatagory', 'advance', 'balance', 'salary'])
            ->map(function (Labour $labour) {
                $kind = $this->categoryKind($labour->labourCatagory);

                return (object) [
                    'labour_code' => $labour->labourCode,
                    'labour_name' => $labour->labourName,
                    'category' => $kind,
                    'category_label' => $this->categoryLabel($kind),
                    'advance' => (float) ($labour->advance ?? 0),
                    'balance' => (float) ($labour->balance ?? 0),
                    'salary' => (float) ($labour->salary ?? 0),
                ];
            });
    }

    /**
     * @param  array{category: string, labour_code: string}  $filters
     */
    protected function buildAdvanceRows(array $filters): Collection
    {
        return $this->buildLabourMasterRows($filters)
            ->sortByDesc('advance')
            ->values();
    }

    /**
     * All labour (worker / ajent / fitter) with current balance.
     *
     * @param  array{category: string, labour_code: string}  $filters
     */
    protected function buildBalanceRows(array $filters): Collection
    {
        return $this->buildLabourMasterRows($filters)
            ->sortBy('labour_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    protected function applyReportCategory($query, string $reportType, array $filters): void
    {
        $lockedCategory = match ($reportType) {
            'worker' => 'worker',
            'ajent' => 'ajent',
            'fitter' => 'fitter',
            default => null,
        };

        if ($lockedCategory) {
            $this->applyCategoryFilter($query, $lockedCategory);
        } elseif (($filters['category'] ?? '') !== '') {
            $this->applyCategoryFilter($query, $filters['category']);
        }
    }

    protected function applyLabourMasterCategory($query, string $category): void
    {
        $kind = $this->categoryKind($category);

        if ($kind === 'ajent') {
            $query->where(function ($q) {
                $q->whereRaw('LOWER(TRIM(COALESCE(labourCatagory, ""))) = ?', ['ajent'])
                    ->orWhereRaw('LOWER(TRIM(COALESCE(labourCatagory, ""))) = ?', ['agent']);
            });

            return;
        }

        if ($kind === 'fitter') {
            $query->whereRaw('LOWER(COALESCE(labourCatagory, "")) LIKE ?', ['%fitter%']);

            return;
        }

        $query->where(function ($q) {
            $q->whereRaw('LOWER(TRIM(COALESCE(labourCatagory, ""))) NOT IN (?, ?)', ['ajent', 'agent'])
                ->whereRaw('LOWER(COALESCE(labourCatagory, "")) NOT LIKE ?', ['%fitter%']);
        });
    }

    protected function applyCategoryFilter($query, string $category): void
    {
        $kind = $this->categoryKind($category);

        if ($kind === 'ajent') {
            $query->where(function ($q) {
                $q->whereRaw('LOWER(TRIM(COALESCE(labourCatagory, ""))) = ?', ['ajent'])
                    ->orWhereRaw('LOWER(TRIM(COALESCE(labourCatagory, ""))) = ?', ['agent']);
            });

            return;
        }

        if ($kind === 'fitter') {
            $query->whereRaw('LOWER(COALESCE(labourCatagory, "")) LIKE ?', ['%fitter%']);

            return;
        }

        $query->where(function ($q) {
            $q->whereRaw('LOWER(TRIM(COALESCE(labourCatagory, ""))) NOT IN (?, ?)', ['ajent', 'agent'])
                ->whereRaw('LOWER(COALESCE(labourCatagory, "")) NOT LIKE ?', ['%fitter%']);
        });
    }

    protected function categoryKind(?string $category): string
    {
        $c = strtolower(trim((string) $category));
        if ($c === 'ajent' || $c === 'agent') {
            return 'ajent';
        }
        if (str_contains($c, 'fitter')) {
            return 'fitter';
        }

        return 'worker';
    }

    protected function categoryLabel(string $kind): string
    {
        return match ($kind) {
            'ajent' => 'Ajent',
            'fitter' => 'Fitter',
            default => 'Worker',
        };
    }

    protected function resolveFilters(Request $request): array
    {
        return [
            'category' => $request->string('category')->toString(),
            'labour_code' => $request->string('labour_code')->toString(),
        ];
    }

    protected function filterOptions(string $reportType): array
    {
        $laboursQuery = Labour::query()->orderBy('labourName');

        if ($reportType === 'worker') {
            $this->applyLabourMasterCategory($laboursQuery, 'worker');
        } elseif ($reportType === 'ajent') {
            $this->applyLabourMasterCategory($laboursQuery, 'ajent');
        } elseif ($reportType === 'fitter') {
            $this->applyLabourMasterCategory($laboursQuery, 'fitter');
        }

        return [
            'categories' => [
                'worker' => 'Worker',
                'ajent' => 'Ajent',
                'fitter' => 'Fitter',
            ],
            'labours' => $laboursQuery->get(['labourCode', 'labourName', 'labourCatagory']),
        ];
    }

    protected function reportConfig(string $reportType): array
    {
        return match ($reportType) {
            'summary' => [
                'title' => 'Labour Report Summary (All)',
                'route' => 'reports.labour.summary',
                'show' => ['category' => true, 'labour' => true, 'dates' => false],
                'mode' => 'balances',
            ],
            'worker' => [
                'title' => 'Labour Report by Worker',
                'route' => 'reports.labour.by-worker',
                'show' => ['category' => false, 'labour' => true, 'dates' => true],
                'mode' => 'transactions',
            ],
            'ajent' => [
                'title' => 'Labour Report by Ajent',
                'route' => 'reports.labour.by-ajent',
                'show' => ['category' => false, 'labour' => true, 'dates' => true],
                'mode' => 'transactions',
            ],
            'fitter' => [
                'title' => 'Labour Report by Fitter',
                'route' => 'reports.labour.by-fitter',
                'show' => ['category' => false, 'labour' => true, 'dates' => true],
                'mode' => 'transactions',
            ],
            'advance' => [
                'title' => 'Labour Advance Report',
                'route' => 'reports.labour.advance',
                'show' => ['category' => true, 'labour' => true, 'dates' => false],
                'mode' => 'advance',
            ],
            default => [
                'title' => 'Labour Report',
                'route' => 'reports.labour.summary',
                'show' => ['category' => true, 'labour' => true, 'dates' => true],
                'mode' => 'transactions',
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
