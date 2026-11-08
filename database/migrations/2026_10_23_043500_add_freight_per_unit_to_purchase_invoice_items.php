<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $existing = collect(DB::select('SHOW COLUMNS FROM purchase_invoice_items'))->pluck('Field');
        if (! $existing->contains('freightPerUnit')) {
            DB::statement('ALTER TABLE purchase_invoice_items ADD COLUMN freightPerUnit DECIMAL(14,2) NULL');
        }
    }

    public function down(): void
    {
        $existing = collect(DB::select('SHOW COLUMNS FROM purchase_invoice_items'))->pluck('Field');
        if ($existing->contains('freightPerUnit')) {
            DB::statement('ALTER TABLE purchase_invoice_items DROP COLUMN freightPerUnit');
        }
    }
};
