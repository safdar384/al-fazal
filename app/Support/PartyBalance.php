<?php

namespace App\Support;

use App\Models\CustomerLedger;
use App\Models\NewCustomer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Party balance from customerledgers (statuse = active).
 *
 * Ledger signed balance = SUM(debit) − SUM(credit)
 *   • Customer (receivable): display = signed  → amount they owe us
 *   • Supplier (payable):    display = −signed → amount we owe them
 */
class PartyBalance
{
    public const TYPE_RECEIVABLE = 'receivable';

    public const TYPE_PAYABLE = 'payable';

    public static function isSupplier(?NewCustomer $party = null, ?string $partyCode = null): bool
    {
        if ($party === null && $partyCode !== null) {
            $party = NewCustomer::query()->where('custCode', $partyCode)->first();
        }

        if ($party === null) {
            return false;
        }

        return self::categoryLooksLikeSupplier((string) $party->custCatagory);
    }

    /**
     * True for "Supplier" and common typo "Suplier".
     */
    public static function categoryLooksLikeSupplier(string $category): bool
    {
        $c = strtolower(trim($category));

        return str_contains($c, 'supplier') || str_contains($c, 'suplier');
    }

    /**
     * Parties we owe money to: suppliers, and transport (GUDS) customers.
     */
    public static function isPayableParty(?NewCustomer $party = null, ?string $partyCode = null): bool
    {
        if ($party === null && $partyCode !== null) {
            $party = NewCustomer::query()->where('custCode', $partyCode)->first();
        }

        if ($party === null) {
            return false;
        }

        $category = strtolower(trim((string) $party->custCatagory));

        return self::categoryLooksLikeSupplier($category) || $category === 'guds';
    }

    public static function balanceType(?NewCustomer $party = null, ?string $partyCode = null): string
    {
        return self::isPayableParty($party, $partyCode) ? self::TYPE_PAYABLE : self::TYPE_RECEIVABLE;
    }

    public static function balanceLabel(?NewCustomer $party = null, ?string $partyCode = null): string
    {
        return self::isPayableParty($party, $partyCode) ? 'Payable Balance' : 'Receivable Balance';
    }

    /**
     * Signed ledger balance: debit − credit (same running formula as account transaction page).
     */
    public static function ledgerSignedBalance(string $partyCode, ?string $beforeDate = null): float
    {
        $query = CustomerLedger::query()
            ->where('customerCode', $partyCode)
            ->where('statuse', '0');

        if ($beforeDate !== null) {
            $query->whereDate('customerDate', '<', $beforeDate);
        }

        return (float) ($query
            ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as balance')
            ->value('balance') ?? 0);
    }

    /**
     * Amount shown on invoices / party screens (receivable or payable, always ≥ 0 when party owes in the usual direction).
     */
    public static function displayBalance(string $partyCode, ?string $beforeDate = null): float
    {
        $signed = self::ledgerSignedBalance($partyCode, $beforeDate);
        $party = NewCustomer::query()->where('custCode', $partyCode)->first();

        if (self::isPayableParty($party)) {
            return (float) (-$signed);
        }

        return (float) $signed;
    }

    /**
     * @return array{
     *   party_code: string,
     *   balance_type: string,
     *   balance_label: string,
     *   balance: float,
     *   ledger_signed: float,
     *   is_supplier: bool
     * }
     */
    public static function meta(string $partyCode): array
    {
        $party = NewCustomer::query()->where('custCode', $partyCode)->first();
        $signed = self::ledgerSignedBalance($partyCode);
        $isSupplier = self::isSupplier($party, $partyCode);
        $isPayable = self::isPayableParty($party, $partyCode);

        return [
            'party_code' => $partyCode,
            'balance_type' => $isPayable ? self::TYPE_PAYABLE : self::TYPE_RECEIVABLE,
            'balance_label' => $isPayable ? 'Payable Balance' : 'Receivable Balance',
            'balance' => $isPayable ? (float) (-$signed) : (float) $signed,
            'ledger_signed' => (float) $signed,
            'is_supplier' => $isSupplier,
        ];
    }

    public static function syncCustBalance(string $partyCode): void
    {
        $party = NewCustomer::query()->where('custCode', $partyCode)->first();
        if (! $party) {
            return;
        }

        $party->update(['custBalance' => self::displayBalance($partyCode)]);
    }

