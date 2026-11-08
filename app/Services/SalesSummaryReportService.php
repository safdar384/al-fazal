<?php

namespace App\Services;

use App\Models\NewCustomer;
use App\Support\ListPage;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Read-only sales summary aggregations (no invoice mutations).
 */
class SalesSummaryReportService
{
    /**
     * @return array{from: string, to: string}
     */
    public function resolveDateRange(Request $request): array
    {
        $defaultFrom = now()->startOfMonth()->format('Y-m-d');
        $defaultTo = now()->format('Y-m-d');

        try {
            $from = Carbon::parse($request->input('from_date', $defaultFrom))->format('Y-m-d');
            $to = Carbon::parse($request->input('to_date', $defaultTo))->format('Y-m-d');
        } catch (\Throwable) {
            $from = $defaultFrom;
            $to = $defaultTo;
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return ['from' => $from, 'to' => $to];
    }

    /**
     * @return array{
     *   customer_code: string,
     *   product_code: string,
     *   area: string,
     *   sale_type: string
     * }
     */
    public function resolveFilters(Request $request): array
    {
        return [
            'customer_code' => $request->string('customer_code')->toString(),
            'product_code' => $request->string('product_code')->toString(),
            'area' => $request->string('area')->toString(),
            'sale_type' => $request->string('sale_type')->toString(),
        ];
    }

    /**
     * @return array{customers: Collection, products: Collection, areas: Collection}
     */
    public function filterOptions(): array
    {
        return [
            'customers' => NewCustomer::query()
                ->excludeProductSupplier()
                ->orderBy('custName')
                ->get(['custCode', 'custName', 'custArea']),
            'products' => DB::table('product_info')
                ->orderBy('productName')
                ->get(['productCode', 'productName']),
            'areas' => NewCustomer::query()
                ->whereNotNull('custArea')
                ->where('custArea', '!=', '')
                ->select('custArea')
                ->distinct()
                ->orderBy('custArea')
                ->pluck('custArea'),
        ];
    }

    /**
     * @param  array{from: string, to: string}  $range
     * @param  array<string, string>  $filters
     * @return array<string, float|int>
     */
    public function overallSummary(array $range, array $filters): array
    {
        $cacheKey = 'sales_summary_overall_'.md5(json_encode([$range, $filters]));
        $ttl = (int) config('sales.summary_report_cache_seconds', 300);

        return Cache::remember($cacheKey, $ttl, function () use ($range, $filters) {
            $query = $this->filteredInvoiceQuery($range, $filters);
            $query->leftJoinSub($this->itemAggregatesSubquery(), 'agg', 'agg.sale_invoice_id', '=', 'si.id');

            $row = $query->select([
                DB::raw('COUNT(si.id) as invoice_count'),
                DB::raw('COALESCE(SUM(agg.gross_amount), 0) as total_gross'),
                DB::raw('COALESCE(SUM(agg.total_qty), 0) as total_qty'),
                DB::raw('COALESCE(SUM(si.invoiceDiscount), 0) as invoice_discount'),
                DB::raw('COALESCE(SUM(si.grandTotal), 0) as net_sales'),
                DB::raw("COALESCE(SUM(CASE WHEN COALESCE(si.sale_type, 'credit') = 'cash' THEN si.grandTotal ELSE 0 END), 0) as cash_sales"),
                DB::raw("COALESCE(SUM(CASE WHEN COALESCE(si.sale_type, 'credit') = 'credit' THEN si.grandTotal ELSE 0 END), 0) as credit_sales"),
                DB::raw('COALESCE(SUM(si.paid_amount), 0) as total_paid'),
            ])->first();

            $gross = (float) ($row->total_gross ?? 0);
            $net = (float) ($row->net_sales ?? 0);
            $totalDiscount = max(0, round($gross - $net, 2));
            $totalPaid = (float) ($row->total_paid ?? 0);
            $outstanding = max(0, round($net - $totalPaid, 2));

            return [
                'invoice_count' => (int) ($row->invoice_count ?? 0),
                'total_gross' => round($gross, 2),
                'total_qty' => (float) ($row->total_qty ?? 0),
                'total_discount' => $totalDiscount,
                'net_sales' => round($net, 2),
                'cash_sales' => round((float) ($row->cash_sales ?? 0), 2),
                'credit_sales' => round((float) ($row->credit_sales ?? 0), 2),
                'total_paid' => round($totalPaid, 2),
                'total_outstanding' => $outstanding,
                'invoice_discount' => round((float) ($row->invoice_discount ?? 0), 2),
            ];
        });
    }

    /**
     * @param  array{from: string, to: string}  $range
     * @param  array<string, string>  $filters
     */
    public function paginateInvoices(Request $request, array $range, array $filters): LengthAwarePaginator
    {
        $perPage = ListPage::perPage($request);

        $query = $this->filteredInvoiceQuery($range, $filters);
        $query->leftJoinSub($this->itemAggregatesSubquery(), 'agg', 'agg.sale_invoice_id', '=', 'si.id');

        return $query
            ->select([
                'si.id',
                'si.invoiceDate',
                'si.invoiceNo',
                'si.customerCode',
                'si.customerName',
                'si.sale_type',
                'si.payment_status',
                'si.invoiceTotal',
                'si.invoiceDiscount',
                'si.grandTotal',
                'si.paid_amount',
                'si.biltyNo',
                'si.city',
                'si.status',
                'nc.custAddress as customerAddress',
                'nc.custCity as customerCity',
                DB::raw('COALESCE(agg.total_qty, 0) as total_qty'),
                DB::raw('COALESCE(agg.gross_amount, 0) as gross_amount'),
            ])
            ->orderByDesc('si.invoiceDate')
            ->orderByDesc('si.id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($row) {
                $gross = (float) $row->gross_amount;
                $net = (float) $row->grandTotal;
                $paid = (float) ($row->paid_amount ?? 0);
                $discount = max(0, round($gross - $net, 2));
                $balance = max(0, round($net - $paid, 2));
                $addr = trim((string) ($row->customerAddress ?? ''));
                $city = trim((string) ($row->customerCity ?? $row->city ?? ''));
                $addressParts = array_values(array_filter([$addr, $city], fn ($p) => $p !== ''));

                $row->total_qty = (float) $row->total_qty;
                $row->gross_amount = round($gross, 2);
                $row->total_discount = $discount;
                $row->net_amount = round($net, 2);
                $row->invoice_amount = round($net, 2);
                $row->paid_amount = round($paid, 2);
                $row->balance = $balance;
                $row->biltyNo = $row->biltyNo ?? null;
                $row->customer_address = $addressParts === [] ? '—' : implode(', ', $addressParts);
                $row->sale_type_label = ($row->sale_type ?? 'credit') === 'cash' ? 'Cash' : 'Credit';

                return $row;
            });
    }

