<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_materials', function (Blueprint $table) {
            $table->id();
            $table->string('material_code', 50)->unique();
            $table->string('material_name', 100);
            $table->string('packing', 100)->nullable();
            $table->decimal('rate', 18, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_materials');
    }
};
