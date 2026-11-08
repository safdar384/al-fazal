<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labour_account_transactions', function (Blueprint $table) {
            $table->id();
            $table->date('transactionDate');
            $table->unsignedBigInteger('labour_id');
            $table->string('labourCode', 50);
            $table->string('labourName', 255);
            $table->string('labourCatagory', 50);
            $table->string('description', 255);
            $table->string('bankAccount', 100)->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('entryType', ['debit', 'credit']);
            $table->decimal('marketBalance', 15, 2)->nullable();
            $table->decimal('recovery', 15, 2)->nullable();
            $table->decimal('recoveryCommPercent', 8, 2)->nullable();
            $table->decimal('commissionPercent', 8, 2)->nullable();
            $table->boolean('isAdvance')->default(false);
            $table->timestamps();

            $table->index(['labour_id', 'transactionDate']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labour_account_transactions');
    }
};
