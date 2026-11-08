<?php

namespace App\Services;

use App\Models\Guds;
use App\Models\SaleInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Sale Invoice bilty info grouped by Guds (transporter).
 */
class BiltyGudsReportService
{
    /**
     * @return array{from: string, to: string}
     */
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

    /**
     * @return array{guds_name: string, only_with_bilty: bool, delivery_status: string}
     */
    public function resolveFilters(Request $request): array
    {
        $status = strtolower(trim((string) $request->input('delivery_status', 'all')));
        if (! in_array($status, ['all', 'delivered', 'non_delivered'], true)) {
            $status = 'all';
        }

        // Default: only bilty-related rows. Pass only_with_bilty=0 to include all invoices.
        $onlyWithBilty = $request->has('only_with_bilty')
            ? $request->boolean('only_with_bilty')
            : true;

        return [
            'guds_name' => trim((string) $request->input('guds_name', '')),
            'only_with_bilty' => $onlyWithBilty,
            'delivery_status' => $status,
        ];
    }

    /**
     * @return Collection<int, string>
     */
    public function gudsOptions(): Collection
    {
        $fromMaster = Guds::query()
            ->orderBy('gudsName')
            ->pluck('gudsName');

        $fromInvoices = SaleInvoice::query()
            ->whereNotNull('gudsName')
            ->where('gudsName', '!=', '')
            ->distinct()
            ->orderBy('gudsName')
            ->pluck('gudsName');

        return $fromMaster->merge($fromInvoices)->unique()->sort()->values();
    }

    /**
     * @param  array{from: string, to: string}  $range
     * @param  array{guds_name: string, only_with_bilty: bool, delivery_status: string}  $filters
     * @return array{
     *   groups: Collection<int, array{guds_name: string, invoices: Collection, totals: array<string, float|int>}>,
     *   grand: array<string, float|int>,
     *   counts: array{all: int, delivered: int, non_delivered: int}
     * }
     */
    public function build(array $range, array $filters): array
    {
        $base = $this->baseQuery($range, $filters);

        $counts = [
            'all' => (clone $base)->count(),
            'delivered' => (clone $base)->where('si.delivered', true)->count(),
            'non_delivered' => (clone $base)->where(function ($q) {
                $q->where('si.delivered', false)->orWhereNull('si.delivered');
            })->count(),
        ];

        $query = clone $base;
        if ($filters['delivery_status'] === 'delivered') {
            $query->where('si.delivered', true);
        } elseif ($filters['delivery_status'] === 'non_delivered') {
            $query->where(function ($q) {
                $q->where('si.delivered', false)->orWhereNull('si.delivered');
            });
        }

        $rows = $query->get([
            'si.id',
            'si.invoiceNo',
            'si.invoiceDate',
            'si.customerCode',
            'si.customerName',
            'si.city',
            'si.gudsName',
            'si.biltyNo',
            'si.corten',
            'si.biltyExpanse',
            'si.loaderExpanse',
            'si.biltyBalance',
            'si.delivered',
            'si.delivered_date',
            'si.grandTotal',
            'si.sale_type',
            'si.status',
            'nc.bussnessName as partyName',
            'nc.custAddress as customerAddress',
            'nc.custCity as customerCity',
        ]);

        $invoices = $rows->map(function ($row) {
            $party = trim((string) ($row->partyName ?? ''));
            if ($party === '') {
                $party = trim((string) ($row->customerName ?? ''));
            }

            $addr = trim((string) ($row->customerAddress ?? ''));
            $city = trim((string) (($row->customerCity ?? '') ?: ($row->city ?? '')));
            $addressParts = array_values(array_filter([$addr, $city], fn ($p) => $p !== ''));

            $row->party_display = $party !== '' ? $party : '—';
            $row->address_display = $addressParts === [] ? '—' : implode(', ', $addressParts);
            $row->name_display = trim((string) ($row->customerName ?? '')) !== ''
                ? trim((string) $row->customerName)
                : '—';
            $row->is_delivered = (bool) ($row->delivered ?? false);

            return $row;
        });

        $grouped = $invoices->groupBy(function ($inv) {
            $name = trim((string) ($inv->gudsName ?? ''));

            return $name !== '' ? $name : '— Unassigned —';
        });

        $groups = $grouped->map(function (Collection $rows, string $gudsName) {
            $totals = $this->sumRows($rows);

            return [
                'guds_name' => $gudsName,
                'invoices' => $rows->values(),
                'totals' => $totals,
            ];
        })->values();

        return [
            'groups' => $groups,
            'grand' => $this->sumRows($invoices),
            'counts' => $counts,
        ];
    }

