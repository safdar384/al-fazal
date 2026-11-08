<?php

namespace App\Support;

use App\Models\CustomerArea;
use App\Models\NewCustomer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MarketVoucherReport
{
    /**
     * Ledger categories that count as sale/delivery (debit) in the period.
     *
     * @return list<string>
     */
    public static function deliveryCategories(): array
    {
        return array_values(array_unique(array_filter([
            PartyLedger::category('sale_invoice'),
            PartyLedger::category('cash_sale'),
            'Sale Invoice',
            'SaleInvoice',
            'Cash Sale',
        ])));
    }

    /**
     * Recovery-side categories filled from customer ledger (credits) in date range.
     *
     * @return list<string>
     */
    public static function recoveryFillCategories(): array
    {
        return ['Recovery', 'Discount', 'Bilty', 'Other'];
    }

    /**
     * @return array{
     *   rows: Collection<int, object>,
     *   totals: array{pre_balance: float, delivery: float, balance: float, recovery: float, discount: float, bilty: float, other: float, last_balance: float},
     *   area: ?CustomerArea,
     *   meta: array{area_code: string, area_name: string, agent_name: string, from: string, to: string}
     * }
     */
    public static function build(string $areaCode, string $fromDate, string $toDate): array
    {
        $area = CustomerArea::query()->where('areaCode', $areaCode)->first();

        $customers = NewCustomer::query()
            ->excludeProductSupplier()
            ->where('custArea', $areaCode)
            ->orderBy('custPage')
            ->orderBy('custName')
            ->get(['custCode', 'custName', 'bussnessName', 'custCity', 'custPage', 'custArea']);

        if ($customers->isEmpty()) {
            return [
                'rows' => collect(),
                'totals' => self::emptyTotals(),
                'area' => $area,
                'meta' => self::meta($areaCode, $area, $fromDate, $toDate),
            ];
        }

        $codes = $customers->pluck('custCode')->all();

        // Same as Customer Ledger statement:
        // Opening = signed balance before From date; Closing = signed balance through To date.
        $openingsBeforeFrom = self::bulkSignedBalancesBefore($codes, $fromDate);
        $closingsThroughTo = self::bulkSignedBalancesThrough($codes, $toDate);

        // Delivery: credit sale invoices in the selected date range (invoice date).
        $deliveriesFromInvoices = self::bulkCreditSalesInPeriod($codes, $fromDate, $toDate);

        // Fallback: ledger sale debits in period (legacy / manual postings).
        $deliveriesFromLedger = self::bulkLedgerDeliveryInPeriod($codes, $fromDate, $toDate);

        // Recovery / Discount / Bilty / Other credits in date range.
        $ledgerFills = self::bulkCategoryCreditsInPeriod($codes, $fromDate, $toDate, self::recoveryFillCategories());

        $rows = $customers->map(function (NewCustomer $customer) use (
            $openingsBeforeFrom,
            $closingsThroughTo,
            $deliveriesFromInvoices,
            $deliveriesFromLedger,
            $ledgerFills
        ) {
            $code = $customer->custCode;

            // Receivable display = signed (debit − credit), same as PartyBalance::statement.
            $preBalance = round((float) ($openingsBeforeFrom[$code] ?? 0), 2);
            $lastBalance = round((float) ($closingsThroughTo[$code] ?? 0), 2);

            $delivery = round((float) ($deliveriesFromInvoices[$code] ?? 0), 2);
            if ($delivery <= 0) {
                $delivery = round((float) ($deliveriesFromLedger[$code] ?? 0), 2);
            }

            // After delivery (before recovery columns): Opening + Delivery.
            $balance = round($preBalance + $delivery, 2);

            $byCat = $ledgerFills[$code] ?? [];
            $recovery = round((float) ($byCat['Recovery'] ?? 0), 2);
            $discount = round((float) ($byCat['Discount'] ?? 0), 2);
            $bilty = round((float) ($byCat['Bilty'] ?? 0), 2);
            $other = round((float) ($byCat['Other'] ?? 0), 2);

            return (object) [
                'page_no' => $customer->custPage ?? '',
                'customer_name' => $customer->bussnessName ?: $customer->custName,
                'city' => $customer->custCity ?? '',
                'customer_code' => $code,
                'pre_balance' => $preBalance,
                'delivery' => $delivery,
                'balance' => $balance,
                'recovery' => $recovery,
                'discount' => $discount,
                'bilty' => $bilty,
                'other' => $other,
                'last_balance' => $lastBalance,
            ];
        })->values();

        return [
            'rows' => $rows,
            'totals' => self::summarize($rows),
            'area' => $area,
            'meta' => self::meta($areaCode, $area, $fromDate, $toDate),
        ];
    }

    /**
     * Opening: signed balance before fromDate (PartyBalance::ledgerSignedBalance beforeDate).
     * SUM(debit) − SUM(credit) where customerDate < fromDate.
     *
     * @param  list<string>  $customerCodes
     * @return array<string, float>
     */
    protected static function bulkSignedBalancesBefore(array $customerCodes, string $beforeDate): array
    {
        return DB::table('customerledgers')
            ->where(self::activeLedgerConstraint())
            ->whereIn('customerCode', $customerCodes)
            ->whereDate('customerDate', '<', $beforeDate)
            ->groupBy('customerCode')
            ->selectRaw('customerCode, COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) AS amount')
            ->pluck('amount', 'customerCode')
            ->map(fn ($v) => (float) $v)
            ->all();
    }

    /**
     * Closing: signed balance through toDate (inclusive): SUM(debit) − SUM(credit).
     *
     * @param  list<string>  $customerCodes
     * @return array<string, float>
     */
    protected static function bulkSignedBalancesThrough(array $customerCodes, string $throughDate): array
    {
        return DB::table('customerledgers')
            ->where(self::activeLedgerConstraint())
            ->whereIn('customerCode', $customerCodes)
            ->whereDate('customerDate', '<=', $throughDate)
            ->groupBy('customerCode')
            ->selectRaw('customerCode, COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) AS amount')
            ->pluck('amount', 'customerCode')
            ->map(fn ($v) => (float) $v)
            ->all();
    }

    /**
     * Credit sale invoice totals by customer for invoiceDate in [from, to].
     *
     * @param  list<string>  $customerCodes
     * @return array<string, float>
     */
    protected static function bulkCreditSalesInPeriod(array $customerCodes, string $fromDate, string $toDate): array
    {
        return DB::table('sale_invoices')
            ->whereIn('customerCode', $customerCodes)
            ->where(function ($q) {
                $q->where('sale_type', 'credit')->orWhereNull('sale_type');
            })
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Void');
            })
            ->whereDate('invoiceDate', '>=', $fromDate)
            ->whereDate('invoiceDate', '<=', $toDate)
            ->groupBy('customerCode')
            ->selectRaw('customerCode, COALESCE(SUM(grandTotal), 0) AS amount')
            ->pluck('amount', 'customerCode')
            ->map(fn ($v) => (float) $v)
            ->all();
    }

    /**
     * @param  list<string>  $customerCodes
     * @return array<string, float>
     */
    protected static function bulkLedgerDeliveryInPeriod(array $customerCodes, string $fromDate, string $toDate): array
    {
        $categories = self::deliveryCategories();

        return DB::table('customerledgers')
            ->where(self::activeLedgerConstraint())
            ->whereIn('customerCode', $customerCodes)
            ->whereIn('category', $categories)
            ->whereDate('customerDate', '>=', $fromDate)
            ->whereDate('customerDate', '<=', $toDate)
            ->groupBy('customerCode')
            ->selectRaw('customerCode, COALESCE(SUM(debit), 0) AS amount')
            ->pluck('amount', 'customerCode')
            ->map(fn ($v) => (float) $v)
            ->all();
    }

    /**
     * Credit totals by customer + category for date range (statuse = 0).
     *
     * @param  list<string>  $customerCodes
     * @param  list<string>  $categories
     * @return array<string, array<string, float>>
     */
    protected static function bulkCategoryCreditsInPeriod(
        array $customerCodes,
        string $fromDate,
        string $toDate,
        array $categories
    ): array {
        if ($customerCodes === [] || $categories === []) {
            return [];
        }

        $rows = DB::table('customerledgers')
            ->where(self::activeLedgerConstraint())
            ->whereIn('customerCode', $customerCodes)
            ->whereIn('category', $categories)
            ->whereDate('customerDate', '>=', $fromDate)
            ->whereDate('customerDate', '<=', $toDate)
            ->groupBy('customerCode', 'category')
            ->selectRaw('customerCode, category, COALESCE(SUM(credit), 0) AS amount')
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $code = (string) $row->customerCode;
            $cat = (string) $row->category;
            if (! isset($out[$code])) {
                $out[$code] = [];
            }
            $out[$code][$cat] = (float) $row->amount;
        }

        return $out;
    }

    /**
     * Active ledger rows (matches PartyBalance).
     */
    protected static function activeLedgerConstraint(): \Closure
    {
        return function ($query) {
            $query->where('statuse', '0');
        };
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return array{pre_balance: float, delivery: float, balance: float, recovery: float, discount: float, bilty: float, other: float, last_balance: float}
     */
    public static function summarize(Collection $rows): array
    {
        return [
            'pre_balance' => round((float) $rows->sum('pre_balance'), 2),
            'delivery' => round((float) $rows->sum('delivery'), 2),
            'balance' => round((float) $rows->sum('balance'), 2),
            'recovery' => round((float) $rows->sum('recovery'), 2),
            'discount' => round((float) $rows->sum('discount'), 2),
            'bilty' => round((float) $rows->sum('bilty'), 2),
            'other' => round((float) $rows->sum('other'), 2),
            'last_balance' => round((float) $rows->sum('last_balance'), 2),
        ];
    }

    /**
     * @return array{pre_balance: float, delivery: float, balance: float, recovery: float, discount: float, bilty: float, other: float, last_balance: float}
     */
    public static function emptyTotals(): array
    {
        return [
            'pre_balance' => 0.0,
            'delivery' => 0.0,
            'balance' => 0.0,
            'recovery' => 0.0,
            'discount' => 0.0,
            'bilty' => 0.0,
            'other' => 0.0,
            'last_balance' => 0.0,
        ];
    }

    /**
     * @return Collection<int, CustomerArea>
     */
    public static function areaOptions(): Collection
    {
        return CustomerArea::query()
            ->orderBy('areaCode')
            ->get(['areaCode', 'areaName', 'ajentName']);
    }

    /**
     * @return array{area_code: string, area_name: string, agent_name: string, from: string, to: string}
     */
    protected static function meta(
        string $areaCode,
        ?CustomerArea $area,
        string $fromDate,
        string $toDate
    ): array {
        return [
            'area_code' => $areaCode,
            'area_name' => $area?->areaName ?? $areaCode,
            'agent_name' => trim((string) ($area?->ajentName ?? '')),
            'from' => $fromDate,
            'to' => $toDate,
        ];
    }
}
