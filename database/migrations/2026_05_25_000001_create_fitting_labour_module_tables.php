<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('bom_works', function (Blueprint $table) {
            $table->id();
            $table->string('product_code', 50);
            $table->string('work_type', 100);
            $table->foreignId('production_stage_id')->constrained('production_stages')->cascadeOnDelete();
            $table->decimal('labour_rate', 18, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('remarks', 255)->nullable();
            $table->timestamps();

            $table->unique(['product_code', 'work_type', 'production_stage_id'], 'bom_works_unique');
            $table->index('product_code');
        });

        Schema::create('fitting_labours', function (Blueprint $table) {
            $table->id();
            $table->string('entry_no', 40)->unique();
            $table->date('entry_date');
            $table->foreignId('labour_id')->constrained('labours')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->decimal('total_qty', 18, 3)->default(0);
            $table->decimal('total_amount', 18, 4)->default(0);
            $table->string('status', 20)->default('posted');
            $table->timestamps();
        });

        Schema::create('fitting_labour_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fitting_labour_id')->constrained('fitting_labours')->cascadeOnDelete();
            $table->foreignId('bom_work_id')->nullable()->constrained('bom_works')->nullOnDelete();
            $table->string('product_code', 50);
            $table->string('work_type', 100);
            $table->string('stage_name', 100);
            $table->decimal('qty', 18, 3)->default(0);
            $table->decimal('rate', 18, 4)->default(0);
            $table->decimal('amount', 18, 4)->default(0);
            $table->decimal('issued_qty', 18, 3)->default(0);
            $table->decimal('completed_qty', 18, 3)->default(0);
            $table->decimal('pending_qty', 18, 3)->default(0);
            $table->string('remarks', 255)->nullable();
            $table->timestamps();
        });

        $now = now();
        $stages = [
            ['name' => 'Mold Work', 'sort_order' => 1],
            ['name' => 'Pin Fitting', 'sort_order' => 2],
            ['name' => 'Screw Fitting', 'sort_order' => 3],
            ['name' => 'Final Fitting', 'sort_order' => 4],
            ['name' => 'Packing', 'sort_order' => 5],
        ];
        foreach ($stages as $stage) {
            DB::table('production_stages')->insert(array_merge($stage, [
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fitting_labour_items');
        Schema::dropIfExists('fitting_labours');
        Schema::dropIfExists('bom_works');
        Schema::dropIfExists('production_stages');
    }
};
