<?php

namespace App\Console\Commands;

use App\Models\NewCustomer;
use App\Support\PartyBalance;
use Illuminate\Console\Command;

class SyncPartyBalances extends Command
{
    protected $signature = 'party:sync-balances';

    protected $description = 'Recalculate custBalance for all customers and suppliers from customerledgers';

    public function handle(): int
    {
        $codes = NewCustomer::query()->pluck('custCode');

        $bar = $this->output->createProgressBar($codes->count());
        $bar->start();

        foreach ($codes as $code) {
            PartyBalance::syncCustBalance($code);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Synced '.$codes->count().' party balances from ledger.');

        return self::SUCCESS;
    }
}
