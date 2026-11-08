<?php

namespace App\Support;

use App\Models\CustomerCheque;
use App\Models\CustomerLedger;
use App\Models\NewCustomer;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Party sub-ledger postings (customerledgers).
 *
 * Signed balance: SUM(debit) − SUM(credit)
 * Customer sale / supplier payment → debit
 * Customer recovery / supplier purchase → credit
 */
class PartyLedger
{
    public static function category(string $key): string
    {
        return (string) config("accounting.categories.{$key}", $key);
    }

    /**
     * Categories used by Supplier Transaction module (AP only — not Customer Ledger UI).
     *
     * @return list<string>
     */
    public static function supplierTransactionCategories(): array
    {
        return array_values(array_unique([
            self::category('supplier_purchase'),
            self::category('supplier_sale'),
            self::category('supplier_cash'),
            'SupplierPurchase',
            'SupplierSale',
            'SupplierCash',
        ]));
    }

    /**
     * Hide Supplier Transaction rows from Customer Ledger / Account Transaction screens.
     * Still counted in PartyBalance and Supplier Ledger.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public static function excludeSupplierTransactions($query)
    {
        $categories = self::supplierTransactionCategories();

        return $query
            ->where(function ($q) {
                $q->whereNull('invoiceNumber')
                    ->orWhere('invoiceNumber', 'not like', 'ST-%');
            })
            ->where(function ($q) use ($categories) {
                $q->whereNull('category')
                    ->orWhereNotIn('category', $categories);
            });
    }

    public static function postSaleInvoice(
        string $customerCode,
        string $invoiceNo,
        string $invoiceDate,
        float $grandTotal,
        ?string $customerArea = null
    ): CustomerLedger {
        $amount = round(max(0, $grandTotal), 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Sale invoice total must be greater than zero.');
        }

        $entry = self::createEntry([
            'customerDate' => $invoiceDate,
            'invoiceNumber' => $invoiceNo,
            'customerArea' => $customerArea ?? self::partyArea($customerCode),
            'customerCode' => $customerCode,
            'description' => 'Sale Invoice '.$invoiceNo,
            'debit' => $amount,
            'credit' => 0,
            'category' => self::category('sale_invoice'),
        ]);

        PartyBalance::syncCustBalance($customerCode);

        return $entry;
    }

    public static function syncSaleInvoice(
        string $customerCode,
        string $invoiceNo,
        string $invoiceDate,
        float $grandTotal,
        ?string $customerArea = null
    ): CustomerLedger {
        self::deleteByInvoiceNumber($invoiceNo);

        return self::postSaleInvoice($customerCode, $invoiceNo, $invoiceDate, $grandTotal, $customerArea);
    }

    /**
     * Sale Return: credit customer (reduce receivable) for full return amount.
     */
    public static function postSaleReturn(
        string $customerCode,
        string $returnNo,
        string $returnDate,
        float $grandTotal,
        ?string $customerArea = null
    ): CustomerLedger {
        $amount = round(max(0, $grandTotal), 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Sale return total must be greater than zero.');
        }

        $entry = self::createEntry([
            'customerDate' => $returnDate,
            'invoiceNumber' => $returnNo,
            'customerArea' => $customerArea ?? self::partyArea($customerCode),
            'customerCode' => $customerCode,
            'description' => 'Sale Return '.$returnNo,
            'debit' => 0,
            'credit' => $amount,
            'category' => self::category('sale_return'),
        ]);

        PartyBalance::syncCustBalance($customerCode);

        return $entry;
    }

    public static function postPurchaseInvoice(
        string $supplierCode,
        string $invoiceNo,
        string $invoiceDate,
        float $grandTotal
    ): CustomerLedger {
        self::assertSupplierParty($supplierCode);

        $amount = round(max(0, $grandTotal), 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Purchase invoice total must be greater than zero.');
        }

        $entry = self::createEntry([
            'customerDate' => $invoiceDate,
            'invoiceNumber' => $invoiceNo,
            'customerArea' => self::partyArea($supplierCode),
            'customerCode' => $supplierCode,
            'description' => 'Purchase Invoice '.$invoiceNo,
            'debit' => 0,
            'credit' => $amount,
            'category' => self::category('purchase'),
        ]);

        PartyBalance::syncCustBalance($supplierCode);

        return $entry;
    }

