<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_info', function (Blueprint $table) {
            $table->enum('productType', ['Raw', 'Finished'])->default('Finished')->after('companyName');
            $table->foreignId('uom_id')->nullable()->after('productType')->constrained('units');
        });
    }

    public function down(): void
    {
        Schema::table('product_info', function (Blueprint $table) {
            $table->dropConstrainedForeignId('uom_id');
            $table->dropColumn('productType');
        });
    }
};
