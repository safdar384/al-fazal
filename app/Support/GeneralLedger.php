<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * General ledger (accounts + ledgers) — double-entry mirror for invoices.
 * Party detail remains in customerledgers via PartyLedger.
 */
class GeneralLedger
{
    public static function enabled(): bool
    {
        return (bool) config('accounting.general_ledger_enabled', true);
    }

    public static function postSaleInvoice(string $date, string $invoiceNo, float $grandTotal, string $description = ''): void
    {
        if (! self::enabled()) {
            return;
        }

        $amount = round(max(0, $grandTotal), 2);
        if ($amount <= 0) {
            return;
        }

        self::postPair(
            $date,
            $invoiceNo,
            config('accounting.accounts.accounts_receivable'),
            config('accounting.accounts.sales_revenue'),
            $amount,
            $description ?: 'Sale Invoice '.$invoiceNo,
            PartyLedger::category('sale_invoice')
        );
    }

    /**
     * Cash sale: Cash/Bank DEBIT, Sales Revenue CREDIT (no receivable).
     */
    public static function postCashSaleInvoice(string $date, string $invoiceNo, float $grandTotal, string $description = ''): void
    {
        if (! self::enabled()) {
            return;
        }

        $amount = round(max(0, $grandTotal), 2);
        if ($amount <= 0) {
            return;
        }

        self::postPair(
            $date,
            $invoiceNo,
            config('accounting.accounts.cash_on_hand'),
            config('accounting.accounts.sales_revenue'),
            $amount,
            $description ?: 'Cash Sale '.$invoiceNo,
            config('accounting.categories.cash_sale', 'Cash Sale')
        );
    }

    /**
     * Sale Return: Sales Revenue DEBIT, Accounts Receivable CREDIT (reverse of sale invoice).
     */
    public static function postSaleReturn(string $date, string $returnNo, float $grandTotal, string $description = ''): void
    {
        if (! self::enabled()) {
            return;
        }

        $amount = round(max(0, $grandTotal), 2);
        if ($amount <= 0) {
            return;
        }

        self::postPair(
            $date,
            $returnNo,
            config('accounting.accounts.sales_revenue'),
            config('accounting.accounts.accounts_receivable'),
            $amount,
            $description ?: 'Sale Return '.$returnNo,
            PartyLedger::category('sale_return')
        );
    }

    public static function postPurchaseInvoice(string $date, string $invoiceNo, float $grandTotal, string $description = ''): void
    {
        if (! self::enabled()) {
            return;
        }

        $amount = round(max(0, $grandTotal), 2);
        if ($amount <= 0) {
            return;
        }

        self::postPair(
            $date,
            $invoiceNo,
            config('accounting.accounts.purchases_stock'),
            config('accounting.accounts.accounts_payable'),
            $amount,
            $description ?: 'Purchase Invoice '.$invoiceNo,
            PartyLedger::category('purchase')
        );
    }

    /**
     * Purchase Return: Accounts Payable DEBIT, Purchases/Stock CREDIT (reverse of purchase invoice).
     */
    public static function postPurchaseReturn(string $date, string $returnNo, float $grandTotal, string $description = ''): void
    {
        if (! self::enabled()) {
            return;
        }

        $amount = round(max(0, $grandTotal), 2);
        if ($amount <= 0) {
            return;
        }

        self::postPair(
            $date,
            $returnNo,
            config('accounting.accounts.accounts_payable'),
            config('accounting.accounts.purchases_stock'),
            $amount,
            $description ?: 'Purchase Return '.$returnNo,
            PartyLedger::category('purchase_return')
        );
    }

    public static function voidByReference(string $referenceNo): void
    {
        if (! self::enabled()) {
            return;
        }

        DB::table('ledgers')->where('reference_no', $referenceNo)->delete();
    }

    protected static function postPair(
        string $date,
        string $referenceNo,
        ?string $debitAccountCode,
        ?string $creditAccountCode,
        float $amount,
        string $description,
        string $category
    ): void {
        $debitAccountId = self::accountId($debitAccountCode);
        $creditAccountId = self::accountId($creditAccountCode);

        if (! $debitAccountId || ! $creditAccountId) {
            Log::warning('GeneralLedger: missing account mapping', [
                'debit' => $debitAccountCode,
                'credit' => $creditAccountCode,
                'ref' => $referenceNo,
            ]);

            return;
        }

        $now = now();

        DB::table('ledgers')->insert([
            'entry_date' => $date,
            'reference_no' => $referenceNo,
            'account_id' => $debitAccountId,
            'debit' => $amount,
            'credit' => 0,
            'description' => $description,
            'category' => $category,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('ledgers')->insert([
            'entry_date' => $date,
            'reference_no' => $referenceNo,
            'account_id' => $creditAccountId,
            'debit' => 0,
            'credit' => $amount,
            'description' => $description,
            'category' => $category,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    protected static function accountId(?string $code): ?int
    {
        if (! $code) {
            return null;
        }

        $id = DB::table('accounts')->where('account_code', $code)->value('id');

        return $id ? (int) $id : null;
    }
}
