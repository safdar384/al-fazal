<?php

namespace App\Support;

use App\Models\CustomerLedger;
use App\Models\ExpenseLedger;
use App\Models\ProfitLossClosing;
use App\Models\PurchaseInvoice;
use App\Models\SaleInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProfitLossReport
{
    protected ?ErpBalanceSnapshot $balanceSnapshot = null;

    protected function balanceSnapshot(): ErpBalanceSnapshot
    {
        return $this->balanceSnapshot ??= new ErpBalanceSnapshot;
    }

    /**
     * @return array{from: string, to: string}
     */
    public function resolveDateRange(Request $request): array
    {
        $defaultFrom = now()->startOfMonth()->format('Y-m-d');
        $defaultTo = now()->format('Y-m-d');

        try {
            $from = Carbon::parse($request->input('from_date', $defaultFrom))->format('Y-m-d');
            $to = Carbon::parse($request->input('to_date', $defaultTo))->format('Y-m-d');
        } catch (\Throwable) {
            $from = $defaultFrom;
            $to = $defaultTo;
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return ['from' => $from, 'to' => $to];
    }

    /**
     * @return array<string, float|int>
     */
    public function computeForPeriod(string $from, string $to): array
    {
        $sales = SaleInvoice::query()
            ->whereBetween('invoiceDate', [$from, $to]);

        $saleAmount = (float) (clone $sales)->sum('grandTotal');
        $saleBilty = (float) (clone $sales)->sum('biltyExpanse') + (float) (clone $sales)->sum('loaderExpanse');

        $costAmount = (float) DB::table('sale_invoice_items as sii')
            ->join('sale_invoices as si', 'si.id', '=', 'sii.sale_invoice_id')
            ->whereBetween('si.invoiceDate', [$from, $to])
            ->selectRaw('SUM(COALESCE(sii.qty, 0) * COALESCE(sii.cost_rate, 0)) as total')
            ->value('total');

        $purchaseAmount = (float) PurchaseInvoice::query()
            ->whereBetween('invoiceDate', [$from, $to])
            ->sum('grandTotal');

        $expenseRows = ExpenseLedger::query()
            ->whereBetween('entryDate', [$from, $to])
            ->get(['debit', 'credit']);

        $expenseAmount = (float) $expenseRows->sum(fn ($r) => (float) ($r->debit ?? 0) - (float) ($r->credit ?? 0));
        if ($expenseAmount < 0) {
            $expenseAmount = abs($expenseAmount);
        }

        $ledgerDiscount = (float) CustomerLedger::query()
            ->where('category', PartyLedger::category('discount'))
            ->whereBetween('customerDate', [$from, $to])
            ->sum('credit');

        $ledgerBilty = (float) CustomerLedger::query()
            ->whereBetween('customerDate', [$from, $to])
            ->where(function ($q) {
                $q->where('description', 'like', '%bilty%')
                    ->orWhere('description', 'like', '%freight%');
            })
            ->sum('debit');

        $voucherDiscount = $ledgerDiscount + $ledgerBilty;

        $grossProfit = $saleAmount - $costAmount;
        // Sale bilty/loader adds to profit; expenses and voucher discount reduce profit.
        $netProfit = $grossProfit
            + $saleBilty
            - $expenseAmount
            - $voucherDiscount;

        return [
            'sale_amount' => round($saleAmount, 2),
            'cost_amount' => round($costAmount, 2),
            'gross_profit' => round($grossProfit, 2),
            'purchase_amount' => round($purchaseAmount, 2),
            'sale_bilty_amount' => round($saleBilty, 2),
            'expense_amount' => round($expenseAmount, 2),
            'labor_amount' => 0,
            'ledger_discount_amount' => round($ledgerDiscount, 2),
            'ledger_bilty_amount' => round($ledgerBilty, 2),
            'voucher_discount_amount' => round($voucherDiscount, 2),
            'net_profit' => round($netProfit, 2),
        ];
    }

    /**
     * @return Collection<int, ProfitLossClosing>
     */
    public function closingHistory(int $limit = 50, ?string $filterFrom = null, ?string $filterTo = null): Collection
    {
        $query = ProfitLossClosing::query()
            ->orderByDesc('closed_at')
            ->orderByDesc('id');

        if ($filterFrom && $filterTo) {
            $query->whereDate('closed_at', '>=', $filterFrom)
                ->whereDate('closed_at', '<=', $filterTo);
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function findClosing(int $id): ?ProfitLossClosing
    {
        return ProfitLossClosing::query()->find($id);
    }

    public function erpAllTotalBalanceFromClosing(ProfitLossClosing $closing): float
    {
        return round(
            (float) ($closing->customer_receivable ?? 0)
            + (float) ($closing->bank_balance ?? 0)
            + (float) ($closing->labour_balance ?? 0)
            + (float) ($closing->stock_balance ?? 0)
            - (float) ($closing->customer_payable ?? 0)
            - (float) ($closing->expense_balance ?? 0),
            2
        );
    }

    public function previousClosingBefore(ProfitLossClosing $current): ?ProfitLossClosing
    {
        $closedAt = $current->closed_at;

        return ProfitLossClosing::query()
            ->when($closedAt, function ($q) use ($current, $closedAt) {
                $q->where(function ($inner) use ($current, $closedAt) {
                    $inner->where('closed_at', '<', $closedAt)
                        ->orWhere(function ($same) use ($current, $closedAt) {
                            $same->where('closed_at', $closedAt)
                                ->where('id', '<', $current->id);
                        });
                });
            }, fn ($q) => $q->where('id', '<', $current->id))
            ->orderByDesc('closed_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Pre closing all-total + period net profit vs current closing all-total.
     *
     * @return array{
     *     pre_closing_balance: float,
     *     pre_closing: ProfitLossClosing|null,
     *     now_profit: float,
     *     expected_total_balance: float,
     *     current_all_total_balance: float,
     *     balance_difference: float,
     *     is_balanced: bool
     * }
     */
    public function closingReconciliation(ProfitLossClosing $current): array
    {
        $previous = $this->previousClosingBefore($current);
        $preBalance = $previous ? $this->erpAllTotalBalanceFromClosing($previous) : 0.0;
        $nowProfit = (float) $current->net_profit;
        $expected = round($preBalance + $nowProfit, 2);
        $currentTotal = $this->erpAllTotalBalanceFromClosing($current);
        $difference = round($expected - $currentTotal, 2);

        return [
            'pre_closing_balance' => $preBalance,
            'pre_closing' => $previous,
            'now_profit' => $nowProfit,
            'expected_total_balance' => $expected,
            'current_all_total_balance' => $currentTotal,
            'balance_difference' => $difference,
            'is_balanced' => abs($difference) < 0.01,
        ];
    }

    /**
     * @return array<string, float>
     */
    public function fullClosingPreview(string $from, string $to): array
    {
        $period = $this->computeForPeriod($from, $to);
        $balances = $this->balanceSnapshot()->capture($to);

        return array_merge($period, $balances);
    }

    public function storeClosing(string $from, string $to, ?string $notes = null): ProfitLossClosing
    {
        $data = $this->fullClosingPreview($from, $to);

        return ProfitLossClosing::create([
            'from_date' => $from,
            'to_date' => $to,
            'closing_date' => now()->toDateString(), // calendar date; multiple closings per day allowed
            'sale_amount' => $data['sale_amount'],
            'cost_amount' => $data['cost_amount'],
            'gross_profit' => $data['gross_profit'],
            'sale_bilty_amount' => $data['sale_bilty_amount'],
            'expense_amount' => $data['expense_amount'],
            'labor_amount' => $data['labor_amount'] ?? 0,
            'ledger_discount_amount' => $data['ledger_discount_amount'],
            'ledger_bilty_amount' => $data['ledger_bilty_amount'],
            'voucher_discount_amount' => $data['voucher_discount_amount'],
            'net_profit' => $data['net_profit'],
            'customer_receivable' => $data['customer_receivable'],
            'customer_payable' => $data['customer_payable'],
            'bank_balance' => $data['bank_balance'],
            'labour_balance' => $data['labour_balance'],
            'expense_balance' => $data['expense_balance'],
            'stock_balance' => $data['stock_balance'],
            'purchase_amount' => $data['purchase_amount'],
            'closed_at' => now(),
            'notes' => $notes,
        ]);
    }
}
