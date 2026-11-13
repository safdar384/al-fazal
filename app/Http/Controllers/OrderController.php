<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DownloadsDomPdf;
use App\Models\CustomerArea;
use App\Models\NewCustomer;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    use DownloadsDomPdf;

    public function __construct(
        protected OrderService $orders
    ) {}

    public function index(): View
    {
        return view('Pages.Sale.newOrder', $this->orders->formData());
    }

    public function searchCustomers(Request $request): JsonResponse
    {
        $term = (string) $request->query('q', $request->query('term', ''));
        $results = $this->orders->searchCustomers($term, (int) $request->query('limit', 30));

        return response()->json([
            'results' => $results->values(),
            'customers' => $results->values(),
        ]);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $term = (string) $request->query('q', $request->query('term', ''));
        $category = (string) $request->query('category', '');
        $results = $this->orders->searchProducts($term, $category !== '' ? $category : null, (int) $request->query('limit', 48));

        return response()->json([
            'results' => $results->values(),
            'products' => $results->values(),
        ]);
    }

    public function productDetails(Request $request, string $productCode): JsonResponse
    {
        try {
            $details = app(\App\Services\SaleInvoiceService::class)
                ->productDetails($productCode, $request->query('customerCode'));

            $product = \App\Models\newProduct::query()
                ->with('variants')
                ->where('productCode', $productCode)
                ->first();

            if ($product) {
                $details['image'] = $product->image_url;
                $details['company'] = $product->companyName ?? '';
                $details['category'] = $product->catagoryName ?? '';
                $details['packing'] = $product->pakingName ?? '';
                $details['unit'] = $product->unitPerPackName ?? '';
                $details['piecesPerUnit'] = $product->piecesPerUnit;
                $details['stockQty'] = (float) ($product->stockQty ?? 0);
                if ($product->variants->count() > 0) {
                    $details['stockQty'] = (float) $product->variants->sum('stockQty');
                }
            }

            return response()->json($details);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $this->validateOrder($request, true);
            $result = $this->orders->store($validated);

            return response()->json([
                'success' => true,
                'message' => 'Order saved successfully.',
                'orderNo' => $result['order']->orderNo,
                'previewUrl' => $result['previewUrl'],
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

    public function preview(string $orderNo): View
    {
        $order = Order::query()->with(['items', 'customer'])->where('orderNo', $orderNo)->firstOrFail();

        return view('Pages.Sale.orderPreview', [
            'order' => $order,
            'customer' => $order->customer,
            'statuses' => config('orders.statuses', []),
        ]);
    }

    public function pdf(string $orderNo): Response
    {
        $order = Order::query()->with(['items', 'customer'])->where('orderNo', $orderNo)->firstOrFail();

        return $this->downloadDomPdf(
            'Pages.Sale.orderPreviewPdf',
            [
                'order' => $order,
                'customer' => $order->customer,
                'statuses' => config('orders.statuses', []),
            ],
            'order-'.$orderNo.'.pdf'
        );
    }

    public function edit(string $orderNo): View
    {
        $order = Order::query()->with('items')->where('orderNo', $orderNo)->firstOrFail();

        if ($order->converted_invoice_no) {
            abort(403, 'This order was converted to invoice '.$order->converted_invoice_no.' and cannot be edited.');
        }

        return view('Pages.Sale.editOrder', array_merge(
            $this->orders->formData(),
            ['order' => $order]
        ));
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
                'previewUrl' => route('order.preview', $order->orderNo),
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

    public function convertToInvoice(string $orderNo): JsonResponse
    {
        try {
            $order = Order::query()->where('orderNo', $orderNo)->firstOrFail();
            $result = $this->orders->convertToSaleInvoice($order);

            return response()->json([
                'success' => true,
                'message' => 'Order converted to sale invoice '.$result['invoice']->invoiceNo,
                'invoiceNo' => $result['invoice']->invoiceNo,
                'previewUrl' => $result['previewUrl'],
                'orderNo' => $order->orderNo,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, string $orderNo): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string',
            ]);

            $order = Order::query()->where('orderNo', $orderNo)->firstOrFail();
            $this->orders->updateStatus($order, $validated['status']);

            return response()->json(['success' => true, 'message' => 'Status updated.']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function summary(Request $request): View
    {
        $orders = $this->orders->paginateOrders($request);
        $statusTotals = $this->orders->statusTotals($request);

        if ($request->boolean('fragment')) {
            return view('Pages.Sale.orderSummaryLive', compact('orders', 'statusTotals'));
        }

        $customers = NewCustomer::query()
            ->excludeProductSupplier()
            ->orderBy('custName')
            ->get(['custCode', 'custName', 'custArea', 'custCity']);

        $areas = NewCustomer::query()
            ->whereNotNull('custArea')
            ->where('custArea', '!=', '')
            ->distinct()
            ->orderBy('custArea')
            ->pluck('custArea');

        return view('Pages.Sale.orderSummary', [
            'orders' => $orders,
            'statusTotals' => $statusTotals,
            'customers' => $customers,
            'areas' => $areas,
            'statuses' => config('orders.statuses', []),
            'fromDate' => $request->query('fromDate'),
            'toDate' => $request->query('toDate'),
            'customerCode' => $request->query('customerCode'),
            'status' => $request->query('status'),
            'area' => $request->query('area'),
        ]);
    }

    public function printSummary(Request $request): View|Response
    {
        $orders = $this->orders->listOrdersForPrint($request);
        $statusTotals = $this->orders->statusTotals($request);

        if ($request->boolean('download_pdf')) {
            return $this->downloadDomPdf(
                'Pages.Sale.orderSummaryPdf',
                compact('orders', 'statusTotals'),
                'order-summary-'.now()->format('YmdHis').'.pdf',
                'a4',
                'landscape'
            );
        }

        return view('Pages.Sale.orderSummaryPrint', compact('orders', 'statusTotals'));
    }

    public function approvalSection(Request $request): View
    {
        $request->merge([
            'status' => $request->query('status', 'Pending'),
        ]);

        $orders = $this->orders->paginateOrders($request);
        $statuses = config('orders.statuses', []);

        if ($request->boolean('fragment')) {
            return view('Pages.Sale.orderApprovalLive', compact('orders', 'statuses'));
        }

        $areas = CustomerArea::query()->orderBy('areaName')->get(['areaCode', 'areaName']);

        return view('Pages.Sale.orderApproval', [
            'orders' => $orders,
            'areas' => $areas,
            'status' => $request->query('status', 'Pending'),
            'area' => $request->query('area'),
            'statuses' => $statuses,
        ]);
    }

    public function applyApprovalStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:'.implode(',', config('orders.statuses', [])),
            'orderNos' => 'required|array|min:1',
            'orderNos.*' => 'required|string',
        ]);

        $updated = [];
        $errors = [];

        foreach (array_unique($validated['orderNos']) as $orderNo) {
            try {
                $order = Order::query()->where('orderNo', $orderNo)->first();
                if (! $order) {
                    $errors[] = "{$orderNo} was not found.";
                    continue;
                }
                if ($order->isConverted() && $validated['status'] !== 'Completed') {
                    $errors[] = "{$orderNo} is already invoiced.";
                    continue;
                }
                $this->orders->updateStatus($order, $validated['status']);
                $updated[] = $orderNo;
            } catch (InvalidArgumentException $e) {
                $errors[] = "{$orderNo}: {$e->getMessage()}";
            }
        }

        $count = count($updated);
        $message = $count === 1
            ? "1 order set to {$validated['status']}."
            : "{$count} orders set to {$validated['status']}.";

        if ($errors !== []) {
            $message .= ' '.implode(' ', $errors);
        }

        return response()->json([
            'success' => $count > 0,
            'message' => $count > 0 ? $message : ($errors[0] ?? 'No orders were updated.'),
            'updated' => $updated,
        ], $count > 0 ? 200 : 422);
    }

    public function updateApprovalStatus(Request $request, string $orderNo)
    {
        $validated = $request->validate([
            'status' => 'required|in:'.implode(',', config('orders.statuses', [])),
        ]);

        try {
            $order = Order::query()->where('orderNo', $orderNo)->firstOrFail();
            $this->orders->updateStatus($order, $validated['status']);

        return back()->with('success', "Order {$orderNo} status updated to {$validated['status']}.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateOrder(Request $request, bool $requireOrderNo): array
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
            'items' => 'required|array|min:1',
            'items.*.productCode' => 'required|string',
            'items.*.productName' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.rate' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'items.*.remarks' => 'nullable|string|max:500',
            'items.*.amount' => 'nullable|numeric|min:0',
        ];

        if ($requireOrderNo) {
            $rules['orderNo'] = 'required|string|unique:orders,orderNo';
        }

        return $request->validate($rules, [
            'customerCode.required' => 'Please select a customer.',
            'customerName.required' => 'Please select a customer.',
            'orderDate.required' => 'Order date is required.',
            'items.required' => 'Add at least one product before saving.',
            'items.min' => 'Add at least one product before saving.',
            'items.*.productCode.required' => 'Each line must have a product code.',
            'items.*.qty.required' => 'Each product must have a quantity.',
            'items.*.qty.min' => 'Quantity must be greater than 0.',
        ]);
    }
}
