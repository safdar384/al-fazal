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
        Schema::create('new_ajensts', function (Blueprint $table) {
            $table->id();
            $table->string('ajentCode')->unique(); // Text
            $table->string('ajentName')->nullable(); // Text
            $table->decimal('AjentAdvance', 15, 2)->nullable(); // Value (decimal)
            $table->string('ajentContact')->nullable(); // Text
            $table->string('ajentAdderss')->nullable(); // Text
            $table->decimal('ajentCommission', 8, 2)->nullable(); // Value (decimal)
            $table->decimal('ajentBalance', 15, 2)->nullable(); // Value (decimal)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_ajensts');
    }
};
