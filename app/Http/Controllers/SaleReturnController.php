<?php

namespace App\Http\Controllers;

use App\Models\NewCustomer;
use App\Models\SaleReturn;
use App\Services\SaleInvoiceService;
use App\Services\SaleReturnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleReturnController extends Controller
{
    public function __construct(
        protected SaleReturnService $returns,
        protected SaleInvoiceService $saleInvoices
    ) {}

    public function index(): View
    {
        $data = $this->returns->formData();
        $data['editReturn'] = null;

        return view('Pages.Sale.saleReturn', $data);
    }

    public function edit(string $returnNo): View
    {
        $saleReturn = SaleReturn::query()
            ->with('items')
            ->where('returnNo', $returnNo)
            ->firstOrFail();

        return view('Pages.Sale.saleReturn', $this->returns->editFormData($saleReturn));
    }

    public function preview(string $returnNo): View
    {
        $saleReturn = SaleReturn::query()
            ->with('items')
            ->where('returnNo', $returnNo)
            ->firstOrFail();

        $customer = NewCustomer::query()
            ->where('custCode', $saleReturn->customerCode)
            ->first();

        return view('Pages.Sale.returnPreview', compact('saleReturn', 'customer'));
    }

    public function getProductDetails(string $productCode): JsonResponse
    {
        try {
            return response()->json(
                $this->saleInvoices->productDetails($productCode, request()->query('customerCode'))
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customerCode' => ['required', 'string', 'max:50'],
            'customerName' => ['nullable', 'string', 'max:150'],
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
                'previewUrl' => route('sale.return.preview', $returnNo),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Could not save sale return.'], 500);
        }
    }

    public function update(Request $request, string $returnNo): JsonResponse
    {
        $saleReturn = SaleReturn::query()->where('returnNo', $returnNo)->first();
        if (! $saleReturn) {
            return response()->json(['success' => false, 'message' => 'Sale return not found.'], 404);
        }

        $validated = $request->validate([
            'customerCode' => ['required', 'string', 'max:50'],
            'customerName' => ['nullable', 'string', 'max:150'],
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

        try {
            $result = $this->returns->update($saleReturn, $validated);
            $savedNo = $result['return']->returnNo;

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'returnNo' => $savedNo,
                'goodQtyTotal' => (float) $result['return']->goodQtyTotal,
                'damageQtyTotal' => (float) $result['return']->damageQtyTotal,
                'grandTotal' => (float) $result['return']->grandTotal,
                'previewUrl' => route('sale.return.preview', $savedNo),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Could not update sale return.'], 500);
        }
    }

    public function destroy(string $returnNo): JsonResponse
    {
        try {
            $saleReturn = SaleReturn::query()->where('returnNo', $returnNo)->first();
            if (! $saleReturn) {
                return response()->json(['success' => false, 'message' => 'Sale return not found.'], 404);
            }

            $this->returns->delete($saleReturn);

            return response()->json(['success' => true, 'message' => 'Sale return deleted successfully.']);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Could not delete sale return.',
            ], 500);
        }
    }
}
