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
        Schema::table('recovery_bank_distributions', function (Blueprint $table) {
            $table->unsignedBigInteger('voucher_id')->after('id'); // references market_vouchers.id
            $table->string('bank_account_code')->after('voucher_id'); // bank account code
            $table->decimal('amount', 15, 2)->default(0)->after('bank_account_code'); // amount allocated to this bank
            $table->unsignedBigInteger('ledger_id')->nullable()->after('amount'); // references ledgers.id

            $table->index('voucher_id');
            $table->index('bank_account_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recovery_bank_distributions', function (Blueprint $table) {
            $table->dropIndex(['voucher_id']);
            $table->dropIndex(['bank_account_code']);
            $table->dropColumn(['voucher_id', 'bank_account_code', 'amount', 'ledger_id']);
        });
    }
};
