<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fitting_labours', function (Blueprint $table) {
            $table->string('entry_category', 10)->default('work')->after('labour_id');
            $table->string('description', 500)->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('fitting_labours', function (Blueprint $table) {
            $table->dropColumn(['entry_category', 'description']);
        });
    }
};
