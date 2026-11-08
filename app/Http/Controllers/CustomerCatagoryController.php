<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerCatagoryRequest;
use App\Models\CustomerCatagory;
use App\Models\NewCustomer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerCatagoryController extends Controller
{
    /**
     * Store a newly created customer category (AJAX from customer form).
     */
    public function store(StoreCustomerCatagoryRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $category = CustomerCatagory::create($request->validated());

            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Customer category added successfully!',
                    'data' => $category,
                ], 201);
            }

            return redirect()
                ->route('Customer.create')
                ->with('success', 'Customer category added successfully!');
        } catch (Exception $e) {
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add customer category.',
                ], 422);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to add customer category. Please try again.');
        }
    }

    /**
     * Remove the specified customer category.
     * Blocks delete when any customer still uses this category.
     */
    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        try {
            $category = CustomerCatagory::findOrFail($id);

            $inUse = NewCustomer::query()
                ->whereRaw('LOWER(custCatagory) = ?', [strtolower($category->catagoryName)])
                ->exists();

            if ($inUse) {
                $message = "Cannot delete \"{$category->catagoryName}\" because it is used by one or more customers.";

                if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 422);
                }

                return redirect()->back()->with('error', $message);
            }

            $name = $category->catagoryName;
            $category->delete();

            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Category \"{$name}\" deleted successfully!",
                    'data' => ['id' => (int) $id, 'catagoryName' => $name],
                ]);
            }

            return redirect()->back()->with('success', "Category \"{$name}\" deleted successfully!");
        } catch (Exception $e) {
            if ($request->ajax() || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete customer category.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Failed to delete customer category.');
        }
    }
}
