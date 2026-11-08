<?php

namespace App\Support;

use App\Models\FittingProductWork;
use App\Models\FittingWorkStock;
use App\Models\newProduct;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Work-wise stock per Product + Work (separate from product inventory).
 * Qty is stored in the product's own unit (Pieces / Dozen / etc.).
 */
class FittingWorkStockService
{
    /**
     * Increase stock when labour completes work.
     */
    public static function increase(
        string $productCode,
        string $productName,
        string $workName,
        float $qty,
        ?string $unitName = null
    ): void {
        $qty = round($qty, 2);
        if ($qty <= 0) {
            return;
        }

        [$productCode, $workName] = self::normalizeKeys($productCode, $workName);
        if (! self::tracksStock($workName)) {
            return;
        }
        $unitName = self::resolveUnit($productCode, $unitName);

        $row = FittingWorkStock::query()
            ->where('product_code', $productCode)
            ->where('work_name', $workName)
            ->lockForUpdate()
            ->first();

        if ($row) {
            $row->update([
                'product_name' => $productName !== '' ? $productName : $row->product_name,
                'unit_name' => $unitName,
                'qty' => round((float) $row->qty + $qty, 2),
            ]);
        } else {
            FittingWorkStock::create([
                'product_code' => $productCode,
                'product_name' => $productName,
                'work_name' => $workName,
                'unit_name' => $unitName,
                'qty' => $qty,
            ]);
        }

        FittingModuleCache::flushWorkStocks();
    }

    /**
     * Reverse stock previously added by a Work entry (edit/delete).
     */
    public static function decrease(string $productCode, string $workName, float $qty): void
    {
        $qty = round($qty, 2);
        if ($qty <= 0) {
            return;
        }

        [$productCode, $workName] = self::normalizeKeys($productCode, $workName);
        if (! self::tracksStock($workName)) {
            return;
        }

        $row = FittingWorkStock::query()
            ->where('product_code', $productCode)
            ->where('work_name', $workName)
            ->lockForUpdate()
            ->first();

        if (! $row) {
            return;
        }

        $newQty = round(max(0, (float) $row->qty - $qty), 2);
        $row->update(['qty' => $newQty]);

        FittingModuleCache::flushWorkStocks();
    }

    /**
     * Replace impact when editing: reverse old Work qty, then add new Work qty.
     *
     * @param  array{productCode?: string, productName?: string, workName?: string, qty?: mixed, unitName?: string|null}|null  $old
     * @param  array{productCode?: string, productName?: string, workName?: string, qty?: mixed, unitName?: string|null}|null  $new
     */
    public static function replace(?array $old, ?array $new): void
    {
        if ($old) {
            self::decrease(
                (string) ($old['productCode'] ?? ''),
                (string) ($old['workName'] ?? ''),
                (float) ($old['qty'] ?? 0)
            );
        }

        if ($new) {
            self::increase(
                (string) ($new['productCode'] ?? ''),
                (string) ($new['productName'] ?? ''),
                (string) ($new['workName'] ?? ''),
                (float) ($new['qty'] ?? 0),
                isset($new['unitName']) ? (string) $new['unitName'] : null
            );
        }
    }

    public static function qty(string $productCode, string $workName): float
    {
        [$productCode, $workName] = self::normalizeKeys($productCode, $workName);

        return (float) (FittingWorkStock::query()
            ->where('product_code', $productCode)
            ->where('work_name', $workName)
            ->value('qty') ?? 0);
    }

    public static function assertAvailable(string $productCode, string $workName, float $qty): void
    {
        $qty = round($qty, 2);
        if ($qty <= 0) {
            return;
        }

        $available = self::qty($productCode, $workName);
        if ($available + 0.0001 < $qty) {
            $unit = self::resolveUnit($productCode);
            throw new InvalidArgumentException(
                "Insufficient work stock for {$workName}. Available: {$available} {$unit}"
            );
        }
    }

