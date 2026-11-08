<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profit_loss_closings', function (Blueprint $table) {
            $table->dropUnique('profit_loss_closings_closing_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('profit_loss_closings', function (Blueprint $table) {
            $table->unique('closing_date');
        });
    }
};
