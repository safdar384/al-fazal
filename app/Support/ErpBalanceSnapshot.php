<?php

namespace App\Support;

use App\Models\BankAccount;
use App\Models\ExpenseLedger;
use App\Models\Labour;
use Illuminate\Support\Facades\DB;

/**
 * Point-in-time ERP balance totals for period closing.
 */
class ErpBalanceSnapshot
{
    /**
     * @return array<string, float>
     */
    public function capture(?string $asOfDate = null): array
    {
        $portfolio = PartyBalance::portfolioTotals();

        $bankBalance = 0.0;
        foreach (BankAccount::query()->get() as $bank) {
            $bankBalance += BankLedgerQuery::signedBalance($bank, $asOfDate);
        }

        $expenseBalance = (float) (ExpenseLedger::query()
            ->when($asOfDate, fn ($q) => $q->whereDate('entryDate', '<=', $asOfDate))
            ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as bal')
            ->value('bal') ?? 0);

        if ($expenseBalance < 0) {
            $expenseBalance = abs($expenseBalance);
        }

        $labourBalance = (float) Labour::query()->sum('balance');

        $stockBalance = (float) DB::table('product_info')
            ->selectRaw('COALESCE(SUM(COALESCE(stockQty, 0) * COALESCE(costRate, 0)), 0) as val')
            ->value('val');

        return [
            'customer_receivable' => round((float) $portfolio['total_receivable'], 2),
            'customer_payable' => round((float) $portfolio['total_payable'], 2),
            'bank_balance' => round($bankBalance, 2),
            'labour_balance' => round($labourBalance, 2),
            'expense_balance' => round($expenseBalance, 2),
            'stock_balance' => round($stockBalance, 2),
        ];
    }

    /**
     * @param  array<string, float>  $periodFigures  From ProfitLossReport::computeForPeriod
     * @param  array<string, float>  $balances  From capture()
     * @return array<string, float>
     */
    public function mergeForClosing(array $periodFigures, array $balances): array
    {
        return array_merge($periodFigures, $balances);
    }
}
