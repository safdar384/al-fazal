<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearSeedData extends Command
{
    protected $signature = 'seed:clear {--force : Run without confirmation}';

    protected $description = 'Delete all data inserted by seeders (truncate seeded tables).';

    protected array $tables = [
        'accounts',
        'customerareas',
        'bank_accounts',
        'product_catagory',
        'colours',
        'product_company',
        'customerledgers',
        'newcustomers',
        'packing',
        'product_info',
    ];

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will TRUNCATE seeded tables and DELETE all their data. Continue?')) {
            $this->info('Operation cancelled.');

            return Command::SUCCESS;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($this->tables as $table) {
            try {
                DB::table($table)->truncate();
                $this->line("<info>Truncated:</info> {$table}");
            } catch (\Throwable $e) {
                $this->error("Failed: {$table} - ".$e->getMessage());
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('Seed data cleared successfully.');

        return Command::SUCCESS;
    }
}
