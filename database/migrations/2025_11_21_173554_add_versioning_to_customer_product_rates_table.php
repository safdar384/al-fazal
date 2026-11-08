<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customer_product_rates', function (Blueprint $table) {
            // Drop the unique constraint first
            $table->dropUnique(['customerCode', 'productCode']);

            // Add versioning fields
            $table->date('effective_from')->default(now())->after('productCode');
            $table->boolean('is_active')->default(true)->after('status');

            // Add new composite unique constraint including effective_from
            $table->unique(['customerCode', 'productCode', 'effective_from'], 'customer_product_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_product_rates', function (Blueprint $table) {
            $table->dropUnique('customer_product_date_unique');
            $table->dropColumn(['effective_from', 'is_active']);
            $table->unique(['customerCode', 'productCode']);
        });
    }
};
