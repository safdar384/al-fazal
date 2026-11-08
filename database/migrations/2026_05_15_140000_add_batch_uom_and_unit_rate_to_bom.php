<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            $table->string('batch_uom', 20)->default('piece')->after('batch_size');
        });

        Schema::table('bom_items', function (Blueprint $table) {
            $table->decimal('unit_rate', 18, 4)->nullable()->after('qty_per_batch');
        });
    }

    public function down(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            $table->dropColumn('batch_uom');
        });

        Schema::table('bom_items', function (Blueprint $table) {
            $table->dropColumn('unit_rate');
        });
    }
};
