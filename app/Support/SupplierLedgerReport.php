<?php

namespace App\Support;

use App\Models\CustomerLedger;
use App\Models\NewCustomer;
use App\Models\SupplierTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Unified supplier ledger:
 * purchase/sale invoice lines + supplier_transactions (+ items) + other party ledger.
 */
class SupplierLedgerReport
{
    public const TYPE_PURCHASE = 'purchase';

    public const TYPE_SALE = 'sale';

    public const TYPE_PAYMENT = 'payment';

    public const TYPE_DISCOUNT = 'discount';

    public const TYPE_OTHER = 'other';

    /**
     * @return array{rows: Collection<int, array<string, mixed>>, summary: array<string, mixed>}
     */
    public function build(string $supplierCode, string $fromDate, string $toDate, ?string $transactionType = null): array
    {
        $party = NewCustomer::query()->where('custCode', $supplierCode)->first();
        $isPayable = PartyBalance::isPayableParty($party, $supplierCode);
        $balanceLabel = PartyBalance::balanceLabel($party, $supplierCode);

        $openingSigned = PartyBalance::ledgerSignedBalance($supplierCode, $fromDate);
        $runningSigned = $openingSigned;
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        $events = collect()
            ->merge($this->purchaseLineEvents($supplierCode, $fromDate, $toDate))
            ->merge($this->transportLineEvents($supplierCode, $fromDate, $toDate))
            ->merge($this->saleLineEvents($supplierCode, $fromDate, $toDate))
            ->merge($this->supplierTransactionEvents($supplierCode, $fromDate, $toDate))
            ->merge($this->partyLedgerEvents($supplierCode, $fromDate, $toDate));

        if ($transactionType && $transactionType !== 'all') {
            $events = $events->filter(fn (array $e) => $e['transaction_type'] === $transactionType)->values();
        }

        $rows = collect([
            $this->openingRow($fromDate, $openingSigned, $isPayable, $balanceLabel),
        ]);

        foreach ($events->sortBy([['sort_at', 'asc'], ['sort_seq', 'asc']]) as $event) {
            $debit = round((float) $event['debit'], 2);
            $credit = round((float) $event['credit'], 2);
            $totalDebit += $debit;
            $totalCredit += $credit;
            $runningSigned = round($runningSigned + $debit - $credit, 2);

            $rows->push([
                'date' => $event['date'],
                'invoice_no' => $event['invoice_no'],
                'transaction_type' => $event['transaction_type'],
                'product_name' => $event['product_name'],
                'qty' => $event['qty'],
                'rate' => $event['rate'],
                'discount' => $event['discount'],
                'final_rate' => $event['final_rate'] ?? $event['rate'],
                'amount' => $event['amount'],
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => $this->displayBalance($runningSigned, $isPayable),
                'is_opening' => false,
                'source' => $event['source'] ?? null,
            ]);
        }

        return [
            'rows' => $rows,
            'summary' => [
                'total_debit' => round($totalDebit, 2),
                'total_credit' => round($totalCredit, 2),
                'opening_balance' => $this->displayBalance($openingSigned, $isPayable),
                'closing_balance' => $this->displayBalance($runningSigned, $isPayable),
                'balance_label' => $balanceLabel,
                'balance_type' => $isPayable ? PartyBalance::TYPE_PAYABLE : PartyBalance::TYPE_RECEIVABLE,
                'current_balance' => PartyBalance::displayBalance($supplierCode),
            ],
        ];
    }

