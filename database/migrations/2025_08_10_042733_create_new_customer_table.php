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
        Schema::create('newCustomers', function (Blueprint $table) {
            $table->id();
            $table->string('custCode')->unique(); // Text
            $table->string('custArea')->nullable(); // Text
            $table->string('bussnessName')->nullable(); // Text
            $table->string('custName')->nullable(); // Text
            $table->string('custAddress')->nullable(); // Text
            $table->string('custContect')->nullable(); // Text
            $table->string('custCity')->nullable(); // Text
            $table->string('custPage')->nullable(); // Text
            $table->decimal('custdiscount', 8, 2)->nullable(); // Value (decimal)
            $table->integer('custPayTermDay')->nullable(); // Value (integer)
            $table->decimal('custCreditLimit', 15, 2)->nullable(); // Value (decimal)
            $table->date('custOppDate')->nullable(); // Value (date)
            $table->decimal('custBalance', 15, 2)->nullable(); // Value (decimal)
            $table->string('custCatagory')->nullable(); // Text
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newCustomers');
    }
};
