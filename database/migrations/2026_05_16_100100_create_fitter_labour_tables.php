<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labour_issues', function (Blueprint $table) {
            $table->id();
            $table->string('issue_no', 40)->unique();
            $table->date('issue_date');
            $table->foreignId('labour_id')->constrained('labours')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->decimal('total_issue_qty', 15, 3)->default(0);
            $table->decimal('total_returned_qty', 15, 3)->default(0);
            $table->decimal('total_waste_qty', 15, 3)->default(0);
            $table->decimal('total_pending_qty', 15, 3)->default(0);
            $table->string('status', 30)->default('pending');
            $table->boolean('deduct_stock')->default(false);
            $table->timestamps();
        });

        Schema::create('labour_issue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('labour_issue_id')->constrained('labour_issues')->cascadeOnDelete();
            $table->string('product_code', 50);
            $table->string('product_name');
            $table->string('unit', 30)->nullable();
            $table->decimal('issue_qty', 15, 3);
            $table->decimal('returned_qty', 15, 3)->default(0);
            $table->decimal('waste_qty', 15, 3)->default(0);
            $table->decimal('pending_qty', 15, 3)->default(0);
            $table->decimal('fitting_rate', 15, 2)->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('labour_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no', 40)->unique();
            $table->date('return_date');
            $table->foreignId('labour_issue_id')->constrained('labour_issues')->cascadeOnDelete();
            $table->foreignId('labour_id')->constrained('labours')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->decimal('total_return_qty', 15, 3)->default(0);
            $table->decimal('total_waste_qty', 15, 3)->default(0);
            $table->decimal('total_pending_qty', 15, 3)->default(0);
            $table->decimal('total_labour_amount', 15, 2)->default(0);
            $table->string('status', 30)->default('pending');
            $table->timestamps();
        });

        Schema::create('labour_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('labour_return_id')->constrained('labour_returns')->cascadeOnDelete();
            $table->foreignId('labour_issue_item_id')->constrained('labour_issue_items')->cascadeOnDelete();
            $table->decimal('issue_qty', 15, 3);
            $table->decimal('return_qty', 15, 3)->default(0);
            $table->decimal('waste_qty', 15, 3)->default(0);
            $table->decimal('pending_qty', 15, 3)->default(0);
            $table->decimal('fitting_rate', 15, 2)->default(0);
            $table->decimal('labour_amount', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('labour_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no', 40)->unique();
            $table->date('payment_date');
            $table->foreignId('labour_id')->constrained('labours')->cascadeOnDelete();
            $table->foreignId('labour_return_id')->nullable()->constrained('labour_returns')->nullOnDelete();
            $table->decimal('completed_qty', 15, 3)->default(0);
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->string('bank_account', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labour_payments');
        Schema::dropIfExists('labour_return_items');
        Schema::dropIfExists('labour_returns');
        Schema::dropIfExists('labour_issue_items');
        Schema::dropIfExists('labour_issues');
    }
};
