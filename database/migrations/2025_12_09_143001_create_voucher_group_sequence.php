<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Duplicate of 2025_12_09_000003 — keep idempotent for fresh/sqlite test migrates
        if (Schema::hasTable('voucher_group_sequences')) {
            return;
        }

        Schema::create('voucher_group_sequences', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        DB::table('voucher_group_sequences')->insert(['created_at' => now(), 'updated_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_group_sequences');
    }
};
