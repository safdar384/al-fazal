<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use a raw ALTER TABLE statement inside try/catch to avoid metadata queries
        try {
            \DB::statement('ALTER TABLE `customerledgers` ADD COLUMN `accountType` VARCHAR(255) NULL AFTER `category`');
        } catch (\Exception $e) {
            // ignore if column already exists or the DB doesn't support this metadata call
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            \DB::statement('ALTER TABLE `customerledgers` DROP COLUMN `accountType`');
        } catch (\Exception $e) {
            // ignore
        }
    }
};
