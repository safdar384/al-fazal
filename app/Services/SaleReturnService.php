<?php

namespace App\Services;

use App\Models\NewCustomer;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Support\GeneralLedger;
use App\Support\ListPage;
use App\Support\PartyBalance;
use App\Support\PartyLedger;
use App\Support\SaleInvoiceCalculator;
use App\Support\SaleStockService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SaleReturnService
{
    public function __construct(
        protected SaleInvoiceService $saleInvoices
    ) {}

    /** @var array{total_amount: float, credit_amount: float, cash_amount: float} */
    protected array $listAmounts = [
        'total_amount' => 0.0,
        'credit_amount' => 0.0,
        'cash_amount' => 0.0,
    ];

    /**
     * @return array{total_amount: float, credit_amount: float, cash_amount: float}
     */
    public function lastListAmounts(): array
    {
        return $this->listAmounts;
    }

    public function nextReturnNumber(): string
    {
        return $this->formatReturnNumber($this->maxReturnSequence() + 1);
    }

    public function allocateNextReturnNumber(): string
    {
        $next = $this->maxReturnSequence() + 1;

        do {
            $candidate = $this->formatReturnNumber($next);
            if (! SaleReturn::query()->where('returnNo', $candidate)->exists()) {
                return $candidate;
            }
            $next++;
        } while ($next < 99999);

        throw new \RuntimeException('Unable to allocate a new sale return number.');
    }

    protected function maxReturnSequence(): int
    {
        return (int) SaleReturn::query()
            ->where('returnNo', 'like', 'SR-%')
            ->pluck('returnNo')
            ->map(fn (string $no) => preg_match('/^SR-(\d+)$/i', $no, $m) ? (int) $m[1] : 0)
            ->max();
    }

    protected function formatReturnNumber(int $sequence): string
    {
        return 'SR-'.str_pad((string) max(1, $sequence), 5, '0', STR_PAD_LEFT);
    }

    /**
     * @return array{customers: Collection, products: Collection, nextReturnNo: string}
     */
    public function formData(): array
    {
        $data = $this->saleInvoices->formData();

        return [
            'customers' => $data['customers'],
            'products' => $data['products'],
            'nextReturnNo' => $this->nextReturnNumber(),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{return: SaleReturn, message: string}
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

                    $goodQty = 0.0;
                    $damageQty = 0.0;
                    $stockItems = [];

                    foreach ($computed['items'] as $item) {
                        if (empty($item['productCode'])) {
                            continue;
                        }
                        $qty = (float) ($item['qty'] ?? 0);
                        $condition = strtolower((string) ($item['condition'] ?? SaleReturnItem::CONDITION_GOOD));
                        if ($condition === SaleReturnItem::CONDITION_DAMAGE) {
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

                    if ($computed['noOfItems'] < 1 || $computed['grandTotal'] <= 0) {
                        throw new \InvalidArgumentException('Add at least one product with a valid amount.');
                    }

                    $customer = NewCustomer::query()->where('custCode', $validated['customerCode'])->first();
                    if (! $customer) {
                        throw new \InvalidArgumentException('Customer not found.');
                    }

                    $meta = PartyBalance::meta($validated['customerCode']);

                    $saleReturn = SaleReturn::create([
                        'returnNo' => $returnNo,
                        'returnDate' => $validated['returnDate'],
                        'customerCode' => $validated['customerCode'],
                        'customerName' => $validated['customerName'] ?: ($customer->custName ?? ''),
                        'city' => $validated['city'] ?? ($customer->custCity ?? ''),
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

                    foreach ($computed['items'] as $item) {
                        if (empty($item['productCode'])) {
                            continue;
                        }
                        $condition = strtolower((string) ($item['condition'] ?? SaleReturnItem::CONDITION_GOOD));
                        if ($condition !== SaleReturnItem::CONDITION_DAMAGE) {
                            $condition = SaleReturnItem::CONDITION_GOOD;
                        }

                        SaleReturnItem::create([
                            'sale_return_id' => $saleReturn->id,
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

                    // Full return amount → customer credit (good + damage).
                    PartyLedger::postSaleReturn(
                        $validated['customerCode'],
                        $returnNo,
                        $validated['returnDate'],
                        $computed['grandTotal'],
                        $customer->custArea
                    );

                    GeneralLedger::postSaleReturn(
                        $validated['returnDate'],
                        $returnNo,
                        $computed['grandTotal']
                    );

                    // Only Good lines restore sellable stock.
                    if ($stockItems !== []) {
                        SaleStockService::restore($stockItems);
                    }

                    return [
                        'return' => $saleReturn->fresh('items'),
                        'message' => 'Sale return '.$returnNo.' saved. Customer credited; stock restored for Good items only.',
                    ];
                });
            } catch (UniqueConstraintViolationException) {
                $attempts++;
                if ($attempts >= 3) {
                    throw new \RuntimeException('Could not allocate a unique return number. Please try again.');
                }
            }
        }

        throw new \RuntimeException('Could not save sale return.');
    }

    public function delete(SaleReturn $saleReturn): void
    {
        DB::transaction(function () use ($saleReturn) {
            $saleReturn->loadMissing('items');
            $returnNo = (string) $saleReturn->returnNo;
            $customerCode = (string) $saleReturn->customerCode;

            $stockItems = $saleReturn->items
                ->filter(fn (SaleReturnItem $item) => $item->isGood() && (float) $item->qty > 0)
                ->map(fn (SaleReturnItem $item) => [
                    'productCode' => (string) $item->productCode,
                    'variant_id' => $item->variant_id,
                    'qty' => (float) $item->qty,
                ])
                ->values()
                ->all();

            // Reverse Good stock that was restored when the return was saved.
            if ($stockItems !== []) {
                SaleStockService::deduct($stockItems);
            }

            GeneralLedger::voidByReference($returnNo);
            PartyLedger::deleteByInvoiceNumber($returnNo);
            SaleReturnItem::query()->where('sale_return_id', $saleReturn->id)->delete();
            $saleReturn->delete();

            if ($customerCode !== '') {
                PartyBalance::syncCustBalance($customerCode);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{return: SaleReturn, message: string}
     */
    public function update(SaleReturn $saleReturn, array $validated): array
    {
        return DB::transaction(function () use ($saleReturn, $validated) {
            $saleReturn->loadMissing('items');
            $returnNo = (string) $saleReturn->returnNo;
            $oldCustomerCode = (string) $saleReturn->customerCode;

            $oldStock = $saleReturn->items
                ->filter(fn (SaleReturnItem $item) => $item->isGood() && (float) $item->qty > 0)
                ->map(fn (SaleReturnItem $item) => [
                    'productCode' => (string) $item->productCode,
                    'variant_id' => $item->variant_id,
                    'qty' => (float) $item->qty,
                ])
                ->values()
                ->all();

            if ($oldStock !== []) {
                SaleStockService::deduct($oldStock);
            }

            GeneralLedger::voidByReference($returnNo);
            PartyLedger::deleteByInvoiceNumber($returnNo);
            SaleReturnItem::query()->where('sale_return_id', $saleReturn->id)->delete();

            $computed = SaleInvoiceCalculator::compute(
                $validated['items'],
                (float) ($validated['invoiceDiscount'] ?? 0)
            );

            $goodQty = 0.0;
            $damageQty = 0.0;
            $stockItems = [];

            foreach ($computed['items'] as $item) {
                if (empty($item['productCode'])) {
                    continue;
                }
                $qty = (float) ($item['qty'] ?? 0);
                $condition = strtolower((string) ($item['condition'] ?? SaleReturnItem::CONDITION_GOOD));
                if ($condition === SaleReturnItem::CONDITION_DAMAGE) {
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

            if ($computed['noOfItems'] < 1 || $computed['grandTotal'] <= 0) {
                throw new \InvalidArgumentException('Add at least one product with a valid amount.');
            }

            $customer = NewCustomer::query()->where('custCode', $validated['customerCode'])->first();
            if (! $customer) {
                throw new \InvalidArgumentException('Customer not found.');
            }

            $meta = PartyBalance::meta($validated['customerCode']);

            $saleReturn->update([
                'returnDate' => $validated['returnDate'],
                'customerCode' => $validated['customerCode'],
                'customerName' => $validated['customerName'] ?: ($customer->custName ?? ''),
                'city' => $validated['city'] ?? ($customer->custCity ?? ''),
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

            foreach ($computed['items'] as $item) {
                if (empty($item['productCode'])) {
                    continue;
                }
                $condition = strtolower((string) ($item['condition'] ?? SaleReturnItem::CONDITION_GOOD));
                if ($condition !== SaleReturnItem::CONDITION_DAMAGE) {
                    $condition = SaleReturnItem::CONDITION_GOOD;
                }

                SaleReturnItem::create([
                    'sale_return_id' => $saleReturn->id,
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

            PartyLedger::postSaleReturn(
                $validated['customerCode'],
                $returnNo,
                $validated['returnDate'],
                $computed['grandTotal'],
                $customer->custArea
            );

            GeneralLedger::postSaleReturn(
                $validated['returnDate'],
                $returnNo,
                $computed['grandTotal']
            );

            if ($stockItems !== []) {
                SaleStockService::restore($stockItems);
            }

            if ($oldCustomerCode !== '' && $oldCustomerCode !== (string) $validated['customerCode']) {
                PartyBalance::syncCustBalance($oldCustomerCode);
            }

            return [
                'return' => $saleReturn->fresh('items'),
                'message' => 'Sale return '.$returnNo.' updated.',
            ];
        });
    }

    /**
     * @return array{customers: Collection, products: Collection, nextReturnNo: string, editReturn: ?array}
     */
    public function editFormData(SaleReturn $saleReturn): array
    {
        $data = $this->formData();
        $saleReturn->loadMissing('items');

        $data['nextReturnNo'] = (string) $saleReturn->returnNo;
        $data['editReturn'] = [
            'id' => (int) $saleReturn->id,
            'returnNo' => (string) $saleReturn->returnNo,
            'returnDate' => $saleReturn->returnDate?->format('Y-m-d') ?? '',
            'customerCode' => (string) $saleReturn->customerCode,
            'customerName' => (string) $saleReturn->customerName,
            'city' => (string) ($saleReturn->city ?? ''),
            'originalInvoiceNo' => (string) ($saleReturn->originalInvoiceNo ?? ''),
            'comments' => (string) ($saleReturn->comments ?? ''),
            'invoiceDiscount' => (float) ($saleReturn->invoiceDiscount ?? 0),
            'items' => $saleReturn->items->map(fn (SaleReturnItem $item) => [
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

    public function paginateReturns(Request $request): LengthAwarePaginator
    {
        $all = $this->filteredReturns($request);
        $this->listAmounts = [
            'total_amount' => round((float) $all->sum('grandTotal'), 2),
            'credit_amount' => round((float) $all->sum('grandTotal'), 2),
            'cash_amount' => 0.0,
        ];

        return ListPage::paginate($request, $all);
    }

    public function listReturnsForPrint(Request $request): Collection
    {
        $all = $this->filteredReturns($request);
        $this->listAmounts = [
            'total_amount' => round((float) $all->sum('grandTotal'), 2),
            'credit_amount' => round((float) $all->sum('grandTotal'), 2),
            'cash_amount' => 0.0,
        ];

        return $all;
    }

    protected function filteredReturns(Request $request): Collection
    {
        $q = SaleReturn::query();

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

        $area = $request->query('area');
        if ($area) {
            $customerCodes = NewCustomer::query()->excludeProductSupplier()->where('custArea', $area)->pluck('custCode');
            if ($customerCodes->isEmpty()) {
                $q->whereRaw('1 = 0');
            } else {
                $q->whereIn('customerCode', $customerCodes);
            }
        }

        $customerCode = $request->query('customerCode');
        if ($customerCode) {
            $q->where('customerCode', $customerCode);
        }

        $customerTable = (new NewCustomer)->getTable();

        return $q
            ->leftJoin($customerTable.' as cust', 'sale_returns.customerCode', '=', 'cust.custCode')
            ->select([
                'sale_returns.id',
                'sale_returns.returnNo',
                'sale_returns.returnDate',
                'sale_returns.customerCode',
                'sale_returns.customerName',
                'sale_returns.city',
                'sale_returns.originalInvoiceNo',
                'sale_returns.noOfItems',
                'sale_returns.grandTotal',
                'sale_returns.status',
                'cust.custAddress as customerAddress',
                'cust.custCity as customerCity',
            ])
            ->orderByDesc('sale_returns.returnDate')
            ->orderByDesc('sale_returns.id')
            ->get()
            ->map(fn ($r) => $this->mapReturnForSummary($r))
            ->values();
    }

    /**
     * Shape a sale return like a sale invoice row for Sale Summary UI/print.
     */
    protected function mapReturnForSummary(object $r): object
    {
        $date = $r->returnDate;
        if ($date instanceof \DateTimeInterface) {
            $date = $date->format('Y-m-d');
        }

        return (object) [
            'id' => (int) $r->id,
            'invoiceNo' => (string) $r->returnNo,
            'orderNo' => $r->originalInvoiceNo ?: null,
            'invoiceDate' => (string) $date,
            'customerCode' => $r->customerCode,
            'customerName' => $r->customerName ?: '—',
            'city' => $r->city,
            'noOfItems' => (int) ($r->noOfItems ?? 0),
            'corten' => 0,
            'biltyNo' => null,
            'grandTotal' => round((float) ($r->grandTotal ?? 0), 2),
            'status' => $r->status,
            'sale_type' => 'return',
            'is_return' => true,
            'payment_status' => null,
            'paid_amount' => 0,
            'customerAddress' => $r->customerAddress ?? null,
            'customerCity' => $r->customerCity ?? ($r->city ?? null),
        ];
    }
}
