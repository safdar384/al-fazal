<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hasTable = DB::selectOne("
            SELECT COUNT(*) AS c FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'supplier_transactions'
        ");
        if (! $hasTable || (int) $hasTable->c === 0) {
            return;
        }

        $hasColumn = DB::selectOne("
            SELECT COUNT(*) AS c FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'supplier_transactions'
              AND COLUMN_NAME = 'bank_account'
        ");
        if ($hasColumn && (int) $hasColumn->c > 0) {
            return;
        }

        DB::statement("
            ALTER TABLE `supplier_transactions`
            ADD COLUMN `bank_account` VARCHAR(100) NULL AFTER `entry_type`
        ");
    }

    public function down(): void
    {
        $hasColumn = DB::selectOne("
            SELECT COUNT(*) AS c FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'supplier_transactions'
              AND COLUMN_NAME = 'bank_account'
        ");
        if (! $hasColumn || (int) $hasColumn->c === 0) {
            return;
        }

        DB::statement('ALTER TABLE `supplier_transactions` DROP COLUMN `bank_account`');
    }
};
