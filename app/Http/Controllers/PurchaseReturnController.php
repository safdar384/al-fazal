<?php

namespace App\Http\Controllers;

use App\Models\NewCustomer;
use App\Models\PurchaseReturn;
use App\Services\PurchaseInvoiceService;
use App\Services\PurchaseReturnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseReturnController extends Controller
{
    public function __construct(
        protected PurchaseReturnService $returns,
        protected PurchaseInvoiceService $purchases
    ) {}

    public function index(): View
    {
        $data = $this->returns->formData();
        $data['editReturn'] = null;

        return view('Pages.Purchase.purchaseReturn', $data);
    }

    public function edit(string $returnNo): View
    {
        $purchaseReturn = PurchaseReturn::query()
            ->with('items')
            ->where('returnNo', $returnNo)
            ->firstOrFail();

        return view('Pages.Purchase.purchaseReturn', $this->returns->editFormData($purchaseReturn));
    }

    public function preview(string $returnNo): View
    {
        $purchaseReturn = PurchaseReturn::query()
            ->with('items')
            ->where('returnNo', $returnNo)
            ->firstOrFail();

        $supplier = NewCustomer::query()
            ->where('custCode', $purchaseReturn->supplierCode)
            ->first();

        return view('Pages.Purchase.purchaseReturnPreview', compact('purchaseReturn', 'supplier'));
    }

    public function getProductDetails(string $productCode): JsonResponse
    {
        try {
            return response()->json(
                $this->purchases->productDetails($productCode, request()->query('supplierCode'))
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateReturn($request);

        try {
            $result = $this->returns->store($validated);
            $returnNo = $result['return']->returnNo;

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'returnNo' => $returnNo,
                'goodQtyTotal' => (float) $result['return']->goodQtyTotal,
                'damageQtyTotal' => (float) $result['return']->damageQtyTotal,
                'grandTotal' => (float) $result['return']->grandTotal,
                'previewUrl' => route('purchase.return.preview', $returnNo),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Could not save purchase return.'], 500);
        }
    }

    public function update(Request $request, string $returnNo): JsonResponse
    {
        $purchaseReturn = PurchaseReturn::query()->where('returnNo', $returnNo)->first();
        if (! $purchaseReturn) {
            return response()->json(['success' => false, 'message' => 'Purchase return not found.'], 404);
        }

        $validated = $this->validateReturn($request);

        try {
            $result = $this->returns->update($purchaseReturn, $validated);
            $savedNo = $result['return']->returnNo;

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'returnNo' => $savedNo,
                'goodQtyTotal' => (float) $result['return']->goodQtyTotal,
                'damageQtyTotal' => (float) $result['return']->damageQtyTotal,
                'grandTotal' => (float) $result['return']->grandTotal,
                'previewUrl' => route('purchase.return.preview', $savedNo),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Could not update purchase return.'], 500);
        }
    }

    public function destroy(string $returnNo): JsonResponse
    {
        try {
            $purchaseReturn = PurchaseReturn::query()->where('returnNo', $returnNo)->first();
            if (! $purchaseReturn) {
                return response()->json(['success' => false, 'message' => 'Purchase return not found.'], 404);
            }

            $this->returns->delete($purchaseReturn);

            return response()->json(['success' => true, 'message' => 'Purchase return deleted successfully.']);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Could not delete purchase return.',
            ], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateReturn(Request $request): array
    {
        return $request->validate([
            'supplierCode' => ['required', 'string', 'max:50'],
            'supplierName' => ['nullable', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:100'],
            'returnDate' => ['required', 'date'],
            'originalInvoiceNo' => ['nullable', 'string', 'max:50'],
            'comments' => ['nullable', 'string', 'max:1000'],
            'invoiceDiscount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.productCode' => ['required', 'string'],
            'items.*.productName' => ['nullable', 'string'],
            'items.*.variant_id' => ['nullable', 'integer'],
            'items.*.variant_name' => ['nullable', 'string'],
            'items.*.variant_value' => ['nullable', 'string'],
            'items.*.variant_added_price' => ['nullable', 'numeric'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.cost_rate' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.condition' => ['nullable', 'in:good,damage'],
        ]);
    }
}
