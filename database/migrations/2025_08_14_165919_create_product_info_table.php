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
        Schema::create('product_info', function (Blueprint $table) {
            $table->id();
            $table->string('productCode')->unique();
            $table->string('productName');
            $table->string('companyName')->nullable();
            $table->string('colourName')->nullable();
            $table->string('pakingName')->nullable();
            $table->unsignedInteger('saleRate')->default(0); // whole number
            $table->decimal('costRate', 10, 2)->nullable();
            $table->decimal('lessQty', 10, 2)->nullable();
            $table->decimal('stockQty', 10, 2)->nullable();
            $table->string('catagoryName')->nullable();
            // No timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_info'); // table name matches
    }
};

