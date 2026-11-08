<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_overheads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('boms')->onDelete('cascade');
            $table->string('overhead_name');
            $table->enum('type', ['Fixed', 'Variable'])->default('Fixed');
            $table->enum('allocation_base', ['PerBatch', 'PerUnit', 'PerMinute', 'PercentMaterial'])->default('PerBatch');
            $table->decimal('value', 18, 6)->default(0); // meaning depends on allocation_base
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_overheads');
    }
};
