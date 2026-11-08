<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $productColumns = collect(DB::select('SHOW COLUMNS FROM product_info'))->pluck('Field')->all();
        if (! in_array('image', $productColumns, true)) {
            DB::statement('ALTER TABLE product_info ADD COLUMN image VARCHAR(255) NULL AFTER catagoryName');
        }

        $variantColumns = collect(DB::select('SHOW COLUMNS FROM product_variants'))->pluck('Field')->all();
        if (! in_array('image', $variantColumns, true)) {
            DB::statement('ALTER TABLE product_variants ADD COLUMN image VARCHAR(255) NULL AFTER isActive');
        }
    }

    public function down(): void
    {
        $productColumns = collect(DB::select('SHOW COLUMNS FROM product_info'))->pluck('Field')->all();
        if (in_array('image', $productColumns, true)) {
            DB::statement('ALTER TABLE product_info DROP COLUMN image');
        }

        $variantColumns = collect(DB::select('SHOW COLUMNS FROM product_variants'))->pluck('Field')->all();
        if (in_array('image', $variantColumns, true)) {
            DB::statement('ALTER TABLE product_variants DROP COLUMN image');
        }
    }
};
