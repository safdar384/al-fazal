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
        // 1) Drop the self-referencing foreign key if it exists
        try {
            Schema::table('market_vouchers', function (Blueprint $table) {
                $table->dropForeign(['market_vouchers_id']);
            });
        } catch (\Exception $e) {
            \Log::info('No self-referencing FK to drop: '.$e->getMessage());
        }

        // 2) Create an index on market_vouchers_id for fast grouping queries
        try {
            Schema::table('market_vouchers', function (Blueprint $table) {
                $table->index('market_vouchers_id');
            });
        } catch (\Exception $e) {
            \Log::info('Could not create index on market_vouchers_id: '.$e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('market_vouchers', function (Blueprint $table) {
                $table->dropIndex(['market_vouchers_id']);
            });
        } catch (\Exception $e) {
            \Log::info('Could not drop index on market_vouchers_id: '.$e->getMessage());
        }
    }
};
