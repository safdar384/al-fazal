<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labour_work_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_final')->default(false);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('labour_work_setups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('labour_id')->constrained('labours')->cascadeOnDelete();
            $table->foreignId('work_type_id')->constrained('labour_work_types')->cascadeOnDelete();
            $table->string('product_code', 50);
            $table->decimal('rate_per_piece', 15, 2)->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->unique(['labour_id', 'work_type_id', 'product_code'], 'labour_work_setup_unique');
        });

        Schema::create('fitter_production_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_no', 40)->unique();
            $table->date('job_date');
            $table->string('product_code', 50);
            $table->string('product_name');
            $table->string('finished_product_code', 50)->nullable();
            $table->decimal('batch_qty', 15, 3);
            $table->decimal('completed_qty', 15, 3)->default(0);
            $table->decimal('waste_qty', 15, 3)->default(0);
            $table->decimal('pending_qty', 15, 3)->default(0);
            $table->foreignId('current_stage_id')->nullable();
            $table->string('status', 30)->default('in_progress');
            $table->boolean('deduct_raw_stock')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('fitter_production_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_job_id')->constrained('fitter_production_jobs')->cascadeOnDelete();
            $table->foreignId('work_type_id')->constrained('labour_work_types');
            $table->foreignId('labour_id')->nullable()->constrained('labours')->nullOnDelete();
            $table->unsignedSmallInteger('sequence');
            $table->decimal('qty_at_stage', 15, 3)->default(0);
            $table->decimal('completed_qty', 15, 3)->default(0);
            $table->decimal('waste_qty', 15, 3)->default(0);
            $table->decimal('transferred_out_qty', 15, 3)->default(0);
            $table->decimal('pending_qty', 15, 3)->default(0);
            $table->decimal('rate_per_piece', 15, 2)->default(0);
            $table->decimal('labour_amount', 15, 2)->default(0);
            $table->string('status', 30)->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('fitter_production_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_job_id')->constrained('fitter_production_jobs')->cascadeOnDelete();
            $table->foreignId('from_stage_id')->constrained('fitter_production_stages');
            $table->foreignId('to_stage_id')->constrained('fitter_production_stages');
            $table->foreignId('from_labour_id')->nullable()->constrained('labours')->nullOnDelete();
            $table->foreignId('to_labour_id')->nullable()->constrained('labours')->nullOnDelete();
            $table->decimal('transfer_qty', 15, 3);
            $table->decimal('waste_qty', 15, 3)->default(0);
            $table->date('transfer_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('labour_issues', function (Blueprint $table) {
            $table->foreignId('production_job_id')->nullable()->after('labour_id')->constrained('fitter_production_jobs')->nullOnDelete();
            $table->foreignId('production_stage_id')->nullable()->after('production_job_id')->constrained('fitter_production_stages')->nullOnDelete();
        });

        $now = now();
        $types = [
            ['name' => 'Mold Work', 'sort_order' => 1, 'is_final' => false],
            ['name' => 'Pin Fitting', 'sort_order' => 2, 'is_final' => false],
            ['name' => 'Screw Fitting', 'sort_order' => 3, 'is_final' => false],
            ['name' => 'Final Fitting', 'sort_order' => 4, 'is_final' => false],
            ['name' => 'Packing', 'sort_order' => 5, 'is_final' => true],
        ];
        foreach ($types as $t) {
            DB::table('labour_work_types')->insert(array_merge($t, [
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        Schema::table('labour_issues', function (Blueprint $table) {
            $table->dropConstrainedForeignId('production_stage_id');
            $table->dropConstrainedForeignId('production_job_id');
        });
        Schema::dropIfExists('fitter_production_transfers');
        Schema::dropIfExists('fitter_production_stages');
        Schema::dropIfExists('fitter_production_jobs');
        Schema::dropIfExists('labour_work_setups');
        Schema::dropIfExists('labour_work_types');
    }
};
