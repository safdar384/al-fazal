<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpenseLedger;
use App\Support\BankLedgerQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    /**
     * Show expense transaction page
     */
    public function expenseTransactionPage()
    {
        $expenses = Expense::where('status', 'Active')->orderBy('expenseName', 'asc')->get();
        $banks = BankAccount::orderBy('bankName', 'asc')->get();

        return view('pages.expense.expenseTransaction', compact('expenses', 'banks'));
    }

    /**
     * Get expense details
     */
    public function getExpenseDetails($expenseCode)
    {
        $expense = Expense::where('expenseCode', $expenseCode)->first();

        if (! $expense) {
            return response()->json(['error' => 'Expense not found'], 404);
        }

        // Calculate total from expense_ledger
        $totalDebit = ExpenseLedger::where('expenseCode', $expenseCode)
            ->sum('debit') ?? 0;
        $totalCredit = ExpenseLedger::where('expenseCode', $expenseCode)
            ->sum('credit') ?? 0;

        return response()->json([
            'expenseCode' => $expense->expenseCode,
            'expenseName' => $expense->expenseName,
            'expenseCategory' => $expense->expenseCategory,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'balance' => $totalDebit - $totalCredit,
        ]);
    }

    /**
     * Store expense transaction
     */
    public function storeExpenseTransaction(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'expenseCode' => 'required|string',
                'description' => 'nullable|string',
                'amount' => 'required|numeric|min:0',
                'paymentMethod' => 'required|string',
                'accountCode' => 'nullable|string',
                'category' => 'required|in:Debit,Credit',
            ]);

            $ledgerData = [
                'entryDate' => now(),
                'expenseCode' => $validated['expenseCode'],
                'description' => $validated['description'] ?? '',
                'amount' => $validated['amount'],
                'paymentMethod' => $validated['paymentMethod'],
                'accountCode' => $validated['accountCode'] ?? null,
                'category' => $validated['category'],
            ];

            // Set debit/credit based on category
            if ($validated['category'] === 'Debit') {
                $ledgerData['debit'] = $validated['amount'];
                $ledgerData['credit'] = 0;
            } else {
                $ledgerData['credit'] = $validated['amount'];
                $ledgerData['debit'] = 0;
            }

            $expenseLedger = ExpenseLedger::create($ledgerData);

            // If bank account selected, create corresponding entry in ledgers (general ledger)
            if (! empty($validated['accountCode'])) {
                $bank = BankAccount::where('bankAccount', $validated['accountCode'])->first();
                if ($bank) {
                    DB::table('ledgers')->insert([
                        'entry_date' => now()->format('Y-m-d'),
                        'reference_no' => (string) $expenseLedger->id,
                        'market_voucher_id' => null,
                        'account_id' => \App\Support\BankLedgerQuery::ensureGlAccount($bank),
                        'debit' => $validated['category'] === 'Credit' ? $validated['amount'] : 0,
                        'credit' => $validated['category'] === 'Debit' ? $validated['amount'] : 0,
                        'description' => 'Expense: '.($validated['description'] ?? ''),
                        'category' => 'Expense',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Transaction saved successfully!']);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get expense ledger
     */
    public function getExpenseLedger($expenseCode)
    {
        $ledgers = ExpenseLedger::where('expenseCode', $expenseCode)
            ->orderBy('entryDate', 'desc')
            ->orderBy('id', 'desc')
            ->limit(15)
            ->get();

        return response()->json(['data' => $ledgers]);
    }

    /**
     * Store new expense
     */
    public function storeExpense(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'expenseCode' => 'required|string|unique:expenses,expenseCode',
                'expenseName' => 'required|string',
                'expenseCategory' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            $validated['status'] = 'Active';
            $validated['amount'] = 0;

            $expense = Expense::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Expense created successfully!',
                'expense' => [
                    'expenseCode' => $expense->expenseCode,
                    'expenseName' => $expense->expenseName,
                    'expenseCategory' => $expense->expenseCategory,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete expense
     */
    public function deleteExpense($expenseCode)
    {
        try {
            DB::beginTransaction();

            $expense = Expense::where('expenseCode', $expenseCode)->firstOrFail();

            // Check if there are transactions
            $hasTransactions = ExpenseLedger::where('expenseCode', $expense->expenseCode)->exists();

            if ($hasTransactions) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete expense with existing transactions!',
                ], 400);
            }

            $expense->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Expense deleted successfully!']);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete expense ledger entry
     */
    public function deleteExpenseLedger($id)
    {
        try {
            DB::beginTransaction();

            $ledger = ExpenseLedger::findOrFail($id);

            // Delete corresponding bank ledger entry if exists
            DB::table('ledgers')->where('reference_no', $id)->where('category', 'Expense')->delete();

            $ledger->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Transaction deleted successfully!']);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update expense ledger entry
     */
    public function updateExpenseLedger(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'description' => 'nullable|string|max:255',
                'amount' => 'required|numeric|min:0',
                'paymentMethod' => 'nullable|string|max:50',
                'accountCode' => 'nullable|string|max:50',
                'category' => 'required|in:Debit,Credit',
            ]);

            $ledger = ExpenseLedger::findOrFail($id);

            // Update ledger entry
            $ledger->description = $validated['description'] ?? '';
            $ledger->amount = $validated['amount'];
            $ledger->paymentMethod = $validated['paymentMethod'] ?? 'Bank';
            $ledger->accountCode = $validated['accountCode'] ?? '';
            $ledger->category = $validated['category'];

            // Set debit/credit based on category
            if ($validated['category'] === 'Debit') {
                $ledger->debit = $validated['amount'];
                $ledger->credit = 0;
            } else {
                $ledger->debit = 0;
                $ledger->credit = $validated['amount'];
            }

            $ledger->save();

            // Update corresponding bank ledger entry if exists
            $bankLedger = DB::table('ledgers')->where('reference_no', $id)->where('category', 'Expense')->first();
            if ($bankLedger && ! empty($validated['accountCode'])) {
                DB::table('ledgers')->where('id', $bankLedger->id)->update([
                    'entry_date' => now(),
                    'debit' => $validated['category'] === 'Credit' ? $validated['amount'] : 0,
                    'credit' => $validated['category'] === 'Debit' ? $validated['amount'] : 0,
                    'description' => 'Expense: '.$validated['description'],
                ]);
            } elseif (! $bankLedger && ! empty($validated['accountCode'])) {
                $bank = BankAccount::where('bankAccount', $validated['accountCode'])->first();
                if ($bank) {
                    DB::table('ledgers')->insert([
                        'entry_date' => now()->format('Y-m-d'),
                        'reference_no' => (string) $id,
                        'market_voucher_id' => null,
                        'account_id' => \App\Support\BankLedgerQuery::ensureGlAccount($bank),
                        'debit' => $validated['category'] === 'Credit' ? $validated['amount'] : 0,
                        'credit' => $validated['category'] === 'Debit' ? $validated['amount'] : 0,
                        'description' => 'Expense: '.($validated['description'] ?? ''),
                        'category' => 'Expense',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Transaction updated successfully!']);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all expenses for management
     */
    public function getAllExpenses()
    {
        $expenses = Expense::orderBy('expenseName', 'asc')->get();

        return response()->json(['data' => $expenses]);
    }

    /**
     * Show expense ledger page
     */
    public function expenseLedgerPage()
    {
        $expenses = Expense::where('status', 'Active')->orderBy('expenseName', 'asc')->get();
        $banks = BankAccount::orderBy('bankName', 'asc')->get();

        return view('pages.expense.expenseLedger', compact('expenses', 'banks'));
    }

    /**
     * Show expense report print page
     */
    public function expenseReportPrintPage()
    {
        return view('pages.expense.expenseReportPrint');
    }

    /**
     * Get expense ledger report with filters
     */
    public function getExpenseLedgerReport(Request $request)
    {
        $query = ExpenseLedger::query()
            ->from('expense_ledger')
            ->leftJoin('expenses', 'expenses.expenseCode', '=', 'expense_ledger.expenseCode')
            ->select([
                'expense_ledger.*',
                'expenses.expenseName as expenseName',
            ]);

        // Filter by expense code
        if ($request->filled('expenseCode')) {
            $query->where('expense_ledger.expenseCode', $request->expenseCode);
        }

        // Filter by date range
        if ($request->filled('fromDate')) {
            $query->whereDate('expense_ledger.entryDate', '>=', $request->fromDate);
        }

        if ($request->filled('toDate')) {
            $query->whereDate('expense_ledger.entryDate', '<=', $request->toDate);
        }

        // Filter by category (Debit/Credit)
        if ($request->filled('category')) {
            $query->where('expense_ledger.category', $request->category);
        }

        $ledgers = $query->orderBy('expense_ledger.entryDate', 'desc')
            ->orderBy('expense_ledger.id', 'desc')
            ->get();

        return response()->json(['data' => $ledgers]);
    }
}