    /**
     * Purchase Return: debit supplier (reduce payable) for full return amount.
     */
    public static function postPurchaseReturn(
        string $supplierCode,
        string $returnNo,
        string $returnDate,
        float $grandTotal,
        ?string $supplierArea = null
    ): CustomerLedger {
        self::assertSupplierParty($supplierCode);

        $amount = round(max(0, $grandTotal), 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Purchase return total must be greater than zero.');
        }

        $entry = self::createEntry([
            'customerDate' => $returnDate,
            'invoiceNumber' => $returnNo,
            'customerArea' => $supplierArea ?? self::partyArea($supplierCode),
            'customerCode' => $supplierCode,
            'description' => 'Purchase Return '.$returnNo,
            'debit' => $amount,
            'credit' => 0,
            'category' => self::category('purchase_return'),
        ]);

        PartyBalance::syncCustBalance($supplierCode);

        return $entry;
    }

    public static function syncPurchaseInvoice(
        string $supplierCode,
        string $invoiceNo,
        string $invoiceDate,
        float $grandTotal
    ): CustomerLedger {
        self::deleteByInvoiceNumber($invoiceNo);

        return self::postPurchaseInvoice($supplierCode, $invoiceNo, $invoiceDate, $grandTotal);
    }

    /**
     * Freight we owe the transport party. Credit increases their payable.
     * Description carries weight, rate, and amount for the ledger screen.
     */
    public static function postTransportCharge(
        string $transportCode,
        string $invoiceNo,
        string $invoiceDate,
        float $weight,
        float $rate,
        float $amount,
        string $vehicleNumber = '',
        string $dispatchNo = ''
    ): ?CustomerLedger {
        $transportCode = trim($transportCode);
        $amount = round(max(0, $amount), 2);
        if ($transportCode === '' || $amount <= 0) {
            return null;
        }

        $party = NewCustomer::query()->where('custCode', $transportCode)->first();
        if (! $party) {
            throw new InvalidArgumentException("Transport [{$transportCode}] was not found.");
        }

        $weightText = rtrim(rtrim(number_format($weight, 2, '.', ''), '0'), '.');
        $rateText = number_format($rate, 2, '.', '');
        $amountText = number_format($amount, 2, '.', ',');
        $parts = ["Transport {$invoiceNo}"];
        $vehicleNumber = trim($vehicleNumber);
        $dispatchNo = trim($dispatchNo);
        if ($vehicleNumber !== '') {
            $parts[] = 'Vehicle '.$vehicleNumber;
        }
        if ($dispatchNo !== '') {
            $parts[] = 'Dispatch '.$dispatchNo;
        }
        $parts[] = 'Weight '.$weightText;
        $parts[] = 'Rate '.$rateText;
        $parts[] = 'Amount '.$amountText;

        $entry = self::createEntry([
            'customerDate' => $invoiceDate,
            'invoiceNumber' => $invoiceNo,
            'customerArea' => self::partyArea($transportCode),
            'customerCode' => $transportCode,
            'description' => implode(' | ', $parts),
            'debit' => 0,
            'credit' => $amount,
            'category' => self::category('transport'),
        ]);

        PartyBalance::syncCustBalance($transportCode);

        return $entry;
    }

    /**
     * Supplier payment: DEBIT supplier (reduces payable).
     */
    public static function postSupplierPayment(
        string $supplierCode,
        string $paymentDate,
        float $amount,
        string $description,
        ?string $referenceNo = null
    ): CustomerLedger {
        self::assertSupplierParty($supplierCode);

        $amount = round(max(0, $amount), 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Payment amount must be greater than zero.');
        }

        $entry = self::createEntry([
            'customerDate' => $paymentDate,
            'invoiceNumber' => $referenceNo,
            'customerArea' => self::partyArea($supplierCode),
            'customerCode' => $supplierCode,
            'description' => $description,
            'debit' => $amount,
            'credit' => 0,
            'category' => self::category('payment'),
        ]);

        PartyBalance::syncCustBalance($supplierCode);

        return $entry;
    }

