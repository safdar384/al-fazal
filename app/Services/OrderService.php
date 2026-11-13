<?php

namespace App\Services;

use App\Models\CustomerArea;
use App\Models\Guds;
use App\Models\NewCustomer;
use App\Models\newProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\ListPage;
use App\Support\SaleInvoiceCalculator;
use App\Support\SaleStockService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Customer orders — demand only. No stock, ledger, or accounting impact.
 */
class OrderService
{
    public function nextOrderNumber(): string
    {
        $last = Order::query()->orderByDesc('id')->first();

        if (! $last || ! preg_match('/ORD-(\d+)/', $last->orderNo, $m)) {
            return 'ORD-00001';
        }

        return 'ORD-'.str_pad((string) ((int) $m[1] + 1), 5, '0', STR_PAD_LEFT);
    }

    /**
     * @return array{
     *   customers: Collection,
     *   products: Collection,
     *   guds: Collection,
     *   nextOrderNo: string,
     *   statuses: array<int, string>,
     *   custAreas: Collection,
     *   custCategories: array<int, string>,
     *   productCategories: Collection
     * }
     */
    public function formData(): array
    {
        $products = newProduct::query()->with('variants')->orderBy('productName')->get();

        return [
            'customers' => NewCustomer::query()->excludeProductSupplier()->orderBy('custName')->get(),
            'products' => $products,
            'guds' => Guds::query()->where('status', 'active')->orderBy('gudsName')->get(),
            'nextOrderNo' => $this->nextOrderNumber(),
            'statuses' => config('orders.statuses', []),
            'custAreas' => CustomerArea::query()->orderBy('areaName')->get(),
            'custCategories' => array_values(array_filter(
                NewCustomer::CATEGORIES,
                fn ($cat) => ! NewCustomer::isProductSupplierCategory($cat)
            )),
            'productCategories' => $products
                ->pluck('catagoryName')
                ->filter(fn ($c) => filled($c))
                ->unique()
                ->sort()
                ->values(),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function searchCustomers(string $term, int $limit = 30): Collection
    {
        $term = trim($term);
        $query = NewCustomer::query()->excludeProductSupplier()->orderBy('custName');

        if ($term !== '') {
            $like = '%'.$term.'%';
            $query->where(function ($q) use ($like) {
                $q->where('custCode', 'like', $like)
                    ->orWhere('custName', 'like', $like)
                    ->orWhere('bussnessName', 'like', $like)
                    ->orWhere('custCity', 'like', $like)
                    ->orWhere('custContect', 'like', $like)
                    ->orWhere('custArea', 'like', $like);
            });
        }

        return $query->limit($limit)->get()->map(fn (NewCustomer $c) => $this->customerOption($c));
    }

    /**
     * @return array<string, mixed>
     */
    public function customerOption(NewCustomer $c): array
    {
        $biz = trim((string) ($c->bussnessName ?: $c->custName));
        $city = trim((string) ($c->custCity ?? ''));

        return [
            'id' => $c->custCode,
            'code' => $c->custCode,
            'name' => $c->custName,
            'business' => $c->bussnessName ?? '',
            'city' => $city,
            'phone' => $c->custContect ?? '',
            'address' => $c->custAddress ?? '',
            'area' => $c->custArea ?? '',
            'discount' => (float) ($c->custdiscount ?? 0),
            'text' => $city !== '' ? $biz.' — '.$city : $biz,
            'label' => $city !== '' ? $biz.' — '.$city : $biz,
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function searchProducts(string $term, ?string $category = null, int $limit = 40): Collection
    {
        $term = trim($term);
        $category = trim((string) $category);
        $query = newProduct::query()->with('variants')->orderBy('productName');

        if ($category !== '') {
            $query->where('catagoryName', $category);
        }

        if ($term !== '') {
            $like = '%'.$term.'%';
            $query->where(function ($q) use ($like) {
                $q->where('productCode', 'like', $like)
                    ->orWhere('productName', 'like', $like)
                    ->orWhere('companyName', 'like', $like);
            });
        }

        return $query->limit($limit)->get()->map(fn (newProduct $p) => $this->productOption($p));
    }

    /**
     * @return array<string, mixed>
     */
    public function productOption(newProduct $p): array
    {
        $stock = (float) ($p->stockQty ?? 0);
        if ($p->relationLoaded('variants') && $p->variants->count() > 0) {
            $stock = (float) $p->variants->sum('stockQty');
        }

        return [
            'code' => $p->productCode,
            'name' => $p->productName,
            'company' => $p->companyName ?? '',
            'category' => $p->catagoryName ?? '',
            'packing' => $p->pakingName ?? '',
            'unit' => $p->unitPerPackName ?? '',
            'piecesPerUnit' => $p->piecesPerUnit ?? null,
            'rate' => (float) ($p->saleRate ?? 0),
            'stockQty' => $stock,
            'image' => $p->image_url,
            'hasVariants' => $p->relationLoaded('variants') && $p->variants->count() > 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{order: Order, previewUrl: string}
     */
    public function store(array $validated): array
    {
        return DB::transaction(function () use ($validated) {
            $computed = $this->computeFromRequest($validated);
            $this->optionalStockCheck($computed['items']);

            $order = Order::create([
                'orderNo' => $validated['orderNo'],
                'orderDate' => $validated['orderDate'],
                'customerCode' => $validated['customerCode'],
                'customerName' => $validated['customerName'],
                'city' => $validated['city'] ?? '',
                'gudsName' => $validated['gudsName'] ?? '',
                'noOfItems' => $computed['noOfItems'],
                'orderTotal' => $computed['orderTotal'],
                'orderDiscount' => $computed['orderDiscount'],
                'grandTotal' => $computed['grandTotal'],
                'comments' => $validated['comments'] ?? '',
                'notes' => $validated['notes'] ?? ($validated['comments'] ?? ''),
                'status' => $validated['status'] ?? 'Pending',
            ]);

            $this->persistItems($order, $computed['items']);

            return [
                'order' => $order->load('items'),
                'previewUrl' => route('order.preview', $order->orderNo),
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(Order $order, array $validated): Order
    {
        if ($order->converted_invoice_no) {
            throw new InvalidArgumentException('Cannot edit an order that was converted to an invoice.');
        }

        if ($order->status === 'Cancelled') {
            throw new InvalidArgumentException('Cannot edit a cancelled order.');
        }

        return DB::transaction(function () use ($order, $validated) {
            $computed = $this->computeFromRequest($validated);
            $this->optionalStockCheck($computed['items']);

            $order->update([
                'orderDate' => $validated['orderDate'],
                'customerCode' => $validated['customerCode'],
                'customerName' => $validated['customerName'],
                'city' => $validated['city'] ?? '',
                'gudsName' => $validated['gudsName'] ?? '',
                'noOfItems' => $computed['noOfItems'],
                'orderTotal' => $computed['orderTotal'],
                'orderDiscount' => $computed['orderDiscount'],
                'grandTotal' => $computed['grandTotal'],
                'comments' => $validated['comments'] ?? '',
                'notes' => $validated['notes'] ?? ($validated['comments'] ?? ''),
            ]);

            OrderItem::query()->where('order_id', $order->id)->delete();
            $this->persistItems($order, $computed['items']);

            return $order->fresh(['items', 'customer']);
        });
    }

    /**
     * Convert order to credit sale invoice (normal sale accounting applies on invoice only).
     *
     * @return array{invoice: \App\Models\SaleInvoice, previewUrl: string, order: Order}
     */
    public function convertToSaleInvoice(Order $order): array
    {
        if ($order->converted_invoice_no) {
            throw new InvalidArgumentException('This order was already converted to invoice '.$order->converted_invoice_no.'.');
        }

        if ($order->status === 'Cancelled') {
            throw new InvalidArgumentException('Cancelled orders cannot be converted.');
        }

        if ($order->items()->count() === 0) {
            throw new InvalidArgumentException('Order has no items.');
        }

        return DB::transaction(function () use ($order) {
            $order->load('items');
            $sales = app(SaleInvoiceService::class);
            $invoiceNo = $sales->nextInvoiceNumber();

            $items = $order->items->map(fn (OrderItem $item) => [
                'productCode' => $item->productCode,
                'productName' => $item->productName,
                'qty' => $item->qty,
                'rate' => (float) $item->rate,
                'discount' => (float) ($item->discount ?? 0),
                'amount' => (float) $item->amount,
                'remarks' => $item->remarks,
            ])->all();

            $customer = NewCustomer::query()->where('custCode', $order->customerCode)->first();
            $meta = \App\Support\PartyBalance::meta($order->customerCode);

            $result = $sales->store([
                'invoiceNo' => $invoiceNo,
                'invoiceDate' => now()->format('Y-m-d'),
                'customerCode' => $order->customerCode,
                'customerName' => $order->customerName,
                'city' => $order->city ?? '',
                'balance' => $meta['balance'] ?? 0,
                'gudsName' => $order->gudsName ?? '',
                'corten' => 0,
                'biltyExpanse' => 0,
                'loaderExpanse' => 0,
                'biltyBalance' => 0,
                'comments' => trim('Converted from order '.$order->orderNo.($order->notes ? ' — '.$order->notes : '')),
                'invoiceDiscount' => (float) ($order->orderDiscount ?? 0),
                'items' => $items,
            ]);

            $order->update([
                'status' => 'Completed',
                'converted_invoice_no' => $result['invoice']->invoiceNo,
            ]);

            return [
                'invoice' => $result['invoice'],
                'previewUrl' => $result['previewUrl'],
                'order' => $order->fresh(),
            ];
        });
    }

    public function updateStatus(Order $order, string $status): Order
    {
        $allowed = config('orders.statuses', []);
        if (! in_array($status, $allowed, true)) {
            throw new InvalidArgumentException('Invalid order status.');
        }

        if ($order->converted_invoice_no && $status !== 'Completed') {
            throw new InvalidArgumentException('Converted orders must remain Completed.');
        }

        $order->update(['status' => $status]);

        return $order->fresh();
    }

    public function paginateOrders(Request $request): LengthAwarePaginator
    {
        $query = Order::query()->with(['customer:custCode,custName,custArea,custCity']);
        $this->applySummaryFilters($query, $request, true);

        $perPage = ListPage::perPage($request);

        return $query
            ->orderByDesc('orderDate')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Order count and value for each status, using the date, area, and customer filters.
     * A selected status returns only that box. All returns every status.
     *
     * @return array<int, array{status: string, total: int, value: float}>
     */
    public function statusTotals(Request $request): array
    {
        $query = Order::query();
        $this->applySummaryFilters($query, $request, false);

        $rows = $query
            ->selectRaw('status, COUNT(*) as total_orders, COALESCE(SUM(grandTotal), 0) as order_value')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $statuses = config('orders.statuses', []);
        $selected = (string) $request->query('status', '');
        $shown = in_array($selected, $statuses, true) ? [$selected] : $statuses;

        $totals = [];
        foreach ($shown as $status) {
            $row = $rows->get($status);
            $totals[] = [
                'status' => $status,
                'total' => (int) ($row->total_orders ?? 0),
                'value' => (float) ($row->order_value ?? 0),
            ];
        }

        return $totals;
    }

    protected function applySummaryFilters($query, Request $request, bool $includeStatus): void
    {
        $fromDate = $request->query('fromDate');
        $toDate = $request->query('toDate');
        if ($fromDate && $toDate) {
            $query->whereBetween('orderDate', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $query->where('orderDate', '>=', $fromDate);
        } elseif ($toDate) {
            $query->where('orderDate', '<=', $toDate);
        }

        if ($request->query('customerCode')) {
            $query->where('customerCode', $request->query('customerCode'));
        }

        if ($includeStatus && $request->query('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->query('area')) {
            $codes = NewCustomer::query()->excludeProductSupplier()->where('custArea', $request->query('area'))->pluck('custCode');
            $query->whereIn('customerCode', $codes->isEmpty() ? ['__none__'] : $codes);
        }
    }

    /**
     * @return Collection<int, Order>
     */
    public function listOrdersForPrint(Request $request): Collection
    {
        $request->merge(['per_page' => 10000]);

        return $this->paginateOrders($request)->getCollection();
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function computeFromRequest(array $validated): array
    {
        $computed = SaleInvoiceCalculator::compute(
            $validated['items'],
            (float) ($validated['orderDiscount'] ?? 0)
        );

        return [
            'items' => $computed['items'],
            'noOfItems' => $computed['noOfItems'],
            'orderTotal' => $computed['invoiceTotal'],
            'orderDiscount' => $computed['invoiceDiscount'],
            'grandTotal' => $computed['grandTotal'],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function persistItems(Order $order, array $items): void
    {
        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'orderNo' => $order->orderNo,
                'productCode' => $item['productCode'],
                'productName' => $item['productName'],
                'qty' => $item['qty'],
                'rate' => $item['rate'],
                'discount' => $item['discount'] ?? 0,
                'amount' => $item['amount'],
                'remarks' => $item['remarks'] ?? null,
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function optionalStockCheck(array $items): void
    {
        if (! config('orders.stock_check_on_save', false)) {
            return;
        }

        $stockItems = collect($items)->map(fn ($i) => [
            'productCode' => $i['productCode'],
            'variant_id' => $i['variant_id'] ?? null,
            'qty' => $i['qty'],
        ])->all();

        SaleStockService::assertAvailable($stockItems);
    }
}
