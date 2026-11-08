<?php

namespace App\Console\Commands;

use App\Support\TransactionalDataReset;
use Illuminate\Console\Command;

/**
 * @deprecated Use `php artisan db:reset-transactions` instead.
 */
class ClearTransactionalErpData extends Command
{
    protected $signature = 'erp:clear-transactions
                            {--force : Skip confirmation (maps to db:reset-transactions --yes --force)}';

    protected $description = '[Deprecated] Use db:reset-transactions. Clears transactional ERP data.';

    public function handle(TransactionalDataReset $reset): int
    {
        $this->warn('erp:clear-transactions is deprecated. Use: php artisan db:reset-transactions');

        if (! $this->option('force') && ! $this->confirm(
            'Continue with legacy clear command?',
            false
        )) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        try {
            $result = $reset->run(false);
        } catch (\Throwable $e) {
            $this->error('Cleanup failed: '.$e->getMessage());

            return self::FAILURE;
        }

        foreach ($result['truncated'] as $row) {
            $this->line(sprintf(' <info>%s</info> — truncated (%d rows).', $row['table'], $row['rows_before']));
        }

        foreach ($result['post_updates'] as $line) {
            $this->line(" <comment>{$line}</comment>");
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