    public function updateDelivered(string $invoiceNo, bool $delivered): SaleInvoice
    {
        $invoice = SaleInvoice::query()->where('invoiceNo', $invoiceNo)->firstOrFail();
        $invoice->delivered = $delivered;
        $invoice->delivered_date = $delivered ? now() : null;
        $invoice->save();

        return $invoice;
    }

    /**
     * @param  list<string>  $invoiceNos
     * @return int  Number of invoices updated
     */
    public function bulkUpdateDelivered(array $invoiceNos, bool $delivered): int
    {
        $invoiceNos = array_values(array_unique(array_filter(array_map(
            static fn ($n) => trim((string) $n),
            $invoiceNos
        ))));

        if ($invoiceNos === []) {
            return 0;
        }

        $query = SaleInvoice::query()->whereIn('invoiceNo', $invoiceNos);

        // Only flip rows that still need the change (already-delivered cannot be re-marked).
        if ($delivered) {
            $query->where(function ($q) {
                $q->where('delivered', false)->orWhereNull('delivered');
            });
        } else {
            $query->where('delivered', true);
        }

        return $query->update([
            'delivered' => $delivered,
            'delivered_date' => $delivered ? now() : null,
            'updated_at' => now(),
        ]);
    }

    /**
     * @param  array{from: string, to: string}  $range
     * @param  array{guds_name: string, only_with_bilty: bool, delivery_status?: string}  $filters
     */
    protected function baseQuery(array $range, array $filters)
    {
        $query = SaleInvoice::query()
            ->from('sale_invoices as si')
            ->leftJoin('newcustomers as nc', 'nc.custCode', '=', 'si.customerCode')
            ->whereDate('si.invoiceDate', '>=', $range['from'])
            ->whereDate('si.invoiceDate', '<=', $range['to'])
            ->orderBy('si.gudsName')
            ->orderBy('si.invoiceDate')
            ->orderBy('si.invoiceNo');

        if ($filters['guds_name'] !== '') {
            $query->where('si.gudsName', $filters['guds_name']);
        }

        if ($filters['only_with_bilty']) {
            $query->where(function ($q) {
                $q->where(function ($inner) {
                    $inner->whereNotNull('si.gudsName')->where('si.gudsName', '!=', '');
                })
                    ->orWhere(function ($inner) {
                        $inner->whereNotNull('si.biltyNo')->where('si.biltyNo', '!=', '');
                    })
                    ->orWhere('si.corten', '>', 0)
                    ->orWhere('si.biltyExpanse', '>', 0)
                    ->orWhere('si.loaderExpanse', '>', 0)
                    ->orWhere('si.biltyBalance', '!=', 0);
            });
        }

        return $query;
    }

    /**
     * @param  Collection<int, mixed>  $rows
     * @return array{invoice_count: int, corten: int, bilty_expanse: float, loader_expanse: float, bilty_balance: float, grand_total: float, delivered_count: int}
     */
    protected function sumRows(Collection $rows): array
    {
        return [
            'invoice_count' => $rows->count(),
            'corten' => (int) $rows->sum(fn ($r) => (int) ($r->corten ?? 0)),
            'bilty_expanse' => round((float) $rows->sum(fn ($r) => (float) ($r->biltyExpanse ?? 0)), 2),
            'loader_expanse' => round((float) $rows->sum(fn ($r) => (float) ($r->loaderExpanse ?? 0)), 2),
            'bilty_balance' => round((float) $rows->sum(fn ($r) => (float) ($r->biltyBalance ?? 0)), 2),
            'grand_total' => round((float) $rows->sum(fn ($r) => (float) ($r->grandTotal ?? 0)), 2),
            'delivered_count' => (int) $rows->filter(fn ($r) => (bool) ($r->delivered ?? false))->count(),
        ];
    }
}
