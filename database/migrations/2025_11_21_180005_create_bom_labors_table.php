<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_labors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('boms')->onDelete('cascade');
            $table->string('operation_name');
            $table->decimal('time_minutes', 18, 6)->default(0);
            $table->decimal('rate_per_minute', 18, 6)->default(0); // can derive from wage tables later
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_labors');
    }
};
