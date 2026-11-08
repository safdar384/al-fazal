<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fitting_labours', function (Blueprint $table) {
            $table->decimal('total_material_cost', 18, 4)->default(0)->after('total_amount');
            $table->decimal('total_waste_qty', 18, 3)->default(0)->after('total_material_cost');
        });

        Schema::create('fitting_labour_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fitting_labour_id')->constrained('fitting_labours')->cascadeOnDelete();
            $table->string('material_code', 50);
            $table->string('material_name', 150);
            $table->decimal('rate', 18, 4)->default(0);
            $table->decimal('per_dozen', 18, 6)->default(12);
            $table->decimal('bom_use_qty', 18, 6)->default(1);
            $table->decimal('wastage_percent', 8, 4)->default(0);
            $table->decimal('consumed_qty', 18, 6)->default(0);
            $table->decimal('waste_qty', 18, 6)->default(0);
            $table->decimal('unit_cost', 18, 6)->default(0);
            $table->decimal('line_cost', 18, 4)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['fitting_labour_id', 'material_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fitting_labour_materials');

        Schema::table('fitting_labours', function (Blueprint $table) {
            $table->dropColumn(['total_material_cost', 'total_waste_qty']);
        });
    }
};
