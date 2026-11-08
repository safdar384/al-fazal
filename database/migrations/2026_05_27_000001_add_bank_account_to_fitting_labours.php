<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fitting_labours', function (Blueprint $table) {
            $table->string('bank_account', 100)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('fitting_labours', function (Blueprint $table) {
            $table->dropColumn('bank_account');
        });
    }
};