    /**
     * @param  array{from: string, to: string}  $range
     * @param  array<string, string>  $filters
     * @return Collection<int, object>
     */
    public function productSummary(array $range, array $filters): Collection
    {
        $query = DB::table('sale_invoice_items as sii')
            ->join('sale_invoices as si', 'si.id', '=', 'sii.sale_invoice_id')
            ->leftJoin('newcustomers as nc', 'nc.custCode', '=', 'si.customerCode')
            ->whereBetween('si.invoiceDate', [$range['from'], $range['to']]);

        $this->applyFiltersToJoinedQuery($query, $filters, 'si', 'nc', 'sii');

        return $query
            ->groupBy('sii.productCode', 'sii.productName')
            ->orderBy('sii.productName')
            ->select([
                'sii.productCode as product_code',
                'sii.productName as product_name',
                DB::raw('COALESCE(SUM(sii.qty), 0) as total_qty'),
                DB::raw('COALESCE(SUM(sii.amount), 0) as total_sales_value'),
                DB::raw('CASE WHEN SUM(sii.qty) > 0 THEN SUM(sii.amount) / SUM(sii.qty) ELSE 0 END as avg_rate'),
                DB::raw('COALESCE(SUM(sii.qty * sii.rate), 0) as gross_amount'),
            ])
            ->get()
            ->map(function ($row) {
                $gross = (float) $row->gross_amount;
                $net = (float) $row->total_sales_value;
                $row->total_qty = (float) $row->total_qty;
                $row->total_sales_value = round($net, 2);
                $row->avg_rate = round((float) $row->avg_rate, 2);
                $row->total_discount = round(max(0, $gross - $net), 2);

                return $row;
            });
    }

    /**
     * @param  array{from: string, to: string}  $range
     * @param  array<string, string>  $filters
     * @return Collection<int, object>
     */
    public function customerSummary(array $range, array $filters): Collection
    {
        $query = $this->filteredInvoiceQuery($range, $filters);
        $query->leftJoinSub($this->itemAggregatesSubquery(), 'agg', 'agg.sale_invoice_id', '=', 'si.id');

        return $query
            ->groupBy('si.customerCode', 'si.customerName')
            ->orderBy('si.customerName')
            ->select([
                'si.customerCode as customer_code',
                'si.customerName as customer_name',
                DB::raw('COUNT(si.id) as invoice_count'),
                DB::raw('COALESCE(SUM(si.grandTotal), 0) as total_sales'),
                DB::raw('COALESCE(SUM(si.paid_amount), 0) as total_paid'),
                DB::raw('COALESCE(SUM(agg.gross_amount), 0) as gross_amount'),
            ])
            ->get()
            ->map(function ($row) {
                $net = (float) $row->total_sales;
                $paid = (float) $row->total_paid;
                $row->invoice_count = (int) $row->invoice_count;
                $row->total_sales = round($net, 2);
                $row->total_paid = round($paid, 2);
                $row->total_outstanding = max(0, round($net - $paid, 2));
                $row->total_discount = round(max(0, (float) $row->gross_amount - $net), 2);

                return $row;
            });
    }

