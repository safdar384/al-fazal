<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\SaleInvoice;
use App\Models\SaleInvoiceBankPayment;
use App\Models\SaleInvoiceItem;
use App\Support\BankLedgerQuery;
use App\Support\GeneralLedger;
use App\Support\SaleInvoiceCalculator;
use App\Support\SaleStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CashSaleService
{
    public function walkInCustomerCode(): string
    {
        return (string) config('sales.cash_customer_code', 'CASH');
    }

    public function nextInvoiceNumber(): string
    {
        $lastCash = SaleInvoice::query()
            ->where('invoiceNo', 'like', 'CS-%')
            ->orderByDesc('id')
            ->first();

        if ($lastCash && preg_match('/^CS-(\d+)$/', $lastCash->invoiceNo, $m)) {
            $nextNum = (int) $m[1] + 1;
        } else {
            $nextNum = 1;
        }

        return 'CS-'.str_pad((string) $nextNum, 5, '0', STR_PAD_LEFT);
    }

    /**
     * @return array{invoice: SaleInvoice, previewUrl: string}
     */
    public function store(array $validated): array
    {
        return DB::transaction(function () use ($validated) {
            $computed = SaleInvoiceCalculator::compute(
                $validated['items'],
                (float) ($validated['invoiceDiscount'] ?? 0)
            );

            $this->assertBankPayments($validated['bank_payments'], $computed['grandTotal']);

            $buyerName = trim((string) ($validated['buyer_name'] ?? ''));
            if ($buyerName === '') {
                $buyerName = 'Walk-in';
            }

            $invoice = SaleInvoice::create([
                'invoiceNo' => $validated['invoiceNo'],
                'invoiceDate' => $validated['invoiceDate'],
                'customerCode' => $this->walkInCustomerCode(),
                'customerName' => $buyerName,
                'city' => trim((string) ($validated['buyer_city'] ?? '')),
                'balance' => 0,
                'gudsName' => '',
                'corten' => 0,
                'biltyExpanse' => 0,
                'loaderExpanse' => 0,
                'biltyBalance' => 0,
                'comments' => $validated['comments'] ?? '',
                'noOfItems' => $computed['noOfItems'],
                'invoiceTotal' => $computed['invoiceTotal'],
                'invoiceDiscount' => $computed['invoiceDiscount'],
                'grandTotal' => $computed['grandTotal'],
                'status' => 'Active',
                'sale_type' => 'cash',
                'payment_status' => 'paid',
                'paid_amount' => $computed['grandTotal'],
            ]);

            $this->persistItems($invoice, $validated['invoiceNo'], $computed['items'], $computed['revenueRatio']);
            $this->syncBankPayments($invoice, $validated['invoiceDate'], $validated['invoiceNo'], $validated['bank_payments']);

            SaleStockService::deduct($computed['items']);

            GeneralLedger::postCashSaleInvoice(
                $validated['invoiceDate'],
                $validated['invoiceNo'],
                $computed['grandTotal']
            );

            return [
                'invoice' => $invoice,
                'previewUrl' => route('sale.invoice.preview', $invoice->invoiceNo),
            ];
        });
    }

    public function update(SaleInvoice $invoice, array $validated): SaleInvoice
    {
        if (($invoice->sale_type ?? 'credit') !== 'cash') {
            throw new InvalidArgumentException('Not a cash sale invoice.');
        }

        return DB::transaction(function () use ($invoice, $validated) {
            $invoiceNo = $invoice->invoiceNo;
            $oldItems = $invoice->items()->get(['productCode', 'variant_id', 'qty'])->map(fn ($row) => [
                'productCode' => $row->productCode,
                'variant_id' => $row->variant_id,
                'qty' => $row->qty,
            ])->all();

            $computed = SaleInvoiceCalculator::compute(
                $validated['items'],
                (float) ($validated['invoiceDiscount'] ?? 0)
            );

            $this->assertBankPayments($validated['bank_payments'], $computed['grandTotal']);
            SaleStockService::restore($oldItems);

            $buyerName = trim((string) ($validated['buyer_name'] ?? ''));
            if ($buyerName === '') {
                $buyerName = 'Walk-in';
            }

            $this->removeBankPayments($invoice);
            SaleInvoiceItem::query()->where('sale_invoice_id', $invoice->id)->delete();

            $invoice->update([
                'invoiceDate' => $validated['invoiceDate'],
                'customerName' => $buyerName,
                'city' => trim((string) ($validated['buyer_city'] ?? '')),
                'comments' => $validated['comments'] ?? '',
                'noOfItems' => $computed['noOfItems'],
                'invoiceTotal' => $computed['invoiceTotal'],
                'invoiceDiscount' => $computed['invoiceDiscount'],
                'grandTotal' => $computed['grandTotal'],
                'payment_status' => 'paid',
                'paid_amount' => $computed['grandTotal'],
                'balance' => 0,
            ]);

            $this->persistItems($invoice, $invoiceNo, $computed['items'], $computed['revenueRatio']);
            $this->syncBankPayments($invoice, $validated['invoiceDate'], $invoiceNo, $validated['bank_payments']);

            SaleStockService::deduct($computed['items']);

            GeneralLedger::voidByReference($invoiceNo);
            GeneralLedger::postCashSaleInvoice(
                $validated['invoiceDate'],
                $invoiceNo,
                $computed['grandTotal']
            );

            return $invoice->fresh(['items', 'bankPayments.bankAccount']);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function validateStore(Request $request): array
    {
        return $request->validate($this->storeRules());
    }

    /**
     * @return array<string, mixed>
     */
    public function validateUpdate(Request $request): array
    {
        return $request->validate($this->updateRules());
    }

    /**
     * @return array<string, string|array>
     */
    public function storeRules(): array
    {
        return array_merge($this->baseRules(), [
            'invoiceNo' => 'required|string|unique:sale_invoices,invoiceNo',
        ]);
    }

    /**
     * @return array<string, string|array>
     */
    public function updateRules(): array
    {
        return $this->baseRules();
    }

    /**
     * @return array<string, string|array>
     */
    protected function baseRules(): array
    {
        return [
            'invoiceDate' => 'required|date',
            'buyer_name' => 'nullable|string|max:255',
            'buyer_city' => 'nullable|string|max:255',
            'comments' => 'nullable|string',
            'invoiceDiscount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.productCode' => 'required|string',
            'items.*.productName' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.cost_rate' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'items.*.variant_id' => 'nullable|integer',
            'items.*.variant_name' => 'nullable|string',
            'items.*.variant_value' => 'nullable|string',
            'items.*.variant_added_price' => 'nullable|numeric',
            'bank_payments' => 'required|array|min:1',
            'bank_payments.*.bank_account' => 'required|string',
            'bank_payments.*.amount' => 'required|numeric|min:0.01',
        ];
    }

    /**
     * @param  array<int, array{bank_account: string, amount: mixed}>  $bankPayments
     */
    protected function assertBankPayments(array $bankPayments, float $grandTotal): void
    {
        $bankSum = 0.0;
        foreach ($bankPayments as $row) {
            $bankSum += (float) $row['amount'];
        }

        if (abs($bankSum - $grandTotal) > 0.02) {
            throw new InvalidArgumentException(
                'Bank payment total ('.number_format($bankSum, 2).') must equal Grand Total ('.number_format($grandTotal, 2).').'
            );
        }
    }

    protected function removeBankPayments(SaleInvoice $invoice): void
    {
        foreach (SaleInvoiceBankPayment::query()->where('sale_invoice_id', $invoice->id)->get() as $payment) {
            if ($payment->ledger_id) {
                DB::table('ledgers')->where('id', $payment->ledger_id)->delete();
            }
        }
        SaleInvoiceBankPayment::query()->where('sale_invoice_id', $invoice->id)->delete();
    }

    /**
     * @param  array<int, array{bank_account: string, amount: mixed}>  $bankPayments
     */
    protected function syncBankPayments(SaleInvoice $invoice, string $invoiceDate, string $invoiceNo, array $bankPayments): void
    {
        foreach ($bankPayments as $payRow) {
            $bank = BankAccount::query()->where('bankAccount', $payRow['bank_account'])->first();
            if (! $bank) {
                throw new InvalidArgumentException('Invalid bank account code: '.$payRow['bank_account']);
            }

            $amount = (float) $payRow['amount'];
            $ledgerId = DB::table('ledgers')->insertGetId([
                'entry_date' => $invoiceDate,
                'reference_no' => null,
                'market_voucher_id' => null,
                'account_id' => BankLedgerQuery::ensureGlAccount($bank),
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Cash Sale '.$invoiceNo.' — '.$bank->bankName,
                'category' => 'CashSaleBank',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('ledgers')->where('id', $ledgerId)->update([
                'reference_no' => 'CSB-'.$ledgerId,
            ]);

            SaleInvoiceBankPayment::create([
                'sale_invoice_id' => $invoice->id,
                'bank_account_id' => $bank->id,
                'amount' => $amount,
                'ledger_id' => $ledgerId,
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function persistItems(SaleInvoice $invoice, string $invoiceNo, array $items, float $revenueRatio): void
    {
        app(SaleInvoiceService::class)->persistItems($invoice, $invoiceNo, $items, $revenueRatio);
    }
}
