<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransactionalDataReset
{
    /** @var array<string, string>|null lower logical => actual table name */
    private ?array $resolvedTableNames = null;

    /**
     * @return array{truncated: list<array{table: string, rows_before: int}>, skipped: list<string>, post_updates: list<string>}
     */
    public function run(bool $includeCacheTables = false): array
    {
        $truncated = [];
        $skipped = [];
        $postUpdates = [];

        $tables = config('transaction_reset.transactional_tables', []);
        if ($includeCacheTables) {
            $tables = array_merge($tables, config('transaction_reset.cache_tables', []));
        }

        $protected = array_map('strtolower', config('transaction_reset.protected_tables', []));

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tables as $logical) {
                $key = strtolower($logical);
                if (in_array($key, $protected, true)) {
                    $skipped[] = "{$logical} (protected)";

                    continue;
                }

                $actual = $this->resolveTableName($logical);
                if ($actual === null) {
                    $skipped[] = "{$logical} (not found)";

                    continue;
                }

                if (in_array(strtolower($actual), $protected, true)) {
                    $skipped[] = "{$actual} (protected)";

                    continue;
                }

                $before = (int) DB::table($actual)->count();
                DB::table($actual)->truncate();
                $truncated[] = ['table' => $actual, 'rows_before' => $before];
            }

            foreach (config('transaction_reset.post_reset_updates', []) as $logical => $columns) {
                $actual = $this->resolveTableName($logical);
                if ($actual === null || ! is_array($columns)) {
                    continue;
                }

                $affected = DB::table($actual)->update($columns);
                $cols = implode(', ', array_keys($columns));
                $postUpdates[] = "{$actual}: set {$cols} ({$affected} rows)";
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        return [
            'truncated' => $truncated,
            'skipped' => $skipped,
            'post_updates' => $postUpdates,
        ];
    }

    /**
     * @return array<string, int> table => row count
     */
    public function verifySample(): array
    {
        $out = [];
        foreach (['sale_invoices', 'purchase_invoices', 'orders', 'customerledgers', 'ledgers', 'expenses'] as $logical) {
            $actual = $this->resolveTableName($logical);
            $out[$actual ?? $logical] = $actual ? (int) DB::table($actual)->count() : -1;
        }

        return $out;
    }

    public function resolveTableName(string $logical): ?string
    {
        $map = $this->getResolvedTableNames();
        $key = strtolower($logical);

        return $map[$key] ?? null;
    }

    /**
     * @return array<string, string> lower => actual
     */
    private function getResolvedTableNames(): array
    {
        if ($this->resolvedTableNames !== null) {
            return $this->resolvedTableNames;
        }

        $names = $this->discoverTableNames();
        if ($names === []) {
            $candidates = array_merge(
                config('transaction_reset.transactional_tables', []),
                config('transaction_reset.cache_tables', []),
                array_keys(config('transaction_reset.post_reset_updates', [])),
            );
            foreach ($candidates as $logical) {
                if (Schema::hasTable($logical) && ! in_array($logical, $names, true)) {
                    $names[] = $logical;
                }
            }
        }

        $this->resolvedTableNames = [];
        foreach ($names as $name) {
            $this->resolvedTableNames[strtolower((string) $name)] = (string) $name;
        }

        return $this->resolvedTableNames;
    }

    /**
     * @return list<string>
     */
    private function discoverTableNames(): array
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $database = DB::getDatabaseName();
            $rows = DB::select(
                'SELECT TABLE_NAME AS n FROM information_schema.tables WHERE table_schema = ? AND TABLE_TYPE = \'BASE TABLE\'',
                [$database]
            );

            return array_values(array_filter(array_map(function ($row) {
                $r = (array) $row;

                return $r['n'] ?? $r['TABLE_NAME'] ?? null;
            }, $rows)));
        }

        if ($driver === 'sqlite') {
            $rows = DB::select("SELECT name AS n FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'");

            return array_values(array_map(fn ($row) => (string) (($row->n ?? $row->name) ?? ''), $rows));
        }

        if ($driver === 'pgsql') {
            $rows = DB::select("SELECT tablename AS n FROM pg_tables WHERE schemaname = 'public'");

            return array_values(array_map(fn ($row) => (string) ($row->n ?? ''), $rows));
        }

        return [];
    }
}
