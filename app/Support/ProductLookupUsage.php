<?php

namespace App\Support;

use App\Models\newProduct;

class ProductLookupUsage
{
    /**
     * Lowercased distinct values currently stored on products for a column.
     *
     * @return list<string>
     */
    public static function usedNames(string $column): array
    {
        self::assertColumn($column);

        return newProduct::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->pluck($column)
            ->map(fn ($name) => strtolower(trim((string) $name)))
            ->unique()
            ->values()
            ->all();
    }

    public static function isUsed(string $column, string $name): bool
    {
        self::assertColumn($column);

        return newProduct::query()
            ->whereRaw('LOWER('.$column.') = ?', [strtolower(trim($name))])
            ->exists();
    }

    private static function assertColumn(string $column): void
    {
        $allowed = [
            'companyName',
            'colourName',
            'pakingName',
            'unitPerPackName',
            'catagoryName',
        ];

        if (! in_array($column, $allowed, true)) {
            throw new \InvalidArgumentException("Unsupported product lookup column: {$column}");
        }
    }
}
