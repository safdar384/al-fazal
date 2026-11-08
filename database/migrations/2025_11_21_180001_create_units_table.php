<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('symbol', 16)->nullable();
                $table->decimal('to_base_factor', 18, 6)->default(1); // conversion to base unit of same dimension
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
