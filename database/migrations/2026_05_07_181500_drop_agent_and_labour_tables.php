<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('agent_ledger');
        Schema::dropIfExists('new_ajensts');
    }

    public function down(): void
    {
        Schema::create('new_ajensts', function (Blueprint $table) {
            $table->id();
            $table->string('ajentCode')->nullable();
            $table->string('ajentName')->nullable();
            $table->string('ajentCategory', 100)->nullable();
            $table->decimal('salary', 15, 2)->default(0);
            $table->decimal('commission_percentage', 8, 2)->nullable();
            $table->double('AjentAdvance')->nullable();
            $table->string('ajentContact')->nullable();
            $table->string('ajentAdderss')->nullable();
            $table->double('ajentCommission')->nullable();
            $table->double('ajentBalance')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_ledger', function (Blueprint $table) {
            $table->id();
            $table->date('entryDate');
            $table->string('agentCode')->nullable();
            $table->string('description')->nullable();
            $table->decimal('marketBalance', 15, 2)->default(0);
            $table->decimal('recovery', 15, 2)->default(0);
            $table->decimal('recoveryPercent', 8, 2)->default(0);
            $table->decimal('commissionPercent', 8, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('debit', 15, 2)->default(0);
            $table->string('category')->nullable();
            $table->timestamps();
        });
    }
};