    /**
     * Item-level discount (%) + proportional invoice discount → final rate & amount.
     *
     * @return array{
     *   rate: float,
     *   discount: float,
     *   final_rate: float,
     *   amount: float,
     *   line_subtotal: float
     * }
     */
    public static function computeLinePricing(
        float $qty,
        float $rate,
        float $itemDiscountPercent,
        float $lineAmountStored,
        float $invoiceLineTotal,
        float $invoiceGrandTotal
    ): array {
        $qty = max(0, $qty);
        $rate = max(0, $rate);
        $itemDiscountPercent = max(0, min(100, $itemDiscountPercent));

        $lineSubtotal = $qty > 0 ? ($qty * $rate) : 0.0;
        if ($lineSubtotal <= 0 && $lineAmountStored > 0) {
            $lineSubtotal = $lineAmountStored;
        }

        $afterItemDiscount = $lineSubtotal * (1 - ($itemDiscountPercent / 100));
        if ($lineAmountStored > 0 && abs($afterItemDiscount - $lineAmountStored) < 0.02) {
            $afterItemDiscount = $lineAmountStored;
        }

        $ratio = ($invoiceLineTotal > 0 && $invoiceGrandTotal >= 0)
            ? ($invoiceGrandTotal / $invoiceLineTotal)
            : 1.0;
        $ratio = max(0, min(1.0, $ratio));

        $finalAmount = round($afterItemDiscount * $ratio, 2);
        $finalRate = $qty > 0 ? round($finalAmount / $qty, 2) : 0.0;

        return [
            'rate' => round($rate, 2),
            'discount' => round($itemDiscountPercent, 2),
            'final_rate' => $finalRate,
            'amount' => $finalAmount,
            'line_subtotal' => round($lineSubtotal, 2),
        ];
    }

    protected function purchaseLineEvents(string $supplierCode, string $fromDate, string $toDate): Collection
    {
        $lines = DB::table('purchase_invoice_items as pii')
            ->join('purchase_invoices as pi', 'pi.id', '=', 'pii.purchase_invoice_id')
            ->where('pi.supplierCode', $supplierCode)
            ->whereBetween('pi.invoiceDate', [$fromDate, $toDate])
            ->select([
                'pi.invoiceDate',
                'pi.invoiceNo',
                'pi.invoiceTotal',
                'pi.grandTotal',
                'pii.productName',
                'pii.qty',
                'pii.rate',
                'pii.discount',
                'pii.amount',
                'pii.created_at',
                'pii.id as line_id',
            ])
            ->get();

        $invoiceTotals = $lines->groupBy('invoiceNo')->map(function ($group) {
            return (float) $group->sum(fn ($r) => (float) $r->amount);
        });

        return $lines->map(function ($line) use ($invoiceTotals) {
            $invoiceNo = $line->invoiceNo;
            $lineTotal = $invoiceTotals[$invoiceNo] ?? (float) $line->amount;
            $grandTotal = (float) $line->grandTotal;
            $invoiceTotal = (float) $line->invoiceTotal;
            if ($lineTotal <= 0) {
                $lineTotal = $invoiceTotal > 0 ? $invoiceTotal : 1;
            }

            $pricing = self::computeLinePricing(
                (float) $line->qty,
                (float) $line->rate,
                (float) $line->discount,
                (float) $line->amount,
                $lineTotal,
                $grandTotal > 0 ? $grandTotal : $lineTotal
            );

            $sortAt = $line->created_at
                ? Carbon::parse($line->created_at)
                : Carbon::parse($line->invoiceDate)->startOfDay();

            return [
                'sort_at' => $sortAt->format('Y-m-d H:i:s'),
                'sort_seq' => (int) $line->line_id,
                'date' => Carbon::parse($line->invoiceDate)->format('Y-m-d'),
                'invoice_no' => $invoiceNo,
                'transaction_type' => self::TYPE_PURCHASE,
                'product_name' => $line->productName,
                'qty' => (float) $line->qty,
                'rate' => $pricing['rate'],
                'discount' => $pricing['discount'],
                'final_rate' => $pricing['final_rate'],
                'amount' => $pricing['amount'],
                'debit' => 0.0,
                'credit' => $pricing['amount'],
            ];
        });
    }

