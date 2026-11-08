<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fitting_labours', function (Blueprint $table) {
            $table->string('entry_category', 20)->default('work')->change();
        });
    }

    public function down(): void
    {
        Schema::table('fitting_labours', function (Blueprint $table) {
            $table->string('entry_category', 10)->default('work')->change();
        });
    }
};
