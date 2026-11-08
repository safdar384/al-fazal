<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $has = DB::selectOne("
            SELECT COUNT(*) AS c
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'supplier_transactions'
              AND COLUMN_NAME = 'entry_type'
        ");

        if (! $has || (int) $has->c === 0) {
            DB::statement("
                ALTER TABLE `supplier_transactions`
                ADD COLUMN `entry_type` VARCHAR(20) NULL AFTER `txn_type`
            ");
        }

        // Existing cash rows defaulted to Debit (previous payment behavior)
        DB::statement("
            UPDATE `supplier_transactions`
            SET `entry_type` = 'pay'
            WHERE `txn_type` = 'Cash' AND (`entry_type` IS NULL OR `entry_type` = '' OR `entry_type` = 'debit')
        ");
        DB::statement("
            UPDATE `supplier_transactions`
            SET `entry_type` = 'receive'
            WHERE `txn_type` = 'Cash' AND `entry_type` = 'credit'
        ");
    }

    public function down(): void
    {
        $has = DB::selectOne("
            SELECT COUNT(*) AS c
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'supplier_transactions'
              AND COLUMN_NAME = 'entry_type'
        ");

        if ($has && (int) $has->c > 0) {
            DB::statement('ALTER TABLE `supplier_transactions` DROP COLUMN `entry_type`');
        }
    }
};
