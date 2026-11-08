<?php

namespace App\Services;

use App\Models\NewCustomer;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Support\GeneralLedger;
use App\Support\ListPage;
use App\Support\PartyBalance;
use App\Support\PartyLedger;
use App\Support\PurchaseStockService;
use App\Support\SaleInvoiceCalculator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseReturnService
{
    public function __construct(
        protected PurchaseInvoiceService $purchases
    ) {}

    public function nextReturnNumber(): string
    {
        return $this->formatReturnNumber($this->maxReturnSequence() + 1);
    }

    public function allocateNextReturnNumber(): string
    {
        $next = $this->maxReturnSequence() + 1;

        do {
            $candidate = $this->formatReturnNumber($next);
            if (! PurchaseReturn::query()->where('returnNo', $candidate)->exists()) {
                return $candidate;
            }
            $next++;
        } while ($next < 99999);

        throw new \RuntimeException('Unable to allocate a new purchase return number.');
    }

    protected function maxReturnSequence(): int
    {
        return (int) PurchaseReturn::query()
            ->where('returnNo', 'like', 'PR-%')
            ->pluck('returnNo')
            ->map(fn (string $no) => preg_match('/^PR-(\d+)$/i', $no, $m) ? (int) $m[1] : 0)
            ->max();
    }

    protected function formatReturnNumber(int $sequence): string
    {
        return 'PR-'.str_pad((string) max(1, $sequence), 5, '0', STR_PAD_LEFT);
    }

    /**
     * @return array{suppliers: Collection, products: Collection, nextReturnNo: string}
     */
    public function formData(): array
    {
        $data = $this->purchases->formData();

        return [
            'suppliers' => $data['suppliers'],
            'products' => $data['products'],
            'nextReturnNo' => $this->nextReturnNumber(),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{return: PurchaseReturn, message: string}
     */
    public function store(array $validated): array
    {
        $attempts = 0;

        while ($attempts < 3) {
            try {
                return DB::transaction(function () use ($validated) {
                    $returnNo = $this->allocateNextReturnNumber();
                    $computed = SaleInvoiceCalculator::compute(
                        $validated['items'],
                        (float) ($validated['invoiceDiscount'] ?? 0)
                    );

                    [$goodQty, $damageQty, $stockItems] = $this->splitStockItems($computed['items']);

                    if ($computed['noOfItems'] < 1 || $computed['grandTotal'] <= 0) {
                        throw new \InvalidArgumentException('Add at least one product with a valid amount.');
                    }

                    $supplier = NewCustomer::query()->where('custCode', $validated['supplierCode'])->first();
                    if (! $supplier) {
                        throw new \InvalidArgumentException('Supplier not found.');
                    }

                    $meta = PartyBalance::meta($validated['supplierCode']);

                    $purchaseReturn = PurchaseReturn::create([
                        'returnNo' => $returnNo,
                        'returnDate' => $validated['returnDate'],
                        'supplierCode' => $validated['supplierCode'],
                        'supplierName' => $validated['supplierName'] ?: ($supplier->custName ?? ''),
                        'city' => $validated['city'] ?? ($supplier->custCity ?? ''),
                        'balance' => $meta['balance'] ?? 0,
                        'originalInvoiceNo' => $validated['originalInvoiceNo'] ?? null,
                        'comments' => $validated['comments'] ?? '',
                        'noOfItems' => $computed['noOfItems'],
                        'invoiceTotal' => $computed['invoiceTotal'],
                        'invoiceDiscount' => $computed['invoiceDiscount'],
                        'grandTotal' => $computed['grandTotal'],
                        'goodQtyTotal' => round($goodQty, 2),
                        'damageQtyTotal' => round($damageQty, 2),
                        'status' => 'Active',
                    ]);

                    $this->createItems($purchaseReturn, $returnNo, $computed['items']);

                    PartyLedger::postPurchaseReturn(
                        $validated['supplierCode'],
                        $returnNo,
                        $validated['returnDate'],
                        $computed['grandTotal'],
                        $supplier->custArea
                    );

                    GeneralLedger::postPurchaseReturn(
                        $validated['returnDate'],
                        $returnNo,
                        $computed['grandTotal']
                    );

                    // Good lines leave warehouse (return to supplier).
                    if ($stockItems !== []) {
                        PurchaseStockService::reverse($stockItems);
                    }

                    return [
                        'return' => $purchaseReturn->fresh('items'),
                        'message' => 'Purchase return '.$returnNo.' saved. Supplier debited; stock reduced for Good items only.',
                    ];
                });
            } catch (UniqueConstraintViolationException) {
                $attempts++;
                if ($attempts >= 3) {
                    throw new \RuntimeException('Could not allocate a unique return number. Please try again.');
                }
            }
        }

        throw new \RuntimeException('Could not save purchase return.');
    }

    public function delete(PurchaseReturn $purchaseReturn): void
    {
        DB::transaction(function () use ($purchaseReturn) {
            $purchaseReturn->loadMissing('items');
            $returnNo = (string) $purchaseReturn->returnNo;
            $supplierCode = (string) $purchaseReturn->supplierCode;

            $stockItems = $purchaseReturn->items
                ->filter(fn (PurchaseReturnItem $item) => $item->isGood() && (float) $item->qty > 0)
                ->map(fn (PurchaseReturnItem $item) => [
                    'productCode' => (string) $item->productCode,
                    'variant_id' => $item->variant_id,
                    'qty' => (float) $item->qty,
                ])
                ->values()
                ->all();

            // Restore stock that was reduced when the return was saved.
            if ($stockItems !== []) {
                PurchaseStockService::receive($stockItems);
            }

            GeneralLedger::voidByReference($returnNo);
            PartyLedger::deleteByInvoiceNumber($returnNo);
            PurchaseReturnItem::query()->where('purchase_return_id', $purchaseReturn->id)->delete();
            $purchaseReturn->delete();

            if ($supplierCode !== '') {
                PartyBalance::syncCustBalance($supplierCode);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{return: PurchaseReturn, message: string}
     */
    public function update(PurchaseReturn $purchaseReturn, array $validated): array
    {
        return DB::transaction(function () use ($purchaseReturn, $validated) {
            $purchaseReturn->loadMissing('items');
            $returnNo = (string) $purchaseReturn->returnNo;
            $oldSupplierCode = (string) $purchaseReturn->supplierCode;

            $oldStock = $purchaseReturn->items
                ->filter(fn (PurchaseReturnItem $item) => $item->isGood() && (float) $item->qty > 0)
                ->map(fn (PurchaseReturnItem $item) => [
                    'productCode' => (string) $item->productCode,
                    'variant_id' => $item->variant_id,
                    'qty' => (float) $item->qty,
                ])
                ->values()
                ->all();

            if ($oldStock !== []) {
                PurchaseStockService::receive($oldStock);
            }

            GeneralLedger::voidByReference($returnNo);
            PartyLedger::deleteByInvoiceNumber($returnNo);
            PurchaseReturnItem::query()->where('purchase_return_id', $purchaseReturn->id)->delete();

            $computed = SaleInvoiceCalculator::compute(
                $validated['items'],
                (float) ($validated['invoiceDiscount'] ?? 0)
            );

            [$goodQty, $damageQty, $stockItems] = $this->splitStockItems($computed['items']);

            if ($computed['noOfItems'] < 1 || $computed['grandTotal'] <= 0) {
                throw new \InvalidArgumentException('Add at least one product with a valid amount.');
            }

            $supplier = NewCustomer::query()->where('custCode', $validated['supplierCode'])->first();
            if (! $supplier) {
                throw new \InvalidArgumentException('Supplier not found.');
            }

            $meta = PartyBalance::meta($validated['supplierCode']);

            $purchaseReturn->update([
                'returnDate' => $validated['returnDate'],
                'supplierCode' => $validated['supplierCode'],
                'supplierName' => $validated['supplierName'] ?: ($supplier->custName ?? ''),
                'city' => $validated['city'] ?? ($supplier->custCity ?? ''),
                'balance' => $meta['balance'] ?? 0,
                'originalInvoiceNo' => $validated['originalInvoiceNo'] ?? null,
                'comments' => $validated['comments'] ?? '',
                'noOfItems' => $computed['noOfItems'],
                'invoiceTotal' => $computed['invoiceTotal'],
                'invoiceDiscount' => $computed['invoiceDiscount'],
                'grandTotal' => $computed['grandTotal'],
                'goodQtyTotal' => round($goodQty, 2),
                'damageQtyTotal' => round($damageQty, 2),
                'status' => 'Active',
            ]);

            $this->createItems($purchaseReturn, $returnNo, $computed['items']);

            PartyLedger::postPurchaseReturn(
                $validated['supplierCode'],
                $returnNo,
                $validated['returnDate'],
                $computed['grandTotal'],
                $supplier->custArea
            );

            GeneralLedger::postPurchaseReturn(
                $validated['returnDate'],
                $returnNo,
                $computed['grandTotal']
            );

            if ($stockItems !== []) {
                PurchaseStockService::reverse($stockItems);
            }

            if ($oldSupplierCode !== '' && $oldSupplierCode !== (string) $validated['supplierCode']) {
                PartyBalance::syncCustBalance($oldSupplierCode);
            }

            return [
                'return' => $purchaseReturn->fresh('items'),
                'message' => 'Purchase return '.$returnNo.' updated.',
            ];
        });
    }

    /**
     * @return array{suppliers: Collection, products: Collection, nextReturnNo: string, editReturn: array}
     */
    public function editFormData(PurchaseReturn $purchaseReturn): array
    {
        $data = $this->formData();
        $purchaseReturn->loadMissing('items');

        $data['nextReturnNo'] = (string) $purchaseReturn->returnNo;
        $data['editReturn'] = [
            'id' => (int) $purchaseReturn->id,
            'returnNo' => (string) $purchaseReturn->returnNo,
            'returnDate' => $purchaseReturn->returnDate?->format('Y-m-d') ?? '',
            'supplierCode' => (string) $purchaseReturn->supplierCode,
            'supplierName' => (string) $purchaseReturn->supplierName,
            'city' => (string) ($purchaseReturn->city ?? ''),
            'originalInvoiceNo' => (string) ($purchaseReturn->originalInvoiceNo ?? ''),
            'comments' => (string) ($purchaseReturn->comments ?? ''),
            'invoiceDiscount' => (float) ($purchaseReturn->invoiceDiscount ?? 0),
            'items' => $purchaseReturn->items->map(fn (PurchaseReturnItem $item) => [
                'productCode' => (string) $item->productCode,
                'productName' => (string) $item->productName,
                'variant_id' => $item->variant_id ? (int) $item->variant_id : null,
                'variant_name' => $item->variant_name,
                'variant_value' => $item->variant_value,
                'variant_added_price' => (float) ($item->variant_added_price ?? 0),
                'qty' => (float) $item->qty,
                'rate' => (float) $item->rate,
                'cost_rate' => (float) ($item->cost_rate ?? 0),
                'discount' => (float) ($item->discount ?? 0),
                'amount' => (float) $item->amount,
                'condition' => strtolower((string) ($item->condition ?? 'good')),
            ])->values()->all(),
        ];

        return $data;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{0: float, 1: float, 2: list<array{productCode: string, variant_id: mixed, qty: float}>}
     */
    protected function splitStockItems(array $items): array
    {
        $goodQty = 0.0;
        $damageQty = 0.0;
        $stockItems = [];

        foreach ($items as $item) {
            if (empty($item['productCode'])) {
                continue;
            }
            $qty = (float) ($item['qty'] ?? 0);
            $condition = strtolower((string) ($item['condition'] ?? PurchaseReturnItem::CONDITION_GOOD));
            if ($condition === PurchaseReturnItem::CONDITION_DAMAGE) {
                $damageQty += $qty;
            } else {
                $goodQty += $qty;
                $stockItems[] = [
                    'productCode' => (string) $item['productCode'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'qty' => $qty,
                ];
            }
        }

        return [$goodQty, $damageQty, $stockItems];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function createItems(PurchaseReturn $purchaseReturn, string $returnNo, array $items): void
    {
        foreach ($items as $item) {
            if (empty($item['productCode'])) {
                continue;
            }
            $condition = strtolower((string) ($item['condition'] ?? PurchaseReturnItem::CONDITION_GOOD));
            if ($condition !== PurchaseReturnItem::CONDITION_DAMAGE) {
                $condition = PurchaseReturnItem::CONDITION_GOOD;
            }

            PurchaseReturnItem::create([
                'purchase_return_id' => $purchaseReturn->id,
                'returnNo' => $returnNo,
                'productCode' => $item['productCode'],
                'productName' => $item['productName'] ?? '',
                'variant_id' => $item['variant_id'] ?? null,
                'variant_name' => $item['variant_name'] ?? null,
                'variant_value' => $item['variant_value'] ?? null,
                'variant_added_price' => (float) ($item['variant_added_price'] ?? 0),
                'qty' => (float) ($item['qty'] ?? 0),
                'rate' => (float) ($item['rate'] ?? 0),
                'cost_rate' => (float) ($item['cost_rate'] ?? 0),
                'discount' => (float) ($item['discount'] ?? 0),
                'amount' => (float) ($item['amount'] ?? 0),
                'condition' => $condition,
            ]);
        }
    }

    public function paginateReturns(Request $request): LengthAwarePaginator
    {
        return ListPage::paginate($request, $this->filteredReturns($request, false));
    }

    public function listReturnsForPrint(Request $request): Collection
    {
        return $this->filteredReturns($request, true);
    }

    protected function filteredReturns(Request $request, bool $withItems = false): Collection
    {
        $q = PurchaseReturn::query();
        if ($withItems) {
            $q->with('items');
        }

        $fromDate = trim((string) $request->query('fromDate', ''));
        $toDate = trim((string) $request->query('toDate', ''));
        if ($fromDate === '' && $toDate === '') {
            $fromDate = now()->startOfMonth()->toDateString();
            $toDate = now()->toDateString();
        }
        if ($fromDate !== '') {
            $q->whereDate('returnDate', '>=', $fromDate);
        }
        if ($toDate !== '') {
            $q->whereDate('returnDate', '<=', $toDate);
        }

        $supplierCode = $request->query('supplierCode');
        if ($supplierCode) {
            $q->where('supplierCode', $supplierCode);
        }

        if ($search = trim((string) $request->query('search', ''))) {
            $like = '%'.$search.'%';
            $q->where(function ($w) use ($like) {
                $w->where('returnNo', 'like', $like)
                    ->orWhere('supplierName', 'like', $like)
                    ->orWhere('originalInvoiceNo', 'like', $like);
            });
        }

        return $q->orderByDesc('returnDate')
            ->orderByDesc('id')
            ->get()
            ->map(fn (PurchaseReturn $r) => $this->mapReturnForSummary($r, $withItems))
            ->values();
    }

    protected function mapReturnForSummary(PurchaseReturn $r, bool $withItems = false): object
    {
        $date = $r->returnDate;
        if ($date instanceof \DateTimeInterface) {
            $date = $date->format('Y-m-d');
        }

        $items = $withItems
            ? $r->items->map(fn (PurchaseReturnItem $item) => (object) [
                'qty' => (float) $item->qty,
                'productCode' => $item->productCode,
                'productName' => $item->productName,
            ])
            : collect();

        return (object) [
            'id' => (int) $r->id,
            'invoiceNo' => (string) $r->returnNo,
            'invoiceDate' => (string) $date,
            'supplierCode' => (string) $r->supplierCode,
            'supplierName' => (string) ($r->supplierName ?: '—'),
            'city' => (string) ($r->city ?? ''),
            'noOfItems' => (int) ($r->noOfItems ?? 0),
            'grandTotal' => round((float) ($r->grandTotal ?? 0), 2),
            'paid_amount' => 0.0,
            'payment_status' => 'return',
            'status' => (string) ($r->status ?? 'Active'),
            'purchase_type' => 'return',
            'is_return' => true,
            'items' => $items,
        ];
    }
}
