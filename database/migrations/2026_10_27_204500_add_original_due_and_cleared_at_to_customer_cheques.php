<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_cheques')) {
            return;
        }

        // Raw DDL for older MySQL (avoids information_schema generation_expression)
        $cols = collect(DB::select('SHOW COLUMNS FROM customer_cheques'))->pluck('Field')->all();

        if (! in_array('original_due_date', $cols, true)) {
            DB::statement('ALTER TABLE customer_cheques ADD COLUMN original_due_date DATE NULL AFTER due_date');
        }
        if (! in_array('cleared_at', $cols, true)) {
            DB::statement('ALTER TABLE customer_cheques ADD COLUMN cleared_at DATE NULL AFTER status');
        }

        DB::table('customer_cheques')
            ->whereNull('original_due_date')
            ->update([
                'original_due_date' => DB::raw('due_date'),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer_cheques')) {
            return;
        }

        $cols = collect(DB::select('SHOW COLUMNS FROM customer_cheques'))->pluck('Field')->all();

        if (in_array('cleared_at', $cols, true)) {
            DB::statement('ALTER TABLE customer_cheques DROP COLUMN cleared_at');
        }
        if (in_array('original_due_date', $cols, true)) {
            DB::statement('ALTER TABLE customer_cheques DROP COLUMN original_due_date');
        }
    }
};
