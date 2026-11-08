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
        Schema::create('market_vouchers', function (Blueprint $table) {
            $table->id();
            $table->date('voucher_date'); // ✅ Recovery Date
            $table->string('customerArea', 50);
            $table->string('customerCode', 50);
            $table->string('description', 255);
            $table->decimal('recovery', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('misc', 15, 2)->default(0);
            $table->decimal('preBalance', 15, 2)->default(0);
            $table->decimal('lastBalance', 15, 2)->default(0);
            $table->decimal('delivered', 15, 2)->default(0);
            $table->unsignedBigInteger('market_vouchers_id')->nullable();
            $table->foreign('market_vouchers_id')->references('id')->on('ledgers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_vouchers');
    }
};
