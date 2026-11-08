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
        Schema::create('expense_ledger', function (Blueprint $table) {
            $table->id();
            $table->date('entryDate');
            $table->string('expenseCode');
            $table->string('expenseCategory')->nullable();
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('paymentMethod')->nullable(); // Cash, Bank, Card
            $table->string('accountCode')->nullable(); // Reference to accounts table
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('referenceNo')->nullable();
            $table->string('category')->nullable(); // Expense type category
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_ledger');
    }
};
