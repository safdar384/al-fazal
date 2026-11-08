<?php

namespace App\Http\Controllers;

use App\Models\CustomerProductRate;
use App\Models\NewCustomer;
use App\Models\newProduct;
use App\Support\PartyBalance;
use App\Support\SupplierModuleCache;
use Illuminate\Http\Request;

class CustomerDiscountController extends Controller
{
    public function index()
    {
        $customers = NewCustomer::query()
            ->excludeProductSupplier()
            ->orderBy('custName', 'asc')
            ->get(['custCode', 'custName', 'custArea', 'custdiscount']);
        $products = newProduct::with('variants')->orderBy('productName', 'asc')->get();

        $productVariants = $products->mapWithKeys(function ($product) {
            return [
                $product->productCode => $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'name' => $variant->variantName,
                        'value' => $variant->variantValue,
                        'additionalPrice' => (float) ($variant->additionalPrice ?? 0),
                        'additionalCost' => (float) ($variant->additionalCost ?? 0),
                        'stockQty' => (float) ($variant->stockQty ?? 0),
                    ];
                })->values()->all(),
            ];
        });

        return view('Pages.Setup.customerDiscount', compact('customers', 'products', 'productVariants'));
    }

    public function getCustomerInfo($customerCode)
    {
        $customer = NewCustomer::where('custCode', $customerCode)->first();

        if (! $customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        $meta = PartyBalance::meta($customerCode);

        return response()->json([
            'custCode' => $customer->custCode,
            'custName' => $customer->custName,
            'custArea' => $customer->custArea,
            'custCity' => $customer->custCity,
            'custdiscount' => $customer->custdiscount ?? 0,
            'balance' => $meta['balance'],
            'balance_label' => $meta['balance_label'],
            'balance_type' => $meta['balance_type'],
        ]);
    }

    public function getProductInfo($productCode)
    {
        $product = newProduct::with('variants')->where('productCode', $productCode)->first();

        if (! $product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json([
            'productCode' => $product->productCode,
            'productName' => $product->productName,
            'companyName' => $product->companyName,
            'purchaseRate' => $product->costRate ?? 0,
            'saleRate' => $product->saleRate ?? 0,
            'stock' => $product->stockQty ?? 0,
            'variants' => $product->variants->map(function ($v) {
                return [
                    'id' => $v->id,
                    'name' => $v->variantName,
                    'value' => $v->variantValue,
                    'additionalPrice' => $v->additionalPrice ?? 0,
                    'additionalCost' => $v->additionalCost ?? 0,
                    'stockQty' => $v->stockQty ?? 0,
                ];
            }),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'customerCode' => 'required|string',
                'productCode' => 'required|string',
                'workName' => 'nullable|string|max:100',
                'variant_id' => 'nullable|integer',
                'variant_name' => 'nullable|string',
                'variant_value' => 'nullable|string',
                'specialRate' => 'required|numeric|min:0',
                'rateType' => 'required|string|in:Sale,Purchase',
                'discountPercent' => 'nullable|numeric|min:0|max:100',
                'remarks' => 'nullable|string|max:255',
                'effective_from' => 'nullable|date',
            ]);

            $rateType = $validated['rateType'];
            $workName = ucwords(strtolower(trim((string) ($validated['workName'] ?? ''))));
            if ($workName === '') {
                $workName = null;
            }

            // Existing active rate for same party+product+work+variant+rateType
            $query = CustomerProductRate::where('customerCode', $validated['customerCode'])
                ->where('productCode', $validated['productCode'])
                ->where('rateType', $rateType)
                ->where('is_active', true);

            if ($workName !== null) {
                $query->where('work_name', $workName);
            } else {
                $query->where(function ($q) {
                    $q->whereNull('work_name')->orWhere('work_name', '');
                });
            }

            if (! empty($validated['variant_id'])) {
                $query->where('variant_id', $validated['variant_id']);
            } else {
                $query->whereNull('variant_id');
            }

            $existingRate = $query->first();

            if ($existingRate) {
                if ((float) $existingRate->specialRate === (float) $validated['specialRate']) {
                    $existingRate->update([
                        'discountPercent' => $validated['discountPercent'] ?? 0,
                        'remarks' => $validated['remarks'] ?? '',
                        'effective_from' => $validated['effective_from'] ?? $existingRate->effective_from,
                        'work_name' => $workName,
                        'variant_name' => $validated['variant_name'] ?? $existingRate->variant_name,
                        'variant_value' => $validated['variant_value'] ?? $existingRate->variant_value,
                        'rateType' => $rateType,
                    ]);

                    if (PartyBalance::isSupplier(null, $validated['customerCode'])) {
                        SupplierModuleCache::bumpParty($validated['customerCode']);
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Special rate updated successfully!',
                        'data' => $existingRate->fresh(),
                    ]);
                }

                $existingRate->update(['is_active' => false, 'status' => 'Inactive']);
            }

            // Create new rate version
            $rate = CustomerProductRate::create([
                'customerCode' => $validated['customerCode'],
                'productCode' => $validated['productCode'],
                'work_name' => $workName,
                'variant_id' => $validated['variant_id'] ?? null,
                'variant_name' => $validated['variant_name'] ?? null,
                'variant_value' => $validated['variant_value'] ?? null,
                'effective_from' => $validated['effective_from'] ?? now()->toDateString(),
                'specialRate' => $validated['specialRate'],
                'rateType' => $rateType,
                'discountPercent' => $validated['discountPercent'] ?? 0,
                'remarks' => $validated['remarks'] ?? '',
                'status' => 'Active',
                'is_active' => true,
            ]);

            if (PartyBalance::isSupplier(null, $validated['customerCode'])) {
                SupplierModuleCache::bumpParty($validated['customerCode']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Special rate saved successfully!',
                'data' => $rate,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCustomerRates($customerCode)
    {
        $rates = CustomerProductRate::where('customerCode', $customerCode)
            ->where('status', 'Active')
            ->where('is_active', true)
            ->orderBy('effective_from', 'desc')
            ->get();

        $data = $rates->map(function ($rate) {
            $product = newProduct::where('productCode', $rate->productCode)->first();

            // Get variant info if variant_id exists
            $variantInfo = '';
            if ($rate->variant_id) {
                $variantInfo = $rate->variant_name && $rate->variant_value
                    ? " ({$rate->variant_name}: {$rate->variant_value})"
                    : '';
            }
            $workName = trim((string) ($rate->work_name ?? ''));

            return [
                'id' => $rate->id,
                'productCode' => $rate->productCode,
                'productName' => ($product ? $product->productName : 'Unknown').$variantInfo,
                'companyName' => $product ? $product->companyName : 'Unknown',
                'workName' => $workName,
                'saleRate' => $product ? $product->saleRate : 0,
                'costRate' => $product ? ($product->costRate ?? 0) : 0,
                'specialRate' => $rate->specialRate,
                'rateType' => $rate->rateType ?: 'Sale',
                'discountPercent' => $rate->discountPercent,
                'remarks' => $rate->remarks,
                'effective_from' => $rate->effective_from?->format('d-M-Y') ?? '-',
                'effective_from_iso' => $rate->effective_from?->format('Y-m-d') ?? now()->toDateString(),
                'variant_id' => $rate->variant_id,
                'variant_name' => $rate->variant_name,
                'variant_value' => $rate->variant_value,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function getRateHistory($customerCode, $productCode)
    {
        $rates = CustomerProductRate::history($customerCode, $productCode)->get();

        $data = $rates->map(function ($rate) {
            return [
                'id' => $rate->id,
                'specialRate' => $rate->specialRate,
                'rateType' => $rate->rateType ?: 'Sale',
                'discountPercent' => $rate->discountPercent,
                'remarks' => $rate->remarks,
                'effective_from' => $rate->effective_from?->format('d-M-Y') ?? '-',
                'is_active' => $rate->is_active,
                'status' => $rate->status,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function delete($id)
    {
        try {
            $rate = CustomerProductRate::findOrFail($id);
            // Deactivate instead of delete to preserve history
            $rate->update([
                'is_active' => false,
                'status' => 'Inactive',
            ]);

            if (PartyBalance::isSupplier(null, (string) $rate->customerCode)) {
                SupplierModuleCache::bumpParty((string) $rate->customerCode);
            }

            return response()->json([
                'success' => true,
                'message' => 'Rate deactivated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
