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
            'payee_type' => 'VARCHAR(20) NULL',
            'payee_code' => 'VARCHAR(100) NULL',
            'payee_name' => 'VARCHAR(150) NULL',
            'pay_remark' => 'VARCHAR(255) NULL',
            'paid_at' => 'DATE NULL',
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
    }

    public function down(): void
    {
        foreach (['paid_at', 'pay_remark', 'payee_name', 'payee_code', 'payee_type'] as $name) {
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
