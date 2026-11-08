<?php

namespace App\Support;

use App\Models\newProduct;
use App\Models\ProductVariant;
use InvalidArgumentException;

class SaleStockService
{
    public static function enabled(): bool
    {
        return (bool) config('sales.inventory_enabled', true);
    }

    /**
     * @param  array<int, array{productCode: string, variant_id?: int|null, qty: mixed}>  $items
     */
    public static function assertAvailable(array $items): void
    {
        if (! self::enabled()) {
            return;
        }

        foreach ($items as $item) {
            $qty = (float) ($item['qty'] ?? 0);
            if ($qty <= 0) {
                continue;
            }

            $productCode = (string) ($item['productCode'] ?? '');
            $variantId = $item['variant_id'] ?? null;

            if ($variantId) {
                $variant = ProductVariant::query()
                    ->where('id', $variantId)
                    ->where('productCode', $productCode)
                    ->first();

                if (! $variant) {
                    throw new InvalidArgumentException("Variant not found for product {$productCode}.");
                }

                if ((float) $variant->stockQty < $qty) {
                    throw new InvalidArgumentException(
                        "Insufficient stock for {$productCode} ({$variant->variantName}: {$variant->variantValue}). Available: {$variant->stockQty}"
                    );
                }

                continue;
            }

            $product = newProduct::query()->where('productCode', $productCode)->first();
            if (! $product) {
                throw new InvalidArgumentException("Product {$productCode} not found.");
            }

            if ((float) $product->stockQty < $qty) {
                throw new InvalidArgumentException(
                    "Insufficient stock for {$productCode}. Available: {$product->stockQty}"
                );
            }
        }
    }

    /**
     * @param  array<int, array{productCode: string, variant_id?: int|null, qty: mixed}>  $items
     */
    public static function deduct(array $items): void
    {
        if (! self::enabled()) {
            return;
        }

        foreach ($items as $item) {
            $qty = (float) ($item['qty'] ?? 0);
            if ($qty <= 0) {
                continue;
            }

            $productCode = (string) ($item['productCode'] ?? '');
            $variantId = $item['variant_id'] ?? null;

            if ($variantId) {
                $variant = ProductVariant::query()
                    ->where('id', $variantId)
                    ->where('productCode', $productCode)
                    ->lockForUpdate()
                    ->first();

                if ($variant) {
                    $variant->update([
                        'stockQty' => max(0, (float) $variant->stockQty - $qty),
                    ]);
                }

                continue;
            }

            $product = newProduct::query()->where('productCode', $productCode)->lockForUpdate()->first();
            if ($product) {
                $product->update([
                    'stockQty' => max(0, (float) $product->stockQty - $qty),
                ]);
            }
        }
    }

    /**
     * @param  array<int, array{productCode: string, variant_id?: int|null, qty: mixed}>  $items
     */
    public static function restore(array $items): void
    {
        if (! self::enabled()) {
            return;
        }

        foreach ($items as $item) {
            $qty = (float) ($item['qty'] ?? 0);
            if ($qty <= 0) {
                continue;
            }

            $productCode = (string) ($item['productCode'] ?? '');
            $variantId = $item['variant_id'] ?? null;

            if ($variantId) {
                $variant = ProductVariant::query()
                    ->where('id', $variantId)
                    ->where('productCode', $productCode)
                    ->first();

                if ($variant) {
                    $variant->update([
                        'stockQty' => (float) $variant->stockQty + $qty,
                    ]);
                }

                continue;
            }

            $product = newProduct::query()->where('productCode', $productCode)->first();
            if ($product) {
                $product->update([
                    'stockQty' => (float) $product->stockQty + $qty,
                ]);
            }
        }
    }
}