    public static function postRecovery(
        string $customerCode,
        string $entryDate,
        float $credit,
        string $description,
        ?string $referenceNo = null
    ): CustomerLedger {
        $amount = round(max(0, $credit), 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Recovery amount must be greater than zero.');
        }

        $entry = self::createEntry([
            'customerDate' => $entryDate,
            'invoiceNumber' => $referenceNo,
            'customerArea' => self::partyArea($customerCode),
            'customerCode' => $customerCode,
            'description' => $description,
            'debit' => 0,
            'credit' => $amount,
            'category' => self::category('recovery'),
        ]);

        PartyBalance::syncCustBalance($customerCode);

        return $entry;
    }

    public static function postManualEntry(
        string $customerCode,
        string $entryDate,
        float $amount,
        string $type,
        string $category,
        string $description
    ): CustomerLedger {
        $amount = round(max(0, $amount), 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Transaction amount must be greater than zero.');
        }

        $credit = strtolower($type) === 'credit' ? $amount : 0.0;
        $debit = strtolower($type) === 'debit' ? $amount : 0.0;

        if ($credit <= 0 && $debit <= 0) {
            throw new InvalidArgumentException('Type must be credit or debit.');
        }

        $entry = self::createEntry([
            'customerDate' => $entryDate,
            'invoiceNumber' => null,
            'customerArea' => self::partyArea($customerCode),
            'customerCode' => $customerCode,
            'description' => $description,
            'debit' => $debit,
            'credit' => $credit,
            'category' => $category,
        ]);

        PartyBalance::syncCustBalance($customerCode);

        return $entry;
    }

    public static function postTransfer(
        string $fromCustomerCode,
        string $toCustomerCode,
        string $entryDate,
        float $amount,
        string $description
    ): void {
        $amount = round(max(0, $amount), 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Transfer amount must be greater than zero.');
        }

        if ($fromCustomerCode === $toCustomerCode) {
            throw new InvalidArgumentException('Cannot transfer to the same party.');
        }

        $fromArea = self::partyArea($fromCustomerCode);
        $toArea = self::partyArea($toCustomerCode);
        $base = trim($description);
        $transferCategory = self::category('transfer');

        self::createEntry([
            'customerDate' => $entryDate,
            'invoiceNumber' => null,
            'customerArea' => $fromArea,
            'customerCode' => $fromCustomerCode,
            'description' => trim($base.' (Transfer to '.$toCustomerCode.')'),
            'debit' => 0,
            'credit' => $amount,
            'category' => $transferCategory,
        ]);

        self::createEntry([
            'customerDate' => $entryDate,
            'invoiceNumber' => null,
            'customerArea' => $toArea,
            'customerCode' => $toCustomerCode,
            'description' => trim($base.' (Transfer from '.$fromCustomerCode.')'),
            'debit' => $amount,
            'credit' => 0,
            'category' => $transferCategory,
        ]);

        PartyBalance::syncCustBalance($fromCustomerCode);
        PartyBalance::syncCustBalance($toCustomerCode);
    }

    public static function deleteByInvoiceNumber(string $invoiceNo): void
    {
        $entries = CustomerLedger::query()
            ->where('invoiceNumber', $invoiceNo)
            ->get();

        if ($entries->isEmpty()) {
            return;
        }

        $partyCodes = $entries->pluck('customerCode')->unique()->filter()->values()->all();

        CustomerLedger::query()
            ->where('invoiceNumber', $invoiceNo)
            ->delete();

        foreach ($partyCodes as $code) {
            PartyBalance::syncCustBalance((string) $code);
        }
    }

