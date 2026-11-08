<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('unit_per_pack')) {
            Schema::create('unit_per_pack', function (Blueprint $table) {
                $table->id();
                $table->string('unitName')->unique();
                $table->unsignedInteger('piecesPerUnit');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_per_pack');
    }
};
