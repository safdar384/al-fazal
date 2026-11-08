<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->string('returnNo')->unique();
            $table->date('returnDate');
            $table->string('customerCode');
            $table->string('customerName');
            $table->string('city')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('originalInvoiceNo')->nullable();
            $table->text('comments')->nullable();
            $table->integer('noOfItems')->default(0);
            $table->decimal('invoiceTotal', 15, 2)->default(0);
            $table->decimal('invoiceDiscount', 15, 2)->default(0);
            $table->decimal('grandTotal', 15, 2)->default(0);
            $table->decimal('goodQtyTotal', 15, 2)->default(0);
            $table->decimal('damageQtyTotal', 15, 2)->default(0);
            $table->string('status')->default('Active');
            $table->timestamps();

            $table->index('customerCode');
            $table->index('returnDate');
        });

        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_id')->constrained('sale_returns')->cascadeOnDelete();
            $table->string('returnNo');
            $table->string('productCode');
            $table->string('productName');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->string('variant_name')->nullable();
            $table->string('variant_value')->nullable();
            $table->decimal('variant_added_price', 15, 2)->default(0);
            $table->decimal('qty', 15, 2)->default(0);
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('cost_rate', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            /** good = restore stock; damage = no stock restore */
            $table->string('condition', 20)->default('good');
            $table->timestamps();

            $table->index('returnNo');
            $table->index('productCode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sale_returns');
    }
};
