<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('customerareas', function (Blueprint $table) {
            $table->id();
            $table->string('areaCode', 50)->unique();
            $table->string('areaName', 150);
            $table->string('ajentName', 150)->nullable();
            $table->decimal('balance', 15, 2)->default(0);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customerareas');
    }
};
