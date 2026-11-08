<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $has = DB::selectOne("
            SELECT COUNT(*) AS c FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fitting_work_stocks'
        ");

        if (! $has || (int) $has->c === 0) {
            DB::statement("
                CREATE TABLE `fitting_work_stocks` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `product_code` VARCHAR(50) NOT NULL,
                    `product_name` VARCHAR(255) NULL,
                    `work_name` VARCHAR(100) NOT NULL,
                    `qty` DECIMAL(15,2) NOT NULL DEFAULT 0,
                    `created_at` TIMESTAMP NULL DEFAULT NULL,
                    `updated_at` TIMESTAMP NULL DEFAULT NULL,
                    UNIQUE KEY `fws_product_work_unique` (`product_code`, `work_name`),
                    KEY `fws_product_code_index` (`product_code`),
                    KEY `fws_work_name_index` (`work_name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // Backfill from existing completed Work entries (once).
        $hasLabours = DB::selectOne("
            SELECT COUNT(*) AS c FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fitting_labours'
        ");
        $hasItems = DB::selectOne("
            SELECT COUNT(*) AS c FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fitting_labour_items'
        ");
        $stockCount = DB::selectOne('SELECT COUNT(*) AS c FROM fitting_work_stocks');

        if (
            $hasLabours && (int) $hasLabours->c > 0
            && $hasItems && (int) $hasItems->c > 0
            && $stockCount && (int) $stockCount->c === 0
        ) {
            DB::statement("
                INSERT INTO fitting_work_stocks (product_code, product_name, work_name, qty, created_at, updated_at)
                SELECT
                    i.product_code,
                    MAX(i.product_name) AS product_name,
                    i.work_name,
                    ROUND(SUM(i.qty), 2) AS qty,
                    NOW(),
                    NOW()
                FROM fitting_labour_items i
                INNER JOIN fitting_labours f ON f.id = i.fitting_labour_id
                WHERE f.status = 'Active'
                  AND f.txn_type IN ('Work', 'Purchase', 'Sale')
                  AND i.work_name IS NOT NULL
                  AND TRIM(i.work_name) <> ''
                  AND i.product_code IS NOT NULL
                  AND i.product_code <> 'CASH'
                GROUP BY i.product_code, i.work_name
            ");
        }
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `fitting_work_stocks`');
    }
};
