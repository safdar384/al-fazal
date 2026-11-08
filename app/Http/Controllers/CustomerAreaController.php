<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAreaRequest;
use App\Models\CustomerArea;
use App\Models\NewCustomer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerAreaController extends Controller
{
    /**
     * Store a newly created area (AJAX from customer form modal).
     */
    public function store(StoreAreaRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $area = CustomerArea::create($request->validated());

            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Area created successfully!',
                    'area' => $area,
                ], 201);
            }

            return redirect()
                ->back()
                ->with('success', 'Area created successfully!');

        } catch (Exception $e) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create area.',
                    'errors' => $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create area. Please try again.');
        }
    }

    /**
     * Remove an area. Blocks delete when any customer still uses this area code.
     */
    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        try {
            $area = CustomerArea::findOrFail($id);
            $code = strtoupper(trim((string) $area->areaCode));

            $inUse = NewCustomer::query()
                ->whereRaw('UPPER(custArea) = ?', [$code])
                ->exists();

            if ($inUse) {
                $message = "Cannot delete \"{$area->areaName}\" because it is used by one or more customers.";

                if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 422);
                }

                return redirect()->back()->with('error', $message);
            }

            $name = $area->areaName;
            $area->delete();

            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Area \"{$name}\" deleted successfully!",
                    'data' => [
                        'id' => $id,
                        'areaCode' => $code,
                        'areaName' => $name,
                    ],
                ]);
            }

            return redirect()->back()->with('success', "Area \"{$name}\" deleted successfully!");
        } catch (Exception $e) {
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete area.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Failed to delete area.');
        }
    }

    /**
     * Get areas list for AJAX requests.
     */
    public function getsAreas(Request $request): JsonResponse
    {
        $query = $request->input('search');

        $areas = CustomerArea::when($query, function ($q) use ($query) {
            $q->where('areaCode', 'like', "%{$query}%")
                ->orWhere('areaName', 'like', "%{$query}%");
        })->orderBy('areaName', 'asc')->get();

        return response()->json($areas);
    }
}