    public static function deleteEntry(CustomerLedger $entry): void
    {
        $partyCode = (string) $entry->customerCode;
        $entryId = (string) $entry->id;
        $counterpart = null;

        if (strcasecmp((string) $entry->category, self::category('transfer')) === 0
            || strcasecmp((string) $entry->category, 'Transfer') === 0) {
            $counterpart = self::findTransferCounterpart($entry);
        }

        // Remove linked bank ledger rows (reference_no = customer ledger id)
        DB::table('ledgers')->where('reference_no', $entryId)->delete();

        $entry->delete();

        if ($counterpart) {
            $otherCode = (string) $counterpart->customerCode;
            DB::table('ledgers')->where('reference_no', (string) $counterpart->id)->delete();
            $counterpart->delete();
            PartyBalance::syncCustBalance($otherCode);
        }

        PartyBalance::syncCustBalance($partyCode);
    }

    public static function assertSupplierParty(string $partyCode): void
    {
        if (! PartyBalance::isSupplier(null, $partyCode)) {
            throw new InvalidArgumentException("Party [{$partyCode}] is not a supplier; use sale/recovery flows for customers.");
        }
    }

    protected static function partyArea(string $partyCode): string
    {
        $area = NewCustomer::query()
            ->where('custCode', $partyCode)
            ->value('custArea');

        return (string) ($area ?? '');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected static function createEntry(array $attributes): CustomerLedger
    {
        return CustomerLedger::create(array_merge([
            'statuse' => '0',
        ], $attributes));
    }

    protected static function findTransferCounterpart(CustomerLedger $entry): ?CustomerLedger
    {
        $desc = $entry->description ?? '';
        $otherCode = null;
        $role = null;

        if (preg_match('/Transfer to\s+(\S+)/i', $desc, $m)) {
            $otherCode = $m[1];
            $role = 'to';
        } elseif (preg_match('/Transfer from\s+(\S+)/i', $desc, $m)) {
            $otherCode = $m[1];
            $role = 'from';
        }

        $transferCategory = self::category('transfer');

        if ($otherCode) {
            if ($role === 'to') {
                return CustomerLedger::query()
                    ->where(function ($q) use ($transferCategory) {
                        $q->where('category', $transferCategory)
                            ->orWhere('category', 'Transfer');
                    })
                    ->where('customerCode', $otherCode)
                    ->whereDate('customerDate', $entry->customerDate)
                    ->where('credit', $entry->debit)
                    ->where('debit', 0)
                    ->where('description', 'like', '%Transfer from '.$entry->customerCode.'%')
                    ->orderBy('id')
                    ->first();
            }

            return CustomerLedger::query()
                ->where(function ($q) use ($transferCategory) {
                    $q->where('category', $transferCategory)
                        ->orWhere('category', 'Transfer');
                })
                ->where('customerCode', $otherCode)
                ->whereDate('customerDate', $entry->customerDate)
                ->where('debit', $entry->credit)
                ->where('credit', 0)
                ->where('description', 'like', '%Transfer to '.$entry->customerCode.'%')
                ->orderBy('id')
                ->first();
        }

        if ((float) $entry->credit > 0) {
            return CustomerLedger::query()
                ->where(function ($q) use ($transferCategory) {
                    $q->where('category', $transferCategory)
                        ->orWhere('category', 'Transfer');
                })
                ->where('customerCode', '!=', $entry->customerCode)
                ->whereDate('customerDate', $entry->customerDate)
                ->where('debit', $entry->credit)
                ->where('credit', 0)
                ->orderBy('id')
                ->first();
        }

        return CustomerLedger::query()
            ->where(function ($q) use ($transferCategory) {
                $q->where('category', $transferCategory)
                    ->orWhere('category', 'Transfer');
            })
            ->where('customerCode', '!=', $entry->customerCode)
            ->whereDate('customerDate', $entry->customerDate)
            ->where('credit', $entry->debit)
            ->where('debit', 0)
            ->orderBy('id')
            ->first();
    }
}
