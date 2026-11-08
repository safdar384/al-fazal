<?php

namespace App\Services;

use App\Models\NewCustomer;
use App\Models\PurchaseInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PurchaseSummaryReportService
{
    public function resolveDateRange(Request $request): array
    {
        $defaultFrom = now()->startOfMonth()->format('Y-m-d');
        $defaultTo = now()->format('Y-m-d');

        try {
            $from = Carbon::parse($request->input('from_date', $defaultFrom))->format('Y-m-d');
            $to = Carbon::parse($request->input('to_date', $defaultTo))->format('Y-m-d');
        } catch (\Throwable) {
            $from = $defaultFrom;
            $to = $defaultTo;
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return ['from' => $from, 'to' => $to];
    }

    public function resolveFilters(Request $request): array
    {
        return [
            'supplier_code' => $request->string('supplier_code')->toString(),
            'product_code' => $request->string('product_code')->toString(),
            'payment_status' => $request->string('payment_status')->toString(),
        ];
    }

    public function filterOptions(): array
    {
        return [
            'suppliers' => NewCustomer::query()
                ->rawMaterialSuppliers()
                ->orderBy('custName')
                ->get(['custCode', 'custName', 'custCity']),
            'products' => DB::table('product_info')->orderBy('productName')->get(['productCode', 'productName']),
        ];
    }

    /**
     * @param  array{from: string, to: string}  $range
     * @param  array<string, string>  $filters
     */
    public function overallSummary(array $range, array $filters): array
    {
        $key = 'purchase_summary_'.md5(json_encode([$range, $filters]));
        $ttl = (int) config('purchases.summary_report_cache_seconds', 300);

        return Cache::remember($key, $ttl, function () use ($range, $filters) {
            $query = $this->baseInvoiceQuery($range, $filters);
            $row = $query->select([
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('COALESCE(SUM(grandTotal), 0) as total_purchases'),
                DB::raw('COALESCE(SUM(paid_amount), 0) as total_paid'),
                DB::raw('COALESCE(SUM(grandTotal - paid_amount), 0) as total_outstanding'),
            ])->first();

            return [
                'invoice_count' => (int) ($row->invoice_count ?? 0),
                'total_purchases' => round((float) ($row->total_purchases ?? 0), 2),
                'total_paid' => round((float) ($row->total_paid ?? 0), 2),
                'total_outstanding' => round(max(0, (float) ($row->total_outstanding ?? 0)), 2),
            ];
        });
    }

    /**
     * @param  array{from: string, to: string}  $range
     * @param  array<string, string>  $filters
     */
    public function invoiceRows(array $range, array $filters): Collection
    {
        return $this->baseInvoiceQuery($range, $filters)
            ->orderByDesc('invoiceDate')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @param  array{from: string, to: string}  $range
     * @param  array<string, string>  $filters
     */
    public function supplierRows(array $range, array $filters): Collection
    {
        return $this->baseInvoiceQuery($range, $filters)
            ->groupBy('supplierCode', 'supplierName')
            ->orderBy('supplierName')
            ->select([
                'supplierCode',
                'supplierName',
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('COALESCE(SUM(grandTotal), 0) as total_purchases'),
                DB::raw('COALESCE(SUM(paid_amount), 0) as total_paid'),
            ])
            ->get()
            ->map(function ($row) {
                $row->total_outstanding = max(0, round((float) $row->total_purchases - (float) $row->total_paid, 2));

                return $row;
            });
    }

    /**
     * @param  array{from: string, to: string}  $range
     */
    public function pendingSupplierBalances(array $range): Collection
    {
        return NewCustomer::query()
            ->rawMaterialSuppliers()
            ->orderBy('custName')
            ->get(['custCode', 'custName'])
            ->map(function ($supplier) {
                $payable = (float) DB::table('customerledgers')
                    ->where('customerCode', $supplier->custCode)
                    ->selectRaw('COALESCE(SUM(credit), 0) - COALESCE(SUM(debit), 0) as payable')
                    ->value('payable');

                $supplier->payable_balance = round(max(0, $payable), 2);

                return $supplier;
            })
            ->filter(fn ($s) => $s->payable_balance > 0)
            ->values();
    }

    /**
     * @param  array{from: string, to: string}  $range
     * @param  array<string, string>  $filters
     */
    protected function baseInvoiceQuery(array $range, array $filters)
    {
        $query = PurchaseInvoice::query()->whereBetween('invoiceDate', [$range['from'], $range['to']]);

        if ($filters['supplier_code']) {
            $query->where('supplierCode', $filters['supplier_code']);
        }

        if ($filters['payment_status']) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if ($filters['product_code']) {
            $query->whereExists(function ($sub) use ($filters) {
                $sub->select(DB::raw(1))
                    ->from('purchase_invoice_items as pii')
                    ->whereColumn('pii.purchase_invoice_id', 'purchase_invoices.id')
                    ->where('pii.productCode', $filters['product_code']);
            });
        }

        return $query;
    }
}
