<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('orderNo')->unique();
            $table->date('orderDate');
            $table->string('customerCode');
            $table->string('customerName');
            $table->string('city')->nullable();
            $table->string('gudsName')->nullable();
            $table->integer('noOfItems')->default(0);
            $table->decimal('orderTotal', 15, 2)->default(0);
            $table->decimal('orderDiscount', 15, 2)->default(0);
            $table->decimal('grandTotal', 15, 2)->default(0);
            $table->text('comments')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('orderNo');
            $table->string('productCode');
            $table->string('productName');
            $table->integer('qty');
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('discount', 8, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
