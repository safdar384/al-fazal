<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\CustomerArea;
use App\Models\CustomerCheque;
use App\Models\CustomerLedger;
use App\Models\CustomerProductRate;
use App\Models\Labour;
use App\Models\NewCustomer;
use App\Models\Order;
use App\Models\PurchaseInvoice;
use App\Models\RecoveryVoucher;
use App\Models\SaleInvoice;
use App\Models\SupplierTransaction;
use App\Support\CustomerBalanceReport;
use App\Support\PartyBalance;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NewCustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(): View
    {
        $customers = CustomerBalanceReport::enrichCustomerList(
            NewCustomer::orderBy('custName', 'asc')->get()
        );

        $portfolio = PartyBalance::portfolioTotals();
        $portfolio['net_balance'] = round($portfolio['total_payable'] - $portfolio['total_receivable'], 2);

        return view('Pages.Setup.customer', compact('customers', 'portfolio'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): View
    {
        $custAreas = CustomerArea::orderBy('areaName', 'asc')->get();
        $custCatagories = NewCustomer::CATEGORIES;
        $usedAreaCodes = NewCustomer::query()
            ->whereNotNull('custArea')
            ->where('custArea', '!=', '')
            ->pluck('custArea')
            ->map(fn ($code) => strtoupper(trim((string) $code)))
            ->unique()
            ->values()
            ->all();
        $agentLabours = Labour::whereRaw('LOWER(labourCatagory) = ?', ['ajent'])
            ->orderBy('labourName', 'asc')
            ->get(['labourCode', 'labourName']);

        return view('Pages.Setup.newCustomer', compact('custAreas', 'custCatagories', 'usedAreaCodes', 'agentLabours'));
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(StoreCustomerRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $customer = NewCustomer::create($request->validated());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Customer created successfully!',
                    'customer' => [
                        'id' => $customer->custCode,
                        'custCode' => $customer->custCode,
                        'code' => $customer->custCode,
                        'custName' => $customer->custName,
                        'name' => $customer->custName,
                        'business' => $customer->bussnessName ?? '',
                        'city' => $customer->custCity ?? '',
                        'phone' => $customer->custContect ?? '',
                        'address' => $customer->custAddress ?? '',
                        'area' => $customer->custArea ?? '',
                        'discount' => (float) ($customer->custdiscount ?? 0),
                        'text' => trim(($customer->bussnessName ?: $customer->custName).($customer->custCity ? ' — '.$customer->custCity : '')),
                        'label' => trim(($customer->bussnessName ?: $customer->custName).($customer->custCity ? ' — '.$customer->custCity : '')),
                    ],
                ]);
            }

            return redirect()
                ->route('Customer.create')
                ->with('success', 'Customer created successfully!');

        } catch (Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create customer. Please try again.',
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create customer. Please try again.');
        }
    }

    /**
     * Display the specified customer.
     */
    public function show(int $id): View
    {
        $customer = NewCustomer::findOrFail($id);

        return view('Pages.Setup.showCustomer', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(int $id): View
    {
        $customer = NewCustomer::findOrFail($id);
        $custAreas = CustomerArea::orderBy('id', 'asc')->get();
        $custCatagories = NewCustomer::CATEGORIES;
        $codeLocked = $this->customerHasTransactionalData((string) $customer->custCode);

        return view('Pages.Setup.editCustomer', compact('customer', 'custAreas', 'custCatagories', 'codeLocked'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(UpdateCustomerRequest $request, int $id): RedirectResponse
    {
        try {
            $customer = NewCustomer::findOrFail($id);
            $validated = $request->validated();
            $oldCode = strtoupper(trim((string) $customer->custCode));
            $newCode = strtoupper(trim((string) ($validated['custCode'] ?? '')));

            if ($newCode !== $oldCode && $this->customerHasTransactionalData($oldCode)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors([
                        'custCode' => 'Customer code cannot be changed because ledger / transaction entries exist for this customer.',
                    ]);
            }

            $customer->update($validated);

            return redirect()
                ->route('Customer.index')
                ->with('success', 'Customer updated successfully!');

        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update customer. Please try again.');
        }
    }

    /**
     * True when this customer code is used in ledger or related documents.
     */
    protected function customerHasTransactionalData(string $custCode): bool
    {
        $code = strtoupper(trim($custCode));
        if ($code === '') {
            return false;
        }

        if (CustomerLedger::whereRaw('UPPER(customerCode) = ?', [$code])->exists()) {
            return true;
        }
        if (SaleInvoice::whereRaw('UPPER(customerCode) = ?', [$code])->exists()) {
            return true;
        }
        if (Order::whereRaw('UPPER(customerCode) = ?', [$code])->exists()) {
            return true;
        }
        if (PurchaseInvoice::whereRaw('UPPER(supplierCode) = ?', [$code])->exists()) {
            return true;
        }
        if (RecoveryVoucher::whereRaw('UPPER(customerCode) = ?', [$code])->exists()) {
            return true;
        }
        if (CustomerCheque::whereRaw('UPPER(customer_code) = ?', [$code])->exists()) {
            return true;
        }
        if (CustomerProductRate::whereRaw('UPPER(customerCode) = ?', [$code])->exists()) {
            return true;
        }
        if (SupplierTransaction::whereRaw('UPPER(supplier_code) = ?', [$code])->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $customer = NewCustomer::findOrFail($id);
            $customerName = $customer->custName;
            $customer->delete();

            return redirect()
                ->route('Customer.index')
                ->with('success', "Customer '{$customerName}' deleted successfully!");

        } catch (Exception $e) {
            return redirect()
                ->route('Customer.index')
                ->with('error', 'Failed to delete customer. Please try again.');
        }
    }
}
