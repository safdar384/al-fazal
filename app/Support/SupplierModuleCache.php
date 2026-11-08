<?php

namespace App\Support;

use App\Models\BankAccount;
use App\Models\CustomerProductRate;
use App\Models\FittingWorkStock;
use App\Models\NewCustomer;
use App\Models\newProduct;
use App\Models\SupplierTransaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Cache for Supplier Transaction + Supplier Ledger reads.
 * Uses the file store (not DB cache) so reads do not hit MySQL on every request.
 * Party data uses a version bump so keys stay fresh after writes.
 */
class SupplierModuleCache
{
    /** Suppliers / products catalog */
    public const TTL_CATALOG = 900;

    /** Entries, ledger report, balance, rates */
    public const TTL_PARTY = 180;

    protected static function store()
    {
        return Cache::store('file');
    }

    protected static function key(string $suffix): string
    {
        return 'supplier_mod.'.config('app.env', 'local').'.'.$suffix;
    }

    public static function partyVersion(string $supplierCode): int
    {
        return (int) self::store()->get(self::key('ver.'.$supplierCode), 0);
    }

    /**
     * Call after supplier transaction create/delete (and rate changes for that supplier).
     */
    public static function bumpParty(string $supplierCode): void
    {
        $code = trim($supplierCode);
        if ($code === '') {
            return;
        }

        $verKey = self::key('ver.'.$code);
        self::store()->put($verKey, self::partyVersion($code) + 1, now()->addDays(30));

        // Payable totals on dashboard also change
        if (class_exists(DashboardService::class)) {
            DashboardService::flushCache();
        }
    }

    public static function flushCatalog(): void
    {
        self::store()->forget(self::key('suppliers_list'));
        self::store()->forget(self::key('suppliers_list_v2'));
        self::store()->forget(self::key('suppliers_list_v3'));
        self::store()->forget(self::key('suppliers_options'));
        self::store()->forget(self::key('suppliers_options_v2'));
        self::store()->forget(self::key('products_catalog'));
    }

    /**
     * @template T
     * @param  callable(): T  $callback
     * @return T
     */
    public static function rememberParty(string $supplierCode, string $suffix, callable $callback, ?int $ttl = null): mixed
    {
        $ttl ??= self::TTL_PARTY;
        $key = self::key('party.'.$supplierCode.'.v'.self::partyVersion($supplierCode).'.'.$suffix);

        return self::store()->remember($key, $ttl, $callback);
    }

    /**
     * One payload for supplier select: balance + entries + products (single HTTP + cache key).
     *
     * @return array<string, mixed>
     */
    public static function selectContext(string $supplierCode, string $type): array
    {
        $rateType = $type === 'Sale' ? 'Sale' : 'Purchase';

        return self::rememberParty($supplierCode, 'select.v5.'.$rateType, function () use ($supplierCode, $type, $rateType) {
            $meta = PartyBalance::meta($supplierCode);
            $entries = self::buildTransactionEntries($supplierCode, $meta);
            $products = self::buildSupplierProducts($supplierCode, $type, $rateType);

            return [
                'balance' => $meta['balance'],
                'balance_label' => $meta['balance_label'],
                'balance_type' => $meta['balance_type'],
                'is_supplier' => (bool) ($meta['is_supplier'] ?? true),
                'entries' => $entries['entries'],
                'opening_balance' => $entries['opening_balance'],
                'closing_balance' => $entries['closing_balance'],
                'total_debit' => $entries['total_debit'],
                'total_credit' => $entries['total_credit'],
                'products' => $products['products'],
                'source' => $products['source'],
                'type' => $type,
            ];
        });
    }

