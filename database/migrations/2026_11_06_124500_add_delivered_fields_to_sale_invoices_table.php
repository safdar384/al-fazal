<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Avoid Schema::hasColumn — older MySQL lacks information_schema.generation_expression
        // which Laravel uses when introspecting columns.
        try {
            DB::statement('ALTER TABLE `sale_invoices` ADD COLUMN `delivered` TINYINT(1) NOT NULL DEFAULT 0 AFTER `biltyBalance`');
        } catch (\Throwable $e) {
            if (! str_contains(strtolower($e->getMessage()), 'duplicate column')) {
                throw $e;
            }
        }

        try {
            DB::statement('ALTER TABLE `sale_invoices` ADD COLUMN `delivered_date` TIMESTAMP NULL DEFAULT NULL AFTER `delivered`');
        } catch (\Throwable $e) {
            if (! str_contains(strtolower($e->getMessage()), 'duplicate column')) {
                throw $e;
            }
        }
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE `sale_invoices` DROP COLUMN `delivered_date`');
        } catch (\Throwable) {
            //
        }

        try {
            DB::statement('ALTER TABLE `sale_invoices` DROP COLUMN `delivered`');
        } catch (\Throwable) {
            //
        }
    }
};
