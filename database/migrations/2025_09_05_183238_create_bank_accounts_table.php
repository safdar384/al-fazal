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
        Schema::create('bank_accounts', function (Blueprint $table) {
        $table->id();
        $table->string('bankAccount');   // account number ya code
        $table->string('bankName');      // bank ka naam
        $table->string('bankAddress')->nullable(); // optional
        $table->string('branchCode')->nullable();  // optional
       
    
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
