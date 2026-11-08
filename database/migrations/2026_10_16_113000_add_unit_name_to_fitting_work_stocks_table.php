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
              AND TABLE_NAME = 'fitting_work_stocks'
              AND COLUMN_NAME = 'unit_name'
        ");

        if (! $hasCol || (int) $hasCol->c === 0) {
            DB::statement("
                ALTER TABLE `fitting_work_stocks`
                ADD COLUMN `unit_name` VARCHAR(50) NULL AFTER `work_name`
            ");
        }

        // Backfill unit from product master where missing.
        DB::statement("
            UPDATE fitting_work_stocks fws
            INNER JOIN product_info p ON p.productCode = fws.product_code
            SET fws.unit_name = NULLIF(TRIM(p.unitPerPackName), '')
            WHERE fws.unit_name IS NULL OR TRIM(fws.unit_name) = ''
        ");

        DB::statement("
            UPDATE fitting_work_stocks
            SET unit_name = 'Pieces'
            WHERE unit_name IS NULL OR TRIM(unit_name) = ''
        ");
    }

    public function down(): void
    {
        $hasCol = DB::selectOne("
            SELECT COUNT(*) AS c FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'fitting_work_stocks'
              AND COLUMN_NAME = 'unit_name'
        ");

        if ($hasCol && (int) $hasCol->c > 0) {
            DB::statement('ALTER TABLE `fitting_work_stocks` DROP COLUMN `unit_name`');
        }
    }
};
