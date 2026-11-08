<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bom_items', function (Blueprint $table) {
            $table->decimal('per_dozen', 18, 6)->default(0)->after('qty_per_batch');
            $table->decimal('use_qty', 18, 6)->default(1)->after('per_dozen');
        });
    }

    public function down(): void
    {
        Schema::table('bom_items', function (Blueprint $table) {
            $table->dropColumn(['per_dozen', 'use_qty']);
        });
    }
};
