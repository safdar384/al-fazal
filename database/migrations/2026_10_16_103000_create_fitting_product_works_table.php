<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $has = DB::selectOne("
            SELECT COUNT(*) AS c FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fitting_product_works'
        ");

        if (! $has || (int) $has->c === 0) {
            DB::statement("
                CREATE TABLE `fitting_product_works` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `product_code` VARCHAR(50) NOT NULL,
                    `product_name` VARCHAR(255) NULL,
                    `work_name` VARCHAR(100) NOT NULL,
                    `rate` DECIMAL(15,2) NOT NULL DEFAULT 0,
                    `status` VARCHAR(20) NOT NULL DEFAULT 'Active',
                    `created_at` TIMESTAMP NULL DEFAULT NULL,
                    `updated_at` TIMESTAMP NULL DEFAULT NULL,
                    UNIQUE KEY `fpw_product_work_unique` (`product_code`, `work_name`),
                    KEY `fpw_product_code_index` (`product_code`),
                    KEY `fpw_work_name_index` (`work_name`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        $hasCol = DB::selectOne("
            SELECT COUNT(*) AS c FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'fitting_labour_items'
              AND COLUMN_NAME = 'work_name'
        ");
        if (! $hasCol || (int) $hasCol->c === 0) {
            DB::statement("
                ALTER TABLE `fitting_labour_items`
                ADD COLUMN `work_name` VARCHAR(100) NULL AFTER `product_name`
            ");
        }
    }

    public function down(): void
    {
        $hasCol = DB::selectOne("
            SELECT COUNT(*) AS c FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'fitting_labour_items'
              AND COLUMN_NAME = 'work_name'
        ");
        if ($hasCol && (int) $hasCol->c > 0) {
            DB::statement('ALTER TABLE `fitting_labour_items` DROP COLUMN `work_name`');
        }
        DB::statement('DROP TABLE IF EXISTS `fitting_product_works`');
    }
};
