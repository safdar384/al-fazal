<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $existing = collect(DB::select('SHOW COLUMNS FROM purchase_invoices'))->pluck('Field');
        $add = [];

        if (! $existing->contains('transportCode')) {
            $add[] = 'ADD COLUMN transportCode VARCHAR(255) NULL';
        }
        if (! $existing->contains('transportName')) {
            $add[] = 'ADD COLUMN transportName VARCHAR(255) NULL';
        }
        if (! $existing->contains('vehicleNumber')) {
            $add[] = 'ADD COLUMN vehicleNumber VARCHAR(255) NULL';
        }
        if (! $existing->contains('dispatchNo')) {
            $add[] = 'ADD COLUMN dispatchNo VARCHAR(255) NULL';
        }
        if (! $existing->contains('weight')) {
            $add[] = 'ADD COLUMN weight DECIMAL(14,2) NULL';
        }

        if ($add !== []) {
            DB::statement('ALTER TABLE purchase_invoices '.implode(', ', $add));
        }
    }

    public function down(): void
    {
        $existing = collect(DB::select('SHOW COLUMNS FROM purchase_invoices'))->pluck('Field');
        $drop = [];
        foreach (['transportCode', 'transportName', 'vehicleNumber', 'dispatchNo', 'weight'] as $column) {
            if ($existing->contains($column)) {
                $drop[] = 'DROP COLUMN '.$column;
            }
        }
        if ($drop !== []) {
            DB::statement('ALTER TABLE purchase_invoices '.implode(', ', $drop));
        }
    }
};
