<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerArea;
use App\Models\NewCustomer;
use App\Models\RecoveryVoucher;
use App\Models\customerLedger;

class customerLedgerController extends Controller
{
    // Show the page
    public function index()
    {
        $areas = CustomerArea::orderBy('areaCode', 'asc')->get();
        
        return view('pages.customerAccount.custRecoveryTransection', compact('areas'));
    }

    // Return customers in area
    public function getCustomersByArea($areaCode)
    {
        $customers = NewCustomer::where('custArea', $areaCode)
                                ->orderBy('custName', 'asc')
                                ->get(['custCode','custName']); // return only needed fields


         $balance = CustomerArea::where('areaCode', $areaCode)->value('balance');

        //return response()->json($customers, );
        return response()->json([
        'customers' => $customers,
        'balance'   => $balance
        ]); 
    }

    // Return vouchers for area (all time, not limited to today)
    public function getVouchersByArea($areaCode)
    {
        $vouchers = RecoveryVoucher::whereHas('customer', function($q) use ($areaCode) {
            $q->where('custArea', $areaCode);
        })
        ->orderBy('id', 'asc')
        ->get();

        return response()->json($vouchers);
    }

    public function getVoucher($id)
    {
        $voucher = RecoveryVoucher::find($id);
        return response()->json($voucher);
    }

    // Edit voucher form
    public function edit($id)
    {

        $areas = CustomerArea::orderBy('areaCode', 'asc')->get();

        $voucher = RecoveryVoucher::findOrFail($id);
        $custmerLedger = customerLedger::find($voucher->customer_ledgers_id);
        if ($custmerLedger) {
            $voucher->customerDate = $custmerLedger->customerDate;
            $voucher->invoiceNumber = $custmerLedger->invoiceNumber;
        }          
        return view('pages.customerAccount.editRecoveryTranasection', compact('voucher', 'areas'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'customerArea'    => 'required',
            'customerCode'    => 'required',
            'marketRecovery'  => 'required|numeric|min:0',
            'voucherDiscount' => 'nullable|numeric|min:0',
            'misc'            => 'nullable|numeric|min:0',
            'customerDate'    => 'required|date',
        ]);

        // Find voucher
        $voucher = RecoveryVoucher::findOrFail($id);

        // Save old recovery for balance adjustment
        $oldRecovery = $voucher->recovery;

        // --- Update RecoveryVoucher ---
        $voucher->update([
            'customerArea' => $request->customerArea,
            'customerCode' => $request->customerCode,
            'recovery'     => $request->marketRecovery,
            'discount'     => $request->voucherDiscount,
            'misc'         => $request->misc,
            'description'  => $request->description,
            'voucher_date' => $request->customerDate,
            'lastBalance'  => $request->lastBalance ?? $voucher->lastBalance,
        ]);

        // --- Update related CustomerLedger ---
        $ledger = CustomerLedger::find($voucher->customer_ledgers_id);

        if ($ledger) {
            $ledger->update([
                'customerDate'  => $request->customerDate,
                'invoiceNumber' => $voucher->id, // invoice number
                'customerArea'  => $request->customerArea,
                'customerCode'  => $request->customerCode,
                'description'   => $request->description,
                'credit'        => $request->marketRecovery,
                'debit'         => 0,
                'category'      => 'Recovery',
            ]);
        }

        // --- Update Area Balance ---
        $area = CustomerArea::where('areaCode', $request->customerArea)->first();
        if ($area) {
            $area->balance = ($area->balance - $oldRecovery) + $request->marketRecovery;
            $area->save();
        }

        return redirect()->route('ledgerTransection.index')
            ->with('success', 'Record updated successfully');
    }


    
    // Store new customer ledger entry
    public function store(Request $request)
    {
        $request->validate([
            'customerDate'    => 'required|date',
            'customerArea'    => 'required',
            'customerCode'    => 'required',
            'description'     => 'required',
            'marketRecovery'  => 'required|numeric|min:0',
            'voucherDiscount' => 'required|numeric|min:0',
            'biltyMisc'       => 'required|numeric|min:0',
        ]);

        // Calculate total
        $amount = $request->marketRecovery + $request->voucherDiscount + $request->biltyMisc;

        // Save to Customer Ledger
        $custLedger = customerLedger::create([
            'customerDate' => $request->customerDate,
            'invoiceNumber'=> $request->invoiceNumber ?? null,
            'customerArea' => $request->customerArea,
            'customerCode' => $request->customerCode,
            'description'  => $request->description,
            'credit'       => 0,
            'debit'        => $amount,
            'category'     => 'Recovery',
        ]);

        // Save to Recovery Voucher
        RecoveryVoucher::create([
            'voucher_date'          => $request->customerDate,
            'customerArea'          => $request->customerArea,
            'customerCode'          => $request->customerCode,
            'description'           => $request->description,
            'recovery'              => $request->marketRecovery,
            'discount'              => $request->voucherDiscount,
            'misc'                  => $request->biltyMisc,
            'customer_ledgers_id'   => $custLedger->id,
            'preBalance'            => $request->preBalance ?? 0,
            'lastBalance'           => $request->lastBalance ?? 0,
        ]);

        // save area balance
        $area = CustomerArea::where('areaCode', $request->customerArea)->first();
        if ($area) {
            $area->balance = ($area->balance ?? 0) + ($request->marketRecovery ?? 0);
            $area->save();
        }else {
            return response()->json(['error' => 'Area not found'], 404);
        }   

        // Return success response
        return response()->json([
            'success'      => true,
            'customerCode' => $request->customerCode,
            'recovery'     => $request->marketRecovery,
            'discount'     => $request->voucherDiscount,
            'misc'         => $request->biltyMisc,
            'lastBalance'  => $request->lastBalance,
        ]);
    }

    // Get balance for a customer
    public function getBalance($customerCode)
    {
        $balance = customerLedger::where('customerCode', $customerCode)
            ->selectRaw('COALESCE(SUM(credit),0) - COALESCE(SUM(debit),0) as balance')
            ->value('balance');

        return response()->json(['balance' => $balance]);
    }

    // Get vouchers by area for today only
    public function getByArea($areaCode)
    {
        $today = now()->format('Y-m-d'); // index-friendly

        $vouchers = RecoveryVoucher::select('id','voucher_date','customerCode','description','recovery','discount','misc','lastBalance', 'preBalance','customerArea')
            ->where('customerArea', $areaCode)
            ->where('voucher_date', $today)   // fast compare (index use karega)
            ->orderBy('voucher_date', 'desc')
            ->limit(50) // records ek sath
            ->get();
        $customers = NewCustomer::where('custArea', $areaCode)
                                ->orderBy('custName', 'asc', )
                                ->get(['custCode','custName','custCity','custAddress']); // return only needed fields

         foreach ($vouchers as $voucher) {
            $customer = $customers->firstWhere('custCode', $voucher->customerCode);
            $voucher->custName = $customer ? $customer->custName : 'Unknown';
            $voucher->custCity = $customer ? $customer->custCity : 'Unknown';
            $voucher->custAddress = $customer ? $customer->custAddress : 'Unknown';
        }  
        

        return response()->json($vouchers);
    }


}
