<?php

namespace App\Support;

use App\Models\FittingLabour;
use App\Models\Labour;
use App\Models\newProduct;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * File-cache for Fitting Labour page (mirrors SupplierModuleCache).
 */
class FittingModuleCache
{
    public const TTL_CATALOG = 900;

    public const TTL_PARTY = 180;

    protected static function store()
    {
        return Cache::store('file');
    }

    protected static function key(string $suffix): string
    {
        return 'fitting_mod.'.config('app.env', 'local').'.'.$suffix;
    }

    public static function labourVersion(string $labourCode): int
    {
        return (int) self::store()->get(self::key('ver.'.$labourCode), 0);
    }

    public static function bumpLabour(string $labourCode): void
    {
        $code = trim($labourCode);
        if ($code === '') {
            return;
        }
        self::store()->put(self::key('ver.'.$code), self::labourVersion($code) + 1, now()->addDays(30));
    }

    public static function flushCatalog(): void
    {
        self::store()->forget(self::key('fitters_list'));
        self::store()->forget(self::key('products_catalog'));
        self::flushWorkStocks();
    }

    public static function flushWorkStocks(): void
    {
        self::store()->forget(self::key('work_stocks_list'));
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{productCode: string, productName: string, workName: string, qty: float}>
     */
    public static function workStocks(): Collection
    {
        return self::store()->remember(self::key('work_stocks_list'), self::TTL_CATALOG, function () {
            return FittingWorkStockService::listAll();
        });
    }

    /**
     * @template T
     * @param  callable(): T  $callback
     * @return T
     */
    public static function rememberLabour(string $labourCode, string $suffix, callable $callback, ?int $ttl = null): mixed
    {
        $ttl ??= self::TTL_PARTY;
        $key = self::key('labour.'.$labourCode.'.v'.self::labourVersion($labourCode).'.'.$suffix);

        return self::store()->remember($key, $ttl, $callback);
    }

    /**
     * @return Collection<int, Labour>
     */
    public static function fitters(): Collection
    {
        return self::store()->remember(self::key('fitters_list'), self::TTL_CATALOG, function () {
            return Labour::query()
                ->whereRaw('LOWER(labourCatagory) LIKE ?', ['%fitter%'])
                ->orderBy('labourName')
                ->get(['id', 'labourCode', 'labourName', 'labourAddress', 'labourCatagory', 'balance']);
        });
    }

    /**
     * @return array{products: Collection<int, newProduct>, productVariants: array<string, array<int, array<string, mixed>>>}
     */
    public static function productsCatalog(): array
    {
        return self::store()->remember(self::key('products_catalog'), self::TTL_CATALOG, function () {
            $products = newProduct::query()
                ->with('variants')
                ->orderBy('productName')
                ->get(['id', 'productCode', 'productName', 'companyName', 'unitPerPackName', 'piecesPerUnit', 'saleRate', 'costRate']);

            $productVariants = $products->mapWithKeys(function ($product) {
                return [
                    $product->productCode => $product->variants->map(function ($variant) {
                        return [
                            'id' => $variant->id,
                            'name' => $variant->variantName,
                            'value' => $variant->variantValue,
                            'additionalPrice' => (float) ($variant->additionalPrice ?? 0),
                            'additionalCost' => (float) ($variant->additionalCost ?? 0),
                        ];
                    })->values()->all(),
                ];
            })->all();

            return [
                'products' => $products,
                'productVariants' => $productVariants,
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public static function selectContext(string $labourCode): array
    {
        return self::rememberLabour($labourCode, 'select', function () use ($labourCode) {
            $labour = Labour::query()->where('labourCode', $labourCode)->first();
            if (! $labour || ! str_contains(strtolower((string) $labour->labourCatagory), 'fitter')) {
                return [
                    'is_fitter' => false,
                    'entries' => [],
                    'products' => [],
                    'balance' => 0,
                ];
            }

            $entriesPayload = self::buildEntries($labourCode);
            $products = self::buildProductsList();

            return [
                'is_fitter' => true,
                'balance' => (float) ($labour->balance ?? $entriesPayload['closing_balance']),
                'balance_label' => 'Labour Balance',
                'balance_type' => 'payable',
                'entries' => $entriesPayload['entries'],
                'closing_balance' => $entriesPayload['closing_balance'],
                'total_debit' => $entriesPayload['total_debit'],
                'total_credit' => $entriesPayload['total_credit'],
                'products' => $products,
            ];
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected static function buildProductsList(): array
    {
        return newProduct::query()
            ->orderBy('productName')
            ->get(['productCode', 'productName', 'companyName', 'saleRate', 'costRate'])
            ->map(function ($p) {
                $rate = (float) ($p->costRate ?: $p->saleRate ?: 0);

                return [
                    'productCode' => $p->productCode,
                    'productName' => $p->productName,
                    'companyName' => $p->companyName,
                    'saleRate' => (float) ($p->saleRate ?? 0),
                    'costRate' => (float) ($p->costRate ?? 0),
                    'displayRate' => $rate,
                    'rateLabel' => 'Labour Rate',
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected static function buildEntries(string $labourCode): array
    {
        $rows = FittingLabour::query()
            ->where('labour_code', $labourCode)
            ->where('status', 'Active')
            ->with(['items' => function ($q) {
                $q->select([
                    'id', 'fitting_labour_id', 'product_code', 'product_name', 'work_name',
                    'variant_id', 'variant_name', 'variant_value', 'qty', 'rate', 'amount',
                ])->orderBy('id');
            }])
            ->orderBy('txn_date')
            ->orderBy('id')
            ->limit(200)
            ->get([
                'id', 'txn_no', 'txn_type', 'entry_type', 'txn_date', 'total_amount', 'remarks',
            ]);

        $running = 0.0;
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $entries = [];

        foreach ($rows as $txn) {
            $item = $txn->items->first();
            $amount = round((float) ($txn->total_amount ?? $item->amount ?? 0), 2);
            $debit = 0.0;
            $credit = 0.0;

            if (in_array($txn->txn_type, [FittingLabour::TYPE_WORK, FittingLabour::TYPE_PURCHASE, FittingLabour::TYPE_SALE], true)) {
                // Work (and any brief Sale/Purchase leftovers) count as labour work debit
                $debit = $amount;
            } elseif ($txn->txn_type === FittingLabour::TYPE_CASH) {
                $et = strtolower((string) $txn->entry_type);
                if (in_array($et, ['receive', 'debit'], true)) {
                    $debit = $amount;
                } else {
                    $credit = $amount; // pay
                }
            }

            $totalDebit += $debit;
            $totalCredit += $credit;
            $running += $debit - $credit;

            $entries[] = [
                'id' => $txn->id,
                'type' => $txn->txn_type,
                'entryType' => $txn->entry_type,
                'txnNo' => $txn->txn_no,
                'date' => optional($txn->txn_date)->format('Y-m-d'),
                'productCode' => $item->product_code ?? '',
                'productName' => $item->product_name ?? '',
                'workName' => $item->work_name ?? '',
                'remarks' => $txn->remarks ?? '',
                'variantId' => $item->variant_id ?? null,
                'variantName' => $item->variant_name ?? '',
                'variantValue' => $item->variant_value ?? '',
                'variant' => trim(($item->variant_name ?? '').' '.($item->variant_value ?? '')),
                'qty' => (float) ($item->qty ?? 0),
                'rate' => (float) ($item->rate ?? 0),
                'amount' => $amount,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => round($running, 2),
            ];
        }

        return [
            'entries' => $entries,
            'closing_balance' => round($running, 2),
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
        ];
    }
}
