<?php

namespace App\Http\Controllers;

use App\Models\NewCustomer;
use App\Models\CustomerArea;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Exception;

class NewCustomerController extends Controller
{
    /**
     * Display a listing of customers.
     *
     * @return View
     */
    public function index(): View
    {
        $customers = NewCustomer::orderBy('custName', 'asc')->get();
        
        return view('pages.Setup.Customer', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     *
     * @return View
     */
    public function create(): View
    {
        $custAreas = CustomerArea::orderBy('id', 'asc')->get();
        
        return view('pages.Setup.NewCustomer', compact('custAreas'));
    }

    /**
     * Store a newly created customer in storage.
     *
     * @param StoreCustomerRequest $request
     * @return RedirectResponse
     */
    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        try {
            NewCustomer::create($request->validated());

            return redirect()
                ->route('Customer.index')
                ->with('success', 'Customer created successfully!');

        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create customer. Please try again.');
        }
    }

    /**
     * Display the specified customer.
     *
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $customer = NewCustomer::findOrFail($id);
        
        return view('pages.Setup.showCustomer', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     *
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        $customer = NewCustomer::findOrFail($id);
        $custAreas = CustomerArea::orderBy('id', 'asc')->get();

        return view('pages.Setup.editCustomer', compact('customer', 'custAreas'));
    }

    /**
     * Update the specified customer in storage.
     *
     * @param UpdateCustomerRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(UpdateCustomerRequest $request, int $id): RedirectResponse
    {
        try {
            $customer = NewCustomer::findOrFail($id);
            $customer->update($request->validated());

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
     * Remove the specified customer from storage.
     *
     * @param int $id
     * @return RedirectResponse
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
