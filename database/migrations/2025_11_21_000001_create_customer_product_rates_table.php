<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_product_rates', function (Blueprint $table) {
            $table->id();
            $table->string('customerCode');
            $table->string('productCode');
            $table->decimal('specialRate', 15, 2); // Special discount rate
            $table->decimal('discountPercent', 8, 2)->default(0); // Additional discount %
            $table->string('remarks')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();

            // Unique constraint: one rate per customer-product combination
            $table->unique(['customerCode', 'productCode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_product_rates');
    }
};
