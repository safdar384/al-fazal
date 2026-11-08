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
        Schema::create('sale_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoiceNo')->unique();
            $table->date('invoiceDate');
            $table->string('customerCode');
            $table->string('customerName');
            $table->string('city')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('gudsName')->nullable();
            $table->integer('corten')->default(0);
            $table->decimal('biltyExpanse', 15, 2)->default(0);
            $table->decimal('loaderExpanse', 15, 2)->default(0);
            $table->decimal('biltyBalance', 15, 2)->default(0);
            $table->text('comments')->nullable();
            $table->integer('noOfItems')->default(0);
            $table->decimal('invoiceTotal', 15, 2)->default(0);
            $table->decimal('invoiceDiscount', 15, 2)->default(0);
            $table->decimal('grandTotal', 15, 2)->default(0);
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_invoices');
    }
};
