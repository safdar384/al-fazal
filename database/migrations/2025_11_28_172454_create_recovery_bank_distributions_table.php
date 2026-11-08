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
        // Skip if table already exists
        if (! Schema::hasTable('recovery_bank_distributions')) {
            Schema::create('recovery_bank_distributions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('voucher_id'); // references market_vouchers.id
                $table->string('bank_account_code'); // bank account code
                $table->decimal('amount', 15, 2)->default(0); // amount allocated to this bank
                $table->unsignedBigInteger('ledger_id')->nullable(); // references ledgers.id
                $table->timestamps();

                $table->index('voucher_id');
                $table->index('bank_account_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recovery_bank_distributions');
    }
};
