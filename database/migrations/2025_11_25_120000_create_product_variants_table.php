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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->string('productCode');
            $table->string('variantName'); // Size, Weight, etc.
            $table->string('variantValue'); // Small, Large, 1kg, 5kg, etc.
            $table->decimal('additionalCost', 10, 2)->default(0);
            $table->decimal('additionalPrice', 10, 2)->default(0);
            $table->integer('stockQty')->default(0);
            $table->boolean('isActive')->default(true);

            $table->foreign('productCode')->references('productCode')->on('product_info')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
