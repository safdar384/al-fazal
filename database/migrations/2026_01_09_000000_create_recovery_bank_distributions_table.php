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
        // Check if table already exists
        if (! Schema::hasTable('recovery_bank_distributions')) {
            Schema::create('recovery_bank_distributions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('voucher_id')->nullable()->index();
                $table->string('bank_account_code');
                $table->decimal('amount', 12, 2);
                $table->string('reference')->nullable();
                $table->date('distribution_date')->nullable();
                $table->unsignedBigInteger('ledger_id')->nullable();
                $table->timestamps();

                // Index for better performance
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
