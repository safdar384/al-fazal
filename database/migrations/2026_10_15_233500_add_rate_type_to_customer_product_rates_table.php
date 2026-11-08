<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hasColumn = DB::selectOne("
            SELECT COUNT(*) AS c
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'customer_product_rates'
              AND COLUMN_NAME = 'rateType'
        ");

        if (! $hasColumn || (int) $hasColumn->c === 0) {
            DB::statement("ALTER TABLE `customer_product_rates` ADD COLUMN `rateType` VARCHAR(20) NOT NULL DEFAULT 'Sale' AFTER `specialRate`");
        }

        // Backfill suppliers → Purchase
        $customerTable = null;
        foreach (['newcustomers', 'customers', 'new_customers'] as $table) {
            $exists = DB::selectOne("
                SELECT COUNT(*) AS c
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
            ", [$table]);
            if ($exists && (int) $exists->c > 0) {
                $customerTable = $table;
                break;
            }
        }

        if ($customerTable) {
            DB::statement("
                UPDATE customer_product_rates cpr
                INNER JOIN `{$customerTable}` c ON c.custCode = cpr.customerCode
                SET cpr.rateType = 'Purchase'
                WHERE LOWER(c.custCatagory) LIKE '%supplier%'
            ");
        }

        DB::statement("UPDATE customer_product_rates SET rateType = 'Sale' WHERE rateType IS NULL OR rateType = ''");

        // Unique index including rateType (drop old if present)
        $oldIdx = DB::selectOne("
            SELECT COUNT(*) AS c
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'customer_product_rates'
              AND INDEX_NAME = 'customer_product_variant_date_unique'
        ");
        if ($oldIdx && (int) $oldIdx->c > 0) {
            DB::statement('ALTER TABLE `customer_product_rates` DROP INDEX `customer_product_variant_date_unique`');
        }

        $newIdx = DB::selectOne("
            SELECT COUNT(*) AS c
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'customer_product_rates'
              AND INDEX_NAME = 'cpr_party_prod_var_type_date_uq'
        ");
        if (! $newIdx || (int) $newIdx->c === 0) {
            try {
                DB::statement('
                    ALTER TABLE `customer_product_rates`
                    ADD UNIQUE INDEX `cpr_party_prod_var_type_date_uq`
                    (`customerCode`, `productCode`, `variant_id`, `rateType`, `effective_from`)
                ');
            } catch (\Throwable $e) {
                // ignore duplicate / engine limits on nullable unique parts
            }
        }
    }

    public function down(): void
    {
        $newIdx = DB::selectOne("
            SELECT COUNT(*) AS c
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'customer_product_rates'
              AND INDEX_NAME = 'cpr_party_prod_var_type_date_uq'
        ");
        if ($newIdx && (int) $newIdx->c > 0) {
            DB::statement('ALTER TABLE `customer_product_rates` DROP INDEX `cpr_party_prod_var_type_date_uq`');
        }

        $oldIdx = DB::selectOne("
            SELECT COUNT(*) AS c
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'customer_product_rates'
              AND INDEX_NAME = 'customer_product_variant_date_unique'
        ");
        if (! $oldIdx || (int) $oldIdx->c === 0) {
            try {
                DB::statement('
                    ALTER TABLE `customer_product_rates`
                    ADD UNIQUE INDEX `customer_product_variant_date_unique`
                    (`customerCode`, `productCode`, `variant_id`, `effective_from`)
                ');
            } catch (\Throwable $e) {
            }
        }

        $hasColumn = DB::selectOne("
            SELECT COUNT(*) AS c
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'customer_product_rates'
              AND COLUMN_NAME = 'rateType'
        ");
        if ($hasColumn && (int) $hasColumn->c > 0) {
            DB::statement('ALTER TABLE `customer_product_rates` DROP COLUMN `rateType`');
        }
    }
};
