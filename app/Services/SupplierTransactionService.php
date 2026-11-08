<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\NewCustomer;
use App\Models\newProduct;
use App\Models\SupplierTransaction;
use App\Models\SupplierTransactionItem;
use App\Support\BankLedgerQuery;
use App\Support\FittingWorkStockService;
use App\Support\PartyBalance;
use App\Support\PartyLedger;
use App\Support\PurchaseStockService;
use App\Support\SaleStockService;
use App\Support\SupplierModuleCache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SupplierTransactionService
{
    public const BANK_LEDGER_CATEGORY = 'SupplierCash';

    public function nextTxnNo(): string
    {
        $last = SupplierTransaction::query()
            ->orderByDesc('id')
            ->value('txn_no');

        if (! $last || ! preg_match('/ST-(\d+)/', $last, $m)) {
            return 'ST-0001';
        }

        return 'ST-'.str_pad((string) ((int) $m[1] + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): SupplierTransaction
    {
        $party = NewCustomer::query()->where('custCode', $data['supplierCode'])->first();
        if (! $party) {
            throw new InvalidArgumentException('Supplier not found.');
        }
        if (! NewCustomer::isProductSupplierCategory($party->custCatagory ?? null)) {
            throw new InvalidArgumentException('Selected party must be a Product Supplier.');
        }

        $type = $data['type'];
        if (! in_array($type, [
            SupplierTransaction::TYPE_SALE,
            SupplierTransaction::TYPE_PURCHASE,
            SupplierTransaction::TYPE_CASH,
        ], true)) {
            throw new InvalidArgumentException('Invalid transaction type.');
        }

        $date = $data['transactionDate'] ?? now()->format('Y-m-d');

        if ($type === SupplierTransaction::TYPE_CASH) {
            return $this->storeCash($party, $data, $date);
        }

        return $this->storeProductTxn($party, $data, $type, $date);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function storeCash(NewCustomer $party, array $data, string $date): SupplierTransaction
    {
        $description = trim((string) ($data['description'] ?? $data['remarks'] ?? ''));
        if ($description === '') {
            throw new InvalidArgumentException('Description is required for cash entry.');
        }

        $amount = round((float) ($data['amount'] ?? 0), 2);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Cash amount must be greater than zero.');
        }

        $entryType = strtolower((string) ($data['entryType'] ?? 'pay'));
        if (! in_array($entryType, ['pay', 'receive'], true)) {
            throw new InvalidArgumentException('Cash entry type must be Pay or Receive.');
        }

        $bankCode = trim((string) ($data['bankAccount'] ?? ''));
        if ($bankCode === '') {
            throw new InvalidArgumentException('Please select a bank account.');
        }
        $bank = BankAccount::query()->where('bankAccount', $bankCode)->first();
        if (! $bank) {
            throw new InvalidArgumentException('Bank account not found.');
        }

        // Pay → Debit (payment to supplier); Receive → Credit
        $ledgerSide = $entryType === 'receive' ? 'credit' : 'debit';
        $label = $entryType === 'receive' ? 'Receive' : 'Pay';

        return DB::transaction(function () use ($party, $description, $amount, $date, $entryType, $ledgerSide, $label, $bank) {
            $txnNo = $this->nextTxnNo();

            $txn = SupplierTransaction::create([
                'txn_no' => $txnNo,
                'supplier_code' => $party->custCode,
                'supplier_name' => $party->custName,
                'txn_type' => SupplierTransaction::TYPE_CASH,
                'entry_type' => $entryType,
                'bank_account' => $bank->bankAccount,
                'txn_date' => $date,
                'total_amount' => $amount,
                'remarks' => $description,
                'status' => 'Active',
            ]);

            SupplierTransactionItem::create([
                'supplier_transaction_id' => $txn->id,
                'txn_no' => $txnNo,
                'product_code' => 'CASH',
                'product_name' => $description,
                'variant_id' => null,
                'variant_name' => null,
                'variant_value' => null,
                'qty' => 1,
                'rate' => $amount,
                'amount' => $amount,
            ]);

            $ledger = PartyLedger::postManualEntry(
                $party->custCode,
                $date,
                $amount,
                $ledgerSide,
                PartyLedger::category('supplier_cash'),
                'Cash ('.$label.') — '.$description.' ['.$bank->bankName.']'
            );

            $ledger->update(['invoiceNumber' => $txnNo]);
            $this->syncBankLedgerForCash($txn, $bank, $date, $amount, $entryType, $description);

            $loaded = $txn->load('items');
            SupplierModuleCache::bumpParty($party->custCode);

            return $loaded;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function storeProductTxn(NewCustomer $party, array $data, string $type, string $date): SupplierTransaction
    {
        $product = newProduct::query()->where('productCode', $data['productCode'] ?? '')->first();
        if (! $product) {
            throw new InvalidArgumentException('Product not found.');
        }

        $qty = round((float) $data['qty'], 2);
        $rate = round((float) $data['rate'], 2);
        $amount = round($qty * $rate, 2);
        if ($qty <= 0 || $amount < 0) {
            throw new InvalidArgumentException('Invalid quantity or amount.');
        }

        $workName = ucwords(strtolower(trim((string) ($data['workName'] ?? ''))));

        $stockItem = [[
            'productCode' => $product->productCode,
            'productName' => $product->productName,
            'workName' => $workName,
            'variant_id' => $data['variant_id'] ?? null,
            'qty' => $qty,
        ]];

        return DB::transaction(function () use ($data, $party, $product, $type, $qty, $rate, $amount, $date, $stockItem, $workName) {
            $this->assertStockAvailable($type, $stockItem);

            $txnNo = $this->nextTxnNo();

            $txn = SupplierTransaction::create([
                'txn_no' => $txnNo,
                'supplier_code' => $party->custCode,
                'supplier_name' => $party->custName,
                'txn_type' => $type,
                'txn_date' => $date,
                'total_amount' => $amount,
                'remarks' => $data['remarks'] ?? null,
                'status' => 'Active',
            ]);

            SupplierTransactionItem::create([
                'supplier_transaction_id' => $txn->id,
                'txn_no' => $txnNo,
                'product_code' => $product->productCode,
                'product_name' => $product->productName,
                'work_name' => $workName !== '' ? $workName : null,
                'variant_id' => $data['variant_id'] ?? null,
                'variant_name' => $data['variant_name'] ?? null,
                'variant_value' => $data['variant_value'] ?? null,
                'qty' => $qty,
                'rate' => $rate,
                'amount' => $amount,
            ]);

            $this->postStock($type, $stockItem);
            $ledgerLabel = $workName !== ''
                ? $product->productName.' / '.$workName
                : (string) $product->productName;
            $this->postLedger(
                $type,
                $party->custCode,
                $txnNo,
                $date,
                $amount,
                $ledgerLabel
            );

            $loaded = $txn->load('items');
            SupplierModuleCache::bumpParty($party->custCode);

            return $loaded;
        });
    }

    /**
     * Update existing ST entry (keeps same txn_no). Reverses old stock/ledger then re-posts.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(SupplierTransaction $txn, array $data): SupplierTransaction
    {
        $party = NewCustomer::query()->where('custCode', $data['supplierCode'] ?? $txn->supplier_code)->first();
        if (! $party) {
            throw new InvalidArgumentException('Supplier not found.');
        }
        if (! NewCustomer::isProductSupplierCategory($party->custCatagory ?? null)) {
            throw new InvalidArgumentException('Selected party must be a Product Supplier.');
        }

        $type = $data['type'] ?? $txn->txn_type;
        if (! in_array($type, [
            SupplierTransaction::TYPE_SALE,
            SupplierTransaction::TYPE_PURCHASE,
            SupplierTransaction::TYPE_CASH,
        ], true)) {
            throw new InvalidArgumentException('Invalid transaction type.');
        }

        $date = $data['transactionDate']
            ?? optional($txn->txn_date)->format('Y-m-d')
            ?? now()->format('Y-m-d');

        return DB::transaction(function () use ($txn, $party, $data, $type, $date) {
            $txn->loadMissing('items');
            $txnNo = (string) $txn->txn_no;

            // Reverse previous stock + ledger
            if ($txn->txn_type !== SupplierTransaction::TYPE_CASH) {
                $oldStock = $txn->items
                    ->filter(fn ($item) => $item->product_code !== 'CASH')
                    ->map(fn ($item) => [
                        'productCode' => $item->product_code,
                        'productName' => $item->product_name,
                        'workName' => (string) ($item->work_name ?? ''),
                        'variant_id' => $item->variant_id,
                        'qty' => (float) $item->qty,
                    ])->all();
                if ($oldStock !== []) {
                    $this->reverseStock($txn->txn_type, $oldStock);
                }
            }
            PartyLedger::deleteByInvoiceNumber($txnNo);
            $this->deleteBankLedgerForTxn($txnNo);
            $txn->items()->delete();

            if ($type === SupplierTransaction::TYPE_CASH) {
                $description = trim((string) ($data['description'] ?? $data['remarks'] ?? ''));
                if ($description === '') {
                    throw new InvalidArgumentException('Description is required for cash entry.');
                }
                $amount = round((float) ($data['amount'] ?? 0), 2);
                if ($amount <= 0) {
                    throw new InvalidArgumentException('Cash amount must be greater than zero.');
                }
                $entryType = strtolower((string) ($data['entryType'] ?? 'pay'));
                if (! in_array($entryType, ['pay', 'receive'], true)) {
                    throw new InvalidArgumentException('Cash entry type must be Pay or Receive.');
                }
                $bankCode = trim((string) ($data['bankAccount'] ?? ''));
                if ($bankCode === '') {
                    throw new InvalidArgumentException('Please select a bank account.');
                }
                $bank = BankAccount::query()->where('bankAccount', $bankCode)->first();
                if (! $bank) {
                    throw new InvalidArgumentException('Bank account not found.');
                }
                $ledgerSide = $entryType === 'receive' ? 'credit' : 'debit';
                $label = $entryType === 'receive' ? 'Receive' : 'Pay';

                $txn->update([
                    'supplier_code' => $party->custCode,
                    'supplier_name' => $party->custName,
                    'txn_type' => SupplierTransaction::TYPE_CASH,
                    'entry_type' => $entryType,
                    'bank_account' => $bank->bankAccount,
                    'txn_date' => $date,
                    'total_amount' => $amount,
                    'remarks' => $description,
                    'status' => 'Active',
                ]);

                SupplierTransactionItem::create([
                    'supplier_transaction_id' => $txn->id,
                    'txn_no' => $txnNo,
                    'product_code' => 'CASH',
                    'product_name' => $description,
                    'variant_id' => null,
                    'variant_name' => null,
                    'variant_value' => null,
                    'qty' => 1,
                    'rate' => $amount,
                    'amount' => $amount,
                ]);

                $ledger = PartyLedger::postManualEntry(
                    $party->custCode,
                    $date,
                    $amount,
                    $ledgerSide,
                    PartyLedger::category('supplier_cash'),
                    'Cash ('.$label.') — '.$description.' ['.$bank->bankName.']'
                );
                $ledger->update(['invoiceNumber' => $txnNo]);
                $this->syncBankLedgerForCash($txn, $bank, $date, $amount, $entryType, $description);
            } else {
                $product = newProduct::query()->where('productCode', $data['productCode'] ?? '')->first();
                if (! $product) {
                    throw new InvalidArgumentException('Product not found.');
                }
                $qty = round((float) $data['qty'], 2);
                $rate = round((float) $data['rate'], 2);
                $amount = round($qty * $rate, 2);
                if ($qty <= 0 || $amount < 0) {
                    throw new InvalidArgumentException('Invalid quantity or amount.');
                }
                $workName = ucwords(strtolower(trim((string) ($data['workName'] ?? ''))));
                $stockItem = [[
                    'productCode' => $product->productCode,
                    'productName' => $product->productName,
                    'workName' => $workName,
                    'variant_id' => $data['variant_id'] ?? null,
                    'qty' => $qty,
                ]];
                $this->assertStockAvailable($type, $stockItem);

                $txn->update([
                    'supplier_code' => $party->custCode,
                    'supplier_name' => $party->custName,
                    'txn_type' => $type,
                    'entry_type' => null,
                    'bank_account' => null,
                    'txn_date' => $date,
                    'total_amount' => $amount,
                    'remarks' => $data['remarks'] ?? null,
                    'status' => 'Active',
                ]);

                SupplierTransactionItem::create([
                    'supplier_transaction_id' => $txn->id,
                    'txn_no' => $txnNo,
                    'product_code' => $product->productCode,
                    'product_name' => $product->productName,
                    'work_name' => $workName !== '' ? $workName : null,
                    'variant_id' => $data['variant_id'] ?? null,
                    'variant_name' => $data['variant_name'] ?? null,
                    'variant_value' => $data['variant_value'] ?? null,
                    'qty' => $qty,
                    'rate' => $rate,
                    'amount' => $amount,
                ]);

                $this->postStock($type, $stockItem);
                $ledgerLabel = $workName !== ''
                    ? $product->productName.' / '.$workName
                    : (string) $product->productName;
                $this->postLedger(
                    $type,
                    $party->custCode,
                    $txnNo,
                    $date,
                    $amount,
                    $ledgerLabel
                );
            }

            $loaded = $txn->fresh(['items']);
            SupplierModuleCache::bumpParty($party->custCode);

            return $loaded;
        });
    }

    public function delete(SupplierTransaction $txn): void
    {
        $supplierCode = (string) $txn->supplier_code;

        DB::transaction(function () use ($txn) {
            $txn->loadMissing('items');

            if ($txn->txn_type !== SupplierTransaction::TYPE_CASH) {
                $stockItems = $txn->items
                    ->filter(fn ($item) => $item->product_code !== 'CASH')
                    ->map(fn ($item) => [
                        'productCode' => $item->product_code,
                        'productName' => $item->product_name,
                        'workName' => (string) ($item->work_name ?? ''),
                        'variant_id' => $item->variant_id,
                        'qty' => (float) $item->qty,
                    ])->all();

                $this->reverseStock($txn->txn_type, $stockItems);
            }

            PartyLedger::deleteByInvoiceNumber($txn->txn_no);
            $this->deleteBankLedgerForTxn((string) $txn->txn_no);
            $txn->delete();
        });

        SupplierModuleCache::bumpParty($supplierCode);
    }

    /**
     * Double-entry bank side for supplier cash:
     * Pay (party debit) → bank credit; Receive (party credit) → bank debit.
     */
    protected function syncBankLedgerForCash(
        SupplierTransaction $txn,
        BankAccount $bank,
        string $date,
        float $amount,
        string $entryType,
        string $description
    ): void {
        $amount = round(max(0, $amount), 2);
        if ($amount <= 0) {
            return;
        }

        $bankDebit = $entryType === 'receive' ? $amount : 0.0;
        $bankCredit = $entryType === 'pay' ? $amount : 0.0;
        $ref = (string) $txn->txn_no;
        $accountId = BankLedgerQuery::ensureGlAccount($bank);
        $label = $entryType === 'receive' ? 'Receive' : 'Pay';
        $desc = 'Supplier Cash ('.$label.') '.$txn->supplier_code.' — '.$description;

        $payload = [
            'entry_date' => $date,
            'reference_no' => $ref,
            'market_voucher_id' => null,
            'account_id' => $accountId,
            'debit' => $bankDebit,
            'credit' => $bankCredit,
            'description' => $desc,
            'category' => self::BANK_LEDGER_CATEGORY,
            'updated_at' => now(),
        ];

        $existingId = DB::table('ledgers')
            ->where('reference_no', $ref)
            ->where('category', self::BANK_LEDGER_CATEGORY)
            ->value('id');

        if ($existingId) {
            DB::table('ledgers')->where('id', $existingId)->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('ledgers')->insert($payload);
        }
    }

    protected function deleteBankLedgerForTxn(string $txnNo): void
    {
        $txnNo = trim($txnNo);
        if ($txnNo === '') {
            return;
        }

        DB::table('ledgers')
            ->where('reference_no', $txnNo)
            ->where('category', self::BANK_LEDGER_CATEGORY)
            ->delete();
    }

    /**
     * @param  array<int, array{productCode: string, productName?: string, workName?: string, variant_id?: int|null, qty: mixed}>  $items
     */
    protected function assertStockAvailable(string $type, array $items): void
    {
        if ($type !== SupplierTransaction::TYPE_SALE) {
            return;
        }

        $productItems = [];
        foreach ($items as $item) {
            $workName = trim((string) ($item['workName'] ?? ''));
            $qty = (float) ($item['qty'] ?? 0);
            if ($workName !== '') {
                FittingWorkStockService::assertAvailable(
                    (string) ($item['productCode'] ?? ''),
                    $workName,
                    $qty
                );
            } else {
                $productItems[] = $item;
            }
        }

        if ($productItems !== []) {
            SaleStockService::assertAvailable($productItems);
        }
    }

    /**
     * Work selected → work stock; otherwise product inventory.
     * Purchase work increases work stock; Sale work decreases work stock.
     *
     * @param  array<int, array{productCode: string, productName?: string, workName?: string, variant_id?: int|null, qty: mixed}>  $items
     */
    protected function postStock(string $type, array $items): void
    {
        $productItems = [];
        foreach ($items as $item) {
            $workName = trim((string) ($item['workName'] ?? ''));
            $qty = (float) ($item['qty'] ?? 0);
            $productCode = (string) ($item['productCode'] ?? '');
            $productName = (string) ($item['productName'] ?? '');

            if ($workName !== '') {
                if ($type === SupplierTransaction::TYPE_PURCHASE) {
                    FittingWorkStockService::increase($productCode, $productName, $workName, $qty);
                } elseif ($type === SupplierTransaction::TYPE_SALE) {
                    FittingWorkStockService::decrease($productCode, $workName, $qty);
                }
                continue;
            }

            $productItems[] = $item;
        }

        if ($productItems === []) {
            return;
        }

        if ($type === SupplierTransaction::TYPE_PURCHASE) {
            PurchaseStockService::receive($productItems);

            return;
        }

        if ($type === SupplierTransaction::TYPE_SALE) {
            SaleStockService::deduct($productItems);
        }
    }

    /**
     * @param  array<int, array{productCode: string, productName?: string, workName?: string, variant_id?: int|null, qty: mixed}>  $items
     */
    protected function reverseStock(string $type, array $items): void
    {
        $productItems = [];
        foreach ($items as $item) {
            $workName = trim((string) ($item['workName'] ?? ''));
            $qty = (float) ($item['qty'] ?? 0);
            $productCode = (string) ($item['productCode'] ?? '');
            $productName = (string) ($item['productName'] ?? '');

            if ($workName !== '') {
                // Reverse: Purchase undo → decrease; Sale undo → increase
                if ($type === SupplierTransaction::TYPE_PURCHASE) {
                    FittingWorkStockService::decrease($productCode, $workName, $qty);
                } elseif ($type === SupplierTransaction::TYPE_SALE) {
                    FittingWorkStockService::increase($productCode, $productName, $workName, $qty);
                }
                continue;
            }

            $productItems[] = $item;
        }

        if ($productItems === []) {
            return;
        }

        if ($type === SupplierTransaction::TYPE_PURCHASE) {
            PurchaseStockService::reverse($productItems);

            return;
        }

        if ($type === SupplierTransaction::TYPE_SALE) {
            SaleStockService::restore($productItems);
        }
    }

    protected function postLedger(
        string $type,
        string $supplierCode,
        string $txnNo,
        string $date,
        float $amount,
        string $productName = ''
    ): void {
        // Dedicated supplier-txn categories so Customer Ledger UI can hide these rows.
        // Payable still updates via PartyBalance (same customerledgers store).
        if ($type === SupplierTransaction::TYPE_PURCHASE) {
            $ledger = PartyLedger::postManualEntry(
                $supplierCode,
                $date,
                $amount,
                'credit',
                PartyLedger::category('supplier_purchase'),
                trim('Supplier Purchase '.$txnNo.($productName !== '' ? ' — '.$productName : ''))
            );
            $ledger->update(['invoiceNumber' => $txnNo]);

            return;
        }

        if ($type === SupplierTransaction::TYPE_SALE) {
            $ledger = PartyLedger::postManualEntry(
                $supplierCode,
                $date,
                $amount,
                'debit',
                PartyLedger::category('supplier_sale'),
                trim('Supplier Sale '.$txnNo.($productName !== '' ? ' — '.$productName : ''))
            );
            $ledger->update(['invoiceNumber' => $txnNo]);
        }
    }
}
