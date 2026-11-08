<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Duplicate of 2025_11_26_190401 — no-op when already applied.
     */
    public function up(): void
    {
        if (! Schema::hasTable('customer_product_rates')) {
            return;
        }

        // Already handled by the earlier migration of the same name.
        // Guard against re-dropping a missing unique index on fresh sqlite runs.
        try {
            Schema::table('customer_product_rates', function (Blueprint $table) {
                $table->dropUnique('customer_product_date_unique');
            });
        } catch (\Throwable) {
            // Index already gone / never existed
        }

        try {
            Schema::table('customer_product_rates', function (Blueprint $table) {
                $table->unique(
                    ['customerCode', 'productCode', 'variant_id', 'effective_from'],
                    'customer_product_variant_date_unique'
                );
            });
        } catch (\Throwable) {
            // Unique already present
        }
    }

    public function down(): void
    {
        // Intentionally empty — companion migration owns rollback.
    }
};
