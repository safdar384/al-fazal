<?php

namespace App\Support;

/**
 * Standard sale invoice totals (matches front-end saleInvoice.blade.php).
 *
 * Line: amount = (qty × rate) × (1 − item_discount% / 100)
 * Invoice: grandTotal = sum(lines) − flat invoice discount (Rs.)
 */
class SaleInvoiceCalculator
{
    public static function lineAmount(float $qty, float $rate, float $discountPercent): float
    {
        $qty = max(0, $qty);
        $rate = max(0, $rate);
        $discountPercent = max(0, min(100, $discountPercent));

        return round($qty * $rate * (1 - $discountPercent / 100), 2);
    }

    /**
     * @param  array<int, array{qty: mixed, rate: mixed, discount?: mixed, amount?: mixed}>  $items
     * @return array{
     *   items: array<int, array<string, mixed>>,
     *   invoiceTotal: float,
     *   invoiceDiscount: float,
     *   grandTotal: float,
     *   revenueRatio: float,
     *   noOfItems: int
     * }
     */
    public static function compute(array $items, float $invoiceDiscountFlat = 0): array
    {
        $normalized = [];
        $invoiceTotal = 0.0;
        $noOfItems = 0;

        foreach ($items as $item) {
            $qty = (float) ($item['qty'] ?? 0);
            $rate = (float) ($item['rate'] ?? 0);
            $discount = (float) ($item['discount'] ?? 0);
            $amount = self::lineAmount($qty, $rate, $discount);

            $row = $item;
            $row['amount'] = $amount;
            $normalized[] = $row;

            if (! empty($item['productCode'])) {
                $noOfItems++;
            }
            $invoiceTotal += $amount;
        }

        $invoiceTotal = round($invoiceTotal, 2);
        $invoiceDiscount = round(max(0, min($invoiceDiscountFlat, $invoiceTotal)), 2);
        $grandTotal = round(max(0, $invoiceTotal - $invoiceDiscount), 2);
        $revenueRatio = $invoiceTotal > 0 ? ($grandTotal / $invoiceTotal) : 1.0;

        return [
            'items' => $normalized,
            'invoiceTotal' => $invoiceTotal,
            'invoiceDiscount' => $invoiceDiscount,
            'grandTotal' => $grandTotal,
            'revenueRatio' => $revenueRatio,
            'noOfItems' => $noOfItems,
        ];
    }
}
