<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Models\CustomerArea;
use App\Models\NewCustomer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerApiController extends Controller
{
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        try {
            $customer = NewCustomer::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully!',
                'data' => [
                    'id' => $customer->custCode,
                    'custCode' => $customer->custCode,
                    'code' => $customer->custCode,
                    'custName' => $customer->custName,
                    'name' => $customer->custName,
                    'business' => $customer->bussnessName ?? '',
                    'city' => $customer->custCity ?? '',
                    'phone' => $customer->custContect ?? '',
                    'address' => $customer->custAddress ?? '',
                    'area' => $customer->custArea ?? '',
                    'category' => $customer->custCatagory ?? '',
                    'discount' => (float) ($customer->custdiscount ?? 0),
                ],
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer. Please try again.',
            ], 500);
        }
    }

    public function areas(): JsonResponse
    {
        $areas = CustomerArea::query()
            ->orderBy('areaCode')
            ->get(['id', 'areaCode', 'areaName', 'ajentName']);

        return response()->json([
            'success' => true,
            'data' => $areas,
        ]);
    }

    public function categories(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => NewCustomer::CATEGORIES,
        ]);
    }

    public function lookup(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));
        $limit = (int) $request->query('limit', 30);

        $query = NewCustomer::query()->excludeProductSupplier()->orderBy('custName');
        if ($term !== '') {
            $like = '%'.$term.'%';
            $query->where(function ($q) use ($like) {
                $q->where('custCode', 'like', $like)
                    ->orWhere('custName', 'like', $like)
                    ->orWhere('bussnessName', 'like', $like)
                    ->orWhere('custContect', 'like', $like)
                    ->orWhere('custArea', 'like', $like);
            });
        }

        $rows = $query->limit($limit)->get([
            'custCode', 'custName', 'bussnessName', 'custCity', 'custContect', 'custAddress', 'custArea', 'custdiscount',
        ]);

        return response()->json([
            'success' => true,
            'data' => $rows->map(fn (NewCustomer $c) => [
                'code' => $c->custCode,
                'name' => $c->custName,
                'business' => $c->bussnessName ?? '',
                'city' => $c->custCity ?? '',
                'phone' => $c->custContect ?? '',
                'address' => $c->custAddress ?? '',
                'area' => $c->custArea ?? '',
                'discount' => (float) ($c->custdiscount ?? 0),
            ])->values(),
        ]);
    }
}
