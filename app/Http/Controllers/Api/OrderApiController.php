<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\SaleInvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class OrderApiController extends Controller
{
    public function __construct(
        protected OrderService $orders
    ) {}

    public function pending(Request $request): JsonResponse
    {
        $request->merge(['status' => 'Pending']);

        $orders = Order::query()
            ->with(['customer:custCode,custName,custArea,custCity,bussnessName', 'items'])
            ->where('status', 'Pending')
            ->whereNull('converted_invoice_no')
            ->when($request->filled('customerCode'), function ($q) use ($request) {
                $q->where('customerCode', $request->query('customerCode'));
            })
            ->when($request->filled('fromDate') && $request->filled('toDate'), function ($q) use ($request) {
                $q->whereBetween('orderDate', [$request->query('fromDate'), $request->query('toDate')]);
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $like = '%'.trim((string) $request->query('q')).'%';
                $q->where(function ($inner) use ($like) {
                    $inner->where('orderNo', 'like', $like)
                        ->orWhere('customerName', 'like', $like)
                        ->orWhere('customerCode', 'like', $like);
                });
            })
            ->orderByDesc('orderDate')
            ->orderByDesc('id')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $orders->getCollection()->map(fn (Order $order) => $this->transformOrder($order))->values(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show(string $orderNo): JsonResponse
    {
        $order = Order::query()->with(['items', 'customer'])->where('orderNo', $orderNo)->firstOrFail();

        return response()->json([
            'success' => true,
            'editable' => blank($order->converted_invoice_no) && $order->status !== 'Cancelled',
            'data' => $this->transformOrder($order, true),
            'nextOrderNo' => $this->orders->nextOrderNumber(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateOrder($request, true);
            if (blank($validated['orderNo'] ?? null)) {
                $validated['orderNo'] = $this->orders->nextOrderNumber();
            }
            $validated['status'] = $validated['status'] ?? 'Pending';

            $result = $this->orders->store($validated);

            return response()->json([
                'success' => true,
                'message' => 'Order saved successfully.',
                'orderNo' => $result['order']->orderNo,
                'data' => $this->transformOrder($result['order']->load('items'), true),
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?: 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $orderNo): JsonResponse
    {
        try {
            $order = Order::query()->where('orderNo', $orderNo)->firstOrFail();
            $validated = $this->validateOrder($request, false);
            $order = $this->orders->update($order, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully.',
                'orderNo' => $order->orderNo,
                'data' => $this->transformOrder($order->load('items'), true),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?: 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function searchCustomers(Request $request): JsonResponse
    {
        $term = (string) $request->query('q', $request->query('term', ''));
        $results = $this->orders->searchCustomers($term, (int) $request->query('limit', 30));

        return response()->json([
            'success' => true,
            'data' => $results->values(),
        ]);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $term = (string) $request->query('q', $request->query('term', ''));
        $category = (string) $request->query('category', '');
        $results = $this->orders->searchProducts(
            $term,
            $category !== '' ? $category : null,
            (int) $request->query('limit', 48)
        );

        return response()->json([
            'success' => true,
            'data' => $results->values(),
        ]);
    }

    public function productDetails(Request $request, string $productCode): JsonResponse
    {
        try {
            $details = app(SaleInvoiceService::class)
                ->productDetails($productCode, $request->query('customerCode'));

            return response()->json([
                'success' => true,
                'data' => $details,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function formMeta(): JsonResponse
    {
        $categories = \App\Models\newProduct::query()
            ->whereNotNull('catagoryName')
            ->where('catagoryName', '!=', '')
            ->distinct()
            ->orderBy('catagoryName')
            ->pluck('catagoryName')
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'nextOrderNo' => $this->orders->nextOrderNumber(),
                'productCategories' => $categories,
                'statuses' => config('orders.statuses', []),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateOrder(Request $request, bool $creating): array
    {
        $rules = [
            'orderDate' => 'required|date',
            'customerCode' => 'required|string',
            'customerName' => 'required|string',
            'city' => 'nullable|string',
            'gudsName' => 'nullable|string',
            'comments' => 'nullable|string',
            'notes' => 'nullable|string',
            'orderDiscount' => 'nullable|numeric|min:0',
            'status' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.productCode' => 'required|string',
            'items.*.productName' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.rate' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'items.*.remarks' => 'nullable|string|max:500',
            'items.*.amount' => 'nullable|numeric|min:0',
        ];

        if ($creating) {
            $rules['orderNo'] = 'nullable|string|unique:orders,orderNo';
        }

        return $request->validate($rules, [
            'customerCode.required' => 'Please select a customer.',
            'customerName.required' => 'Please select a customer.',
            'orderDate.required' => 'Order date is required.',
            'items.required' => 'Add at least one product before saving.',
            'items.min' => 'Add at least one product before saving.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformOrder(Order $order, bool $withItems = false): array
    {
        $data = [
            'orderNo' => $order->orderNo,
            'orderDate' => (string) $order->orderDate,
            'customerCode' => $order->customerCode,
            'customerName' => $order->customerName,
            'city' => $order->city,
            'area' => $order->customer?->custArea,
            'gudsName' => $order->gudsName,
            'comments' => $order->comments,
            'notes' => $order->notes,
            'noOfItems' => (int) $order->noOfItems,
            'orderTotal' => (float) $order->orderTotal,
            'orderDiscount' => (float) $order->orderDiscount,
            'grandTotal' => (float) $order->grandTotal,
            'status' => $order->status,
            'converted_invoice_no' => $order->converted_invoice_no,
        ];

        if ($withItems || $order->relationLoaded('items')) {
            $data['items'] = $order->items->map(fn ($item) => [
                'productCode' => $item->productCode,
                'productName' => $item->productName,
                'qty' => (float) $item->qty,
                'rate' => (float) $item->rate,
                'discount' => (float) ($item->discount ?? 0),
                'amount' => (float) ($item->amount ?? 0),
                'remarks' => $item->remarks ?? '',
            ])->values();
        }

        return $data;
    }
}