    /**
     * Ledger statement rows for a date range (customerledgers only).
     *
     * @return array{
     *   opening_signed: float,
     *   opening_display: float,
     *   closing_signed: float,
     *   closing_display: float,
     *   total_debit: float,
     *   total_credit: float,
     *   rows: Collection<int, array<string, mixed>>
     * }
     */
    public static function statement(string $partyCode, string $fromDate, string $toDate): array
    {
        $party = NewCustomer::query()->where('custCode', $partyCode)->first();
        $isPayable = self::isPayableParty($party, $partyCode);

        $openingSigned = self::ledgerSignedBalance($partyCode, $fromDate);
        $openingDisplay = $isPayable ? -$openingSigned : $openingSigned;

        $ledgerRows = CustomerLedger::query()
            ->where('customerCode', $partyCode)
            ->where('statuse', '0')
            ->whereBetween('customerDate', [$fromDate, $toDate])
            ->orderBy('customerDate')
            ->orderBy('id')
            ->get();

        $runningSigned = $openingSigned;
        $runningDisplay = $openingDisplay;
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        $rows = $ledgerRows->map(function (CustomerLedger $row) use ($isPayable, &$runningSigned, &$runningDisplay, &$totalDebit, &$totalCredit) {
            $debit = (float) ($row->debit ?? 0);
            $credit = (float) ($row->credit ?? 0);
            $totalDebit += $debit;
            $totalCredit += $credit;

            $runningSigned += $debit - $credit;
            $deltaDisplay = $isPayable ? ($credit - $debit) : ($debit - $credit);
            $runningDisplay += $deltaDisplay;

            $category = trim((string) ($row->category ?? ''));

            return [
                'date' => Carbon::parse($row->customerDate)->format('Y-m-d'),
                'reference_no' => $row->invoiceNumber ?: ('LED-'.$row->id),
                'type' => $category !== '' ? $category : ($debit > 0 ? 'Debit' : ($credit > 0 ? 'Credit' : 'Ledger')),
                'description' => trim((string) ($row->description ?: '-')),
                'qty' => null,
                'rate' => null,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => $runningDisplay,
                'ledger_signed' => $runningSigned,
            ];
        });

        return [
            'opening_signed' => $openingSigned,
            'opening_display' => $openingDisplay,
            'closing_signed' => $runningSigned,
            'closing_display' => $runningDisplay,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'balance_type' => $isPayable ? self::TYPE_PAYABLE : self::TYPE_RECEIVABLE,
            'balance_label' => $isPayable ? 'Payable Balance' : 'Receivable Balance',
            'rows' => $rows,
        ];
    }

    /**
     * Portfolio totals for dashboard (from ledger, not cached custBalance).
     *
     * @return array{total_receivable: float, total_payable: float, customer_count: int, supplier_count: int}
     */
    public static function portfolioTotals(): array
    {
        $signedByParty = CustomerLedger::query()
            ->where('statuse', '0')
            ->groupBy('customerCode')
            ->selectRaw('customerCode, COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as signed')
            ->pluck('signed', 'customerCode');

        // One query instead of N× NewCustomer lookups (was making dashboard very slow).
        $supplierCodes = NewCustomer::query()
            ->where(function ($q) {
                $q->whereRaw('LOWER(custCatagory) LIKE ?', ['%supplier%'])
                    ->orWhereRaw('LOWER(custCatagory) = ?', ['guds']);
            })
            ->pluck('custCode')
            ->filter()
            ->mapWithKeys(fn ($code) => [(string) $code => true])
            ->all();

        $totalReceivable = 0.0;
        $totalPayable = 0.0;
        $customerCount = 0;
        $supplierCount = 0;

        foreach ($signedByParty as $code => $signed) {
            $signed = (float) $signed;
            $code = (string) $code;
            if (isset($supplierCodes[$code])) {
                $payable = -$signed;
                if ($payable > 0) {
                    $totalPayable += $payable;
                    $supplierCount++;
                }
            } elseif ($signed > 0) {
                $totalReceivable += $signed;
                $customerCount++;
            }
        }

        return [
            'total_receivable' => round($totalReceivable, 2),
            'total_payable' => round($totalPayable, 2),
            'customer_count' => $customerCount,
            'supplier_count' => $supplierCount,
        ];
    }

    public static function supplierOptions(): Collection
    {
        $fromMaster = NewCustomer::query()
            ->where(function ($q) {
                $q->whereRaw('LOWER(custCatagory) LIKE ?', ['%supplier%'])
                    ->orWhereRaw('LOWER(custCatagory) = ?', ['guds'])
                    ->orWhereRaw('LOWER(custCatagory) LIKE ?', ['%suplier%']);
            })
            ->orderBy('custName')
            ->get([
                'custCode as supplierCode',
                'custName as supplierName',
                'custArea as supplierArea',
                'custCity as supplierCity',
            ]);

        $fromPurchases = DB::table('purchase_invoices')
            ->whereNotNull('supplierCode')
            ->where('supplierCode', '!=', '')
            ->select('supplierCode', 'supplierName')
            ->distinct()
            ->orderBy('supplierName')
            ->get();

        $partyByCode = NewCustomer::query()
            ->whereIn('custCode', $fromPurchases->pluck('supplierCode')->filter()->unique()->values()->all())
            ->get(['custCode', 'custName', 'custArea', 'custCity'])
            ->keyBy('custCode');

        $fromPurchases = $fromPurchases->map(function ($row) use ($partyByCode) {
            $party = $partyByCode->get($row->supplierCode);

            return (object) [
                'supplierCode' => $row->supplierCode,
                'supplierName' => $party->custName ?? $row->supplierName,
                'supplierArea' => $party->custArea ?? '',
                'supplierCity' => $party->custCity ?? '',
            ];
        });

        return $fromMaster
            ->concat($fromPurchases)
            ->unique('supplierCode')
            ->sortBy('supplierName', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }
}
