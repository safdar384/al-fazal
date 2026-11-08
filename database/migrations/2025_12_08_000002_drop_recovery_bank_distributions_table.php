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
        Schema::dropIfExists('recovery_bank_distributions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('recovery_bank_distributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('voucher_id');
            $table->string('bank_account_code');
            $table->decimal('amount', 15, 2);
            $table->unsignedBigInteger('ledger_id')->nullable();
            $table->timestamps();
            $table->foreign('voucher_id')->references('id')->on('market_vouchers')->onDelete('cascade');
        });
    }
};
