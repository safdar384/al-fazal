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
        Schema::create('customerledgers', function (Blueprint $table) {
            $table->id();
            $table->date('customerDate');
            $table->string('invoiceNumber')->nullable();
            $table->string('customerArea');
            $table->string('customerCode');
            $table->string('description');
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('debit', 15, 2)->default(0);
            $table->string('category')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customerledgers');
    }
};
