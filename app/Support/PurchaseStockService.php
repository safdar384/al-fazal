<?php

namespace App\Support;

use App\Models\newProduct;
use App\Models\ProductVariant;

/**
 * Purchase stock: increase on receive (opposite of sale deduction).
 */
class PurchaseStockService
{
    public static function enabled(): bool
    {
        return (bool) config('purchases.inventory_enabled', true);
    }

    /**
     * @param  array<int, array{productCode: string, variant_id?: int|null, qty: mixed}>  $items
     */
    public static function receive(array $items): void
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
                    $variant->update(['stockQty' => (float) $variant->stockQty + $qty]);
                }

                continue;
            }

            $product = newProduct::query()->where('productCode', $productCode)->lockForUpdate()->first();
            if ($product) {
                $product->update(['stockQty' => (float) $product->stockQty + $qty]);
            }
        }
    }

    /**
     * Reverse stock added by a purchase (delete/update).
     *
     * @param  array<int, array{productCode: string, variant_id?: int|null, qty: mixed}>  $items
     */
    public static function reverse(array $items): void
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
                    $variant->update(['stockQty' => max(0, (float) $variant->stockQty - $qty)]);
                }

                continue;
            }

            $product = newProduct::query()->where('productCode', $productCode)->first();
            if ($product) {
                $product->update(['stockQty' => max(0, (float) $product->stockQty - $qty)]);
            }
        }
    }
}
