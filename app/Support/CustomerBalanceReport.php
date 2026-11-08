<?php

namespace App\Support;

use App\Models\NewCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomerBalanceReport
{
    /**
     * @param  array{area?: string, customer_code?: string}  $filters
     * @return Collection<int, object>
     */
    public static function buildRows(string $fromDate, string $toDate, array $filters = []): Collection
    {
        $openingQuery = DB::table('customerledgers as cl')
            ->leftJoin('newcustomers as nc', 'nc.custCode', '=', 'cl.customerCode')
            ->where('cl.statuse', '0')
            ->where('cl.customerDate', '<', $fromDate);

        $periodQuery = DB::table('customerledgers as cl')
            ->leftJoin('newcustomers as nc', 'nc.custCode', '=', 'cl.customerCode')
            ->where('cl.statuse', '0')
            ->whereBetween('cl.customerDate', [$fromDate, $toDate]);

        if (! empty($filters['area'])) {
            $openingQuery->where('nc.custArea', $filters['area']);
            $periodQuery->where('nc.custArea', $filters['area']);
        }

        if (! empty($filters['customer_code'])) {
            $openingQuery->where('cl.customerCode', $filters['customer_code']);
            $periodQuery->where('cl.customerCode', $filters['customer_code']);
        }

        $openingRows = $openingQuery
            ->groupBy('cl.customerCode', 'nc.custName', 'nc.bussnessName', 'nc.custArea', 'cl.customerArea')
            ->select([
                'cl.customerCode as customer_code',
                DB::raw('COALESCE(nc.bussnessName, nc.custName, cl.customerCode) as customer_name'),
                DB::raw("COALESCE(nc.custArea, cl.customerArea, '') as area"),
                DB::raw('SUM(COALESCE(cl.debit,0) - COALESCE(cl.credit,0)) as opening_balance'),
            ])
            ->get()
            ->keyBy('customer_code');

        $periodRows = $periodQuery
            ->groupBy('cl.customerCode', 'nc.custName', 'nc.bussnessName', 'nc.custArea', 'cl.customerArea')
            ->select([
                'cl.customerCode as customer_code',
                DB::raw('COALESCE(nc.bussnessName, nc.custName, cl.customerCode) as customer_name'),
                DB::raw("COALESCE(nc.custArea, cl.customerArea, '') as area"),
                DB::raw('SUM(COALESCE(cl.debit,0)) as debit'),
                DB::raw('SUM(COALESCE(cl.credit,0)) as credit'),
            ])
            ->get()
            ->keyBy('customer_code');

        $customerCodes = $openingRows->keys()->merge($periodRows->keys())->unique()->values();

        return $customerCodes
            ->map(function ($code) use ($openingRows, $periodRows) {
                $opening = $openingRows->get($code);
                $period = $periodRows->get($code);

                $customerName = $period->customer_name ?? $opening->customer_name ?? $code;
                $area = trim((string) ($period->area ?? $opening->area ?? ''));

                $openingSigned = (float) ($opening->opening_balance ?? 0);
                $debit = (float) ($period->debit ?? 0);
                $credit = (float) ($period->credit ?? 0);
                $closingSigned = $openingSigned + $debit - $credit;

                $party = NewCustomer::query()->where('custCode', $code)->first();
                $isSupplier = PartyBalance::isSupplier($party, $code);
                $closingDisplay = $isSupplier ? -$closingSigned : $closingSigned;
                $split = self::splitBalances($closingDisplay, $isSupplier);

                return (object) [
                    'customer_code' => $code,
                    'customer_name' => $customerName,
                    'area' => $area,
                    'party_type' => $isSupplier ? 'Supplier' : 'Customer',
                    'receivable_balance' => $split['receivable_balance'],
                    'payable_balance' => $split['payable_balance'],
                ];
            })
            ->sortBy('customer_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * @return array{
     *   total_receivable: float,
     *   total_payable: float,
     *   net_balance: float
     * }
     */
    public static function summarize(Collection $rows): array
    {
        $totalReceivable = (float) $rows->sum('receivable_balance');
        $totalPayable = (float) $rows->sum('payable_balance');

        return [
            'total_receivable' => round($totalReceivable, 2),
            'total_payable' => round($totalPayable, 2),
            'net_balance' => round($totalPayable - $totalReceivable, 2),
        ];
    }

    /**
     * @return array{receivable_balance: float, payable_balance: float}
     */
    public static function splitBalances(float $closingDisplay, bool $isSupplier): array
    {
        if ($isSupplier) {
            return [
                'receivable_balance' => 0.0,
                'payable_balance' => $closingDisplay > 0 ? round($closingDisplay, 2) : 0.0,
            ];
        }

        return [
            'receivable_balance' => $closingDisplay > 0 ? round($closingDisplay, 2) : 0.0,
            'payable_balance' => 0.0,
        ];
    }

    /**
     * @return array{area: string, customer_code: string}
     */
    public static function resolveFilters(Request $request): array
    {
        return [
            'area' => $request->string('area')->toString(),
            'customer_code' => $request->string('customer_code')->toString(),
        ];
    }

    /**
     * Enrich customer list rows with ledger-based receivable / payable columns.
     *
     * @param  Collection<int, NewCustomer>  $customers
     * @return Collection<int, NewCustomer>
     */
    public static function enrichCustomerList(Collection $customers): Collection
    {
        return $customers->map(function (NewCustomer $customer) {
            $meta = PartyBalance::meta($customer->custCode);
            $split = self::splitBalances($meta['balance'], $meta['is_supplier']);

            $customer->receivable_balance = $split['receivable_balance'];
            $customer->payable_balance = $split['payable_balance'];

            return $customer;
        });
    }
}
