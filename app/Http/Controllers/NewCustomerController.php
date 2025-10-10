<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NewCustomer;
use App\Models\CustomerArea;

class NewCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = NewCustomer::all(); // Fetch all customers
        return view('pages.Setup.Customer', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $custAreas = CustomerArea::orderBy('id', 'asc')->get();
        return view('pages.Setup.NewCustomer', compact('custAreas')); // Return the view for creating a new customer
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
        'custCode'        => 'required|string|max:50|unique:newcustomers,custCode',
        'custArea'        => 'required|string|max:100',
        'bussnessName'    => 'required|string|max:150',
        'custName'        => 'required|string|max:100',
        'custAddress'     => 'required|string|max:255',
        'custContect'     => 'required|string|max:50',
        'custCity'        => 'required|string|max:100',
        'custPage'        => 'required|string|max:100',
        'custdiscount'    => 'required|numeric|min:0|max:100',
        'custPayTermDay'  => 'required|integer|min:0',
        'custCreditLimit' => 'required|numeric|min:0',
        'custOppDate'     => 'required|date',
        'custBalance'     => 'required|numeric',
        'custCatagory'    => 'required|string|max:100',
    ]);

    NewCustomer::create($request->all());

    return redirect('/Customer')->with('success', 'Customer added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
         $customer = NewCustomer::findOrFail($id);
         $custAreas = CustomerArea::orderBy('id', 'asc')->get();

        return view('pages.Setup.editCustomer', compact('customer', 'custAreas'));
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, string $id)
    {
        $customer = NewCustomer::findOrFail($id);
        $customer->update($request->all());

    
    return redirect()->route('Customer.index')->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $customer = NewCustomer::findOrFail($id);
        $customer->delete();

        return redirect()->route('Customer.index')->with('success', 'Customer deleted successfully.');
        
    }
}

