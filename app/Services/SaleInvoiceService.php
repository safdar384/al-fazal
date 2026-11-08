<?php

namespace App\Services;

use App\Models\CustomerProductRate;
use App\Models\Guds;
use App\Models\NewCustomer;
use App\Models\newProduct;
use App\Models\Order;
use App\Models\SaleInvoice;
use App\Models\SaleInvoiceBankPayment;
use App\Models\SaleInvoiceItem;
use App\Support\GeneralLedger;
use App\Support\PartyLedger;
use App\Support\ListPage;
use App\Support\SaleInvoiceCalculator;
use App\Support\SaleStockService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class SaleInvoiceService
{
    /** Preview next credit sale invoice number (INV-xxxxx). */
    public function nextInvoiceNumber(): string
    {
        return $this->formatCreditInvoiceNumber($this->maxCreditInvoiceSequence() + 1);
    }

    /**
     * Allocate the next unused INV-* number (call inside a DB transaction).
     */
    public function allocateNextCreditInvoiceNumber(): string
    {
        $next = $this->maxCreditInvoiceSequence() + 1;

        do {
            $candidate = $this->formatCreditInvoiceNumber($next);
            $exists = SaleInvoice::query()->where('invoiceNo', $candidate)->exists();
            if (! $exists) {
                return $candidate;
            }
            $next++;
        } while ($next < 99999);

        throw new \RuntimeException('Unable to allocate a new sale invoice number.');
    }

    protected function maxCreditInvoiceSequence(): int
    {
        return (int) SaleInvoice::query()
            ->where('invoiceNo', 'like', 'INV-%')
            ->pluck('invoiceNo')
            ->map(fn (string $no) => preg_match('/^INV-(\d+)$/i', $no, $m) ? (int) $m[1] : 0)
            ->max();
    }

    protected function formatCreditInvoiceNumber(int $sequence): string
    {
        return 'INV-'.str_pad((string) max(1, $sequence), 5, '0', STR_PAD_LEFT);
    }

    /**
     * @return array{customers: Collection, products: Collection, guds: Collection, nextInvoiceNo: string}
     */
    public function formData(): array
    {
        return [
            'customers' => NewCustomer::query()->excludeProductSupplier()->orderBy('custName')->get([
                'custCode',
                'custName',
                'bussnessName',
                'custCity',
                'custArea',
                'custdiscount',
            ]),
            'products' => newProduct::query()->with('variants')->orderBy('productName')->get(),
            'guds' => Guds::query()->where('status', 'active')->orderBy('gudsName')->get(),
            'nextInvoiceNo' => $this->nextInvoiceNumber(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function productDetails(string $productCode, ?string $customerCode = null): array
    {
        $product = newProduct::query()->with('variants')->where('productCode', $productCode)->first();

        if (! $product) {
            throw new \InvalidArgumentException('Product not found');
        }

        $ratesByVariant = collect();
        if ($customerCode) {
            $ratesByVariant = CustomerProductRate::query()
                ->where('customerCode', $customerCode)
                ->where('productCode', $productCode)
                ->where('is_active', true)
                ->forRateType(CustomerProductRate::TYPE_SALE)
                ->get()
                ->keyBy(fn ($r) => $r->variant_id === null ? 'base' : (string) $r->variant_id);
        }

        $baseRate = $ratesByVariant->get('base');
        $hasSpecialRate = $baseRate !== null;
        $specialRate = $hasSpecialRate ? (float) $baseRate->specialRate : null;

        $allVariants = $product->variants;
        $variantsToShow = $allVariants;

        if ($customerCode) {
            $withSpecial = $allVariants->filter(fn ($v) => $ratesByVariant->has((string) $v->id));
            if ($withSpecial->isNotEmpty()) {
                $variantsToShow = $withSpecial;
            }
        }

        $variantsWithRates = $variantsToShow->map(function ($v) use ($ratesByVariant, $product) {
            $variantRate = $ratesByVariant->get((string) $v->id);
            $hasVariantSpecialRate = $variantRate !== null;
            $variantSpecialRate = $hasVariantSpecialRate ? (float) $variantRate->specialRate : null;
            $baseCostRate = (float) ($product->costRate ?? 0);
            $variantAdditionalCost = (float) ($v->additionalCost ?? 0);

            return [
                'id' => $v->id,
                'name' => $v->variantName,
                'value' => $v->variantValue,
                'additionalPrice' => $v->additionalPrice ?? 0,
                'additionalCost' => $v->additionalCost ?? 0,
                'stockQty' => $v->stockQty ?? 0,
                'specialRate' => $variantSpecialRate,
                'hasSpecialRate' => $hasVariantSpecialRate,
                'costRate' => $baseCostRate + $variantAdditionalCost,
            ];
        })->values();

        return [
            'productCode' => $product->productCode,
            'productName' => $product->productName,
            'saleRate' => $hasSpecialRate ? $specialRate : (float) ($product->saleRate ?? 0),
            'standardRate' => (float) ($product->saleRate ?? 0),
            'costRate' => (float) ($product->costRate ?? 0),
            'hasSpecialRate' => $hasSpecialRate,
            'specialRate' => $specialRate,
            'variants' => $variantsWithRates,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{invoice: SaleInvoice, previewUrl: string}
     */
    public function store(array $validated): array
    {
        $attempts = 0;

        while ($attempts < 3) {
            try {
                return DB::transaction(function () use ($validated) {
                    $invoiceNo = $this->allocateNextCreditInvoiceNumber();

                    $computed = SaleInvoiceCalculator::compute(
                        $validated['items'],
                        (float) ($validated['invoiceDiscount'] ?? 0)
                    );

                    $linkedOrderNo = (string) ($validated['orderNo'] ?? '');
                    $order = null;
                    if ($linkedOrderNo !== '' && $linkedOrderNo !== '__none__') {
                        $order = Order::query()
                            ->where('orderNo', $linkedOrderNo)
                            ->lockForUpdate()
                            ->first();

                        if (! $order
                            || $order->customerCode !== $validated['customerCode']
                            || $order->converted_invoice_no
                            || $order->status !== 'In Process'
                        ) {
                            throw new \InvalidArgumentException('Select an open order for this customer.');
                        }
                    }

                    $invoice = SaleInvoice::create([
                        'invoiceNo' => $invoiceNo,
                'invoiceDate' => $validated['invoiceDate'],
                'customerCode' => $validated['customerCode'],
                'customerName' => $validated['customerName'],
                'orderNo' => $order?->orderNo,
                'city' => $validated['city'] ?? '',
                'balance' => $validated['balance'] ?? 0,
                'gudsName' => $validated['gudsName'] ?? '',
                'corten' => $validated['corten'] ?? 0,
                'biltyExpanse' => $validated['biltyExpanse'] ?? 0,
                'loaderExpanse' => $validated['loaderExpanse'] ?? 0,
                'biltyBalance' => $validated['biltyBalance'] ?? 0,
                'comments' => $validated['comments'] ?? '',
                'noOfItems' => $computed['noOfItems'],
                'invoiceTotal' => $computed['invoiceTotal'],
                'invoiceDiscount' => $computed['invoiceDiscount'],
                'grandTotal' => $computed['grandTotal'],
                'status' => 'Active',
                'sale_type' => 'credit',
                'payment_status' => 'unpaid',
                'paid_amount' => 0,
            ]);

                    $this->persistItems($invoice, $invoiceNo, $computed['items'], $computed['revenueRatio']);

                    if ($order) {
                        $order->update([
                            'status' => 'Completed',
                            'converted_invoice_no' => $invoiceNo,
                        ]);
                    }

                    $customer = NewCustomer::query()->where('custCode', $validated['customerCode'])->first();

                    PartyLedger::postSaleInvoice(
                        $validated['customerCode'],
                        $invoiceNo,
                        $validated['invoiceDate'],
                        $computed['grandTotal'],
                        $customer?->custArea
                    );

                    GeneralLedger::postSaleInvoice(
                        $validated['invoiceDate'],
                        $invoiceNo,
                        $computed['grandTotal']
                    );

                    return [
                        'invoice' => $invoice,
                        'previewUrl' => route('sale.invoice.preview', $invoice->invoiceNo),
                    ];
                });
            } catch (UniqueConstraintViolationException) {
                $attempts++;
                if ($attempts >= 3) {
                    throw new \RuntimeException('Could not allocate a unique invoice number. Please try again.');
                }
            }
        }

        throw new \RuntimeException('Could not save sale invoice.');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(SaleInvoice $invoice, array $validated): SaleInvoice
    {
        return DB::transaction(function () use ($invoice, $validated) {
            $invoiceNo = $invoice->invoiceNo;
            $computed = SaleInvoiceCalculator::compute(
                $validated['items'],
                (float) ($validated['invoiceDiscount'] ?? 0)
            );

            $invoice->update([
                'invoiceDate' => $validated['invoiceDate'],
                'customerCode' => $validated['customerCode'],
                'customerName' => $validated['customerName'],
                'city' => $validated['city'] ?? '',
                'balance' => $validated['balance'] ?? 0,
                'gudsName' => $validated['gudsName'] ?? '',
                'corten' => $validated['corten'] ?? 0,
                'biltyExpanse' => $validated['biltyExpanse'] ?? 0,
                'loaderExpanse' => $validated['loaderExpanse'] ?? 0,
                'biltyBalance' => $validated['biltyBalance'] ?? 0,
                'comments' => $validated['comments'] ?? '',
                'noOfItems' => $computed['noOfItems'],
                'invoiceTotal' => $computed['invoiceTotal'],
                'invoiceDiscount' => $computed['invoiceDiscount'],
                'grandTotal' => $computed['grandTotal'],
            ]);

            SaleInvoiceItem::query()->where('sale_invoice_id', $invoice->id)->delete();
            $this->persistItems($invoice, $invoiceNo, $computed['items'], $computed['revenueRatio']);

            $customer = NewCustomer::query()->where('custCode', $validated['customerCode'])->first();

            GeneralLedger::voidByReference($invoiceNo);
            PartyLedger::syncSaleInvoice(
                $validated['customerCode'],
                $invoiceNo,
                $validated['invoiceDate'],
                $computed['grandTotal'],
                $customer?->custArea
            );
            GeneralLedger::postSaleInvoice($validated['invoiceDate'], $invoiceNo, $computed['grandTotal']);

            return $invoice->fresh(['items']);
        });
    }

    public function delete(SaleInvoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $invoiceNo = $invoice->invoiceNo;

            if (($invoice->sale_type ?? 'credit') === 'cash') {
                $stockItems = $invoice->items()->get(['productCode', 'variant_id', 'qty'])->map(fn ($row) => [
                    'productCode' => $row->productCode,
                    'variant_id' => $row->variant_id,
                    'qty' => $row->qty,
                ])->all();
                SaleStockService::restore($stockItems);

                $payments = SaleInvoiceBankPayment::query()->where('sale_invoice_id', $invoice->id)->get();
                foreach ($payments as $payment) {
                    if ($payment->ledger_id) {
                        DB::table('ledgers')->where('id', $payment->ledger_id)->delete();
                    }
                }
                SaleInvoiceBankPayment::query()->where('sale_invoice_id', $invoice->id)->delete();
            }

            GeneralLedger::voidByReference($invoiceNo);
            PartyLedger::deleteByInvoiceNumber($invoiceNo);
            SaleInvoiceItem::query()->where('sale_invoice_id', $invoice->id)->delete();
            $invoice->delete();
        });
    }

    /** @var array{total_amount: float, credit_amount: float, cash_amount: float} */
    protected array $listAmounts = [
        'total_amount' => 0.0,
        'credit_amount' => 0.0,
        'cash_amount' => 0.0,
    ];

    /**
     * @return array{total_amount: float, credit_amount: float, cash_amount: float}
     */
    public function lastListAmounts(): array
    {
        return $this->listAmounts;
    }

    public function paginateInvoices(Request $request): LengthAwarePaginator
    {
        $query = SaleInvoice::query();

        [$fromDate, $toDate] = $this->invoiceListDates($request);
        if ($fromDate && $toDate) {
            $query->whereBetween('invoiceDate', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $query->where('invoiceDate', '>=', $fromDate);
        } elseif ($toDate) {
            $query->where('invoiceDate', '<=', $toDate);
        }

        $area = $request->query('area');
        if ($area) {
            $customerCodes = NewCustomer::query()->excludeProductSupplier()->where('custArea', $area)->pluck('custCode');
            if ($customerCodes->isEmpty()) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('customerCode', $customerCodes);
            }
        }

        $customerCode = $request->query('customerCode');
        if ($customerCode) {
            $query->where('customerCode', $customerCode);
        }

        $saleType = $request->query('sale_type');
        if (in_array($saleType, ['cash', 'credit'], true)) {
            $query->where('sale_type', $saleType);
        }

        $paymentStatus = $request->query('payment_status');
        if (in_array($paymentStatus, ['paid', 'unpaid'], true)) {
            $query->where('payment_status', $paymentStatus);
        }

        $amounts = (clone $query)
            ->selectRaw('COALESCE(SUM(grandTotal), 0) as total_amount')
            ->selectRaw("COALESCE(SUM(CASE WHEN sale_type = 'cash' THEN grandTotal ELSE 0 END), 0) as cash_amount")
            ->selectRaw("COALESCE(SUM(CASE WHEN sale_type = 'cash' THEN 0 ELSE grandTotal END), 0) as credit_amount")
            ->first();

        $this->listAmounts = [
            'total_amount' => (float) ($amounts->total_amount ?? 0),
            'credit_amount' => (float) ($amounts->credit_amount ?? 0),
            'cash_amount' => (float) ($amounts->cash_amount ?? 0),
        ];

        $perPage = ListPage::perPage($request);

        $customerTable = (new NewCustomer)->getTable();

        $paginator = $query
            ->leftJoin($customerTable.' as cust', 'sale_invoices.customerCode', '=', 'cust.custCode')
            ->select([
                'sale_invoices.id',
                'sale_invoices.invoiceNo',
                'sale_invoices.orderNo',
                'sale_invoices.invoiceDate',
                'sale_invoices.customerCode',
                'sale_invoices.customerName',
                'sale_invoices.city',
                'sale_invoices.noOfItems',
                'sale_invoices.corten',
                'sale_invoices.biltyNo',
                'sale_invoices.grandTotal',
                'sale_invoices.status',
                'sale_invoices.sale_type',
                'sale_invoices.payment_status',
                'sale_invoices.paid_amount',
                'cust.custAddress as customerAddress',
                'cust.custCity as customerCity',
            ])
            ->orderByDesc('sale_invoices.invoiceDate')
            ->orderByDesc('sale_invoices.id')
            ->paginate($perPage);

        $this->fillMissingOrderNumbers($paginator->getCollection());

        return $paginator;
    }

    public function updateBiltyNo(string $invoiceNo, ?string $biltyNo): SaleInvoice
    {
        $invoice = SaleInvoice::query()->where('invoiceNo', $invoiceNo)->firstOrFail();
        $invoice->biltyNo = $biltyNo !== null && $biltyNo !== '' ? trim($biltyNo) : null;
        $invoice->save();

        return $invoice;
    }

    /**
     * Default the sale list to the current month when no dates are sent.
     *
     * @return array{0: ?string, 1: ?string}
     */
    protected function invoiceListDates(Request $request): array
    {
        $fromDate = $request->query('fromDate');
        $toDate = $request->query('toDate');

        if (! $fromDate && ! $toDate) {
            return [now()->startOfMonth()->toDateString(), now()->toDateString()];
        }

        return [$fromDate ?: null, $toDate ?: null];
    }

    /**
     * @return Collection<int, SaleInvoice>
     */
    public function listInvoicesForPrint(Request $request): Collection
    {
        $query = SaleInvoice::query();

        [$fromDate, $toDate] = $this->invoiceListDates($request);
        if ($fromDate && $toDate) {
            $query->whereBetween('invoiceDate', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $query->where('invoiceDate', '>=', $fromDate);
        } elseif ($toDate) {
            $query->where('invoiceDate', '<=', $toDate);
        }

        $area = $request->query('area');
        if ($area) {
            $customerCodes = NewCustomer::query()->excludeProductSupplier()->where('custArea', $area)->pluck('custCode');
            if ($customerCodes->isNotEmpty()) {
                $query->whereIn('customerCode', $customerCodes);
            }
        }

        if ($request->query('customerCode')) {
            $query->where('customerCode', $request->query('customerCode'));
        }

        $saleType = $request->query('sale_type');
        if (in_array($saleType, ['cash', 'credit'], true)) {
            $query->where('sale_type', $saleType);
        }

        $customerTable = (new NewCustomer)->getTable();
        $invoices = $query
            ->leftJoin($customerTable.' as cust', 'sale_invoices.customerCode', '=', 'cust.custCode')
            ->select([
                'sale_invoices.*',
                'cust.custAddress as customerAddress',
                'cust.custCity as customerCity',
            ])
            ->orderByDesc('sale_invoices.invoiceDate')
            ->orderByDesc('sale_invoices.id')
            ->get();
        $this->fillMissingOrderNumbers($invoices);

        return $invoices;
    }

    /**
     * Older completed orders store the invoice on the order only.
     * Show that order number beside the invoice when the invoice itself has none.
     */
    protected function fillMissingOrderNumbers(Collection $invoices): void
    {
        $missing = $invoices
            ->filter(fn (SaleInvoice $invoice) => blank($invoice->orderNo))
            ->pluck('invoiceNo')
            ->filter()
            ->values();

        if ($missing->isEmpty()) {
            return;
        }

        $linked = Order::query()
            ->whereIn('converted_invoice_no', $missing)
            ->pluck('orderNo', 'converted_invoice_no');

        foreach ($invoices as $invoice) {
            if (blank($invoice->orderNo) && $linked->has($invoice->invoiceNo)) {
                $invoice->orderNo = $linked->get($invoice->invoiceNo);
                $invoice->syncOriginalAttribute('orderNo');
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function persistItems(SaleInvoice $invoice, string $invoiceNo, array $items, float $revenueRatio): void
    {
        $productCosts = newProduct::query()
            ->whereIn('productCode', collect($items)->pluck('productCode')->filter()->unique())
            ->pluck('costRate', 'productCode');

        foreach ($items as $item) {
            $costRate = (float) ($item['cost_rate'] ?? 0);
            if ($costRate <= 0) {
                $costRate = (float) ($productCosts[$item['productCode']] ?? 0);
            }
            if ($revenueRatio < 1.0) {
                $costRate = $costRate * $revenueRatio;
            }

            SaleInvoiceItem::create([
                'sale_invoice_id' => $invoice->id,
                'invoiceNo' => $invoiceNo,
                'productCode' => $item['productCode'],
                'productName' => $item['productName'],
                'variant_id' => $item['variant_id'] ?? null,
                'variant_name' => $item['variant_name'] ?? null,
                'variant_value' => $item['variant_value'] ?? null,
                'variant_added_price' => $item['variant_added_price'] ?? 0,
                'qty' => $item['qty'],
                'rate' => $item['rate'],
                'cost_rate' => $costRate,
                'discount' => $item['discount'] ?? 0,
                'amount' => $item['amount'],
            ]);
        }
    }
}
