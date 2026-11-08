<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $columns = collect(DB::select('SHOW COLUMNS FROM product_info'))->pluck('Field')->all();

        if (! in_array('unitPerPackName', $columns, true)) {
            DB::statement('ALTER TABLE product_info ADD COLUMN unitPerPackName VARCHAR(255) NULL AFTER pakingName');
        }

        if (! in_array('piecesPerUnit', $columns, true)) {
            DB::statement('ALTER TABLE product_info ADD COLUMN piecesPerUnit INT UNSIGNED NULL AFTER unitPerPackName');
        }
    }

    public function down(): void
    {
        $columns = collect(DB::select('SHOW COLUMNS FROM product_info'))->pluck('Field')->all();

        if (in_array('piecesPerUnit', $columns, true)) {
            DB::statement('ALTER TABLE product_info DROP COLUMN piecesPerUnit');
        }

        if (in_array('unitPerPackName', $columns, true)) {
            DB::statement('ALTER TABLE product_info DROP COLUMN unitPerPackName');
        }
    }
};
