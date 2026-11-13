<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DownloadsDomPdf;
use App\Models\Guds;
use App\Models\NewCustomer;
use App\Models\Order;
use App\Models\SaleInvoice;
use App\Services\SaleInvoiceService;
use App\Services\SaleReturnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SaleInvoiceController extends Controller
{
    use DownloadsDomPdf;

    public function __construct(
        protected SaleInvoiceService $sales,
        protected SaleReturnService $saleReturns
    ) {}

    public function index(): View
    {
        $data = $this->sales->formData();

        return view('Pages.Sale.saleInvoice', $data);
    }

    public function getCustomerDetails(string $customerCode): JsonResponse
    {
        $customer = NewCustomer::query()->where('custCode', $customerCode)->first();

        if (! $customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        $meta = \App\Support\PartyBalance::meta($customerCode);

        return response()->json([
            'custCode' => $customer->custCode,
            'custName' => $customer->custName,
            'custCity' => $customer->custCity,
            'balance' => $meta['balance'],
            'balance_label' => $meta['balance_label'],
            'balance_type' => $meta['balance_type'],
            'ledger_signed' => $meta['ledger_signed'],
        ]);
    }

    public function customerOrders(string $customerCode): JsonResponse
    {
        $orders = Order::query()
            ->where('customerCode', $customerCode)
            ->whereNull('converted_invoice_no')
            ->where('status', 'In Process')
            ->orderByDesc('orderDate')
            ->orderByDesc('id')
            ->get(['orderNo', 'orderDate', 'grandTotal', 'status']);

        return response()->json($orders->map(fn (Order $order) => [
            'orderNo' => $order->orderNo,
            'orderDate' => optional($order->orderDate)->format('d-M-Y'),
            'grandTotal' => number_format((float) $order->grandTotal, 2),
            'status' => $order->status,
        ])->values());
    }

    public function orderForInvoice(string $orderNo): JsonResponse
    {
        $order = Order::query()->with('items')->where('orderNo', $orderNo)->first();

        if (! $order
            || $order->converted_invoice_no
            || $order->status !== 'In Process'
        ) {
            return response()->json(['message' => 'Order is not available.'], 404);
        }

        return response()->json([
            'orderNo' => $order->orderNo,
            'gudsName' => $order->gudsName,
            'items' => $order->items->map(fn ($item) => [
                'productCode' => $item->productCode,
                'productName' => $item->productName,
                'qty' => (float) $item->qty,
                'rate' => (float) $item->rate,
                'discount' => (float) $item->discount,
                'amount' => (float) $item->amount,
            ])->values(),
        ]);
    }

    public function getProductDetails(string $productCode): JsonResponse
    {
        try {
            return response()->json(
                $this->sales->productDetails($productCode, request()->query('customerCode'))
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateInvoice($request);

            $result = $this->sales->store($validated);

            return response()->json([
                'success' => true,
                'message' => 'Invoice saved successfully!',
                'invoiceNo' => $result['invoice']->invoiceNo,
                'previewUrl' => $result['previewUrl'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getAllInvoices(Request $request): JsonResponse
    {
        $isReturn = $request->query('sale_type') === 'return';        $paginator = $isReturn
            ? $this->saleReturns->paginateReturns($request)
            : $this->sales->paginateInvoices($request);
        $amounts = $isReturn
            ? $this->saleReturns->lastListAmounts()
            : $this->sales->lastListAmounts();

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_amount' => $amounts['total_amount'],
                'credit_amount' => $amounts['credit_amount'],
                'cash_amount' => $amounts['cash_amount'],
            ],
        ]);
    }

    public function updateBiltyNo(Request $request, string $invoiceNo): JsonResponse
    {
        $validated = $request->validate([
            'biltyNo' => 'nullable|string|max:100',
        ]);

        try {
            $invoice = $this->sales->updateBiltyNo($invoiceNo, $validated['biltyNo'] ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Bilty No. saved.',
                'biltyNo' => $invoice->biltyNo,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function printSummary(Request $request): View|Response
    {
        $invoices = $request->query('sale_type') === 'return'
            ? $this->saleReturns->listReturnsForPrint($request)
            : $this->sales->listInvoicesForPrint($request);

        if ($request->boolean('download_pdf')) {
            return $this->downloadDomPdf(
                'Pages.Sale.saleSummaryPdf',
                compact('invoices'),
                'sale-summary-'.now()->format('YmdHis').'.pdf',
                'a4',
                'landscape'
            );
        }

        return view('Pages.Sale.saleSummaryPrint', compact('invoices'));
    }

    public function getInvoiceDetails(string $invoiceNo): JsonResponse
    {
        $invoice = SaleInvoice::query()
            ->with(['items', 'bankPayments.bankAccount'])
            ->where('invoiceNo', $invoiceNo)
            ->first();

        if (! $invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        return response()->json(['data' => $invoice]);
    }

    public function preview(string $invoiceNo): View
    {
        $invoice = SaleInvoice::query()
            ->with(['items', 'bankPayments.bankAccount'])
            ->where('invoiceNo', $invoiceNo)
            ->firstOrFail();

        $customer = NewCustomer::query()->where('custCode', $invoice->customerCode)->first();

        return view('Pages.Sale.invoicePreview', compact('invoice', 'customer'));
    }

    public function pdf(string $invoiceNo): Response
    {
        $invoice = SaleInvoice::query()
            ->with(['items', 'bankPayments.bankAccount'])
            ->where('invoiceNo', $invoiceNo)
            ->firstOrFail();

        $customer = NewCustomer::query()->where('custCode', $invoice->customerCode)->first();

        return $this->downloadDomPdf(
            'Pages.Sale.invoicePreviewPdf',
            compact('invoice', 'customer'),
            'sale-invoice-'.$invoiceNo.'.pdf'
        );
    }

    public function storeGuds(Request $request): JsonResponse
    {
        $request->validate([
            'gudsName' => 'required|string|max:255|unique:guds,gudsName',
            'gudsExpanse' => 'required|numeric|min:0',
            'gudsLoader' => 'required|numeric|min:0',
        ]);

        $guds = Guds::create([
            'gudsName' => strtoupper($request->gudsName),
            'gudsExpanse' => $request->gudsExpanse,
            'gudsLoader' => $request->gudsLoader,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Guds created successfully',
            'data' => $guds,
        ]);
    }

    public function getAllGuds(): JsonResponse
    {
        $guds = Guds::query()->where('status', 'active')->orderBy('gudsName')->get();

        return response()->json(['data' => $guds]);
    }

    public function saleSummary(): View
    {
        $customers = NewCustomer::query()
            ->excludeProductSupplier()
            ->orderBy('custName')
            ->get(['custCode', 'custName', 'custArea', 'custCity']);

        $areas = NewCustomer::query()
            ->select('custArea', 'custCity')
            ->whereNotNull('custArea')
            ->where('custArea', '<>', '')
            ->orderBy('custArea')
            ->get()
            ->groupBy('custArea')
            ->map(function ($group) {
                $firstWithCity = $group->firstWhere('custCity', '!=', '');

                return [
                    'code' => $group->first()->custArea,
                    'city' => strtoupper(($firstWithCity?->custCity) ?: ($group->first()->custCity ?: '')),
                ];
            })
            ->values();

        return view('Pages.Sale.saleSummary', compact('customers', 'areas'));
    }

    public function edit(string $invoiceNo): View|Response
    {
        $invoice = SaleInvoice::query()->with('items')->where('invoiceNo', $invoiceNo)->first();

        if (! $invoice) {
            abort(404, 'Invoice not found');
        }

        if (($invoice->sale_type ?? 'credit') === 'cash') {
            return redirect()->route('cash.sale.edit', ['invoiceNo' => $invoiceNo]);
        }

        $invoiceData = [
            'id' => (int) $invoice->id,
            'invoiceNo' => (string) $invoice->invoiceNo,
            'invoiceDate' => $invoice->invoiceDate ? $invoice->invoiceDate->format('Y-m-d') : '',
            'customerCode' => (string) $invoice->customerCode,
            'customerName' => (string) $invoice->customerName,
            'city' => (string) ($invoice->city ?? ''),
            'balance' => (float) ($invoice->balance ?? 0),
            'gudsName' => (string) ($invoice->gudsName ?? ''),
            'corten' => (int) ($invoice->corten ?? 0),
            'biltyExpanse' => (float) ($invoice->biltyExpanse ?? 0),
            'loaderExpanse' => (float) ($invoice->loaderExpanse ?? 0),
            'biltyBalance' => (float) ($invoice->biltyBalance ?? 0),
            'comments' => (string) ($invoice->comments ?? ''),
            'noOfItems' => (int) ($invoice->noOfItems ?? 0),
            'invoiceTotal' => (float) ($invoice->invoiceTotal ?? 0),
            'invoiceDiscount' => (float) ($invoice->invoiceDiscount ?? 0),
            'grandTotal' => (float) ($invoice->grandTotal ?? 0),
            'status' => (string) ($invoice->status ?? 'Active'),
            'items' => $invoice->items->map(fn ($item) => [
                'id' => (int) $item->id,
                'sale_invoice_id' => (int) $item->sale_invoice_id,
                'productCode' => (string) $item->productCode,
                'productName' => (string) $item->productName,
                'qty' => (int) $item->qty,
                'rate' => (float) $item->rate,
                'discount' => (float) ($item->discount ?? 0),
                'amount' => (float) $item->amount,
            ])->values()->toArray(),
        ];

        $form = $this->sales->formData();

        return view('Pages.Sale.editInvoice', [
            'invoice' => $invoice,
            'invoiceData' => $invoiceData,
            'customers' => $form['customers'],
            'products' => $form['products'],
            'guds' => $form['guds'],
        ]);
    }

    public function update(Request $request, string $invoiceNo): JsonResponse
    {
        try {
            $invoice = SaleInvoice::query()->where('invoiceNo', $invoiceNo)->first();

            if (! $invoice) {
                return response()->json(['success' => false, 'message' => 'Invoice not found'], 404);
            }

            $validated = $this->validateInvoice($request, false);
            $this->sales->update($invoice, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully!',
                'invoiceNo' => $invoice->invoiceNo,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteInvoice(string $invoiceNo): JsonResponse
    {
        try {
            $invoice = SaleInvoice::query()->where('invoiceNo', $invoiceNo)->first();

            if (! $invoice) {
                return response()->json(['success' => false, 'message' => 'Invoice not found'], 404);
            }

            $this->sales->delete($invoice);

            return response()->json(['success' => true, 'message' => 'Invoice deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateInvoice(Request $request, bool $requireInvoiceNo = true): array
    {
        $rules = [
            'invoiceDate' => 'required|date',
            'customerCode' => 'required|string',
            'customerName' => 'required|string',
            'city' => 'nullable|string',
            'balance' => 'nullable|numeric',
            'gudsName' => 'nullable|string',
            'corten' => 'nullable|integer|min:0',
            'biltyExpanse' => 'nullable|numeric|min:0',
            'loaderExpanse' => 'nullable|numeric|min:0',
            'biltyBalance' => 'nullable|numeric',
            'comments' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.productCode' => 'required|string',
            'items.*.productName' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.cost_rate' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'items.*.amount' => 'required|numeric|min:0',
            'invoiceDiscount' => 'nullable|numeric|min:0',
        ];

        if ($requireInvoiceNo) {
            // Invoice number is assigned server-side on create (INV-* sequence).
            $rules['orderNo'] = 'required|string';
            $rules['noOfItems'] = 'required|integer|min:1';
            $rules['invoiceTotal'] = 'required|numeric|min:0';
            $rules['grandTotal'] = 'required|numeric|min:0';
        } else {
            $rules['noOfItems'] = 'required|integer|min:1';
            $rules['invoiceTotal'] = 'required|numeric|min:0';
            $rules['grandTotal'] = 'required|numeric|min:0';
        }

        return $request->validate($rules, [
            'orderNo.required' => 'Select the customer order number.',
            'customerCode.required' => 'Please select a customer.',
            'customerName.required' => 'Please select a customer.',
            'items.required' => 'Add at least one product before saving.',
            'items.min' => 'Add at least one product before saving.',
            'items.*.productCode.required' => 'Each line must have a product.',
            'items.*.qty.required' => 'Quantity is required for each product.',
            'items.*.qty.min' => 'Quantity must be at least 1.',
            'noOfItems.min' => 'Add at least one product before saving.',
        ]);
    }
}
