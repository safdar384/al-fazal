<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Support\BankLedgerQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    public function index(): View
    {
        $bankAC = BankAccount::orderBy('bankName')->get()->map(function (BankAccount $bank) {
            $bank->setAttribute('balance', BankLedgerQuery::signedBalance($bank));

            return $bank;
        });

        return view('Pages.Setup.bankAccount', compact('bankAC'));
    }

    public function listAccounts(): JsonResponse
    {
        $banks = BankAccount::orderBy('bankName')->get()->map(function (BankAccount $bank) {
            return [
                'id' => $bank->id,
                'bankAccount' => $bank->bankAccount,
                'bankName' => $bank->bankName,
                'bankAddress' => $bank->bankAddress,
                'branchCode' => $bank->branchCode,
                'balance' => BankLedgerQuery::signedBalance($bank),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $banks,
            'total' => $banks->count(),
            'totalBalance' => round($banks->sum('balance'), 2),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->normalizeBankInput($request);

        $validated = $request->validate([
            'bankAccount' => 'required|string|max:50|unique:bank_accounts,bankAccount',
            'bankName' => 'required|string|max:255',
            'bankAddress' => 'required|string|max:255',
            'branchCode' => 'required|string|max:20',
        ]);

        $bank = BankAccount::create($validated);
        BankLedgerQuery::ensureGlAccount($bank);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bank Account created successfully!',
                'bank' => [
                    'id' => $bank->id,
                    'bankAccount' => $bank->bankAccount,
                    'bankName' => $bank->bankName,
                    'bankAddress' => $bank->bankAddress,
                    'branchCode' => $bank->branchCode,
                    'balance' => 0,
                ],
            ]);
        }

        return redirect()->route('bankAccount.index')->with('success', 'Bank Account created successfully!');
    }

    public function update(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $bank = BankAccount::findOrFail($id);
        $previousCode = $bank->bankAccount;
        $this->normalizeBankInput($request);

        $validated = $request->validate([
            'bankAccount' => 'required|string|max:50|unique:bank_accounts,bankAccount,'.$bank->id,
            'bankName' => 'required|string|max:255',
            'bankAddress' => 'required|string|max:255',
            'branchCode' => 'required|string|max:20',
        ]);

        $bank->update($validated);
        $bank->refresh();
        BankLedgerQuery::syncGlAccount($bank, $previousCode);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bank Account updated successfully!',
                'bank' => [
                    'id' => $bank->id,
                    'bankAccount' => $bank->bankAccount,
                    'bankName' => $bank->bankName,
                    'bankAddress' => $bank->bankAddress,
                    'branchCode' => $bank->branchCode,
                    'balance' => BankLedgerQuery::signedBalance($bank),
                ],
            ]);
        }

        return redirect()->route('bankAccount.index')->with('success', 'Bank Account updated successfully!');
    }

    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $bank = BankAccount::findOrFail($id);
        $name = $bank->bankName;
        $bank->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Bank Account \"{$name}\" deleted successfully!",
            ]);
        }

        return redirect()->route('bankAccount.index')->with('success', 'Bank Account deleted successfully!');
    }

    protected function normalizeBankInput(Request $request): void
    {
        $request->merge([
            'bankAccount' => ucwords(strtolower(trim((string) $request->input('bankAccount', '')))),
            'bankName' => ucwords(strtolower(trim((string) $request->input('bankName', '')))),
            'bankAddress' => ucwords(strtolower(trim((string) $request->input('bankAddress', '')))),
            'branchCode' => strtoupper(trim((string) $request->input('branchCode', ''))),
        ]);
    }
}
