<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = collect(DB::select('SHOW COLUMNS FROM packing'))->pluck('Field')->all();

        if (! in_array('baseUnit', $columns, true)) {
            DB::statement('ALTER TABLE packing ADD COLUMN baseUnit VARCHAR(50) NULL AFTER pakingName');
        }

        if (! in_array('qtyInPack', $columns, true)) {
            DB::statement('ALTER TABLE packing ADD COLUMN qtyInPack INT UNSIGNED NULL AFTER baseUnit');
        }

        // Backfill existing rows
        DB::table('packing')->whereNull('baseUnit')->update(['baseUnit' => 'PIECE']);
        DB::table('packing')->whereNull('qtyInPack')->orWhere('qtyInPack', 0)->update(['qtyInPack' => 1]);

        // Drop single-column unique on pakingName if present; allow same name with different unit/qty.
        try {
            DB::statement('ALTER TABLE packing DROP INDEX packing_pakingname_unique');
        } catch (\Throwable $e) {
            // index name may differ
        }

        $indexes = collect(DB::select('SHOW INDEX FROM packing'))->pluck('Key_name')->unique()->all();
        if (! in_array('packing_name_unit_qty_unique', $indexes, true)) {
            try {
                DB::statement('ALTER TABLE packing ADD UNIQUE packing_name_unit_qty_unique (pakingName, baseUnit, qtyInPack)');
            } catch (\Throwable $e) {
                // ignore if duplicates block unique — data still usable
            }
        }
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE packing DROP INDEX packing_name_unit_qty_unique');
        } catch (\Throwable $e) {
            //
        }

        $columns = collect(DB::select('SHOW COLUMNS FROM packing'))->pluck('Field')->all();

        if (in_array('qtyInPack', $columns, true)) {
            DB::statement('ALTER TABLE packing DROP COLUMN qtyInPack');
        }
        if (in_array('baseUnit', $columns, true)) {
            DB::statement('ALTER TABLE packing DROP COLUMN baseUnit');
        }
    }
};
