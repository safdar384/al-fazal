<?php

namespace App\Support;

use App\Models\CustomerLedger;
use App\Models\Expense;
use App\Models\NewCustomer;
use App\Models\Order;
use App\Models\PurchaseInvoice;
use App\Models\SaleInvoice;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public const CACHE_TTL_SUMMARY = 300;

    public const CACHE_TTL_CHART = 300;

    public const CACHE_TTL_ACCOUNTING = 300;

    public const CACHE_TTL_ACTIVITY = 120;

    public const CACHE_TTL_COUNTS = 600;

    protected function cache()
    {
        // File store avoids slow database cache round-trips on every dashboard hit.
        return Cache::store('file');
    }

    /**
     * Data for initial dashboard render (summary cards + accounting; chart/activity load async).
     *
     * @return array<string, mixed>
     */
    /**
     * Empty KPI payload used when cache is cold (keeps login → dashboard instant).
     *
     * @return array<string, mixed>
     */
    public function emptySummaryViewData(): array
    {
        return [
            'summary' => [
                'sales' => 0.0,
                'purchase' => 0.0,
                'orders' => 0,
                'recovery' => 0.0,
                'expenses' => 0.0,
            ],
            'thisMonth' => [
                'sales' => 0.0,
                'purchase' => 0.0,
                'orders' => 0,
                'recovery' => 0.0,
                'expenses' => 0.0,
            ],
            'profitEstimate' => 0.0,
            'partyCounts' => [
                'total_customers' => 0,
                'total_suppliers' => 0,
            ],
            'accounting' => [
                'total_receivable' => 0.0,
                'total_payable' => 0.0,
                'customer_count' => 0,
                'supplier_count' => 0,
            ],
        ];
    }

    /**
     * Prefer cached summary so the first paint after login never waits on heavy SQL.
     *
     * @return array{0: array<string, mixed>, 1: bool} [data, needsWarm]
     */
    public function summaryViewDataFast(): array
    {
        $financial = $this->cache()->get($this->cacheKey('financial'));
        $partyCounts = $this->cache()->get($this->cacheKey('party_counts'));
        $accounting = $this->cache()->get($this->cacheKey('accounting'));

        if (is_array($financial) && is_array($partyCounts) && is_array($accounting)) {
            return [[
                'summary' => $financial['summary'],
                'thisMonth' => $financial['thisMonth'],
                'profitEstimate' => $financial['profitEstimate'],
                'partyCounts' => $partyCounts,
                'accounting' => $accounting,
            ], false];
        }

        return [$this->emptySummaryViewData(), true];
    }

    public function summaryViewData(): array
    {
        $financial = $this->financialTotals();

        // Accounting portfolio can be heavy; never fail the whole dashboard for it.
        try {
            $accounting = $this->accountingTotals();
        } catch (\Throwable $e) {
            report($e);
            $accounting = [
                'total_receivable' => 0.0,
                'total_payable' => 0.0,
                'customer_count' => 0,
                'supplier_count' => 0,
            ];
        }

        try {
            $partyCounts = $this->partyCounts();
        } catch (\Throwable $e) {
            report($e);
            $partyCounts = ['total_customers' => 0, 'total_suppliers' => 0];
        }

        return [
            'summary' => $financial['summary'],
            'thisMonth' => $financial['thisMonth'],
            'profitEstimate' => $financial['profitEstimate'],
            'partyCounts' => $partyCounts,
            'accounting' => $accounting,
        ];
    }

    /** Warm file cache after the HTTP response so the next visit is instant. */
    public function warmSummaryCache(): void
    {
        try {
            $this->summaryViewData();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * @return array{labels: array<int, string>, salesSeries: array<int, float>, purchaseSeries: array<int, float>, expensesSeries: array<int, float>}
     */
    public function chartSeries(): array
    {
        return $this->cache()->remember($this->cacheKey('chart'), self::CACHE_TTL_CHART, function () {
            $now = Carbon::now();
            $labels = [];
            $monthKeys = [];

            for ($i = 5; $i >= 0; $i--) {
                $date = $now->copy()->subMonths($i);
                $labels[] = $date->format('M Y');
                $monthKeys[] = $date->format('Y-m');
            }

            $rangeStart = $now->copy()->subMonths(5)->startOfMonth()->format('Y-m-d');
            $rangeEnd = $now->copy()->endOfMonth()->format('Y-m-d');

            $salesByMonth = $this->aggregateByMonth('sale_invoices', 'invoiceDate', 'grandTotal', $rangeStart, $rangeEnd);
            $purchaseByMonth = $this->aggregateByMonth('purchase_invoices', 'invoiceDate', 'grandTotal', $rangeStart, $rangeEnd);
            $expenseByMonth = $this->aggregateByMonth('expenses', 'expenseDate', 'amount', $rangeStart, $rangeEnd);

            return [
                'labels' => $labels,
                'salesSeries' => array_map(fn ($key) => (float) ($salesByMonth[$key] ?? 0), $monthKeys),
                'purchaseSeries' => array_map(fn ($key) => (float) ($purchaseByMonth[$key] ?? 0), $monthKeys),
                'expensesSeries' => array_map(fn ($key) => (float) ($expenseByMonth[$key] ?? 0), $monthKeys),
            ];
        });
    }

    /**
     * @return array<string, float> keyed by Y-m
     */
    protected function aggregateByMonth(
        string $table,
        string $dateColumn,
        string $amountColumn,
        string $rangeStart,
        string $rangeEnd
    ): array {
        $dateExpr = "DATE_FORMAT(`{$dateColumn}`, '%Y-%m')";

        return DB::table($table)
            ->whereBetween($dateColumn, [$rangeStart, $rangeEnd])
            ->selectRaw("{$dateExpr} as month_key, COALESCE(SUM(`{$amountColumn}`), 0) as total")
            ->groupByRaw($dateExpr)
            ->pluck('total', 'month_key')
            ->map(fn ($v) => (float) $v)
            ->all();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function recentActivity(): Collection
    {
        return $this->cache()->remember($this->cacheKey('activity'), self::CACHE_TTL_ACTIVITY, function () {
            return $this->buildRecentActivity();
        });
    }

    /**
     * @return array{total_customers: int, total_suppliers: int}
     */
    public function partyCounts(): array
    {
        return $this->cache()->remember($this->cacheKey('party_counts'), self::CACHE_TTL_COUNTS, function () {
            return [
                'total_customers' => (int) NewCustomer::query()
                    ->where(function ($q) {
                        $q->whereNull('custCatagory')
                            ->orWhere(function ($qq) {
                                $qq->whereRaw('LOWER(custCatagory) NOT LIKE ?', ['%supplier%'])
                                    ->whereRaw('LOWER(custCatagory) NOT LIKE ?', ['%suplier%']);
                            });
                    })
                    ->count(),
                'total_suppliers' => (int) NewCustomer::query()
                    ->where(function ($q) {
                        $q->whereRaw('LOWER(custCatagory) LIKE ?', ['%supplier%'])
                            ->orWhereRaw('LOWER(custCatagory) LIKE ?', ['%suplier%']);
                    })
                    ->count(),
            ];
        });
    }

    /**
     * @return array{total_receivable: float, total_payable: float, customer_count: int, supplier_count: int}
     */
    public function accountingTotals(): array
    {
        return $this->cache()->remember($this->cacheKey('accounting'), self::CACHE_TTL_ACCOUNTING, function () {
            return PartyBalance::portfolioTotals();
        });
    }

    public static function flushCache(): void
    {
        $store = Cache::store('file');
        $prefix = 'dashboard.'.config('app.env', 'local').'.';
        $month = Carbon::now()->format('Y-m');
        $suffixes = ['financial', 'chart', 'accounting', 'activity', 'party_counts'];

        foreach ($suffixes as $suffix) {
            $store->forget($prefix.$suffix.'.'.$month);
            $store->forget($prefix.$suffix);
        }
    }

    /**
     * @return array{summary: array<string, float|int>, thisMonth: array<string, float|int>, profitEstimate: float}
     */
    protected function financialTotals(): array
    {
        return $this->cache()->remember($this->cacheKey('financial'), self::CACHE_TTL_SUMMARY, function () {
            $now = Carbon::now();
            $monthStart = $now->copy()->startOfMonth();
            $monthEnd = $now->copy()->endOfMonth();
            $recoveryCategory = PartyLedger::category('recovery');

            $summary = [
                'sales' => (float) SaleInvoice::sum('grandTotal'),
                'purchase' => (float) PurchaseInvoice::sum('grandTotal'),
                'orders' => (int) Order::count(),
                'recovery' => $this->sumRecovery($recoveryCategory),
                'expenses' => (float) Expense::sum('amount'),
            ];

            $thisMonth = [
                'sales' => (float) SaleInvoice::whereBetween('invoiceDate', [$monthStart, $monthEnd])->sum('grandTotal'),
                'purchase' => (float) PurchaseInvoice::whereBetween('invoiceDate', [$monthStart, $monthEnd])->sum('grandTotal'),
                'orders' => (int) Order::whereBetween('orderDate', [$monthStart, $monthEnd])->count(),
                'recovery' => $this->sumRecovery($recoveryCategory, $monthStart, $monthEnd),
                'expenses' => (float) Expense::whereBetween('expenseDate', [$monthStart, $monthEnd])->sum('amount'),
            ];

            $profitEstimate = $summary['sales'] - ($summary['purchase'] + $summary['expenses']);

            return [
                'summary' => $summary,
                'thisMonth' => $thisMonth,
                'profitEstimate' => $profitEstimate,
            ];
        });
    }

    protected function sumRecovery(string $recoveryCategory, ?Carbon $from = null, ?Carbon $to = null): float
    {
        $query = CustomerLedger::query()
            ->where('category', $recoveryCategory)
            ->where('statuse', '0');

        if ($from && $to) {
            $query->whereBetween('customerDate', [$from->format('Y-m-d'), $to->format('Y-m-d')]);
        }

        return (float) $query->sum('credit');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $rows
     * @return array<string, float>
     */
    protected function sumByMonthKey(Collection $rows, string $dateField, string $amountField): array
    {
        $totals = [];

        foreach ($rows as $row) {
            $date = $row->{$dateField} ?? null;
            if (! $date) {
                continue;
            }
            $key = Carbon::parse($date)->format('M Y');
            $totals[$key] = ($totals[$key] ?? 0) + (float) ($row->{$amountField} ?? 0);
        }

        return $totals;
    }

    protected function buildRecentActivity(): Collection
    {
        $recoveryCategory = PartyLedger::category('recovery');

        $recentSales = SaleInvoice::query()
            ->select(['invoiceNo', 'customerName', 'grandTotal', 'status', 'invoiceDate'])
            ->orderByDesc('invoiceDate')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'type' => 'Sale',
                'reference' => $item->invoiceNo,
                'party' => $item->customerName,
                'amount' => (float) $item->grandTotal,
                'status' => $item->status ?: 'Completed',
                'date' => $item->invoiceDate ? Carbon::parse($item->invoiceDate) : null,
            ]);

        $recentPurchases = PurchaseInvoice::query()
            ->select(['invoiceNo', 'supplierName', 'grandTotal', 'status', 'invoiceDate'])
            ->orderByDesc('invoiceDate')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'type' => 'Purchase',
                'reference' => $item->invoiceNo,
                'party' => $item->supplierName,
                'amount' => (float) $item->grandTotal,
                'status' => $item->status ?: 'Processed',
                'date' => $item->invoiceDate ? Carbon::parse($item->invoiceDate) : null,
            ]);

        $recentOrders = Order::query()
            ->select(['orderNo', 'customerName', 'grandTotal', 'status', 'orderDate'])
            ->orderByDesc('orderDate')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'type' => 'Order',
                'reference' => $item->orderNo,
                'party' => $item->customerName,
                'amount' => (float) $item->grandTotal,
                'status' => $item->status ?: 'Open',
                'date' => $item->orderDate ? Carbon::parse($item->orderDate) : null,
            ]);

        $recentRecoveries = CustomerLedger::query()
            ->where('category', $recoveryCategory)
            ->where('statuse', '0')
            ->where('credit', '>', 0)
            ->select(['id', 'customerCode', 'description', 'credit', 'customerDate', 'invoiceNumber'])
            ->orderByDesc('customerDate')
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(function ($item) use ($recoveryCategory) {
                return [
                    'type' => $recoveryCategory,
                    'reference' => $item->invoiceNumber ?: ('CL-'.$item->id),
                    'party' => $item->customerCode,
                    'amount' => (float) $item->credit,
                    'status' => 'Received',
                    'date' => $item->customerDate ? Carbon::parse($item->customerDate) : null,
                ];
            });

        $recentExpenses = Expense::query()
            ->select(['expenseCode', 'expenseName', 'amount', 'status', 'expenseDate'])
            ->orderByDesc('expenseDate')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'type' => 'Expense',
                'reference' => $item->expenseCode,
                'party' => $item->expenseName,
                'amount' => (float) $item->amount,
                'status' => $item->status ?: 'Posted',
                'date' => $item->expenseDate ? Carbon::parse($item->expenseDate) : null,
            ]);

        return (new Collection)
            ->concat($recentSales)
            ->concat($recentPurchases)
            ->concat($recentOrders)
            ->concat($recentRecoveries)
            ->concat($recentExpenses)
            ->sortByDesc(fn ($item) => $item['date'] ? $item['date']->timestamp : 0)
            ->take(10)
            ->values();
    }

    protected function cacheKey(string $segment): string
    {
        $month = Carbon::now()->format('Y-m');

        return 'dashboard.'.config('app.env', 'local').'.'.$segment.'.'.$month;
    }
}
