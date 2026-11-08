<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\TransactionalDataReset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResetTransactionalData extends Command
{
    protected $signature = 'db:reset-transactions
                            {--force : Skip the typed confirmation phrase (yes/no prompt still applies unless --yes)}
                            {--yes : Skip the yes/no confirmation prompt}
                            {--admin-email= : Admin email (required when APP_ENV=production)}
                            {--actor= : Name recorded in the audit log}
                            {--with-cache : Also truncate cache tables}';

    protected $description = 'Safely truncate transactional ERP data while preserving customers, products, and areas.';

    public function handle(TransactionalDataReset $reset): int
    {
        if (! $this->assertAllowedToRun()) {
            return self::FAILURE;
        }

        if (! $this->assertAdminActor()) {
            return self::FAILURE;
        }

        $this->displayWarning();

        if (! $this->option('yes') && ! $this->confirm('Do you want to continue?', false)) {
            $this->warn('Cancelled — no changes were made.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirmTypedPhrase()) {
            $this->warn('Cancelled — confirmation phrase did not match.');

            return self::SUCCESS;
        }

        $actor = $this->resolveActor();

        $this->newLine();
        $this->info('Truncating transactional tables…');

        try {
            $result = $reset->run((bool) $this->option('with-cache'));
        } catch (\Throwable $e) {
            $this->error('Reset failed: '.$e->getMessage());
            Log::error('db:reset-transactions failed', [
                'actor' => $actor,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }

        foreach ($result['truncated'] as $row) {
            $this->line(sprintf('  <info>%s</info> truncated (%d rows removed).', $row['table'], $row['rows_before']));
        }

        foreach ($result['skipped'] as $skip) {
            $this->line("  <fg=gray>Skipped: {$skip}</>");
        }

        foreach ($result['post_updates'] as $line) {
            $this->line("  <comment>{$line}</comment>");
        }

        $verify = $reset->verifySample();
        $this->newLine();
        $this->info('Verification (should be 0):');
        foreach ($verify as $table => $count) {
            $this->line("  {$table}: {$count}");
        }

        Log::warning('ERP transactional database reset completed', [
            'command' => 'db:reset-transactions',
            'actor' => $actor,
            'admin_email' => $this->option('admin-email'),
            'environment' => app()->environment(),
            'database' => config('database.connections.'.config('database.default').'.database'),
            'truncated_tables' => array_column($result['truncated'], 'table'),
            'skipped' => $result['skipped'],
            'with_cache' => (bool) $this->option('with-cache'),
            'executed_at' => now()->toIso8601String(),
        ]);

        $this->newLine();
        $this->info('Transaction reset completed successfully. Master data (customers, products, areas) was preserved.');

        return self::SUCCESS;
    }

    private function assertAllowedToRun(): bool
    {
        if (config('transaction_reset.enabled')) {
            return true;
        }

        if (app()->environment('local', 'testing')) {
            $this->warn('TRANSACTION_RESET_ENABLED is false — allowing run in '.app()->environment().' environment only.');

            return true;
        }

        $this->error('Transaction reset is disabled.');
        $this->line('Set TRANSACTION_RESET_ENABLED=true in .env after taking a full database backup.');

        return false;
    }

    private function assertAdminActor(): bool
    {
        if (! app()->environment('production')) {
            return true;
        }

        $email = $this->option('admin-email');
        if (! $email) {
            $this->error('Production requires --admin-email= of an admin user.');

            return false;
        }

        $user = User::query()->where('email', $email)->first();
        if (! $user || ! $user->isAdmin()) {
            $this->error('The provided --admin-email is not an active admin account.');

            return false;
        }

        return true;
    }

    private function displayWarning(): void
    {
        $this->newLine();
        $this->warn('╔══════════════════════════════════════════════════════════════════╗');
        $this->warn('║  DATABASE TRANSACTION RESET — DESTRUCTIVE OPERATION              ║');
        $this->warn('╚══════════════════════════════════════════════════════════════════╝');
        $this->newLine();
        $this->line('This will <fg=red>permanently remove</> all rows from transactional tables, including:');
        $this->line('  • Sales & purchase invoices (and line items)');
        $this->line('  • Orders');
        $this->line('  • Customer / supplier ledger entries');
        $this->line('  • Expenses, recovery vouchers, GL ledger entries');
        $this->line('  • Stock quantities on variants will be zeroed');
        $this->line('  • Party balances (custBalance) will be zeroed');
        $this->newLine();
        $this->info('Preserved (NOT deleted):');
        $this->line('  • Customers (newCustomers)');
        $this->line('  • Products (product_info, variants setup)');
        $this->line('  • Areas (customerareas)');
        $this->line('  • Categories, packing, companies, bank accounts, users');
        $this->newLine();
        $this->error('Take a full database backup BEFORE continuing. This cannot be undone.');
        $this->newLine();
    }

    private function confirmTypedPhrase(): bool
    {
        $phrase = (string) config('transaction_reset.confirmation_phrase', 'RESET TRANSACTIONS');
        $typed = $this->ask("Type \"{$phrase}\" to confirm");

        return trim((string) $typed) === $phrase;
    }

    private function resolveActor(): string
    {
        if ($email = $this->option('admin-email')) {
            return 'admin:'.$email;
        }

        if ($actor = $this->option('actor')) {
            return (string) $actor;
        }

        $osUser = getenv('USERNAME') ?: getenv('USER');

        return $osUser ? 'cli:'.$osUser : 'cli:unknown';
    }
}