    /**
     * @return Collection<int, NewCustomer>
     */
    public static function suppliersForTransactionPage(): Collection
    {
        return self::store()->remember(self::key('suppliers_list_v3'), self::TTL_CATALOG, function () {
            // Supplier Transaction: Product Supplier category only
            return NewCustomer::query()
                ->productSuppliers()
                ->orderBy('custName', 'asc')
                ->get(['custCode', 'custName', 'bussnessName', 'custAddress', 'custArea', 'custCity', 'custCatagory']);
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
                ->get([
                    'id',
                    'productCode',
                    'productName',
                    'companyName',
                    'saleRate',
                    'costRate',
                    'stockQty',
                    'image',
                ]);

            $productVariants = $products->mapWithKeys(function ($product) {
                return [
                    $product->productCode => $product->variants->map(function ($variant) {
                        return [
                            'id' => $variant->id,
                            'name' => $variant->variantName,
                            'value' => $variant->variantValue,
                            'additionalPrice' => (float) ($variant->additionalPrice ?? 0),
                            'additionalCost' => (float) ($variant->additionalCost ?? 0),
                            'stockQty' => (float) ($variant->stockQty ?? 0),
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

    public static function supplierOptions(): Collection
    {
        return self::store()->remember(self::key('suppliers_options_v2'), self::TTL_CATALOG, function () {
            return PartyBalance::supplierOptions();
        });
    }

    /**
     * @return array<string, mixed>
     */
    public static function partyMeta(string $supplierCode): array
    {
        return self::rememberParty($supplierCode, 'meta', function () use ($supplierCode) {
            return PartyBalance::meta($supplierCode);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public static function transactionEntries(string $supplierCode): array
    {
        return self::rememberParty($supplierCode, 'entries.v4', function () use ($supplierCode) {
            return self::buildTransactionEntries($supplierCode);
        });
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, summary: array<string, mixed>}
     */
    public static function ledgerReport(
        string $supplierCode,
        string $fromDate,
        string $toDate,
        string $transactionType,
        SupplierLedgerReport $report
    ): array {
        $suffix = 'ledger.'.md5(implode('|', [$fromDate, $toDate, $transactionType]));

        return self::rememberParty($supplierCode, $suffix, function () use (
            $supplierCode,
            $fromDate,
            $toDate,
            $transactionType,
            $report
        ) {
            $built = $report->build($supplierCode, $fromDate, $toDate, $transactionType);

            return [
                'rows' => $built['rows'] instanceof Collection
                    ? $built['rows']->values()->all()
                    : $built['rows'],
                'summary' => $built['summary'],
            ];
        });
    }

    /**
     * @return array{products: array<int, array<string, mixed>>, source: string, type: string}
     */
    public static function supplierProducts(string $supplierCode, string $type): array
    {
        $rateType = $type === 'Sale' ? 'Sale' : 'Purchase';

        return self::rememberParty($supplierCode, 'products.v2.'.$rateType, function () use ($supplierCode, $type, $rateType) {
            return self::buildSupplierProducts($supplierCode, $type, $rateType);
        });
    }

    /**
     * @return array{products: array<int, array<string, mixed>>, source: string, type: string}
     */
    protected static function buildSupplierProducts(string $supplierCode, string $type, string $rateType): array
    {
        $specialRates = CustomerProductRate::query()
            ->where('customerCode', $supplierCode)
            ->where('status', 'Active')
            ->where('is_active', true)
            ->forRateType($rateType)
            ->get(['productCode', 'specialRate', 'variant_id', 'work_name']);

        if ($specialRates->isEmpty()) {
            return [
                'products' => [],
                'source' => 'supplier_rates',
                'type' => $type,
            ];
        }

        // Prefer product-only (no work) + base (null variant) rate for list display.
        // Still list products that only have work-level rates.
        $rateByProduct = [];
        foreach ($specialRates as $row) {
            $code = (string) $row->productCode;
            $rate = (float) $row->specialRate;
            if ($rate <= 0) {
                continue;
            }
            $isProductOnly = $row->work_name === null || trim((string) $row->work_name) === '';
            $isBaseVariant = $row->variant_id === null;

            if ($isProductOnly && $isBaseVariant) {
                $rateByProduct[$code] = $rate;
            } elseif ($isProductOnly && ! isset($rateByProduct[$code])) {
                $rateByProduct[$code] = $rate;
            } elseif (! isset($rateByProduct[$code])) {
                // Fallback so work-only rates still make the product selectable
                $rateByProduct[$code] = $rate;
            }
        }

        if ($rateByProduct === []) {
            return [
                'products' => [],
                'source' => 'supplier_rates',
                'type' => $type,
            ];
        }

        $products = newProduct::query()
            ->with('variants')
            ->whereIn('productCode', array_keys($rateByProduct))
            ->orderBy('productName')
            ->get(['productCode', 'productName', 'companyName', 'saleRate', 'costRate', 'stockQty', 'image'])
            ->map(function ($p) use ($rateByProduct, $rateType) {
                $savedRate = $rateByProduct[$p->productCode] ?? 0.0;
                if ($savedRate <= 0) {
                    return null;
                }

                $variants = $p->variants;
                $stock = (float) ($p->stockQty ?? 0);
                if ($variants->isNotEmpty()) {
                    $stock = (float) $variants->sum(fn ($v) => (float) ($v->stockQty ?? 0));
                }

                return [
                    'productCode' => $p->productCode,
                    'productName' => $p->productName,
                    'companyName' => $p->companyName,
                    'saleRate' => (float) ($p->saleRate ?? 0),
                    'costRate' => (float) ($p->costRate ?? 0),
                    'netRate' => $savedRate,
                    'displayRate' => $savedRate,
                    'rateLabel' => $rateType === 'Sale' ? 'Sale Rate' : 'Net Rate',
                    'hasNetRate' => true,
                    'stockQty' => $stock,
                    'hasVariants' => $variants->isNotEmpty(),
                    'image' => $p->image_url,
                ];
            })
            ->filter()
            ->values()
            ->all();

        return [
            'products' => $products,
            'source' => 'supplier_rates',
            'type' => $type,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * Works with Sale/Purchase net rate + current stock (party-cached rates, live stock).
     *
     * @return array<int, array{workName: string, qty: float, unitName: string, specialRate: float}>
     */
    public static function productWorksPayload(
        string $supplierCode,
        string $productCode,
        string $type
    ): array {
        if ($supplierCode === '' || $type === 'Cash') {
            return FittingWorkStockService::worksForProduct($productCode)->values()->all();
        }

        $rateType = $type === 'Sale' ? 'Sale' : 'Purchase';

        $ratedWorks = self::rememberParty($supplierCode, 'works.'.$productCode.'.'.$rateType, function () use (
            $supplierCode,
            $productCode,
            $rateType
        ) {
            return CustomerProductRate::query()
                ->where('customerCode', $supplierCode)
                ->where('productCode', $productCode)
                ->where('status', 'Active')
                ->where('is_active', true)
                ->whereNotNull('work_name')
                ->where('work_name', '!=', '')
                ->forRateType($rateType)
                ->orderBy('work_name')
                ->get(['work_name', 'specialRate'])
                ->map(fn ($row) => [
                    'workName' => trim((string) $row->work_name),
                    'specialRate' => (float) $row->specialRate,
                ])
                ->filter(fn ($row) => $row['workName'] !== '' && $row['specialRate'] > 0)
                ->unique(fn ($row) => strtolower($row['workName']))
                ->values()
                ->all();
        });

        if ($ratedWorks === []) {
            return [];
        }

        $unitName = FittingWorkStockService::resolveUnit($productCode);
        $stockByWork = FittingWorkStock::query()
            ->where('product_code', $productCode)
            ->get(['work_name', 'qty', 'unit_name'])
            ->keyBy(fn ($r) => strtolower((string) $r->work_name));

        return collect($ratedWorks)->map(function (array $row) use ($stockByWork, $unitName) {
            $key = strtolower($row['workName']);
            $stock = $stockByWork->get($key);

            return [
                'workName' => $row['workName'],
                'qty' => (float) ($stock->qty ?? 0),
                'unitName' => FittingWorkStockService::normalizeUnitLabel($stock->unit_name ?? $unitName),
                'specialRate' => (float) $row['specialRate'],
            ];
        })->values()->all();
    }

    /**
     * One round-trip: product rate (+ variants) and rated works with stock.
     *
     * @return array{ok: bool, status: int, payload: array<string, mixed>}
     */
    public static function productContextPayload(
        string $supplierCode,
        string $productCode,
        string $type,
        ?int $variantId,
        ?string $workName = null
    ): array {
        $rate = self::productRatePayload($supplierCode, $productCode, $type, $variantId, $workName);
        $works = self::productWorksPayload($supplierCode, $productCode, $type);

        $payload = $rate['payload'];
        $payload['works'] = $works;

        return [
            'ok' => (bool) ($rate['ok'] ?? false),
            'status' => (int) ($rate['status'] ?? 200),
            // Always 200 if works exist so UI can still show Work list when product-only rate is missing
            'http' => (! empty($works) || ($rate['ok'] ?? false)) ? 200 : (int) ($rate['status'] ?? 422),
            'payload' => $payload,
        ];
    }

    /**
     * Lightweight catalog for Net Rate modal (file-cached).
     *
     * @return array{products: array<int, array<string, mixed>>, productVariants: array<string, array<int, array<string, mixed>>>}
     */
    public static function productsCatalogJson(): array
    {
        $catalog = self::productsCatalog();

        return [
            'products' => $catalog['products']->map(function ($p) use ($catalog) {
                $variants = $catalog['productVariants'][$p->productCode] ?? [];
                $stock = (float) ($p->stockQty ?? 0);
                if (! empty($variants)) {
                    $stock = collect($variants)->sum(fn ($v) => (float) ($v['stockQty'] ?? 0));
                }

                return [
                    'productCode' => $p->productCode,
                    'productName' => $p->productName,
                    'companyName' => $p->companyName,
                    'saleRate' => (float) ($p->saleRate ?? 0),
                    'costRate' => (float) ($p->costRate ?? 0),
                    'stockQty' => $stock,
                    'hasVariants' => ! empty($variants),
                    'image' => $p->image_url,
                ];
            })->values()->all(),
            'productVariants' => $catalog['productVariants'],
        ];
    }

    public static function productRatePayload(
        string $supplierCode,
        string $productCode,
        string $type,
        ?int $variantId,
        ?string $workName = null
    ): array {
        $rateType = $type === 'Sale' ? 'Sale' : 'Purchase';
        $workKey = $workName !== null && trim($workName) !== ''
            ? preg_replace('/\s+/', '_', strtolower(trim($workName)))
            : 'nowork';
        $suffix = 'rate.'.$productCode.'.'.$rateType.'.'.($variantId ?: 'base').'.'.$workKey;

        return self::rememberParty($supplierCode, $suffix, function () use (
            $supplierCode,
            $productCode,
            $rateType,
            $variantId,
            $workName
        ) {
            $normalizedWork = ucwords(strtolower(trim((string) $workName)));
            if ($normalizedWork === '') {
                $normalizedWork = null;
            }

            $rateQuery = CustomerProductRate::query()
                ->where('customerCode', $supplierCode)
                ->where('productCode', $productCode)
                ->where('status', 'Active')
                ->where('is_active', true)
                ->forRateType($rateType);

            // Work selected → only that work's rate. No work → only product-level rate.
            if ($normalizedWork !== null) {
                $scoped = (clone $rateQuery)->where('work_name', $normalizedWork);
            } else {
                $scoped = (clone $rateQuery)->where(function ($w) {
                    $w->whereNull('work_name')->orWhere('work_name', '');
                });
            }

            if (! empty($variantId)) {
                $rateRow = (clone $scoped)->where('variant_id', $variantId)->first()
                    ?: (clone $scoped)->whereNull('variant_id')->first();
            } else {
                $rateRow = (clone $scoped)->whereNull('variant_id')->first()
                    ?: $scoped->first();
            }

            if (! $rateRow || (float) $rateRow->specialRate <= 0) {
                $label = $rateType === 'Sale' ? 'sale rate' : 'purchase net rate';
                $msg = $normalizedWork !== null
                    ? "No {$label} set for this supplier/product/work ({$normalizedWork}). Add Net Rate with Work first."
                    : "No {$label} set for this supplier/product (without work). Add Net Rate first.";

                return [
                    'ok' => false,
                    'status' => 422,
                    'payload' => [
                        'rate' => 0,
                        'productName' => '',
                        'hasNetRate' => false,
                        'workName' => $normalizedWork,
                        'variants' => [],
                        'message' => $msg,
                    ],
                ];
            }

            $product = newProduct::with('variants')->where('productCode', $productCode)->first();
            $variantRates = CustomerProductRate::query()
                ->where('customerCode', $supplierCode)
                ->where('productCode', $productCode)
                ->where('status', 'Active')
                ->where('is_active', true)
                ->whereNotNull('variant_id')
                ->forRateType($rateType)
                ->get(['variant_id', 'specialRate'])
                ->keyBy('variant_id');

            $variants = $product
                ? $product->variants->map(function ($v) use ($variantRates) {
                    $vr = $variantRates->get($v->id);

                    return [
                        'id' => $v->id,
                        'name' => $v->variantName,
                        'value' => $v->variantValue,
                        'specialRate' => $vr ? (float) $vr->specialRate : null,
                        'hasSpecialRate' => (bool) $vr,
                    ];
                })->filter(fn ($v) => $v['hasSpecialRate'])->values()->all()
                : [];

            return [
                'ok' => true,
                'status' => 200,
                'payload' => [
                    'rate' => (float) $rateRow->specialRate,
                    'productName' => $product->productName ?? '',
                    'hasNetRate' => true,
                    'workName' => $normalizedWork,
                    'rateLabel' => $rateType === 'Sale' ? 'Sale Rate' : 'Net Rate',
                    'variants' => $variants,
                ],
            ];
        });
    }

    /**
     * @param  array<string, mixed>|null  $meta
     * @return array<string, mixed>
     */
    protected static function buildTransactionEntries(string $code, ?array $meta = null): array
    {
        // Single query with first item columns (avoids N+1 items load)
        $rows = SupplierTransaction::query()
            ->where('supplier_code', $code)
            ->where('status', 'Active')
            ->with(['items' => function ($q) {
                $q->select([
                    'id',
                    'supplier_transaction_id',
                    'product_code',
                    'product_name',
                    'work_name',
                    'variant_id',
                    'variant_name',
                    'variant_value',
                    'qty',
                    'rate',
                    'amount',
                ])->orderBy('id');
            }])
            // Latest 200 for the screen, then chronological for running balance
            ->orderByDesc('txn_date')
            ->orderByDesc('id')
            ->limit(200)
            ->get([
                'id',
                'txn_no',
                'txn_type',
                'entry_type',
                'bank_account',
                'txn_date',
                'total_amount',
                'remarks',
            ])
            ->sortBy([
                ['txn_date', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        $meta ??= PartyBalance::meta($code);
        $balanceLabel = (string) ($meta['balance_label'] ?? 'Balance');

        $bankCodes = $rows->pluck('bank_account')->filter()->unique()->values()->all();
        $bankNames = $bankCodes === []
            ? collect()
            : BankAccount::query()
                ->whereIn('bankAccount', $bankCodes)
                ->pluck('bankName', 'bankAccount');

        $firstDate = optional($rows->first()?->txn_date)->format('Y-m-d');
        $openingSigned = $firstDate
            ? PartyBalance::ledgerSignedBalance($code, $firstDate)
            : PartyBalance::ledgerSignedBalance($code);
        $openingDisplay = round(-$openingSigned, 2);

        $runningSigned = $openingSigned;
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $entries = [];

        $entries[] = [
            'id' => null,
            'type' => 'Opening',
            'entryType' => null,
            'txnNo' => '-',
            'date' => $firstDate ?: now()->format('Y-m-d'),
            'productCode' => '',
            'productName' => 'Pre Balance',
            'workName' => '',
            'remarks' => 'Opening '.$balanceLabel,
            'bankAccount' => '',
            'bankName' => '',
            'variantId' => null,
            'variantName' => '',
            'variantValue' => '',
            'variant' => '',
            'qty' => 0,
            'rate' => 0,
            'amount' => 0,
            'debit' => 0.0,
            'credit' => 0.0,
            'balance' => $openingDisplay,
            'ledger_signed' => round($openingSigned, 2),
            'isOpening' => true,
        ];

        foreach ($rows as $txn) {
            $item = $txn->items->first();
            $amount = round((float) ($txn->total_amount ?? $item->amount ?? 0), 2);

            // Derive sides from txn (same rules as PartyLedger posts) — no extra ledger query
            $debit = 0.0;
            $credit = 0.0;
            if ($txn->txn_type === SupplierTransaction::TYPE_PURCHASE) {
                $credit = $amount;
            } elseif ($txn->txn_type === SupplierTransaction::TYPE_SALE) {
                $debit = $amount;
            } elseif ($txn->txn_type === SupplierTransaction::TYPE_CASH) {
                $et = strtolower((string) $txn->entry_type);
                if (in_array($et, ['receive', 'credit'], true)) {
                    $credit = $amount;
                } else {
                    $debit = $amount;
                }
            }

            $totalDebit += $debit;
            $totalCredit += $credit;
            $runningSigned += $debit - $credit;

            $bankCode = (string) ($txn->bank_account ?? '');
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
                'bankAccount' => $bankCode,
                'bankName' => $bankCode !== '' ? (string) ($bankNames[$bankCode] ?? $bankCode) : '',
                'variantId' => $item->variant_id ?? null,
                'variantName' => $item->variant_name ?? '',
                'variantValue' => $item->variant_value ?? '',
                'variant' => trim(($item->variant_name ?? '').' '.($item->variant_value ?? '')),
                'qty' => (float) ($item->qty ?? 0),
                'rate' => (float) ($item->rate ?? 0),
                'amount' => $amount,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => round(-$runningSigned, 2),
                'ledger_signed' => round($runningSigned, 2),
                'isOpening' => false,
            ];
        }

        // Newest first for the table; Pre Balance ends up as the last row
        $entries = array_reverse($entries);

        return [
            'entries' => $entries,
            'opening_balance' => $openingDisplay,
            'closing_balance' => round(-$runningSigned, 2),
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'balance' => $meta['balance'],
            'balance_label' => $meta['balance_label'],
            'balance_type' => $meta['balance_type'],
        ];
    }
}
