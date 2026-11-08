<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hasHeader = DB::selectOne("
            SELECT COUNT(*) AS c FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fitting_labours'
        ");

        if (! $hasHeader || (int) $hasHeader->c === 0) {
            DB::statement("
                CREATE TABLE `fitting_labours` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `txn_no` VARCHAR(50) NOT NULL,
                    `labour_id` BIGINT UNSIGNED NOT NULL,
                    `labour_code` VARCHAR(50) NOT NULL,
                    `labour_name` VARCHAR(255) NULL,
                    `txn_type` VARCHAR(20) NOT NULL,
                    `entry_type` VARCHAR(20) NULL,
                    `txn_date` DATE NOT NULL,
                    `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
                    `remarks` VARCHAR(255) NULL,
                    `labour_account_transaction_id` BIGINT UNSIGNED NULL,
                    `status` VARCHAR(20) NOT NULL DEFAULT 'Active',
                    `created_at` TIMESTAMP NULL DEFAULT NULL,
                    `updated_at` TIMESTAMP NULL DEFAULT NULL,
                    UNIQUE KEY `fitting_labours_txn_no_unique` (`txn_no`),
                    KEY `fitting_labours_labour_id_index` (`labour_id`),
                    KEY `fitting_labours_labour_code_index` (`labour_code`),
                    KEY `fitting_labours_txn_type_index` (`txn_type`),
                    KEY `fitting_labours_txn_date_index` (`txn_date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        $hasItems = DB::selectOne("
            SELECT COUNT(*) AS c FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fitting_labour_items'
        ");

        if (! $hasItems || (int) $hasItems->c === 0) {
            DB::statement("
                CREATE TABLE `fitting_labour_items` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `fitting_labour_id` BIGINT UNSIGNED NOT NULL,
                    `txn_no` VARCHAR(50) NOT NULL,
                    `product_code` VARCHAR(50) NOT NULL,
                    `product_name` VARCHAR(255) NULL,
                    `variant_id` BIGINT UNSIGNED NULL,
                    `variant_name` VARCHAR(100) NULL,
                    `variant_value` VARCHAR(100) NULL,
                    `qty` DECIMAL(15,2) NOT NULL DEFAULT 0,
                    `rate` DECIMAL(15,2) NOT NULL DEFAULT 0,
                    `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
                    `created_at` TIMESTAMP NULL DEFAULT NULL,
                    `updated_at` TIMESTAMP NULL DEFAULT NULL,
                    KEY `fli_fitting_labour_id_index` (`fitting_labour_id`),
                    KEY `fli_txn_no_index` (`txn_no`),
                    KEY `fli_product_code_index` (`product_code`),
                    CONSTRAINT `fli_fitting_labour_fk`
                        FOREIGN KEY (`fitting_labour_id`)
                        REFERENCES `fitting_labours` (`id`)
                        ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `fitting_labour_items`');
        DB::statement('DROP TABLE IF EXISTS `fitting_labours`');
    }
};
