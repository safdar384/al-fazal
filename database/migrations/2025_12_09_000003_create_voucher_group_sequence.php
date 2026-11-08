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
        // Create a sequence/counter table for voucher groups
        Schema::create('voucher_group_sequences', function (Blueprint $table) {
            $table->id(); // auto-increment
            $table->timestamps();
        });

        // Initialize with one row so first group gets ID 1
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