    /**
     * Freight posted on the transport party's ledger: weight, rate, and amount.
     */
    protected function transportLineEvents(string $partyCode, string $fromDate, string $toDate): Collection
    {
        $lines = DB::table('purchase_invoices')
            ->where('transportCode', $partyCode)
            ->whereBetween('invoiceDate', [$fromDate, $toDate])
            ->where('transportAmount', '>', 0)
            ->orderBy('invoiceDate')
            ->orderBy('id')
            ->get([
                'id',
                'invoiceDate',
                'invoiceNo',
                'vehicleNumber',
                'dispatchNo',
                'weight',
                'transportRate',
                'transportAmount',
            ]);

        return $lines->map(function ($line) {
            $weight = (float) $line->weight;
            $rate = (float) $line->transportRate;
            $amount = round((float) $line->transportAmount, 2);
            $vehicle = trim((string) ($line->vehicleNumber ?? ''));
            $dispatch = trim((string) ($line->dispatchNo ?? ''));
            $label = 'Transport';
            if ($vehicle !== '') {
                $label .= ' | Vehicle '.$vehicle;
            }
            if ($dispatch !== '') {
                $label .= ' | Dispatch '.$dispatch;
            }

            return [
                'sort_at' => Carbon::parse($line->invoiceDate)->startOfDay()->format('Y-m-d H:i:s'),
                'sort_seq' => (int) $line->id,
                'date' => Carbon::parse($line->invoiceDate)->format('Y-m-d'),
                'invoice_no' => $line->invoiceNo,
                'transaction_type' => 'transport',
                'product_name' => $label,
                'qty' => $weight,
                'rate' => $rate,
                'discount' => null,
                'final_rate' => $rate,
                'amount' => $amount,
                'debit' => 0.0,
                'credit' => $amount,
                'source' => 'transport',
            ];
        })->values();
    }

    protected function saleLineEvents(string $supplierCode, string $fromDate, string $toDate): Collection
    {
        $lines = DB::table('sale_invoice_items as sii')
            ->join('sale_invoices as si', 'si.id', '=', 'sii.sale_invoice_id')
            ->where('si.customerCode', $supplierCode)
            ->whereBetween('si.invoiceDate', [$fromDate, $toDate])
            ->select([
                'si.invoiceDate',
                'si.invoiceNo',
                'si.invoiceTotal',
                'si.grandTotal',
                'sii.productName',
                'sii.qty',
                'sii.rate',
                'sii.discount',
                'sii.amount',
                'sii.created_at',
                'sii.id as line_id',
            ])
            ->get();

        $invoiceTotals = $lines->groupBy('invoiceNo')->map(function ($group) {
            return (float) $group->sum(fn ($r) => (float) $r->amount);
        });

        return $lines->map(function ($line) use ($invoiceTotals) {
            $invoiceNo = $line->invoiceNo;
            $lineTotal = $invoiceTotals[$invoiceNo] ?? (float) $line->amount;
            $grandTotal = (float) $line->grandTotal;
            $invoiceTotal = (float) $line->invoiceTotal;
            if ($lineTotal <= 0) {
                $lineTotal = $invoiceTotal > 0 ? $invoiceTotal : 1;
            }

            $pricing = self::computeLinePricing(
                (float) $line->qty,
                (float) $line->rate,
                (float) $line->discount,
                (float) $line->amount,
                $lineTotal,
                $grandTotal > 0 ? $grandTotal : $lineTotal
            );

            $sortAt = $line->created_at
                ? Carbon::parse($line->created_at)
                : Carbon::parse($line->invoiceDate)->startOfDay();

            return [
                'sort_at' => $sortAt->format('Y-m-d H:i:s'),
                'sort_seq' => (int) $line->line_id,
                'date' => Carbon::parse($line->invoiceDate)->format('Y-m-d'),
                'invoice_no' => $invoiceNo,
                'transaction_type' => self::TYPE_SALE,
                'product_name' => $line->productName,
                'qty' => (float) $line->qty,
                'rate' => $pricing['rate'],
                'discount' => $pricing['discount'],
                'final_rate' => $pricing['final_rate'],
                'amount' => $pricing['amount'],
                'debit' => $pricing['amount'],
                'credit' => 0.0,
            ];
        });
    }

