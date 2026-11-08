<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            $table->unsignedBigInteger('variant_id')->nullable()->after('productName');
            $table->string('variant_name')->nullable()->after('variant_id');
            $table->string('variant_value')->nullable()->after('variant_name');
            $table->decimal('variant_added_price', 15, 2)->nullable()->after('variant_value');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            $table->dropColumn(['variant_id', 'variant_name', 'variant_value', 'variant_added_price']);
        });
    }
};
