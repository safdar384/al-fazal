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
        if (! Schema::hasTable('recovery_bank_distributions')) {
            return;
        }

        if (Schema::hasColumn('recovery_bank_distributions', 'distribution_date')) {
            return;
        }

        Schema::table('recovery_bank_distributions', function (Blueprint $table) {
            $table->date('distribution_date')->nullable()->after('reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recovery_bank_distributions', function (Blueprint $table) {
            $table->dropColumn('distribution_date');
        });
    }
};