    /**
     * Supplier Transaction module (supplier_transactions + items) with qty / rate.
     */
    protected function supplierTransactionEvents(string $supplierCode, string $fromDate, string $toDate): Collection
    {
        $lines = DB::table('supplier_transaction_items as sti')
            ->join('supplier_transactions as st', 'st.id', '=', 'sti.supplier_transaction_id')
            ->where('st.supplier_code', $supplierCode)
            ->where('st.status', 'Active')
            ->whereBetween('st.txn_date', [$fromDate, $toDate])
            ->select([
                'st.id as txn_id',
                'st.txn_no',
                'st.txn_type',
                'st.entry_type',
                'st.txn_date',
                'st.remarks',
                'st.created_at',
                'sti.id as line_id',
                'sti.product_code',
                'sti.product_name',
                'sti.variant_name',
                'sti.variant_value',
                'sti.qty',
                'sti.rate',
                'sti.amount',
            ])
            ->orderBy('st.txn_date')
            ->orderBy('st.id')
            ->orderBy('sti.id')
            ->get();

        return $lines->map(function ($line) {
            $qty = round((float) ($line->qty ?? 0), 2);
            $rate = round((float) ($line->rate ?? 0), 2);
            $amount = round((float) ($line->amount ?? ($qty * $rate)), 2);
            $txnType = (string) $line->txn_type;
            $entryType = strtolower((string) ($line->entry_type ?? ''));

            $debit = 0.0;
            $credit = 0.0;
            $reportType = self::TYPE_OTHER;

            if ($txnType === SupplierTransaction::TYPE_PURCHASE) {
                $credit = $amount;
                $reportType = self::TYPE_PURCHASE;
            } elseif ($txnType === SupplierTransaction::TYPE_SALE) {
                $debit = $amount;
                $reportType = self::TYPE_SALE;
            } elseif ($txnType === SupplierTransaction::TYPE_CASH) {
                $reportType = self::TYPE_PAYMENT;
                if (in_array($entryType, ['receive', 'credit'], true)) {
                    $credit = $amount;
                } else {
                    $debit = $amount;
                }
            }

            $productName = trim((string) ($line->product_name ?? ''));
            if ($txnType === SupplierTransaction::TYPE_CASH) {
                $productName = $productName !== '' && $productName !== 'CASH'
                    ? $productName
                    : trim((string) ($line->remarks ?: 'Cash'));
            } else {
                $variant = trim(($line->variant_name ?? '').' '.($line->variant_value ?? ''));
                if ($variant !== '') {
                    $productName = trim($productName.' ('.$variant.')');
                }
            }

            $isCash = $txnType === SupplierTransaction::TYPE_CASH;
            $sortAt = $line->created_at
                ? Carbon::parse($line->created_at)
                : Carbon::parse($line->txn_date)->startOfDay();

            return [
                'sort_at' => $sortAt->format('Y-m-d H:i:s'),
                'sort_seq' => (int) $line->line_id,
                'date' => Carbon::parse($line->txn_date)->format('Y-m-d'),
                'invoice_no' => (string) $line->txn_no,
                'transaction_type' => $reportType,
                'product_name' => $productName !== '' ? $productName : '—',
                'qty' => $isCash ? null : $qty,
                'rate' => $isCash ? null : $rate,
                'discount' => null,
                'final_rate' => $isCash ? null : $rate,
                'amount' => $amount,
                'debit' => $debit,
                'credit' => $credit,
                'source' => 'supplier_transaction',
            ];
        })->values();
    }

