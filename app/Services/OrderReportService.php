<?php

namespace App\Services;

use App\Models\NewCustomer;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderReportService
{
    /**
     * @return array{from: string, to: string}
     */
    public function resolveDateRange(Request $request): array
    {
        $defaultFrom = now()->startOfMonth()->format('Y-m-d');
        $defaultTo = now()->format('Y-m-d');

        try {
            $fromRaw = $request->input('from_date', $request->input('fromDate', $defaultFrom));
            $toRaw = $request->input('to_date', $request->input('toDate', $defaultTo));
            $from = Carbon::parse($fromRaw ?: $defaultFrom)->format('Y-m-d');
            $to = Carbon::parse($toRaw ?: $defaultTo)->format('Y-m-d');
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
     * @return array{customer_code: string, area: string, status: string}
     */
    public function resolveFilters(Request $request): array
    {
        return [
            'customer_code' => (string) ($request->input('customer_code') ?: $request->input('customerCode') ?: ''),
            'area' => (string) ($request->input('area') ?: ''),
            'status' => (string) ($request->input('status') ?: ''),
        ];
    }

    public function filterOptions(): array
    {
        return [
            'customers' => NewCustomer::query()->excludeProductSupplier()->orderBy('custName')->get(['custCode', 'custName', 'custArea']),
            'areas' => NewCustomer::query()
                ->whereNotNull('custArea')
                ->where('custArea', '!=', '')
                ->distinct()
                ->orderBy('custArea')
                ->pluck('custArea'),
            'statuses' => config('orders.statuses', []),
        ];
    }

    /**
     * @param  array{from: string, to: string}  $range
     * @param  array<string, string>  $filters
     * @return array<string, float|int>
     */
    public function overallSummary(array $range, array $filters, string $reportType): array
    {
        $query = $this->baseQuery($range, $filters, $reportType);

        $row = $query->select([
            DB::raw('COUNT(*) as order_count'),
            DB::raw('COALESCE(SUM(noOfItems), 0) as total_items'),
            DB::raw('COALESCE(SUM(grandTotal), 0) as total_amount'),
            DB::raw("COALESCE(SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END), 0) as pending_count"),
            DB::raw("COALESCE(SUM(CASE WHEN converted_invoice_no IS NOT NULL THEN 1 ELSE 0 END), 0) as converted_count"),
        ])->first();

        return [
            'order_count' => (int) ($row->order_count ?? 0),
            'total_items' => (int) ($row->total_items ?? 0),
            'total_amount' => round((float) ($row->total_amount ?? 0), 2),
            'pending_count' => (int) ($row->pending_count ?? 0),
            'converted_count' => (int) ($row->converted_count ?? 0),
        ];
    }

    /**
     * @param  array{from: string, to: string}  $range
     * @param  array<string, string>  $filters
     */
    public function buildRows(array $range, array $filters, string $reportType): Collection
    {
        if ($reportType === 'customer') {
            return $this->customerRows($range, $filters);
        }

        $query = $this->baseQuery($range, $filters, $reportType);

        return $query
            ->orderByDesc('orderDate')
            ->orderByDesc('id')
            ->get([
                'orderNo', 'orderDate', 'customerCode', 'customerName', 'city',
                'noOfItems', 'grandTotal', 'status', 'converted_invoice_no', 'notes',
            ]);
    }

    /**
     * @param  array{from: string, to: string}  $range
     * @param  array<string, string>  $filters
     */
    protected function customerRows(array $range, array $filters): Collection
    {
        $query = $this->baseQuery($range, $filters, 'customer');

        return $query
            ->groupBy('customerCode', 'customerName')
            ->orderBy('customerName')
            ->select([
                'customerCode as customer_code',
                'customerName as customer_name',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('COALESCE(SUM(noOfItems), 0) as total_items'),
                DB::raw('COALESCE(SUM(grandTotal), 0) as total_amount'),
                DB::raw("COALESCE(SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END), 0) as pending_orders"),
            ])
            ->get();
    }

    /**
     * @param  array{from: string, to: string}  $range
     * @param  array<string, string>  $filters
     */
    protected function baseQuery(array $range, array $filters, string $reportType): \Illuminate\Database\Eloquent\Builder
    {
        $query = Order::query()->whereBetween('orderDate', [$range['from'], $range['to']]);

        if ($filters['customer_code']) {
            $query->where('customerCode', $filters['customer_code']);
        }

        if ($filters['area']) {
            $codes = NewCustomer::query()->excludeProductSupplier()->where('custArea', $filters['area'])->pluck('custCode');
            $query->whereIn('customerCode', $codes->isEmpty() ? ['__none__'] : $codes);
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        } elseif ($reportType === 'pending') {
            $query->whereIn('status', ['Pending', 'Confirmed', 'In Process']);
        }

        return $query;
    }
}
