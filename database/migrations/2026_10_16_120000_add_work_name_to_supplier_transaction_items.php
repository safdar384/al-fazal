<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hasCol = DB::selectOne("
            SELECT COUNT(*) AS c FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'supplier_transaction_items'
              AND COLUMN_NAME = 'work_name'
        ");

        if (! $hasCol || (int) $hasCol->c === 0) {
            DB::statement("
                ALTER TABLE `supplier_transaction_items`
                ADD COLUMN `work_name` VARCHAR(100) NULL AFTER `product_name`
            ");
        }
    }

    public function down(): void
    {
        $hasCol = DB::selectOne("
            SELECT COUNT(*) AS c FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'supplier_transaction_items'
              AND COLUMN_NAME = 'work_name'
        ");

        if ($hasCol && (int) $hasCol->c > 0) {
            DB::statement('ALTER TABLE `supplier_transaction_items` DROP COLUMN `work_name`');
        }
    }
};
