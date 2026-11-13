<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DownloadsDomPdf;
use App\Models\CustomerProductRate;
use App\Models\NewCustomer;
use App\Models\newProduct;
use App\Models\PurchaseInvoice;
use App\Services\PurchaseInvoiceService;
use App\Services\PurchaseReturnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class PurchaseInvoiceController extends Controller
{
    use DownloadsDomPdf;

    public function __construct(
        protected PurchaseInvoiceService $purchases,
        protected PurchaseReturnService $purchaseReturns
    ) {}

    public function index(): View
    {
        return view('Pages.Purchase.purchaseInvoice', $this->purchases->formData());
    }

    public function getSupplierDetails(string $supplierCode): JsonResponse
    {
        try {
            return response()->json($this->purchases->supplierDetails($supplierCode));
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function getSupplierProducts(string $supplierCode): JsonResponse
    {
        $specialRates = CustomerProductRate::query()
            ->where('customerCode', $supplierCode)
            ->where('status', 'Active')
            ->where('is_active', true)
            ->forRateType(CustomerProductRate::TYPE_PURCHASE)
            ->get(['productCode', 'variant_id', 'variant_name', 'variant_value', 'specialRate']);

        if ($specialRates->isEmpty()) {
            return response()->json(['products' => []]);
        }

        $processedProducts = [];
        foreach ($specialRates as $rate) {
            $product = newProduct::query()->where('productCode', $rate->productCode)->first();
            if (! $product) {
                continue;
            }
            $key = $product->productCode;
            if (! isset($processedProducts[$key])) {
                $processedProducts[$key] = [
                    'productCode' => $product->productCode,
                    'productName' => $product->productName,
                    'companyName' => $product->companyName,
                    'purchaseRate' => $product->purchaseRate,
                    'standardRate' => $product->purchaseRate,
                    'variants' => [],
                ];
            }
            if ($rate->variant_id) {
                $processedProducts[$key]['variants'][] = [
                    'id' => $rate->variant_id,
                    'name' => $rate->variant_name,
                    'value' => $rate->variant_value,
                    'specialRate' => $rate->specialRate,
                ];
            } else {
                $processedProducts[$key]['purchaseRate'] = $rate->specialRate;
                $processedProducts[$key]['specialRate'] = $rate->specialRate;
                $processedProducts[$key]['hasSpecialRate'] = true;
            }
        }

        return response()->json(['products' => array_values($processedProducts)]);
    }

    public function getProductDetails(string $productCode): JsonResponse
    {
        try {
            return response()->json(
                $this->purchases->productDetails($productCode, request()->query('supplierCode'))
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateInvoice($request);
            $result = $this->purchases->store($validated);

            return response()->json([
                'success' => true,
                'message' => 'Purchase saved',
                'invoiceNo' => $result['invoice']->invoiceNo,
                'previewUrl' => $result['previewUrl'],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function preview(string $invoiceNo): View
    {
        $invoice = PurchaseInvoice::query()
            ->with(['items', 'payments', 'supplier'])
            ->where('invoiceNo', $invoiceNo)
            ->firstOrFail();

        $transport = null;
        if ($invoice->transportCode) {
            $transport = NewCustomer::query()
                ->where('custCode', $invoice->transportCode)
                ->first(['custCode', 'custName', 'bussnessName', 'custCity']);
        }

        $transportDisplayName = trim((string) (
            ($transport->bussnessName ?? null)
            ?: ($transport->custName ?? null)
            ?: ($invoice->transportName ?? '')
        ));

        return view('Pages.Purchase.purchaseInvoicePreview', [
            'invoice' => $invoice,
            'supplier' => $invoice->supplier,
            'transport' => $transport,
            'transportDisplayName' => $transportDisplayName !== '' ? $transportDisplayName : null,
        ]);
    }

    public function pdf(string $invoiceNo): Response
    {
        $invoice = PurchaseInvoice::query()
            ->with(['items', 'payments', 'supplier'])
            ->where('invoiceNo', $invoiceNo)
            ->firstOrFail();

        $transport = null;
        if ($invoice->transportCode) {
            $transport = NewCustomer::query()
                ->where('custCode', $invoice->transportCode)
                ->first(['custCode', 'custName', 'bussnessName', 'custCity']);
        }

        $transportDisplayName = trim((string) (
            ($transport->bussnessName ?? null)
            ?: ($transport->custName ?? null)
            ?: ($invoice->transportName ?? '')
        ));

        return $this->downloadDomPdf(
            'Pages.Purchase.purchaseInvoicePreviewPdf',
            [
                'invoice' => $invoice,
                'supplier' => $invoice->supplier,
                'transport' => $transport,
                'transportDisplayName' => $transportDisplayName !== '' ? $transportDisplayName : null,
            ],
            'purchase-invoice-'.$invoiceNo.'.pdf'
        );
    }

    public function getAllInvoices(Request $request)
    {
        $isReturn = $request->query('invoice_type') === 'return'
            || $request->query('purchase_type') === 'return';

        $paginator = $isReturn
            ? $this->purchaseReturns->paginateReturns($request)
            : $this->purchases->paginateInvoices($request);

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function printInvoices(Request $request): View|Response
    {
        $isReturn = $request->query('invoice_type') === 'return'
            || $request->query('purchase_type') === 'return';

        $invoices = $isReturn
            ? $this->purchaseReturns->listReturnsForPrint($request)
            : $this->purchases->listForPrint($request);

        if ($request->boolean('download_pdf')) {
            return $this->downloadDomPdf(
                'Pages.Purchase.purchaseSummaryPdf',
                compact('invoices'),
                'purchase-summary-'.now()->format('YmdHis').'.pdf',
                'a4',
                'landscape'
            );
        }

        return view('Pages.Purchase.purchaseSummaryPrint', compact('invoices'));
    }

    public function purchaseSummary(): View
    {
        $data = $this->purchases->formData();

        return view('Pages.Purchase.purchaseSummary', [
            'suppliers' => $data['suppliers'],
        ]);
    }

    public function deleteInvoice(string $invoiceNo): JsonResponse
    {
        try {
            $invoice = PurchaseInvoice::query()->where('invoiceNo', $invoiceNo)->firstOrFail();
            $this->purchases->delete($invoice);

            return response()->json(['success' => true, 'message' => 'Invoice deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function edit(string $invoiceNo): View
    {
        $invoice = PurchaseInvoice::query()->with('items')->where('invoiceNo', $invoiceNo)->firstOrFail();
        $data = $this->purchases->formData();

        return view('Pages.Purchase.editPurchaseInvoice', array_merge($data, compact('invoice')));
    }

    public function update(Request $request, string $invoiceNo): JsonResponse
    {
        try {
            $invoice = PurchaseInvoice::query()->where('invoiceNo', $invoiceNo)->firstOrFail();
            $validated = $this->validateInvoice($request, false);
            $this->purchases->update($invoice, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Purchase updated',
                'invoiceNo' => $invoice->invoiceNo,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function recordPayment(Request $request, string $invoiceNo): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'payment_date' => 'required|date',
                'notes' => 'nullable|string|max:500',
            ]);

            $invoice = PurchaseInvoice::query()->where('invoiceNo', $invoiceNo)->firstOrFail();
            $result = $this->purchases->addPayment(
                $invoice,
                (float) $validated['amount'],
                $validated['payment_date'],
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded',
                'paid_amount' => $result['invoice']->paid_amount,
                'payment_status' => $result['invoice']->payment_status,
                'balance_due' => $result['invoice']->balanceDue(),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
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
            'supplierCode' => 'required|string',
            'supplierName' => 'required|string',
            'city' => 'nullable|string',
            'balance' => 'nullable|numeric',
            'corten' => 'nullable|integer|min:0',
            'biltyExpanse' => 'nullable|numeric|min:0',
            'loaderExpanse' => 'nullable|numeric|min:0',
            'biltyBalance' => 'nullable|numeric',
            'comments' => 'nullable|string',
            'transportCode' => 'nullable|string|max:50',
            'transportName' => 'nullable|string|max:255',
            'vehicleNumber' => 'nullable|string|max:50',
            'dispatchNo' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0',
            'transportRate' => 'nullable|numeric|min:0',
            'transportAmount' => 'nullable|numeric|min:0',
            'invoiceDiscount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.productCode' => 'required|string',
            'items.*.productName' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'items.*.variant_id' => 'nullable|integer',
        ];

        if ($requireInvoiceNo) {
            $rules['invoiceNo'] = 'required|string|unique:purchase_invoices,invoiceNo';
        }

        return $request->validate($rules);
    }
}
