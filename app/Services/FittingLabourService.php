<?php

namespace App\Services;

use App\Models\FittingLabour;
use App\Models\FittingLabourItem;
use App\Models\Labour;
use App\Models\LabourAccountTransaction;
use App\Models\newProduct;
use App\Support\FittingModuleCache;
use App\Support\FittingWorkStockService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FittingLabourService
{
    public function nextTxnNo(): string
    {
        $last = FittingLabour::query()->orderByDesc('id')->value('txn_no');

        if (! $last || ! preg_match('/FL-(\d+)/', $last, $m)) {
            return 'FL-0001';
        }

        return 'FL-'.str_pad((string) ((int) $m[1] + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): FittingLabour
    {
        $labour = $this->resolveFitter($data['labourCode'] ?? null);
        $type = $data['type'] ?? '';
        if (! in_array($type, [FittingLabour::TYPE_WORK, FittingLabour::TYPE_CASH], true)) {
            throw new InvalidArgumentException('Invalid transaction type.');
        }

        $date = $data['transactionDate'] ?? now()->format('Y-m-d');

        if ($type === FittingLabour::TYPE_CASH) {
            return $this->storeCash($labour, $data, $date);
        }

        return $this->storeWork($labour, $data, $date);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(FittingLabour $txn, array $data): FittingLabour
    {
        $labour = $this->resolveFitter($data['labourCode'] ?? $txn->labour_code);
        $type = $data['type'] ?? $txn->txn_type;
        if ($type === FittingLabour::TYPE_SALE || $type === FittingLabour::TYPE_PURCHASE) {
            $type = FittingLabour::TYPE_WORK;
        }
        if (! in_array($type, [FittingLabour::TYPE_WORK, FittingLabour::TYPE_CASH], true)) {
            throw new InvalidArgumentException('Invalid transaction type.');
        }

        $date = $data['transactionDate']
            ?? optional($txn->txn_date)->format('Y-m-d')
            ?? now()->format('Y-m-d');

        return DB::transaction(function () use ($txn, $labour, $data, $type, $date) {
            $oldStock = $this->workStockImpactFromTxn($txn);

            $this->removeLedgerLink($txn);
            $txn->items()->delete();

            $txnNo = (string) $txn->txn_no;
            $newStock = null;

            if ($type === FittingLabour::TYPE_CASH) {
                [$amount, $entryType, $description, $ledgerSide, $label] = $this->parseCash($data);

                $txn->update([
                    'labour_id' => $labour->id,
                    'labour_code' => $labour->labourCode,
                    'labour_name' => $labour->labourName,
                    'txn_type' => FittingLabour::TYPE_CASH,
                    'entry_type' => $entryType,
                    'txn_date' => $date,
                    'total_amount' => $amount,
                    'remarks' => $description,
                    'status' => 'Active',
                ]);

                FittingLabourItem::create([
                    'fitting_labour_id' => $txn->id,
                    'txn_no' => $txnNo,
                    'product_code' => 'CASH',
                    'product_name' => $description,
                    'qty' => 1,
                    'rate' => $amount,
                    'amount' => $amount,
                ]);

                $ledgerId = $this->postLabourLedger(
                    $labour,
                    $date,
                    $amount,
                    $ledgerSide,
                    'Cash ('.$label.') — '.$description.' ['.$txnNo.']'
                );
                $txn->update(['labour_account_transaction_id' => $ledgerId]);
            } else {
                [$product, $workName, $qty, $rate, $amount] = $this->parseWork($data);

                $txn->update([
                    'labour_id' => $labour->id,
                    'labour_code' => $labour->labourCode,
                    'labour_name' => $labour->labourName,
                    'txn_type' => FittingLabour::TYPE_WORK,
                    'entry_type' => null,
                    'txn_date' => $date,
                    'total_amount' => $amount,
                    'remarks' => $data['remarks'] ?? null,
                    'status' => 'Active',
                ]);

                FittingLabourItem::create([
                    'fitting_labour_id' => $txn->id,
                    'txn_no' => $txnNo,
                    'product_code' => $product->productCode,
                    'product_name' => $product->productName,
                    'work_name' => $workName,
                    'variant_id' => $data['variant_id'] ?? null,
                    'variant_name' => $data['variant_name'] ?? null,
                    'variant_value' => $data['variant_value'] ?? null,
                    'qty' => $qty,
                    'rate' => $rate,
                    'amount' => $amount,
                ]);

                $ledgerId = $this->postLabourLedger(
                    $labour,
                    $date,
                    $amount,
                    'debit',
                    'Fitting Work — '.$product->productName.' / '.$workName.' ['.$txnNo.']'
                );
                $txn->update(['labour_account_transaction_id' => $ledgerId]);

                $newStock = [
                    'productCode' => $product->productCode,
                    'productName' => $product->productName,
                    'workName' => $workName,
                    'qty' => $qty,
                    'unitName' => FittingWorkStockService::resolveUnit(
                        (string) $product->productCode,
                        (string) ($product->unitPerPackName ?? '')
                    ),
                ];
            }

            FittingWorkStockService::replace($oldStock, $newStock);

            $this->syncLabourBalance($labour->id);
            FittingModuleCache::bumpLabour((string) $labour->labourCode);

            return $txn->fresh(['items']);
        });
    }

    public function delete(FittingLabour $txn): void
    {
        $labourId = (int) $txn->labour_id;
        $labourCode = (string) $txn->labour_code;

        DB::transaction(function () use ($txn) {
            $oldStock = $this->workStockImpactFromTxn($txn);
            FittingWorkStockService::replace($oldStock, null);

            $this->removeLedgerLink($txn);
            $txn->delete();
        });

        $this->syncLabourBalance($labourId);
        FittingModuleCache::bumpLabour($labourCode);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function storeCash(Labour $labour, array $data, string $date): FittingLabour
    {
        [$amount, $entryType, $description, $ledgerSide, $label] = $this->parseCash($data);

        return DB::transaction(function () use ($labour, $amount, $entryType, $description, $ledgerSide, $label, $date) {
            $txnNo = $this->nextTxnNo();

            $txn = FittingLabour::create([
                'txn_no' => $txnNo,
                'labour_id' => $labour->id,
                'labour_code' => $labour->labourCode,
                'labour_name' => $labour->labourName,
                'txn_type' => FittingLabour::TYPE_CASH,
                'entry_type' => $entryType,
                'txn_date' => $date,
                'total_amount' => $amount,
                'remarks' => $description,
                'status' => 'Active',
            ]);

            FittingLabourItem::create([
                'fitting_labour_id' => $txn->id,
                'txn_no' => $txnNo,
                'product_code' => 'CASH',
                'product_name' => $description,
                'qty' => 1,
                'rate' => $amount,
                'amount' => $amount,
            ]);

            $ledgerId = $this->postLabourLedger(
                $labour,
                $date,
                $amount,
                $ledgerSide,
                'Cash ('.$label.') — '.$description.' ['.$txnNo.']'
            );
            $txn->update(['labour_account_transaction_id' => $ledgerId]);

            $this->syncLabourBalance($labour->id);
            FittingModuleCache::bumpLabour((string) $labour->labourCode);

            return $txn->load('items');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function storeWork(Labour $labour, array $data, string $date): FittingLabour
    {
        [$product, $workName, $qty, $rate, $amount] = $this->parseWork($data);

        return DB::transaction(function () use ($labour, $product, $workName, $qty, $rate, $amount, $data, $date) {
            $txnNo = $this->nextTxnNo();

            $txn = FittingLabour::create([
                'txn_no' => $txnNo,
                'labour_id' => $labour->id,
                'labour_code' => $labour->labourCode,
                'labour_name' => $labour->labourName,
                'txn_type' => FittingLabour::TYPE_WORK,
                'txn_date' => $date,
                'total_amount' => $amount,
                'remarks' => $data['remarks'] ?? null,
                'status' => 'Active',
            ]);

            FittingLabourItem::create([
                'fitting_labour_id' => $txn->id,
                'txn_no' => $txnNo,
                'product_code' => $product->productCode,
                'product_name' => $product->productName,
                'work_name' => $workName,
                'variant_id' => $data['variant_id'] ?? null,
                'variant_name' => $data['variant_name'] ?? null,
                'variant_value' => $data['variant_value'] ?? null,
                'qty' => $qty,
                'rate' => $rate,
                'amount' => $amount,
            ]);

            $ledgerId = $this->postLabourLedger(
                $labour,
                $date,
                $amount,
                'debit',
                'Fitting Work — '.$product->productName.' / '.$workName.' ['.$txnNo.']'
            );
            $txn->update(['labour_account_transaction_id' => $ledgerId]);

            FittingWorkStockService::increase(
                (string) $product->productCode,
                (string) $product->productName,
                $workName,
                $qty,
                (string) ($product->unitPerPackName ?? '')
            );

            $this->syncLabourBalance($labour->id);
            FittingModuleCache::bumpLabour((string) $labour->labourCode);

            return $txn->load('items');
        });
    }

    /**
     * Stock impact of an existing Work (or legacy Sale/Purchase) entry.
     *
     * @return array{productCode: string, productName: string, workName: string, qty: float}|null
     */
    protected function workStockImpactFromTxn(FittingLabour $txn): ?array
    {
        if (! in_array($txn->txn_type, [
            FittingLabour::TYPE_WORK,
            FittingLabour::TYPE_PURCHASE,
            FittingLabour::TYPE_SALE,
        ], true)) {
            return null;
        }

        $item = $txn->relationLoaded('items')
            ? $txn->items->first()
            : $txn->items()->first();

        if (! $item) {
            return null;
        }

        $productCode = trim((string) ($item->product_code ?? ''));
        $workName = trim((string) ($item->work_name ?? ''));
        $qty = round((float) ($item->qty ?? 0), 2);

        if ($productCode === '' || $productCode === 'CASH' || $workName === '' || $qty <= 0) {
            return null;
        }

        return [
            'productCode' => $productCode,
            'productName' => (string) ($item->product_name ?? ''),
            'workName' => $workName,
            'qty' => $qty,
            'unitName' => FittingWorkStockService::resolveUnit($productCode),
        ];
    }

    protected function resolveFitter(?string $labourCode): Labour
    {
        $labour = Labour::query()->where('labourCode', $labourCode)->first();
        if (! $labour) {
            throw new InvalidArgumentException('Fitter not found.');
        }
        if (! str_contains(strtolower((string) $labour->labourCatagory), 'fitter')) {
            throw new InvalidArgumentException('Selected labour must be a fitter.');
        }

        return $labour;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: float, 1: string, 2: string, 3: string, 4: string}
     */
    protected function parseCash(array $data): array
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
        // Pay → credit (reduces payable); Receive → debit
        $ledgerSide = $entryType === 'receive' ? 'debit' : 'credit';
        $label = $entryType === 'receive' ? 'Receive' : 'Pay';

        return [$amount, $entryType, $description, $ledgerSide, $label];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: newProduct, 1: string, 2: float, 3: float, 4: float}
     */
    protected function parseWork(array $data): array
    {
        $product = newProduct::query()->where('productCode', $data['productCode'] ?? '')->first();
        if (! $product) {
            throw new InvalidArgumentException('Product not found.');
        }
        $workName = ucwords(strtolower(trim((string) ($data['workName'] ?? ''))));
        if ($workName === '') {
            throw new InvalidArgumentException('Please select Work.');
        }
        $qty = round((float) ($data['qty'] ?? 0), 2);
        $rate = round((float) ($data['rate'] ?? 0), 2);
        $amount = round($qty * $rate, 2);
        if ($qty <= 0 || $amount < 0) {
            throw new InvalidArgumentException('Invalid quantity or amount.');
        }

        return [$product, $workName, $qty, $rate, $amount];
    }

    protected function postLabourLedger(
        Labour $labour,
        string $date,
        float $amount,
        string $entryType,
        string $description
    ): int {
        $row = LabourAccountTransaction::create([
            'transactionDate' => $date,
            'labour_id' => $labour->id,
            'labourCode' => $labour->labourCode,
            'labourName' => $labour->labourName,
            'labourCatagory' => $labour->labourCatagory,
            'description' => $description,
            'bankAccount' => null,
            'amount' => $amount,
            'entryType' => $entryType,
            'isAdvance' => false,
        ]);

        return (int) $row->id;
    }

    protected function removeLedgerLink(FittingLabour $txn): void
    {
        if ($txn->labour_account_transaction_id) {
            LabourAccountTransaction::query()
                ->where('id', $txn->labour_account_transaction_id)
                ->delete();
            $txn->update(['labour_account_transaction_id' => null]);
        }
    }

    public function syncLabourBalance(int $labourId): void
    {
        $running = 0.0;
        $rows = LabourAccountTransaction::query()
            ->where('labour_id', $labourId)
            ->orderBy('transactionDate')
            ->orderBy('id')
            ->get(['entryType', 'amount']);

        foreach ($rows as $row) {
            $amount = (float) $row->amount;
            $running += $row->entryType === 'debit' ? $amount : -$amount;
        }

        Labour::query()->where('id', $labourId)->update(['balance' => round($running, 2)]);
    }
}
