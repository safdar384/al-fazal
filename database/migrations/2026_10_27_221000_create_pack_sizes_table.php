<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pack_sizes')) {
            Schema::create('pack_sizes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('packing_type', 50);
                $table->string('unit_name', 50);
                $table->decimal('qty', 12, 2);
                $table->string('label', 120);
                $table->unique(['packing_type', 'unit_name', 'qty'], 'pack_sizes_unique_combo');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_sizes');
    }
};
