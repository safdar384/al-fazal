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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code')->unique(); // e.g. CUST001, BANK001
            $table->string('account_name');
            $table->enum('account_type', ['Customer', 'Supplier', 'Bank', 'Expense', 'Income', 'Labour']);
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
        });

        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->string('reference_no')->nullable();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledgers');
        Schema::dropIfExists('accounts');
    }
};