    protected function partyLedgerEvents(string $supplierCode, string $fromDate, string $toDate): Collection
    {
        $saleCategory = PartyLedger::category('sale_invoice');
        $purchaseCategory = PartyLedger::category('purchase');
        $discountCategory = PartyLedger::category('discount');
        $supplierTxnCategories = PartyLedger::supplierTransactionCategories();

        // Invoice lines + supplier_transactions cover those postings — skip here to avoid double count.
        $excludedCategories = array_values(array_unique(array_merge([
            $saleCategory,
            'Sale Invoice',
            'SaleInvoice',
            $purchaseCategory,
            'Purchase',
            'Purchase Invoice',
            'PurchaseInvoice',
            PartyLedger::category('transport'),
            'Transport',
        ], $supplierTxnCategories)));

        $rows = CustomerLedger::query()
            ->where('customerCode', $supplierCode)
            ->where('statuse', '0')
            ->whereBetween('customerDate', [$fromDate, $toDate])
            ->where(function ($q) {
                $q->whereNull('invoiceNumber')
                    ->orWhere('invoiceNumber', 'not like', 'ST-%');
            })
            ->where(function ($q) use ($excludedCategories) {
                $q->whereNull('category')
                    ->orWhereNotIn('category', $excludedCategories);
            })
            ->orderBy('customerDate')
            ->orderBy('id')
            ->get();

        return $rows->map(function (CustomerLedger $ledger) use ($discountCategory) {
            $debit = (float) ($ledger->debit ?? 0);
            $credit = (float) ($ledger->credit ?? 0);
            if ($debit <= 0 && $credit <= 0) {
                return null;
            }

            $category = trim((string) ($ledger->category ?? ''));
            $type = $this->resolveLedgerTransactionType($category, $discountCategory);
            $sortAt = Carbon::parse($ledger->customerDate)->startOfDay()->addSeconds(min((int) $ledger->id, 86399));

            return [
                'sort_at' => $sortAt->format('Y-m-d H:i:s'),
                'sort_seq' => (int) $ledger->id,
                'date' => Carbon::parse($ledger->customerDate)->format('Y-m-d'),
                'invoice_no' => $ledger->invoiceNumber ?: ('LED-'.$ledger->id),
                'transaction_type' => $type,
                'product_name' => trim((string) ($ledger->description ?: $category ?: 'Ledger')),
                'qty' => null,
                'rate' => null,
                'discount' => null,
                'final_rate' => null,
                'amount' => round(max($debit, $credit), 2),
                'debit' => $debit,
                'credit' => $credit,
                'source' => 'party_ledger',
            ];
        })->filter()->values();
    }

    protected function resolveLedgerTransactionType(string $category, string $discountCategory): string
    {
        $key = strtolower(str_replace([' ', '_'], '', $category));

        if (str_contains($key, 'discount') || strcasecmp($category, $discountCategory) === 0) {
            return self::TYPE_DISCOUNT;
        }
        if (in_array($key, ['recovery', 'accounttransaction', 'payment', 'transfer'], true)
            || str_contains($key, 'payment')) {
            return self::TYPE_PAYMENT;
        }

        return self::TYPE_OTHER;
    }

    protected function openingRow(string $fromDate, float $openingSigned, bool $isSupplier, string $balanceLabel): array
    {
        return [
            'date' => Carbon::parse($fromDate)->format('Y-m-d'),
            'invoice_no' => '-',
            'transaction_type' => 'Opening',
            'product_name' => 'Opening '.$balanceLabel,
            'qty' => null,
            'rate' => null,
            'discount' => null,
            'final_rate' => null,
            'amount' => null,
            'debit' => 0.0,
            'credit' => 0.0,
            'running_balance' => $this->displayBalance($openingSigned, $isSupplier),
            'is_opening' => true,
        ];
    }

    protected function displayBalance(float $signedBalance, bool $isSupplier): float
    {
        return $isSupplier ? round(-$signedBalance, 2) : round($signedBalance, 2);
    }
}
