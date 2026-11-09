<?php

namespace App\Http\Controllers;

use App\Models\FittingProductWork;
use App\Models\newProduct;
use App\Support\FittingModuleCache;
use App\Support\FittingWorkStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockManagementController extends Controller
{
    public function index(): View
    {
        $workStocks = FittingModuleCache::workStocks();

        $productStocks = newProduct::query()
            ->orderBy('productName')
            ->get(['productCode', 'productName', 'companyName', 'unitPerPackName', 'piecesPerUnit', 'stockQty', 'saleRate', 'costRate'])
            ->map(fn (newProduct $p) => [
                'productCode' => $p->productCode,
                'productName' => $p->productName,
                'companyName' => $p->companyName,
                'unitName' => FittingWorkStockService::normalizeUnitLabel($p->unitPerPackName),
                'piecesPerUnit' => (int) ($p->piecesPerUnit ?? 0),
                'stockQty' => (float) ($p->stockQty ?? 0),
            ]);

        $products = newProduct::query()
            ->orderBy('productName')
            ->get(['productCode', 'productName', 'companyName', 'unitPerPackName']);

        $workNames = FittingProductWork::query()
            ->where('status', 'Active')
            ->orderBy('work_name')
            ->pluck('work_name')
            ->unique()
            ->values();

        return view('Pages.stockManagement.stockManagement', [
            'workStocks' => $workStocks,
            'productStocks' => $productStocks,
            'products' => $products,
            'workNames' => $workNames,
        ]);
    }

    public function listWorkStocks(): JsonResponse
    {
        FittingModuleCache::flushWorkStocks();

        return response()->json(['data' => FittingModuleCache::workStocks()->values()]);
    }

    public function saveWorkStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'productCode' => 'required|string',
            'workName' => 'required|string|max:100',
            'qty' => 'required|numeric|min:0',
        ]);

        $product = newProduct::query()->where('productCode', $validated['productCode'])->first();
        if (! $product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 422);
        }

        try {
            $row = FittingWorkStockService::setQty(
                (string) $product->productCode,
                (string) $product->productName,
                (string) $validated['workName'],
                (float) $validated['qty'],
                (string) ($product->unitPerPackName ?? '')
            );

            return response()->json([
                'success' => true,
                'message' => 'Work stock saved',
                'data' => [
                    'productCode' => $row->product_code,
                    'productName' => $row->product_name,
                    'workName' => $row->work_name,
                    'unitName' => FittingWorkStockService::normalizeUnitLabel($row->unit_name),
                    'qty' => (float) $row->qty,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroyWorkStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'productCode' => 'required|string',
            'workName' => 'required|string|max:100',
        ]);

        try {
            FittingWorkStockService::deleteRow($validated['productCode'], $validated['workName']);

            return response()->json(['success' => true, 'message' => 'Work stock removed']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateProductStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'productCode' => 'required|string',
            'stockQty' => 'required|numeric|min:0',
        ]);

        $product = newProduct::query()->where('productCode', $validated['productCode'])->first();
        if (! $product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 422);
        }

        $product->update(['stockQty' => round((float) $validated['stockQty'], 2)]);
        FittingModuleCache::flushCatalog();

        return response()->json([
            'success' => true,
            'message' => 'Product stock updated',
            'data' => [
                'productCode' => $product->productCode,
                'stockQty' => (float) $product->stockQty,
            ],
        ]);
    }
}
