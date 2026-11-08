<?php

namespace App\Http\Controllers;

use App\Models\NewCustomer;
use App\Models\PurchaseInvoice;
use App\Support\PartyBalance;
use App\Support\SupplierLedgerReport;
use App\Support\SupplierModuleCache;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SupplierLedgerController extends Controller
{
    public function __construct(
        protected SupplierLedgerReport $ledgerReport
    ) {}

    protected function pdfAvailable(): bool
    {
        return class_exists(\Barryvdh\DomPDF\Facade\Pdf::class);
    }

    public function index(Request $request): View
    {
        return view('Pages.customerAccount.supplierLedger', $this->baseViewData());
    }

    public function partyBalance(string $supplierCode): JsonResponse
    {
        $meta = SupplierModuleCache::partyMeta($supplierCode);

        if (! $meta['is_supplier'] && ! NewCustomer::query()->where('custCode', $supplierCode)->exists()) {
            $hasPurchases = PurchaseInvoice::query()->where('supplierCode', $supplierCode)->exists();
            if (! $hasPurchases) {
                return response()->json(['message' => 'Supplier not found'], 404);
            }
            $meta['is_supplier'] = true;
            $meta['balance_type'] = PartyBalance::TYPE_PAYABLE;
            $meta['balance_label'] = 'Payable Balance';
            $signed = PartyBalance::ledgerSignedBalance($supplierCode);
            $meta['ledger_signed'] = $signed;
            $meta['balance'] = (float) (-$signed);
        }

        return response()->json($meta);
    }

    public function generate(Request $request): Response|View
    {
        $validated = $request->validate([
            'supplier_code' => 'required|string',
            'from_date' => 'required|date',
            'to_date' => 'required|date',
            'transaction_type' => 'nullable|string|in:all,purchase,sale,payment,discount,transport,other',
        ]);

        $fromDate = Carbon::parse($validated['from_date'])->format('Y-m-d');
        $toDate = Carbon::parse($validated['to_date'])->format('Y-m-d');
        if ($fromDate > $toDate) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        $supplierCode = $validated['supplier_code'];
        $transactionType = $validated['transaction_type'] ?? 'all';

        $built = SupplierModuleCache::ledgerReport(
            $supplierCode,
            $fromDate,
            $toDate,
            $transactionType,
            $this->ledgerReport
        );

        $supplierName = $this->resolveSupplierName($supplierCode);
        $viewData = array_merge($this->baseViewData(), [
            'ledgerRows' => collect($built['rows']),
            'summary' => $built['summary'],
            'selectedSupplierCode' => $supplierCode,
            'selectedSupplierName' => $supplierName,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'transactionType' => $transactionType,
            'isGenerated' => true,
            'balanceLabel' => $built['summary']['balance_label'],
        ]);

        if ($request->boolean('download_pdf')) {
            if (! $this->pdfAvailable()) {
                return response()->view('Pages.customerAccount.supplierLedger', array_merge($viewData, [
                    'pdfError' => 'PDF module missing: please install barryvdh/laravel-dompdf.',
                ]));
            }

            return Pdf::loadView('Pages.customerAccount.supplierLedgerPdf', $viewData)
                ->setPaper('a4', 'landscape')
                ->download('supplier-ledger-'.$supplierCode.'-'.now()->format('YmdHis').'.pdf');
        }

        if ($request->ajax() || $request->wantsJson() || $request->boolean('ajax')) {
            return response()->json([
                'success' => true,
                'supplier_code' => $supplierCode,
                'supplier_name' => $supplierName,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'transaction_type' => $transactionType,
                'rows' => $built['rows'],
                'summary' => $built['summary'],
                'balance_label' => $built['summary']['balance_label'] ?? 'Payable Balance',
            ]);
        }

        return response()->view('Pages.customerAccount.supplierLedger', $viewData);
    }

    public function preview(Request $request, string $supplierCode): View
    {
        $validated = $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date',
            'transaction_type' => 'nullable|string|in:all,purchase,sale,payment,discount,transport,other',
        ]);

        $fromDate = Carbon::parse($validated['from_date'])->format('Y-m-d');
        $toDate = Carbon::parse($validated['to_date'])->format('Y-m-d');
        if ($fromDate > $toDate) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        $transactionType = $validated['transaction_type'] ?? 'all';
        $built = SupplierModuleCache::ledgerReport(
            $supplierCode,
            $fromDate,
            $toDate,
            $transactionType,
            $this->ledgerReport
        );

        $periodText = Carbon::parse($fromDate)->format('d-M-Y').' to '.Carbon::parse($toDate)->format('d-M-Y');

        return view('Pages.customerAccount.supplierLedgerPreview', [
            'ledgerRows' => collect($built['rows']),
            'summary' => $built['summary'],
            'selectedSupplierCode' => $supplierCode,
            'selectedSupplierName' => $this->resolveSupplierName($supplierCode),
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'transactionType' => $transactionType,
            'periodText' => $periodText,
        ]);
    }

    protected function baseViewData(): array
    {
        return [
            'suppliers' => SupplierModuleCache::supplierOptions(),
            'ledgerRows' => collect(),
            'summary' => null,
            'selectedSupplierCode' => '',
            'selectedSupplierName' => '',
            'fromDate' => now()->startOfMonth()->format('Y-m-d'),
            'toDate' => now()->format('Y-m-d'),
            'transactionType' => 'all',
            'isGenerated' => false,
            'pdfAvailable' => $this->pdfAvailable(),
            'balanceLabel' => 'Payable Balance',
        ];
    }

    protected function resolveSupplierName(string $supplierCode): string
    {
        $fromCustomer = NewCustomer::query()
            ->where('custCode', $supplierCode)
            ->value('custName');

        if ($fromCustomer) {
            return (string) $fromCustomer;
        }

        return (string) (PurchaseInvoice::query()
            ->where('supplierCode', $supplierCode)
            ->value('supplierName') ?? $supplierCode);
    }
}
