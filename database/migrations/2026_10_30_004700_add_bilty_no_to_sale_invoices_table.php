<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::selectOne(
            "SELECT COUNT(*) AS c FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'sale_invoices'
               AND COLUMN_NAME = 'biltyNo'"
        );

        if ((int) ($exists->c ?? 0) === 0) {
            DB::statement('ALTER TABLE `sale_invoices` ADD `biltyNo` VARCHAR(100) NULL AFTER `corten`');
        }
    }

    public function down(): void
    {
        $exists = DB::selectOne(
            "SELECT COUNT(*) AS c FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'sale_invoices'
               AND COLUMN_NAME = 'biltyNo'"
        );

        if ((int) ($exists->c ?? 0) > 0) {
            DB::statement('ALTER TABLE `sale_invoices` DROP COLUMN `biltyNo`');
        }
    }
};
