<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $existing = collect(DB::select('SHOW COLUMNS FROM purchase_invoices'))->pluck('Field');
        $add = [];

        if (! $existing->contains('transportRate')) {
            $add[] = 'ADD COLUMN transportRate DECIMAL(14,2) NULL';
        }
        if (! $existing->contains('transportAmount')) {
            $add[] = 'ADD COLUMN transportAmount DECIMAL(14,2) NULL';
        }

        if ($add !== []) {
            DB::statement('ALTER TABLE purchase_invoices '.implode(', ', $add));
        }
    }

    public function down(): void
    {
        $existing = collect(DB::select('SHOW COLUMNS FROM purchase_invoices'))->pluck('Field');
        $drop = [];
        foreach (['transportRate', 'transportAmount'] as $column) {
            if ($existing->contains($column)) {
                $drop[] = 'DROP COLUMN '.$column;
            }
        }
        if ($drop !== []) {
            DB::statement('ALTER TABLE purchase_invoices '.implode(', ', $drop));
        }
    }
};
