<?php

namespace App\Http\Controllers;

use App\Models\Labour;
use App\Models\LabourAccountTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabourController extends Controller
{
    public function index(): View
    {
        $labours = Labour::orderBy('labourName')->get();

        return view('Pages.Setup.labour', compact('labours'));
    }

    public function create(): View
    {
        return view('Pages.Setup.newLabour');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $this->validateLabour($request);
        Labour::create($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Labour created successfully.',
            ]);
        }

        return redirect()
            ->route('Labour.create')
            ->with('success', 'Labour created successfully.');
    }

    public function edit(int $id): View
    {
        $labour = Labour::findOrFail($id);

        return view('Pages.Setup.editLabour', compact('labour'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $labour = Labour::findOrFail($id);
        $labour->update($this->validateLabour($request, $labour->id));

        return redirect()->route('Labour.index')->with('success', 'Labour updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $labour = Labour::findOrFail($id);

        $hasLedger = LabourAccountTransaction::where('labour_id', $labour->id)->exists();
        if ($hasLedger) {
            return redirect()
                ->route('Labour.index')
                ->with('error', "Cannot delete \"{$labour->labourName}\" because ledger entries exist. Remove ledger transactions first.");
        }

        $labour->delete();

        return redirect()->route('Labour.index')->with('success', 'Labour deleted.');
    }

    protected function validateLabour(Request $request, ?int $ignoreId = null): array
    {
        $request->merge([
            'labourCode' => strtoupper(trim((string) $request->input('labourCode', ''))),
            'labourName' => ucwords(strtolower(trim((string) $request->input('labourName', '')))),
            'labourAddress' => ucwords(strtolower(trim((string) $request->input('labourAddress', '')))),
            'labourCatagory' => strtolower(trim((string) $request->input('labourCatagory', ''))),
        ]);

        $validated = $request->validate([
            'labourCode' => 'required|string|max:50|unique:labours,labourCode,'.($ignoreId ?? 'NULL').',id',
            'labourName' => 'required|string|max:255',
            'labourAddress' => 'required|string|max:500',
            'labourCatagory' => 'required|string|max:50',
            'salary' => 'required|numeric|min:0',
            'advance' => 'required|numeric',
            'balance' => 'required|numeric',
            'commissionPercentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['labour_type'] = $validated['labourCatagory'] === 'fitter'
            ? 'fitter_labour'
            : Labour::TYPE_SALARY;

        if ($validated['labourCatagory'] !== 'ajent') {
            $validated['commissionPercentage'] = null;
        }

        return $validated;
    }
}
