<?php

namespace App\Services;

use App\Models\CustomerProductRate;
use App\Models\NewCustomer;
use App\Models\newProduct;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseInvoicePayment;
use App\Support\GeneralLedger;
use App\Support\PartyBalance;
use App\Support\PartyLedger;
use App\Support\ListPage;
use App\Support\PurchaseStockService;
use App\Support\SaleInvoiceCalculator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseInvoiceService
{
    public function nextInvoiceNumber(): string
    {
        $last = PurchaseInvoice::query()->orderByDesc('id')->first();

        if (! $last || ! preg_match('/PINV-(\d+)/', $last->invoiceNo, $m)) {
            return 'PINV-00001';
        }

        return 'PINV-'.str_pad((string) ((int) $m[1] + 1), 5, '0', STR_PAD_LEFT);
    }

    /**
     * @return array{suppliers: Collection, products: Collection, transports: Collection, nextInvoiceNo: string}
     */
    public function formData(): array
    {
        return [
            // Purchase Invoice: Raw Material Supplier only (legacy Supplier/Suplier kept for old records)
            'suppliers' => NewCustomer::query()
                ->rawMaterialSuppliers()
                ->orderBy('custName')
                ->get(['custCode', 'custName', 'bussnessName', 'custAddress', 'custCity', 'custBalance']),
            'transports' => NewCustomer::query()
                ->whereRaw('LOWER(custCatagory) = ?', ['transport'])
                ->orderByRaw("COALESCE(NULLIF(TRIM(bussnessName), ''), custName)")
                ->get(['custCode', 'custName', 'bussnessName', 'custCity']),
            'products' => newProduct::query()->with('variants')->orderBy('productName')->get(),
            'nextInvoiceNo' => $this->nextInvoiceNumber(),
        ];
    }

    public function supplierDetails(string $supplierCode): array
    {
        $supplier = NewCustomer::query()->where('custCode', $supplierCode)->first();
        if (! $supplier) {
            throw new InvalidArgumentException('Supplier not found');
        }

        $meta = PartyBalance::meta($supplierCode);

        return [
            'custCode' => $supplier->custCode,
            'custName' => $supplier->custName,
            'custCity' => $supplier->custCity,
            'balance' => $meta['balance'],
            'balance_label' => $meta['balance_label'],
            'balance_type' => $meta['balance_type'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function productDetails(string $productCode, ?string $supplierCode = null): array
    {
        $product = newProduct::query()->with('variants')->where('productCode', $productCode)->first();
        if (! $product) {
            throw new InvalidArgumentException('Product not found');
        }

        $ratesByVariant = collect();
        if ($supplierCode) {
            $ratesByVariant = CustomerProductRate::query()
                ->where('customerCode', $supplierCode)
                ->where('productCode', $productCode)
                ->where('is_active', true)
                ->forRateType(CustomerProductRate::TYPE_PURCHASE)
                ->get()
                ->keyBy(fn ($r) => $r->variant_id === null ? 'base' : (string) $r->variant_id);
        }

        $baseRate = $ratesByVariant->get('base');
        $hasSpecialRate = $baseRate !== null;
        $specialRate = $hasSpecialRate ? (float) $baseRate->specialRate : null;

        $variantsToShow = $product->variants;
        if ($supplierCode) {
            $withSpecial = $product->variants->filter(fn ($v) => $ratesByVariant->has((string) $v->id));
            if ($withSpecial->isNotEmpty()) {
                $variantsToShow = $withSpecial;
            }
        }

        $variantsWithRates = $variantsToShow->map(function ($v) use ($ratesByVariant, $product) {
            $variantRate = $ratesByVariant->get((string) $v->id);
            $hasVariantSpecialRate = $variantRate !== null;

            return [
                'id' => $v->id,
                'name' => $v->variantName,
                'value' => $v->variantValue,
                'stockQty' => $v->stockQty ?? 0,
                'specialRate' => $hasVariantSpecialRate ? (float) $variantRate->specialRate : null,
                'hasSpecialRate' => $hasVariantSpecialRate,
            ];
        })->values();

        return [
            'productCode' => $product->productCode,
            'productName' => $product->productName,
            'purchaseRate' => $hasSpecialRate ? $specialRate : (float) ($product->purchaseRate ?? 0),
            'standardRate' => (float) ($product->purchaseRate ?? 0),
            'hasSpecialRate' => $hasSpecialRate,
            'specialRate' => $specialRate,
            'variants' => $variantsWithRates,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{invoice: PurchaseInvoice, previewUrl: string}
     */
    public function store(array $validated): array
    {
        return DB::transaction(function () use ($validated) {
            $computed = $this->computeTotals($validated);
            $this->assertUniqueLineItems($computed['items']);

            $paidAmount = round(min((float) ($validated['paid_amount'] ?? 0), $computed['grandTotal']), 2);

            $invoice = PurchaseInvoice::create([
                'invoiceNo' => $validated['invoiceNo'],
                'invoiceDate' => $validated['invoiceDate'],
                'supplierCode' => $validated['supplierCode'],
                'supplierName' => $validated['supplierName'],
                'city' => $validated['city'] ?? '',
                'balance' => $validated['balance'] ?? 0,
                'corten' => (int) ($validated['corten'] ?? 0),
                'biltyExpanse' => (float) ($validated['biltyExpanse'] ?? 0),
                'loaderExpanse' => (float) ($validated['loaderExpanse'] ?? 0),
                'biltyBalance' => (float) ($validated['biltyBalance'] ?? 0),
                'comments' => $validated['comments'] ?? '',
                'transportCode' => $validated['transportCode'] ?? null,
                'transportName' => $validated['transportName'] ?? null,
                'vehicleNumber' => $validated['vehicleNumber'] ?? null,
                'dispatchNo' => $validated['dispatchNo'] ?? null,
                'weight' => $validated['weight'] ?? null,
                'transportRate' => $validated['transportRate'] ?? null,
                'transportAmount' => $validated['transportAmount'] ?? null,
                'noOfItems' => $computed['noOfItems'],
                'invoiceTotal' => $computed['invoiceTotal'],
                'invoiceDiscount' => $computed['invoiceDiscount'],
                'grandTotal' => $computed['grandTotal'],
                'paid_amount' => $paidAmount,
                'payment_status' => $this->resolvePaymentStatus($paidAmount, $computed['grandTotal']),
                'status' => 'Active',
            ]);

            $this->persistItems($invoice, $validated['invoiceNo'], $computed['items']);
            PurchaseStockService::receive($this->stockItemsFrom($computed['items']));
            $this->applyTransportCost($invoice->fresh(['items']));

            PartyLedger::postPurchaseInvoice(
                $validated['supplierCode'],
                $validated['invoiceNo'],
                $validated['invoiceDate'],
                $computed['grandTotal']
            );
            $this->postTransportLedger($validated, $validated['invoiceNo']);

            GeneralLedger::postPurchaseInvoice(
                $validated['invoiceDate'],
                $validated['invoiceNo'],
                $computed['grandTotal']
            );

            if ($paidAmount > 0) {
                $this->recordPayment($invoice, $paidAmount, $validated['invoiceDate'], 'Initial payment on invoice');
            }

            return [
                'invoice' => $invoice->fresh(['items', 'payments']),
                'previewUrl' => route('purchaseInvoice.preview', $invoice->invoiceNo),
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(PurchaseInvoice $invoice, array $validated): PurchaseInvoice
    {
        return DB::transaction(function () use ($invoice, $validated) {
            $invoiceNo = $invoice->invoiceNo;
            $oldStock = $this->stockItemsFromInvoice($invoice);

            $computed = $this->computeTotals($validated);
            $this->assertUniqueLineItems($computed['items']);

            PurchaseStockService::reverse($oldStock);
            $this->reverseTransportCost($invoice);

            $paidAmount = round(min((float) ($validated['paid_amount'] ?? $invoice->paid_amount), $computed['grandTotal']), 2);

            $invoice->update([
                'invoiceDate' => $validated['invoiceDate'],
                'supplierCode' => $validated['supplierCode'],
                'supplierName' => $validated['supplierName'],
                'city' => $validated['city'] ?? $invoice->city,
                'comments' => array_key_exists('comments', $validated) ? $validated['comments'] : $invoice->comments,
                'transportCode' => $validated['transportCode'] ?? $invoice->transportCode,
                'transportName' => $validated['transportName'] ?? $invoice->transportName,
                'vehicleNumber' => $validated['vehicleNumber'] ?? $invoice->vehicleNumber,
                'dispatchNo' => $validated['dispatchNo'] ?? $invoice->dispatchNo,
                'weight' => array_key_exists('weight', $validated) ? $validated['weight'] : $invoice->weight,
                'transportRate' => array_key_exists('transportRate', $validated) ? $validated['transportRate'] : $invoice->transportRate,
                'transportAmount' => array_key_exists('transportAmount', $validated) ? $validated['transportAmount'] : $invoice->transportAmount,
                'corten' => (int) ($validated['corten'] ?? 0),
                'biltyExpanse' => (float) ($validated['biltyExpanse'] ?? 0),
                'loaderExpanse' => (float) ($validated['loaderExpanse'] ?? 0),
                'biltyBalance' => (float) ($validated['biltyBalance'] ?? 0),
                'noOfItems' => $computed['noOfItems'],
                'invoiceTotal' => $computed['invoiceTotal'],
                'invoiceDiscount' => $computed['invoiceDiscount'],
                'grandTotal' => $computed['grandTotal'],
                'paid_amount' => $paidAmount,
                'payment_status' => $this->resolvePaymentStatus($paidAmount, $computed['grandTotal']),
            ]);

            PurchaseInvoiceItem::query()->where('purchase_invoice_id', $invoice->id)->delete();
            $this->persistItems($invoice, $invoiceNo, $computed['items']);
            PurchaseStockService::receive($this->stockItemsFrom($computed['items']));
            $this->applyTransportCost($invoice->fresh(['items']));

            GeneralLedger::voidByReference($invoiceNo);
            PartyLedger::syncPurchaseInvoice(
                $validated['supplierCode'],
                $invoiceNo,
                $validated['invoiceDate'],
                $computed['grandTotal']
            );
            $this->postTransportLedger($validated, $invoiceNo);
            GeneralLedger::postPurchaseInvoice($validated['invoiceDate'], $invoiceNo, $computed['grandTotal']);

            return $invoice->fresh(['items', 'payments']);
        });
    }

    public function delete(PurchaseInvoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $invoiceNo = $invoice->invoiceNo;
            PurchaseStockService::reverse($this->stockItemsFromInvoice($invoice));
            $this->reverseTransportCost($invoice);

            $payments = PurchaseInvoicePayment::query()->where('purchase_invoice_id', $invoice->id)->get();
            foreach ($payments as $payment) {
                if ($payment->ledger_id) {
                    DB::table('customerledgers')->where('id', $payment->ledger_id)->delete();
                }
            }
            PurchaseInvoicePayment::query()->where('purchase_invoice_id', $invoice->id)->delete();

            GeneralLedger::voidByReference($invoiceNo);
            PartyLedger::deleteByInvoiceNumber($invoiceNo);
            PurchaseInvoiceItem::query()->where('purchase_invoice_id', $invoice->id)->delete();
            $invoice->delete();
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    protected function postTransportLedger(array $validated, string $invoiceNo): void
    {
        PartyLedger::postTransportCharge(
            (string) ($validated['transportCode'] ?? ''),
            $invoiceNo,
            (string) $validated['invoiceDate'],
            (float) ($validated['weight'] ?? 0),
            (float) ($validated['transportRate'] ?? 0),
            (float) ($validated['transportAmount'] ?? 0),
            (string) ($validated['vehicleNumber'] ?? ''),
            (string) ($validated['dispatchNo'] ?? '')
        );
    }

    /**
     * Split the transport amount across purchased units and add it to each product cost rate.
     */
    protected function applyTransportCost(PurchaseInvoice $invoice): void
    {
        $amount = round((float) ($invoice->transportAmount ?? 0), 2);
        $items = $invoice->items;
        if ($amount <= 0 || $items->isEmpty()) {
            return;
        }

        $totalQty = (float) $items->sum(fn ($item) => (float) $item->qty);
        if ($totalQty <= 0) {
            return;
        }

        $perUnit = round($amount / $totalQty, 2);
        if ($perUnit <= 0) {
            return;
        }

        $seen = [];
        foreach ($items as $item) {
            $item->update(['freightPerUnit' => $perUnit]);
            $code = (string) $item->productCode;
            if ($code === '' || isset($seen[$code])) {
                continue;
            }
            $seen[$code] = true;
            $product = newProduct::query()->where('productCode', $code)->lockForUpdate()->first();
            if (! $product) {
                continue;
            }
            $product->update([
                'costRate' => round((float) $product->costRate + $perUnit, 2),
            ]);
        }
    }

    /**
     * Remove the freight previously added to product cost rates by this invoice.
     */
    protected function reverseTransportCost(PurchaseInvoice $invoice): void
    {
        $seen = [];
        foreach ($invoice->items as $item) {
            $added = round((float) ($item->freightPerUnit ?? 0), 2);
            $code = (string) $item->productCode;
            if ($code === '' || $added <= 0 || isset($seen[$code])) {
                continue;
            }
            $seen[$code] = true;
            $product = newProduct::query()->where('productCode', $code)->lockForUpdate()->first();
            if (! $product) {
                continue;
            }
            $product->update([
                'costRate' => round(max(0, (float) $product->costRate - $added), 2),
            ]);
        }
    }

    /**
     * @return array{payment: PurchaseInvoicePayment, invoice: PurchaseInvoice}
     */
    public function addPayment(PurchaseInvoice $invoice, float $amount, string $paymentDate, ?string $notes = null): array
    {
        return DB::transaction(function () use ($invoice, $amount, $paymentDate, $notes) {
            $remaining = max(0, round((float) $invoice->grandTotal - (float) $invoice->paid_amount, 2));
            $amount = round($amount, 2);

            if ($amount <= 0 || $amount > $remaining + 0.02) {
                throw new InvalidArgumentException(
                    'Payment must be between 0.01 and remaining balance ('.number_format($remaining, 2).').'
                );
            }

            $ledger = PartyLedger::postSupplierPayment(
                $invoice->supplierCode,
                $paymentDate,
                $amount,
                'Payment against '.$invoice->invoiceNo.($notes ? ' — '.$notes : ''),
                $invoice->invoiceNo.'-PAY-'.now()->format('His')
            );

            $payment = PurchaseInvoicePayment::create([
                'purchase_invoice_id' => $invoice->id,
                'invoiceNo' => $invoice->invoiceNo,
                'payment_date' => $paymentDate,
                'amount' => $amount,
                'payment_method' => 'cash',
                'reference_no' => $invoice->invoiceNo.'-PAY-'.$ledger->id,
                'ledger_id' => $ledger->id,
                'notes' => $notes,
            ]);

            $newPaid = round((float) $invoice->paid_amount + $amount, 2);
            $invoice->update([
                'paid_amount' => $newPaid,
                'payment_status' => $this->resolvePaymentStatus($newPaid, (float) $invoice->grandTotal),
            ]);

            return ['payment' => $payment, 'invoice' => $invoice->fresh(['payments'])];
        });
    }

    public function paginateInvoices(Request $request): LengthAwarePaginator
    {
        $query = PurchaseInvoice::query()->select([
            'id', 'invoiceNo', 'invoiceDate', 'supplierCode', 'supplierName',
            'noOfItems', 'grandTotal', 'paid_amount', 'payment_status', 'status',
        ]);

        if ($request->query('fromDate')) {
            $query->where('invoiceDate', '>=', $request->query('fromDate'));
        }
        if ($request->query('toDate')) {
            $query->where('invoiceDate', '<=', $request->query('toDate'));
        }
        if ($request->query('supplierCode')) {
            $query->where('supplierCode', $request->query('supplierCode'));
        }
        if ($request->query('payment_status')) {
            $query->where('payment_status', $request->query('payment_status'));
        }
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('invoiceNo', 'like', '%'.$search.'%')
                    ->orWhere('supplierName', 'like', '%'.$search.'%');
            });
        }

        $perPage = ListPage::perPage($request);

        return $query->orderByDesc('invoiceDate')->orderByDesc('id')->paginate($perPage);
    }

    /**
     * @return Collection<int, PurchaseInvoice>
     */
    public function listForPrint(Request $request): Collection
    {
        $query = PurchaseInvoice::query()->with('items');

        if ($request->query('fromDate')) {
            $query->where('invoiceDate', '>=', $request->query('fromDate'));
        }
        if ($request->query('toDate')) {
            $query->where('invoiceDate', '<=', $request->query('toDate'));
        }
        if ($request->query('supplierCode')) {
            $query->where('supplierCode', $request->query('supplierCode'));
        }

        return $query->orderByDesc('invoiceDate')->orderByDesc('id')->get();
    }

    protected function resolvePaymentStatus(float $paid, float $grandTotal): string
    {
        if ($grandTotal <= 0) {
            return 'paid';
        }
        if ($paid <= 0) {
            return 'unpaid';
        }
        if ($paid >= $grandTotal - 0.02) {
            return 'paid';
        }

        return 'partial';
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function computeTotals(array $validated): array
    {
        $computed = SaleInvoiceCalculator::compute($validated['items'], 0);

        // Preserve existing form calculations (item % + invoice % discount from UI).
        $invoiceTotal = round((float) ($validated['invoiceTotal'] ?? $computed['invoiceTotal']), 2);
        $grandTotal = round((float) ($validated['grandTotal'] ?? $computed['grandTotal']), 2);
        $extra = round((float) ($validated['biltyBalance'] ?? 0), 2);
        if ($extra != 0) {
            $grandTotal = round($grandTotal + $extra, 2);
        }
        $invoiceDiscount = round(max(0, $invoiceTotal - $grandTotal), 2);

        $computed['invoiceTotal'] = $invoiceTotal;
        $computed['invoiceDiscount'] = $invoiceDiscount;
        $computed['grandTotal'] = $grandTotal;

        return $computed;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function assertUniqueLineItems(array $items): void
    {
        $keys = [];
        foreach ($items as $item) {
            $key = ($item['productCode'] ?? '').'|'.($item['variant_id'] ?? 'base');
            if (isset($keys[$key])) {
                throw new InvalidArgumentException('Duplicate product line: '.$item['productName'].' — merge quantities into one row.');
            }
            $keys[$key] = true;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function persistItems(PurchaseInvoice $invoice, string $invoiceNo, array $items): void
    {
        foreach ($items as $item) {
            PurchaseInvoiceItem::create([
                'purchase_invoice_id' => $invoice->id,
                'invoiceNo' => $invoiceNo,
                'productCode' => $item['productCode'],
                'productName' => $item['productName'],
                'variant_id' => $item['variant_id'] ?? null,
                'variant_name' => $item['variant_name'] ?? null,
                'variant_value' => $item['variant_value'] ?? null,
                'variant_added_price' => $item['variant_added_price'] ?? 0,
                'qty' => $item['qty'],
                'rate' => $item['rate'],
                'discount' => $item['discount'] ?? 0,
                'amount' => $item['amount'],
            ]);
        }
    }

    protected function recordPayment(PurchaseInvoice $invoice, float $amount, string $date, string $notes): void
    {
        $ledger = PartyLedger::postSupplierPayment(
            $invoice->supplierCode,
            $date,
            $amount,
            $notes.' '.$invoice->invoiceNo,
            $invoice->invoiceNo.'-PAY-'.now()->format('His')
        );

        PurchaseInvoicePayment::create([
            'purchase_invoice_id' => $invoice->id,
            'invoiceNo' => $invoice->invoiceNo,
            'payment_date' => $date,
            'amount' => $amount,
            'ledger_id' => $ledger->id,
            'notes' => $notes,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array{productCode: string, variant_id: int|null, qty: float}>
     */
    protected function stockItemsFrom(array $items): array
    {
        return collect($items)->map(fn ($i) => [
            'productCode' => $i['productCode'],
            'variant_id' => $i['variant_id'] ?? null,
            'qty' => $i['qty'],
        ])->all();
    }

    protected function stockItemsFromInvoice(PurchaseInvoice $invoice): array
    {
        return $invoice->items()->get(['productCode', 'variant_id', 'qty'])->map(fn ($row) => [
            'productCode' => $row->productCode,
            'variant_id' => $row->variant_id,
            'qty' => $row->qty,
        ])->all();
    }
}
