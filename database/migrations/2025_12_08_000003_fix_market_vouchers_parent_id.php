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
        // 1) Try to drop any existing foreign key on market_vouchers_id (might reference wrong table)
        try {
            Schema::table('market_vouchers', function (Blueprint $table) {
                $table->dropForeign(['market_vouchers_id']);
            });
        } catch (\Exception $e) {
            // ignore if it doesn't exist
            \Log::info('No existing foreign key to drop on market_vouchers.market_vouchers_id: '.$e->getMessage());
        }

        // 2) Ensure no NULLs remain: set market_vouchers_id = id where null
        DB::statement('UPDATE market_vouchers SET market_vouchers_id = id WHERE market_vouchers_id IS NULL');

        // 3) Modify column to NOT NULL using raw SQL (avoids requiring doctrine/dbal)
        // MySQL / MariaDB expected; use ALTER TABLE MODIFY
        try {
            DB::statement('ALTER TABLE market_vouchers MODIFY market_vouchers_id BIGINT UNSIGNED NOT NULL');
        } catch (\Exception $e) {
            \Log::error('Could not alter market_vouchers.market_vouchers_id to NOT NULL: '.$e->getMessage());
        }

        // 4) Add proper self-referencing foreign key to market_vouchers.id
        try {
            Schema::table('market_vouchers', function (Blueprint $table) {
                $table->foreign('market_vouchers_id')->references('id')->on('market_vouchers')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            \Log::error('Could not add self-referencing foreign key on market_vouchers.market_vouchers_id: '.$e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('market_vouchers', function (Blueprint $table) {
                $table->dropForeign(['market_vouchers_id']);
            });
        } catch (\Exception $e) {
            \Log::info('No foreign key to drop during down(): '.$e->getMessage());
        }

        // Make column nullable again
        try {
            DB::statement('ALTER TABLE market_vouchers MODIFY market_vouchers_id BIGINT UNSIGNED NULL');
        } catch (\Exception $e) {
            \Log::error('Could not alter market_vouchers.market_vouchers_id to NULL in down(): '.$e->getMessage());
        }

        // Note: we deliberately do not restore the original (incorrect) foreign key to ledgers.
    }
};
