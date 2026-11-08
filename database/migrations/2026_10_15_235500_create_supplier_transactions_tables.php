<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hasHeader = DB::selectOne("
            SELECT COUNT(*) AS c FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'supplier_transactions'
        ");

        if (! $hasHeader || (int) $hasHeader->c === 0) {
            DB::statement("
                CREATE TABLE `supplier_transactions` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `txn_no` VARCHAR(50) NOT NULL,
                    `supplier_code` VARCHAR(50) NOT NULL,
                    `supplier_name` VARCHAR(255) NULL,
                    `txn_type` VARCHAR(20) NOT NULL,
                    `txn_date` DATE NOT NULL,
                    `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
                    `remarks` VARCHAR(255) NULL,
                    `status` VARCHAR(20) NOT NULL DEFAULT 'Active',
                    `created_at` TIMESTAMP NULL DEFAULT NULL,
                    `updated_at` TIMESTAMP NULL DEFAULT NULL,
                    UNIQUE KEY `supplier_transactions_txn_no_unique` (`txn_no`),
                    KEY `supplier_transactions_supplier_code_index` (`supplier_code`),
                    KEY `supplier_transactions_txn_type_index` (`txn_type`),
                    KEY `supplier_transactions_txn_date_index` (`txn_date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        $hasItems = DB::selectOne("
            SELECT COUNT(*) AS c FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'supplier_transaction_items'
        ");

        if (! $hasItems || (int) $hasItems->c === 0) {
            DB::statement("
                CREATE TABLE `supplier_transaction_items` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `supplier_transaction_id` BIGINT UNSIGNED NOT NULL,
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
                    KEY `sti_transaction_id_index` (`supplier_transaction_id`),
                    KEY `sti_txn_no_index` (`txn_no`),
                    KEY `sti_product_code_index` (`product_code`),
                    CONSTRAINT `sti_transaction_fk`
                        FOREIGN KEY (`supplier_transaction_id`)
                        REFERENCES `supplier_transactions` (`id`)
                        ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS `supplier_transaction_items`');
        DB::statement('DROP TABLE IF EXISTS `supplier_transactions`');
    }
};