    /**
     * Works for a product with current stock (for Supplier Transaction / Stock UI).
     *
     * @return Collection<int, array{workName: string, qty: float, unitName: string, rate: float|null}>
     */
    public static function worksForProduct(string $productCode): Collection
    {
        $productCode = trim($productCode);
        if ($productCode === '') {
            return collect();
        }

        $unitName = self::resolveUnit($productCode);
        $stockByWork = FittingWorkStock::query()
            ->where('product_code', $productCode)
            ->get(['work_name', 'qty', 'unit_name'])
            ->keyBy(fn ($r) => strtolower((string) $r->work_name));

        $named = FittingProductWork::query()
            ->where('product_code', $productCode)
            ->where('status', 'Active')
            ->orderBy('work_name')
            ->get(['work_name', 'rate']);

        $map = [];
        foreach ($named as $row) {
            $key = strtolower((string) $row->work_name);
            $stock = $stockByWork->get($key);
            $map[$key] = [
                'workName' => $row->work_name,
                'qty' => (float) ($stock->qty ?? 0),
                'unitName' => self::normalizeUnitLabel($stock->unit_name ?? $unitName),
                'rate' => (float) $row->rate,
            ];
        }

        foreach ($stockByWork as $key => $stock) {
            if (isset($map[$key])) {
                continue;
            }
            $map[$key] = [
                'workName' => $stock->work_name,
                'qty' => (float) $stock->qty,
                'unitName' => self::normalizeUnitLabel($stock->unit_name ?? $unitName),
                'rate' => null,
            ];
        }

        return collect(array_values($map))->sortBy('workName', SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    /**
     * Set absolute work-stock qty (Stock Management screen).
     */
    public static function setQty(
        string $productCode,
        string $productName,
        string $workName,
        float $qty,
        ?string $unitName = null
    ): FittingWorkStock {
        $qty = round(max(0, $qty), 2);
        [$productCode, $workName] = self::normalizeKeys($productCode, $workName);
        $unitName = self::resolveUnit($productCode, $unitName);

        $row = FittingWorkStock::query()->updateOrCreate(
            [
                'product_code' => $productCode,
                'work_name' => $workName,
            ],
            [
                'product_name' => $productName !== '' ? $productName : $productCode,
                'unit_name' => $unitName,
                'qty' => $qty,
            ]
        );

        FittingModuleCache::flushWorkStocks();

        return $row;
    }

    public static function deleteRow(string $productCode, string $workName): void
    {
        [$productCode, $workName] = self::normalizeKeys($productCode, $workName);
        FittingWorkStock::query()
            ->where('product_code', $productCode)
            ->where('work_name', $workName)
            ->delete();
        FittingModuleCache::flushWorkStocks();
    }

    /**
     * Normalize product unit for stock (Pieces / Dozen / custom).
     */
    public static function resolveUnit(string $productCode, ?string $fallback = null): string
    {
        $raw = trim((string) ($fallback ?? ''));
        if ($raw === '') {
            $raw = trim((string) (newProduct::query()
                ->where('productCode', $productCode)
                ->value('unitPerPackName') ?? ''));
        }

        return self::normalizeUnitLabel($raw);
    }

    public static function normalizeUnitLabel(?string $unit): string
    {
        $unit = trim((string) $unit);
        if ($unit === '') {
            return 'Pieces';
        }

        $lower = strtolower($unit);
        if (str_contains($lower, 'dozen')) {
            return 'Dozen';
        }
        // Pieces / Piece / Pecies (common typo)
        if (str_contains($lower, 'piece') || str_contains($lower, 'pecies') || str_contains($lower, 'pcs')) {
            return 'Pieces';
        }

        return ucwords($lower);
    }

    /**
     * @return Collection<int, array{productCode: string, productName: string, workName: string, unitName: string, qty: float}>
     */
    public static function listAll(): Collection
    {
        return FittingWorkStock::query()
            ->orderBy('product_name')
            ->orderBy('work_name')
            ->get(['product_code', 'product_name', 'work_name', 'unit_name', 'qty'])
            ->map(fn (FittingWorkStock $row) => [
                'productCode' => $row->product_code,
                'productName' => $row->product_name ?: $row->product_code,
                'workName' => $row->work_name,
                'unitName' => self::normalizeUnitLabel($row->unit_name),
                'qty' => (float) $row->qty,
            ])
            ->values();
    }

    public static function tracksStock(string $workName): bool
    {
        return strtolower(trim($workName)) === 'molding';
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function normalizeKeys(string $productCode, string $workName): array
    {
        return [trim($productCode), trim($workName)];
    }
}
