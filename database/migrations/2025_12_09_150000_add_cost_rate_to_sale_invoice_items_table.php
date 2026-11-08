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
        Schema::table('sale_invoice_items', function (Blueprint $table) {
            $table->decimal('cost_rate', 15, 2)->nullable()->default(0)->after('rate')->comment('Cost rate for profit calculation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_invoice_items', function (Blueprint $table) {
            $table->dropColumn('cost_rate');
        });
    }
};
