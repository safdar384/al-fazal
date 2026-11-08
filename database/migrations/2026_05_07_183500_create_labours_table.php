<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labours', function (Blueprint $table) {
            $table->id();
            $table->string('labourCode', 50)->unique();
            $table->string('labourName');
            $table->string('labourAddress', 500);
            $table->string('labourCatagory', 50);
            $table->decimal('salary', 15, 2)->default(0);
            $table->decimal('advance', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('commissionPercentage', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labours');
    }
};
