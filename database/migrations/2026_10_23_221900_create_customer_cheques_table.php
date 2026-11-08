<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customer_cheques')) {
            return;
        }

        Schema::create('customer_cheques', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 50)->index();
            $table->string('customer_area', 50)->nullable()->index();
            $table->string('bank_name', 150);
            $table->string('cheque_no', 100);
            $table->decimal('amount', 15, 2);
            $table->date('cheque_date');
            $table->date('due_date')->index();
            $table->string('description', 255)->nullable();
            $table->string('status', 20)->default('Pending')->index();
            $table->unsignedBigInteger('ledger_id')->nullable()->index();
            $table->timestamps();

            $table->unique(['customer_code', 'cheque_no'], 'customer_cheques_customer_cheque_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_cheques');
    }
};
