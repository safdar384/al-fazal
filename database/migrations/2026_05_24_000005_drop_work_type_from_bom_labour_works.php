<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE bom_labour_works DROP COLUMN work_type');
        } catch (\Throwable $e) {
            // Column may already be removed.
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('bom_labour_works', 'work_type')) {
            Schema::table('bom_labour_works', function (Blueprint $table) {
                $table->string('work_type', 30)->default('product_cost')->after('work_name');
            });
        }
    }
};
