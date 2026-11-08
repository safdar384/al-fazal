<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('labours', function (Blueprint $table) {
            $table->string('labour_type', 30)->default('salary_labour')->after('labourCatagory');
        });

        DB::table('labours')->whereRaw('LOWER(labourCatagory) = ?', ['fitter'])
            ->update(['labour_type' => 'fitter_labour']);
    }

    public function down(): void
    {
        Schema::table('labours', function (Blueprint $table) {
            $table->dropColumn('labour_type');
        });
    }
};