    /**
     * @param  array{from: string, to: string}  $range
     * @param  array<string, string>  $filters
     * @return Collection<int, object>
     */
    public function invoicesForExport(array $range, array $filters): Collection
    {
        $query = $this->filteredInvoiceQuery($range, $filters);
        $query->leftJoinSub($this->itemAggregatesSubquery(), 'agg', 'agg.sale_invoice_id', '=', 'si.id');

        return $query
            ->select([
                'si.invoiceDate',
                'si.invoiceNo',
                'si.customerName',
                'si.sale_type',
                'si.invoiceDiscount',
                'si.grandTotal',
                'si.paid_amount',
                'si.biltyNo',
                'si.city',
                'nc.custAddress as customerAddress',
                'nc.custCity as customerCity',
                DB::raw('COALESCE(agg.total_qty, 0) as total_qty'),
                DB::raw('COALESCE(agg.gross_amount, 0) as gross_amount'),
            ])
            ->orderBy('si.invoiceDate')
            ->orderBy('si.invoiceNo')
            ->get()
            ->map(function ($row) {
                $gross = (float) $row->gross_amount;
                $net = (float) $row->grandTotal;
                $paid = (float) ($row->paid_amount ?? 0);
                $addr = trim((string) ($row->customerAddress ?? ''));
                $city = trim((string) ($row->customerCity ?? $row->city ?? ''));
                $addressParts = array_values(array_filter([$addr, $city], fn ($p) => $p !== ''));
                $row->total_discount = max(0, round($gross - $net, 2));
                $row->gross_amount = round($gross, 2);
                $row->net_amount = round($net, 2);
                $row->invoice_amount = round($net, 2);
                $row->paid_amount = round($paid, 2);
                $row->balance = max(0, round($net - $paid, 2));
                $row->customer_address = $addressParts === [] ? '—' : implode(', ', $addressParts);
                $row->sale_type_label = ($row->sale_type ?? 'credit') === 'cash' ? 'Cash' : 'Credit';

                return $row;
            });
    }

    protected function itemAggregatesSubquery(): Builder
    {
        return DB::table('sale_invoice_items')
            ->select([
                'sale_invoice_id',
                DB::raw('COALESCE(SUM(qty), 0) as total_qty'),
                DB::raw('COALESCE(SUM(qty * rate), 0) as gross_amount'),
                DB::raw('COALESCE(SUM(amount), 0) as line_net'),
            ])
            ->groupBy('sale_invoice_id');
    }

    /**
     * @param  array{from: string, to: string}  $range
     * @param  array<string, string>  $filters
     */
    protected function filteredInvoiceQuery(array $range, array $filters): Builder
    {
        $query = DB::table('sale_invoices as si')
            ->leftJoin('newcustomers as nc', 'nc.custCode', '=', 'si.customerCode')
            ->whereBetween('si.invoiceDate', [$range['from'], $range['to']]);

        $this->applyFiltersToJoinedQuery($query, $filters, 'si', 'nc');

        return $query;
    }

    /**
     * @param  array<string, string>  $filters
     */
    protected function applyFiltersToJoinedQuery(
        Builder $query,
        array $filters,
        string $invoiceAlias = 'si',
        string $customerAlias = 'nc',
        ?string $itemAlias = null
    ): void {
        if ($filters['customer_code'] !== '') {
            $query->where("{$invoiceAlias}.customerCode", $filters['customer_code']);
        }

        if ($filters['area'] !== '') {
            $query->where("{$customerAlias}.custArea", $filters['area']);
        }

        if (in_array($filters['sale_type'], ['cash', 'credit'], true)) {
            $query->where("{$invoiceAlias}.sale_type", $filters['sale_type']);
        }

        if ($filters['product_code'] !== '') {
            if ($itemAlias) {
                $query->where("{$itemAlias}.productCode", $filters['product_code']);
            } else {
                $query->whereExists(function (Builder $sub) use ($filters, $invoiceAlias) {
                    $sub->select(DB::raw(1))
                        ->from('sale_invoice_items as sii_f')
                        ->whereColumn('sii_f.sale_invoice_id', "{$invoiceAlias}.id")
                        ->where('sii_f.productCode', $filters['product_code']);
                });
            }
        }
    }
}
