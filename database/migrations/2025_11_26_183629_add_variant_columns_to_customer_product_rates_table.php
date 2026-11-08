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
            $table->unsignedBigInteger('variant_id')->nullable()->after('productCode');
            $table->string('variant_name')->nullable()->after('variant_id');
            $table->string('variant_value')->nullable()->after('variant_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_product_rates', function (Blueprint $table) {
            $table->dropColumn(['variant_id', 'variant_name', 'variant_value']);
        });
    }
};
