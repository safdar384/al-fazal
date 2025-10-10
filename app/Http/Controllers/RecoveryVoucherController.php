<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RecoveryVoucherController extends Controller
{
  
    public function store(Request $request)
{
    // 1. Validate Recovery Voucher form
    $request->validate([
        'voucher_date' => 'required|date',
        'customerCode' => 'required|string',
        'description'  => 'nullable|string',
        'recovery'     => 'nullable|numeric',
        'discount'     => 'nullable|numeric',
        'misc'         => 'nullable|numeric',
        'preBalance'   => 'nullable|numeric',
        'lastBalance'  => 'nullable|numeric',
    ]);

    // 2. Save in Recovery Voucher
    $voucher = RecoveryVoucher::create([
        'voucher_date' => $request->voucher_date,
        'customerCode' => $request->customerCode,
        'description'  => $request->description,
        'recovery'     => $request->recovery ?? 0,
        'discount'     => $request->discount ?? 0,
        'misc'         => $request->misc ?? 0,
        'preBalance'   => $request->preBalance ?? 0,
        'lastBalance'  => $request->lastBalance ?? 0,
    ]);

    // 3. Get Area from Customer table (kyunke ledger me area rakhna zaroori hai)
    $customer = \App\Models\NewCustomer::where('custCode', $request->customerCode)->first();

    // 4. Save in Customer Ledger
    \App\Models\customerLedger::create([
        'customerDate'  => $request->voucher_date,
        'invoiceNumber' => null,
        'customerArea'  => $customer ? $customer->custArea : 'UNKNOWN', // empty na ho
        'customerCode'  => $request->customerCode,
        'description'   => $request->description,
        'credit'        => $request->recovery ?? 0,
        'debit'         => 0,
    ]);

    return redirect()->route('ledgerTransection.index')->with('success', 'Voucher and Ledger saved successfully!');
}
    public function todayVouchers()
    {
        $today = date('Y-m-d');
        $vouchers = RecoveryVoucher::whereDate('voucher_date', $today)->with('customer')->get();
        return view('pages.customerAccount.todayVouchers', compact('vouchers'));
    }


}
