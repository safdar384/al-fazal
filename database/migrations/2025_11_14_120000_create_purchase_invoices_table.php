<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoiceNo')->unique();
            $table->date('invoiceDate');
            $table->string('supplierCode');
            $table->string('supplierName');
            $table->string('city')->nullable();
            $table->decimal('balance', 14, 2)->default(0);
            $table->text('comments')->nullable();
            $table->integer('noOfItems')->default(0);
            $table->decimal('invoiceTotal', 14, 2)->default(0);
            $table->decimal('invoiceDiscount', 14, 2)->default(0);
            $table->decimal('grandTotal', 14, 2)->default(0);
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoices');
    }
};
