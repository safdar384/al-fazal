<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hasTable = DB::selectOne("
            SELECT COUNT(*) AS c FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'customer_cheques'
        ");
        if (! $hasTable || (int) $hasTable->c === 0) {
            return;
        }

        $cols = [
            'worker_id' => 'BIGINT UNSIGNED NULL',
            'worker_code' => 'VARCHAR(50) NULL',
            'worker_name' => 'VARCHAR(150) NULL',
        ];

        foreach ($cols as $name => $definition) {
            $exists = DB::selectOne("
                SELECT COUNT(*) AS c FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'customer_cheques'
                  AND COLUMN_NAME = ?
            ", [$name]);
            if ($exists && (int) $exists->c > 0) {
                continue;
            }
            DB::statement("ALTER TABLE `customer_cheques` ADD COLUMN `{$name}` {$definition}");
        }

        $idx = DB::selectOne("
            SELECT COUNT(*) AS c FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'customer_cheques'
              AND INDEX_NAME = 'customer_cheques_worker_id_index'
        ");
        if (! $idx || (int) $idx->c === 0) {
            DB::statement('ALTER TABLE `customer_cheques` ADD INDEX `customer_cheques_worker_id_index` (`worker_id`)');
        }
    }

    public function down(): void
    {
        foreach (['worker_name', 'worker_code', 'worker_id'] as $name) {
            $exists = DB::selectOne("
                SELECT COUNT(*) AS c FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'customer_cheques'
                  AND COLUMN_NAME = ?
            ", [$name]);
            if ($exists && (int) $exists->c > 0) {
                DB::statement("ALTER TABLE `customer_cheques` DROP COLUMN `{$name}`");
            }
        }
    }
};
