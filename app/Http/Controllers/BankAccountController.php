<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bankAC = BankAccount::all(); // Fetch all bank
        return view('pages.Setup.bankAccount', compact('bankAC'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bankAccount'  => 'required|string|max:50|unique:bank_accounts,bankAccount',
            'bankName'     => 'required|string|max:255',
            'bankAddress'  => 'required|string|max:255',
            'branchCode'   => 'required|string|max:20',
            'balance'      => 'required|numeric|min:0',
        ]);

        $bankAccount = BankAccount::create($validated);

        return redirect()->route('bankAccount.index')->with('success', 'Bank Account created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(bankAccount $bankAccount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(bankAccount $bankAccount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $bank = bankAccount::findOrFail($id);
        $bank->update($request->all());

        return redirect()->route('bankAccount.index')->with('success', 'Bank Account Update successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bank = bankAccount::findOrFail($id);
        $bank->delete();

        return redirect()->route('bankAccount.index')->with('success', 'Bank Account Delete successfully!');
    }
}
