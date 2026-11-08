<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_cost_versions', function (Blueprint $table) {
            $table->id();
            $table->string('productCode');
            $table->date('effective_from');
            $table->boolean('is_active')->default(true);
            $table->decimal('material_cost', 18, 4)->default(0);
            $table->decimal('labor_cost', 18, 4)->default(0);
            $table->decimal('overhead_cost', 18, 4)->default(0);
            $table->decimal('wastage_cost', 18, 4)->default(0);
            $table->decimal('batch_cost', 18, 4)->default(0);
            $table->decimal('unit_cost', 18, 6)->default(0);
            $table->string('currency', 8)->default('PKR');
            $table->string('remarks')->nullable();
            $table->timestamps();
            $table->unique(['productCode', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_cost_versions');
    }
};
