<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\newProduct;
use App\Models\SaleInvoice;
use App\Services\CashSaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CashSaleController extends Controller
{
    /** @deprecated Use config('sales.cash_customer_code') */
    public const WALK_IN_CUSTOMER_CODE = 'CASH';

    public function __construct(
        protected CashSaleService $cashSales
    ) {}

    public function index()
    {
        $products = newProduct::with('variants')->orderBy('productName', 'asc')->get();
        $bankAccounts = BankAccount::orderBy('bankName', 'asc')
            ->get(['id', 'bankAccount', 'bankName', 'branchCode']);

        return view('Pages.Sale.cashSale', [
            'products' => $products,
            'bankAccounts' => $bankAccounts,
            'nextInvoiceNo' => $this->cashSales->nextInvoiceNumber(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $this->cashSales->validateStore($request);
            $result = $this->cashSales->store($validated);

            return response()->json([
                'success' => true,
                'message' => 'Cash invoice saved successfully!',
                'invoiceNo' => $result['invoice']->invoiceNo,
                'previewUrl' => $result['previewUrl'],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function edit(string $invoiceNo)
    {
        $invoice = SaleInvoice::with(['items', 'bankPayments.bankAccount'])
            ->where('invoiceNo', $invoiceNo)
            ->firstOrFail();

        if (($invoice->sale_type ?? 'credit') !== 'cash') {
            abort(404);
        }

        $products = newProduct::with('variants')->orderBy('productName', 'asc')->get();
        $bankAccounts = BankAccount::orderBy('bankName', 'asc')
            ->get(['id', 'bankAccount', 'bankName', 'branchCode']);

        $invoiceData = [
            'invoiceNo' => $invoice->invoiceNo,
            'invoiceDate' => $invoice->invoiceDate->format('Y-m-d'),
            'buyer_name' => $invoice->customerName,
            'buyer_city' => (string) ($invoice->city ?? ''),
            'comments' => (string) ($invoice->comments ?? ''),
            'invoiceDiscount' => (float) ($invoice->invoiceDiscount ?? 0),
            'bank_payments' => $invoice->bankPayments->map(function ($p) {
                return [
                    'bank_account' => $p->bankAccount->bankAccount ?? '',
                    'amount' => (float) $p->amount,
                ];
            })->values()->all(),
            'items' => $invoice->items->map(function ($item) {
                return [
                    'productCode' => $item->productCode,
                    'productName' => $item->productName,
                    'variant_id' => $item->variant_id,
                    'variant_name' => $item->variant_name,
                    'variant_value' => $item->variant_value,
                    'variant_added_price' => (float) ($item->variant_added_price ?? 0),
                    'qty' => (int) $item->qty,
                    'rate' => (float) $item->rate,
                    'discount' => (float) ($item->discount ?? 0),
                    'amount' => (float) $item->amount,
                    'cost_rate' => (float) ($item->cost_rate ?? 0),
                ];
            })->values()->all(),
        ];

        return view('Pages.Sale.editCashInvoice', compact('invoice', 'invoiceData', 'products', 'bankAccounts'));
    }

    public function update(Request $request, string $invoiceNo): JsonResponse
    {
        try {
            $invoice = SaleInvoice::where('invoiceNo', $invoiceNo)->firstOrFail();
            if (($invoice->sale_type ?? 'credit') !== 'cash') {
                abort(404);
            }

            $validated = $this->cashSales->validateUpdate($request);
            $invoice = $this->cashSales->update($invoice, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Cash invoice updated successfully!',
                'invoiceNo' => $invoice->invoiceNo,
                'previewUrl' => route('sale.invoice.preview', $invoice->invoiceNo),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
